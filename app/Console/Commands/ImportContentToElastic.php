<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Services\ElasticSearchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('search:import-content')]
#[Description('Import OTT contents into Elasticsearch')]
class ImportContentToElastic extends Command
{
    public function __construct(
        private ElasticSearchService $elasticSearchService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Elasticsearch import...');

        $this->elasticSearchService->createIndex();

        Content::query()
            ->chunkById(1000, function ($contents) {

                $documents = [];

                foreach ($contents as $content) {

                    $documents[] = [
                        'index' => [
                            '_index' => 'ott_contents',
                            '_id' => $content->id,
                        ]
                    ];

                    $documents[] = [
                        'title' => $content->title,
                        'description' => $content->description,
                        'tenant_id' => $content->tenant_id,
                        'content_type' => $content->content_type,
                        'release_year' => $content->release_year,
                        'imdb_rating' => $content->imdb_rating,
                    ];
                }

                $this->elasticSearchService->bulkIndex($documents);

                $this->info('Imported batch of ' . count($contents) . ' contents');
            });

        $this->info('Elasticsearch import completed successfully.');
    }
}