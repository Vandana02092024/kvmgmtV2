<?php

namespace App\Services\LaborExcel;

class LaborSheetParser
{
    public function parse(array $rows, $sheetName)
    {
        $errors = array();
        $headerRow = $this->findHeaderRow($rows);
        if ($headerRow === null) {
            return $this->emptyResult($sheetName, array('Labor employee header could not be found.'));
        }

        $weeks = $this->discoverWeeks($rows, $headerRow);
        if (!count($weeks)) {
            return $this->emptyResult($sheetName, array('No dynamic week columns could be found.'));
        }

        $employeeColumn = $this->findHeaderColumn($rows[$headerRow], 'employeeinformation');
        // Employee Information spans name and the immediately adjacent hourly
        // rate column. Never infer the employee's rate from a weekly hours group.
        $rateColumn = $employeeColumn + 1;
        if ($weeks[0]['columns']['regular'] <= $rateColumn) {
            return $this->emptyResult($sheetName, ['Employee Information must contain separate employee name and adjacent hourly-rate columns before weekly hours.']);
        }
        $notesColumn = $this->findHeaderColumn($rows[$headerRow], 'notes');
        $categories = array();
        $currentCategory = null;
        $reportedTotalRow = null;

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber <= $headerRow) {
                continue;
            }
            $name = $this->text($this->cell($row, $employeeColumn));
            if ($name === '') {
                // The supplied template repeats the weekly column captions below
                // its total row. These are labels, not unnamed employee values.
                $footer = $reportedTotalRow !== null;
                foreach ($weeks as $week) {
                    $c = $week['columns'];
                    $footer = $footer && $this->compact($this->cell($row, $c['regular'])) === 'regular'
                        && in_array($this->compact($this->cell($row, $c['overtime'])), ['over', 'overtime'], true)
                        && $this->compact($this->cell($row, $c['holiday'])) === 'holiday'
                        && $this->compact($this->cell($row, $c['salary'])) === 'salary'
                        && (bool) preg_match('/^(?:[0-9]+(?:st|nd|rd|th)?wk)?total$/', $this->compact($this->cell($row, $c['total'])));
                }
                if ($footer) continue;
                if ($this->rowHasWeekData($row, $weeks)) $errors[] = 'Row ' . $rowNumber . ' has labor values but no employee name.';
                continue;
            }
            if ($this->isReportedTotalLabel($name)) {
                if ($reportedTotalRow !== null) $errors[] = 'Multiple reported total rows were found.';
                $reportedTotalRow = $row;
                continue;
            }

            $category = $this->categoryFromLabel($name);
            if ($category !== null && !$this->rowHasWeekData($row, $weeks)) {
                $category['display_order'] = count($categories);
                $category['employees'] = array();
                $categories[] = $category;
                $currentCategory = count($categories) - 1;
                continue;
            }

            if ($currentCategory === null || !$this->rowHasEmployeeData($row, $rateColumn, $weeks)) {
                if ($currentCategory === null && $this->rowHasWeekData($row, $weeks)) $errors[] = 'Row ' . $rowNumber . ' has no department heading.';
                continue;
            }

