@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading"> <a class="v-btn v-btn-primary " style="margin-right:8px" href="{{ url('/location') }}"><i class="fa fa-arrow-left " style="margin-right: 5px"> </i>BACK</a>EDIT LOCATION
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/location/create') }}">CREATE NEW LOCATION</a>
					</h3>
	                <br /> 
				</div>
			</div>
		</div>
		<div class="row">

			@if(Session::has('message'))
	            <div class="alert alert-success">
	                {{ Session::get('message') }}
	            </div>
	        @endif


			<div class="col-sm-8 col-sm-offset-2">
				<div class="widget">
					<div class="widget-content">
						{!! Form::model($location, array('route' => ['location.update', 'location' => $location->id], 'method' => 'PUT')) !!}
        				{{ csrf_field() }}
							<div class="widget-content">

								<div class="row form-group{{ $errors->has('location_code') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>LOCATION CODE</label>
										{!! Form::text('location_code', null, ['class' => 'form-control', 'rows' => 2]) !!}

	                                    @if ($errors->has('location_code'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('location_code') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>

								<div class="row form-group{{ $errors->has('location') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>LOCATION</label>
										{!! Form::textarea('location', null, ['class' => 'form-control', 'rows' => 2]) !!}
										@if ($errors->has('location'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('location') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								
								
								<div class="row form-group{{ $errors->has('startdate') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>START DATE</label>
										{!! Form::text('startdate', null, ['class' => 'form-control', 'id' => 'weeklyDatePicker', 'rows' => 2,'autocomplete' => 'off','readonly' => $salesectionsReportCount > 0 ? 'readonly' : null
											]) !!}
										@if($salesectionsReportCount > 0)
											<p class="text-danger" style="font-size:12px; "><strong>Note:- Once sales data is added, the start date of the location cannot be changed.</strong></p>
										@endif
										@if ($errors->has('startdate'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('startdate') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								
								<div class="row form-group">							
									<div class="col-sm-12">
										<label>RANK</label>
										{!! Form::text('rank', null, ['class' => 'form-control']) !!}
									</div>
								</div>

								<div class="row form-group">							
									<div class="col-sm-12">
										<label>RATE</label>
										{!! Form::text('rate', null, ['class' => 'form-control']) !!}
									</div>
								</div>

								<div class="row form-group">							
									<div class="col-sm-12">
										<label>ENTITY</label>
										{!! Form::text('entity_name', null, ['class' => 'form-control']) !!}   
									</div>
								</div>
								
							</div>
							<div class="widget-footer">
								<button class="v-btn v-btn-primary">UPDATE</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section><link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
 $("#weeklyDatePicker").datetimepicker({		
      format: 'YYYY-MM-DD',
	  maxDate: 'now'
   });
</script>
@endsection
