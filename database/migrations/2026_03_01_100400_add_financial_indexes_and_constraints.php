<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['loan_id', 'payment_date']);
            $table->index(['deleted_at']);
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->index(['date', 'type']);
            $table->index(['deleted_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['created_at']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index(['status', 'due_date']);
            $table->index(['deleted_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_interest_rate_check CHECK (interest_rate IN (5, 7, 10))");
            DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_payment_term_check CHECK (payment_term > 0)");
            DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_remaining_balance_check CHECK (remaining_balance >= 0)");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount > 0)");
            DB::statement("ALTER TABLE funds ADD CONSTRAINT funds_amount_check CHECK (amount > 0)");
            DB::statement("ALTER TABLE funds ADD CONSTRAINT funds_type_check CHECK (type IN ('deposit', 'withdrawal'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_interest_rate_check');
            DB::statement('ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_payment_term_check');
            DB::statement('ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_remaining_balance_check');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_amount_check');
            DB::statement('ALTER TABLE funds DROP CONSTRAINT IF EXISTS funds_amount_check');
            DB::statement('ALTER TABLE funds DROP CONSTRAINT IF EXISTS funds_type_check');
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['loan_id', 'payment_date']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->dropIndex(['date', 'type']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
