<?php

namespace App\Http\Controllers;

use Inertia\Inertia; 
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = Song::query();

    // Check if the artist filter is present
    if ($request->has('artist') && !empty($request->artist)) {
        $query->where('artist', 'like', '%' . $request->artist . '%');
    }

    // Get the filtered songs
    $songs = $query->get();

    return Inertia::render('Dashboard', [
        'songs' => $songs,
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'src' => 'nullable|string',
            'cover' => 'nullable|string',
        ]);

        if ($request->src) {
            $exploded = explode(',', $request->src);
            $decoded = base64_decode($exploded[1]);
            $filename = 'audio/' . $request->title . '.mp3';
            $request->merge(['src' => $filename]);
            Storage::put('/public/' . $filename, $decoded);
        }

        if ($request->cover) {
            $exploded = explode(',', $request->cover);
            $decoded = base64_decode($exploded[1]);
            $filename = 'image/' . $request->title . '.jpg';
            $request->merge(['cover' => $filename]);
            Storage::put('/public/' . $filename, $decoded);
        }

        Song::create([
            'title' => $request->title,
            'artist' => $request->artist,
            'src' => $request->src,
            'cover' => $request->cover,
        ]);

        return response()->json(['message' => 'Song created successfully.'], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Song $song)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'src' => 'nullable|string',
            'cover' => 'nullable|string',
        ]);

        $song->title = $request->title;
        $song->artist = $request->artist;

        // Handle audio file update
        if ($request->src) {
            if ($song->src) {
                Storage::delete('public/' . $song->src);
            }

            $exploded = explode(',', $request->src);
            if (count($exploded) > 1) {
                $decoded = base64_decode($exploded[1]);
                $filename = 'audio/' . $request->title . '.mp3';
                Storage::put('/public/' . $filename, $decoded);
                $song->src = $filename;
            } else {
                return response()->json(['error' => 'Invalid audio data.'], 400);
            }
        }

        // Handle cover image update
        if ($request->cover) {
            if ($song->cover) {
                Storage::delete('public/' . $song->cover);
            }

            $exploded = explode(',', $request->cover);
            if (count($exploded) > 1) {
                $decoded = base64_decode($exploded[1]);
                $filename = 'image/' . $request->title . '.jpg';
                Storage::put('/public/' . $filename, $decoded);
            $song->cover = $filename;
            } else {
                return response()->json(['error' => 'Invalid cover image data.'], 400);
            }
        }

        $song->save();

        return response()->json(['message' => 'Song updated successfully.'], 200);
    }

    /**
     * Delete the specified resource from storage.
     */
    public function delete($id)
    {
        $song = Song::findOrFail($id);

        if ($song->src) {
            Storage::delete('public/' . $song->src);
        }

        if ($song->cover) {
            Storage::delete('public/' . $song->cover);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully.'], 200);
    }

    
}