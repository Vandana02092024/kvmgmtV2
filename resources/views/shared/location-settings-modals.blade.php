<div class="modal fade kv-location-modal" id="addSettingsItemModal" tabindex="-1" role="dialog" aria-labelledby="addSettingsItemTitle">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addSettingsItemTitle">Add {{ $settingsItemLabel }}</h4>
        </div>
        <form method="POST" action="{{ url('/' . $settingsItemType) }}">
            {{ csrf_field() }}
            <div class="modal-body">
                <div class="kv-settings-fields">
                    <div class="form-group"><label for="add-settings-name">{{ $settingsItemLabel }}</label><input id="add-settings-name" class="form-control" name="name" value="{{ old('name') }}" required></div>
                    <div class="form-group"><label for="add-settings-rank">Sorting number</label><input id="add-settings-rank" class="form-control" type="number" name="sorting_order" value="{{ old('sorting_order') }}"></div>
                    <div class="form-group kv-settings-location-field">
                        <label>Locations</label>
                        @php $addSelectedLocations = old('location', []); if (!is_array($addSelectedLocations)) $addSelectedLocations = [$addSelectedLocations]; @endphp
                        <div class="kv-location-multiselect">
                            <button type="button" class="kv-location-multiselect-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button>
                            <div class="kv-location-multiselect-menu">
                                <input type="search" class="kv-location-search" placeholder="Search locations">
                                <label class="kv-location-option kv-location-select-all"><input type="checkbox" class="kv-location-select-all-input"> Select all locations</label>
                                <div class="kv-location-options">
                                    @foreach($locations as $locationId => $locationName)
                                        <label class="kv-location-option"><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $addSelectedLocations), true)) checked @endif> {{ $locationName }}</label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn kv-settings-submit">Add {{ $settingsItemLabel }}</button></div>
        </form>
    </div></div>
</div>

