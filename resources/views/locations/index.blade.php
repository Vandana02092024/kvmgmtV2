@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading"> 
						<!-- <a class="v-btn v-btn-primary pull-right" style="margin-right:8px" href="{{ url('/menu') }}">
							<i class="fa fa-arrow-left " style="margin-right: 5px"> </i>
							BACK
						</a>  -->
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left"></i>
							BACK
						</a>
						LOCATIONS
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="#addLocationModal" data-toggle="modal">NEW LOCATION</a>
						<a href="{{url('/asign_loaction')}}"class="v-btn v-btn-primary pull-right" > Menu Asign</a>
					</h3>
				</div>
				<br />
			</div>
		</div>

		@if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif


		<div class="row">
			<div class="col-sm-12 col-xs-12">
				<div class="widget">
					<div class="widget-content">
							<div class="responsive-table">
								<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>RANK</th>
							                <th>LOCATION CODE</th>
							                <th>LOCATION</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($locations as $location)
			                            <tr>
			                                <td>{{ $location->rank }}</td>
			                                <td>{{ $location->location_code }}</td>
			                                <td>{{ $location->location }}</td>
											<td>
			                                    {!! Form::open(array('route' => ['location.destroy', $location->id], 'method'=>'DELETE', 'class' => 'delete')) !!}

	                                        <a href="#editLocationModal{{ $location->id }}" class="fa fa-pencil" data-toggle="modal" data-placement="left" title="Edit location" aria-label="Edit {{ $location->location }}"></a>

													|

													{{ link_to_route('location.archive', $location->status == 1 ? 'Archive' : 'Unarchive', ['id' => $location->id, 'st' => $location->status]) }}
													|
													<a href="{{url('/asign_loaction/'.$location->id)}}"> Menu Asign</a>

			                                        <!-- |
			                                        {{ Form::button('',['class'=>'fa fa-close','type'=>'submit','data-toggle'=>"tooltip", 'data-placement'=>"right", 'title'=>"delete"]) }} -->
												{!! Form::close() !!}
			                                </td>
			                            </tr>
			                            @endforeach
							        </tbody>
							    </table>
							</div>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade kv-location-modal" id="addLocationModal" tabindex="-1" role="dialog" aria-labelledby="addLocationTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form method="POST" action="{{ url('/location') }}">
						{{ csrf_field() }}
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="addLocationTitle">Add Location</h4>
						</div>
						<div class="modal-body">
							<div class="kv-location-fields">
								<div class="form-group{{ $errors->has('location_code') ? ' has-error' : '' }}">
									<label for="new-location-code">LOCATION CODE</label>
									<input id="new-location-code" type="text" name="location_code" class="form-control" value="{{ old('location_code') }}" required>
									@if ($errors->has('location_code'))<span class="help-block">{{ $errors->first('location_code') }}</span>@endif
								</div>
								<div class="form-group{{ $errors->has('location') ? ' has-error' : '' }}">
									<label for="new-location-name">LOCATION</label>
									<input id="new-location-name" type="text" name="location" class="form-control" value="{{ old('location') }}" required>
									@if ($errors->has('location'))<span class="help-block">{{ $errors->first('location') }}</span>@endif
								</div>
								<div class="form-group{{ $errors->has('startdate') ? ' has-error' : '' }}">
									<label for="new-location-startdate">START DATE</label>
									<input id="new-location-startdate" type="text" name="startdate" class="form-control kv-location-date" value="{{ old('startdate') }}" autocomplete="off" placeholder="Select date">
									@if ($errors->has('startdate'))<span class="help-block">{{ $errors->first('startdate') }}</span>@endif
								</div>
								<div class="form-group">
									<label for="new-location-rank">RANK</label>
									<input id="new-location-rank" type="text" name="rank" class="form-control" value="{{ old('rank') }}">
								</div>
								<div class="form-group">
									<label for="new-location-rate">RATE</label>
									<input id="new-location-rate" type="text" name="rate" class="form-control" value="{{ old('rate') }}">
								</div>
								<div class="form-group">
									<label for="new-location-entity">ENTITY</label>
									<input id="new-location-entity" type="text" name="entity_name" class="form-control" value="{{ old('entity_name') }}">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn kv-location-cancel" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn kv-location-submit">Add Location</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		@foreach($locations as $location)
			@php $salesReportCount = isset($salesReportCounts[$location->id]) ? $salesReportCounts[$location->id] : 0; @endphp
			<div class="modal fade kv-location-modal" id="editLocationModal{{ $location->id }}" tabindex="-1" role="dialog" aria-labelledby="editLocationTitle{{ $location->id }}">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<form method="POST" action="{{ url('/location/' . $location->id) }}">
							{{ csrf_field() }}
							{{ method_field('PUT') }}
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="editLocationTitle{{ $location->id }}">Edit Location</h4>
							</div>
							<div class="modal-body">
								<div class="kv-location-fields">
									<div class="form-group">
										<label for="location-code-{{ $location->id }}">LOCATION CODE</label>
										<input id="location-code-{{ $location->id }}" type="text" name="location_code" class="form-control" value="{{ $location->location_code }}" required>
									</div>
									<div class="form-group">
										<label for="location-name-{{ $location->id }}">LOCATION</label>
										<input id="location-name-{{ $location->id }}" type="text" name="location" class="form-control" value="{{ $location->location }}" required>
									</div>
									<div class="form-group">
										<label for="location-startdate-{{ $location->id }}">START DATE</label>
										<input id="location-startdate-{{ $location->id }}" type="text" name="startdate" class="form-control kv-location-date" value="{{ $location->startdate }}" autocomplete="off" placeholder="Select date" @if($salesReportCount > 0) readonly aria-describedby="location-startdate-note-{{ $location->id }}" @endif>
										@if($salesReportCount > 0)<small class="kv-location-note" id="location-startdate-note-{{ $location->id }}">Start date is locked after sales data is added.</small>@endif
									</div>
									<div class="form-group">
										<label for="location-rank-{{ $location->id }}">RANK</label>
										<input id="location-rank-{{ $location->id }}" type="text" name="rank" class="form-control" value="{{ $location->rank }}">
									</div>
									<div class="form-group">
										<label for="location-rate-{{ $location->id }}">RATE</label>
										<input id="location-rate-{{ $location->id }}" type="text" name="rate" class="form-control" value="{{ $location->rate }}">
									</div>
									<div class="form-group">
										<label for="location-entity-{{ $location->id }}">ENTITY</label>
										<input id="location-entity-{{ $location->id }}" type="text" name="entity_name" class="form-control" value="{{ $location->entity_name }}">
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn kv-location-cancel" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn kv-location-submit">Save Changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endforeach
	</div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
	    $('#example').DataTable({
			"aaSorting": []
		});
	$('.kv-location-date').not('[readonly]').each(function() {
			$(this).datetimepicker({ format: 'YYYY-MM-DD', maxDate: 'now' });
		});
		@if (isset($openAddLocationModal) && $openAddLocationModal)
			$('#addLocationModal').modal('show');
		@endif
		@if (isset($openEditLocationId))
			$('#editLocationModal{{ $openEditLocationId }}').modal('show');
		@endif
	} );
	@if ($errors->has('location_code') || $errors->has('location'))
		$('#addLocationModal').modal('show');
	@endif
	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this location?");
    });
</script>
@endsection
