<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->string('name');
            $table->datetime('datetime')->nullable();
            $table->longText('short_summary')->nullable();
            $table->longText('long_summary')->nullable();
            $table->longText('notes')->nullable();
            $table->longText('transcription')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
