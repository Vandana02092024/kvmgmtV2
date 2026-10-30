<?php

namespace App\Services\SalesReport;

use App\Salesection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesReportPdfImporter
{
    private $groups = array(
        'sale' => array('sales', 'sale_locations', 'sale', 'saleStatus'),
        'dis' => array('discounts', 'discount_locations', 'discount', 'disStatus'),
        'adj' => array('adjustments', 'adjustment_locations', 'adjustment', 'adjStatus'),
        'rec' => array('receipts', 'receipt_locations', 'receipt', 'recStatus'),
    );

    // Explicit business synonyms only. IDs always come from this store's
    // configured masters; no IDs, new masters or fuzzy matches are invented.
    private $aliases = array(
        'dryclean' => array('sale', 'Drycleaning'), 'drycleaning' => array('sale', 'Drycleaning'),
        'laundry' => array('sale', 'Laundry'), 'alteration' => array('sale', 'Alterations'),
        'alterations' => array('sale', 'Alterations'), 'household' => array('sale', 'Households'),
        'households' => array('sale', 'Households'), 'washnfold' => array('sale', 'WashNFold'),
        'refunds' => array('dis', 'Refund'), 'coupondiscounts' => array('dis', 'Coupons'),
        'finalsalediscounts' => array('dis', 'Final Discounts'), 'loyaltydiscounts' => array('dis', 'Loyalty'),
        'rewards' => array('dis', 'Loyalty'), 'referralcredit' => array('dis', 'Referral'),
        'referralcredits' => array('dis', 'Referral'), 'priceaccountdiscounts' => array('dis', 'Price Account DIscounts'),
        'storecreditredeemed' => array('dis', 'Store Credit Redeemed'), 'prepaidredeemed' => array('dis', 'Prepaid'),
        'salestaxes' => array('adj', 'Sales Tax'), 'salestax' => array('adj', 'Sales Tax'),
        'cashpayouts' => array('adj', 'Paid Out'), 'paidout' => array('adj', 'Paid Out'),
        'cash' => array('rec', 'Cash'), 'cashsales' => array('rec', 'Cash'), 'netcash' => array('rec', 'Cash'),
        'creditcard' => array('rec', 'Credit Card'), 'creditcards' => array('rec', 'Credit Card'),
        'creditcardsales' => array('rec', 'Credit Card'), 'coins' => array('rec', 'Coins'),
        'giftcardredeemed' => array('rec', 'Gift Card Redeemed'),
        'salesbyvirtualterminal' => array('rec', 'Credit Card Ecommerce'),
        'zipsdeliveryfee' => array('sale', 'Delivery'), 'deliveryfee' => array('sale', 'Delivery'),
        'giftcardissued' => array('sale', 'Gift Card Issued'), 'prepaidissued' => array('sale', 'Prepaid'),
    );

    public function context($locationId, $date)
    {
        $location = DB::table('locations')->where('id', $locationId)->where('status', '1')->first();
        if (!$location || !$location->startdate || $location->startdate === '0000-00-00') {
            throw new RuntimeException('This store needs a valid start date before importing a weekly report.');
        }
        $day = \DateTime::createFromFormat('!Y-m-d', $date);
        if (!$day || $day->format('Y-m-d') !== $date) {
            throw new RuntimeException('Invalid report date.');
        }
        $weekStart = clone $day;
        $weekStart->modify('-' . (((int) $day->format('w') + 1) % 7) . ' days');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');
        // Exact legacy weekDataValue(end-of-week) formula used by manual AJAX.
        $storeStart = strtotime($location->startdate);
        if (!$storeStart || $date < $location->startdate) {
            throw new RuntimeException('The report date is before the store start date.');
        }
        $storeNo = (int) ((strtotime($weekEnd->format('Y-m-d')) - $storeStart) / 86400 / 7) + 1;
        $anchor = date('w', $storeStart) > 5 ? date('Y-m-d', strtotime('previous saturday', $storeStart)) : date('Y-m-d', $storeStart);
        $weekNo = (int) date('W', strtotime($anchor . ' +' . ($storeNo - 1) . ' week'));
        return array('location_id' => (int) $locationId, 'location' => $location->location,
            'location_code' => $location->location_code, 'dataDate' => $date, 'dataDay' => $day->format('l'),
            'week_start' => $weekStart->format('Y-m-d'), 'week_end' => $weekEnd->format('Y-m-d'),
            'weekNo' => $weekNo, 'storeNo' => $storeNo);
    }

    public function catalog(array $context)
    {
        $catalog = array();
        foreach ($this->groups as $group => $definition) {
            list($master, $assignment, $foreign, $status) = $definition;
            $fields = DB::table($master . ' as m')->join($assignment . ' as a', 'm.id', '=', 'a.' . $foreign)
                ->where('m.status', '1')->where('a.location', $context['location_id'])
                ->where(function ($query) use ($status, $context) {
                    $query->where('a.' . $status, '0')->orWhere(function ($temporary) use ($status, $context) {
                        $temporary->where('a.' . $status, '1')->where('a.storeWeekNo', $context['weekNo'])->where('a.calWeekNo', $context['storeNo']);
                    });
                })->select('m.id', 'm.name')->distinct()->orderBy('m.name')->get();
            foreach ($fields as $field) {
                $key = $group . '_' . $field->id;
                $catalog[$key] = array('key' => $key, 'group' => $group, 'id' => (int) $field->id, 'name' => $field->name);
            }
        }
        return $catalog;
    }

    public function sources(array $report)
    {
        $sources = array();
        foreach ($report['categories'] as $category) {
            $sources[] = array('name' => $category['name'], 'section' => 'Sales category', 'group' => 'sale',
                'unit' => $this->number($category['total_units']), 'amount' => $this->number($category['total_amount']),
                'pdf_amount' => $this->number($category['total_amount']));
        }
        $seen = array();
        foreach (array('summary' => 'Summary', 'employee_totals' => 'Employee Total') as $section => $label) {
            foreach ($report[$section] as $item) {
                $key = $this->key($item['name']);
                if (in_array($key, array('grosssales', 'grosssalestotal', 'royaltynetsales', 'netsales', 'drawertotal', 'overshort'), true)) {
                    continue; // Derived totals are never ledger fields.
                }
                $alias = isset($this->aliases[$key]) ? $this->aliases[$key] : null;
                $identity = $alias ? implode('|', $alias) : $key;
                if (isset($seen[$identity])) {
                    // Summary and employee totals repeat the same money.
                    if (abs($seen[$identity] - $item['amount']) > 0.005) {
                        throw new RuntimeException('Repeated PDF totals disagree for ' . $item['name']);
                    }
                    continue;
                }
                $seen[$identity] = $item['amount'];
                // Preserve signed PDF amounts for review; existing manual
                // discounts store positive deductions (UI subtracts them).
                $amount = $alias && $alias[0] === 'dis' ? -$item['amount'] : $item['amount'];
                if ($alias && $alias[0] === 'dis' && $amount < 0) {
                    throw new RuntimeException('Unexpected positive discount in PDF: ' . $item['name']);
                }
                if (!$alias && abs($amount) < 0.005) {
                    continue;
                }
                $sources[] = array('name' => $item['name'], 'section' => $label,
                    'group' => $alias ? $alias[0] : null, 'unit' => '', 'amount' => $this->number($amount),
                    'pdf_amount' => $this->number($item['amount']));
            }
        }
        return $sources;
    }

    public function suggestedTarget(array $source, array $catalog)
    {
        $matches = array();
        foreach ($catalog as $target) {
            if ($this->compatibleTarget($source, $target)) {
                $matches[] = $target['key'];
            }
        }
        return count($matches) === 1 ? $matches[0] : '';
    }

    public function compatibleTarget(array $source, array $target)
    {
        $key = $this->key($source['name']);
        $alias = isset($this->aliases[$key]) ? $this->aliases[$key] : null;
        if ($alias) {
            return $target['group'] === $alias[0] && $this->key($target['name']) === $this->key($alias[1]);
        }
        return (!$source['group'] || $source['group'] === $target['group']) && $this->key($target['name']) === $key;
    }

    public function plan(array $sources, array $mapping, array $catalog, array $context)
    {
        if (count($mapping) !== count($sources)) {
            throw new RuntimeException('Review every PDF row before importing.');
        }
        $rows = array();
        foreach ($sources as $index => $source) {
            $key = isset($mapping[$index]) ? $mapping[$index] : '';
            if ($key === 'skip' && abs((float) $source['amount']) < 0.005 && ($source['unit'] === '' || (float) $source['unit'] == 0)) {
                continue;
            }
            if (!is_string($key) || !isset($catalog[$key])) {
                throw new RuntimeException('Select a configured field for ' . $source['name'] . '. Only zero rows can be omitted.');
            }
            $target = $catalog[$key];
            if (!$this->compatibleTarget($source, $target)) {
                throw new RuntimeException('Incorrect daily entry field for ' . $source['name'] . '. Configure the matching field for this store before importing.');
            }
            if (isset($rows[$key])) {
                throw new RuntimeException('Two PDF rows map to ' . $target['name'] . '. They must not be combined silently.');
            }
            $amount = $target['group'] === 'dis' ? $this->number(-(float) $source['pdf_amount']) : $source['amount'];
            if ($target['group'] === 'dis' && (float) $amount < 0) {
                throw new RuntimeException('A positive PDF amount cannot be saved as a discount: ' . $source['name']);
            }
            $rows[$key] = array('nameDep' => $target['group'], 'nameId' => $target['id'],
                'dataDate' => $context['dataDate'], 'dataDay' => $context['dataDay'],
                'unit' => $source['unit'], 'amount' => $amount,
                'weekNo' => $context['weekNo'], 'storeNo' => $context['storeNo'],
                'location_id' => $context['location_id']);
        }
        if (!count($rows)) {
            throw new RuntimeException('No daily values are available to import.');
        }
        return array_values($rows);
    }

    public function snapshot(array $context)
    {
        $rows = DB::table('salesections')->where('location_id', $context['location_id'])
            ->where('dataDate', $context['dataDate'])->orderBy('id')->get();
        return hash('sha256', json_encode($rows));
    }

    public function save(array $pending, array $mapping, $userId, $replace)
    {
        return DB::transaction(function () use ($pending, $mapping, $userId, $replace) {
            // Serialize imports for this store, including imports into empty days.
            $location = DB::table('locations')->where('id', $pending['context']['location_id'])->lockForUpdate()->first();
            if (!$location || !$location->status || !DB::table('users')->where('id', $userId)->where('status', '1')->exists() ||
                !DB::table('user_locations')->where('user', $userId)->where('location', $location->id)->exists()) {
                throw new RuntimeException('Your store access changed. No data was saved.');
            }
            $context = $this->context($location->id, $pending['context']['dataDate']);
            if ($context !== $pending['context']) {
                throw new RuntimeException('Store settings changed. Upload the report again.');
            }
            DB::table('salesections')->where('location_id', $location->id)->where('dataDate', $context['dataDate'])->lockForUpdate()->get();
            if ($this->snapshot($context) !== $pending['snapshot']) {
                throw new RuntimeException('Daily data changed after this preview. Upload again to review the latest values.');
            }
            $rows = $this->plan($pending['sources'], $mapping, $this->catalog($context), $context);
            $metrics = app(SalesReportDailyMetrics::class)->row($pending['daily_metrics'] ?? array(), $context);
            if ($metrics) $rows[] = $metrics;
            $result = array('inserted' => 0, 'updated' => 0, 'unchanged' => 0);
            foreach ($rows as $row) {
                // Match the natural daily identity without relying on legacy
                // inconsistent week numbers; never insert another duplicate.
                $existing = Salesection::where('location_id', $location->id)->where('dataDate', $context['dataDate'])
                    ->where('nameDep', $row['nameDep'])->where('nameId', $row['nameId'])->get();
                if (count($existing) > 1) {
                    throw new RuntimeException('Duplicate daily records already exist. Resolve them before importing.');
                }
                if (!count($existing)) {
                    $row['comment'] = '';
                    Salesection::create($row);
                    $result['inserted']++;
                    continue;
                }
                $record = $existing[0];
                $values = array('amount' => $row['amount']);
                if ($row['nameDep'] === 'daily_metrics') {
                    $values = array();
                    foreach (SalesReportDailyMetrics::fields() as $field => $label) {
                        if ($row[$field] !== null) $values[$field] = $row[$field];
                    }
                } elseif ($row['unit'] !== '') {
                    $values['unit'] = $row['unit'];
                }
                $changed = false;
                foreach ($values as $field => $value) {
                    if ($record->$field === null || (string) $record->$field === '' || abs((float) $record->$field - (float) $value) > 0.005) {
                        $changed = true;
                    }
                }
                if (!$changed) {
                    $result['unchanged']++;
                    continue;
                }
                if (!$replace) {
                    throw new RuntimeException('This day already has different values. Review the existing data and explicitly select replacement before importing.');
                }
                $values['weekNo'] = $row['weekNo'];
                $values['storeNo'] = $row['storeNo'];
                $values['dataDay'] = $row['dataDay'];
                $record->update($values); // Preserve existing comments and other fields.
                $result['updated']++;
            }
            return $result;
        });
    }

    private function number($value)
    {
        if (!is_numeric($value) || !is_finite((float) $value) || abs($value) > 999999999.99) {
            throw new RuntimeException('Invalid numeric value in PDF.');
        }
        return number_format((float) $value, 2, '.', '');
    }

    private function key($text)
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($text));
    }
}
