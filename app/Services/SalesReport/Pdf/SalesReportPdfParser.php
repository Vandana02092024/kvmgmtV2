<?php

namespace App\Services\SalesReport\Pdf;

class SalesReportPdfParser
{
    public function parse(array $extraction)
    {
        $rows = isset($extraction['rows']) && is_array($extraction['rows']) ? $extraction['rows'] : array();
        $rawText = isset($extraction['raw_text']) ? (string) $extraction['raw_text'] : '';
        $retail = $this->readRetailSection($rows);
        $summary = $this->readSummarySection($rows);
        $errors = array_merge($retail['errors'], $summary['errors']);

        if (count($retail['categories']) && count($summary['items'])) {
            $categoryAmount = $this->sum($retail['categories'], 'total_amount');
            $categoryUnits = $this->sum($retail['categories'], 'total_units');
            $gross = $this->grossSummaryAmount($summary['items']);
            $net = $this->netSummaryAmount($summary['items']);

            if ($gross === null || abs($categoryAmount - $gross) > 0.02) {
                $errors[] = 'Dynamic category amounts do not match Summary gross sales.';
            }
            if ($retail['gross_amount'] !== null && $gross !== null && abs($retail['gross_amount'] - $gross) > 0.02) {
                $errors[] = 'Retail gross sales do not match Summary gross sales.';
            }
            if ($retail['gross_units'] !== null && abs($categoryUnits - $retail['gross_units']) > 0.01) {
                $errors[] = 'Dynamic category units do not match Retail gross units.';
            }
            if ($net === null || !$this->summaryReconciles($summary['items'], $net)) {
                $errors[] = 'Dynamic Summary amounts do not reconcile to net sales.';
            }
        }

        $reportDate = $this->reportDate($rawText);
        if ($reportDate === null) {
            $errors[] = 'Report From date could not be read.';
        }

        return array(
            'report_date' => $reportDate,
            'raw_text' => $rawText,
            'categories' => $retail['categories'],
            'summary' => $summary['items'],
            'errors' => array_values(array_unique($errors)),
        );
    }

    private function readRetailSection(array $rows)
    {
        $start = null;
        $columns = array('invoice' => null, 'units' => null, 'amount' => null);
        foreach ($rows as $index => $row) {
            if ($this->isRetailHeader($row)) {
                $start = $index + 1;
                $columns = $this->columnPositions($row);
                break;
            }
        }
        if ($start === null) {
            return array('categories' => array(), 'gross_units' => null, 'gross_amount' => null,
                'errors' => array('Dynamic retail category table header could not be read.'));
        }

        $categories = array();
        $errors = array();
        $block = array();
        $grossUnits = null;
        $grossAmount = null;
        $foundGross = false;

        for ($index = $start; $index < count($rows); $index++) {
            $row = $rows[$index];
            $label = $this->compact($row['text']);
            if ($this->isGrossSalesRow($row)) {
                $gross = $this->totalFromRow($row, $columns);
                if ($gross !== null) {
                    $grossUnits = $gross['total_units'];
                    $grossAmount = $gross['total_amount'];
                }
                $foundGross = true;
                break;
            }
            if ($this->isSummaryHeading($row) || strpos($label, 'discounts') === 0) {
                break;
            }

            if ($this->isTotalRow($row)) {
                $name = $this->categoryName($block, $columns);
                $total = $this->totalFromRow($row, $columns);
                if ($name === null) {
                    $errors[] = 'A category heading before a Total row could not be read.';
                } elseif ($total === null) {
                    $errors[] = 'Units and amount for dynamic category ' . $name . ' could not be read.';
                } else {
                    $total['name'] = $name;
                    $total['display_order'] = count($categories);
                    $categories[] = $total;
                }
                $block = array();
                continue;
            }
            $block[] = $row;
        }

        if (!$foundGross) {
            $errors[] = 'Retail gross sales row could not be read.';
        }
        if (!count($categories)) {
            $errors[] = 'No dynamic category totals could be read.';
        }

        $seen = array();
        foreach ($categories as $category) {
            $key = $this->compact($category['name']);
            if (isset($seen[$key])) {
                $errors[] = 'Duplicate dynamic category ' . $category['name'] . ' was found.';
            }
            $seen[$key] = true;
        }

        return array('categories' => $categories, 'gross_units' => $grossUnits,
            'gross_amount' => $grossAmount, 'errors' => $errors);
    }

