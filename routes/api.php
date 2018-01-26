<?php

use Illuminate\Http\Request;
use App\State;
use App\SpotifyToken;
use App\SpotifyClient;
use App\Events\PlayStateUpdateEvent;
use App\Events\QueueUpdateEvent;
use App\Events\TrackUpdateEvent;
use App\Song;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

  Route::get('/',function(Request $request) {

    $r = (object)[];
    $r->queue = Song::getQueue();
    $r->state = State::getInstance();

    return response()
            ->json( $r )
            ->withCallback($request->input('callback'));

  });

  Route::get('/browse/new-releases/',function(Request $request) {

    $client = SpotifyClient::getInstance();
    
    return response()
            ->json( $client->getNewReleases() )
            ->withCallback($request->input('callback'));

  });

  Route::get('/search/{searchterm}',function(Request $request, $searchterm) {

    $client = SpotifyClient::getInstance();
    $results = $client->search( $searchterm );

    return response()
            ->json( $results )
            ->withCallback($request->input('callback'));    
  });

  Route::get('/browse/album/{id}',function(Request $request, $id) {

    $client = SpotifyClient::getInstance();
    $album = $client->getAlbum( $id );

    return response()
            ->json( $album )
            ->withCallback($request->input('callback'));    

  });

  Route::get('/browse/artist/{id}',function(Request $request, $id) {

    $client = SpotifyClient::getInstance();
    $artist = $client->getArtist( $id );

    return response()
            ->json( $artist )
            ->withCallback($request->input('callback'));    
  });

  Route::get('/browse/playlist/{id}',function(Request $request, $id) {

    $client = SpotifyClient::getInstance();
    $playlist = $client->getPlaylist( $id );

    return response()
            ->json( $playlist )
            ->withCallback($request->input('callback'));    

  });  

  Route::get('/nowplaying',function(Request $request ) {

    $s = Song::first();
    $song = ( isset($s) ) ? Song::first()->getJSONObject() : (object)[];

    return response()
            ->json( $song )
            ->withCallback($request->input('callback'));    

  });

  Route::get('/queue', function( Request $request ) {

    return response()
            ->json( Song::getQueue() )
            ->withCallback($request->input('callback'));     
  });  

  Route::post('/pause',function(Request $request) {

    $state = State::getInstance();
    $state->setPlaying( !$state->playing );

    return response()
            ->json( $state )
            ->withCallback($request->input('callback'));    
  });

  Route::post('/volume',function(Request $request) {

    $state = State::getInstance();
    $state->setVolume( Input::get('volume') );

    return response()
            ->json( $state )
            ->withCallback($request->input('callback'));    

  });

  Route::post('/add/',function(Request $request) {

    $client = SpotifyClient::getInstance();
    $id = Input::get('id');

    // Remove any radio tracks in the queue.
    $count = Song::count();
    if ( $count != 0 && Song::where( 'radio' , true )->count() != 1 ) {
      Song::where( 'radio' , true )->orderBy('id','DESC')->offset(1)->limit( $count-1 )->delete();
    }

    // Get the next track.
    $tracks = $client->getTracksFromAny( $id );

    foreach ($tracks as $track ) {

      Song::create([
        'spotify_obj' => json_encode( $track ),
        'radio' => false
      ]);

    }

    event(new QueueUpdateEvent());

    return response()
            ->json( $tracks )
            ->withCallback($request->input('callback'));    

  });

  Route::post('/skip',function(Request $request ) {

    // If there's nothing currently playing, bail.
    if ( Song::count() == 0 ) {
      return response()->json( (object)[] );
    }

    // Get the song we're popping off the queue.
    $songToUnqueue = Song::first();
    $songToUnqueue->delete();

    // If this is the last track, let's find radio.
    if ( Song::count() == 0 ) {
      
      // Get the last track we played so we can use it get the radio.
      $songToUnqueueObject = json_decode( $songToUnqueue->spotify_obj );

      $client = SpotifyClient::getInstance();
      $tracks = $client->getRadio( $songToUnqueueObject->id );

      // Add all the tracks to our DB.
      foreach ($tracks as $track) {
        Song::create([
          'spotify_obj' => json_encode( $track ),
          'radio' => true
        ]);
      }
    }

    // Get our new next track.
    $newTrack = Song::first();
    
    // Broadcast event.
    event(new QueueUpdateEvent());
    event(new TrackUpdateEvent());

    $state = State::getInstance();
    $state->setPlaying( true );

    return response()
            ->json( $newTrack->getJSONObject() )
            ->withCallback($request->input('callback'));     

  });

});

Route::get('/authorize', function( Request $request ) {

	return redirect( SpotifyClient::getInstance()->getAuthURL() );

});

Route::get('/callback', function( Request $request ) {

  SpotifyClient::getInstance()->setAccessToken( $_GET['code'] );
	return "success";

});

Route::get('/refresh',function(Request $request) {

  return SpotifyClient::getInstance()->refreshAccessToken();

});
