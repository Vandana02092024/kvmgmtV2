<div class="modal fade payroll-form-modal" id="payrollAddModal" tabindex="-1" role="dialog" aria-labelledby="payrollAddTitle">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="payrollAddTitle">Add {{ $payrollLabel }}</h4></div>
        <form method="POST" action="{{ url('/' . $payrollRoute) }}">{{ csrf_field() }}
            <div class="modal-body"><div class="payroll-form-grid">
                <div class="form-group"><label>{{ $payrollLabel }} name</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
                @if($payrollHasMultiplier)<div class="form-group"><label>Pay rate multiplier</label><input class="form-control" name="pay_rate_multiplier" value="{{ old('pay_rate_multiplier') }}"></div>@endif
                <div class="form-group"><label>Sorting number</label><input class="form-control" type="number" name="sorting_order" value="{{ old('sorting_order') }}"></div>
                <div class="form-group"><label>Locations</label>
                    @php $selected = old('location', []); if (!is_array($selected)) $selected = [$selected]; @endphp
                    <div class="payroll-picker"><button type="button" class="payroll-picker-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button><div class="payroll-picker-menu"><input class="payroll-picker-search" type="search" placeholder="Search locations"><div class="payroll-picker-options">
                        @foreach($locations as $locationId => $locationName)<label><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $selected), true)) checked @endif> {{ $locationName }}</label>@endforeach
                    </div></div></div>
                </div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn payroll-submit">Add {{ $payrollLabel }}</button></div>
        </form>
    </div></div>
</div>

@foreach($payrollItems as $payrollItem)
    @php $selected = isset($locationSelections[$payrollItem->id]) ? $locationSelections[$payrollItem->id] : []; @endphp
    <div class="modal fade payroll-form-modal" id="payrollEditModal{{ $payrollItem->id }}" tabindex="-1" role="dialog" aria-labelledby="payrollEditTitle{{ $payrollItem->id }}">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="payrollEditTitle{{ $payrollItem->id }}">Edit {{ $payrollLabel }}</h4></div>
            <form method="POST" action="{{ url('/' . $payrollRoute . '/' . $payrollItem->id) }}">{{ csrf_field() }}{{ method_field('PUT') }}
                <div class="modal-body"><div class="payroll-form-grid">
                    <div class="form-group"><label>{{ $payrollLabel }} name</label><input class="form-control" name="name" value="{{ $payrollItem->name }}" required></div>
                    @if($payrollHasMultiplier)<div class="form-group"><label>Pay rate multiplier</label><input class="form-control" name="pay_rate_multiplier" value="{{ $payrollItem->pay_rate_multiplier }}"></div>@endif
                    <div class="form-group"><label>Sorting number</label><input class="form-control" type="number" name="sorting_order" value="{{ $payrollItem->sorting_order }}"></div>
                    <div class="form-group"><label>Locations</label><div class="payroll-picker"><button type="button" class="payroll-picker-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button><div class="payroll-picker-menu"><input class="payroll-picker-search" type="search" placeholder="Search locations"><div class="payroll-picker-options">
                        @foreach($locations as $locationId => $locationName)<label><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $selected), true)) checked @endif> {{ $locationName }}</label>@endforeach
                    </div></div></div></div>
                </div></div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn payroll-submit">Save changes</button></div>
            </form>
        </div></div>
    </div>
@endforeach

