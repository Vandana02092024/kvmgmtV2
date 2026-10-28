@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading">EDIT SUBTRACTION
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/subtraction') }}">BACK</a>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a>
                	<a class="v-btn v-btn-primary pull-right" href="{{ url('/subtraction/create') }}">CREATE NEW SUBTRACTION</a>
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
						{!! Form::model($subtraction, array('route' => ['subtraction.update', 'subtraction' => $subtraction->id], 'method' => 'PUT')) !!}
        				{{ csrf_field() }}
							<div class="widget-content">

								<div class="row form-group{{ $errors->has('subtraction') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>SUBTRACTION</label>
										{!! Form::textarea('subtractname', null, ['class' => 'form-control', 'rows' => 2]) !!}

	                                    @if ($errors->has('subtraction'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('subtraction') }}</strong>
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
@endsection
