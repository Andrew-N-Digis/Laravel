<?php

declare(strict_types=1);

namespace App\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PhotoUploadServiceLocal implements MediaUploadServiceInterface
{
    public function store(UploadedFile $file, string $dirName): string
    {
        $path = $file->store($dirName, 'public');

        return asset('storage/' . $path);
    }

    public function delete(string $name): void
    {
        Storage::delete([storage_path() . '/' . $name]);
    }
}