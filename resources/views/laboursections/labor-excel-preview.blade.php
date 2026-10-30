@extends('layouts.layout')
@section('content')
<section class="wrap"><div class="container-fluid">
    <h3>Review Labor Excel</h3>
    @if($errors->any())
        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="alert alert-info">
        <strong>{{ $pending['context']['location_name'] }}</strong> · {{ $pending['context']['start_date'] }} to {{ $pending['context']['end_date'] }}<br>
        File: {{ $pending['filename'] }} · Selected sheet: <strong>{{ $pending['report']['sheet_name'] }}</strong> / {{ $pending['report']['sheet_count'] }} tabs · {{ $pending['report']['employee_count'] }} employees.<br>
        Workbook weekly groups map left to right to store weeks {{ $pending['context']['start_week'] }} and {{ $pending['context']['end_week'] }}.
        Confirm the calendar period yourself: weekly labels in this workbook do not establish dates or store identity.
    </div>
    @if($planError)<div class="alert alert-warning">{{ $planError }}</div>@endif
    @if($pending['report']['errors'])
        <div class="alert alert-danger"><strong>Workbook validation failed. Correct the file before importing.</strong><ul>@foreach($pending['report']['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <p><strong>Employee Information:</strong> the name column identifies the employee; the immediately following column is that employee's hourly pay rate. Each row's weekly hours/salary are saved under that employee's ID. Existing employees must match the Excel name and rate. For a missing employee, review the <strong>Create employee</strong> option: the Excel name, rate and equal weekly salary will create the employee and its selected store/department assignments. Existing employee masters are not changed.</p>
    <form method="POST" action="{{ route('labor-excel.import') }}">
        {{ csrf_field() }}<input type="hidden" name="token" value="{{ $pending['token'] }}">
        <div class="table-responsive"><table class="table table-bordered table-condensed">
            <thead><tr><th rowspan="2">Excel department / employee</th><th rowspan="2">Assigned department / employee</th><th rowspan="2">Excel hourly rate</th>
                @foreach($pending['report']['weeks'] as $i => $week)<th colspan="5">{{ $week['label'] }} → store week {{ $pending['context']['start_week'] + $i }}</th>@endforeach
                <th rowspan="2">Notes (reference only)</th></tr><tr>
                @foreach($pending['report']['weeks'] as $week)<th>Regular hours</th><th>OT hours</th><th>Holiday hours</th><th>Salary</th><th>Pay total</th>@endforeach
            </tr></thead><tbody>
            @foreach($sources as $index => $source)
                <tr><td>{{ $source['department'] }} / <strong>{{ $source['employee']['name'] }}</strong><br><small>Excel row {{ $source['employee']['source_row'] }}</small></td>
                    <td><select name="mapping[{{ $index }}]" class="form-control" style="min-width:300px" required>
                        <option value="">Select matching employee / department</option>
                        @foreach($employeeOptions[$index] as $key => $target)
                            <option value="{{ $key }}" {{ ($mapping[$index] ?? '') === $key ? 'selected' : '' }}>{{ !empty($target['new_employee']) ? 'Create employee: ' : 'Existing: ' }}{{ $target['department'] }} / {{ $target['name'] }}{{ empty($target['new_employee']) ? ' (#'.$target['empId'].')' : '' }} — rate {{ $target['hourly_rate'] }}, salary {{ $target['salary'] ?: 0 }}/week</option>
                        @endforeach
                    </select>@if(!$employeeOptions[$index])<small class="text-danger">This name already exists in the selected store but has no active eligible assignment. Review its status and store/department assignments in Payroll.</small>@endif
                    @if(isset($employeeOptions[$index][$mapping[$index] ?? '']))
                        <?php $selectedEmployee = $employeeOptions[$index][$mapping[$index]]; ?>
                        @if(empty($selectedEmployee['new_employee']) && $source['employee']['hourly_rate'] !== null && abs((float)$selectedEmployee['hourly_rate'] - $source['employee']['hourly_rate']) > .00001)
                            <small class="text-danger">Rate mismatch: Excel {{ $source['employee']['hourly_rate'] }}, existing employee {{ $selectedEmployee['hourly_rate'] }}. Import is blocked until reviewed; the master rate will not be overwritten.</small>
                        @endif
                    @endif</td><td>{{ $source['employee']['hourly_rate'] ?? 'Salary only (no hourly rate)' }}</td>
                    @foreach($source['employee']['weeks'] as $week)
                        <td>{{ $week['regular_hours'] }}</td><td>{{ $week['overtime_hours'] }}</td><td>{{ $week['holiday_hours'] }}</td><td>{{ number_format($week['salary_amount'], 2) }}</td><td>{{ number_format($week['total_amount'], 2) }}</td>
                    @endforeach
                    <td>{{ $source['employee']['notes'] }}</td>
                </tr>
            @endforeach
            </tbody><tfoot><tr><th colspan="3">Excel totals</th>
                @foreach($pending['report']['totals']['weeks'] as $week)
                    <th>{{ $week['regular_hours'] }}</th><th>{{ $week['overtime_hours'] }}</th><th>{{ $week['holiday_hours'] }}</th><th>{{ number_format($week['salary_amount'], 2) }}</th><th>{{ number_format($week['total_amount'], 2) }}</th>
                @endforeach
                <th>Grand total {{ number_format($pending['report']['totals']['grand']['total_amount'], 2) }}</th>
            </tr></tfoot>
        </table></div>
        @if($existing->count())
            <div class="alert alert-warning">This period already has {{ $existing->count() }} saved fields. Only mapped employee/week Regular, Overtime, Holiday and Salary fields are imported. Other employees, Sick and Vacation entries remain and can make the final period total differ from the Excel total.</div>
            <details><summary>Review existing saved fields before replacing</summary><div class="table-responsive"><table class="table table-bordered table-condensed"><thead><tr><th>Department</th><th>Employee</th><th>Week</th><th>Category</th><th>Saved hours / amount</th></tr></thead><tbody>
                @foreach($existing as $row)<tr><td>{{ $row->department_name }}</td><td>{{ $row->employee_name }}</td><td>{{ $row->weekNo }}</td><td>{{ $row->category_name }}</td><td>{{ $row->rate }}</td></tr>@endforeach
            </tbody></table></div></details>
            <div class="checkbox"><label><input type="checkbox" name="replace_existing" value="1" {{ old('replace_existing') ? 'checked' : '' }}> Allow replacement of differing matching fields with Excel values, including zero values.</label></div>
        @endif
        @if(collect($employeeOptions)->flatten(1)->contains(function ($target) { return !empty($target['new_employee']); }))
            <div class="checkbox"><label><input type="checkbox" name="create_employees" value="1" {{ old('create_employees') ? 'checked' : '' }}> Create missing employees selected above using their Excel name, hourly rate and weekly salary, and assign them to this store and selected department.</label></div>
        @endif
        <div class="checkbox"><label><input type="checkbox" name="reviewed" value="1" required> I verified the store, calendar period, weekly order, employee mappings, rates, salaries and totals.</label></div>
        <p>Excel notes and calculated totals are shown for review. Existing payroll tables store category hours/amounts; the period total is calculated from employee masters using the manual report rules.</p>
        <button class="btn btn-primary" type="submit" {{ $pending['report']['errors'] || count($pending['report']['weeks']) !== 2 ? 'disabled' : '' }}>Import Labor Excel</button>
    </form>
    <form method="POST" action="{{ route('labor-excel.cancel') }}" style="margin-top:10px">{{ csrf_field() }}<input type="hidden" name="token" value="{{ $pending['token'] }}"><button class="btn btn-default" type="submit">Cancel import</button></form>
</div></section>
@endsection