    private function readSummarySection(array $rows)
    {
        $start = null;
        $summaryPage = null;
        foreach ($rows as $index => $row) {
            if ($this->isSummaryHeading($row)) {
                $start = $index + 1;
                $summaryPage = isset($row['page']) ? (int) $row['page'] : null;
                break;
            }
        }
        if ($start === null) {
            $boundary = $this->summaryBoundaryStart($rows);
            if ($boundary !== null) {
                $start = $boundary['index'];
                $summaryPage = $boundary['page'];
            }
        }
        if ($start === null) {
            return array('items' => array(), 'errors' => array('Dynamic Summary block could not be located.'));
        }

        $section = array();
        for ($index = $start; $index < count($rows); $index++) {
            $row = $rows[$index];
            if ($summaryPage !== null && isset($row['page']) && (int) $row['page'] !== $summaryPage) {
                break;
            }
            $label = $this->compact($row['text']);
            if (strpos($label, 'salessummaryby') !== false || strpos($label, 'ordersummary') !== false) {
                break;
            }
            $section[] = $row;
        }

        $columns = $this->summaryColumns($section);
        if ($columns === null) {
            return array('items' => array(), 'errors' => array('Dynamic Summary columns could not be read.'));
        }

        $items = array();
        $errors = array();
        foreach ($section as $row) {
            $item = $this->summaryItem($row, $section, $columns);
            if ($item !== null) {
                $item['display_order'] = count($items);
                $items[] = $item;
                if ($this->isNetSalesLabel($item['name'])) {
                    break;
                }
            }
        }

        $items = $this->reconcileSummaryItems($items);

        if (count($items) < 2) {
            $errors[] = 'Dynamic Summary did not contain enough amount rows.';
        }
        $seen = array();
        foreach ($items as $item) {
            $key = $this->compact($item['name']);
            if (isset($seen[$key])) {
                $errors[] = 'Duplicate dynamic Summary label ' . $item['name'] . ' was found.';
            }
            $seen[$key] = true;
        }

        return array('items' => $items, 'errors' => $errors);
    }

    private function summaryBoundaryStart(array $rows)
    {
        foreach ($rows as $netIndex => $netRow) {
            if (!$this->isNetSalesLabel($netRow['text'])) {
                continue;
            }
            $page = isset($netRow['page']) ? (int) $netRow['page'] : null;
            for ($index = $netIndex - 1; $index >= 0; $index--) {
                $row = $rows[$index];
                if ($page !== null && isset($row['page']) && (int) $row['page'] !== $page) {
                    break;
                }
                if ($this->isGrossSalesRow($row) && count($this->numbers($row))) {
                    return array('index' => $index, 'page' => $page);
                }
            }
        }
        return null;
    }

    private function summaryColumns(array $rows)
    {
        foreach ($rows as $row) {
            if (!$this->approximatelyContains($this->compact($row['text']), 'grosssales', 2)) {
                continue;
            }
            $numbers = $this->numbers($row);
            if (!count($numbers)) {
                continue;
            }
            $amount = $numbers[count($numbers) - 1];
            $letterWords = array_values(array_filter($row['words'], function ($word) use ($amount) {
                return (float) $word['x'] < $amount['x'] && $this->containsLetters($word['text']);
            }));
            if (!count($letterWords)) {
                continue;
            }
            return array(
                'label_x' => (float) $letterWords[0]['x'],
                'amount_x' => (float) $amount['x'],
                'page_width' => isset($row['page_width']) ? (float) $row['page_width'] : 0,
            );
        }
        return null;
    }

