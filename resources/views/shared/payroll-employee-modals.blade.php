<div class="modal fade payroll-employee-modal" id="payrollEmployeeAddModal" tabindex="-1" role="dialog" aria-labelledby="payrollEmployeeAddTitle">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="payrollEmployeeAddTitle">Add payroll employee</h4></div>
        <form method="POST" action="{{ url('/payrollemployee') }}">{{ csrf_field() }}
            <div class="modal-body"><div class="payroll-employee-grid">
                <div class="form-group"><label>Employee name</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                <div class="form-group"><label>Salary</label><input class="form-control" name="salary" value="{{ old('salary') }}"></div>
                <div class="form-group"><label>Hourly rate</label><input class="form-control" name="rateperhour" value="{{ old('rateperhour', $rate) }}"></div>
                <div class="form-group"><label>Sorting number</label><input class="form-control" type="number" name="sorting_order" value="{{ old('sorting_order') }}"></div>
                <div class="form-group"><label>Departments</label>
                    @php $selectedDepartments = old('department', []); if (!is_array($selectedDepartments)) $selectedDepartments = [$selectedDepartments]; @endphp
                    <div class="payroll-employee-picker"><button type="button" class="payroll-employee-picker-toggle"><span>Select departments</span><i class="fa fa-chevron-down"></i></button><div class="payroll-employee-picker-menu"><input type="search" placeholder="Search departments"><div class="payroll-employee-picker-options">
                        @foreach($departments as $departmentId => $departmentName)<label><input type="checkbox" name="department[]" value="{{ $departmentId }}" @if(in_array((string) $departmentId, array_map('strval', $selectedDepartments), true)) checked @endif> {{ $departmentName }}</label>@endforeach
                    </div></div></div>
                </div>
                <div class="form-group"><label>Locations</label>
                    @php $selectedLocations = old('location', []); if (!is_array($selectedLocations)) $selectedLocations = [$selectedLocations]; @endphp
                    <div class="payroll-employee-picker"><button type="button" class="payroll-employee-picker-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button><div class="payroll-employee-picker-menu"><input type="search" placeholder="Search locations"><div class="payroll-employee-picker-options">
                        @foreach($locations as $locationId => $locationName)<label><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $selectedLocations), true)) checked @endif> {{ $locationName }}</label>@endforeach
                    </div></div></div>
                </div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn payroll-employee-submit">Add employee</button></div>
        </form>
    </div></div>
</div>

@foreach($payrollemployees as $employee)
    @php $selectedDepartments = isset($departmentSelections[$employee->id]) ? $departmentSelections[$employee->id] : []; $selectedLocations = isset($locationSelections[$employee->id]) ? $locationSelections[$employee->id] : []; @endphp
    <div class="modal fade payroll-employee-modal" id="payrollEmployeeEditModal{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="payrollEmployeeEditTitle{{ $employee->id }}">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="payrollEmployeeEditTitle{{ $employee->id }}">Edit payroll employee</h4></div>
            <form method="POST" action="{{ url('/payrollemployee/' . $employee->id) }}">{{ csrf_field() }}{{ method_field('PUT') }}
                <div class="modal-body"><div class="payroll-employee-grid">
                    <div class="form-group"><label>Employee name</label><input class="form-control" name="name" value="{{ $employee->name }}" required></div>
                    <div class="form-group"><label>Salary</label><input class="form-control" name="salary" value="{{ $employee->salary }}"></div>
                    <div class="form-group"><label>Hourly rate</label><input class="form-control" name="rateperhour" value="{{ $employee->rateperhour }}"></div>
                    <div class="form-group"><label>Sorting number</label><input class="form-control" type="number" name="sorting_order" value="{{ $employee->sorting_order }}"></div>
                    <div class="form-group"><label>Departments</label><div class="payroll-employee-picker"><button type="button" class="payroll-employee-picker-toggle"><span>Select departments</span><i class="fa fa-chevron-down"></i></button><div class="payroll-employee-picker-menu"><input type="search" placeholder="Search departments"><div class="payroll-employee-picker-options">
                        @foreach($departments as $departmentId => $departmentName)<label><input type="checkbox" name="department[]" value="{{ $departmentId }}" @if(in_array((string) $departmentId, array_map('strval', $selectedDepartments), true)) checked @endif> {{ $departmentName }}</label>@endforeach
                    </div></div></div></div>
                    <div class="form-group"><label>Locations</label><div class="payroll-employee-picker"><button type="button" class="payroll-employee-picker-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button><div class="payroll-employee-picker-menu"><input type="search" placeholder="Search locations"><div class="payroll-employee-picker-options">
                        @foreach($locations as $locationId => $locationName)<label><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $selectedLocations), true)) checked @endif> {{ $locationName }}</label>@endforeach
                    </div></div></div></div>
                </div></div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn payroll-employee-submit">Save changes</button></div>
            </form>
        </div></div>
    </div>
@endforeach

