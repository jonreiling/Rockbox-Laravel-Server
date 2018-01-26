<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    //
    protected $fillable = ['spotify_obj','radio'];
    protected $hidden = ['id','created_at','updated_at'];

    public static function getQueue()
    {

      $songs = Song::all();
//      $songs = Song::where( 'radio' , false )->get();
      // If there's not a regular queue, check to see if there's something in the radio queue.
//      if ( count($songs) == 0 ) $songs = Song::where( 'radio' , true )->limit( 1 )->get();

      $i = 0;
      $queue = array();
      foreach ($songs as $song) {

        if ( $i == 0 || !$song->radio ) {

          $queue[] = $song->getJSONObject();
        }

        $i++;
      }

      return $queue;
    }  

    public function getJSONObject() {

      $obj = json_decode( $this->spotify_obj );
      $obj->radio = (bool)$this->radio;
      return $obj;

    }  

    protected $casts = [
        'radio' => 'boolean',
    ];      
}