            $employee = $this->employeeFromRow($row, $rowNumber, $name, $rateColumn, $notesColumn, $weeks);
            $this->validateEmployeeWeeks($employee, $errors);
            $categories[$currentCategory]['employees'][] = $employee;
        }

        $employeeCount = 0;
        foreach ($categories as $category) {
            $employeeCount += count($category['employees']);
        }
        if (!count($categories)) {
            $errors[] = 'No labor categories could be found.';
        }
        if (!$employeeCount) {
            $errors[] = 'No employees with week data could be found.';
        }

        $weeks = $this->attachReportedTotals($weeks, $reportedTotalRow);
        $calculatedTotals = $this->calculatedTotals($categories, $weeks);
        $this->validateReportedTotals($weeks, $calculatedTotals, $errors);

        return array(
            'sheet_name' => $sheetName,
            'weeks' => $weeks,
            'categories' => $categories,
            'employee_count' => $employeeCount,
            'totals' => $calculatedTotals,
            'errors' => array_values(array_unique($errors)),
        );
    }

    private function findHeaderRow(array $rows)
    {
        foreach ($rows as $rowNumber => $row) {
            if ($this->findHeaderColumn($row, 'employeeinformation') !== null &&
                $this->findHeaderColumn($row, 'regular') !== null &&
                $this->findHeaderColumn($row, 'total') !== null) {
                return $rowNumber;
            }
        }
        return null;
    }

    private function discoverWeeks(array $rows, $headerRow)
    {
        $header = $rows[$headerRow];
        $maxColumn = count($header) ? max(array_keys($header)) : 0;
        $weeks = array();
        for ($column = 1; $column <= $maxColumn; $column++) {
            if ($this->compact($this->cell($header, $column)) !== 'regular') {
                continue;
            }
            $labels = array(
                'regular' => $column,
                'overtime' => $column + 1,
                'holiday' => $column + 2,
                'salary' => $column + 3,
                'total' => $column + 4,
            );
            if (!$this->matchesHeader($header, $labels['overtime'], array('over', 'overtime')) ||
                !$this->matchesHeader($header, $labels['holiday'], array('holiday')) ||
                !$this->matchesHeader($header, $labels['salary'], array('salary')) ||
                !$this->matchesHeader($header, $labels['total'], array('total'))) {
                continue;
            }
            $label = $this->weekLabel($rows, $headerRow, $column, count($weeks) + 1);
            $weeks[] = array(
                'key' => $this->compact($label) ?: 'week' . (count($weeks) + 1),
                'label' => $label,
                'display_order' => count($weeks),
                'columns' => $labels,
                'reported' => array(),
            );
            $column += 4;
        }
        return $weeks;
    }

    private function weekLabel(array $rows, $headerRow, $column, $number)
    {
        for ($row = $headerRow - 1; $row >= max(1, $headerRow - 3); $row--) {
            $label = isset($rows[$row]) ? $this->text($this->cell($rows[$row], $column)) : '';
            if ($label !== '') {
                return $label;
            }
        }
        return 'Week #' . $number;
    }

    private function employeeFromRow(array $row, $rowNumber, $name, $rateColumn, $notesColumn, array $weeks)
    {
        $rate = $this->number($this->cell($row, $rateColumn));
        $employeeWeeks = array();
        $totals = $this->zeroTotals();
        foreach ($weeks as $week) {
            $columns = $week['columns'];
            $values = array(
                'regular_hours' => $this->numberOrZero($this->cell($row, $columns['regular'])),
                'overtime_hours' => $this->numberOrZero($this->cell($row, $columns['overtime'])),
                'holiday_hours' => $this->numberOrZero($this->cell($row, $columns['holiday'])),
                'salary_amount' => $this->numberOrZero($this->cell($row, $columns['salary'])),
                'total_amount' => $this->number($this->cell($row, $columns['total'])),
                'display_order' => $week['display_order'],
            );
            if ($values['total_amount'] === null) {
                $values['total_amount'] = $this->expectedWeekTotal($values, $rate);
            }
            $employeeWeeks[] = $values;
            $totals['regular_hours'] += $values['regular_hours'];
            $totals['overtime_hours'] += $values['overtime_hours'];
            $totals['holiday_hours'] += $values['holiday_hours'];
            $totals['salary_amount'] += $values['salary_amount'];
            $totals['total_amount'] += $values['total_amount'];
        }

        return array(
            'name' => $name,
            'normalized_name' => $this->normalizeName($name),
            'hourly_rate' => $rate,
            'notes' => $notesColumn === null ? null : $this->nullableText($this->cell($row, $notesColumn)),
            'source_row' => $rowNumber,
            'weeks' => $employeeWeeks,
            'totals' => $totals,
        );
    }

    private function validateEmployeeWeeks(array $employee, array &$errors)
    {
        $rate = $employee['hourly_rate'];
        foreach ($employee['weeks'] as $index => $week) {
            $expected = $this->expectedWeekTotal($week, $rate);
            if ($expected === null) {
                continue;
            }
            if (abs($expected - $week['total_amount']) > (float) config('labor_excel.total_tolerance', 0.05)) {
                $errors[] = $employee['name'] . ' week ' . ($index + 1) . ' pay does not match hours, rate and salary.';
            }
        }
    }

    private function expectedWeekTotal(array $week, $rate)
    {
        $hourlyPay = 0;
        $hasHours = $week['regular_hours'] != 0 || $week['overtime_hours'] != 0 || $week['holiday_hours'] != 0;
        if ($hasHours && $rate === null) {
            return null;
        }
        if ($rate !== null) {
            $hourlyPay = ($week['regular_hours'] * $rate) +
                ($week['overtime_hours'] * $rate * 1.5) +
                ($week['holiday_hours'] * $rate);
        }
        return round($hourlyPay + $week['salary_amount'], 2);
    }

    private function attachReportedTotals(array $weeks, $row)
    {
        if ($row === null) {
            return $weeks;
        }
        foreach ($weeks as &$week) {
            $columns = $week['columns'];
            $week['reported'] = array(
                'regular_hours' => $this->number($this->cell($row, $columns['regular'])),
                'overtime_hours' => $this->number($this->cell($row, $columns['overtime'])),
                'holiday_hours' => $this->number($this->cell($row, $columns['holiday'])),
                'salary_amount' => $this->number($this->cell($row, $columns['salary'])),
                'total_amount' => $this->number($this->cell($row, $columns['total'])),
            );
        }
        unset($week);
        return $weeks;
    }

    private function calculatedTotals(array $categories, array $weeks)
    {
        $weekTotals = array();
        foreach ($weeks as $week) {
            $weekTotals[$week['display_order']] = $this->zeroTotals();
        }
        $grand = $this->zeroTotals();
        foreach ($categories as $category) {
            foreach ($category['employees'] as $employee) {
                foreach ($employee['weeks'] as $index => $values) {
                    foreach ($weekTotals[$index] as $field => $unused) {
                        $weekTotals[$index][$field] += $values[$field];
                        $grand[$field] += $values[$field];
                    }
                }
            }
        }
        return array('weeks' => $weekTotals, 'grand' => $grand);
    }

    private function validateReportedTotals(array $weeks, array $calculated, array &$errors)
    {
        $tolerance = (float) config('labor_excel.total_tolerance', 0.05);
        foreach ($weeks as $week) {
            foreach ($week['reported'] as $field => $reported) {
                if ($reported !== null && abs($reported - $calculated['weeks'][$week['display_order']][$field]) > $tolerance) {
                    $errors[] = $week['label'] . ' reported ' . str_replace('_', ' ', $field) . ' does not match employee rows.';
                }
            }
        }
    }

    private function categoryFromLabel($label)
    {
        if (!preg_match('/^\s*([0-9]+)\s*-\s*(.+?)\s*$/', $label, $matches)) {
            return null;
        }
        return array('code' => $matches[1], 'name' => trim($matches[2]));
    }

    private function rowHasEmployeeData(array $row, $rateColumn, array $weeks)
    {
        if ($this->number($this->cell($row, $rateColumn)) !== null) {
            return true;
        }
        return $this->rowHasWeekData($row, $weeks);
    }

    private function rowHasWeekData(array $row, array $weeks)
    {
        foreach ($weeks as $week) {
            foreach ($week['columns'] as $column) {
                $number = $this->number($this->cell($row, $column));
                if ($number !== null && abs($number) > 0.00001) {
                    return true;
                }
            }
        }
        return false;
    }

    private function findHeaderColumn(array $row, $expected)
    {
        foreach ($row as $column => $value) {
            if ($this->compact($value) === $expected) {
                return (int) $column;
            }
        }
        return null;
    }

    private function matchesHeader(array $row, $column, array $expected)
    {
        return in_array($this->compact($this->cell($row, $column)), $expected, true);
    }

    private function isReportedTotalLabel($value)
    {
        return strpos($this->compact($value), 'pleasetotalallcolumns') === 0;
    }

    private function number($value)
    {
        if (is_int($value) || is_float($value)) {
            if (!is_finite((float) $value) || $value < 0) throw new \RuntimeException('Labor amounts and hours must be finite, non-negative numbers.');
            return (float) $value;
        }
        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }
        if (!preg_match('/^\$?\s*(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/D', $value)) {
            throw new \RuntimeException('Invalid labor number: ' . mb_substr($value, 0, 60) . '. Use numeric hours and amounts.');
        }
        return (float) str_replace(['$', ',', ' '], '', $value);
    }

    private function numberOrZero($value)
    {
        $number = $this->number($value);
        return $number === null ? 0.0 : $number;
    }

    private function normalizeName($value)
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function text($value)
    {
        return preg_replace('/\s+/', ' ', trim((string) $value));
    }

    private function nullableText($value)
    {
        $value = $this->text($value);
        return $value === '' ? null : $value;
    }

    private function compact($value)
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $value));
    }

    private function cell(array $row, $column)
    {
        return $column !== null && array_key_exists($column, $row) ? $row[$column] : null;
    }

    private function zeroTotals()
    {
        return array('regular_hours' => 0.0, 'overtime_hours' => 0.0,
            'holiday_hours' => 0.0, 'salary_amount' => 0.0, 'total_amount' => 0.0);
    }

    private function emptyResult($sheetName, array $errors)
    {
        return array('sheet_name' => $sheetName, 'weeks' => array(), 'categories' => array(),
            'employee_count' => 0, 'totals' => array('weeks' => array(), 'grand' => $this->zeroTotals()),
            'errors' => $errors);
    }
}
