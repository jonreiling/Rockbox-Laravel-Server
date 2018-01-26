<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpotifyToken extends Model
{
    //
    protected $fillable = ['access_token','refresh_token'];

}
