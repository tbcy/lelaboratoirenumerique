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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');                    // create, update, delete
            $table->string('resource_type');             // client, invoice, quote, etc.
            $table->unsignedBigInteger('resource_id');
            $table->json('changes');                     // old/new values
            $table->string('api_key_identifier');        // SHA-256 hash of API key
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
