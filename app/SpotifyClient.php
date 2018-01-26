<?php
namespace App;
use App\SpotifyToken;

class SpotifyClient
{
    const RETURN_ASSOC = 'assoc';
    const RETURN_OBJECT = 'object';

    protected $session = null;
    protected $api = null;
    protected static $instance = null;

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Constructor
     * Set up Request object.
     *
     * @param Request $request Optional. The Request object to use.
     */
    public function __construct($request = null)
    {
        
        $this->session = new \SpotifyWebAPI\Session(
    		'ee5bcc2c221c4a98814e88612c5e289a',
    		'8d5e5343c72141c78691c0e4dca3b0ff',
    		'http://rockbox.jonreiling.com/api/callback'
		);

        $this->api = new \SpotifyWebAPI\SpotifyWebAPI();

        // 
        $spotifyToken = SpotifyToken::find(1)->first();

        // Set the access token on the API wrapper
        $this->api->setAccessToken($spotifyToken->access_token);          

    }

    public function search( $term ) {

        $search = (object)[];
        $results = $this->api->search($term, ['artist','track','album'] , ['market'=>'US'] );
        //print_r($results->tracks->items);
        $search->tracks = $this->simplifyTracks( $results->tracks->items , null );
        $search->albums = $this->simplifyAlbums( $results->albums->items );
        $search->artists = $this->simplifyArtists( $results->artists->items );

        if ( count($search->albums) > 10 ) $search->albums = array_slice($search->albums,0,10);
        if ( count($search->artists) > 4 ) $search->artists = array_slice($search->artists,0,4);

        return $search;

    }

    public function getTracksFromAny( $id ) {

        if ( strpos( $id, 'track' ) !== false ) {

            $track = $this->getTrack( $id );
            return [ $track ];

        } else if ( strpos( $id, 'album' ) !== false ) {

            $album = $this->getAlbum( $id );
            return $album->tracks;

        } else if ( strpos( $id, 'playlist' ) !== false ) {

            $playlist = $this->getPlaylist( $id );
            return $playlist->tracks;

        }

        return [];
    }

    public function getTrack( $id ) {
        $results = $this->api->getTrack( $id );
        $track = $this->simplifyTrack( $results );
        return $track;
    }

    public function getArtist( $id ) {

        $resultsArtist = $this->api->getArtist( $id );
        $resultsAlbums = $this->api->getArtistAlbums( $id , [ "limit"=>50,"album_type"=>"album,ep","market"=>"US" ] );
        $resultsTracks = $this->api->getArtistTopTracks( $id , ["country"=>"US"] );

        $artist = $this->simplifyArtist( $resultsArtist );
        $artist->albums = $this->simplifyAlbums( $resultsAlbums->items );
        $artist->topTracks = $this->simplifyTracks( $resultsTracks->tracks );

        return $artist;

    }

    public function getAlbum( $id ) {

        $results = $this->api->getAlbum( $id );

        $album = $this->simplifyAlbum( $results );
        $album->tracks = $this->simplifyTracks( $results->tracks->items , $this->simplifyAlbum( $results ) );
        $album->release_date = $results->release_date;

        return $album;
    }

    public function getPlaylist( $id ) {

        $idParts = explode( ':', $id );

        $results = $this->api->getUserPlaylist( $idParts[ 2 ], $idParts[ 4 ] );
        $playlist = (object)[];

        $playlist->tracks = $this->simplifyTracks( $results->tracks->items );

        return $playlist;
    }


    public function getNewReleases() {

        $results = $this->api->getNewReleases( ["country" => "US"] );

        $albums = $this->simplifyAlbums( $results->albums->items );

        return $albums;
    }

    public function getRadio( $seed ) {

        $seed = explode(":", $seed)[2];

        $results = $this->api->getRecommendations( [ "seed_tracks" => [ $seed ] , "limit" => 50 , "market" => "US" ] );
        
        $tracks = $this->simplifyTracks( $results->tracks );

        return $tracks;
    }

