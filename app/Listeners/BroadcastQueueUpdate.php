<?php

namespace App\Listeners;

use App\Events\QueueUpdateEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastQueueUpdate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueueUpdateEvent  $event
     * @return void
     */
    public function handle(QueueUpdateEvent $event)
    {
        //
    }
}
