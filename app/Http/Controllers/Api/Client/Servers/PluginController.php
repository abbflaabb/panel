<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Files\InstallPluginRequest;

class PluginController extends ClientApiController
{
    public function __construct(private DaemonFileRepository $fileRepository)
    {
        parent::__construct();
    }

    public function install(InstallPluginRequest $request, Server $server): JsonResponse
    {
        $this->fileRepository->setServer($server)->pull(
            $request->string('url')->toString(),
            '/plugins',
            [
                'filename' => $request->string('filename')->toString(),
                'use_header' => false,
                'foreground' => true,
            ],
        );

        Activity::event('server:plugin.install')
            ->property('filename', $request->string('filename')->toString())
            ->property('provider', parse_url($request->string('url')->toString(), PHP_URL_HOST))
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
