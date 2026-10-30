<tr class="thead-Light"><th colspan="13" style="text-align:center">CUSTOMER ACTIVITY (COUNTS)</th></tr>
@foreach(\App\Services\SalesReport\SalesReportDailyMetrics::fields() as $metricField => $metricLabel)
<tr class="metricColumn">
    <th>{{ $metricLabel }}</th>
    @foreach($dates as $metricIndex => $metricDate)
        @if($metricIndex !== 1)
        <td>
            <input type="number" min="0" max="2147483647" step="1" class="form-control enterTab sales-daily-metric"
                style="width:90px;font-size:12px" unit="count" dayno="day{{ $metricIndex + 1 }}"
                data-report-date="{{ $metricDate }}" data-metric-field="{{ $metricField }}"
                aria-label="{{ $metricLabel }} {{ $metricDate }}" value="{{ $dailyMetrics[$metricDate][$metricField] ?? '' }}">
        </td>
        <td style="border-right:2px solid #585454"></td>
        @endif
    @endforeach
</tr>
@endforeach
