<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerSubdomain extends Model
{
    protected $table = 'server_subdomains';

    protected $fillable = [
        'server_id',
        'allocation_id',
        'domain_id',
        'label',
        'fqdn',
        'address_record_id',
        'srv_record_id',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(ManagedDomain::class, 'domain_id');
    }
}