<style>
.payroll-form-modal{z-index:15000!important}.modal-backdrop{z-index:14990!important}
.payroll-form-modal .modal-dialog{width:min(600px,calc(100% - 24px));margin:7vh auto 20px}
.payroll-form-modal .modal-content{overflow:visible;border:1px solid #dce5ef;border-radius:12px;background:#fff;box-shadow:0 18px 48px rgba(15,35,61,.24)}
.payroll-form-modal .modal-header{position:relative;padding:10px 20px;border:0;color:#fff;background:linear-gradient(112deg, #0d2747de 0%, #1E5A6B 100%);border-radius:11px 11px 0 0}
.payroll-form-modal .modal-title{margin:0 30px 4px 0;color:#fff;font-size:17px;font-weight:700}
.payroll-form-modal .modal-header p{margin:0;color:#cfdaea;font-size:12px}
.payroll-form-modal .modal-header .close{position:absolute;top:12px;right:16px;color:#fff;font-size:25px;opacity:.85;text-shadow:none}.payroll-form-modal .modal-body{padding:18px 20px}.payroll-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}.payroll-form-grid .form-group{min-width:0;margin:0}.payroll-form-grid label{display:block;margin-bottom:6px;color:#526780;font-size:11px;font-weight:700;text-transform:uppercase}.payroll-form-grid .form-control,.payroll-picker-toggle{height:38px;border:1px solid #d1dce9;border-radius:6px;box-shadow:none}.payroll-location-field{grid-column:1/-1}.payroll-picker{position:relative}.payroll-picker-toggle{display:flex;align-items:center;justify-content:space-between;width:100%;padding:8px 11px;color:#344e6b;background:#fff;text-align:left}.payroll-picker-toggle span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.payroll-picker-menu{position:absolute;z-index:1320;top:calc(100% + 4px);right:0;left:0;display:none;padding:8px;border:1px solid #d5dfeb;border-radius:7px;background:#fff;box-shadow:0 10px 24px rgba(16,39,68,.16)}.payroll-picker.is-open .payroll-picker-menu{display:block}.payroll-picker-search{width:100%;height:34px;margin-bottom:6px;padding:6px 9px;border:1px solid #d7e0eb;border-radius:5px}.payroll-picker-options{max-height:170px;overflow-y:auto}.payroll-picker-options label{display:block;margin:0;padding:7px 8px;border-radius:4px;color:#334d68;font-size:12px;font-weight:400;text-transform:none;cursor:pointer}.payroll-picker-options label:hover{background:#f1f5f9}.payroll-picker-options input{margin:0 7px 0 0;vertical-align:middle}.payroll-form-modal .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 20px;border-top:1px solid #e6edf4;border-radius:0 0 11px 11px;background:#f8fafc}.payroll-form-modal .modal-footer .btn{min-height:36px;margin:0;padding:8px 14px;border-radius:7px;font-size:11px;font-weight:700}.payroll-form-modal .payroll-submit{border:1px solid #f2bd3b;color:#17263b;background:#f2bd3b}.payroll-form-modal .payroll-submit:hover,.payroll-form-modal .payroll-submit:focus{border-color:#ffd15e;color:#17263b;background:#ffd15e}@media(max-width:560px){.payroll-form-grid{grid-template-columns:1fr}.payroll-form-grid .payroll-location-field{grid-column:auto}}
.payroll-add-trigger,.payroll-add-trigger:hover,.payroll-add-trigger:focus{border-color:#f2bd3b!important;color:#17263b!important;background:#f2bd3b!important}.payroll-add-trigger:hover,.payroll-add-trigger:focus{border-color:#ffd15e!important;background:#ffd15e!important}
</style>
<script>
$(function(){
    $('.payroll-picker').each(function(){payrollPickerSummary($(this));});
    @if($openAddModal) $('#payrollAddModal').appendTo('body').modal('show'); @endif
    @if($openEditId) $('#payrollEditModal{{ $openEditId }}').appendTo('body').modal('show'); @endif
});
function payrollPickerSummary(picker){var selected=picker.find('.payroll-picker-options input:checked').map(function(){return $(this).closest('label').text().trim();}).get();var text=selected.length?selected.slice(0,2).join(', '):'Select locations';if(selected.length>2)text+=' +'+(selected.length-2);picker.find('.payroll-picker-toggle span').text(text);}
$(document).on('click','.payroll-picker-toggle',function(){var picker=$(this).closest('.payroll-picker');$('.payroll-picker').not(picker).removeClass('is-open');picker.toggleClass('is-open');});
$(document).on('click',function(event){if(!$(event.target).closest('.payroll-picker').length)$('.payroll-picker').removeClass('is-open');});
$(document).on('input','.payroll-picker-search',function(){var query=$(this).val().toLowerCase();$(this).siblings('.payroll-picker-options').find('label').each(function(){$(this).toggle($(this).text().toLowerCase().indexOf(query)!==-1);});});
$(document).on('change','.payroll-picker-options input',function(){payrollPickerSummary($(this).closest('.payroll-picker'));});
</script>
