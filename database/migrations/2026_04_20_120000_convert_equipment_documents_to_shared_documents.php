<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_document_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_document_id')->constrained('equipment_documents')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->unique(['equipment_document_id', 'equipment_id'], 'eq_doc_equipment_unique');
        });

        if (Schema::hasColumn('equipment_documents', 'equipment_id')) {
            DB::table('equipment_documents')
                ->select(['id', 'equipment_id'])
                ->whereNotNull('equipment_id')
                ->orderBy('id')
                ->chunkById(500, function ($rows): void {
                    $inserts = [];
                    foreach ($rows as $row) {
                        $inserts[] = [
                            'equipment_document_id' => $row->id,
                            'equipment_id' => $row->equipment_id,
                        ];
                    }
                    if ($inserts !== []) {
                        DB::table('equipment_document_equipment')->insertOrIgnore($inserts);
                    }
                });

            Schema::table('equipment_documents', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('equipment_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('equipment_documents', 'equipment_id')) {
            Schema::table('equipment_documents', function (Blueprint $table): void {
                $table->foreignId('equipment_id')->nullable()->after('uploaded_at');
            });
        }

        if (Schema::hasTable('equipment_document_equipment')) {
            DB::table('equipment_document_equipment')
                ->select(['equipment_document_id', 'equipment_id'])
                ->orderBy('id')
                ->chunkById(500, function ($rows): void {
                    foreach ($rows as $row) {
                        DB::table('equipment_documents')
                            ->where('id', $row->equipment_document_id)
                            ->whereNull('equipment_id')
                            ->update(['equipment_id' => $row->equipment_id]);
                    }
                });

            Schema::dropIfExists('equipment_document_equipment');
        }

        Schema::table('equipment_documents', function (Blueprint $table): void {
            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
        });
    }
};
