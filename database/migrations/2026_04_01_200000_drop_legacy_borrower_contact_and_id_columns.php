<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowers', function (Blueprint $table): void {
            $columnsToDrop = [];

            if (Schema::hasColumn('borrowers', 'phone')) {
                $columnsToDrop[] = 'phone';
            }

            if (Schema::hasColumn('borrowers', 'id_document_path')) {
                $columnsToDrop[] = 'id_document_path';
            }

            if (Schema::hasColumn('borrowers', 'id_document_original_name')) {
                $columnsToDrop[] = 'id_document_original_name';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table): void {
            if (!Schema::hasColumn('borrowers', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (!Schema::hasColumn('borrowers', 'id_document_path')) {
                $table->string('id_document_path')->nullable();
            }

            if (!Schema::hasColumn('borrowers', 'id_document_original_name')) {
                $table->string('id_document_original_name')->nullable();
            }
        });
    }
};
