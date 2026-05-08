<?php

namespace App\Jobs;

use App\Models\Content;
use App\Services\ElasticSearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncContentToElasticJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $contentId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ElasticSearchService $elasticSearchService
    ): void {

        $content = Content::query()

            ->select([
                'id',
                'tenant_id',
                'title',
                'description',
                'content_type',
                'release_year',
                'imdb_rating',
            ])

            ->find($this->contentId);

        if (!$content) {
            return;
        }

        $elasticSearchService->index(
            document: [
                'title' => $content->title,
                'description' => $content->description,
                'tenant_id' => $content->tenant_id,
                'content_type' => $content->content_type,
                'release_year' => $content->release_year,
                'imdb_rating' => $content->imdb_rating,
            ],

            id: $content->id
        );
    }
}