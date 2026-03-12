<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('id_document_path')->nullable()->after('identification_number');
            $table->string('id_document_original_name')->nullable()->after('id_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['id_document_path', 'id_document_original_name']);
        });
    }
};