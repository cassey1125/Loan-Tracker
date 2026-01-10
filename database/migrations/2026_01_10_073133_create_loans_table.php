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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->date('due_date');
            $table->integer('payment_term'); // Assuming days for simplicity, or we can add a 'term_unit' column
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
