<?php

$profile = getenv('USERPROFILE');
$projectPoppler = storage_path('app/sales-pdf-tools/poppler-26.09.0/Library/bin');
$bundledPoppler = $profile ? $profile . '/.cache/codex-runtimes/codex-primary-runtime/dependencies/native/poppler/Library/bin' : '';
$windowsTesseract = 'C:/Program Files/Tesseract-OCR/tesseract.exe';

return array(
    'poppler_path' => env('SALES_REPORT_PDF_POPPLER_PATH', is_dir($projectPoppler) ? $projectPoppler : (is_dir($bundledPoppler) ? $bundledPoppler : '')),
    'pdftoppm_binary' => env('SALES_REPORT_PDF_PDFTOPPM_BINARY', 'pdftoppm'),
    'pdfinfo_binary' => env('SALES_REPORT_PDF_PDFINFO_BINARY', 'pdfinfo'),
    'tesseract_binary' => env('SALES_REPORT_PDF_TESSERACT_BINARY', is_file($windowsTesseract) ? $windowsTesseract : 'tesseract'),
    'tessdata_path' => env('SALES_REPORT_PDF_TESSDATA_PATH', ''),
    'language' => env('SALES_REPORT_PDF_LANGUAGE', 'eng'),
    'dpi' => (int) env('SALES_REPORT_PDF_DPI', 400),
    'page_segmentation_mode' => (int) env('SALES_REPORT_PDF_PSM', 6),
    'minimum_confidence' => (float) env('SALES_REPORT_PDF_MIN_CONFIDENCE', 25),
    'summary_width_ratio' => (float) env('SALES_REPORT_PDF_SUMMARY_WIDTH_RATIO', 0.68),
    'command_timeout' => (int) env('SALES_REPORT_PDF_COMMAND_TIMEOUT', 240),
    'max_pages' => (int) env('SALES_REPORT_PDF_MAX_PAGES', 20),
    'max_file_size_kb' => (int) env('SALES_REPORT_PDF_MAX_FILE_SIZE_KB', 20480),
    'temporary_directory' => storage_path('app/sales-report-pdf-temp'),
);