	public function getAuthURL() {
		
		$options = [
	    	'scope' => [
	        	'playlist-read-private',
	        	'user-read-private',
		    ],
		];
	
		return $this->session->getAuthorizeUrl($options);
	}

    public function setAccessToken($code) {

        // Request a access token using the code from Spotify
        $this->session->requestAccessToken($code);

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        $spotifyToken = SpotifyToken::find(1)->first();
        $spotifyToken->access_token = $accessToken;
        $spotifyToken->refresh_token = $refreshToken;
        $spotifyToken->save();

        // Set the access token on the API wrapper
        $this->api->setAccessToken($accessToken);        

    }

    public function refreshAccessToken() {

        $spotifyToken = SpotifyToken::find(1)->first();

        // Fetch the refresh token from somewhere. A database for example.
        $this->session->refreshAccessToken( $spotifyToken->refresh_token );

        $accessToken = $this->session->getAccessToken();

        $spotifyToken->access_token = $accessToken;
        $spotifyToken->save();

        // Set our new access token on the API wrapper and continue to use the API as usual
        $this->api->setAccessToken($accessToken);        
    }

    protected function simplifyArtists( $items ) {

        $artists = array();

        for ( $i = 0 ; $i < count( $items ) ; $i++ ) {
            $artists[] = $this->simplifyArtist( $items[ $i ] );
        }

        return $artists;


    }

    protected function simplifyArtist( $artist ) {

        $simplifiedArtist = (object)[];
        $simplifiedArtist->name = $artist->name;
        $simplifiedArtist->id = $artist->uri;
        if (isset($artist->images[0])) $simplifiedArtist->image = $artist->images[0]->url;

        return $simplifiedArtist;
    }

    protected function simplifyAlbums( $items ) {

        $albums = array();
        $lastAlbum = "";

        for ( $i = 0 ; $i < count( $items ) ; $i++ ) {

            $album = $this->simplifyAlbum( $items[ $i ] );

            if ( $album->name != $lastAlbum ) {

                $albums[] = $album;
            }

            $lastAlbum = $album->name;

        }

        return $albums;


    }

    protected function simplifyAlbum( $album ) {

        $simplifiedAlbum = (object)[];
        $simplifiedAlbum->name = $album->name;
        $simplifiedAlbum->id = $album->uri;
        $simplifiedAlbum->image = $album->images[0]->url;

        $simplifiedAlbum->artist = (object)[];
        $simplifiedAlbum->artist->name = $album->artists[0]->name;
        $simplifiedAlbum->artist->id = $album->artists[0]->uri;

        return $simplifiedAlbum;
    }

    protected function simplifyTracks( $items , $album = null ) {

        $tracks = array();

        for ( $i = 0 ; $i < count( $items ) ; $i++ ) {
            $track = $this->simplifyTrack( $items[ $i ] , $album );
            $tracks[] = $track;
        }

        return $tracks;
    }

    protected function simplifyTrack( $track, $album = null ) {

        // Required for playlist tracks.
        if ( isset( $track->added_at ) ) $track = $track->track;

        $simplifiedTrack = (object)[];
        $simplifiedTrack->name = $track->name;
        $simplifiedTrack->id = $track->uri;
        $simplifiedTrack->explicit = $track->explicit;

        $simplifiedTrack->artist = (object)[];
        $simplifiedTrack->artist->name = $track->artists[0]->name;
        $simplifiedTrack->artist->id = $track->artists[0]->uri;

        if ( isset($album) ) {

            $simplifiedTrack->album = $album;

        } else {

            $simplifiedTrack->album = (object)[];
            $simplifiedTrack->album->name = $track->album->name;
            $simplifiedTrack->album->id = $track->album->uri;
            $simplifiedTrack->album->image = $track->album->images[0]->url;
        }

        return $simplifiedTrack;

    }

}
