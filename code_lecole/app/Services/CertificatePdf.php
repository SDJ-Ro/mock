<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

// Turns a printable page (HTML) into a PDF file with Dompdf (the library: composer.json). Used for the character
// certificate (CertificateController::pdf()). Dompdf may only read files in public/assets (our CSS and the Poppins
// fonts), never the internet, and keeps its font measurements in storage/cache/dompdf.
class CertificatePdf
{
    public static function make(string $html): string
    {
        $root  = dirname(__DIR__, 2);
        $cache = "{$root}/storage/cache/dompdf";

        if (!is_dir($cache))
        {
            mkdir($cache, 0775, true);
        }

        $options = new Options();
        $options->setChroot("{$root}/public/assets");
        $options->setFontDir($cache);
        $options->setFontCache($cache);
        $options->setIsRemoteEnabled(false);
        $options->setDefaultPaperSize('A4');

        $pdf = new Dompdf($options);
        $pdf->setBasePath("{$root}/public/assets/css");
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }
}
