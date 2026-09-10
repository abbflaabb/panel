<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagedDomain extends Model
{
    protected $table = 'managed_domains';

    protected $fillable = ['name', 'zone_id', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function records(): HasMany
    {
        return $this->hasMany(ManagedDnsRecord::class, 'domain_id');
    }
}
