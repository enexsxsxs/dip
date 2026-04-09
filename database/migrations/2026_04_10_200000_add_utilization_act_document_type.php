<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('equipment_document_types')->insertOrIgnore([
            ['code' => 'utilization_act'],
        ]);
    }

    public function down(): void
    {
        $id = DB::table('equipment_document_types')->where('code', 'utilization_act')->value('id');
        if ($id !== null) {
            DB::table('equipment_documents')->where('document_type_id', $id)->delete();
            DB::table('equipment_document_types')->where('id', $id)->delete();
        }
    }
};
