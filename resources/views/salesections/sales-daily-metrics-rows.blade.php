<tr class="thead-Light"><th colspan="13" style="text-align:center">CUSTOMER ACTIVITY (COUNTS)</th></tr>
@foreach(\App\Services\SalesReport\SalesReportDailyMetrics::fields() as $metricField => $metricLabel)
<tr class="metricColumn">
    <th>{{ $metricLabel }}</th>
    @foreach($dates as $metricIndex => $metricDate)
        @if($metricIndex !== 1)
        <td>
            <input type="number" min="0" max="2147483647" step="1" class="form-control enterTab sales-daily-metric"
                style="width:90px;font-size:12px" unit="count" dayno="day{{ $metricIndex + 1 }}"
                data-report-date="{{ $metricDate }}" data-metric-field="{{ $metricField }}" data-comment-label="{{ $metricLabel }}"
                aria-label="{{ $metricLabel }} {{ $metricDate }}" value="{{ $dailyMetrics[$metricDate][$metricField] ?? '' }}">
            <button type="button" class="sales-comment-trigger" data-sales-comment="{{ $dailyMetrics[$metricDate]['comments'][$metricField] ?? '' }}" aria-label="Add or view comment" title="Add or view comment"><i class="fa fa-comment-o" aria-hidden="true"></i></button>
        </td>
        <td style="border-right:2px solid #585454"></td>
        @endif
    @endforeach
</tr>
@endforeach
