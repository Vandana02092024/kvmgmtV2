<?php

namespace App\Services\SalesReport\Pdf;

class SalesReportOcrParser
{
    public function parse($tsv, $pageNumber, $minimumConfidence = null)
    {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $tsv));
        if (count($lines) < 2) {
            return array('rows' => array(), 'text' => '', 'width' => 0, 'height' => 0);
        }

        $header = str_getcsv(array_shift($lines), "\t");
        $words = array();
        $pageWidth = 0;
        $pageHeight = 0;
        $minimumConfidence = $minimumConfidence === null ? (float) config('sales_report_pdf.minimum_confidence', 25) : (float) $minimumConfidence;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, "\t");
            if (count($values) < count($header)) {
                $values = array_pad($values, count($header), '');
            }
            $item = array_combine($header, array_slice($values, 0, count($header)));
            if (!$item) {
                continue;
            }

            if ((int) $item['level'] === 1) {
                $pageWidth = (int) $item['width'];
                $pageHeight = (int) $item['height'];
                continue;
            }
            if ((int) $item['level'] !== 5 || trim($item['text']) === '') {
                continue;
            }
            if ((float) $item['conf'] < $minimumConfidence && !$this->looksNumeric($item['text'])) {
                continue;
            }

            $words[] = array(
                'text' => trim($item['text']),
                'x' => (float) $item['left'],
                'y' => (float) $item['top'] + ((float) $item['height'] / 2),
                'width' => (float) $item['width'],
                'height' => (float) $item['height'],
                'confidence' => (float) $item['conf'],
            );
        }

        usort($words, function ($left, $right) {
            if (abs($left['y'] - $right['y']) > 2) {
                return $left['y'] < $right['y'] ? -1 : 1;
            }
            return $left['x'] < $right['x'] ? -1 : ($left['x'] > $right['x'] ? 1 : 0);
        });

        $rows = array();
        foreach ($words as $word) {
            $rowIndex = null;
            foreach ($rows as $index => $candidate) {
                if (abs($candidate['y'] - $word['y']) <= max(8, $word['height'] * 0.55)) {
                    $rowIndex = $index;
                    break;
                }
            }
            if ($rowIndex === null) {
                $rows[] = array(
                    'page' => (int) $pageNumber,
                    'page_width' => $pageWidth,
                    'page_height' => $pageHeight,
                    'y' => $word['y'],
                    'text' => '',
                    'words' => array($word),
                );
            } else {
                $count = count($rows[$rowIndex]['words']);
                $rows[$rowIndex]['words'][] = $word;
                $rows[$rowIndex]['y'] = (($rows[$rowIndex]['y'] * $count) + $word['y']) / ($count + 1);
            }
        }

        foreach ($rows as &$row) {
            usort($row['words'], function ($left, $right) {
                return $left['x'] < $right['x'] ? -1 : ($left['x'] > $right['x'] ? 1 : 0);
            });
            $texts = array();
            foreach ($row['words'] as $word) {
                $texts[] = $word['text'];
            }
            $row['text'] = trim(implode(' ', $texts));
        }
        unset($row);
        usort($rows, function ($left, $right) {
            return $left['y'] < $right['y'] ? -1 : ($left['y'] > $right['y'] ? 1 : 0);
        });

        return array(
            'rows' => $rows,
            'text' => implode("\n", array_map(function ($row) { return $row['text']; }, $rows)),
            'width' => $pageWidth,
            'height' => $pageHeight,
        );
    }

    private function looksNumeric($text)
    {
        return preg_match('/^[\(\$]?[-0-9][0-9,\.]*[\)]?$/', trim((string) $text)) === 1;
    }
}
