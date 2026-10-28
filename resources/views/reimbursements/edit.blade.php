@extends('layouts.layout')

@section('content')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <!--<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
  <script>
  $( function() {
    $( ".datepicker" ).datepicker();
  } );
  </script>
<script type="text/javascript">
$(function() {
   $("input[name='zip_type']").click(function() {
	  fetchReport();
   });
 });
 function fetchReport()
 {
	 if ($("#new").is(":checked")) {
		 // $('.fetch_results').find('input:text,textarea,select').val('');
		//  $('.fetch_results').val('');
		  $("#dvPRI").hide();	
	  }
     else if ($("#pri").is(":checked")) {
       $("#dvPRI").show();
     } else {
       $("#dvPRI").hide();
     }
 }
</script>

<script type="text/javascript">
 $(document).ready(function(){
	 fetchReport();
});
</script>
<style type="text/css">
  .office-use-section
  {
  	padding: 10px 20px;
    background: #eee;
    margin: 10px;
  }
  #describe-garment-table>thead>tr>th
  {
  	font-size: 12px;

  }
  textarea.form-control.animated {
			height: 30px;
		}
		.autoSize {
			 display:inline-block;
			width: 600px;
			min-height: 34px;
			border: solid 1px #ccc;
		}
		.bigger_radio
		{
			width: 1.9em!important;
			height: 1.9em!important;
			margin-top: -2px!important;
			margin-left: -26px!important;
		}
		.bigger_radio_label
		{
			padding-left: 22px!important;
            padding-top: 10px!important;
		}

</style>


