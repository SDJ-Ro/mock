<?php
// The "Export PDF" button: opens the browser's print box for a printable page the server draws, so the
// person can print it or "Save as PDF". export-pdf.js loads that page out of sight and prints it. Its look:
// export-pdf-button.css. Used by the Digital Record Book; later the People pages. Set first:
//   $exportUrl the printable page's address, e.g. '/student/academic/print?grade=6&term=1'
//              (a script may change data-url when the page changes what it shows)
?>
<button type="button" class="c-export-btn j-export-pdf" data-url="<?= e($exportUrl) ?>">
  <?= icon('download', 14) ?>
  <span class="j-export-pdf-text">Export PDF</span>
</button>
<?php unset($exportUrl); ?>
