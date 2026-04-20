<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Сохранение загрузок с исходными именами файлов (без hash от Laravel).
 * При совпадении имён в папке — «имя (1).ext», «имя (2).ext».
 */
final class OriginalFilenameStorage
{
    public static function storeOnPublicDisk(UploadedFile $file, string $directoryRelative, ?string $filenamePrefix = null): string
    {
        $disk = Storage::disk('public');
        $dir = trim(str_replace('\\', '/', $directoryRelative), '/');

        $original = basename($file->getClientOriginalName());
        $safe = self::sanitizeFilename($original);
        if ($safe === '' || $safe === '.' || $safe === '..') {
            $ext = $file->getClientOriginalExtension();
            $safe = 'файл.'.($ext !== '' ? $ext : 'bin');
        }

        $prefix = self::sanitizeFilename((string) ($filenamePrefix ?? ''));
        if ($prefix !== '') {
            $safe = $prefix.'_'.$safe;
        }

        $finalName = self::ensureUniqueFilename($disk, $dir, $safe);
        $disk->putFileAs($dir, $file, $finalName);

        return $dir.'/'.$finalName;
    }

    private static function sanitizeFilename(string $name): string
    {
        $name = basename(str_replace(["\0"], '', $name));
        $name = str_replace(['/', '\\'], '_', $name);
        $name = preg_replace('/[^\p{L}\p{N}\s._\-–—()«»№]/u', '_', $name) ?? '';
        $name = trim(preg_replace('/_+/u', '_', $name) ?? '', '._ ');

        return Str::limit($name, 180, '');
    }

    private static function ensureUniqueFilename(Filesystem $disk, string $dir, string $filename): string
    {
        if (! $disk->exists($dir.'/'.$filename)) {
            return $filename;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        for ($i = 1; $i < 1000; $i++) {
            $candidate = $base.' ('.$i.')'.($ext !== '' ? '.'.$ext : '');
            if (! $disk->exists($dir.'/'.$candidate)) {
                return $candidate;
            }
        }

        return $base.'_'.Str::random(8).($ext !== '' ? '.'.$ext : '');
    }
}
