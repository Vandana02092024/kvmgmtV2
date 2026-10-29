<?php

namespace App\Services\SalesReport;

use App\Services\SalesReport\Pdf\SalesReportPdfExtractor;
use App\Services\SalesReport\Pdf\SalesReportPdfParser;
use RuntimeException;

/** Adapts the proven sales report reader to the existing daily sales ledger. */
class SalesReportPdfReader
{
    private $extractor;
    private $parser;

    public function __construct(SalesReportPdfExtractor $extractor, SalesReportPdfParser $parser)
    {
        $this->extractor = $extractor;
        $this->parser = $parser;
    }

    public function read($path)
    {
        $extraction = $this->extractor->extract($path);
        return $this->parse($extraction);
    }

    public function parse(array $extraction)
    {
        $report = $this->parser->parse($extraction);
        if (count($report['errors'])) {
            throw new RuntimeException(implode(' ', $report['errors']));
        }
        // The reference parser reads the first From date. Never apply a period
        // total, or a second daily report, to that first day by accident.
        preg_match_all('/\b(From|To)\s*:?\s*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i', $extraction['raw_text'], $dates, PREG_SET_ORDER);
        $from = $to = array();
        foreach ($dates as $match) {
            $date = $this->date($match[2]);
            if (strtolower($match[1]) === 'from') {
                $from[] = $date;
            } else {
                $to[] = $date;
            }
        }
        if (count($from) !== 1 || count($to) !== 1 || !$from[0] || $from[0] !== $to[0] || $from[0] !== $report['report_date']) {
            throw new RuntimeException('Upload one daily report with matching From and To dates. Multi-day totals cannot be saved as a single day.');
        }

        $employee = $this->employeeTotals($extraction['rows']);
        // A digit repaired by reconciliation must also agree with an independent
        // printed Total column; reconciliation alone is not evidence of a digit.
        foreach ($report['summary'] as $item) {
            $key = $this->key($item['name']);
            $employeeKey = $key === 'loyaltydiscounts' ? 'rewards' : ($key === 'referralcredit' ? 'referralcredits' : $key);
            if (isset($employee[$employeeKey]) && abs($employee[$employeeKey]['amount'] - $item['amount']) > 0.005) {
                throw new RuntimeException('Summary and employee Total disagree for ' . $item['name'] . '. No data was saved.');
            }
            if (!empty($item['ocr_corrected']) && !isset($employee[$employeeKey])) {
                throw new RuntimeException('An uncertain OCR value for ' . $item['name'] . ' could not be independently verified.');
            }
        }
        $report['employee_totals'] = array_values($employee);
        $report['page_count'] = $extraction['page_count'];
        $report['warnings'] = array();
        $report['daily_metrics'] = $this->dailyMetrics(array_merge($extraction['rows'], isset($extraction['metric_rows']) ? $extraction['metric_rows'] : array()));
        // A missing tender is not a zero and is never inferred as a residual.
        foreach (array('Cash', 'Credit Card') as $tender) {
            $found = false;
            foreach ($employee as $item) {
                if (in_array($this->key($item['name']), $tender === 'Cash' ? array('cash', 'cashsales', 'netcash') : array('creditcard', 'creditcards', 'creditcardsales'), true)) {
                    $found = true;
                }
            }
            if (!$found) {
                $report['warnings'][] = $tender . ' receipt amount is not readable in this PDF. That receipt will remain unchanged; enter it manually from the original report.';
            }
        }
        return $report;
    }

    private function employeeTotals(array $rows)
    {
        $active = false;
        $totalX = null;
        $totals = array();
        foreach ($rows as $row) {
            if (strpos($this->key($row['text']), 'salessummarybyemployee') !== false) {
                $active = true;
                continue;
            }
            if (!$active) {
                continue;
            }
            if ($totalX === null) {
                foreach ($row['words'] as $word) {
                    if ($this->key($word['text']) === 'total') {
                        $totalX = $word['x'];
                        break;
                    }
                }
                continue;
            }
            $label = array();
            $number = null;
            foreach ($row['words'] as $word) {
                if ($word['x'] < $totalX - $row['page_width'] * 0.025) {
                    $label[] = $word['text'];
                } elseif (abs($word['x'] - $totalX) < $row['page_width'] * 0.035) {
                    $value = $this->money($word['text']);
                    if ($value !== null) {
                        if ($number !== null) {
                            throw new RuntimeException('Ambiguous employee Total column.');
                        }
                        $number = $value;
                    }
                }
            }
            $name = trim(implode(' ', $label));
            // Counts, including "# of Credit Card Sales", are not money.
            if ($number === null || $name === '' || strpos($name, '#') !== false || preg_match('/^of\b/i', $name)) {
                continue;
            }
            $key = $this->key($name);
            if (isset($totals[$key])) {
                throw new RuntimeException('Duplicate employee Total row: ' . $name);
            }
            $totals[$key] = array('name' => $name, 'amount' => $number);
        }
        return $totals;
    }

    public static function activityFields()
    {
        return array('ez_drop_signups' => 'EZ Drop Signups', 'new_customers' => 'New Customers');
    }

    private function dailyMetrics(array $rows)
    {
        $patterns = array('ez_drop_signups' => '/\b(?:EZ\s*)?Drop\s*Signups?\s*:?\s*(\d+)\b(?![.,]\d)/i',
            // The circled printed label can acquire one extra edge character.
            'new_customers' => '/\b(?:[a-z]?New|Jw)\s*Customers?[a-z]?\s*:?\s*(\d+)\b(?![.,]\d)/i');
        $values = array('ez_drop_signups' => null, 'new_customers' => null);
        foreach ($rows as $row) {
            $countText = preg_replace('/["\x{2018}\x{2019}\x{201c}\x{201d}\/|]/u', ' ', $row['text']);
            foreach ($patterns as $field => $pattern) {
                if (preg_match($pattern, $countText, $match)) {
                    $confident = !isset($row['words']);
                    foreach (isset($row['words']) ? $row['words'] : array() as $word) {
                        if ($word['text'] === $match[1] && (!isset($word['confidence']) || $word['confidence'] >= 50)) $confident = true;
                    }
                    if (!$confident) continue;
                    if ((float) $match[1] > 2147483647) {
                        throw new RuntimeException('Customer count is outside the supported range.');
                    }
                    $value = (int) $match[1];
                    if ($values[$field] !== null && $values[$field] !== $value) {
                        throw new RuntimeException('Conflicting PDF values for ' . self::activityFields()[$field] . '.');
                    }
                    $values[$field] = $value;
                }
            }
        }
        return $values;
    }

    private function money($raw)
    {
        $negative = strpos($raw, '(') !== false || strpos($raw, '-') !== false;
        $value = str_replace(array('$', '(', ')', '-'), '', $raw);
        if (preg_match('/^\d+,\d{2}$/', $value)) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
        // Only monetary values, not integer counts or handwritten annotations.
        if (!preg_match('/^\d+\.\d{2}$/', $value)) {
            return null;
        }
        return ($negative ? -1 : 1) * (float) $value;
    }

    private function date($raw)
    {
        foreach (array('!m/d/Y', '!m-d-Y', '!m/d/y', '!m-d-y') as $format) {
            $date = \DateTime::createFromFormat($format, $raw);
            $errors = \DateTime::getLastErrors();
            if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }

    private function key($text)
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($text));
    }
}
