<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return response()->json([
        'app' => 'Food Trunk API',
        'status' => 'ok',
    ]);
});

/**
 * Sirve los archivos subidos (logos, portadas, fotos de platillos) a
 * través de Laravel en vez del enlace simbólico estático, para que el
 * middleware de CORS sí se aplique (necesario para Flutter Web, que sí
 * exige CORS al decodificar imágenes — los navegadores normales no lo
 * necesitan para una simple etiqueta <img>, pero Flutter Web sí).
 */
Route::get('/storage/{path}', function (string $path) {
    $disk = Storage::disk(config('filesystems.default'));

    abort_unless($disk->exists($path), 404);

    return response($disk->get($path))
        ->header('Content-Type', $disk->mimeType($path))
        ->header('Cache-Control', 'public, max-age=31536000');
})->where('path', '.*');
