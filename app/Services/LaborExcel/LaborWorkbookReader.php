<?php

namespace App\Services\LaborExcel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class LaborWorkbookReader
{
    private $parser;

    public function __construct(LaborSheetParser $parser)
    {
        $this->parser = $parser;
    }

    public function read($path)
    {
        if (!is_file($path)) {
            throw new RuntimeException('Uploaded Excel file could not be found.');
        }
        $reader = IOFactory::createReaderForFile($path);
        if (!($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Xls) &&
            !($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Xlsx)) {
            throw new RuntimeException('Upload a genuine XLS or XLSX workbook.');
        }
        $info = $reader->listWorksheetInfo($path);
        if (count($info) > (int) config('labor_excel.max_sheets', 30)) {
            throw new RuntimeException('Workbook has too many sheets. Upload the Labor sheet separately.');
        }
        foreach ($info as $sheetInfo) {
            if ($sheetInfo['totalRows'] > (int) config('labor_excel.max_rows', 10000) ||
                $sheetInfo['totalColumns'] > (int) config('labor_excel.max_columns', 200)) {
                throw new RuntimeException('Workbook exceeds the row or column limit. Upload the Labor sheet separately.');
            }
        }
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($path);
        try {
            $sheet = $this->selectSheet($workbook);
            $rows = $this->sheetRows($sheet);
            $result = $this->parser->parse($rows, $sheet->getTitle());
            $result['sheet_count'] = $workbook->getSheetCount();
            return $result;
        } finally {
            $workbook->disconnectWorksheets();
            unset($workbook);
        }
    }

    private function selectSheet($workbook)
    {
        $preferred = trim((string) config('labor_excel.preferred_sheet', 'Labor'));
        foreach ($workbook->getWorksheetIterator() as $sheet) {
            if (strcasecmp(trim($sheet->getTitle()), $preferred) === 0) {
                return $sheet;
            }
        }
        if ($workbook->getSheetCount() === 1) {
            return $workbook->getSheet(0);
        }

        $matches = array();
        foreach ($workbook->getWorksheetIterator() as $sheet) {
            if ($this->looksLikeLaborSheet($sheet)) {
                $matches[] = $sheet;
            }
        }
        if (count($matches) === 1) {
            return $matches[0];
        }
        throw new RuntimeException('Labor sheet could not be selected. Use a sheet named Labor, a single-sheet workbook, or one unambiguous labor-formatted sheet.');
    }

    private function looksLikeLaborSheet($sheet)
    {
        $lastRow = min(20, $sheet->getHighestDataRow());
        $lastColumn = min(30, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn()));
        for ($row = 1; $row <= $lastRow; $row++) {
            for ($column = 1; $column <= $lastColumn; $column++) {
                $value = strtolower(preg_replace('/[^a-z]+/', '', (string) $this->cellValue($sheet, $column, $row)));
                if ($value === 'employeeinformation') {
                    return true;
                }
            }
        }
        return false;
    }

    private function sheetRows($sheet)
    {
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        if ($highestRow > (int) config('labor_excel.max_rows', 10000) ||
            $highestColumn > (int) config('labor_excel.max_columns', 200)) {
            throw new RuntimeException('Labor sheet exceeds the configured row or column limit.');
        }
        $rows = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            $values = array();
            for ($column = 1; $column <= $highestColumn; $column++) {
                $value = $this->cellValue($sheet, $column, $row);
                if ($value !== null && $value !== '') {
                    $values[$column] = $value;
                }
            }
            $rows[$row] = $values;
        }
        return $rows;
    }

    private function cellValue($sheet, $column, $row)
    {
        $cell = $sheet->getCell([$column, $row]);
        if ($cell->isFormula() && preg_match('/\[[^\]]+\].*!|WEBSERVICE|HYPERLINK|RTD\s*\(/i', (string) $cell->getValue())) {
            throw new RuntimeException('External formulas are not supported. Save the Labor sheet with local values and upload again.');
        }
        try {
            $value = $cell->getCalculatedValue();
            if (is_string($value) && preg_match('/^#(REF!|VALUE!|DIV\/0!|N\/A|NAME\?|NUM!|NULL!|SPILL!|CALC!)/', $value)) {
                throw new RuntimeException('Excel contains a formula error.');
            }
            if (is_object($value) && method_exists($value, '__toString')) $value = (string) $value;
            return $value;
        } catch (\Throwable $exception) {
            throw new RuntimeException('Cannot read cell ' . $cell->getCoordinate() . ' in ' . $sheet->getTitle() . '. Resolve formula errors and save the workbook again.', 0, $exception);
        }
    }
}
