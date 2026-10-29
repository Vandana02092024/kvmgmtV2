@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading kv-page-hero kv-claims-hero">
					<h3 class="heading">
						<span class="kv-hero-title"><i class="fa fa-file-text-o"></i><span>Claim Forms</span></span>
						<a class="v-btn v-btn-primary kv-claim-primary-action" href="{{ url('/reimbursement/create') }}"><i class="fa fa-plus"></i> New Claim</a>
					
						<form class="widget-new ng-pristine ng-valid" id="vendorSelForm" method="POST" action="{{ url('/reimbursement/filter') }}">
						{{ csrf_field() }}
						
						<div class="row kv-claims-filter">

							<div class="col-sm-12">
								<div class="widget-content">
									<div class="row form-group">
										<label class="col-sm-1">STATUS</label>
										<div class="col-sm-3">
											<?php $status_ary = array('All' => 'All', 'Payment Pending' => 'Payment Pending', 'Approved Pending' => 'Approved Pending', 'Pending' => 'Pending', 'Paid' => 'Paid') ?>
											
											{{ Form::select('status', $status_ary, null,['class' => 'form-control', 'id' => 'sellerid']) }}
										</div>

										<div class="col-sm-8">
											<input type="submit" name="" value="Filter" class="v-btn v-btn-primary pull-right">
											<a href="{{ url('reimbursement') }}" class="v-btn v-btn-primary pull-right">Clear</a>
										</div>
									</div>
									
								</div>
							</div>
						</div>
						</form>
					</h3>
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

					

                	</form>
					<div class="widget-content">
						<table id="example" class="table table-striped table-bordered kv-claims-table" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>Reimbursement Id</th>
									<th>Customer Name</th>
									<th>Submitted Date</th>
									<th>Submitted By</th>
									<th>Payment Type</th>
									<th>Status</th>
									<th>ACTION</th>
								</tr>
							</thead>
							<tbody>
								
								@foreach($reimburses as $reimburse)
								@php
								$val = "CF-".$reimburse->location_code."-".str_pad($reimburse->id, 4, '0', STR_PAD_LEFT);
								@endphp
									<?php 
										if ($reimburse->resolution == 'storeCredit'){
												$resValue = ucfirst($reimburse->resolution);
												$resPos = 4;
												$begin = substr($resValue, 0, $resPos+1);
												$end = substr($resValue, $resPos+1);
												$resolution = $begin." ".$end;
										}else{
												$resolution = ucfirst($reimburse->resolution);
										}
									?>										
							
								@php
								$claimValue = $reimburse->proof_purchase;
								$approvedAmount = $reimburse->approved_amount;
								@endphp
								
								<?php 	
								/*	$originalClaimValue = Auth::user()->claim;	
									$loginuser = Auth::user()->role;		
									if($loginuser == 'Owner') {												
										if($reimburse->resolution == ''){
											$status = 'Pending';
										}elseif($reimburse->resolution == 'check' && ($reimburse->check_amount != '') &&($reimburse->credit_amount != '') && ($reimburse->resolution_check != '')){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'storeCredit' && ($reimburse->resolution_store_credit_amount !='') && ($reimburse->resolution_store_credit_added != '') && ($reimburse->resolution_store_credit_check_date != '')){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'checkstorecredit' && ($reimburse->resolution_check_store_credit_added != '') && ($reimburse->resolution_check_store_credit_check != '') && ($reimburse->resolution_check_store_credit_check_date != '') && ($reimburse->resolution_check_store_credit_check_credit_number != '' )  && ($reimburse->resolution_check_store_credit_added_amount != '') && ($reimburse->resolution_check_store_credit_check_credit_amount != '')){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'cash' && ($reimburse->resolution_cash_paid_amount != '') && ($reimburse->resolution_cash_paid != '') && ($reimburse->resolution_cash_paid_date != '')){
											$status = 'Paid';
										}
										else {
											$status = 'Payment Pending';
										}
									}
									
									if($loginuser == 'Supervisor' || $loginuser == 'Employee') {	
												$status = 'Pending';
											if($originalClaimValue > $reimburse->approved_amount){
												if($reimburse->resolution == ''){
												$status = 'Pending';
												}elseif($reimburse->resolution == 'check' && ($reimburse->check_amount != '') &&($reimburse->credit_amount != '') && ($reimburse->resolution_check != '')){
												$status = 'Paid';
												}elseif($reimburse->resolution == 'storeCredit' && ($reimburse->resolution_store_credit_amount !='') && ($reimburse->resolution_store_credit_added != '') && ($reimburse->resolution_store_credit_check_date != '')){
												$status = 'Paid';
												}elseif($reimburse->resolution == 'checkstorecredit' && ($reimburse->resolution_check_store_credit_added != '') && ($reimburse->resolution_check_store_credit_check != '') && ($reimburse->resolution_check_store_credit_check_date != '') && ($reimburse->resolution_check_store_credit_check_credit_number != '' )  && ($reimburse->resolution_check_store_credit_added_amount != '') && ($reimburse->resolution_check_store_credit_check_credit_amount != '')){
												$status = 'Paid';
												}elseif($reimburse->resolution == 'cash' && ($reimburse->resolution_cash_paid_amount != '') && ($reimburse->resolution_cash_paid != '') && ($reimburse->resolution_cash_paid_date != '')){
												$status = 'Paid';
												}
												else{
												$status = 'Payment Pending';
												}
											} else if ($originalClaimValue < $reimburse->approved_amount) {
												$status = 'Pending';
												if($reimburse->resolution == ''){
												$status = 'Pending';
												}elseif($reimburse->resolution == 'check' && ($reimburse->check_amount != '') &&($reimburse->credit_amount != '') && ($reimburse->resolution_check != '')){
													$status = 'Paid';
												}elseif($reimburse->resolution == 'storeCredit' && ($reimburse->resolution_store_credit_amount !='') && ($reimburse->resolution_store_credit_added != '') && ($reimburse->resolution_store_credit_check_date != '')){
													$status = 'Paid';
												}elseif($reimburse->resolution == 'checkstorecredit' && ($reimburse->resolution_check_store_credit_added != '') && ($reimburse->resolution_check_store_credit_check != '') && ($reimburse->resolution_check_store_credit_check_date != '') && ($reimburse->resolution_check_store_credit_check_credit_number != '' )  && ($reimburse->resolution_check_store_credit_added_amount != '') && ($reimburse->resolution_check_store_credit_check_credit_amount != '')){
													$status = 'Paid';
												}elseif($reimburse->resolution == 'cash' && ($reimburse->resolution_cash_paid_amount != '') && ($reimburse->resolution_cash_paid != '') && ($reimburse->resolution_cash_paid_date != '')){
													$status = 'Paid';
												}
												else{
													$status = 'Approval Pending';
												}
											}
									}*/
										
										/* else if($reimburse->resolution== ''){
											$status = 'Pending';
										}elseif($reimburse->resolution == 'check' && ($reimburse->credit_amount) != '' && ($reimburse->resolution_check) != ''){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'storeCredit' && ($reimburse->resolution_store_credit_added) != '' && ($reimburse->resolution_store_credit_check_date) != ''){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'checkstorecredit' && ($reimburse->resolution_check_store_credit_added) != '' && ($reimburse->resolution_check_store_credit_check) != '' && $reimburse->resolution_check_store_credit_check_date != '' && $reimburse->resolution_check_store_credit_check_credit_number != '' ){
											$status = 'Paid';
										}elseif($reimburse->resolution == 'cash' && $reimburse->resolution_cash_paid != '' && $reimburse->resolution_cash_paid_date != ''){
											$status = 'Paid';
										}else if($claimValue > $originalClaimValue  && ($reimburse->approved_amount == '')) {
											$status = 'Approval Pending';
										}
										else if ($originalClaimValue > $claimValue && ($reimburse->resolution == 'check') && ($reimburse->resolution == 'checkstorecredit') && ($reimburse->approved_amount != '')){			
											$status = 'Payment Pending';
											
											// mail to owner
										} */
								?>
								
								<tr>
									<td>{{ $val  }}</td>
									<td>{{ ucfirst($reimburse->first_name)." ".ucfirst($reimburse->last_name) }}</td>
									<!--<td>{{ date('m/d/Y', strtotime($reimburse->created_at))	}}</td>-->
									<td>{{ $reimburse->today_date	}}</td>
									<td>{{ ucfirst($reimburse->name) }}</td>
									<td>{{ $resolution }}</td>
									<td>{{ $reimburse->status }}</td>
									
									<td>
										{!! Form::open(array('route' => ['reimbursement.destroy', $reimburse->id], 'method'=>'DELETE', 'class' => 'delete')) !!}
											
											{{ link_to_route('reimbursement.edit','',[$reimburse->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
											
											@if(Auth::user()->role == 'Owner')
											|
											{{ Form::button('',['class'=>'fa fa-close','type'=>'submit','data-toggle'=>"tooltip", 'data-placement'=>"right", 'title'=>"delete"]) }}
											@endif	
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
</section>

<script type="text/javascript">
	$(document).ready(function() {
	    $('#example').DataTable({
			order: [],
			columnDefs: [ { orderable: false, targets: [0]}]
		});
	} );
	// column.header()

	$(".delete").on("submit", function(){
        return confirm("Do you want to delete this user?");
    });
</script>
@endsection
