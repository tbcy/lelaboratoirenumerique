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
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->json('images')->nullable(); // Array of image paths
            $table->json('connection_ids'); // Array of social_connection ids to post to
            $table->enum('status', ['draft', 'approved', 'scheduled', 'published', 'failed', 'rejected'])->default('draft');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('published_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
