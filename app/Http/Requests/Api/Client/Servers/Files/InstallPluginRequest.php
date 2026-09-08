<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Files;

use Closure;
use Pterodactyl\Models\Permission;
use Illuminate\Validation\Validator;
use Pterodactyl\Contracts\Http\ClientPermissionsRequest;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class InstallPluginRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url:https',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                    $allowed = $host === 'api.spiget.org'
                        || $host === 'cdn.modrinth.com'
                        || str_ends_with($host, '.modrinth.com');

                    if (!$allowed) {
                        $fail('Plugin downloads are only allowed from approved plugin providers.');
                    }
                },
            ],
            'filename' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9._-]+\.jar$/i'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $url = (string) $this->input('url');
            $ip = gethostbyname((string) parse_url($url, PHP_URL_HOST));

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                $validator->errors()->add('url', 'The plugin download host is not publicly routable.');
            }
        });
    }
}
