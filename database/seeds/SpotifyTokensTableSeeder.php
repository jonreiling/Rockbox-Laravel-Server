<?php

use Illuminate\Database\Seeder;

class SpotifyTokensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('spotify_tokens')->insert([
          'access_token' => '0',
          'refresh_token' => '0'
        ]);
    }
}
