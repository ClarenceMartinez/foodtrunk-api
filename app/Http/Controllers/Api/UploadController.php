<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UploadController extends Controller
{
    /**
     * Carpetas permitidas, una por tipo de entidad. Esto evita que alguien
     * mande un "folder" arbitrario y además deja la estructura ordenada
     * para cuando migremos a S3 (cada tipo queda en su propio prefijo,
     * ej. s3://bucket/company-logos/, s3://bucket/menu-item-photos/).
     */
    private const ALLOWED_FOLDERS = [
        'company-logos',
        'food-truck-logos',
        'food-truck-covers',
        'menu-item-photos',
        'user-avatars',
    ];

    /**
     * POST /api/uploads
     * Sube una imagen y devuelve su URL pública. El frontend luego guarda
     * esa URL en el campo correspondiente (logo_url, cover_image_url,
     * image_url) usando los endpoints normales de actualización.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:4096'], // máx 4MB
            'folder' => ['required', Rule::in(self::ALLOWED_FOLDERS)],
        ]);

        $disk = config('filesystems.default'); // 'public' hoy, 's3' el día que migremos
        $folder = $request->string('folder');
        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $filename, $disk);

        return response()->json([
            'data' => [
                'url' => Storage::disk($disk)->url($path),
                'path' => $path,
            ],
        ], 201);
    }
}
