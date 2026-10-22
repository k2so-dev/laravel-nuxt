<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Imsus\LaravelImgproxy\Exceptions\ImgproxyStorageException;
use Imsus\LaravelImgproxy\Imgproxy;

class UploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('uploads.max_size')],
            'entity' => ['required', 'string', 'in:avatars'],
        ]);

        $file = $request->file('image');
        $disk = config('uploads.disk');
        $name = Str::uuid7();

        if (!config('uploads.optimize')) {
            $path = $file->storeAs($request->entity, $name.'.'.$file->extension(), ['disk' => $disk]);
        } else {
            // imgproxy reads the original from the shared storage volume (storage/app)
            $tmp = $file->storeAs('uploads-tmp', $name.'.'.$file->extension(), ['disk' => 'local']);

            try {
                $path = Imgproxy::image('local:///private/'.$tmp, 'internal')
                    ->applyPreset('optimize')
                    ->toStorage($disk, $request->entity.'/'.$name.'.'.config('laravel-imgproxy.presets.optimize.format'))
                    ->path();
            } catch (ImgproxyStorageException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'image' => __('The image could not be processed.'),
                ]);
            } finally {
                Storage::disk('local')->delete($tmp);
            }
        }

        TemporaryUpload::create([
            'path' => $path,
        ]);

        return response()->json([
            'ok' => true,
            'path' => $path,
        ]);
    }
}
