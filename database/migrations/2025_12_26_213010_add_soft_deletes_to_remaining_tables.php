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
        // Add soft deletes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to catalog tables
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('catalog_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to time_entries table
        Schema::table('time_entries', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to social_connections table
        Schema::table('social_connections', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to quote_lines and invoice_lines
        Schema::table('quote_lines', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('catalog_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('social_connections', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('quote_lines', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
