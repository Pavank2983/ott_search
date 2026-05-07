<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add search_vector column
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE contents
            ADD COLUMN search_vector tsvector
        ");

        /*
        |--------------------------------------------------------------------------
        | Create GIN index
        |--------------------------------------------------------------------------
        */

        DB::statement("
            CREATE INDEX contents_search_vector_idx
            ON contents
            USING GIN(search_vector)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS contents_search_vector_idx
        ");

        DB::statement("
            ALTER TABLE contents
            DROP COLUMN IF EXISTS search_vector
        ");
    }
};