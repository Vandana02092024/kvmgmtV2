@extends('layouts.layout')

@section('content')
<section id="sitemap" class="sitemap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<ul class="breadcrumb">
				    <li><a href="dashboard.php">MENU</a></li>
				    <li class="active captilize">Edit vendor</li>
				</ul>
			</div>
		</div>
	</div>
</section>
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading"> <a class="v-btn v-btn-primary " style="margin-right:8px" href="{{ url('/menu }}"><i class="fa fa-arrow-left " style="margin-right: 5px"> </i>BACK</a>Edit vendor 
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/seller/create') }}">Create New Vendor</a>
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
						{!! Form::model($vendor, array('route' => ['vendor.update', 'vendor' => $vendor->id], 'method' => 'PUT')) !!}
        				{{ csrf_field() }}
							<div class="widget-content">
								<div class="row form-group{{ $errors->has('name') ? ' has-error' : '' }}">
									<div class="col-sm-12">
										<label for="name">Name</label>
										{!! Form::text('name', null, ['class' => 'form-control']) !!}

	                                    @if ($errors->has('name'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('name') }}</strong>
	                                        </span>
	                                    @endif
									</div>							
								</div>
								<div class="row form-group{{ $errors->has('email') ? ' has-error' : '' }}">
									<div class="col-sm-12">
										<label>Email</label>
										{!! Form::text('email', null, ['class' => 'form-control']) !!}

	                                    @if ($errors->has('email'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('email') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								<div class="row form-group{{ $errors->has('phone') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>Phone</label>
										{!! Form::text('phone', null, ['class' => 'form-control']) !!}

	                                    @if ($errors->has('phone'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('phone') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>

								<div class="row form-group{{ $errors->has('address') ? ' has-error' : '' }}">							
									<div class="col-sm-12">
										<label>Address</label>
										{!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2]) !!}

	                                    @if ($errors->has('address'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('address') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								
								
							</div>
							<div class="widget-footer">
								<button class="v-btn v-btn-primary">Update</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
