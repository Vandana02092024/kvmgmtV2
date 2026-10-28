@extends('layouts.layout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.css') }}" type="text/css">
<section id="dashboard-page" class="wrap kv-user-form-page">
    <div class="container-fluid">
        <div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">
                        <a class="v-btn v-btn-primary pull-right" style="margin-right:8px" href="{{ url('/user') }}"><i class="fa fa-arrow-left" style="margin-right:5px"></i>BACK</a>
                        EDIT USER
                        <a class="v-btn v-btn-primary pull-right" href="{{ url('/user/create') }}">CREATE NEW USER</a>
                    </h3>
				</div>
				<br />
			</div>
		</div>

        <div class="row">
            @if(Session::has('message'))
                <div class="col-sm-12 alert alert-success">{{ Session::get('message') }}</div>
            @endif

            <div class="col-sm-10 col-sm-offset-1">
                <div class="widget kv-user-create-card">
                    <div class="widget-content">
                        {!! Form::model($user, [
                            'url' => url('/user/' . $user->id),
                            'method' => 'PUT'
                        ]) !!}
                        {{ csrf_field() }}

                        <div class="kv-user-fields">
                            <div class="kv-user-field{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name">NAME</label>
                                {!! Form::text('name', old('name', $user->name), [ 'class' => 'form-control', 'id' => 'name']) !!}
                                @if ($errors->has('name'))<span class="help-block"><strong>{{ $errors->first('name') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email">EMAIL</label>
                                {!! Form::text('email', null, ['class' => 'form-control', 'id' => 'email', 'readonly' => 'true']) !!}
                                @if ($errors->has('email'))<span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field{{ $errors->has('phone') ? ' has-error' : '' }}">
                                <label for="phone">PHONE</label>
                                {!! Form::text('phone', null, ['class' => 'form-control', 'id' => 'phone']) !!}
                                @if ($errors->has('phone'))<span class="help-block"><strong>{{ $errors->first('phone') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field{{ $errors->has('role') ? ' has-error' : '' }}">
                                <label for="roleId">ROLE</label>
                                {{ Form::select('role', $roles, null, ['class' => 'form-control', 'onChange' => 'getVal()', 'id' => 'roleId']) }}
                                @if ($errors->has('role'))<span class="help-block"><strong>{{ $errors->first('role') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field kv-user-access-field{{ $errors->has('moduleList') ? ' has-error' : '' }}">
                                <label>MODULE ACCESS</label>
                                <div class="kv-user-checkbox-grid kv-user-module-grid">
                                    @foreach(['1' => 'Sales', '2' => 'Labour', '3' => 'Reporting'] as $moduleId => $moduleName)
                                        <label><input type="checkbox" name="moduleList[]" value="{{ $moduleId }}" @if(in_array($moduleId, old('moduleList', $module_data))) checked @endif> {{ $moduleName }}</label>
                                    @endforeach
                                </div>
                                @if ($errors->has('moduleList'))<span class="help-block"><strong>{{ $errors->first('moduleList') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field kv-user-claim-field{{ $errors->has('claim') ? ' has-error' : '' }}">
                                <label for="claim">CLAIM</label>
                                {!! Form::text('claim', null, ['class' => 'form-control', 'id' => 'claim']) !!}
                                @if ($errors->has('claim'))<span class="help-block"><strong>{{ $errors->first('claim') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field kv-user-location-field{{ $errors->has('email_location') ? ' has-error' : '' }}" id="emLoc">
                                <label>EMAIL LOCATION</label>
                                <select class="form-control kv-user-multiselect" name="email_location[]" multiple="multiple" data-placeholder="Select email locations">
                                    @foreach($locations as $id => $location)
                                        <option value="{{ $id }}" @if(in_array($id, old('email_location', array_values($selEmailLocations)))) selected @endif>{{ $location }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('email_location'))<span class="help-block"><strong>{{ $errors->first('email_location') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field kv-user-location-field{{ $errors->has('location') ? ' has-error' : '' }}">
                                <label>ADD LOCATIONS</label>
                                @if(count($selLocations))
                                    <div class="kv-user-assigned-locations" aria-label="Currently assigned locations">
                                        <span class="kv-user-assigned-label">Currently assigned</span>
                                        @foreach($locations as $id => $location)
                                            @if(($relationshipId = array_search($id, $selLocations)) !== false)
                                                <span class="kv-user-location-chip">
                                                    {{ $location }}
                                                    {!! link_to_route('resetlocation', '×', ['id' => $relationshipId, 'user' => $user->id], ['class' => 'kv-user-location-reset', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'Remove this location', 'aria-label' => 'Remove ' . $location]) !!}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <select class="form-control kv-user-multiselect" name="location[]" multiple="multiple" data-placeholder="Choose locations to add">
                                    @foreach($locations as $id => $location)
                                        @if(!in_array($id, array_values($selLocations)))
                                            <option value="{{ $id }}" @if(in_array($id, old('location', []))) selected @endif>{{ $location }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if ($errors->has('location'))<span class="help-block"><strong>{{ $errors->first('location') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field{{ $errors->has('npassword') ? ' has-error' : '' }}">
                                <label for="npassword">NEW PASSWORD</label>
                                <input id="npassword" type="password" class="form-control" name="npassword" autocomplete="new-password">
                                {!! Form::hidden('password', null, ['class' => 'form-control']) !!}
                                @if ($errors->has('npassword'))<span class="help-block"><strong>{{ $errors->first('npassword') }}</strong></span>@endif
                            </div>
                            <div class="kv-user-field{{ $errors->has('npassword_confirmation') ? ' has-error' : '' }}">
                                <label for="npassword-confirm">CONFIRM NEW PASSWORD</label>
                                <input id="npassword-confirm" type="password" class="form-control" name="npassword_confirmation" autocomplete="new-password">
                                @if ($errors->has('npassword_confirmation'))<span class="help-block"><strong>{{ $errors->first('npassword_confirmation') }}</strong></span>@endif
                            </div>
                        </div>

                        <div class="widget-footer kv-user-form-actions">
                            <button type="submit" class="v-btn v-btn-primary">UPDATE USER</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript" src="{{ asset('js/bootstrap-multiselect.js') }}"></script>
<script type="text/javascript">
$('.fa-close').on('click', function() {
    return confirm('Do you want to reset this location?');
});

$(document).ready(function() {
    $('.kv-user-multiselect').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        maxHeight: 240,
        numberDisplayed: 1,
        buttonText: function(options, select) {
            if (options.length === 0) {
                return $(select).data('placeholder') || 'Select locations';
            }
            return options.length === 1 ? options.text() : options.length + ' locations selected';
        },
        selectAllText: 'Select all locations'
    });

    updateEmailLocationVisibility();
});

function updateEmailLocationVisibility() {
    if ($('#roleId').val() === 'Owner') {
        $('#emLoc').show();
    } else {
        $('#emLoc').hide();
    }
}

function getVal() {
    updateEmailLocationVisibility();
}
</script>
@endsection
