<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Song;

class SongsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
    Song::create([
        'title' => 'Nautilus',
        'artist'=> 'Yorushika',
        'src' => '/audio/nautilus.mp3',
        'cover' => '/image/elma.rgb.jpg'
    ]);

    Song::create([
        'title' => 'Haru',
        'artist'=> 'Yorushika',
        'src' => '/audio/haru.mp3',
        'cover' => '/image/haru.rgb.jpg'
    ]);
    }
}
