<?php
// The character certificate as a PDF page: Dompdf (App\Services\CertificatePdf) reads this HTML and makes the file.
// The certificate is the same template as on screen (components/_certificate_sheet.php, 'pdf'); global.css gives the
// colour tokens, certificate-sheet.css the certificate's look, pdf.css the paper, the margins and the Poppins fonts.
// Dompdf reads the CSS from the disk (it may not use the internet), so the files are given by path, not by address.
// From CertificateController::pdf(): $certificate (prepared).
?>
<!doctype html>
<html lang="<?= e(country('language')) ?>">
<head>
  <meta charset="UTF-8" />
  <title><?= e("{$certificate['name']} — Character Certificate") ?></title>
  <link rel="stylesheet" href="global.css" />
  <link rel="stylesheet" href="components/certificate-sheet.css" />
  <link rel="stylesheet" href="pdf.css" />
</head>
<body>
  <?php
  $sheet    = $certificate;
  $sheetFor = 'pdf';
  require dirname(__DIR__) . '/components/_certificate_sheet.php';
  ?>
</body>
</html>
