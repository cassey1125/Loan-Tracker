<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_reports', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('loan_principal_total', 15, 2)->default(0);
            $table->decimal('loan_interest_total', 15, 2)->default(0);
            $table->decimal('payments_total', 15, 2)->default(0);
            $table->decimal('fund_deposits_total', 15, 2)->default(0);
            $table->decimal('fund_withdrawals_total', 15, 2)->default(0);
            $table->decimal('calculated_fund_net', 15, 2)->default(0);
            $table->integer('mismatch_count')->default(0);
            $table->json('mismatches')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_reports');
    }
};