    private function isRetailHeader(array $row)
    {
        $label = $this->compact($row['text']);
        return $this->approximatelyContains($label, 'category', 2) &&
            $this->approximatelyContains($label, 'unit', 1) &&
            $this->approximatelyContains($label, 'amount', 2);
    }

    private function columnPositions(array $row)
    {
        $positions = array('invoice' => null, 'units' => null, 'amount' => null);
        foreach ($row['words'] as $word) {
            $label = $this->compact($word['text']);
            foreach (array('invoice', 'units', 'amount') as $column) {
                if ($positions[$column] === null &&
                    ($label === $column || levenshtein($label, $column) <= 1)) {
                    $positions[$column] = (float) $word['x'];
                }
            }
        }
        return $positions;
    }

    private function categoryName(array $block, array $columns)
    {
        foreach ($block as $row) {
            $leftWords = array();
            foreach ($row['words'] as $word) {
                if ($columns['invoice'] === null || (float) $word['x'] < $columns['invoice'] - 15) {
                    $leftWords[] = $word;
                }
            }
            $candidate = $this->cleanLabel(implode(' ', array_map(function ($word) {
                return $word['text'];
            }, $leftWords)));
            if ($candidate === '' || count($this->numbers($row)) || !$this->containsLetters($candidate)) {
                continue;
            }
            $compact = $this->compact($candidate);
            if ($compact === 'category' || strpos($compact, 'retailsales') !== false || $compact === 'total') {
                continue;
            }
            return $candidate;
        }
        return null;
    }

    private function totalFromRow(array $row, array $columns)
    {
        $numbers = $this->numbers($row);
        if (count($numbers) < 2) {
            return null;
        }
        $unit = $columns['units'] === null ? $numbers[max(0, count($numbers) - 3)]
            : $this->nearestNumber($numbers, $columns['units'], 180);
        $amount = $columns['amount'] === null ? $numbers[count($numbers) - 1]
            : $this->nearestNumber($numbers, $columns['amount'], 250);
        if ($unit === null || $amount === null || $unit['x'] === $amount['x']) {
            return null;
        }
        return array('total_units' => $unit['value'], 'total_amount' => abs($amount['value']));
    }

    private function summaryItem(array $row, array $section, array $columns)
    {
        $pageWidth = $columns['page_width'] > 0 ? $columns['page_width'] : 1000;
        $labelTolerance = max(20, $pageWidth * 0.018);
        $amountTolerance = max(80, $pageWidth * 0.12);
        $words = array_values(array_filter($row['words'], function ($word) use ($columns, $labelTolerance) {
            return (float) $word['x'] >= $columns['label_x'] - $labelTolerance &&
                (float) $word['x'] < $columns['amount_x'];
        }));
        if (!count($words)) {
            return null;
        }

        $label = $this->summaryLabel($words);
        if ($label === '') {
            return null;
        }

        $amount = $this->summaryAmountNearRow($row, $section, $columns['amount_x'], $amountTolerance);
        if ($amount === null) {
            return null;
        }

        return array(
            'name' => $label,
            'raw_value' => $amount['raw'],
            'value' => null,
            'amount' => $amount['value'],
        );
    }

    private function summaryLabel(array $words)
    {
        while (count($words)) {
            $last = $words[count($words) - 1];
            $text = trim((string) $last['text']);
            $confidence = isset($last['confidence']) ? (float) $last['confidence'] : 100;
            if (!$this->containsLetters($text) || (strlen($this->compact($text)) <= 2 && $confidence < 75)) {
                array_pop($words);
                continue;
            }
            break;
        }
        $label = $this->cleanLabel(implode(' ', array_map(function ($word) {
            return $word['text'];
        }, $words)));
        return $this->containsLetters($label) ? $label : '';
    }

