<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class AvatarService
{
    public function store(UploadedFile $file): string
    {
        try {
            // Decode one frame, correct orientation, crop, and re-encode the image.
            // The uploaded original and its filename are never made public.
            $manager = new ImageManager(Driver::class, decodeAnimation: false);
            $thumbnail = $manager->read($file->getRealPath())
                ->cover(256, 256)
                ->toJpeg(85);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'avatar' => 'This picture could not be prepared. Please try a different image.',
            ]);
        }

        $path = 'avatars/'.Str::uuid().'.jpg';

        try {
            Storage::disk('public')->put($path, (string) $thumbnail);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'avatar' => 'The profile picture could not be saved. Please try again.',
            ]);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'avatars/')) {
            return;
        }

        // A failed cleanup must not undo a successfully saved account.
        try {
            Storage::disk('public')->delete($path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
