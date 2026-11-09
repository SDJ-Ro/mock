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

    // Files that are not pictures (a notice's attachment): the type decided by the real content, like the pictures
    private const DOCUMENT_TYPES = [
        'application/pdf'    => 'pdf',
        'application/msword' => 'doc',
        'image/png'          => 'png',
        'image/jpeg'         => 'jpg',
    ];

    private const DOCX_TYPE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    // The type each saved extension is sent as when someone downloads the file
    public const DOCUMENT_DOWNLOAD_TYPES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => self::DOCX_TYPE,
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
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

    // A PDF, Word or PNG / JPG file saved in storage/uploads/{folder} — NOT in public/: nobody can open it by its
    // address, only through a page that checks who is asking (NoticeController::attachment). The real content decides
    // the type (a renamed .exe or .php is refused), the saved name is random and the original name is kept for display.
    // $_FILES['attachment'] → [saved name, original name, null] when saved, [null, null, 'message'] when refused,
    // [null, null, null] when no file was chosen
    public static function document(array $file, string $folder, int $maxKb): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
        {
            return [null, null, null];
        }

        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $maxKb * 1024))
        {
            return [null, null, 'The file must be ' . round(min($maxKb * 1024, ini_bytes('upload_max_filesize')) / 1024 ** 2, 1) . ' MB or smaller.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']))
        {
            return [null, null, 'The file could not be uploaded. Please try again.'];
        }

        $type      = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extension = self::DOCUMENT_TYPES[$type] ?? self::wordDocument($file['tmp_name'], $type);

        // A picture must really be one (a PNG / JPG header alone is not enough)
        if ($extension === null || (in_array($extension, ['png', 'jpg'], true) && getimagesize($file['tmp_name']) === false))
        {
            return [null, null, 'Please choose a PDF, Word (DOC, DOCX), JPG or PNG file.'];
        }

        $directory = dirname(__DIR__) . "/storage/uploads/{$folder}";

        if (!is_dir($directory))
        {
            mkdir($directory, 0755, true);
        }

        $name = bin2hex(random_bytes(12)) . ".{$extension}";

        if (!move_uploaded_file($file['tmp_name'], "{$directory}/{$name}"))
        {
            return [null, null, 'The file could not be saved. Please try again.'];
        }

        // The name the person sees: no folder marks or control characters, at most 255 letters (it is escaped where shown)
        $shown = trim(preg_replace('/[\x00-\x1F\x7F\\\\\/]+/u', '_', (string) ($file['name'] ?? '')) ?? '');

        return [$name, mb_substr($shown !== '' ? $shown : "attachment.{$extension}", 0, 255), null];
    }

    // The folder a document saved by document() lives in → its full path, or null for a name document() never made
    // (anything else — "../x", an address — never reaches the disk)
    public static function documentPath(?string $name, string $folder): ?string
    {
        if ($name === null || !preg_match('/^[a-f0-9]{24}\.(pdf|docx?|png|jpg)$/', $name))
        {
            return null;
        }

        $path = dirname(__DIR__) . "/storage/uploads/{$folder}/{$name}";

        return is_file($path) ? $path : null;
    }

    // Deletes a document document() saved (nothing happens for null or a name it never made)
    public static function forgetDocument(?string $name, string $folder): void
    {
        $path = self::documentPath($name, $folder);

        if ($path !== null)
        {
            unlink($path);
        }
    }

    // A .docx is a zip that holds word/document.xml: the zip's file list is plain text at the start of each entry, so
    // looking for those two names needs no zip extension (the file is never opened or run here, only given back as a
    // download) → 'docx', or null
    private static function wordDocument(string $path, string $type): ?string
    {
        if ($type === self::DOCX_TYPE)
        {
            return 'docx';
        }

        if (($type === 'application/zip' || $type === 'application/octet-stream') && filesize($path) <= 10 * 1024 ** 2)
        {
            $content = (string) file_get_contents($path);

            return str_starts_with($content, "PK\x03\x04") && str_contains($content, 'word/document.xml') && str_contains($content, '[Content_Types].xml') ? 'docx' : null;
        }

        return null;
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
