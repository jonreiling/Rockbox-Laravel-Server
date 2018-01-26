<?php

use Illuminate\Database\Seeder;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('states')->insert([
        	'connected' => false,
        	'playing' => false,
        	'volume' => 30
        ]);
    }
}