    private function summaryAmountNearRow(array $labelRow, array $section, $amountX, $tolerance)
    {
        $best = null;
        $bestScore = INF;
        foreach ($section as $candidateRow) {
            $yGap = abs((float) $candidateRow['y'] - (float) $labelRow['y']);
            if ($yGap > 18) {
                continue;
            }
            foreach ($this->numbers($candidateRow) as $number) {
                $xGap = abs($number['x'] - $amountX);
                if ($xGap > $tolerance) {
                    continue;
                }
                $score = ($yGap * 10) + $xGap;
                if ($score < $bestScore) {
                    $best = $number;
                    $bestScore = $score;
                }
            }
        }
        return $best;
    }

    private function reconcileSummaryItems(array $items)
    {
        $grossIndex = null;
        $netIndex = null;
        foreach ($items as $index => $item) {
            if ($grossIndex === null && $this->approximatelyContains($this->compact($item['name']), 'grosssales', 2)) {
                $grossIndex = $index;
            }
            if ($this->isNetSalesLabel($item['name'])) {
                $netIndex = $index;
            }
        }
        if ($grossIndex === null || $netIndex === null) {
            return $items;
        }

        $candidateIndexes = array();
        foreach ($items as $index => $item) {
            if ($index === $grossIndex || $index === $netIndex || $item['amount'] >= -9.999) {
                continue;
            }
            $alternative = $this->dropLeadingIntegerDigit($item['amount']);
            if ($alternative !== null && abs($alternative - $item['amount']) > 0.001) {
                $candidateIndexes[$index] = $alternative;
            }
        }

        $indexes = array_keys($candidateIndexes);
        if (count($indexes) > 12) {
            return $items;
        }
        $best = null;
        $bestChanges = INF;
        $combinations = 1 << count($indexes);
        for ($mask = 0; $mask < $combinations; $mask++) {
            $allAdjustments = 0;
            $deductionsOnly = 0;
            $changes = 0;
            foreach ($items as $index => $item) {
                if ($index === $netIndex) {
                    continue;
                }
                $amount = $item['amount'];
                $position = array_search($index, $indexes, true);
                if ($position !== false && ($mask & (1 << $position))) {
                    $amount = $candidateIndexes[$index];
                    $changes++;
                }
                $allAdjustments += $amount;
                if ($index === $grossIndex || $amount < 0) {
                    $deductionsOnly += $amount;
                }
            }
            $matches = abs($allAdjustments - $items[$netIndex]['amount']) <= 0.02 ||
                abs($deductionsOnly - $items[$netIndex]['amount']) <= 0.02;
            if ($matches && $changes < $bestChanges) {
                $best = $mask;
                $bestChanges = $changes;
            }
        }
        if ($best === null) {
            return $items;
        }
        foreach ($indexes as $position => $index) {
            if ($best & (1 << $position)) {
                $items[$index]['ocr_corrected'] = true;
                $items[$index]['amount'] = $candidateIndexes[$index];
            }
        }
        return $items;
    }

    private function dropLeadingIntegerDigit($amount)
    {
        $negative = $amount < 0;
        $formatted = number_format(abs($amount), 2, '.', '');
        list($integer, $decimal) = explode('.', $formatted);
        if (strlen($integer) < 2) {
            return null;
        }
        $integer = substr($integer, 1);
        $value = (float) (($integer === '' ? '0' : $integer) . '.' . $decimal);
        return $negative ? -$value : $value;
    }

    private function numbers(array $row)
    {
        $numbers = array();
        foreach ($row['words'] as $word) {
            $parsed = $this->parseNumber($word['text']);
            if ($parsed !== null) {
                $parsed['x'] = (float) $word['x'];
                $numbers[] = $parsed;
            }
        }
        return $numbers;
    }

