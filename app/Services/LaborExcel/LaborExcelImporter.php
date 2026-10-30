<?php

namespace App\Services\LaborExcel;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LaborExcelImporter
{
    private $locking = false;

    private function table($name)
    {
        $query = DB::table($name);
        // MySQL REPEATABLE READ snapshots can lag behind concurrent payroll
        // edits. During commit all checks must use current, locking reads.
        return $this->locking ? $query->lockForUpdate() : $query;
    }

    public function authorize($locationId, $userId)
    {
        if (!$locationId || !$this->table('users')->where('id', $userId)->where('status', '1')->first() ||
            !$this->table('user_locations')->join('locations', 'locations.id', '=', 'user_locations.location')
                ->where('user_locations.user', $userId)->where('locations.id', $locationId)->where('locations.status', '1')->first()) {
            throw new RuntimeException('Select an active store assigned to your account before uploading.');
        }
    }

    public function context($locationId, $startWeek)
    {
        if (!ctype_digit((string) $startWeek) || (int) $startWeek < 1 || (int) $startWeek % 2 !== 1 || (int) $startWeek > 100000) {
            throw new RuntimeException('Select a valid bi-weekly labor period before uploading.');
        }
        $store = $this->table('locations')->where('id', $locationId)->where('status', '1')->first();
        $anchor = $store ? \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $store->startdate) : false;
        if (!$anchor || $anchor->format('Y-m-d') !== $store->startdate) throw new RuntimeException('Configure the selected store start date first.');
        // Same Saturday anchor and odd/even store weeks as the manual form's add_weeks().
        $start = $anchor->modify('+' . ((int) $startWeek - 1) . ' weeks');
        if ($anchor->format('w') !== '6') $start = $start->modify('previous saturday');
        return ['location_id' => (int) $locationId, 'location_name' => $store->location,
            'start_week' => (int) $startWeek, 'end_week' => (int) $startWeek + 1,
            'start_date' => $start->format('Y-m-d'), 'end_date' => $start->modify('+13 days')->format('Y-m-d')];
    }

    public function catalog(array $context)
    {
        $loc = $context['location_id'];
        $knownNames = [];
        $localEmployeeIds = array_fill_keys(array_merge(
            $this->table('payroll_employee_locations')->where('payrollcat_depid', $loc)->pluck('payrollemp_id')->all(),
            $this->table('payroll_employee_departments')->where('location', $loc)->pluck('payrollemp_id')->all()
        ), true);
        // Acquire employee locks in one consistent order before department
        // joins, including when different stores import at the same time.
        foreach ($this->table('payroll_employees')->select('id', 'name')->orderBy('id')->get() as $employee) {
            if (isset($localEmployeeIds[$employee->id])) $knownNames[$this->normalize($employee->name)] = true;
        }
        $departments = $this->table('payroll_departments as d')->join('payroll_department_locations as a', 'a.payrolldep_id', '=', 'd.id')
            ->where('a.location', $loc)->where('d.status', '1')->select('d.id', 'd.name')->distinct()->orderBy('d.id')->get();
        $categories = $this->table('payroll_categories as c')->join('payroll_category_locations as a', 'a.payrollcat_id', '=', 'c.id')
            ->where('a.location', $loc)->where('c.status', '1')->select('c.id', 'c.name')->distinct()->orderBy('c.id')->get();
        $targets = [];
        foreach ($departments as $department) {
            // payrollcat_depid means department in this table, and location in the other table.
            $employees = $this->table('payroll_employees as e')
                ->join('payroll_employee_departments as d', 'd.payrollemp_id', '=', 'e.id')
                ->join('payroll_employee_locations as l', 'l.payrollemp_id', '=', 'e.id')
                ->where('d.location', $loc)->where('d.payrollcat_depid', $department->id)
                ->where('l.payrollcat_depid', $loc)->where('e.status', '1')
                ->select('e.id', 'e.name', 'e.rateperhour', 'e.salary')->distinct()->orderBy('e.id')->get();
            foreach ($employees as $employee) {
                $key = $department->id . ':' . $employee->id;
                $targets[$key] = ['key' => $key, 'depId' => (int) $department->id, 'empId' => (int) $employee->id,
                    'department' => $department->name, 'name' => $employee->name,
                    'hourly_rate' => $employee->rateperhour, 'salary' => $employee->salary];
            }
        }
        return ['targets' => $targets, 'categories' => $categories->all(), 'departments' => $departments->all(), 'known_names' => $knownNames];
    }

    public function employeeOptions(array $source, $index, array $catalog)
    {
        $employee = $source['employee'];
        $name = $this->normalize($employee['name']);
        $options = [];
        foreach ($catalog['targets'] as $key => $target) {
            // Show the matching identity even when its rate differs; the plan
            // reports that mismatch instead of leaving an empty dropdown.
            if ($this->normalize($target['name']) === $name) {
                $options[$key] = $target;
            }
        }
        // Prevent duplicate identities in this store. A same-name employee at
        // another store may have a different rate and must remain unchanged.
        if (!isset($catalog['known_names'][$name])) {
            foreach ($catalog['departments'] as $department) {
                $key = 'new:' . $department->id;
                $options[$key] = ['key' => $key, 'depId' => (int) $department->id, 'empId' => -($index + 1),
                    'department' => $department->name, 'name' => $employee['name'],
                    'hourly_rate' => $employee['hourly_rate'] ?? 0,
                    'salary' => $employee['weeks'][0]['salary_amount'] ?? 0, 'new_employee' => true];
            }
        }
        return $options;
    }

    public function sources(array $report)
    {
        $sources = [];
        foreach ($report['categories'] as $department) foreach ($department['employees'] as $employee) {
            $sources[] = ['department' => $department['name'], 'employee' => $employee];
        }
        return $sources;
    }

    public function suggestions(array $report, array $catalog)
    {
        $mapping = [];
        foreach ($this->sources($report) as $index => $source) {
            $matches = [];
            $combinedMatches = [];
            foreach ($this->employeeOptions($source, $index, $catalog) as $key => $target) {
                if ($this->departmentKey($target['department']) === $this->departmentKey($source['department']) &&
                    $this->normalize($target['name']) === $this->normalize($source['employee']['name'])) $matches[] = $key;
                elseif ($this->departmentContains($target['department'], $source['department'])) $combinedMatches[] = $key;
            }
            // COUNTER may use the assigned Counter / Tagging department. Only
            // use a whole slash-separated component, with exact matches first.
            if (!$matches) $matches = $combinedMatches;
            if (count($matches) > 1 && $source['employee']['hourly_rate'] !== null) {
                $options = $this->employeeOptions($source, $index, $catalog);
                $rateMatches = array_values(array_filter($matches, function ($key) use ($source, $options) {
                    return abs((float) $options[$key]['hourly_rate'] - $source['employee']['hourly_rate']) < .00001;
                }));
                if (count($rateMatches) === 1) $matches = $rateMatches;
            }
            $mapping[$index] = count($matches) === 1 ? $matches[0] : '';
        }
        return $mapping;
    }

    public function previewMapping(array $report, array $catalog, array $previous = [])
    {
        $mapping = $this->suggestions($report, $catalog);
        foreach ($this->sources($report) as $index => $source) {
            $key = $previous[$index] ?? null;
            // Old previews can contain blank mappings generated before an
            // employee/department matching fix. Refresh those without losing
            // a still-valid, explicitly chosen employee assignment.
            if (is_string($key) && $key !== '' && isset($this->employeeOptions($source, $index, $catalog)[$key])) $mapping[$index] = $key;
        }
        return $mapping;
    }

    private function departmentKey($name)
    {
        $name = preg_replace('/^\s*\d+\s*-\s*/u', '', (string) $name);
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $this->normalize($name));
    }

    private function departmentContains($target, $source)
    {
        $parts = preg_split('~/~u', $target);
        if (count($parts) < 2) return false;
        foreach ($parts as $part) if ($this->departmentKey($part) === $this->departmentKey($source)) return true;
        return false;
    }

    public function headers(array $context)
    {
        return $this->table('laborheadersections')->where('location_id', $context['location_id'])
            ->where('start_week', '<=', $context['end_week'])
            ->where(function ($q) use ($context) { $q->where('end_week', '>=', $context['start_week'])->orWhere('start_week', '>=', $context['start_week']); })
            ->orderBy('id')->get();
    }

    public function snapshot(array $context)
    {
        $headers = $this->headers($context);
        $details = $this->table('laboursections')->whereIn('header_id', $headers->pluck('id'))->orderBy('id')->get();
        $masters = $this->table('payroll_employees')->whereIn('id', $details->pluck('empId'))->orderBy('id')->get();
        return hash('sha256', json_encode([$context, $this->catalog($context), $headers->all(), $details->all(), $masters->all()]));
    }

    public function plan(array $report, array $mapping, array $catalog, array $context)
    {
        if ($report['errors']) throw new RuntimeException(implode(' ', $report['errors']));
        if (count($report['weeks']) !== 2) throw new RuntimeException('The manual labor report has two weeks. Upload exactly two weekly column groups for the selected bi-weekly period.');
        $sources = $this->sources($report);
        if (count($mapping) !== count($sources) || count($sources) === 0) throw new RuntimeException('Map every workbook employee before importing.');
        $categoryIds = [];
        foreach (['regular', 'overtime', 'holiday', 'salary'] as $name) {
            $matches = array_values(array_filter($catalog['categories'], function ($cat) use ($name) { return strtolower(trim($cat->name)) === $name; }));
            if (count($matches) !== 1) throw new RuntimeException('Assign exactly one active ' . ucfirst($name) . ' payroll category to this store.');
            $categoryIds[$name] = $matches[0]->id;
        }
        $rows = []; $usedEmployees = []; $usedNames = [];
        foreach ($sources as $index => $source) {
            $key = $mapping[$index] ?? '';
            $options = $this->employeeOptions($source, $index, $catalog);
            if (!is_string($key) || !isset($options[$key])) throw new RuntimeException('Excel row ' . $source['employee']['source_row'] . ': select the same employee name in an assigned department, or create the missing employee. If that name already exists in this store, review its status/store assignments in Payroll first.');
            $target = $options[$key]; $employee = $source['employee'];
            if (!empty($target['new_employee'])) $this->validateNewEmployee($employee);
            $normalizedName = $this->normalize($employee['name']);
            if (isset($usedEmployees[$target['empId']]) || isset($usedNames[$normalizedName])) throw new RuntimeException('Each employee must be mapped once. Duplicate employee name/mapping for ' . $target['name'] . '.');
            $usedEmployees[$target['empId']] = true;
            $usedNames[$normalizedName] = true;
            $hourly = $this->masterNumber($target['hourly_rate'], $target['name']);
            $salary = $this->masterNumber($target['salary'], $target['name']);
            if ($employee['hourly_rate'] !== null && abs($employee['hourly_rate'] - $hourly) > 0.00001) {
                throw new RuntimeException($employee['name'] . ': Excel hourly rate (' . $employee['hourly_rate'] . ') differs from employee master (' . $hourly . '). Review the mapping or update the employee manually first.');
            }
            foreach ($employee['weeks'] as $weekIndex => $week) {
                if (abs($week['salary_amount'] - $salary) > 0.00001) throw new RuntimeException($employee['name'] . ': week ' . ($weekIndex + 1) . ' salary differs from the employee master. The manual report uses the master salary for both weeks; review it before importing.');
                $expected = $hourly * ($week['regular_hours'] + $week['overtime_hours'] * 1.5 + $week['holiday_hours']) + $salary;
                if (abs($expected - $week['total_amount']) > (float) config('labor_excel.total_tolerance', .05)) throw new RuntimeException($employee['name'] . ': weekly pay does not match the selected employee master.');
                foreach (['regular' => 'regular_hours', 'overtime' => 'overtime_hours', 'holiday' => 'holiday_hours', 'salary' => 'salary_amount'] as $category => $field) {
                    $value = $week[$field];
                    if (!is_numeric($value) || !is_finite((float) $value) || $value < 0 || $value > 99999999) throw new RuntimeException('Invalid or excessive hours/amount in Excel row ' . $employee['source_row'] . '.');
                    if (abs((float) $value - round((float) $value, 2)) > .0000001) throw new RuntimeException('Excel row ' . $employee['source_row'] . ' contains hours/amounts with more than two decimal places. The manual labor form supports two decimal places.');
                    $rows[] = ['empId' => $target['empId'], 'depId' => $target['depId'], 'catId' => $categoryIds[$category],
                        'weekNo' => $context['start_week'] + $weekIndex, 'rate' => (string) $value];
                }
            }
        }
        return $rows;
    }

    public function save(array $pending, array $mapping, $userId, $replace, $createEmployees = false)
    {
        return DB::transaction(function () use ($pending, $mapping, $userId, $replace, $createEmployees) {
            $this->locking = true;
            try {
                $previous = $pending['context'];
                $this->table('locations')->where('id', $previous['location_id'])->lockForUpdate()->first();
                $this->authorize($previous['location_id'], $userId);
                $context = $this->context($previous['location_id'], $previous['start_week']);
                if ($context !== $previous || !hash_equals($pending['snapshot'], $this->snapshot($context))) throw new RuntimeException('The labor report, store setup or payroll assignments changed after preview. Upload again to review current values.');
                // Lock master rows while checking rates and computing the same totals as manual entry.
                $existingEmployeeIds = $this->table('laboursections')->whereIn('header_id', $this->headers($context)->pluck('id'))->pluck('empId')->all();
                $employeeIds = array_values(array_unique(array_merge($existingEmployeeIds, array_column($this->catalog($context)['targets'], 'empId'))));
                $this->table('payroll_employees')->whereIn('id', $employeeIds)->orderBy('id')->lockForUpdate()->get();
                $catalog = $this->catalog($context);
                if (!hash_equals($pending['snapshot'], $this->snapshot($context))) throw new RuntimeException('Payroll data changed after preview. Upload again.');
                $rows = $this->plan($pending['report'], $mapping, $catalog, $context);
                $newEmployees = [];
                foreach ($this->sources($pending['report']) as $index => $source) {
                    $target = $this->employeeOptions($source, $index, $catalog)[$mapping[$index]];
                    if (!empty($target['new_employee'])) {
                        if (!$createEmployees) throw new RuntimeException('Review the new employee names, hourly rates, salaries and departments, then confirm creation of missing employees. No data was saved.');
                        $newEmployees[$target['empId']] = $target;
                    }
                }
                $headers = $this->headers($context);
                if ($headers->count() > 1) throw new RuntimeException('Multiple overlapping labor headers exist. Review the existing records before importing.');
                $header = $headers->first();
                if ($header && ($header->start_week != $context['start_week'] || $header->end_week != $context['end_week'] ||
                    $header->start_date !== $context['start_date'] || $header->end_date !== $context['end_date'])) throw new RuntimeException('The existing labor header has a different period. Review it before importing.');
                $now = date('Y-m-d H:i:s');
                $employeeIdsCreated = [];
                foreach ($newEmployees as $temporaryId => $target) {
                    $employee = \App\PayrollEmployee::create([
                        'name' => $target['name'], 'rateperhour' => (string) $target['hourly_rate'], 'salary' => (string) $target['salary'],
                        'working_hour' => '0', 'sorting_order' => 0, 'status' => '1', 'employeeStatus' => '1',
                    ]);
                    \App\PayrollEmployee_location::create(['payrollemp_id' => $employee->id, 'payrollcat_depid' => $context['location_id']]);
                    \App\PayrollEmployee_department::create(['payrollemp_id' => $employee->id, 'payrollcat_depid' => $target['depId'], 'location' => $context['location_id']]);
                    // Same audit table and fields used by manual employee creation.
                    \App\Track_employees::create(['action' => 'Create', 'employee_detail' => 'Employee ' . $employee->name . ' added from Labor Excel',
                        'location' => $context['location_name'], 'location_id' => $context['location_id'],
                        'created_by' => $this->table('users')->where('id', $userId)->value('name')]);
                    $employeeIdsCreated[$temporaryId] = $employee->id;
                }
                $headerId = $header ? $header->id : $this->table('laborheadersections')->insertGetId([
                    'location_id' => $context['location_id'], 'start_week' => $context['start_week'], 'end_week' => $context['end_week'],
                    'start_date' => $context['start_date'], 'end_date' => $context['end_date'], 'total_amount' => 0, 'created_at' => $now, 'updated_at' => $now]);
                $counts = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
                foreach ($rows as $row) {
                    if (isset($employeeIdsCreated[$row['empId']])) $row['empId'] = $employeeIdsCreated[$row['empId']];
                    $identity = $row; unset($identity['rate']); $identity['header_id'] = $headerId;
                    $existing = $this->table('laboursections')->where($identity)->lockForUpdate()->get();
                    if ($existing->count() > 1) throw new RuntimeException('Duplicate existing employee/week/category rows were found. Review the period before importing.');
                    if ($existing->isEmpty()) {
                        $this->table('laboursections')->insert($row + ['header_id' => $headerId, 'created_at' => $now, 'updated_at' => $now]); $counts['inserted']++;
                    } elseif (abs((float) $existing->first()->rate - (float) $row['rate']) < .000001) {
                        $counts['unchanged']++;
                    } else {
                        if (!$replace) throw new RuntimeException('Existing hours/amounts would change. Review them and explicitly allow replacement of matching fields. No data was saved.');
                        $this->table('laboursections')->where('id', $existing->first()->id)->update(['rate' => $row['rate'], 'updated_at' => $now]); $counts['updated']++;
                    }
                }
                $total = $this->total($headerId, $context);
                if ($total > 999999999) throw new RuntimeException('Labor total exceeds the supported amount.');
                $this->table('laborheadersections')->where('id', $headerId)->update(['total_amount' => round($total, 2), 'updated_at' => $now]);
                return $counts + ['header_id' => $headerId, 'total' => round($total, 2), 'employees_created' => count($employeeIdsCreated)];
            } finally {
                $this->locking = false;
            }
        });
    }

    public function total($headerId, array $context)
    {
        $catalog = $this->catalog($context);
        $departmentIds = array_map(function ($d) { return (int) $d->id; }, $catalog['departments']);
        $rows = $this->table('laboursections as l')->leftJoin('payroll_employees as e', 'e.id', '=', 'l.empId')
            ->leftJoin('payroll_categories as c', 'c.id', '=', 'l.catId')->where('l.header_id', $headerId)
            ->select('l.*', 'e.rateperhour', 'e.salary', 'e.id as employee_id', 'c.name as category_name')->get();
        $total = 0; $employees = []; $identities = [];
        foreach ($rows as $row) {
            $identity = $row->depId . ':' . $row->empId . ':' . $row->catId . ':' . $row->weekNo;
            if (isset($identities[$identity])) throw new RuntimeException('Duplicate labor detail rows exist. Review the period first.');
            $identities[$identity] = true;
            if (!$row->employee_id || !$row->category_name || !in_array((int) $row->depId, $departmentIds, true) ||
                !in_array((int) $row->weekNo, [$context['start_week'], $context['end_week']], true)) throw new RuntimeException('Existing labor details contain missing masters or entries outside the selected period. Review them before importing.');
            $employeeKey = $row->depId . ':' . $row->empId;
            if (!isset($employees[$employeeKey])) { $total += $this->masterNumber($row->salary, 'existing employee') * 2; $employees[$employeeKey] = true; }
            $category = strtolower(trim($row->category_name));
            if ($category !== 'salary') $total += $this->masterNumber($row->rate, 'existing labor entry') * $this->masterNumber($row->rateperhour, 'existing employee') * ($category === 'overtime' ? 1.5 : 1);
        }
        return $total;
    }

    private function masterNumber($value, $name)
    {
        if ($value === null || $value === '') return 0.0;
        if (!is_numeric($value) || !is_finite((float) $value) || $value < 0) throw new RuntimeException('Invalid payroll value for ' . $name . '. Review the employee master.');
        return (float) $value;
    }

    private function validateNewEmployee(array $employee)
    {
        if (trim($employee['name']) === '' || mb_strlen($employee['name']) > 50) throw new RuntimeException('Employee name must contain 1 to 50 characters. Review Excel row ' . $employee['source_row'] . '.');
        if ($employee['hourly_rate'] === null && array_sum(array_column($employee['weeks'], 'salary_amount')) <= 0) throw new RuntimeException($employee['name'] . ': enter an hourly rate in the column immediately after the employee name.');
        if ($employee['hourly_rate'] === null) foreach ($employee['weeks'] as $week) {
            if ($week['regular_hours'] != 0 || $week['overtime_hours'] != 0 || $week['holiday_hours'] != 0) throw new RuntimeException($employee['name'] . ': hourly work requires an explicit hourly rate next to the employee name.');
        }
        foreach ([$employee['hourly_rate'] ?? 0, $employee['weeks'][0]['salary_amount'] ?? 0] as $value) {
            if (!is_numeric($value) || !is_finite((float) $value) || $value < 0 || strlen((string) $value) > 10) throw new RuntimeException($employee['name'] . ': rate/salary does not fit the employee master.');
        }
    }

    private function normalize($value)
    {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }
}
