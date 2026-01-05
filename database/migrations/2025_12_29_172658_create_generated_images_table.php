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
        Schema::create('generated_images', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->text('content_source')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->default('image/png');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('social_post_id')->nullable()->constrained()->nullOnDelete();
            $table->json('api_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_images');
    }
};
