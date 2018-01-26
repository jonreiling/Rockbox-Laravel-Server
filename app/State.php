<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\PlayStateUpdateEvent;
use App\Events\VolumeUpdateEvent;
use App\Events\ConnectionUpdateEvent;

class State extends Model
{
	protected $fillable = ['connected','playing','volume'];
	protected $hidden = ['id','created_at','updated_at'];
    //

    public static function getInstance()
    {
        return State::first();
    }

    public function setPlaying( $playing ) {

      $this->playing = $playing;
      $this->save();

      event(new PlayStateUpdateEvent($this));

    }

    public function setVolume( $volume ) {

      $this->volume = $volume;
      $this->save();

      event(new VolumeUpdateEvent($this));

    }

    public function setConnected( $connected ) {

      $this->connected = $connected;
      $this->save();

      event(new ConnectionUpdateEvent($this));

    }    

    protected $casts = [
        'playing' => 'boolean',
        'connected' => 'boolean',
    ];    

}
