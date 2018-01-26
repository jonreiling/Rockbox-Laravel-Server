<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PlayStateUpdateEvent' => [
            'App\Listeners\BroadcastPlayStateUpdate',
        ],
        'App\Events\VolumeUpdateEvent' => [
            'App\Listeners\BroadcastVolumeUpdate',
        ],
        'App\Events\QueueUpdateEvent' => [
            'App\Listeners\BroadcastQueueUpdate',
        ],        
        'App\Events\ConnectionUpdateEvent' => [
            'App\Listeners\BroadcastConnectionUpdate',
        ],       
        'App\Events\TrackUpdateEvent' => [
        ],            
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
