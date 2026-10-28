@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-heading">
                    <h3 class="heading">EDIT EMPLOYEE
                    <a class="v-btn v-btn-primary pull-right" href="{{ url('/employee') }}">BACK</a>
                    <a class="v-btn v-btn-primary pull-right" href="{{ url('/employee/create') }}">CREATE NEW EMPLOYEE</a>
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

            <div class="col-sm-8">
                <div class="widget">
                    <div class="widget-content">
                        {!! Form::model($employee, array('route' => ['employee.update', 'employee' => $employee->id], 'method' => 'PUT')) !!}
                        {{ csrf_field() }}



                            <div class="widget-content">
								<div class="row form-group{{ $errors->has('empname') ? ' has-error' : '' }}">
									<div class="col-sm-12">
										<label for="empname">NAME</label>
									
										{!! Form::text('empname', null, ['class' => 'form-control']) !!}
	                                    @if ($errors->has('empname'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('empname') }}</strong>
	                                        </span>
	                                    @endif
									</div>							
								</div>
								<div class="row form-group{{ $errors->has('empemail') ? ' has-error' : '' }}">
									<div class="col-sm-12">
										<label>EMAIL</label>
										
										{!! Form::text('empemail', null, ['class' => 'form-control']) !!}
	                                    @if ($errors->has('empemail'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('empemail') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								<div class="row form-group{{ $errors->has('	empaddress') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>ADDRESS</label>
										
										{!! Form::text('empaddress', null, ['class' => 'form-control']) !!}										
	                                    @if ($errors->has('empaddress'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('	empaddress') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								<div class="row form-group{{ $errors->has('paymentrate') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>PAYMENT RATE PER HOUR</label>
										
										{!! Form::text('paymentrate', null, ['class' => 'form-control']) !!}
	                                    @if ($errors->has('paymentrate'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('	paymentrate') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								<?php $empId = $employee['emplocation'];?>
                                <div class="row form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                    <div class="col-sm-12">
                                        <label>SELECT LOCATION</label>
                                        @foreach($locations as $id => $location)		
											<div class="checkbox">
												<label>
												<input type="checkbox" class="check" name="emplocation" value="{{ $id }}" <?php if($id==$empId){ echo "checked";}?> >{{ $location }}
												</label>
											
											</div>
                                        @endforeach

                                        @if ($errors->has('location'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('location') }}</strong>
                                            </span>
                                        @endif
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
</section>

<script>
 $('.check').click(function() {
        $('.check').not(this).prop('checked', false);
    });
</script>
</script>
@endsection
