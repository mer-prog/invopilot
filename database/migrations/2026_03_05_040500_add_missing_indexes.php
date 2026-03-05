<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->index(['organization_id', 'issue_date']);
            $table->index(['organization_id', 'paid_at']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index('invoice_id');
        });

        Schema::table('recurring_invoices', function (Blueprint $table): void {
            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'next_issue_date']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex(['organization_id', 'issue_date']);
            $table->dropIndex(['organization_id', 'paid_at']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['invoice_id']);
        });

        Schema::table('recurring_invoices', function (Blueprint $table): void {
            $table->dropIndex(['organization_id', 'is_active']);
            $table->dropIndex(['organization_id', 'next_issue_date']);
        });
    }
};
