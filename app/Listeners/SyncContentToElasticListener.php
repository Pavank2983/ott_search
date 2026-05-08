<?php

namespace App\Listeners;

use App\Events\ContentSynced;
use App\Jobs\SyncContentToElasticJob;

class SyncContentToElasticListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContentSynced $event): void
    {
        SyncContentToElasticJob::dispatch(
            $event->contentId
        );
    }
}