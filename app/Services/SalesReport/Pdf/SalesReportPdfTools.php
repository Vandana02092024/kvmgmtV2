<?php

namespace App\Services\SalesReport\Pdf;

use RuntimeException;

class SalesReportPdfTools
{
    private $runner;
    private $status;

    public function __construct(SalesReportPdfProcess $runner)
    {
        $this->runner = $runner;
    }

    public function status()
    {
        if ($this->status !== null) {
            return $this->status;
        }

        $tools = array(
            'pdftoppm' => array($this->pdftoppm(), array('-v')),
            'pdfinfo' => array($this->pdfinfo(), array('-v')),
            'tesseract' => array($this->tesseract(), array('--version')),
        );
        $versions = array();
        $errors = array();

        if (!extension_loaded('gd') || !function_exists('imagecreatefrompng') ||
            !function_exists('imagerotate') || !function_exists('imagecrop')) {
            $errors[] = 'PHP GD extension is disabled. Enable extension=gd and restart Apache.';
        } else {
            $versions['gd'] = gd_info()['GD Version'];
        }

        foreach ($tools as $name => $tool) {
            try {
                $result = $this->runner->run($tool[0], $tool[1], 15);
                $text = trim($result['stdout'] . "\n" . $result['stderr']);
                $versions[$name] = $this->firstLine($text);
            } catch (\Exception $exception) {
                $errors[] = $name . ': ' . $exception->getMessage();
            }
        }

        if (isset($versions['tesseract'])) {
            try {
                $arguments = array('--list-langs');
                $tessdata = trim((string) config('sales_report_pdf.tessdata_path'));
                if ($tessdata !== '') {
                    $arguments = array_merge($arguments, array('--tessdata-dir', $tessdata));
                }
                $languages = $this->runner->run($this->tesseract(), $arguments, 20);
                $available = preg_split('/\r\n|\r|\n/', strtolower($languages['stdout']));
                $required = array(strtolower((string) config('sales_report_pdf.language', 'eng')), 'osd');
                foreach ($required as $language) {
                    if (!in_array($language, $available, true)) {
                        $errors[] = 'tesseract: required language data ' . $language . ' is missing.';
                    }
                }
            } catch (\Exception $exception) {
                $errors[] = 'tesseract languages: ' . $exception->getMessage();
            }
        }

        $this->status = array(
            'ready' => count($errors) === 0,
            'versions' => $versions,
            'errors' => $errors,
        );
        return $this->status;
    }

    public function assertReady()
    {
        $status = $this->status();
        if (!$status['ready']) {
            throw new RuntimeException('Sales report PDF toolchain is not ready. ' . implode(' ', $status['errors']));
        }
    }

    public function pdftoppm()
    {
        return $this->popplerBinary(config('sales_report_pdf.pdftoppm_binary'));
    }

    public function pdfinfo()
    {
        return $this->popplerBinary(config('sales_report_pdf.pdfinfo_binary'));
    }

    public function tesseract()
    {
        return (string) config('sales_report_pdf.tesseract_binary', 'tesseract');
    }

    private function popplerBinary($binary)
    {
        $directory = trim((string) config('sales_report_pdf.poppler_path'));
        if ($directory === '') {
            return (string) $binary;
        }
        $extension = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && substr($binary, -4) !== '.exe' ? '.exe' : '';
        return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $binary . $extension;
    }

    private function firstLine($text)
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        return count($lines) ? trim($lines[0]) : 'available';
    }
}
