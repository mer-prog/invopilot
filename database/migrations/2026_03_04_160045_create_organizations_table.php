<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('logo_url')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('default_currency', 3)->default('USD');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->integer('invoice_next_number')->default(1);
            $table->integer('default_payment_terms')->default(30);
            $table->text('default_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