<section id="dashboard-page" class="wrap kv-claim-form-page">

	<div class="container-fluid">
		
		<div class="row">

			<div class="col-sm-12">

				<div class="page-heading kv-page-hero kv-claims-hero">

					<h3 class="heading">
						<span class="kv-hero-title"><i class="fa fa-file-text-o"></i><span>Edit Claim Form</span></span>
						<span class="kv-claim-header-actions">
							<a class="v-btn v-btn-secondary" target="_blank"
								href="{{ url('/rembursementPdf/' . $reimbursement->id) }}">
									<i class="fa fa-file-pdf-o"></i> PDF
							</a>
							<a class="v-btn v-btn-primary" href="{{ url('/reimbursement') }}"><i class="fa fa-arrow-left " style="margin-right: 5px"> </i>BACK</a>
						</span>
					</h3>
				</div>

			</div>

		</div>
		


		<div class="row">

			<div class="col-sm-12">

				<div class="widget form-layout kv-claim-form">

					<div class="widget-content no-padding">
						{!! Form::model($reimbursement, [
							'url' => url('/reimbursement/' . $reimbursement->id),
							'method' => 'PUT',
							'id' => 'myform'
						]) !!}
						{{ csrf_field() }}
						{!! Form::hidden('id', $reimbursement->id, ['class' => 'form-control']) !!}
						{!! Form::hidden('userRole', $userRole, ['class' => 'form-control ownerCheck']) !!}
						{!! Form::hidden('userid', $reimbursement->user_id, ['class' => 'form-control']) !!}
						
                            <div class="main-detail loass_container ">
								
								<div class="form-group row{{ $errors->has('zip_type') ? ' has-error' : '' }}">
							   	  <div class="">
							   	  	<div class="col-md-3">
										<label class="radio-inline">
										  <input type="radio" id="new" name="zip_type" checked value="0" {{ $reimbursement->zip_type == 0 ? 'checked' : '' }}>NEW CLAIM FORM
										</label>

										@if ($errors->has('zip_type'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('zip_type') }}</strong>
	                                        </span>
	                                    @endif

									</div>
									<div class="col-md-3">
										<label class="radio-inline">
										  <input type="radio" id="pri" name="zip_type" {{ $reimbursement->zip_type == 1 ? 'checked' : '' }} value="1">USE EXISTING PROBLEM REPORT
										</label>
								    </div>
									<div id="dvPRI" class="col-md-6{{ $errors->has('report_id') ? ' has-error' : '' }}" style="display: none">
									    {{ Form::select('report_id', $reportIds, null, ['class' => 'selectlist form-control col-md-5','id' => 'custID']) }}

									    @if ($errors->has('report_id'))
	                                        <span class="help-block">
	                                            <strong>{{ $errors->first('report_id') }}</strong>
	                                        </span>
	                                    @endif

									</div>

							   	  </div>
							   </div>
					<div class="kv-claim-section-heading"><span>Claim and customer details</span><small>Start with the claim reference, then enter the customer’s contact information.</small></div>
								
							<div class="row kv-claim-grid-row">
									<div class="col-md-3 {{ $errors->has('today_date') ? ' has-error' : '' }}" >
                            			<label>Today’s Date</label>
	                                    <div>
											<input type="text" readonly value="<?php echo date('m/d/Y', strtotime($reimbursement->today_date));?>" name="today_date" class="form-control dp">
											@if ($errors->has('today_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('today_date') }}</strong>
			                                        </span>
			                                    @endif
										
										</div>
	                                </div>
									
									
	                                <div class="col-md-3 {{ $errors->has('claim_check') ? ' has-error' : '' }}">
	                                    <label>Claim Check #</label>
	                                    <div>
										
										{!! Form::text('claim_check', null, ['class' => 'form-control']) !!}
										@if ($errors->has('claim_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('claim_check') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                                <div class="col-md-3 {{ $errors->has('original_claim_check') ? ' has-error' : '' }}">
	                                    <label>Do you have your original Claim check? </label>
	                                    <div>
	                                    	<label class="radio-inline">
											{!! Form::radio('original_claim_check', '1', true,['id' => 'cc1']) !!}
													Yes
										     
										    </label>
										    <label class="radio-inline">
											{!! Form::radio('original_claim_check', '0', true,['id' => 'cc2']) !!}
													No
										      
										    </label>
	                                    </div>	
										@if ($errors->has('original_claim_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('original_claim_check') }}</strong>
			                                        </span>
			                                    @endif
	                                </div>
									
					<div id="claimDetail" class="col-md-3 {{ $errors->has('claim_detail') ? ' has-error' : '' }}">
		                                    <label>Check Number</label>
		                                    <div>
											{!! Form::text('claim_detail', null, ['class' => 'form-control numbersOnly']) !!}
											&nbsp;<span id="errmsg"></span>
											@if ($errors->has('claim_detail'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('claim_detail') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
									
                                </div>
				<div class="row kv-claim-fields-wide kv-claim-grid-row">
                            		<div class="col-md-3 {{ $errors->has('claim_check_date') ? ' has-error' : '' }}">
                            			<label>Claim Check Date</label>
	                                     <div>
										 
						 {!! Form::text('claim_check_date', null, ['class' => 'form-control datepicker']) !!}
										 @if ($errors->has('claim_check_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('claim_check_date') }}</strong>
			                                        </span>
			                                    @endif
												</div>
	                                </div>
	                                <div class="col-md-3 {{ $errors->has('special_claim_check') ? ' has-error' : '' }}">
	                                    <label>Special Claim Check # (if any)</label>
	                                    <div>
										
										{!! Form::text('special_claim_check', null, ['class' => 'form-control']) !!}
										@if ($errors->has('special_claim_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('special_claim_check') }}</strong>
			                                        </span>
			                                    @endif
												</div>
	                                </div>
	                                <div class="col-md-4 {{ $errors->has('compensation') ? ' has-error' : '' }}">
	                                    <label>Compensation</label>
	                                    <div class="kv-claim-choice-grid">
	                                    	<label class="radio-inline">
											{!! Form::radio('compensation', '1', true) !!}
													Store Credit
										     
										    </label>
										    <label class="radio-inline">
											{!! Form::radio('compensation', '0', true) !!}
													Check
										      
										    </label>
											 <label class="radio-inline">
											{!! Form::radio('compensation', '2', true) !!}
													Split
										      
										    </label>
											<label class="radio-inline">
											{!! Form::radio('compensation', '3', true) !!}
													Cash
										      
										    </label>
	                                    </div>	
										@if ($errors->has('compensation'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('compensation') }}</strong>
			                                        </span>
			                                    @endif
	                                </div>
                                </div>
				<div class="row kv-claim-fields-wide kv-claim-grid-row">
                                	<div class="col-md-3 {{ $errors->has('first_name') ? ' has-error' : '' }}">
                                		<label>First Name</label>
	                                    <div>
										{!! Form::text('first_name', null, ['class' => 'form-control fetch_results','id' => 'customer_name']) !!}
										
										@if ($errors->has('first_name'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('first_name') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                                <div class="col-md-3 {{ $errors->has('last_name') ? ' has-error' : '' }}">
                                		<label>Last Name</label>
	                                    <div>
										{!! Form::text('last_name', null, ['class' => 'form-control fetch_results','id' => 'customer_last_name']) !!}
										@if ($errors->has('last_name'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('last_name') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                                <div class="col-md-4 kv-claim-item-field {{ $errors->has('item') ? ' has-error' : '' }}">
	                                	<label>Item was?</label>
	                                    <div class="kv-claim-item-options">
	                                    	<label class="radio-inline">
											{!! Form::radio('item', '1', true) !!}
													Dry Cleaned
										      
										    </label>
										    <label class="radio-inline">
												{!! Form::radio('item', '0', true) !!}
													Laundered
										     
										    </label>
											 <label class="radio-inline">
												{!! Form::radio('item', '2', true) !!}
													Alterations
										     
										    </label>
											 <label class="radio-inline">
												{!! Form::radio('item', '3', true) !!}
													Household
										     
										    </label>
	                                    </div>
											@if ($errors->has('item'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('item') }}</strong>
			                                        </span>
			                                    @endif
	                                </div>
	                            </div>
                            <div class="row kv-claim-fields-wide kv-claim-grid-row">
                            		<div class="col-md-3 {{ $errors->has('address') ? ' has-error' : '' }}">
                            			<label>Address</label>
	                                    <div>
										{!! Form::textarea('address', null, ['class' => 'form-control fetch_results','style'=>'height:47px','id' => 'address']) !!}
										
										@if ($errors->has('address'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('address') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                                <div class="col-md-3 {{ $errors->has('city') ? ' has-error' : '' }}">
                            			<label>City</label>
	                                    <div>
										
										{!! Form::text('city', null, ['class' => 'form-control fetch_results','id' => 'city']) !!}
										@if ($errors->has('city'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('city') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                 
	                                <div class="col-md-4 {{ $errors->has('email_address') ? ' has-error' : '' }}">
	                                    <label>Email Address</label>
	                                    <div>
	                                    	<div>
											{!! Form::text('email_address', null, ['class' => 'form-control fetch_results']) !!}
											
											@if ($errors->has('email_address'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('email_address') }}</strong>
			                                        </span>
			                                    @endif
											</div>
	                                    </div>	
	                                </div>
                                </div>
								<div class="row kv-claim-fields-wide kv-claim-grid-row">
                   
	                                <div class="col-md-3 {{ $errors->has('state') ? ' has-error' : '' }}">
	                                    <label>State</label>
	                                    <div>
											<select name="state" class="form-control fetch_results" id = "state">
												<option value="">Select State</option>
												<option value="Alabama" @if( $reimbursement->state == 'Alabama') selected="selected"; @endif>Alabama</option>
												<option value="Alaska" @if( $reimbursement->state == 'Alaska') selected="selected"; @endif>Alaska</option>
												<option value="Arizona" @if( $reimbursement->state == 'Arizona') selected="selected"; @endif>Arizona</option>
												<option value="Arkansas" @if( $reimbursement->state == 'Arkansas') selected="selected"; @endif>Arkansas</option>
												<option value="California" @if( $reimbursement->state == 'California') selected="selected"; @endif>California</option>
												<option value="Colorado" @if( $reimbursement->state == 'Colorado') selected="selected"; @endif>Colorado</option>
												<option value="Connecticut" @if( $reimbursement->state == 'Connecticut') selected="selected"; @endif>Connecticut</option>
												<option value="Delaware" @if( $reimbursement->state == 'Delaware') selected="selected"; @endif>Delaware</option>
												<option value="District of Columbia" @if( $reimbursement->state == 'District of Columbia') selected="selected"; @endif>District of Columbia</option>
												<option value="Florida" @if( $reimbursement->state == 'Florida') selected="selected"; @endif>Florida</option>
												<option value="Georgia" @if( $reimbursement->state == 'Georgia') selected="selected"; @endif>Georgia</option>
												<option value="Hawaii" @if( $reimbursement->state == 'Hawaii') selected="selected"; @endif>Hawaii</option>
												<option value="Idaho" @if( $reimbursement->state == 'Idaho') selected="selected"; @endif>Idaho</option>
												<option value="Illinois" @if( $reimbursement->state == 'Illinois') selected="selected"; @endif>Illinois</option>
												<option value="Indiana" @if( $reimbursement->state == 'Indiana') selected="selected"; @endif>Indiana</option>
												<option value="Iowa" @if( $reimbursement->state == 'Iowa') selected="selected"; @endif>Iowa</option>
												<option value="Kansas" @if( $reimbursement->state == 'Kansas') selected="selected"; @endif>Kansas</option>
												<option value="Kentucky" @if( $reimbursement->state == 'Kentucky') selected="selected"; @endif>Kentucky</option>
												<option value="Louisiana" @if( $reimbursement->state == 'Louisiana') selected="selected"; @endif>Louisiana</option>
												<option value="Maine" @if( $reimbursement->state == 'Maine') selected="selected"; @endif>Maine</option>
												<option value="Maryland" @if( $reimbursement->state == 'Maryland') selected="selected"; @endif>Maryland</option>
												<option value="Massachusetts" @if( $reimbursement->state == 'Massachusetts') selected="selected"; @endif>Massachusetts</option>
												<option value="Michigan" @if( $reimbursement->state == 'Michigan') selected="selected"; @endif>Michigan</option>
												<option value="Minnesota" @if( $reimbursement->state == 'Minnesota') selected="selected"; @endif>Minnesota</option>
												<option value="Mississippi" @if( $reimbursement->state == 'Mississippi') selected="selected"; @endif>Mississippi</option>
												<option value="Missouri" @if( $reimbursement->state == 'Missouri') selected="selected"; @endif>Missouri</option>
												<option value="Montana" @if( $reimbursement->state == 'Montana') selected="selected"; @endif>Montana</option>
												<option value="Nebraska" @if( $reimbursement->state == 'Nebraska') selected="selected"; @endif>Nebraska</option>
												<option value="Nevada" @if( $reimbursement->state == 'Nevada') selected="selected"; @endif>Nevada</option>
												<option value="New Hampshire" @if( $reimbursement->state == 'New Hampshire') selected="selected"; @endif>New Hampshire</option>
												<option value="New Jersey" @if( $reimbursement->state == 'New Jersey') selected="selected"; @endif>New Jersey</option>
												<option value="New Mexico" @if( $reimbursement->state == 'New Mexico') selected="selected"; @endif>New Mexico</option>
												<option value="New York" @if( $reimbursement->state == 'New York') selected="selected"; @endif>New York</option>
												<option value="North Carolina" @if( $reimbursement->state == 'North Carolina') selected="selected"; @endif>North Carolina</option>
												<option value="North Dakota" @if( $reimbursement->state == 'North Dakota') selected="selected"; @endif>North Dakota</option>
												<option value="Ohio" @if( $reimbursement->state == 'Ohio') selected="selected"; @endif>Ohio</option>
												<option value="Oklahoma" @if( $reimbursement->state == 'Oklahoma') selected="selected"; @endif>Oklahoma</option>
												<option value="Oregon" @if( $reimbursement->state == 'Oregon') selected="selected"; @endif>Oregon</option>
												<option value="Pennsylvania" @if( $reimbursement->state == 'Pennsylvania') selected="selected"; @endif>Pennsylvania</option>
												<option value="Rhode Island" @if( $reimbursement->state == 'Rhode Island') selected="selected"; @endif>Rhode Island</option>
												<option value="South Carolina" @if( $reimbursement->state == 'South Carolina') selected="selected"; @endif>South Carolina</option>
												<option value="South Dakota" @if( $reimbursement->state == 'South Dakota') selected="selected"; @endif>South Dakota</option>
												<option value="Tennessee" @if( $reimbursement->state == 'Tennessee') selected="selected"; @endif>Tennessee</option>
												<option value="Texas" @if( $reimbursement->state == 'Texas') selected="selected"; @endif>Texas</option>
												<option value="Utah" @if( $reimbursement->state == 'Utah') selected="selected"; @endif>Utah</option>
												<option value="Vermont" @if( $reimbursement->state == 'Vermont') selected="selected"; @endif>Vermont</option>
												<option value="Virginia" @if( $reimbursement->state == 'Virginia') selected="selected"; @endif>Virginia</option>
												<option value="Washington" @if( $reimbursement->state == 'Washington') selected="selected"; @endif>Washington</option>
												<option value="West Virginia" @if( $reimbursement->state == 'West Virginia') selected="selected"; @endif>West Virginia</option>
												<option value="Wisconsin" @if( $reimbursement->state == 'Wisconsin') selected="selected"; @endif>Wisconsin</option>
												<option value="Wyoming" @if( $reimbursement->state == 'Wyoming') selected="selected"; @endif>Wyoming</option>
											</select>
										</div>
										@if ($errors->has('state'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('state') }}</strong>
			                                        </span>
			                                    @endif
	                                </div>
	                               <div class="col-md-3 {{ $errors->has('zip_code') ? ' has-error' : '' }}">
	                                    <label>Zip Code</label>
	                                    <div>
	                                    	<div>
										{!! Form::text('zip_code', null, ['class' => 'form-control numbersOnly fetch_results','onKeyPress' => "if(this.value.length==5) return false;",'id' => 'zip' ]) !!}
											
											@if ($errors->has('zip_code'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('zip_code') }}</strong>
			                                        </span>
			                                    @endif
											</div>
	                                    </div>	
	                                </div>
	                                <div class="col-md-4 {{ $errors->has('telephone_number') ? ' has-error' : '' }}">
	                                    <label>Telephone Number</label>
	                                    <div>
										{!! Form::text('telephone_number', null, ['class' => 'form-control fetch_results',"data-mask" => "(999) 999-9999",'id' => 'customer_last_name']) !!}
										
										@if ($errors->has('telephone_number'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('telephone_number') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
	                              
                                </div>
                                 <div class="row_blank" style="height:10px;"></div>
                                <div class="describe-garments">
								<div class="row">
									<div class="col-md-9">	
										<h4>DESCRIBE ITEM(S):</h4>
									</div>
									<div class="col-md-3">
										<a class="btn btn-danger btn-sm pull-right garmentdis_add add-btn add_decription">+ Add More</a>

									</div>	
								</div>	

								<table class="table table-striped kv-claim-items-table" id="search_table">

									<thead>

										<tr>

											<th>ITEM DESCRIPTION</th>
                                		 	<th>MATERIAL </th>
                                		 	<th>COLOR</th>
                                		 	<th>DATE PURCHASED</th>
											<th>PURCHASE LOCATION</th>
                                		 	<th>AMOUNT PAID</th>
                                		 	
                                		 	

											<th>ACTION</th>

										</tr>

									</thead>

									<tbody>
										@php $exist = 0; @endphp
										@foreach($description as $key => $value)
										
										<input type="hidden" name="gdid[]" value="{{ $value->id }}">
										@php $exist = 1; @endphp
										<tr>
											<td>
											{!! Form::text('item_desc'.$value->id, $value->item_desc, ['class' => 'form-control']) !!}
											</td>
											<td>
											{!! Form::text('material'.$value->id, $value->material, ['class' => 'form-control']) !!}
											</td>
											<td>
											{!! Form::text('color'.$value->id, $value->color, ['class' => 'form-control']) !!}
											</td>
											<td>
											{!! Form::text('date_purchase'.$value->id, $value->date_purchase, ['class' => 'form-control datepicker']) !!}
											</td>
											<td>
															{!! Form::text('purchase_location'.$value->id, $value->purchase_location, ['class' => 'form-control']) !!}
											
											</td>
											<td>
											<div style="position:relative;"><span class="dolor">$</span>
											{!! Form::text('amount_paid'.$value->id, $value->amount_paid, ['class' => 'form-control amount_paid currency','onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
											</div>
											</td>
											
											
											
											<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="if(confirm('Are you sure?')) delete_row(this,{{ $value->id }});"><i class="fa fa-trash"></i></a></li>
												</ul>
											</td>
											
										</tr>
										@endforeach
										@if($exist == 0)
										@endif	
									</tbody>

								</table>

							</div>
							
							
							<div class="row kv-claim-total-row"><div class="col-md-4 {{ $errors->has('total_desc') ? ' has-error' : '' }}">
	                                    <label>Total</label>
	                                    <div style="position:relative;">
										<span class="dolor">$</span>
										{!! Form::text('total_desc', null, ['class' => 'form-control currency','readonly','id'=>'total_desc'])!!}
										
										@if ($errors->has('total_desc'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('total_desc') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div></div>
							
							 <div class="kv-claim-section-heading"><span>Claim total and proof of purchase</span><small>Enter the total item value and the customer’s settlement offer.</small></div>
							 <div class="row_blank" style="height:5px;"></div>
                                 <div class="row kv-claim-terms">
                                	<div class="col-md-12">
                                       <p style="font-size:13px"><b>PROOF OF PURCHASE (one of the following):</b> 1) Original or duplicate receipt from store, 2) Itemized credit card receipt, or 3) Monthly statement from store where purchased</p>
                                	 </div>
                                 </div>
                                 <div class="row_blank" style="height:5px;"></div>
                                 <div class="row kv-claim-terms"><div class="col-md-12"><p style="font-size:13px">After allowing for depreciation from age and wear, I would be willing to accept $
								 
					 {!! Form::text('proof_purchase', null, ['class' => 'currency kv-claim-proof-amount']) !!}
								 @if ($errors->has('proof_purchase'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('proof_purchase') }}</strong>
			                                        </span>
			                                    @endif
								 as full settlement of this claim. I acknowledge that this listed amount is an offer made
by me to be considered by ZIPS. Please note that once the claim is resolved, the item(s), for which you received compensation will remain in our possession.</p></div></div>
                                <div class="row_blank" style="height:5px;"></div>
                                 <div class="row kv-claim-terms"><div class="col-md-12"><p style="font-size:13px">Customer is responsible to ensure this form is filled out completely and legibly. Store Credit will be added to Customer account or check is mailed to the home address listed above
within ten (10) business days of final resolution. Copies of this document are not provided.</p></div></div>
<div class="row_blank" style="height:5px;"></div>
                                 <div class="row"><div class="col-md-12"><p style="font-size:13px">The statements I made above are true and correct.</p></div></div>
								 
								  <?php 
								 if ($flag == 1){								 
								 ?>
								 <form method="post">
				 <div class="row kv-approved-amount-row">
				  <div class="col-md-3 kv-approved-amount-field {{ $errors->has('approved_amount') ? ' has-error' : '' }}">
	                                    <label>Approved Amount</label>
	                                    <div>
										
										{!! Form::text('approved_amount', null, ['class' => 'form-control','id'=>'approved_amount']) !!}
										@if ($errors->has('approved_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('approved_amount') }}</strong>
			                                        </span>
			                                    @endif
										</div>
	                                </div>
					<div class="col-md-3 kv-approved-amount-actions top-10">
	                                   <button type="button" class="v-btn v-btn-primary add-btn" onclick = editable();>Edit</button>	                                
	                                   <button type="button" class="v-btn v-btn-primary add-btn" id ="disablebutton" onclick = approve();>Approve</button>
	                                </div>
									<?php if($reimbursement->approved_amount_user_name != ''  && ($reimbursement->approved_amount_date != '')) { ?>
					    <div class="col-md-6 kv-approved-amount-meta">
									Amount approved by {{ $reimbursement->approved_amount_user_name }} on {{ $reimbursement->approved_amount_date }} at {{ $reimbursement->approved_amount_time }}
									
									</div>
									<?php } ?>
								 </div>
								 </form>
								 <?php } ?>
								 
								 
								 
								  <div class="office-use-section">
								<h5><i class="fa fa-lock" aria-hidden="true"></i><b>FOR OFFICE USE ONLY:</b></h5>
                                	<div class="row">
	                            		<div class="col-md-4 {{ $errors->has('store') ? ' has-error' : '' }}">
	                            			<label>Store #</label>
		                                    <div>
											
											{!! Form::text('store', null, ['class' => 'form-control','readonly']) !!}
											
											</div>
											@if ($errors->has('store'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('store') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										<div class="col-md-4 {{ $errors->has('store_owner_manager') ? ' has-error' : '' }}">
		                                    <label>Store Owner/Manager:</label>
		                                    <div>
											{!! Form::text('store_owner_manager', null, ['class' => 'form-control']) !!}
											
											@if ($errors->has('store_owner_manager'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('store_owner_manager') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
		                                <div class="col-md-4 {{ $errors->has('receipts_attached') ? ' has-error' : '' }}">
		                                    <label>Receipts Attached: </label>
		                                    <div>
		                                    	<label class="radio-inline">
												{!! Form::radio('receipts_attached', '1', true) !!}
													Yes
											      
											    </label>
											    <label class="radio-inline">
												{!! Form::radio('receipts_attached', '0', true) !!}
													No
											      
											    </label>
		                                    </div>
												@if ($errors->has('receipts_attached'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('receipts_attached') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
                                    </div>
									<div class="row">
                                    	<div class="col-md-4 {{ $errors->has('claim_type') ? ' has-error' : '' }}">
		                                    <label>Claim Type:</label>
		                                    <div>
											    <label class="radio-inline">
												{!! Form::radio('claim_type', '1', true,['id' => 'ct1']) !!}
													Missing Item
											      
											    </label>
											    <label class="radio-inline">
												{!! Form::radio('claim_type', '2', true,['id' => 'ct2']) !!}
													Missing Order
											      
											    </label>
											    <label class="radio-inline">
												{!! Form::radio('claim_type', '3', true,['id' => 'ct3']) !!}
													Damaged Item
											      
											    </label>
											    
		                                    </div>
												@if ($errors->has('claim_type'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('claim_type') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										   <div id="dam_desc" class="col-md-4 {{ $errors->has('damaged_desc') ? ' has-error' : '' }}">
		                                    <label>Comments/Damage Description:</label>
		                                    <div>
											{!! Form::textarea('damaged_desc', null, ['class' => 'form-control animated','style'=>'height:40px']) !!}
											
											@if ($errors->has('damaged_desc'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('damaged_desc') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
										
										 <div class="col-md-4 {{ $errors->has('item_return') ? ' has-error' : '' }}">
		                                    <label>Item Returned to Customer </label>
		                                    <div>
		                                    	<label class="radio-inline">
												{!! Form::radio('item_return', '1', true) !!}
													Yes
											      
											    </label>
											    <label class="radio-inline">
												{!! Form::radio('item_return', '0', true) !!}
													No
											      
											    </label>
		                                    </div>
												@if ($errors->has('item_return'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('item_return') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										
										
                                    </div>
									 <div class="row">
	                            		<div class="col-md-3 {{ $errors->has('resolution') ? ' has-error' : '' }}">
	                            			<label>Resolution:</label>
		                                    <div>
		                                    	
												<select class="form-control" name="resolution" id="resolution_id" >
												<option value="" >Select</option>
												<option value="check" @if( $reimbursement->resolution == 'check') selected="selected"; @endif>Check</option>
												<option value="storeCredit" @if( $reimbursement->resolution == 'storeCredit') selected="selected"; @endif>Store Credit</option>
												<option value="checkstorecredit" @if( $reimbursement->resolution == 'checkstorecredit') selected="selected"; @endif>Check and Store Credit</option>
												<option value="cash" @if( $reimbursement->resolution == 'cash') selected="selected"; @endif>Cash</option>
											</select>
		                                    </div>
											@if ($errors->has('resolution'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										
                                    </div>
									<div id="div_check" style="display:none;">
                                     <div class="row">	
                                    	<div class="col-md-3 {{ $errors->has('resolution_check_pickup_mail') ? ' has-error' : '' }}">
		                                    <div>
		                                    	<label class="radio-inline bigger_radio_label">
												{!! Form::radio('resolution_check_pickup_mail', '1', true,['class' => 'bigger_radio']) !!}
													Pick Up
											     
											    </label>
											    <label class="radio-inline bigger_radio_label">
												{!! Form::radio('resolution_check_pickup_mail', '0', true,['class' => 'bigger_radio']) !!}
													Mail
											      
											    </label>
		                                    </div>	
											@if ($errors->has('resolution_check_pickup_mail'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_pickup_mail') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										<div class="col-md-3 {{ $errors->has('check_amount') ? ' has-error' : '' }}">
		                                    <label>Check Amount:</label>
		                                    <div>
											{!! Form::text('check_amount', null, ['class' => 'form-control currency','onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
											
											@if ($errors->has('check_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('check_amount') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
		                                <div class="col-md-3 {{ $errors->has('credit_amount') ? ' has-error' : '' }}">
		                                    <label>Check Date:</label>
		                                    <div>
											{!! Form::text('credit_amount', null, ['class' => 'form-control datepicker ownerCheckValue']) !!}
											
											@if ($errors->has('credit_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('credit_amount') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
		                                <div class="col-md-3 {{ $errors->has('resolution_check') ? ' has-error' : '' }}">
		                                    <label> Check#:  </label>
		                                    <div>
											{!! Form::text('resolution_check', null, ['class' => 'form-control ownerCheckValue']) !!}
											
											@if ($errors->has('resolution_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>

                                    </div>
                                    </div>
									<div id="div_storeCredit" >
                                     <div class="form-group row">
										<div class="col-md-3 {{ $errors->has('resolution_store_credit_amount') ? ' has-error' : '' }}">
		                                    <label> Credit Amount </label>
		                                    <div>
											{!! Form::text('resolution_store_credit_amount', null, ['class' => 'form-control currency' ,'onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
											
											@if ($errors->has('resolution_store_credit_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_store_credit_amount') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>									 
                                    	<div class="col-md-3 {{ $errors->has('resolution_store_credit_added') ? ' has-error' : '' }}">
		                                    <div>
		                                    	 <label>Store Credit Added Date:</label>
		                                         <div>
												 {!! Form::text('resolution_store_credit_added', null, ['class' => 'form-control datepicker']) !!}
												 

		                                    </div>	
											@if ($errors->has('resolution_store_credit_added'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_store_credit_added') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
                                    </div>
									
									 <div class="col-md-3 {{ $errors->has('resolution_store_credit_check_date') ? ' has-error' : '' }}">
		                                    <label> Store Credit Number  </label>
		                                    <div>
											{!! Form::text('resolution_store_credit_check_date', null, ['class' => 'form-control']) !!}
											
											@if ($errors->has('resolution_store_credit_check_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_store_credit_check_date') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
									 
										
                                    </div>
									</div>
                                    <div id="div_checkstoreCredit">
                                     <div class="form-group row">
									 
                                        <div class="col-md-3 {{ $errors->has('resolution_check_store_credit_pickup_mail') ? ' has-error' : '' }}">
		                                    <div>
		                                    	<label class="radio-inline bigger_radio_label">
												{!! Form::radio('resolution_check_store_credit_pickup_mail', '1', true,['class' => 'bigger_radio']) !!}
													Pick Up
											    </label>
											    <label class="radio-inline bigger_radio_label">
												{!! Form::radio('resolution_check_store_credit_pickup_mail', '0', true,['class' => 'bigger_radio']) !!}
													Mail
											      
											    </label>
		                                    </div>	
											@if ($errors->has('resolution_check_store_credit_pickup_mail'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_pickup_mail') }}</strong>
			                                        </span>
			                                    @endif
		                                </div>
										<div class="col-md-3 {{ $errors->has('resolution_check_store_credit_added_amount') ? ' has-error' : '' }}">
		                                    <div>
		                                    	 <label>Check Amount:</label>
		                                         <div>
												 {!! Form::text('resolution_check_store_credit_added_amount', null, ['class' => 'form-control currency' ,'onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
												 
												 @if ($errors->has('resolution_check_store_credit_added_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_added_amount') }}</strong>
			                                        </span>
			                                    @endif
												 </div>

		                                    </div>	
		                                </div>	
                                    	<div class="col-md-3 {{ $errors->has('resolution_check_store_credit_added') ? ' has-error' : '' }}">
		                                    <label> Check Date  </label>
		                                    <div>
											
											{!! Form::text('resolution_check_store_credit_added', null, ['class' => 'form-control datepicker ownerCheckValue']) !!}
											
											@if ($errors->has('resolution_check_store_credit_added'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_added') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
										
		                                <div class="col-md-3 {{ $errors->has('resolution_check_store_credit_check') ? ' has-error' : '' }}">
		                                    <label> Check#:  </label>
		                                    <div>
											
											{!! Form::text('resolution_check_store_credit_check', null, ['class' => 'form-control ownerCheckValue']) !!}
											
											@if ($errors->has('resolution_check_store_credit_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_check') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                </div>
                                    </div>
									
									<div class="form-group row">
									
									
									<div class="col-md-3 col-md-offset-3 {{ $errors->has('resolution_check_store_credit_check_credit_amount') ? ' has-error' : '' }}">
		                                    <label> Credit Amount:  </label>
		                                    {!! Form::text('resolution_check_store_credit_check_credit_amount', null, ['class' => 'form-control currency' ,'onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
											@if ($errors->has('resolution_check_store_credit_check_credit_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_check_credit_amount') }}</strong>
			                                        </span>
			                                    @endif
											</div>
											<div class="col-md-3 {{ $errors->has('resolution_check_store_credit_check_date') ? ' has-error' : '' }}">
		                                    <label> Store Credit Added Date:  </label>
		                                    {!! Form::text('resolution_check_store_credit_check_date', null, ['class' => 'form-control datepicker']) !!}
											@if ($errors->has('resolution_check_store_credit_check_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_check_date') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                               
										<div class="col-md-3 {{ $errors->has('resolution_check_store_credit_check_credit_number') ? ' has-error' : '' }}">
		                                    <label>Store Credit Number:  </label>
		                                    {!! Form::text('resolution_check_store_credit_check_credit_number', null, ['class' => 'form-control']) !!}
											@if ($errors->has('resolution_check_store_credit_check_credit_number'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_check_store_credit_check_credit_number') }}</strong>
			                                        </span>
			                                    @endif
											</div>
		                                
								
		                                
									 </div>
									
									
									
                                    </div>
                                     <div id="div_cash" style="display:none;margin-top:10px;">
                                     <div class="form-group row">
									 <div class="col-md-3 {{ $errors->has('resolution_cash_paid_amount') ? ' has-error' : '' }}">
		                                    <div>
		                                    	 <label>Cash Amount:</label>
		                                         <div>
												 {!! Form::text('resolution_cash_paid_amount', null, ['class' => 'form-control currency','onkeypress'=>"return validateFloatKeyPress(this,event);"]) !!}
												 @if ($errors->has('resolution_cash_paid_amount'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_cash_paid_date') }}</strong>
			                                        </span>
			                                    @endif
												 </div>

		                                    </div>	
		                                </div>
		                                
                                        <div class="col-md-3 {{ $errors->has('resolution_cash_paid') ? ' has-error' : '' }}">
		                                    <div>
		                                    	 <label>Paid By:</label>
		                                         <div>
												 {!! Form::text('resolution_cash_paid', null, ['class' => 'form-control']) !!}
												 
												 @if ($errors->has('resolution_cash_paid'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_cash_paid') }}</strong>
			                                        </span>
			                                    @endif
												 </div>
		                                    </div>	
		                                </div>	
                                    	<div class="col-md-3 {{ $errors->has('resolution_cash_paid_date') ? ' has-error' : '' }}">
		                                    <div>
		                                    	 <label>Paid Date:</label>
		                                         <div>
												 {!! Form::text('resolution_cash_paid_date', null, ['class' => 'form-control datepicker']) !!}
												 @if ($errors->has('resolution_cash_paid_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('resolution_cash_paid_date') }}</strong>
			                                        </span>
			                                    @endif
												 </div>

		                                    </div>	
		                                </div>
										
										


                                    </div>
                                    </div>

                                </div>
                                <div class="row">
                                	<div class="col-md-12 text-center">
                                		<button class="v-btn v-btn-primary add-btn" onclick = "upbtn();" id="upBtn">UPDATE</button>
                                	</div>
                                </div>
                                <div class="row_blank" style="height:10px;"></div>	

									

						</form>

					</div>

						

				</div>

			</div>

		</div>

	</div>

</section>
<script type="text/javascript">
	function upbtn(){
		$('#upBtn').css('display','none');
	}
  $(document).ready(function() {
	  
	  $(".dp").datepicker({
			format: 'mm/dd/yyyy',
		});
	  
	  
	  // initialize table
  $('.add_decription').click(function() {

	    var appendTxt = '<tr><td><input type="text" name="item_desc[]" class="form-control"></td><td><input type="text" name="material[]" class="form-control"></td><td><input type="text" name="color[]" class="form-control"></td><td><input type="text" name="date_purchase[]" class="form-control dp"></td><td><input type="text" name="purchase_location[]" class="form-control"></td><td><div style="position:relative;"><span class="dolor">$</span><input type="text" name="amount_paid[]" class="form-control amount_paid currency" onkeypress="return validateFloatKeyPress(this,event);"></div></td><td><ul class="inline"><li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li></ul></td></tr>';



	    $('#search_table tbody').append(appendTxt);
		$('.currency').currencyFormat();
	    $(".dp").datepicker({

	        format: 'mm/dd/yyyy',

	    });

	});


   
});
</script>
<script src="{{ asset('/js/auto-resize.js') }}"></script> 
<script type="text/javascript">
$(function() {
$('#div_check').hide();
$('#div_storeCredit').hide();
$('#div_checkstoreCredit').hide();
$('#div_cash').hide();
$('#resolution_id').change(function(){
		//alert($('#resolution_id').val());
		
        if($('#resolution_id').val() == 'check') {
            $('#div_check').show(); 
			
        } else {
            $('#div_check').hide(); 
			
        } 
		
		if($('#resolution_id').val() == 'storeCredit') {
            $('#div_storeCredit').show(); 
        } else {
            $('#div_storeCredit').hide(); 
        } 
		
		if($('#resolution_id').val() == 'checkstorecredit') {
			
            $('#div_checkstoreCredit').show(); 
        } else {
            $('#div_checkstoreCredit').hide(); 
        } 
		
		if($('#resolution_id').val() == 'cash') {
			
            $('#div_cash').show(); 
        } else {
            $('#div_cash').hide(); 
        } 
    });
});



$( document ).ready(function() {
	
	if($('#resolution_id').val() == 'check') {
            $('#div_check').show(); 
			
        } else {
            $('#div_check').hide(); 
			
        } 
		
		if($('#resolution_id').val() == 'storeCredit') {
            $('#div_storeCredit').show(); 
        } else {
            $('#div_storeCredit').hide(); 
        } 
		
		if($('#resolution_id').val() == 'checkstorecredit') {
			
            $('#div_checkstoreCredit').show(); 
        } else {
            $('#div_checkstoreCredit').hide(); 
        } 
		
		if($('#resolution_id').val() == 'cash') {
			
            $('#div_cash').show(); 
        } else {
            $('#div_cash').hide(); 
        } 
	
});

function delete_row(x,id)
{
	
	var  token = $('input[name=_token]').val();
    $.ajax({
        type:       "POST",
        dataType:   "html",
        url:        "{{ url('/') }}/removeSub",
		headers: {'X-CSRF-TOKEN': token},
        data: {id: id},
        success:    function(data)
        {
						
            if(data == 'yes'){
            	$(x).parents("tr").remove();
            	alert('Item deleted successfully!');
				sumValue();
            }else{
            	alert('Can not deleted the item!');
            }
        }
    });
	
}
$("#telephone_number").inputmask({"mask": "(999) 999-9999"});
$(function(){
	
	$('.animated').autosize({append: ""});
});

$(document).on("change", ".amount_paid", function() {
   sumValue();
});	
function sumValue()
{
	var sumtotal = 0;
    $(".amount_paid").each(function(){
        sumtotal += +$(this).val();
		sum = parseFloat(sumtotal).toFixed(2);
    });
    $("#total_desc").val(sum);
}
function remove_this(x)

  {
	
  	$(x).parents("tr").remove();
	sumValue();
  	return false;

  } 

(function($) {
        $.fn.currencyFormat = function() {
            this.each( function( i ) {
                $(this).change( function( e ){
                    if( isNaN( parseFloat( this.value ) ) ) return;
                    this.value = parseFloat(this.value).toFixed(2);
                });
            });
            return this; 
        }
    })( jQuery );

   
    $( function() {
        $('.currency').currencyFormat();
    });  
	
	
function switchVar() {
    if($("#ct3").prop("checked"))
        $("#dam_desc").show();
     else
        $("#dam_desc").hide();
}

function switchClaim() {
    if($("#cc1").prop("checked"))
        $("#claimDetail").show();
     else
        $("#claimDetail").hide();
}

$(document).ready(function() {
    switchVar();
	switchClaim();
    $("input[name=claim_type]").change(switchVar);
	$("input[name=original_claim_check]").change(switchClaim);
	
	
	$(".numbersOnly").keypress(function (e) {
     if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
        //display error message
       // $("#errmsg").html("Only Numeric digits").show().fadeOut("slow");
               return false;
    }
   });
   
   
   var valuecheck = $(".ownerCheck" ).val();
	 if(!(valuecheck == 'Owner')){
		  $(".ownerCheckValue").attr("disabled", "disabled");
	  }
	
});		

$('#custID').on('change', function(event){
		
		var cID = $('#custID').val();
		
		$.ajax({
        type:       "POST",
        dataType:   "html",
        url:        "{{ url('/') }}/reportData",
        data: {cID: cID},
        success: function(data)
        {
		var data_value = data.split("|");
		  $('#customer_name').val(data_value[0].trim());
		  $('#customer_last_name').val(data_value[1].trim());
		  $('#address').val(data_value[2].trim());
		  $('#city').val(data_value[3].trim());
		  $('#state').val(data_value[4].trim());
		  $('#zip').val(data_value[5].trim());
		  $('#telephone_number').val(data_value[6].trim());
		}
    });
	});
	
	
	function validateFloatKeyPress(el, evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    var number = el.value.split('.');
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    //just one dot
    if(number.length>1 && charCode == 46){
         return false;
    }
    //get the carat position
    var caratPos = getSelectionStart(el);
    var dotPos = el.value.indexOf(".");
    if( caratPos > dotPos && dotPos>-1 && (number[1].length > 1)){
        return false;
    }
    return true;
}

//thanks: http://javascript.nwbox.com/cursor_position/
function getSelectionStart(o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate()
		r.moveEnd('character', o.value.length)
		if (r.text == '') return o.value.length
		return o.value.lastIndexOf(r.text)
	} else return o.selectionStart
}

function approve(){
		// seller_info($(this).find(":selected").val());
		
		var approved_amount = $('#approved_amount').val();
		var form_id  = <?php echo $reimbursement->id ;?>;
		var userid  = "<?php echo $userid;?>";
		var currentdate = new Date(); 
		var cdate= 	(currentdate.getMonth()+1)  + "/" 	
						+  currentdate.getDate() + "/"               
						+ currentdate.getFullYear()  						
		var ctime = currentdate.toLocaleString([], { hour: '2-digit', minute: '2-digit' });
		
		if(approved_amount == ' ') {
			alert("Please enter amount");
			return false;
		}
	
		  $.ajax({
			type:       "POST",			
			url:        "{{ url('/') }}/approved_amount",
			data: {approved_amount: approved_amount,form_id:form_id,userid:userid,cdate:cdate,ctime:ctime},
			success: function(data)
			{	
				alert("Approved Amount Updated");
				$('#approved_amount').attr('readonly','true');
				$('#disablebutton').attr('disabled','true');
				$('#disablebutton').css('background-color','grey');
			}
		});  
		
}
function editable(){
	
	$('#approved_amount').removeAttr('readonly');
	$('#disablebutton').removeAttr('disabled');
	$('#disablebutton').css('background-color','#000');
}


$(document).ready(function() {
	var approved_amount = $('#approved_amount').val();
	
	
	if(approved_amount != '') {
		$('#approved_amount').attr('readonly','true');
		$('#disablebutton').attr('disabled','true');
		$('#disablebutton').css('background-color','grey');
	}

}); 	
</script>

<style type="text/css">
 .dolor
 {
 	position: absolute;
    left: -12px;
    top: 5px;
 }
 .loass_container .radio-inline input[type=radio] {
   width: 1.3em;
   height: 1.3em;
   margin-top: 1px;
  
}
</style>
@endsection
