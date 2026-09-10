<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\SubdomainReadRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\SubdomainRequest;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\ManagedDomain;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\ServerSubdomain;

class SubdomainController extends ClientApiController
{
    public function index(SubdomainReadRequest $request, Server $server): array
    {
        return [
            'subdomains' => ServerSubdomain::query()
                ->with(['allocation', 'domain'])
                ->where('server_id', $server->id)
                ->orderBy('fqdn')
                ->get()
                ->map(fn (ServerSubdomain $item) => [
                    'id' => $item->id,
                    'fqdn' => $item->fqdn,
                    'allocation' => $item->allocation->toString(),
                    'allocationId' => $item->allocation_id,
                    'domainId' => $item->domain_id,
                ]),
            'domains' => ManagedDomain::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
            'allocations' => $server->allocations()->orderBy('port')->get()->map(fn (Allocation $allocation) => [
                'id' => $allocation->id,
                'address' => $allocation->toString(),
                'target' => $allocation->alias,
                'port' => $allocation->port,
            ]),
        ];
    }

    public function store(SubdomainRequest $request, Server $server): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i'],
            'domainId' => ['required', 'integer', 'exists:managed_domains,id'],
            'allocationId' => ['required', 'integer', 'exists:allocations,id'],
        ]);

        $domain = ManagedDomain::query()->whereKey($data['domainId'])->where('active', true)->firstOrFail();
        $allocation = $server->allocations()->whereKey($data['allocationId'])->firstOrFail();
        $fqdn = strtolower($data['label'] . '.' . $domain->name);

        if (ServerSubdomain::where('fqdn', $fqdn)->exists()) {
            abort(422, 'This subdomain is already in use.');
        }

        $recordIds = $this->createCloudflareRecords($domain, $fqdn, $allocation);
        $item = ServerSubdomain::create([
            'server_id' => $server->id,
            'allocation_id' => $allocation->id,
            'domain_id' => $domain->id,
            'label' => strtolower($data['label']),
            'fqdn' => $fqdn,
            'address_record_id' => $recordIds['address'],
            'srv_record_id' => $recordIds['srv'],
        ]);

        return [
            'id' => $item->id,
            'fqdn' => $item->fqdn,
            'allocation' => $allocation->toString(),
            'allocationId' => $allocation->id,
            'domainId' => $domain->id,
        ];
    }

    public function delete(SubdomainRequest $request, Server $server, ServerSubdomain $subdomain): JsonResponse
    {
        abort_unless($subdomain->server_id === $server->id, 404);
        $this->deleteCloudflareRecords($subdomain);
        $subdomain->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function createCloudflareRecords(ManagedDomain $domain, string $fqdn, Allocation $allocation): array
    {
        if (!filled(config('services.cloudflare.token')) || !filled($domain->zone_id)) {
            return ['address' => null, 'srv' => null];
        }

        $http = $this->cloudflare();
        $address = $http->post("zones/{$domain->zone_id}/dns_records", [
            'type' => filter_var($allocation->alias, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'AAAA' : 'A',
            'name' => $fqdn,
            'content' => $allocation->alias,
            'ttl' => 1,
            'proxied' => false,
        ]);
        if ($address->failed()) {
            abort(422, $address->json('errors.0.message', 'Cloudflare rejected the address record.'));
        }

        $srv = $http->post("zones/{$domain->zone_id}/dns_records", [
            'type' => 'SRV',
            'name' => '_minecraft._tcp.' . $fqdn,
            'data' => [
                'service' => '_minecraft',
                'proto' => '_tcp',
                'name' => $fqdn,
                'priority' => 0,
                'weight' => 0,
                'port' => $allocation->port,
                'target' => $fqdn,
            ],
            'ttl' => 1,
        ]);
        if ($srv->failed()) {
            $http->delete("zones/{$domain->zone_id}/dns_records/{$address->json('result.id')}");
            abort(422, $srv->json('errors.0.message', 'Cloudflare rejected the SRV record.'));
        }

        return ['address' => $address->json('result.id'), 'srv' => $srv->json('result.id')];
    }

    private function deleteCloudflareRecords(ServerSubdomain $subdomain): void
    {
        if (!filled(config('services.cloudflare.token')) || !$subdomain->domain?->zone_id) {
            return;
        }

        $http = $this->cloudflare();
        foreach ([$subdomain->address_record_id, $subdomain->srv_record_id] as $recordId) {
            if ($recordId) {
                $http->delete("zones/{$subdomain->domain->zone_id}/dns_records/{$recordId}");
            }
        }
    }

    private function cloudflare()
    {
        return Http::baseUrl('https://api.cloudflare.com/client/v4/')
            ->withToken((string) config('services.cloudflare.token'))
            ->acceptJson()
            ->timeout(20);
    }
}
