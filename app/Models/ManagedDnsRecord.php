<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagedDnsRecord extends Model
{
    protected $table = 'managed_dns_records';

    protected $fillable = [
        'domain_id',
        'cloudflare_id',
        'type',
        'name',
        'content',
        'ttl',
        'proxied',
    ];

    protected $casts = [
        'ttl' => 'integer',
        'proxied' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(ManagedDomain::class, 'domain_id');
    }
}