@foreach($settingsItems as $settingsItem)
    @php $editSelectedLocations = isset($locationSelections[$settingsItem->id]) ? $locationSelections[$settingsItem->id] : []; @endphp
    <div class="modal fade kv-location-modal" id="editSettingsItemModal{{ $settingsItem->id }}" tabindex="-1" role="dialog" aria-labelledby="editSettingsItemTitle{{ $settingsItem->id }}">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editSettingsItemTitle{{ $settingsItem->id }}">Edit {{ $settingsItemLabel }}</h4>
            </div>
            <form method="POST" action="{{ url('/' . $settingsItemType . '/' . $settingsItem->id) }}">
                {{ csrf_field() }}{{ method_field('PUT') }}
                <div class="modal-body">
                    <div class="kv-settings-fields">
                        <div class="form-group"><label for="edit-settings-name-{{ $settingsItem->id }}">{{ $settingsItemLabel }}</label><input id="edit-settings-name-{{ $settingsItem->id }}" class="form-control" name="name" value="{{ $settingsItem->name }}" required></div>
                        <div class="form-group"><label for="edit-settings-rank-{{ $settingsItem->id }}">Sorting number</label><input id="edit-settings-rank-{{ $settingsItem->id }}" class="form-control" type="number" name="sorting_order" value="{{ $settingsItem->sorting_order }}"></div>
                        <div class="form-group kv-settings-location-field">
                            <label>Locations</label>
                            <div class="kv-location-multiselect">
                                <button type="button" class="kv-location-multiselect-toggle"><span>Select locations</span><i class="fa fa-chevron-down"></i></button>
                                <div class="kv-location-multiselect-menu">
                                    <input type="search" class="kv-location-search" placeholder="Search locations">
                                    <label class="kv-location-option kv-location-select-all"><input type="checkbox" class="kv-location-select-all-input"> Select all locations</label>
                                    <div class="kv-location-options">
                                        @foreach($locations as $locationId => $locationName)
                                            <label class="kv-location-option"><input type="checkbox" name="location[]" value="{{ $locationId }}" @if(in_array((string) $locationId, array_map('strval', $editSelectedLocations), true)) checked @endif> {{ $locationName }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn kv-settings-submit">Save changes</button></div>
            </form>
        </div></div>
    </div>
@endforeach

<style>
    .kv-location-modal { z-index: 1300 !important; }
    .kv-location-modal .modal-dialog { width: min(620px, calc(100% - 24px)); margin: 7vh auto 20px; }
    .kv-location-modal .modal-content { overflow: visible; border: 1px solid #dce5ef; border-radius: 12px; box-shadow: 0 18px 48px rgba(15,35,61,.24); }
    .kv-location-modal .modal-header { position: relative; padding: 10px 20px; border: 0; color: #fff; background: linear-gradient(112deg,#122b49,#1b3d68); border-radius: 11px 11px 0 0; }
    .kv-location-modal .modal-title { margin: 0 32px 4px 0; color: #fff; font-size: 17px; font-weight: 700; }
    .kv-location-modal .modal-header p { margin: 0; color: #cfdaea; font-size: 11px; }
    .kv-location-modal .modal-header .close { position: absolute; top: 12px; right: 16px; color: #fff; font-size: 25px; opacity: .85; text-shadow: none; }
    .kv-location-modal .modal-body { padding: 18px 20px; }
    .kv-settings-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px 16px; }
    .kv-settings-fields .form-group { min-width: 0; margin: 0; }
    .kv-settings-fields .form-group > label { display: block; margin-bottom: 6px; color: #526780; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .kv-settings-fields .form-control { height: 38px; border: 1px solid #d1dce9; border-radius: 6px; box-shadow: none; }
    .kv-settings-fields .kv-settings-location-field { grid-column: 1 / -1; }
    .kv-location-multiselect { position: relative; }
    .kv-location-multiselect-toggle { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 40px; padding: 8px 11px; border: 1px solid #d1dce9; border-radius: 6px; color: #344e6b; background: #fff; text-align: left; }
    .kv-location-multiselect-toggle span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .kv-location-multiselect-menu { position: absolute; z-index: 1320; top: calc(100% + 4px); right: 0; left: 0; display: none; padding: 8px; border: 1px solid #d5dfeb; border-radius: 7px; background: #fff; box-shadow: 0 10px 24px rgba(16,39,68,.16); }
    .kv-location-multiselect.is-open .kv-location-multiselect-menu { display: block; }
    .kv-location-search { width: 100%; height: 34px; margin-bottom: 6px; padding: 6px 9px; border: 1px solid #d7e0eb; border-radius: 5px; }
    .kv-location-options { max-height: 180px; overflow-y: auto; }
    .kv-location-option { display: block; margin: 0; padding: 7px 8px; border-radius: 4px; color: #334d68; font-size: 12px; font-weight: 400; cursor: pointer; }
    .kv-location-option:hover { background: #f1f5f9; }
    .kv-location-option input { margin: 0 7px 0 0; vertical-align: middle; }
    .kv-location-select-all { margin-bottom: 4px; border-bottom: 1px solid #e9eef4; font-weight: 600; }
    .kv-location-modal .modal-footer { display: flex; justify-content: flex-end; gap: 8px; padding: 13px 20px; border-top: 1px solid #e6edf4; border-radius: 0 0 11px 11px; background: #f8fafc; }
    .kv-location-modal .modal-footer .btn { min-height: 36px; margin: 0; padding: 8px 14px; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .kv-location-modal .kv-settings-submit { border: 1px solid #f2bd3b; color: #17263b; background: #f2bd3b; }
    .kv-location-modal .kv-settings-submit:hover,
    .kv-location-modal .kv-settings-submit:focus { border-color: #ffd15e; color: #17263b; background: #ffd15e; }
    @media (max-width: 560px) { .kv-settings-fields { grid-template-columns: 1fr; } .kv-settings-fields .kv-settings-location-field { grid-column: auto; } }
</style>

<script>
    $(function () {
        $('.kv-location-multiselect').each(function () { updateLocationSummary($(this)); });
        @if($openAddModal || $errors->any()) $('#addSettingsItemModal').modal('show'); @endif
        @if($openEditId) $('#editSettingsItemModal{{ $openEditId }}').modal('show'); @endif
    });
    function updateLocationSummary(widget) {
        var selected = widget.find('.kv-location-options input[type="checkbox"]:checked').map(function () { return $(this).closest('label').text().trim(); }).get();
        var checked = selected.length;
        var total = widget.find('.kv-location-options input[type="checkbox"]').length;
        var summary = checked === 0 ? 'Select locations' : selected.slice(0, 2).join(', ');
        if (checked > 2) summary += ' +' + (checked - 2);
        widget.find('.kv-location-multiselect-toggle span').text(summary);
        widget.find('.kv-location-select-all-input').prop('checked', total > 0 && checked === total);
    }
    $(document).on('click', '.kv-location-multiselect-toggle', function () {
        var current = $(this).closest('.kv-location-multiselect');
        $('.kv-location-multiselect').not(current).removeClass('is-open');
        current.toggleClass('is-open');
    });
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.kv-location-multiselect').length) $('.kv-location-multiselect').removeClass('is-open');
    });
    $(document).on('input', '.kv-location-search', function () {
        var query = $(this).val().toLowerCase();
        $(this).siblings('.kv-location-options').find('.kv-location-option').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(query) !== -1);
        });
    });
    $(document).on('change', '.kv-location-options input[type="checkbox"]', function () { updateLocationSummary($(this).closest('.kv-location-multiselect')); });
    $(document).on('change', '.kv-location-select-all-input', function () {
        var widget = $(this).closest('.kv-location-multiselect');
        widget.find('.kv-location-options input[type="checkbox"]:visible').prop('checked', this.checked);
        updateLocationSummary(widget);
    });
</script>
