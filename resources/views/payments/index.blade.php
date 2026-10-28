@extends('layouts.layout')

@section('content')

<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading">VIEW PAYMENTS
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/menu') }}">BACK</a>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/dashboard') }}">DASHBOARD</a>
                	<a class="v-btn v-btn-primary pull-right" href="{{ url('/payment/create') }}">CREATE NEW PAYMENT</a>
					</h3>
	                <br /> 
				</div>
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
							                <th>Sr. No</th>
							                <th>PAYMENT</th>
							                <th>ACTION</th>
							            </tr>
							        </thead>
							        <tbody>
										<?php $i=0; ?>
							            @foreach($payments as $payment)
										<?php $i++; ?>
			                            <tr>
			                                <td><?php echo $i; ?></td>
			                                <td>{{ $payment->paymentname }}</td>
			                                <td>
			                                    {!! Form::open(array('route' => ['payment.destroy', $payment->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
			                                        
			                                        {{ link_to_route('payment.edit','',[$payment->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
			                                        
			                                        |   
													
													{{ link_to_route('payment.payArchive', $payment->status == 1 ? 'Archive' : 'Unarchive', ['id' => $payment->id, 'st' => $payment->status]) }}
													
													
													
			                                         |
			                                        {{ Form::button('',['class'=>'fa fa-close','type'=>'submit','data-toggle'=>"tooltip", 'data-placement'=>"right", 'title'=>"delete"]) }}

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
