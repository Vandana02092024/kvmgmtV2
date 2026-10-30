<?php

namespace App\Services\SalesReport;

use App\Salesection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesReportDailyMetrics
{
    public static function fields()
    {
        return array('ez_drop_signups' => 'EZ Drop Signups', 'new_customers' => 'New Customers');
    }

    public function values(array $values)
    {
        $result = array();
        foreach (self::fields() as $field => $label) {
            $value = isset($values[$field]) ? $values[$field] : null;
            if ($value === null || $value === '') {
                $result[$field] = null;
            } elseif ((!is_int($value) && !is_string($value)) || !preg_match('/^\d+$/', (string) $value) || (float) $value > 2147483647) {
                throw new RuntimeException($label . ' must be a whole number between 0 and 2147483647.');
            } else {
                $result[$field] = (int) $value;
            }
        }
        return $result;
    }

    public function week($locationId, $start, $end)
    {
        $records = DB::table('salesections')->where('location_id', $locationId)->where('nameDep', 'daily_metrics')
            ->where('nameId', 0)->whereBetween('dataDate', array($start, $end))->get();
        $days = array();
        foreach ($records as $record) {
            if (isset($days[$record->dataDate])) {
                throw new RuntimeException('Duplicate daily customer metrics exist for ' . $record->dataDate . '.');
            }
            $days[$record->dataDate] = array('ez_drop_signups' => $record->ez_drop_signups, 'new_customers' => $record->new_customers);
        }
        return $days;
    }

    public function row(array $values, array $context)
    {
        $values = $this->values($values);
        if ($values['ez_drop_signups'] === null && $values['new_customers'] === null) {
            return null;
        }
        return array_merge(array('nameDep' => 'daily_metrics', 'nameId' => 0, 'dataDate' => $context['dataDate'],
            'dataDay' => $context['dataDay'], 'unit' => '', 'amount' => '', 'weekNo' => $context['weekNo'],
            'storeNo' => $context['storeNo'], 'location_id' => $context['location_id']), $values);
    }

    /** Called inside the same transaction as the ordinary manual sales save. */
    public function saveManual($locationId, $userId, array $entries)
    {
        if (count($entries) > 7) {
            throw new RuntimeException('Save customer counts for one reporting week at a time.');
        }
        $location = DB::table('locations')->where('id', $locationId)->where('status', '1')->lockForUpdate()->first();
        if (!$location || !DB::table('users')->where('id', $userId)->where('status', '1')->exists() ||
            !DB::table('user_locations')->where('user', $userId)->where('location', $locationId)->exists()) {
            throw new RuntimeException('Select an active store assigned to your account.');
        }
        $importer = app(SalesReportPdfImporter::class);
        $seen = array();
        $week = null;
        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['date']) || !is_string($entry['date'])) {
                throw new RuntimeException('Invalid daily customer count date.');
            }
            $context = $importer->context($locationId, $entry['date']);
            if (isset($seen[$context['dataDate']]) || ($week !== null && $week !== $context['week_start'])) {
                throw new RuntimeException('Customer count dates must be unique and in the same reporting week.');
            }
            $seen[$context['dataDate']] = true;
            $week = $context['week_start'];
            $values = $this->values($entry);
            $records = Salesection::where('location_id', $locationId)->where('dataDate', $context['dataDate'])
                ->where('nameDep', 'daily_metrics')->where('nameId', 0)->lockForUpdate()->get();
            if (count($records) > 1) {
                throw new RuntimeException('Duplicate daily customer metrics must be resolved before saving.');
            }
            if (count($records)) {
                $records[0]->update(array_intersect_key($values, $entry)); // An explicitly cleared input becomes NULL.
            } elseif ($row = $this->row($values, $context)) {
                $row['comment'] = '';
                Salesection::create($row);
            }
        }
    }

    public function attachWeekly($reports, $locationId)
    {
        if (!count($reports)) return;
        $starts = array();
        foreach ($reports as $report) $starts[] = $report->periodStart;
        $days = $this->week($locationId, min($starts), date('Y-m-d', strtotime(max($starts) . ' +6 days')));
        foreach ($reports as $report) {
            foreach (self::fields() as $field => $label) {
                $total = null;
                for ($offset = 0; $offset < 7; $offset++) {
                    $date = date('Y-m-d', strtotime($report->periodStart . ' +' . $offset . ' days'));
                    if (isset($days[$date][$field])) $total = ($total ?? 0) + (int) $days[$date][$field];
                }
                $report->$field = $total;
            }
        }
    }
}
