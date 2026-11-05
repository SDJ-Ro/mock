<?php

declare(strict_types=1);

namespace Core;

use finfo;

// Saves uploaded files safely. The file's REAL content decides its type (not its name) and it gets a random new name.
//   image()     public pictures (PNG, JPG, WebP — SVG is refused because it can contain scripts): My Profile (your
//               photo), People (a new student's photo), Extracurricular (covers, galleries)
//   document()  private files in storage/uploads/ (outside public/: no web address reaches them), sent only by
//               send() after a controller has checked who is asking: Achievements (a proof, a student's evidence)
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

    // A private file in storage/uploads/{folder}. $types: the allowed types (the file's type => the word for it,
    // config/achievements.php 'proof_types'). $_FILES['proof'] → [its path inside storage/uploads, null] when saved,
    // [null, 'message'] when refused, [null, null] when no file was chosen
    public static function document(array $file, string $folder, array $types, int $maxKb): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
        {
            return [null, null];
        }

        // Too big for us, or for PHP itself (upload_max_filesize): the message names whichever limit is smaller
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $maxKb * 1024))
        {
            return [null, 'The file must be ' . round(min($maxKb * 1024, ini_bytes('upload_max_filesize')) / 1024 ** 2, 1) . ' MB or smaller.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name']))
        {
            return [null, 'The file could not be uploaded. Please try again.'];
        }

        // Look inside the file: a renamed .txt or .php "letter.pdf" is caught here (a picture must also open as one)
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!isset($types[$type]) || (str_starts_with($type, 'image/') && getimagesize($file['tmp_name']) === false))
        {
            return [null, 'Please choose a ' . word_list($types) . ' file.'];
        }

        $directory = self::storage($folder);

        if (!is_dir($directory))
        {
            mkdir($directory, 0755, true);
        }

        $name = bin2hex(random_bytes(12));   // random, no extension: nobody can guess, overwrite or run it

        if (!move_uploaded_file($file['tmp_name'], "{$directory}/{$name}"))
        {
            return [null, 'The file could not be saved. Please try again.'];
        }

        return ["{$folder}/{$name}", null];
    }

    // Is this private file (a path document() returned) still there?
    public static function exists(string $path): bool
    {
        return !str_contains($path, '..') && is_file(self::storage($path));
    }

    // Sends a private file to the browser, which shows it (a PDF or a picture) in a new tab, under the name it was
    // uploaded with. Only after the controller has checked who is asking, and exists().
    public static function send(string $path, string $name): never
    {
        $file = self::storage($path);

        header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($file));
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: inline; filename="' . str_replace(['"', '\\'], '', basename($name)) . '"');
        header('X-Content-Type-Options: nosniff');   // the browser must not guess another type (e.g. run it as a page)
        header('Cache-Control: private, no-store');
        readfile($file);
        exit;
    }

    // Deletes a file image() or document() saved: a picture by its web address ('/uploads/…', never the built-in
    // pictures in /assets), a private file by its path ('proofs/…'). Nothing happens for null.
    public static function forget(?string $url): void
    {
        if ($url === null || str_contains($url, '..'))
        {
            return;
        }

        if (str_starts_with($url, '/uploads/'))
        {
            $path = dirname(__DIR__) . '/public' . $url;
        }
        elseif (!str_starts_with($url, '/'))
        {
            $path = self::storage($url);
        }
        else
        {
            return;
        }

        if (is_file($path))
        {
            unlink($path);
        }
    }

    // Where private files live: storage/uploads/{path}
    private static function storage(string $path): string
    {
        return dirname(__DIR__) . '/storage/uploads/' . $path;
    }
}
