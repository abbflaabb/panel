<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('server_subdomains', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('server_id');
            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->cascadeOnDelete();

            $table->unsignedInteger('allocation_id');
            $table->foreign('allocation_id')
                ->references('id')
                ->on('allocations')
                ->cascadeOnDelete();

            $table->foreignId('domain_id')
                ->constrained('managed_domains')
                ->cascadeOnDelete();

            $table->string('label', 63);
            $table->string('fqdn', 253)->unique();
            $table->string('address_record_id', 64)->nullable();
            $table->string('srv_record_id', 64)->nullable();

            $table->timestamps();

            $table->unique(['server_id', 'label', 'domain_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_subdomains');
    }
};
