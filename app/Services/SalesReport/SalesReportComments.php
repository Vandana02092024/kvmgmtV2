<?php

namespace App\Services\SalesReport;

use App\Salesection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReportComments
{
    public function handle(Request $request, $save)
    {
        $data = validator($request->all(), [
            'nameId' => ['required', 'string', 'regex:/^(?:(sale|dis|adj|rec)_[0-9]+|daily_metrics_0)$/'],
            'metric_field' => ['required_if:nameId,daily_metrics_0', 'string', \Illuminate\Validation\Rule::in(array_keys(SalesReportDailyMetrics::fields()))],
            'popdayname' => ['required', 'string', 'max:40'],
            'popupweekNo' => ['required', 'integer', 'min:1'],
            'popupStorNo' => ['required', 'integer', 'min:1'],
            'expected_location_id' => ['sometimes', 'required', 'integer'],
            'message' => [$save ? 'present' : 'sometimes', 'nullable', 'string', 'max:10000'],
            'expected_comment' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ])->validate();

        return DB::transaction(function () use ($data, $save) {
            $locationId = session('location_id');
            $location = DB::table('locations')->where('id', $locationId)->where('status', '1')->lockForUpdate()->first();
            abort_unless($location && Auth::user() && (string) Auth::user()->status === '1' &&
                DB::table('user_locations')->where('user', Auth::id())->where('location', $locationId)->exists(), 403);
            abort_if(isset($data['expected_location_id']) && (int) $data['expected_location_id'] !== (int) $locationId,
                409, 'The selected store changed. Reload this report before saving.');
            abort_unless(preg_match('/^(Saturday|Sunday|Monday|Tuesday|Wednesday|Thursday|Friday)\s+(\d{2}-\d{2}-\d{4})$/', trim($data['popdayname']), $parts), 422, 'Invalid comment date.');
            $date = \DateTime::createFromFormat('!m-d-Y', $parts[2]);
            abort_unless($date && $date->format('m-d-Y') === $parts[2] && $date->format('l') === $parts[1], 422, 'Invalid comment date.');
            $importer = app(SalesReportPdfImporter::class);
            $context = $importer->context($locationId, $date->format('Y-m-d'));
            abort_unless((int) $data['popupweekNo'] === $context['weekNo'] && (int) $data['popupStorNo'] === $context['storeNo'], 409, 'Report week changed. Reload this report.');
            $isMetric = $data['nameId'] === 'daily_metrics_0';
            if ($isMetric) {
                $target = ['group' => 'daily_metrics', 'id' => 0];
            } else {
                abort_if(isset($data['metric_field']), 422, 'Customer activity comments must use their dedicated fields.');
                $catalog = $importer->catalog($context);
                abort_unless(isset($catalog[$data['nameId']]), 422, 'This sales field is not configured for this store and week.');
                $target = $catalog[$data['nameId']];
            }
            $records = Salesection::where('location_id', $locationId)->where('dataDate', $context['dataDate'])
                ->when(!$isMetric, function ($query) use ($context) { $query->where('storeNo', $context['storeNo']); })
                ->where('nameDep', $target['group'])
                ->where('nameId', $target['id'])->lockForUpdate()->get();
            abort_if(count($records) > 1, 409, 'Duplicate sales rows must be resolved before editing this comment.');
            $stored = count($records) ? (string) $records[0]->comment : '';
            $metricComments = $isMetric ? SalesReportDailyMetrics::comments($stored) : [];
            abort_if($metricComments === null, 409, 'An existing customer activity note needs review before separate input comments can be saved. No changes were made.');
            $current = $isMetric ? ($metricComments[$data['metric_field']] ?? '') : $stored;
            if ($save) {
                abort_if(array_key_exists('expected_comment', $data) && (string) $data['expected_comment'] !== $current && (string) ($data['message'] ?? '') !== $current,
                    409, 'This comment was changed in another tab or by another user. Copy your draft, then reload the saved comment.');
                $message = (string) ($data['message'] ?? '');
                $stored = $message;
                if ($isMetric) {
                    if ($message === '') unset($metricComments[$data['metric_field']]);
                    else $metricComments[$data['metric_field']] = $message;
                    $stored = $metricComments ? json_encode($metricComments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) : '';
                    abort_if(strlen($stored) > 65535, 422, 'The combined customer activity comments are too long. Shorten the note before saving.');
                }
                if (count($records)) {
                    $records[0]->update(['comment' => $stored]);
                } elseif ($message !== '') {
                    Salesection::create(['location_id' => $locationId, 'nameDep' => $target['group'], 'nameId' => $target['id'],
                        'dataDate' => $context['dataDate'], 'dataDay' => $context['dataDay'], 'weekNo' => $context['weekNo'],
                        'storeNo' => $context['storeNo'], 'unit' => '', 'amount' => '', 'comment' => $stored]);
                }
                $current = $message;
            }
            return ['comment' => $current, 'saved' => (bool) $save];
        });
    }
}
