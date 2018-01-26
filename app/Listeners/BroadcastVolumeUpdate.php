<?php

namespace App\Listeners;

use App\Events\VolumeUpdateEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastVolumeUpdate
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
     * @param  VolumeUpdateEvent  $event
     * @return void
     */
    public function handle(VolumeUpdateEvent $event)
    {
        //
    }
}
