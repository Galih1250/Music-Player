<?php

use App\Http\Controllers\SongController;
use App\Models\Song;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Display the list of songs
Route::get('/', [SongController::class, 'index']);

// Store a new song
Route::post('/', [SongController::class, 'store'])->name('song.store');

// Delete a song
Route::delete('/songs/{id}', [SongController::class, 'delete'])->name('songs.delete');

// Edit a song (show the edit form)
Route::get('/songs/{song}/edit', [SongController::class, 'edit'])->name('songs.edit');

// Update a song
Route::put('/songs/{song}', [SongController::class, 'update'])->name('song.update');

Route::resource('songs', SongController::class);