    private function parseNumber($raw)
    {
        $raw = trim((string) $raw);
        $negative = strpos($raw, '(') !== false && strpos($raw, ')') !== false;
        $clean = str_replace(array('$', '%', '(', ')', ' '), '', $raw);
        if (strpos($clean, '.') === false && preg_match('/^-?[0-9]+,[0-9]{2}$/', $clean)) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }
        if (!preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $clean)) {
            return null;
        }
        $value = (float) $clean;
        if ($negative) {
            $value = -abs($value);
        }
        return array('raw' => $raw, 'value' => $value);
    }

    private function nearestNumber(array $numbers, $x, $maxDistance)
    {
        $best = null;
        $distance = $maxDistance;
        foreach ($numbers as $number) {
            $gap = abs($number['x'] - $x);
            if ($gap < $distance) {
                $best = $number;
                $distance = $gap;
            }
        }
        return $best;
    }

    private function isTotalRow(array $row)
    {
        return preg_match('/^(?:total|totat|tota1)\b/i', trim($row['text'])) === 1;
    }

    private function isGrossSalesRow(array $row)
    {
        return $this->approximatelyContains($this->compact($row['text']), 'grosssales', 2);
    }

    private function isSummaryHeading(array $row)
    {
        if (count($this->numbers($row))) {
            return false;
        }
        $label = $this->compact($row['text']);
        return $label === 'summary' || levenshtein($label, 'summary') <= 2;
    }

    private function grossSummaryAmount(array $items)
    {
        foreach ($items as $item) {
            if ($this->approximatelyContains($this->compact($item['name']), 'grosssales', 2)) {
                return $item['amount'];
            }
        }
        return count($items) ? $items[0]['amount'] : null;
    }

    private function netSummaryAmount(array $items)
    {
        foreach ($items as $item) {
            if ($this->isNetSalesLabel($item['name'])) {
                return $item['amount'];
            }
        }
        return count($items) ? $items[count($items) - 1]['amount'] : null;
    }

    private function isNetSalesLabel($label)
    {
        return $this->approximatelyContains($this->compact($label), 'netsales', 2);
    }

    private function summaryReconciles(array $items, $net)
    {
        $allAdjustments = 0;
        $deductionsOnly = 0;
        $grossFound = false;
        foreach ($items as $item) {
            if ($this->isNetSalesLabel($item['name'])) {
                continue;
            }
            $allAdjustments += $item['amount'];
            if ($this->approximatelyContains($this->compact($item['name']), 'grosssales', 2)) {
                $deductionsOnly += $item['amount'];
                $grossFound = true;
            } elseif ($item['amount'] < 0) {
                $deductionsOnly += $item['amount'];
            }
        }
        return abs($allAdjustments - $net) <= 0.02 ||
            ($grossFound && abs($deductionsOnly - $net) <= 0.02);
    }

    private function reportDate($text)
    {
        if (!preg_match('/\bFrom\s*:?\s*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})/i', $text, $matches)) {
            return null;
        }
        foreach (array('m/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y', 'm/d/y', 'n/j/y') as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $matches[1]);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }

    private function sum(array $rows, $field)
    {
        $total = 0;
        foreach ($rows as $row) {
            $total += $row[$field];
        }
        return $total;
    }

    private function containsLetters($value)
    {
        return preg_match('/[a-z]{2}/i', $value) === 1;
    }

    private function cleanLabel($value)
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        return trim($value, " \t\n\r\0\x0B:;-|_");
    }

    private function compact($value)
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $value));
    }

    private function approximatelyContains($haystack, $needle, $distance)
    {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
        $length = strlen($needle);
        if (strlen($haystack) < max(1, $length - $distance)) {
            return false;
        }
        for ($index = 0; $index <= strlen($haystack) - max(1, $length - $distance); $index++) {
            foreach (range(max(1, $length - $distance), min(strlen($haystack) - $index, $length + $distance)) as $size) {
                if (levenshtein(substr($haystack, $index, $size), $needle) <= $distance) {
                    return true;
                }
            }
        }
        return false;
    }
}
