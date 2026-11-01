<?php

declare(strict_types=1);

namespace Core;

use finfo;

// Saves uploaded pictures safely. The file's REAL content decides its type (not its
// name), it gets a random new name, and only PNG, JPG and WebP are accepted — SVG is refused because it can contain
// scripts. Used by My Profile (your photo), People (a new student's photo) and Extracurricular (covers, galleries).
final class Upload
{
    private const IMAGE_TYPES = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    // Public images in public/uploads/{folder}
    // $_FILES['photo'] → [web address, null] when saved, [null, 'message'] when refused, [null, null] when no file was chosen
    public static function image(array $file, string $folder, int $maxKb): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
        {
            return [null, null];
        }

        // Too big for us, or for PHP itself (upload_max_filesize): the message names whichever limit is smaller
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $maxKb * 1024))
        {
            return [null, 'The photo must be ' . round(min($maxKb * 1024, ini_bytes('upload_max_filesize')) / 1024 ** 2, 1) . ' MB or smaller.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']))
        {
            return [null, 'The file could not be uploaded. Please try again.'];
        }

        // Look inside the file: a renamed .txt or .php "photo.png" is caught here
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!isset(self::IMAGE_TYPES[$type]) || getimagesize($file['tmp_name']) === false)
        {
            return [null, 'Please choose a PNG, JPG or WebP image.'];
        }

        $directory = dirname(__DIR__) . "/public/uploads/{$folder}";

        if (!is_dir($directory))
        {
            mkdir($directory, 0755, true);
        }

        $name = bin2hex(random_bytes(12)) . '.' . self::IMAGE_TYPES[$type];   // random: nobody can guess or overwrite it

        if (!move_uploaded_file($file['tmp_name'], "{$directory}/{$name}"))
        {
            return [null, 'The photo could not be saved. Please try again.'];
        }

        return ["/uploads/{$folder}/{$name}", null];
    }

    // The files of a "choose several" box (<input type="file" name="gallery[]" multiple>) one by one, each shaped like
    // a single $_FILES entry for image(); boxes left empty are skipped. Anything else sent → no files.
    public static function each(array $files): array
    {
        if (!is_array($files['name'] ?? null) || !is_array($files['error'] ?? null))
        {
            return [];
        }

        $each = [];

        foreach (array_keys($files['name']) as $i)
        {
            $file = array_map(fn ($values) => is_array($values) ? ($values[$i] ?? null) : null, $files);

            if (is_int($file['error']) && is_string($file['tmp_name']) && $file['error'] !== UPLOAD_ERR_NO_FILE)
            {
                $each[] = $file;
            }
        }

        return $each;
    }

    // Deletes a picture image() saved, by its web address — never anything outside public/uploads (the built-in
    // pictures in /assets stay). Nothing happens for null.
    public static function forget(?string $url): void
    {
        $path = dirname(__DIR__) . '/public' . $url;

        if ($url !== null && str_starts_with($url, '/uploads/') && !str_contains($url, '..') && is_file($path))
        {
            unlink($path);
        }
    }
}
