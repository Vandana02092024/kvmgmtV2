<?php

namespace App\Services\SalesReport\Pdf;

use RuntimeException;

class SalesReportPdfExtractor
{
    private $runner;
    private $toolchain;
    private $tsvParser;

    public function __construct(
        SalesReportPdfProcess $runner,
        SalesReportPdfTools $toolchain,
        SalesReportOcrParser $tsvParser
    ) {
        $this->runner = $runner;
        $this->toolchain = $toolchain;
        $this->tsvParser = $tsvParser;
    }

    public function extract($pdfPath)
    {
        $this->toolchain->assertReady();
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            throw new RuntimeException('The uploaded PDF cannot be read.');
        }

        $timeout = (int) config('sales_report_pdf.command_timeout', 240);
        $info = $this->runner->run($this->toolchain->pdfinfo(), array($pdfPath), $timeout);
        $pageCount = $this->pageCount($info['stdout']);
        $maxPages = (int) config('sales_report_pdf.max_pages', 20);
        if ($pageCount < 1 || $pageCount > $maxPages) {
            throw new RuntimeException('PDF page count must be between 1 and ' . $maxPages . '.');
        }

        $temporaryDirectory = $this->createTemporaryDirectory();
        try {
            $prefix = $temporaryDirectory . DIRECTORY_SEPARATOR . 'page';
            $dpi = (int) config('sales_report_pdf.dpi', 300);
            $this->runner->run($this->toolchain->pdftoppm(), array(
                '-f', '1', '-l', (string) $pageCount, '-r', (string) $dpi,
                '-gray', '-png', $pdfPath, $prefix,
            ), $timeout);

            $images = glob($prefix . '-*.png');
            natsort($images);
            $images = array_values($images);
            if (count($images) !== $pageCount) {
                throw new RuntimeException('Poppler did not render every PDF page.');
            }

            $rows = array();
            $metricRows = array();
            $texts = array();
            foreach ($images as $index => $image) {
                $this->assertImageMemory($image);
                // Scans in a single upload can have different page orientations.
                $rotationDetected = false;
                try {
                    $rotation = $this->detectRotation($image, $timeout);
                    if ($rotation !== 0) {
                        $this->rotatePng($image, $rotation);
                    }
                    $rotationDetected = true;
                } catch (\Exception $exception) {
                    $rotationDetected = false;
                }
                $arguments = array(
                    $image, 'stdout', '--dpi', (string) $dpi, '--oem', '1',
                    '--psm', (string) ($rotationDetected
                        ? config('sales_report_pdf.page_segmentation_mode', 6) : 1),
                    '-l', (string) config('sales_report_pdf.language', 'eng'),
                );
                $tessdata = trim((string) config('sales_report_pdf.tessdata_path'));
                if ($tessdata !== '') {
                    $arguments[] = '--tessdata-dir';
                    $arguments[] = $tessdata;
                }
                $arguments[] = 'tsv';
                $result = $this->runner->run($this->toolchain->tesseract(), $arguments, $timeout);
                $page = $this->tsvParser->parse($result['stdout'], $index + 1);
                $rows = array_merge($rows, $page['rows']);
                $texts[] = $page['text'];
                if (preg_match('/(?:^|\n)Summary(?:\n|$)|Drop\s*Signups|New\s*Customers|Total\s*Claim\s*Checks/i', $page['text'])) {
                    $metricRows = array_merge($metricRows, $this->readCustomerCounts($image, $temporaryDirectory, $index + 1, $dpi, $timeout));
                }
            }

            return array(
                'raw_text' => implode("\n\f\n", $texts),
                'rows' => $rows,
                'metric_rows' => $metricRows,
                'page_count' => $pageCount,
                'dpi' => $dpi,
                'versions' => $this->toolchain->status()['versions'],
            );
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    private function pageCount($pdfInfo)
    {
        return preg_match('/^Pages:\s+(\d+)/mi', $pdfInfo, $matches) ? (int) $matches[1] : 0;
    }

    private function assertImageMemory($path)
    {
        $size = getimagesize($path);
        if (!$size || !$size[0] || !$size[1]) {
            throw new RuntimeException('Rendered sales PDF page is not a valid image.');
        }
        $limit = ini_parse_quantity((string) ini_get('memory_limit'));
        // Allow for the source, rotated GD surface and allocation overhead.
        // Reject before allocation, so a low-memory server gets a useful error
        // instead of a fatal error that bypasses cleanup and preview handling.
        $required = memory_get_usage(true) + $size[0] * $size[1] * 12 + 8 * 1024 * 1024;
        if ($limit > 0 && $required > $limit) {
            throw new RuntimeException('Increase PHP memory_limit to 512M for scanned sales PDFs. No sales data was saved.');
        }
    }

    private function readCustomerCounts($image, $directory, $pageNumber, $dpi, $timeout)
    {
        if (!function_exists('imagecrop')) return array();
        $source = imagecreatefrompng($image);
        if (!$source) return array();
        // Customer activity is a separate left-hand block, beside the monetary
        // Summary. Cropping prevents its labels being merged into amount rows.
        $crop = imagecrop($source, array('x' => 0, 'y' => 0,
            'width' => (int) (imagesx($source) * 0.23), 'height' => imagesy($source)));
        imagedestroy($source);
        if (!$crop) return array();
        $path = $directory . '/customer-counts-' . $pageNumber . '.png';
        imagepng($crop, $path);
        imagedestroy($crop);
        $arguments = array($path, 'stdout', '--dpi', (string) $dpi, '--oem', '1', '--psm', '6', '-l', config('sales_report_pdf.language', 'eng'));
        $tessdata = trim((string) config('sales_report_pdf.tessdata_path'));
        if ($tessdata !== '') $arguments = array_merge($arguments, array('--tessdata-dir', $tessdata));
        $arguments[] = 'tsv';
        $result = $this->runner->run($this->toolchain->tesseract(), $arguments, $timeout);
        $parsed = $this->tsvParser->parse($result['stdout'], $pageNumber);
        $signupY = $claimsY = null;
        foreach ($parsed['rows'] as $row) {
            if (preg_match('/Drop\s*Signups/i', $row['text'])) $signupY = $row['y'];
            if (preg_match('/Total\s*Claim\s*Checks/i', $row['text'])) $claimsY = $row['y'];
        }
        // Circles/handwriting can make block OCR omit the middle printed row.
        // Read that line in isolation; the reader still requires its customer
        // label and an actual integer, never a count inferred from neighbours.
        if ($signupY !== null && $claimsY !== null && $claimsY > $signupY) {
            $source = imagecreatefrompng($image);
            $lineHeight = (int) (($claimsY - $signupY) * 0.34);
            if ($lineHeight > 0 && $lineHeight < imagesy($source) * 0.1) {
                $line = imagecrop($source, array('x' => (int) (imagesx($source) * 0.016),
                    'y' => (int) (($signupY + $claimsY) / 2 - $lineHeight * 0.45),
                    'width' => (int) (imagesx($source) * 0.21), 'height' => $lineHeight));
                if ($line) {
                    $linePath = $directory . '/customer-line-' . $pageNumber . '.png';
                    imagepng($line, $linePath);
                    imagedestroy($line);
                    $lineArguments = $arguments;
                    $lineArguments[0] = $linePath;
                    $lineArguments[7] = '7';
                    $lineResult = $this->runner->run($this->toolchain->tesseract(), $lineArguments, $timeout);
                    $lineRows = $this->tsvParser->parse($lineResult['stdout'], $pageNumber, 0);
                    $parsed['rows'] = array_merge($parsed['rows'], $lineRows['rows']);
                }
            }
            imagedestroy($source);
        }
        return $parsed['rows'];
    }

    private function detectRotation($image, $timeout)
    {
        $arguments = array(
            $image, 'stdout', '--psm', '0', '-l', 'osd',
        );
        $tessdata = trim((string) config('sales_report_pdf.tessdata_path'));
        if ($tessdata !== '') {
            $arguments = array_merge($arguments, array('--tessdata-dir', $tessdata));
        }
        $result = $this->runner->run($this->toolchain->tesseract(), $arguments, $timeout);
        $output = $result['stdout'] . "\n" . $result['stderr'];
        if (!preg_match('/Rotate:\s*(0|90|180|270)/i', $output, $matches)) {
            throw new RuntimeException('Tesseract could not determine page orientation.');
        }
        return (int) $matches[1];
    }

    private function rotatePng($path, $clockwiseDegrees)
    {
        if (!function_exists('imagecreatefrompng') || !function_exists('imagerotate')) {
            throw new RuntimeException('PHP GD extension is required to rotate OCR pages.');
        }
        $source = imagecreatefrompng($path);
        if (!$source) {
            throw new RuntimeException('Rendered PDF page could not be opened for rotation.');
        }
        $rotated = imagerotate($source, 360 - $clockwiseDegrees, 0xFFFFFF);
        imagedestroy($source);
        if (!$rotated || !imagepng($rotated, $path)) {
            if ($rotated) {
                imagedestroy($rotated);
            }
            throw new RuntimeException('Rendered PDF page could not be rotated.');
        }
        imagedestroy($rotated);
    }

    private function createTemporaryDirectory()
    {
        $base = config('sales_report_pdf.temporary_directory');
        if (!is_dir($base) && !mkdir($base, 0755, true)) {
            throw new RuntimeException('Sales report PDF temporary directory could not be created.');
        }
        // The legacy root entry point makes storage reachable on some hosts.
        // Rendered report pages must not be directly downloadable during OCR.
        file_put_contents($base . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        file_put_contents($base . '/web.config', '<?xml version="1.0"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>');
        $directory = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . uniqid('ocr_', true);
        if (!mkdir($directory, 0755, true)) {
            throw new RuntimeException('Sales report PDF job directory could not be created.');
        }
        return $directory;
    }

    private function removeTemporaryDirectory($directory)
    {
        if (!$directory || !is_dir($directory)) {
            return;
        }
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($directory);
    }
}
