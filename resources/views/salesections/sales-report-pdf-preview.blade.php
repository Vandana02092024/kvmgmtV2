@extends('layouts.layout')
@section('content')
<section class="wrap kv-weekly-ledger"><div class="container-fluid">
    <h3>Review Daily Sales PDF</h3>
    <p><strong>{{ $pending['context']['location_code'] }} — {{ $pending['context']['location'] }}</strong>
        | {{ $pending['context']['dataDay'] }}, {{ $pending['context']['dataDate'] }}
        | Week {{ $pending['context']['weekNo'] }} / Store week {{ $pending['context']['storeNo'] }}</p>
    <p>{{ $pending['filename'] }} ({{ $pending['report']['page_count'] }} pages)
        · <a href="{{ route('sales-pdf.file', $pending['token']) }}" target="_blank" rel="noopener">View original PDF</a></p>
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @foreach($pending['report']['warnings'] as $warning)<div class="alert alert-warning">{{ $warning }}</div>@endforeach
    <div class="alert alert-info">Review each PDF row against this store's daily entry field. Only matching configured fields are available. If a nonzero row has no matching field, update the store's sales settings and upload again. Discounts are positive deductions, just like manual entry. Gross sales, net sales and drawer totals are checks and are not added again. Unreadable fields remain unchanged.</div>
    <form action="{{ route('sales-pdf.import') }}" method="POST" id="sales-pdf-import-form">
        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{ $pending['token'] }}">
        <div class="table-responsive"><table class="table table-bordered table-striped" style="background:#fff">
            <thead><tr><th>PDF row</th><th>Units</th><th>PDF amount</th><th>Daily amount</th><th>Daily entry field</th><th>Existing daily value</th></tr></thead>
            <tbody>
            @foreach($pending['sources'] as $index => $source)
                <?php $selected = old('mapping.'.$index, $pending['mapping'][$index]); ?>
                <tr><td>{{ $source['name'] }}<br><small>{{ $source['section'] }}</small></td>
                    <td>{{ $source['unit'] }}</td><td>${{ $source['pdf_amount'] }}</td><td class="pdf-daily-amount" data-pdf-amount="{{ $source['pdf_amount'] }}">${{ $source['amount'] }}</td>
                    <td><select class="form-control pdf-mapping" name="mapping[{{ $index }}]" required style="min-width:210px">
                        <option value="">Select field…</option>
                        @if((float)$source['amount'] == 0 && ($source['unit'] === '' || (float)$source['unit'] == 0))
                            <option value="skip" {{ $selected === 'skip' ? 'selected' : '' }}>Leave unchanged (zero row)</option>
                        @endif
                        @foreach(['sale' => 'Sales', 'dis' => 'Discounts', 'adj' => 'Adjustments', 'rec' => 'Receipts'] as $group => $label)
                            @if(!$source['group'] || $source['group'] === $group)
                            <optgroup label="{{ $label }}">
                                @foreach($catalog as $key => $field)
                                    @if($field['group'] === $group && app(\App\Services\SalesReport\SalesReportPdfImporter::class)->compatibleTarget($source, $field))
                                    <option value="{{ $key }}" {{ $selected === $key ? 'selected' : '' }}>{{ $label }} — {{ $field['name'] }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                            @endif
                        @endforeach
                    </select></td><td class="pdf-existing"></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @if(!empty($pending['daily_metrics']))
        <h4>Customer Activity (Counts)</h4>
        <p>Counts are stored once for this store and day. They do not change sales or receipt amounts.</p>
        <table class="table table-bordered" style="background:#fff"><thead><tr><th>Activity</th><th>PDF count</th><th>Existing count</th></tr></thead><tbody>
        @foreach(\App\Services\SalesReport\SalesReportPdfReader::activityFields() as $metricField => $metricLabel)
            <tr><th>{{ $metricLabel }}</th><td>{{ isset($pending['daily_metrics'][$metricField]) ? $pending['daily_metrics'][$metricField] : 'Not read - unchanged' }}</td>
                <td>{{ $existingByField['daily_metrics_0'][0][$metricField] ?? 'Blank' }}</td></tr>
        @endforeach
        </tbody></table>
        @endif
        <p><strong>Verified PDF totals:</strong>
            @foreach($pending['report']['summary'] as $item)
                @if(in_array(strtolower($item['name']), ['gross sales', 'royalty net sales', 'net sales']))
                    {{ $item['name'] }}: ${{ number_format($item['amount'], 2) }} &nbsp;
                @endif
            @endforeach
        </p>
        <div class="checkbox"><label><input type="checkbox" name="replace_existing" value="1" {{ old('replace_existing') ? 'checked' : '' }}> Replace different existing values for the mapped fields on this day. Other fields and comments stay unchanged.</label></div>
        <div class="checkbox"><label><input type="checkbox" name="reviewed" value="1" required> I checked the store, PDF date, amounts, mapping and any missing receipt warnings.</label></div>
        <button class="btn btn-primary" type="submit" id="sales-pdf-import-button">Import Reviewed Daily Values</button>
        <p class="help-block">Preview expires after 30 minutes. Any validation failure cancels the complete import.</p>
    </form>
    <form action="{{ route('sales-pdf.cancel') }}" method="POST" style="margin-top:12px">
        {{ csrf_field() }}<input type="hidden" name="token" value="{{ $pending['token'] }}">
        <button class="btn btn-default" type="submit">Cancel Import</button>
    </form>
</div></section>
<script>
(function () {
    var existing = {!! json_encode($existingByField, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    $('.pdf-mapping').on('change', function () {
        var rows = existing[this.value] || [];
        var text = this.value === 'skip' ? 'Unchanged' : 'No existing value';
        if (rows.length > 1) text = 'Duplicate records — import blocked';
        if (rows.length === 1) text = 'Units: ' + (rows[0].unit || 'blank') + ' / Amount: ' + (rows[0].amount || 'blank');
        $(this).closest('tr').find('.pdf-existing').text(text);
        var amount = $(this).closest('tr').find('.pdf-daily-amount');
        var pdfAmount = parseFloat(amount.attr('data-pdf-amount'));
        amount.text('$' + (this.value.indexOf('dis_') === 0 ? -pdfAmount : pdfAmount).toFixed(2));
    }).trigger('change');
    $('#sales-pdf-import-form').on('submit', function () {
        $('#sales-pdf-import-button').prop('disabled', true).text('Importing…');
    });
}());
</script>
@endsection