<style>
.payroll-employee-modal{z-index:15000!important}.modal-backdrop{z-index:14990!important}
.payroll-employee-modal .modal-dialog{width:min(650px,calc(100% - 24px));margin:7vh auto 20px}
.payroll-employee-modal .modal-content{overflow:visible;border:1px solid #dce5ef;border-radius:12px;background:#fff;box-shadow:0 18px 48px rgba(15,35,61,.24)}
.payroll-employee-modal .modal-header{position:relative;padding:10px 20px;border:0;color:#fff;background:linear-gradient(112deg, #0d2747de 0%, #1E5A6B 100%);border-radius:11px 11px 0 0}
.payroll-employee-modal .modal-title{margin:0 30px 4px 0;color:#fff;font-size:17px;font-weight:700}
.payroll-employee-modal .modal-header p{margin:0;color:#cfdaea;font-size:12px}
.payroll-employee-modal .modal-header .close{position:absolute;top:12px;right:16px;color:#fff;font-size:25px;opacity:.85;text-shadow:none}
.payroll-employee-modal .modal-body{padding:18px 20px}
.payroll-employee-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}
.payroll-employee-grid .form-group{min-width:0;margin:0}
.payroll-employee-grid label{display:block;margin-bottom:6px;color:#526780;font-size:11px;font-weight:700;text-transform:uppercase}
.payroll-employee-grid .form-control,.payroll-employee-picker-toggle{height:38px;border:1px solid #d1dce9;border-radius:6px;box-shadow:none}
.payroll-employee-picker-field{grid-column:1/-1}
.payroll-employee-picker{position:relative}
.payroll-employee-picker-toggle{display:flex;align-items:center;justify-content:space-between;width:100%;padding:8px 11px;color:#344e6b;background:#fff;text-align:left}
.payroll-employee-picker-toggle span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.payroll-employee-picker-menu{position:absolute;z-index:1320;top:calc(100% + 4px);right:0;left:0;display:none;padding:8px;border:1px solid #d5dfeb;border-radius:7px;background:#fff;box-shadow:0 10px 24px rgba(16,39,68,.16)}.payroll-employee-picker.is-open .payroll-employee-picker-menu{display:block}.payroll-employee-picker-menu>input{width:100%;height:34px;margin-bottom:6px;padding:6px 9px;border:1px solid #d7e0eb;border-radius:5px}.payroll-employee-picker-options{max-height:150px;overflow-y:auto}.payroll-employee-picker-options label{display:block;margin:0;padding:7px 8px;border-radius:4px;color:#334d68;font-size:12px;font-weight:400;text-transform:none;cursor:pointer}.payroll-employee-picker-options label:hover{background:#f1f5f9}.payroll-employee-picker-options input{margin:0 7px 0 0;vertical-align:middle}.payroll-employee-modal .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 20px;border-top:1px solid #e6edf4;border-radius:0 0 11px 11px;background:#f8fafc}.payroll-employee-modal .modal-footer .btn{min-height:36px;margin:0;padding:8px 14px;border-radius:7px;font-size:11px;font-weight:700}.payroll-employee-modal .payroll-employee-submit{border:1px solid #f2bd3b;color:#17263b;background:#f2bd3b}.payroll-employee-modal .payroll-employee-submit:hover,.payroll-employee-modal .payroll-employee-submit:focus{border-color:#ffd15e;color:#17263b;background:#ffd15e}@media(max-width:560px){.payroll-employee-grid{grid-template-columns:1fr}.payroll-employee-grid .payroll-employee-picker-field{grid-column:auto}}
.payroll-add-trigger,.payroll-add-trigger:hover,.payroll-add-trigger:focus{border-color:#f2bd3b!important;color:#17263b!important;background:#f2bd3b!important}.payroll-add-trigger:hover,.payroll-add-trigger:focus{border-color:#ffd15e!important;background:#ffd15e!important}
</style>
<script>
$(function(){ $('.payroll-employee-picker').each(function(){payrollEmployeeSummary($(this));}); @if($openAddModal) $('#payrollEmployeeAddModal').appendTo('body').modal('show'); @endif @if($openEditId) $('#payrollEmployeeEditModal{{ $openEditId }}').appendTo('body').modal('show'); @endif });
function payrollEmployeeSummary(picker){var selected=picker.find('.payroll-employee-picker-options input:checked').map(function(){return $(this).closest('label').text().trim();}).get();var summary=selected.length?selected.slice(0,2).join(', '):'Select '+(picker.find('input[name="department[]"]').length?'departments':'locations');if(selected.length>2)summary+=' +'+(selected.length-2);picker.find('.payroll-employee-picker-toggle span').text(summary);}
$(document).on('click','.payroll-employee-picker-toggle',function(){var picker=$(this).closest('.payroll-employee-picker');$('.payroll-employee-picker').not(picker).removeClass('is-open');picker.toggleClass('is-open');});$(document).on('click',function(event){if(!$(event.target).closest('.payroll-employee-picker').length)$('.payroll-employee-picker').removeClass('is-open');});$(document).on('input','.payroll-employee-picker-menu>input',function(){var query=$(this).val().toLowerCase();$(this).siblings('.payroll-employee-picker-options').find('label').each(function(){$(this).toggle($(this).text().toLowerCase().indexOf(query)!==-1);});});$(document).on('change','.payroll-employee-picker-options input',function(){payrollEmployeeSummary($(this).closest('.payroll-employee-picker'));});
</script>
