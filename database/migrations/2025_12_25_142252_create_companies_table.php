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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Company name
            $table->string('legal_form')->nullable(); // LLC, Inc., etc.
            $table->string('siret')->nullable();
            $table->string('vat_number')->nullable(); // Intra-community VAT number
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('France');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable(); // Path to logo file
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('legal_mentions')->nullable(); // Default legal mentions
            $table->string('quote_prefix')->default('D-'); // Quote prefix
            $table->integer('quote_counter')->default(1);
            $table->string('invoice_prefix')->default('F-'); // Invoice prefix
            $table->integer('invoice_counter')->default(1);
            $table->integer('default_payment_delay')->default(30); // Default payment delay (days)
            $table->decimal('default_vat_rate', 5, 2)->default(20.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
