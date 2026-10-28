@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left"></i>
							BACK
							</a>
						 VENDOR
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="{{ url('/seller/create') }}">NEW VENDOR</a>
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
			<div class="col-sm-12">
				<div class="widget">
					<div class="widget-content">
							<div class="responsive-table">
								<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
							        <thead>
							            <tr>
							                <th>NAME</th>
							                <th>EMAIL</th>
							                <th>PHONE</th>
							                <th>DELIVERY DAY</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($sellers as $user)
			                            <tr>
			                                <td>{{ $user->name }}</td>
			                                <td>{{ $user->email }}</td>
			                                <td>{{ $user->phone }}</td>
											<td>
												@php
													$deliveryDays = $user->seller_delivery_days
														? explode(',', $user->seller_delivery_days)
														: [];
												@endphp

												{{ collect($deliveryDays)->map(function ($day) use ($days) {
													return $days[(int) $day] ?? null;
												})->filter()->implode(', ') }}
											</td>
			                                <!-- <td>{{ $days[$user->seller_delivery_days] or '' }}</td> -->
			                                <td>
			                                    {!! Form::open(array('route' => ['seller.destroy', $user->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
			                                        {{ link_to_route('seller.edit','',[$user->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
			                                        |
													{{ link_to_route('seller.vArchive', $user->status == 1 ? 'Archive' : 'Unarchive', ['id' => $user->id, 'st' => $user->status]) }}
													

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
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#example').DataTable();
	} );
	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
