<?php

namespace App\Listeners;

use App\Events\PlayStateUpdateEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastPlayStateUpdate
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
     * @param  PlayStateUpdateEvent  $event
     * @return void
     */
    public function handle(PlayStateUpdateEvent $event)
    {
        //
    }
}
