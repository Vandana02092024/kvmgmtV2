<?php

// Console-only diagnostics. This command never queries or writes business data.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../bootstrap/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$status = $app->make(App\Services\SalesReport\Pdf\SalesReportPdfTools::class)->status();
$status['php'] = PHP_VERSION;
$status['php_ini'] = php_ini_loaded_file();
$status['memory_limit'] = ini_get('memory_limit');
$status['upload_max_filesize'] = ini_get('upload_max_filesize');
$status['post_max_size'] = ini_get('post_max_size');
$status['poppler_path'] = config('sales_report_pdf.poppler_path');
$status['configuration_notes'] = array();
foreach (array('memory_limit' => '512M', 'upload_max_filesize' => '20M', 'post_max_size' => '24M') as $setting => $recommended) {
    $current = ini_parse_quantity((string) ini_get($setting));
    if ($current > 0 && $current < ini_parse_quantity($recommended)) {
        $status['configuration_notes'][] = 'Set ' . $setting . '=' . $recommended . ' in php.ini and restart Apache.';
    }
}
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
exit($status['ready'] ? 0 : 1);
