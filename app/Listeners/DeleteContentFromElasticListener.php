<?php

namespace App\Listeners;

use App\Events\ContentDeleted;
use App\Jobs\DeleteContentFromElasticJob;

class DeleteContentFromElasticListener
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
    public function handle(ContentDeleted $event): void
    {
        DeleteContentFromElasticJob::dispatch(
            $event->contentId
        );
    }
}