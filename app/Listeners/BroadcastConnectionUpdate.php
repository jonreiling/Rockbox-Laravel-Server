<?php

namespace App\Listeners;

use App\Events\ConnectionUpdateEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastConnectionUpdate
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
     * @param  ConnectionUpdateEvent  $event
     * @return void
     */
    public function handle(ConnectionUpdateEvent $event)
    {
        //
    }
}
