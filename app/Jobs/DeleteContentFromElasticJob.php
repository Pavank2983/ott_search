<?php

namespace App\Jobs;

use App\Services\ElasticSearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteContentFromElasticJob implements ShouldQueue
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

        $elasticSearchService->delete(
            $this->contentId
        );
    }
}