<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('managed_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name', 253)->unique();
            $table->string('zone_id', 64)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('managed_dns_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('managed_domains')->cascadeOnDelete();
            $table->string('cloudflare_id', 64)->nullable()->unique();
            $table->string('type', 16);
            $table->string('name', 253);
            $table->text('content');
            $table->unsignedInteger('ttl')->default(1);
            $table->boolean('proxied')->default(false);
            $table->timestamps();
            $table->index(['domain_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_dns_records');
        Schema::dropIfExists('managed_domains');
    }
};
