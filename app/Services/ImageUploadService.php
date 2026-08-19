<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function upload(
        UploadedFile $file,
        string $folder,
        int $maxKb = 512,
        string $mimes = 'jpg,jpeg,png,webp',
        ?string $oldPath = null,
    ): ?string {
        if (!$file->isValid()) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = $folder . '/' . Str::random(20) . '.' . $ext;

        $dir = dirname($filename);
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir, 0755, true);
        }

        try {
            $file->storeAs($dir, basename($filename), 'public');
        } catch (\Throwable $e) {
            return null;
        }

        if ($oldPath && !preg_match('#^https?://#i', $oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $filename;
    }

    public function uploadMultiple(
        array $files,
        string $folder,
        int $maxKb = 512,
        string $mimes = 'jpg,jpeg,png,webp',
    ): array {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $this->upload($file, $folder, $maxKb, $mimes);
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        return $paths;
    }

    public function delete(string $path): bool
    {
        if ($path && !preg_match('#^https?://#i', $path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
}
