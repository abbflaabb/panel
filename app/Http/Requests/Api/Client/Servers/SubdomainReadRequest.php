<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers;

use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class SubdomainReadRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return 'subdomain.read';
    }
}
