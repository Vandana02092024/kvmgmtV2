@extends('layouts.layout')

@section('content')

<style type="text/css">
	#example_filter {
		display: none;
	}
	.pagination {
		float: inline-end;
		margin: -25px 0 !important;
	}
</style>
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading kv-page-hero kv-sales-hero">
					<h3 class="heading">
						<span class="kv-hero-title"><i class="fa fa-usd"></i><span>Weekly Sales Reports</span></span>

					<a class="v-btn v-btn-primary pull-right" href="{{ url('/salesection/create') }}"><i class="fa fa-plus"></i> New Week Report</a>
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
					<div class="widget-content">
						<div class="responsive-table">
							<div class="row kv-sales-toolbar">
							    <div class="col-sm-4">
							        <!-- <div class="" id="" style="position: absolute; width: fit-content;">
							                <select name="example_length" aria-controls="example" class="form-control input-sm">
							                	<option selected="" hidden="">{{ empty($count) ? '10' : $count}}</option>
							                    <option value="10">10</option>
							                    <option value="25">25</option>
							                    <option value="50">50</option>
							                    <option value="100">100</option>
							                </select>
							        </div> -->
							    </div>
							    <div class="col-sm-6">
							    	<div class="" id="" style="float: right; width: fit-content;">
							    		 <div id="example_filter1" class="dataTables_filter" style="display: flex;">
								            <input type="date" title="From Date" name="from_date" id="from_date" class="form-control" value="{{ empty($from_date) ? '' : $from_date}}" />
								            <input type="date" title="To Date" name="to_date" id="to_date" class="form-control" value="{{ empty($to_date) ? '' : $to_date}}" style="margin-left: 5px;" />
								        </div>
							    	</div>
							    </div>
							    <div class="col-sm-2">
							    	<div class="" id="" style="float: right; width: fit-content;">
							    		 <div id="example_filter1" class="dataTables_filter" style="display: flex;">
								            <input type="search" name="search" id="search" class="form-control" placeholder="WEEK #" /><label style="font-size: 15px; padding: 5px;"><i class="fa fa-search" id="searchButton"></i></label>
								        </div>
							    	</div>
							    </div>
							</div>
							<table id="example" class="table table-bordered" cellspacing="0" width="100%">
						        <thead>
						            <tr>
						                <th>CALENDAR WEEK</th>
										<th>WEEK #</th>
										<th>START DATE</th>
										<th>END DATE</th>
										<th>Gross Sales</th>
										<th>Net Sales</th>
										<th>Labor %</th>
										<th>ACTION</th>
						            </tr>
						        </thead>
						        <tbody>
								<?php 	//dd($data);?>

						            @foreach($data as $dat)

												@php
												$week=getWeekByDate($dat->dataDate);
												$weekdate=getStartAndEndDatebyweek($week,date('Y-m-d', strtotime($dat->dataDate)));
												@endphp

												<?php
												$tot_sale = getLaberRate($dat->location_id,$dat->storeNo);
								$range = $dat->storeNo;  $gsale = number_format($dat->amount,2);
								$sale_tax = getSaleTax($dat->location_id,$weekdate['start_date'],$weekdate['end_date']);
								$netsale= getNetSale($dat->location_id,$weekdate['start_date'],$weekdate['end_date'])-$sale_tax

												?>

												<!-- Add by kajal -->
												<?php
													// $update_week = correctWeekUpdate($weekdate['end_date'],$week,$weekdate['start_date'],$dat->storeNo,$dat->location_id);
												?>
												<!-- End -->
												<?php

										//		deleteDuplicateData($dat->storeNo,$dat->location_id,$weekdate['start_date'],$weekdate['end_date'])?>
												<tr>
														<td>{{ $week }} </td>
														<td>{{ $dat->storeNo }}</td>
														<td><?php echo date("m-d-Y", strtotime($weekdate['start_date']));  ?></td>
														<td><?php echo date("m-d-Y", strtotime($weekdate['end_date'])); ?></td>
														<td>{{ $gsale }}</td>
														<td>{{  number_format($netsale,2) }}</td>
														<td> @if(($netsale >0) && ($tot_sale) > 0)  {{ number_format(($tot_sale/$netsale)*100,2)}} @else 0.00 @endif </td>
														<td>
													{{ link_to_route('salesection.edit','',['record-'.$dat->id],['class' => 'fa fa-pencil','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"edit"]) }}
														&nbsp;
														{{ link_to_route('salesection.printData','',[$dat->storeNo],['class' => 'fa fa-print','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"Print"]) }}
														&nbsp;
														{{ link_to_route('labor_report','',[$range],['class' => 'fa fa-file','data-toggle'=>"tooltip", 'data-placement'=>"left", 'title'=>"Labor Report"]) }}
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
	// $(document).ready(function() {
	//     $('#example').DataTable({
	// 		order: [],
	// 		paging: false,
	// 		columnDefs: [ { orderable: false, targets: [0]}]
	// 	});
	// } );
	$(document).ready(function () {
		$('#example').DataTable({
			"aaSorting": []
		});
	});

	$(document).ready(function() {
    $('select[name="example_length"]').on('change', function() {
    	var count = this.value;
		        $.ajax({
		        type: "get",
		        url: '{!! route('salesection.filter.data') !!}',
		        data: {
                    count: count
                },
		        success: function(data)
		        {
		          window.location.href = "{!! route('salesection.filter.data') !!}"+"/"+count;
		        }
		    });
	    });

	     $('#searchButton').on('click', function() {
	        var searchValue = $('#search').val();
	        var from_date = $('#from_date').val();
	        var to_date = $('#to_date').val();
	        // alert("Search Value: " + searchValue);

	        $.ajax({
		        type: "get",
		        url: '{!! route('salesection.filter.data') !!}',
		        data: {
                    search: searchValue,
                    from_date: from_date,
                    to_date: to_date
                },
		        success: function(data)
		        {
		          window.location.href = "{!! route('salesection.filter.data') !!}"+"?search="+searchValue+"&from="+from_date+"&to="+to_date;
		        }
		    });
	    });

	      $('#search').on('keypress', function(event) {
		        if (event.key === "Enter") {
		            event.preventDefault();
		            $('#searchButton').click();
		        }
		    });
	});
</script>

@endsection
