@extends('layouts.layout')

@section('content')
<section id="sitemap" class="sitemap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<ul class="breadcrumb">
				    <li><a href="dashboard.php">MENU</a></li>
				    <li class="active captilize">Add vendors</li>
				</ul>
			</div>
		</div>
	</div>
</section>
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-6">
				<div class="page-heading">
					<h3 class="heading">Add vendor</h3>
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
						<form class="widget ng-pristine ng-valid" method="POST" action="{{ route('vendor.store') }}">
        				{{ csrf_field() }}
							<div class="widget-content">
								<div class="row form-group{{ $errors->has('name') ? ' has-error' : '' }}">
									<div class="col-sm-12">
										<label for="name">Name</label>
										<input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}">

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
										<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

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
										<input id="phone" type="phone" class="form-control" name="phone" value="{{ old('phone') }}">

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
										<textarea id="address" type="address" class="form-control" name="address" >{{ old('address') }}</textarea>

	                                    @if ($errors->has('address'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('address') }}</strong>
	                                        </span>
	                                    @endif
									</div>
								</div>
								
								
							</div>
							<div class="widget-footer">
								<button class="v-btn v-btn-primary">Add</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
