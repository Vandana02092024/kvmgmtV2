@extends('layouts.layout')

@section('content')
<section id="dashboard-page" class="wrap">

	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading">
					<h3 class="heading">Customer Problem Report</h3>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/report') }}">BACK</a>
	                <br /> 
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<div class="widget form-layout">
					<div class="widget-content no-padding">

						<!-- <form class="widget ng-pristine ng-valid" method="POST" action="{{ url('/report') }}"> -->
						{!! Form::model($report, array('route' => ['report.update', 'report' => $report->id], 'method' => 'PUT')) !!}
						{{ csrf_field() }}

						{!! Form::hidden('report_id', $report->id, ['class' => 'form-control']) !!}
						<div class="main-detail">
							<div class="form-group row">
								<div class="col-md-6">
									<div class="form-group row{{ $errors->has('filled_date') ? ' has-error' : '' }}">
										<label class="col-sm-4">Date</label>
										<div class="col-sm-8">
											{!! Form::text('filled_date', ($report->filled_date != '0000-00-00')?date('m/d/Y',strtotime($report->filled_date)):'', ['class' => 'form-control']) !!}
											@if ($errors->has('filled_date'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('filled_date') }}</strong>
		                                        </span>
		                                    @endif
										</div>

									</div>
									<div class="form-group row{{ $errors->has('customer_name') ? ' has-error' : '' }}">
										<label class="col-sm-4">Customer Name</label>
										<div class="col-sm-8">
											<table>
												<tr>
													<td>
														{!! Form::text('customer_name', null, ['class' => 'form-control']) !!}
													</td>
													<td width="11%"></td>
													<td>
														{!! Form::text('customer_last_name', null, ['class' => 'form-control']) !!}
													</td>
												</tr>
											</table>
											@if (($errors->has('customer_name')) or ($errors->has('customer_last_name')))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('customer_name') }}</strong>
		                                        </span>
		                                    @endif

											
										</div>
									</div>
									<div class="form-group row{{ $errors->has('address') ? ' has-error' : '' }}">
										<label class="col-sm-4">Address</label>
										<div class="col-sm-8">
											{!! Form::text('address', null, ['class' => 'form-control']) !!}
											@if ($errors->has('address'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('address') }}</strong>
		                                        </span>
		                                    @endif
										</div>
									</div>

									<div class="form-group row{{ $errors->has('city') ? ' has-error' : '' }}">
										<label class="col-sm-4">City</label>
										<div class="col-sm-8">
											{!! Form::text('city', null, ['class' => 'form-control']) !!}
											@if ($errors->has('city'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('city') }}</strong>
		                                        </span>
		                                    @endif
										</div>
										


									</div>

									<div class="form-group row{{ $errors->has('state') ? ' has-error' : '' }}">
										<label class="col-sm-4">State</label>
										<div class="col-sm-8">
											<select name="state" class="form-control">
												<option value="Alabama" @if( $report->state == 'Alabama') selected="selected"; @endif>Alabama</option>
												<option value="Alaska" @if( $report->state == 'Alaska') selected="selected"; @endif>Alaska</option>
												<option value="Arizona" @if( $report->state == 'Arizona') selected="selected"; @endif>Arizona</option>
												<option value="Arkansas" @if( $report->state == 'Arkansas') selected="selected"; @endif>Arkansas</option>
												<option value="California" @if( $report->state == 'California') selected="selected"; @endif>California</option>
												<option value="Colorado" @if( $report->state == 'Colorado') selected="selected"; @endif>Colorado</option>
												<option value="Connecticut" @if( $report->state == 'Connecticut') selected="selected"; @endif>Connecticut</option>
												<option value="Delaware" @if( $report->state == 'Delaware') selected="selected"; @endif>Delaware</option>
												<option value="District of Columbia" @if( $report->state == 'District of Columbia') selected="selected"; @endif>District of Columbia</option>
												<option value="Florida" @if( $report->state == 'Florida') selected="selected"; @endif>Florida</option>
												<option value="Georgia" @if( $report->state == 'Georgia') selected="selected"; @endif>Georgia</option>
												<option value="Hawaii" @if( $report->state == 'Hawaii') selected="selected"; @endif>Hawaii</option>
												<option value="Idaho" @if( $report->state == 'Idaho') selected="selected"; @endif>Idaho</option>
												<option value="Illinois" @if( $report->state == 'Illinois') selected="selected"; @endif>Illinois</option>
												<option value="Indiana" @if( $report->state == 'Indiana') selected="selected"; @endif>Indiana</option>
												<option value="Iowa" @if( $report->state == 'Iowa') selected="selected"; @endif>Iowa</option>
												<option value="Kansas" @if( $report->state == 'Kansas') selected="selected"; @endif>Kansas</option>
												<option value="Kentucky" @if( $report->state == 'Kentucky') selected="selected"; @endif>Kentucky</option>
												<option value="Louisiana" @if( $report->state == 'Louisiana') selected="selected"; @endif>Louisiana</option>
												<option value="Maine" @if( $report->state == 'Maine') selected="selected"; @endif>Maine</option>
												<option value="Maryland" @if( $report->state == 'Maryland') selected="selected"; @endif>Maryland</option>
												<option value="Massachusetts" @if( $report->state == 'Massachusetts') selected="selected"; @endif>Massachusetts</option>
												<option value="Michigan" @if( $report->state == 'Michigan') selected="selected"; @endif>Michigan</option>
												<option value="Minnesota" @if( $report->state == 'Minnesota') selected="selected"; @endif>Minnesota</option>
												<option value="Mississippi" @if( $report->state == 'Mississippi') selected="selected"; @endif>Mississippi</option>
												<option value="Missouri" @if( $report->state == 'Missouri') selected="selected"; @endif>Missouri</option>
												<option value="Montana" @if( $report->state == 'Montana') selected="selected"; @endif>Montana</option>
												<option value="Nebraska" @if( $report->state == 'Nebraska') selected="selected"; @endif>Nebraska</option>
												<option value="Nevada" @if( $report->state == 'Nevada') selected="selected"; @endif>Nevada</option>
												<option value="New Hampshire" @if( $report->state == 'New Hampshire') selected="selected"; @endif>New Hampshire</option>
												<option value="New Jersey" @if( $report->state == 'New Jersey') selected="selected"; @endif>New Jersey</option>
												<option value="New Mexico" @if( $report->state == 'New Mexico') selected="selected"; @endif>New Mexico</option>
												<option value="New York" @if( $report->state == 'New York') selected="selected"; @endif>New York</option>
												<option value="North Carolina" @if( $report->state == 'North Carolina') selected="selected"; @endif>North Carolina</option>
												<option value="North Dakota" @if( $report->state == 'North Dakota') selected="selected"; @endif>North Dakota</option>
												<option value="Ohio" @if( $report->state == 'Ohio') selected="selected"; @endif>Ohio</option>
												<option value="Oklahoma" @if( $report->state == 'Oklahoma') selected="selected"; @endif>Oklahoma</option>
												<option value="Oregon" @if( $report->state == 'Oregon') selected="selected"; @endif>Oregon</option>
												<option value="Pennsylvania" @if( $report->state == 'Pennsylvania') selected="selected"; @endif>Pennsylvania</option>
												<option value="Rhode Island" @if( $report->state == 'Rhode Island') selected="selected"; @endif>Rhode Island</option>
												<option value="South Carolina" @if( $report->state == 'South Carolina') selected="selected"; @endif>South Carolina</option>
												<option value="South Dakota" @if( $report->state == 'South Dakota') selected="selected"; @endif>South Dakota</option>
												<option value="Tennessee" @if( $report->state == 'Tennessee') selected="selected"; @endif>Tennessee</option>
												<option value="Texas" @if( $report->state == 'Texas') selected="selected"; @endif>Texas</option>
												<option value="Utah" @if( $report->state == 'Utah') selected="selected"; @endif>Utah</option>
												<option value="Vermont" @if( $report->state == 'Vermont') selected="selected"; @endif>Vermont</option>
												<option value="Virginia" @if( $report->state == 'Virginia') selected="selected"; @endif>Virginia</option>
												<option value="Washington" @if( $report->state == 'Washington') selected="selected"; @endif>Washington</option>
												<option value="West Virginia" @if( $report->state == 'West Virginia') selected="selected"; @endif>West Virginia</option>
												<option value="Wisconsin" @if( $report->state == 'Wisconsin') selected="selected"; @endif>Wisconsin</option>
												<option value="Wyoming" @if( $report->state == 'Wyoming') selected="selected"; @endif>Wyoming</option>
											</select>
											@if ($errors->has('status'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('status') }}</strong>
		                                        </span>
		                                    @endif
										</div>


									</div>

									<div class="form-group row{{ $errors->has('zip') ? ' has-error' : '' }}">

										<label class="col-sm-4">Zip Code</label>
										<div class="col-sm-8">
											{!! Form::text('zip', null, ['class' => 'form-control']) !!}
											@if ($errors->has('zip'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('zip') }}</strong>
		                                        </span>
		                                    @endif
										</div>

									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group row{{ $errors->has('clerk') ? ' has-error' : '' }}">

										<label class="col-sm-4">Clerk</label>
										<div class="col-sm-8">
											{!! Form::text('clerk', null, ['class' => 'form-control']) !!}
											@if ($errors->has('clerk'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('clerk') }}</strong>
		                                        </span>
		                                    @endif
										</div>

									</div>
									<div class="form-group row">

										<label class="col-sm-4">Home Phone</label>
										<div class="col-sm-8">
											{!! Form::text('home_phone', null, ['class' => 'form-control', "data-mask" => "(999) 999-9999"]) !!}
										</div>

									</div>
									<div class="form-group row">

										<label class="col-sm-4">Work Phone</label>
										<div class="col-sm-8">
											{!! Form::text('work_phone', null, ['class' => 'form-control', "data-mask" => "(999) 999-9999"]) !!}
										</div>

									</div>
									<div class="form-group row{{ $errors->has('cell_phone') ? ' has-error' : '' }}">

										<label class="col-sm-4">Cell Phone</label>
										<div class="col-sm-8">
											{!! Form::text('cell_phone', null, ['class' => 'form-control', "data-mask" => "(999) 999-9999"]) !!}
											@if ($errors->has('cell_phone'))
		                                        <span class="help-block">
		                                            <strong>{{ $errors->first('cell_phone') }}</strong>
		                                        </span>
		                                    @endif
										</div>

									</div>

									

								</div>
							</div>
						</div>
						<div class="divider"></div>
							<div class="specific-problem">
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group row{{ $errors->has('problem') ? ' has-error' : '' }}">
											<div class="col-sm-12">
												<h4>SPECIFIC PROBLEM (PLEASE CHECK ONE)</span></h4>
												<label class="radio col-sm-6">
													{!! Form::radio('problem', 'Missing Garment', true) !!}
													Missing Garment
												</label>
												<label class="radio col-sm-6">{!! Form::radio('problem', 'Damaged Garment', true) !!}Damaged Garment</label>
												<label class="radio col-sm-6">{!! Form::radio('problem', 'Missing Order', true) !!}Missing Order</label>
												<label class="radio col-sm-6">{!! Form::radio('problem', 'Other',true, ['id' => 'other']) !!}Other</label>
												<div class="text">
													<input type="text" name="other_problem" id="text1" value="{{ $report->problem }}" maxlength="30">
												</div>
												@if ($errors->has('problem'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('problem') }}</strong>
			                                        </span>
			                                    @endif
											</div>
										</div>
									</div>

									<div class="col-sm-3">
										<div class="form-group row{{ $errors->has('claim_check') ? ' has-error' : '' }}">
											<div class="col-sm-12">
												<h4>Claim Check Present?</h4>
												<label class="radio col-md-12">{!! Form::radio('claim_check', 1,true, ['id' => 'claimcheck']) !!}Yes</label>
												<label class="radio col-md-12">{!! Form::radio('claim_check', 0,true) !!}No</label>
												@if ($errors->has('claim_check'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('claim_check') }}</strong>
			                                        </span>
			                                    @endif
											</div>
										</div>	
									</div>
									<div class="col-sm-3">
										<div class="form-group row">
											<div class="col-sm-12 claimchk">
												<label class="radio col-md-8">Claim Check</label>
												<input type="text" name="claim_check_val" id="claimcheckval" value="{{ old('claim_check_val') }}" maxlength="30">
											</div>
										</div>	
									</div>
								</div>

								<div class="row">
									<div class="col-sm-12">
										<div class="form-group row{{ $errors->has('missing_date') ? ' has-error' : '' }}">
											<div class="col-sm-5">
												<h4>What date do you think the garment/order was missing/damaged?</h4>
											</div>
											<div class="col-sm-3">

												{!! Form::text('missing_date', (($report->missing_date != '0000-00-00')?date('m/d/Y',strtotime($report->missing_date)):''), ['class' => 'form-control']) !!}
												@if ($errors->has('missing_date'))
			                                        <span class="help-block">
			                                            <strong>{{ $errors->first('missing_date') }}</strong>
			                                        </span>
			                                    @endif
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-sm-12">
										<div class="form-group row">
											<div class="col-sm-5">
												<h4>Please Provide Lot # if available</h4>
											</div>
											<div class="col-sm-3">
												{!! Form::text('invoice_tag', null, ['class' => 'form-control']) !!}
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-sm-12">
										<div class="form-group row">
											<div class="col-sm-5">
												<h4>Please provide Tag #’s If available</h4>
											</div>
											<div class="col-sm-3">
												{!! Form::text('tag_no', null, ['class' => 'form-control']) !!}
											</div>
										</div>
									</div>
								</div>

							</div>

							

							<div class="describe-garments">
								<div class="row">
									<div class="col-md-9">	
										<h4>DESCRIBE GARMENT(S)</h4>
									</div>
									<div class="col-md-3">
										<a class="btn btn-danger btn-sm pull-right garmentdis_add add-btn">+ Add More</a>
									</div>	
								</div>	
								<table class="table" id="describe-table">
									<thead>
										<tr>
											<th>Type</th>
											<th>Size</th>
											<th>Brand</th>
											<th>Color</th>
											<th>Material</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@php $exist = 0; @endphp
										@foreach($garment_description as $key => $value)
										<input type="hidden" name="gdid[]" value="{{ $value->id }}">
										@php $exist = 1; @endphp
											<tr>
												<td>{!! Form::text('type'.$value->id, $value->type, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('size'.$value->id, $value->size, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('brand_name'.$value->id, $value->brand_name, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('color'.$value->id, $value->color, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('material'.$value->id, $value->material, ['class' => 'form-control']) !!}</td>
												<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="if(confirm('Are you sure?')) delete_row(this, {{ $value->id }}, 1);"><i class="fa fa-trash"></i></a></li>
												</ul>
											</td>
											</tr>
										@endforeach
										
										@if($exist == 0)
										<tr>
											<td><input type="text" name="type[]" onchange="search_garment(this)" value="" class="form-control"></td>
											<td><input type="text" name="size[]" onchange="search_garment(this)" value="" class="form-control"></td>
											<td><input type="text" name="brand_name[]" onchange="search_garment(this)" value="" class="form-control"></td>
											<td><input type="text" name="color[]" onchange="search_garment(this)" value="" class="form-control"></td>
											<td><input type="text" name="material[]" onchange="search_garment(this)" value="" class="form-control"></td>
											<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li>
													<!-- <li><a href="#" class="v-btn v-btn-primary btn-xs"><i class="fa fa-pencil"></i></a></li> -->
												</ul>
											</td>
										</tr>
										@endif
									</tbody>
								</table>
								<div id="divshow"></div>
							</div>


							<div class="Search-result">
								<div class="row">
									<div class="col-md-9">	
										<h4>SEARCH RESULTS</h4>
									</div>
									<div class="col-md-3">
										<a class="btn btn-danger btn-sm pull-right search_add add-btn">+ Add More</a>
									</div>	
								</div>	
								<table class="table" id="search_table">
									<thead>
										<tr>
											<th>Date</th>
											<th>By Who</th>
											<th>Action</th>
											<th>Resolved</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@php $exist = 0; @endphp
										@foreach($search_result as $key => $value)
										<input type="hidden" name="srid[]" value="{{ $value->id }}">
										@php $exist = 1; @endphp
											<tr>
												<td>{!! Form::text('search_date'.$value->id, (($value->search_date != '0000-00-00')?date('m/d/Y',strtotime($value->search_date)):''), ['class' => 'form-control dp']) !!}</td>
												<td>{!! Form::text('search_by_who'.$value->id, $value->by_who, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('action'.$value->id, $value->action, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('resolved'.$value->id, $value->resolved, ['class' => 'form-control']) !!}</td>
												<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="if(confirm('Are you sure?')) delete_row(this, {{ $value->id }}, 2);"><i class="fa fa-trash"></i></a></li>
												</ul>
											</td>
											</tr>
										@endforeach
										@if($exist == 0)
										<tr>
											<td><input type="text" name="search_date[]" value="" class="form-control dp"></td>
											<td><input type="text" name="search_by_who[]" value="" class="form-control"></td>
											<td><input type="text" name="action[]" value="" class="form-control"></td>
											<td><input type="text" name="resolved[]" value="" class="form-control"></td>
											<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li>
													<!-- <li><a href="#" class="v-btn v-btn-primary btn-xs"><i class="fa fa-pencil"></i></a></li> -->
												</ul>
											</td>
										</tr>
										@endif
									</tbody>
								</table>
							</div>



							
							<div class="customer-notification">
								<div class="row">
									<div class="col-md-9">	
										<h4>CUSTOMER NOTIFICATIONS</h4>
									</div>
									<div class="col-md-3">
										<a class="btn btn-danger btn-sm pull-right custnotify_add add-btn">+ Add More</a>
									</div>	
								</div>	

								<table class="table" id="custnotify_table">
									<thead>
										<tr>
											<th>Date</th>
											<th>By Who</th>
											<th>Comment</th>
											<th>Manager Action</th>
											<th></th>
										</tr>
									</thead>
									<tbody>	
										@php $exist = 0; @endphp
										@foreach($customer_notified as $key => $value)
										<input type="hidden" name="cnid[]" value="{{ $value->id }}">
										@php $exist = 1; @endphp
											<tr>
												<td>{!! Form::text('customer_notified_date'.$value->id, ($value->customer_notified_date != '0000-00-00')?date('m/d/Y',strtotime($value->customer_notified_date)):'', ['class' => 'form-control dp']) !!}</td>
												<td>{!! Form::text('by_who'.$value->id, $value->by_who, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('comments'.$value->id, $value->comments, ['class' => 'form-control']) !!}</td>
												<td>{!! Form::text('manager_action'.$value->id, $value->action, ['class' => 'form-control']) !!}</td>
												<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="if(confirm('Are you sure?')) delete_row(this, {{ $value->id }}, 3);"><i class="fa fa-trash"></i></a></li>
												</ul>
											</td>
											</tr>
										@endforeach
										@if($exist == 0)
										<tr>
											<td><input type="text" name="customer_notified_date[]" value="" class="form-control dp"></td>
											<td><input type="text" name="by_who[]" value="" class="form-control"></td>
											<td><textarea name="comments[]" class="form-control"></textarea></td>
											<td><textarea name="manager_action[]" class="form-control"></textarea></td>
											<td>
												<ul class="inline">
													<li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li>
													<!-- <li><a href="#" class="v-btn v-btn-primary btn-xs"><i class="fa fa-pencil"></i></a></li> -->
												</ul>
											</td>
										</tr>
										@endif
									</tbody>
								</table>
							</div>

							
							
							


							<div class="resolution">
								<h4>RESOLUTION ( STORE USE ONLY)</h4>
								<table width="100%" class="table-border">
									<tr>
										<th colspan="4">
											<label class="radio-inline">
												{!! Form::radio('resolution', 1,true) !!}Resolved
											</label>
										</th>
									</tr>
									<tr>
										<td width="10%">&nbsp;</td>
										<td width="10%">
											<label class="radio-inline">
												{!! Form::radio('resolved_as', 'Found',true) !!}Found
											</label>
										</td>
										<td>
											<label class="col-sm-3">Date</label>
											<div class="col-sm-9">
												@php $found_date = ($report->resolved_as == 'Found')?date('m/d/Y',strtotime($report->resolved_date)):''; @endphp
												{!! Form::text('found_date', $found_date, ['class' => 'form-control']) !!}
											</div>
										</td>
										<td>
											<label class="col-sm-5">New Rack #</label>
											<div class="col-sm-7">
												{!! Form::text('new_location', null, ['class' => 'form-control']) !!}
											</div>
										</td>
									</tr>
									<tr style="height:5px"><td colspan="4"></td></tr>
									<tr>
										<td></td>
										<td>
											<label class="radio-inline">
												{!! Form::radio('resolved_as', 'Fixed',true) !!}Fixed</label>
											</td>
										<td><label class="col-md-3">Date</label>
											<div class="col-sm-9">
												@php $fixed_date = ($report->resolved_as == 'Fixed')?date('m/d/Y',strtotime($report->resolved_date)):''; @endphp
												{!! Form::text('fixed_date', $fixed_date, ['class' => 'form-control']) !!}
											</div>
										</td>
										<td></td>
									</tr>
									<tr style="height:5px"><td colspan="4"></td></tr>
									<tr>
										<td></td>
										<td>
											<label class="radio-inline">
												{!! Form::radio('resolved_as', 'Claim',true) !!}Claim</label>
											</td>
										<td>
											<label class="col-sm-7">Date claim given to customer</label>
											<div class="col-sm-5">
												@php $claim_date = ($report->resolved_as == 'Claim')?date('m/d/Y',strtotime($report->resolved_date)):''; @endphp
												{!! Form::text('clim_date', $claim_date, ['class' => 'form-control']) !!}
											</div>
										</td>
										<td>
											<label class="col-sm-5">Date claim Submitted</label>
											<div class="col-sm-7">
												{!! Form::text('claim_submitted_date', ($report->claim_submitted_date != '0000-00-00')?date('m/d/Y',strtotime($report->claim_submitted_date)):'', ['class' => 'form-control']) !!}
											</div>
										</td>
									</tr>
									<tr style="height:5px"><td colspan="4"></td></tr>
									<tr>
										<th colspan="4">
											<label class="radio-inline">
												@php $resolution = ($report->resolution === '')?'':(($report->resolution === '1')?'':'checked="checked"');
												/* // {!! Form::radio('resolution', 0, false) !!} */
												 @endphp
												 <br />
												 <input type="radio" name="resolution" value="0" @php echo $resolution; @endphp >
												Not Resolved
											</label>
										</th>
									</tr>
									<tr style="height:5px"><td colspan="4"></td></tr>
									<tr>
										<td></td>
										<td><label>Date closed</label></td>
										<td colspan="2">
											<div class="col-md-4">
												{!! Form::text('closed_date', ($report->closed_date != '0000-00-00')?date('m/d/Y',strtotime($report->closed_date)):'', ['class' => 'form-control']) !!}
											</div>
										</td>
									</tr>
								</table>
							</div>

							<!-- <div class="description">
								<p>We will take every effort to locate your missing garment or order(s). If you have a claim check for the items
								listed, we will contact you as soon as we have any additional information. If you do not have a claim check, we
								will ONLY contact you if we locate your garment or orders that match the description listed above. It can take
								2-4 weeks to have your problem report researched. We do not provide copies of this form and cannot provide
								you with updates unless you have a valid claim check. It is our policy for our customers to verify that their
								orders are complete before leaving the premises.</p>
								<p>I have read and understood the above statement</p>
								<h4 class="text-right">Signature: <br /> </h4>
								<p class="text-right">{{ Auth::user()->name }} &nbsp;</p>
							</div>	 -->								
						
					</div>
					<div class="widget-footer">
						<button class="v-btn v-btn-primary add-btn">Update</button>
					</div>
					</form>
				</div>
			</div>

		</div>

	</div>

</section>


<script type="text/javascript">

$('input[name="filled_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');

    $('input[name="missing_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly'); 

    $('input[name="found_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');
    $('input[name="fixed_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');
    $('input[name="claim_submitted_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');
    $('input[name="clim_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');
    $('input[name="closed_date"]').datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');
    $(".dp").datepicker({
        format: 'mm/dd/yyyy',
    }).attr('readonly', 'readonly');

  $(document).ready(function() {
	  // initialize table
   // $('.garmentdis_add').click(function(){

   // 	$('#describe-table tbody').append($("#describe-table tbody tr:last").clone());

   // });

  $('.garmentdis_add').click(function() {
	    var appendTxt = '<tr><td><input type="text" name="type[]" onchange="search_garment(this)" value="" class="form-control"></td><td><input type="text" name="size[]" onchange="search_garment(this)" value="" class="form-control"></td><td><input type="text" name="brand_name[]" onchange="search_garment(this)" value="" class="form-control"></td><td><input type="text" name="color[]" onchange="search_garment(this)" value="" class="form-control"></td><td><input type="text" name="material[]" onchange="search_garment(this)" value="" class="form-control"></td><td><ul class="inline"><li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li></ul></td></tr>';
	    $('#describe-table tbody').append(appendTxt);
	}); 


   // $(this).parents("tr").remove();

   // $('.search_add').click(function(){

   // $('#search_table tbody').append($("#search_table tbody tr:last").clone());

   // });

	$('.search_add').click(function() {
	    var appendTxt = '<tr><td><input type="text" name="search_date[]" value="" class="form-control dp"></td><td><input type="text" name="search_by_who[]" class="form-control"></td><td><input type="text" name="action[]" value="" class="form-control"></td><td><input type="text" name="resolved[]" value="" class="form-control"></td><td><ul class="inline"><li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li></ul></td></tr>';

	    $('#search_table tbody').append(appendTxt);
	    $(".dp").datepicker({
	        format: 'mm/dd/yyyy',
	    }).attr('readonly', 'readonly');
	}); 

	$('.custnotify_add').click(function() {
	    var appendTxt = '<tr><td><input type="text" name="customer_notified_date[]" value="" class="form-control dp"></td><td><input type="text" name="by_who[]" class="form-control"></td><td><textarea name="comments[]" class="form-control"></textarea></td><td><textarea name="manager_action[]" class="form-control"></textarea></td><td><ul class="inline"><li><a class="v-btn v-btn-primary btn-xs" onclick="remove_this(this)"><i class="fa fa-trash"></i></a></li></ul></td></tr>';
	    $('#custnotify_table tbody').append(appendTxt);
	    $(".dp").datepicker({
	        format: 'mm/dd/yyyy',
	    }).attr('readonly', 'readonly');
	}); 

   // $('.custnotify_add').click(function(){

   // $('#custnotify_table tbody').append($("#custnotify_table tbody tr:last").clone());

   // });


   
});


  function remove_this(x)
  {
  	$(x).parents("tr").remove();
  	return false;
  }
</script>
<script type="text/javascript">

$(document).ready(function () {
    $(".text").hide();

    if($("input[type=radio][id='other']").is(':checked')) {
    	$(".text").show();
    }

    if($("input[type=radio][id='claimcheck']").is(':checked')) {
    	$(".claimchk").show();
    }

    $("#other").click(function () {
        $(".text").show();
    });
});

$(".claimchk").hide();

$("input[name='problem']").on("click", function() {
	if($("input[type=radio][name='problem']:checked").val() != 'Other'){
		$(".text").hide();
	}
});

$("input[type=radio][name='claim_check']").on("click", function() {
	if($("input[type=radio][name='claim_check']:checked").val() != 1){
		$(".claimchk").hide();
	}else{
		$(".claimchk").show();
	}
});

// Found Check line //
$("input[name='resolved_as']").on("click", function() {
	$("input[name=resolution][value='1']").prop("checked",true);
});
$("input[name='found_date']").on("change", function() {
	$("input[name=resolved_as][value='Found']").prop("checked",true);
	$("input[name=resolution][value='1']").prop("checked",true);
});
$("input[name='new_location']").on("change", function() {
	$("input[name=resolved_as][value='Found']").prop("checked",true);
	$("input[name=resolution][value='1']").prop("checked",true);
});
// ###################### //

// Fixed check line //
$("input[name='fixed_date']").on("change", function() {
	$("input[name=resolved_as][value='Fixed']").prop("checked",true);
	$("input[name=resolution][value='1']").prop("checked",true);
});
// ###################### //

// Cliam check line //
$("input[name='clim_date']").on("change", function() {
	$("input[name=resolved_as][value='Claim']").prop("checked",true);
	$("input[name=resolution][value='1']").prop("checked",true);
});
$("input[name='claim_submitted_date']").on("change", function() {
	$("input[name=resolved_as][value='Claim']").prop("checked",true);
	$("input[name=resolution][value='1']").prop("checked",true);
});
// ###################### //


$("input[name='closed_date']").on("change", function() {
	$("input[name=resolution][value='0']").prop("checked",true);
});


function delete_row(x, id, op)
{
    $.ajax({
        type:       "POST",
        dataType:   "html",
        url:        "{{ url('/') }}/removeProblemSub",
        data: {id: id, op : op},
        success:    function(data)
        {
            if(data == 'yes'){
            	$(x).parents("tr").remove();
            	alert('Item deleted successfully!');
            }else{
            	alert('Can not deleted the item!');
            }
        }
    });
}


function search_garment(x)
{
	// alert(x);
	var keys = [];

	$(x).parents("tr").find("input").each(function() {
        keys.push(this.value);
    });



    $.ajax({
        type:       "POST",
        dataType:   "html",
        url:        "{{ url('/') }}/searchGarments",
        data: {id: keys},
        success:    function(data)
        {
            $("#divshow").html(data);
        }
    });
}
</script>
@endsection
