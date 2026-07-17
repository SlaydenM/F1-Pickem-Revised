<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateImageController extends Controller
{
    public function show($year, $filename): BinaryFileResponse
    {
        $path = "private/driver_logos/{$year}/{$filename}";

        if (!Storage::exists($path)) {
            abort(404);
        }

        return Storage::response($path);

        // $path = Storage::disk('local')->path($relativePath);

        // return response()->file($path);
    }
}
