<table class="table table-bordered" style="width:100%;font-size:11px;margin-bottom:12px">
    <caption>Customer Activity (Counts)</caption>
    <thead><tr><th>Activity</th>@foreach($dates as $metricDate)<th>{{ date('D m/d', strtotime($metricDate)) }}</th>@endforeach<th>Week total</th></tr></thead>
    <tbody>
    @foreach(\App\Services\SalesReport\SalesReportDailyMetrics::fields() as $metricField => $metricLabel)
        @php $metricTotal = 0; $metricHasValue = false; @endphp
        <tr><th>{{ $metricLabel }}</th>
        @foreach($dates as $metricDate)
            @php
                $metricValue = $dailyMetrics[$metricDate][$metricField] ?? null;
                if ($metricValue !== null) { $metricTotal += $metricValue; $metricHasValue = true; }
            @endphp
            <td>{{ $metricValue === null ? '—' : $metricValue }}</td>
        @endforeach
            <td>{{ $metricHasValue ? $metricTotal : '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
