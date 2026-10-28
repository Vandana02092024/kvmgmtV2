@extends('layouts.layout')

@section('content')
@php $settingsItemType = 'discount'; $settingsItemLabel = 'Discount'; $settingsItems = $discounts; @endphp
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class= "page-heading kv-page-hero">
					<h3 class="heading">
						 <!-- <a class="v-btn v-btn-primary pull-right " style="margin-right:8px" href="{{ url('/menu') }}">
							<i class="fa fa-arrow-left " style="margin-right: 5px"> </i>
							BACK
						</a> -->
						<a class="v-btn v-btn-primary pull-right"
							style="margin-right:8px"
							href="javascript:history.back();">
							<i class="fa fa-arrow-left"></i>
							BACK
						</a>
						DISCOUNT
						<!-- <a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">MENU</a> -->
						<a class="v-btn v-btn-primary pull-right" href="#addSettingsItemModal" data-toggle="modal">CREATE NEW DISCOUNT</a>
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
							                <th>DISCOUNT</th>
							                							                
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
							            @foreach($discounts as $discount)
			                            <tr>
			                                <td>{{ $discount->name }}</td>
			                                                               
			                                <td>
			                                    {!! Form::open(array('route' => ['discount.destroy', $discount->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
	                                        <a href="#editSettingsItemModal{{ $discount->id }}" class="fa fa-pencil" data-toggle="modal" data-placement="left" title="edit"></a>
													
													|
													
													{{ link_to_route('discount.archive', $discount->status == 1 ? 'Archive' : 'Unarchive', ['id' => $discount->id, 'st' => $discount->status]) }}
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
	</div>
	@include('shared.location-settings-modals')
</section>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#example').DataTable({
  "ordering": false
});
	} );

	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
