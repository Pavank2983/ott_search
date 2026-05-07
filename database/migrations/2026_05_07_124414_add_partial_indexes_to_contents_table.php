<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE INDEX contents_search_published_idx
            ON contents
            USING GIN(search_vector)
            WHERE status = 'published'
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS contents_search_published_idx
        ");
    }
};