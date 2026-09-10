<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ManagedDnsRecord;
use Pterodactyl\Models\ManagedDomain;

class DomainController extends Controller
{
    private const RECORD_TYPES = ['A', 'AAAA', 'CNAME', 'TXT', 'MX', 'SRV'];

    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.domains.index', [
            'domains' => ManagedDomain::with('records')->orderBy('name')->get(),
            'cloudflareConfigured' => filled(config('services.cloudflare.token')),
        ]);
    }

    public function storeDomain(): RedirectResponse
    {
        $data = request()->validate([
            'name' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i'],
            'zone_id' => ['nullable', 'string', 'max:64', 'regex:/^[a-f0-9]{32}$/i'],
        ]);

        $name = strtolower(rtrim($data['name'], '.'));
        $zoneId = $data['zone_id'] ?? $this->findZoneId($name);
        if (!$zoneId && filled(config('services.cloudflare.token'))) {
            $this->alert->danger('Cloudflare zone was not found. Add the Zone ID or check the API token permissions.')->flash();
            return back()->withInput();
        }

        ManagedDomain::create(['name' => $name, 'zone_id' => $zoneId, 'active' => true]);
        $this->alert->success('Domain was added successfully.')->flash();

        return redirect()->route('admin.domains');
    }

    public function destroyDomain(ManagedDomain $domain): RedirectResponse
    {
        $domain->delete();
        $this->alert->success('Domain and its local records were removed.')->flash();

        return redirect()->route('admin.domains');
    }

    public function storeRecord(ManagedDomain $domain): RedirectResponse
    {
        $data = request()->validate([
            'type' => ['required', 'string', 'in:' . implode(',', self::RECORD_TYPES)],
            'name' => ['required', 'string', 'max:253', 'regex:/^[a-z0-9_.*@:-]+$/i'],
            'content' => ['required', 'string', 'max:4096'],
            'ttl' => ['required', 'integer', 'in:1,60,120,300,600,900,1800,3600,7200,18000,28800,43200,86400'],
            'proxied' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'type' => strtoupper($data['type']),
            'name' => $this->normalizeRecordName($data['name'], $domain),
            'content' => $data['content'],
            'ttl' => (int) $data['ttl'],
            'proxied' => (bool) ($data['proxied'] ?? false),
        ];

        $cloudflareId = null;
        if (filled(config('services.cloudflare.token')) && filled($domain->zone_id)) {
            $response = $this->cloudflare()->post("zones/{$domain->zone_id}/dns_records", $payload);
            if ($response->failed()) {
                $this->alert->danger('Cloudflare rejected the DNS record: ' . $response->json('errors.0.message', 'Unknown error'))->flash();
                return back()->withInput();
            }
            $cloudflareId = $response->json('result.id');
        }

        $domain->records()->create($payload + ['cloudflare_id' => $cloudflareId]);
        $this->alert->success('DNS record was added successfully.')->flash();

        return back();
    }

    public function destroyRecord(ManagedDnsRecord $record): RedirectResponse
    {
        if ($record->cloudflare_id && filled(config('services.cloudflare.token')) && filled($record->domain->zone_id)) {
            $response = $this->cloudflare()->delete("zones/{$record->domain->zone_id}/dns_records/{$record->cloudflare_id}");
            if ($response->failed() && $response->status() !== 404) {
                $this->alert->danger('Cloudflare rejected the DNS record deletion.')->flash();
                return back();
            }
        }

        $record->delete();
        $this->alert->success('DNS record was deleted.')->flash();

        return back();
    }

    private function findZoneId(string $domain): ?string
    {
        if (!filled(config('services.cloudflare.token'))) {
            return null;
        }

        $response = $this->cloudflare()->get('zones', ['name' => $domain, 'status' => 'active']);
        return $response->successful() ? $response->json('result.0.id') : null;
    }

    private function normalizeRecordName(string $name, ManagedDomain $domain): string
    {
        $name = strtolower(rtrim($name, '.'));
        if ($name === '@') {
            return $domain->name;
        }

        return Str::endsWith($name, '.' . $domain->name) ? $name : $name . '.' . $domain->name;
    }

    private function cloudflare()
    {
        return Http::baseUrl('https://api.cloudflare.com/client/v4/')
            ->withToken((string) config('services.cloudflare.token'))
            ->acceptJson()
            ->timeout(20);
    }
}
