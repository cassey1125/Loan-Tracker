<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motor_rentals', function (Blueprint $table) {
            $table->unsignedInteger('rental_days')->default(1)->after('rental_date');
            $table->date('rental_end_date')->nullable()->after('rental_days');
        });

        DB::statement('UPDATE motor_rentals SET rental_days = 1, rental_end_date = rental_date');
    }

    public function down(): void
    {
        Schema::table('motor_rentals', function (Blueprint $table) {
            $table->dropColumn(['rental_days', 'rental_end_date']);
        });
    }
};
