<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSearchVectors extends Command
{
    /**
     * Command signature.
     */
    protected $signature = 'search:generate-vectors';

    /**
     * Command description.
     */
    protected $description = 'Generate PostgreSQL full-text search vectors';

    /**
     * Execute command.
     */
    public function handle(): void
    {
        $this->info('Generating search vectors...');

        DB::statement("
            UPDATE contents
            SET search_vector =
                setweight(
                    to_tsvector(
                        'english',
                        coalesce(title, '')
                    ),
                    'A'
                )
                ||
                setweight(
                    to_tsvector(
                        'english',
                        coalesce(description, '')
                    ),
                    'B'
                )
                ||
                setweight(
                    to_tsvector(
                        'english',
                        coalesce(language, '')
                    ),
                    'C'
                )
                ||
                setweight(
                    to_tsvector(
                        'english',
                        coalesce(search_text, '')
                    ),
                    'B'
                )
        ");

        $this->info('Search vectors generated successfully.');
    }
}