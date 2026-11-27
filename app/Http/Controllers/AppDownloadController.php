<?php

namespace App\Http\Controllers;

use App\Models\AppRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppDownloadController extends Controller
{
    public function index()
    {
        $release = AppRelease::current();
        if ($release && !$release->fileAvailable()) {
            $release = null;
        }
        return response()->view('downloads.app', compact('release'))
            ->header('Cache-Control', 'no-store');
    }

    public function download(Request $request)
    {
        $release = AppRelease::current();
        abort_unless($release && $release->fileAvailable(), 404);
        $path = Storage::disk('local')->path($release->apk_path);
        $version = preg_replace('/[^a-zA-Z0-9._-]/', '-', $release->version);
        $response = response()->download($path, 'Uzima-Milele-'.$version.'.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
        // Count download requests, not installations or completed transfers.
        if (!$request->isMethod('HEAD') && !$request->headers->has('Range')) {
            $release->increment('downloads');
        }
        return $response;
    }
}
