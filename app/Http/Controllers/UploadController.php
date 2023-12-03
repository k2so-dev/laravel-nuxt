<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'entity' => ['required', 'string', 'in:avatars'],
            'width' => 'nullable|integer|min:1|max:1920',
            'height' => 'nullable|integer|min:1|max:1920',
        ]);

        $extension = 'webp';

        $path = $request->file('image')
            ->convert($request->width, $request->height, $extension)
            ->storeAs(
                $request->entity,
                implode('.', [Str::ulid()->toBase32(), $extension]),
                ['disk' => 'public']
            );

        TemporaryUpload::create([
            'path' => $path,
        ]);

        return response()->json([
            'ok' => true,
            'path' => $path,
        ]);
    }
}
