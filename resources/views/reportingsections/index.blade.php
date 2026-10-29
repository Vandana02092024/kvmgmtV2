@extends('layouts.layout')

@section('content')
 <!-- <link media="all" type="text/css" rel="stylesheet" href="https://kvmgmt.com/plugins/datatable/dataTables.bootstrap.min.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs jqueryui/1.12.1/jquery-ui.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->
<script type="text/javascript" src="{{ asset('js/bootstrap-multiselect.js') }}"></script>
<link type="text/css" rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.css') }}" type="text/css">
<style>
  html, body {

  height: 100%;
  margin: 0px;
}
.amcharts-chart-div a {
    display: none!important;
    }
#chartdiv {
    width: 100%;
    height: 100%;
}
span.multiselect-native-select {
    position: relative;
    width: 100%;
    display: block;
    text-align: left;
}button.multiselect.dropdown-toggle.btn.btn-default{
  width: 100%;
  text-align: left;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 42px;
  white-space: nowrap;
}
.btn-group {
    width: 100%;
    position: relative;
}
 td.details-control {
    background: url('img/details_open.png') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('img/details_close.png') no-repeat center center;
}


.cur_data th{
	font-size:12px;
}
.cur_td {
    font-size: 12px;
}
table.dataTable thead .sorting_asc, .sorting_desc, .sorting {
    background-image: none !important;
}
/*.sw-20{
	width: 20%;
}*/

 </style>

<section id="dashboard-page" class="wrap">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<div class="page-heading kv-page-hero kv-reports-hero">
					<h3 class="heading">
						<span class="kv-hero-title"><i class="fa fa-bar-chart"></i><span>Reports &amp; Analytics</span></span>
						<span class="kv-report-actions">
							<a class="v-btn v-btn-primary kv-report-primary-action" id="print_data" href="{{'#'}}">PDF REPORT</a>
							<!-- <a class="v-btn v-btn-primary kv-report-secondary-action" href="{{ url('/dashboard') }}">MENU</a> -->
						</span>
					</h3>
			</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 top-filter">
				<div class="widget">
					<div class="widget-content">
						<ul class="inline filter-list">
							<li class="filter-item">
								{{ csrf_field() }}
								<label>Start Date</label>
								<input type="text" name="" class="form-control filter-sm" value="<?php echo date('m-d-Y',strtotime('-30 days'));?>" autocomplete="off" id="weeklyDatePicker" name="datepick" placeholder=" From ">
							</li>
							<li class="filter-item">
								<label>End Date</label>
								<input type="text" name="" class="form-control filter-sm"   value="<?php echo date('m-d-Y');?>" autocomplete="off" id="weeklyDatePicker1" name="datepick" placeholder=" To ">
							</li>
							<li class="filter-item">
								<label>Sort By</label>
								<select class="form-control filter-sm" id="sort-by" onchange="abc(this.value);">
									<option value="">Select</option>
									<?php
									$selected = '';
									foreach($sorting as $k=>$v){
									?>
									<option value="<?php echo $k; ?>" <?php if($k == 2) echo 'selected';?>><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Location</label>
								<select class="form-control multi-dropdown filter-sm" id="loc" multiple="multiple">
									<!-- <option value=''>Select Location</option> -->
									<?php
									// $selected = '';
									foreach($location as $k=>$v){ ?>
									<option value="<?php echo $k; ?>"
										<?php if($k == $locationId)echo 'selected'; ?> >
										<?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Sales</label>
								<select class="form-control multi-dropdown" id="sales" multiple="multiple">
									<!-- <option value=''>Select Sales</option> -->
									<?php foreach($sales as $k=>$v){ ?>
									<option value="<?php echo $k; ?>" selected><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Discounts</label>
								<select class="form-control multi-dropdown" id="discount" multiple="multiple">
								<!-- 	<option value=''>Select Discounts</option> -->
									<?php foreach($discount as $k=>$v){ ?>
									<option value="<?php echo $k; ?>" selected ><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Adjustments</label>
								<select class="form-control multi-dropdown" id="adjustment" multiple="multiple">
									<!-- <option value=''>Select Adjustment</option> -->
									<?php foreach($adjustment as $k=>$v){ ?>
									<option value="<?php echo $k; ?>" selected ><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Receipts</label>
								<select class="form-control multi-dropdown" id="receipt" multiple="multiple">
									<!-- <option value=''>Select Receipt</option> -->
									<?php foreach($receipt as $k=>$v){ ?>
									<option value="<?php echo $k; ?>" selected ><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<li class="filter-item">
								<label>Data By</label>
								<select class="form-control filter-sm" id="sort-by1">
									<option value="">Select</option>
									<?php
									$selected = '';
									foreach($sorting1 as $k=>$v){
									?>
									<option value="<?php echo $k; ?>" <?php if($k == 0) echo 'selected';?>><?php echo $v; ?></option>

									<?php } ?>
								</select>
							</li>
							<!-- <li class="filter-item">
								<label>Data By</label>
								  <div class="radio" id="sort-by1">
									<?php
									$checked = '';
									foreach($sorting1 as $k=>$v){ ?>
									<label><input type="radio" value="<?php echo $k; ?>" name="optradio" <?php if($k == 0) echo 'checked';?>><?php echo $v; ?></label>
									<?php } ?>
								</div>
							</li> -->
							<li class="filter-item">
								<input class="v-btn v-btn-primary " type="submit" name="submit" id="submit_alert" value="Submit">
							</li>
						</ul>

					</div>
				</div>
			</div>
		</div>
<style type="text/css">
	.stats{    padding: 10px;
    background-color: #fff;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px #ddd;
}
.stats-row{font-size: 14px;
    margin-bottom: 10px;
    font-weight: 700;}
	.stats h4{font-size: 14px;
    color: #9a9a9a;
    margin-bottom: 15px;}
	.stats p{    font-size: 11px;
    color: #272727;}
	.filter-list{display: inline-block;}
	.filter-list .filter-item{display: inline-block; padding:0 9px; border-right: 1px solid #eee; margin-bottom: 5px;}
	.filter-list .filter-item:last-child{padding-right: 0; border-right: 0;}
	.filter-list .filter-item .filter-sm{width: 100px;}
	/* Keep the filter dropdown menus outside the widget's clipping boundary. */
	.top-filter { position: relative; z-index: 30; }
	.top-filter .widget { position: relative; overflow: visible !important; }
	.top-filter .widget-content { overflow: visible !important; }
	.top-filter .multiselect-native-select,
	.top-filter .multiselect-native-select .btn-group { position: relative; }
	.multiselect{max-width: 130px!important; min-width: 130px!important;}
	.top-filter .filter-list .filter-item .multiselect-native-select,
	.top-filter .filter-list .filter-item .multiselect-native-select .btn-group { width: 156px; }
	.top-filter button.multiselect { width: 156px!important; min-width: 156px!important; max-width: 156px!important; }
	.top-filter .multiselect-selected-text { display: inline-block; min-width: 0; max-width: calc(100% - 22px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.top-filter button.multiselect .caret { flex: 0 0 auto; margin-left: 8px; }
	.top-filter .multiselect-container { z-index: 2000; min-width: 100%; max-width: 320px; max-height: 300px; overflow-x: hidden; overflow-y: auto; }
	@media (max-width: 900px) {
		.top-filter .filter-list .filter-item .multiselect-native-select,
		.top-filter .filter-list .filter-item .multiselect-native-select .btn-group { width: 32vw; max-width: 156px; min-width: 110px; }
		.top-filter button.multiselect { width: 32vw!important; min-width: 110px!important; max-width: 156px!important; }
	}
	#chartdiv-pre {width: 100%; height: 300px;}
	#chartdiv-month {width: 100%;height: 300px;}
	#linechart-test1 {width: 100%; height: 300px;}
	#linechart-test2 {width: 100%; height: 300px;}
	.chart-icon .fa-line-chart:before {background-color: transparent;};
</style>

		<div class="row">
			<div class="col-md-12">
				<div class="widget">
					<div class="widget-header" style="padding-bottom: 0">
						<h4 class="">Reporting Data Chart</h4>
					</div>
					<div class="widget-content"  style=" padding: 0;">
						<div id="chartdiv" style="height:300px;width: 100%;"></div>
					</div>
					<div class="widget-footer">

					</div>
				</div>
			</div>
		</div>
		<div class="row">
      <div class="col-md-12">
        <div class="widget">
          <div class="widget-content" style="min-height: 300px;">
            <div class="responsive-table" id="report_data">
              <table id="example" class="display nowrap" style="width:100%">
                    <thead  id="table_thead">

                    </thead>
                </table>
            </div>
          </div>
        </div>
      </div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="widget">
					<div class="widget-header" style="padding-bottom: 10px;">
						<h4 class="">Current Reporting Data - <span style="font-size: 14px;">{{ session()->get('locationName') }}</span>
						</h4>
					</div>
					<div class="widget-content"  style=" padding: 0;">
						<div style="height:190px;width: 100%;">
							<table id="current_date" class="table display nowrap" style="width:100%; border: 1px solid #fff;">

								@foreach($currentData as $key=>$val)
								@foreach($currentMonth as $key1=>$val1)
								@foreach($currentYear as $key2=>$val2)
								@foreach($preYear as $key3=>$val3)
								<tr style="background: #ddd" class="cur_data">
									<th></th>
									<th>Today<br>(<?php echo date("m/Y") ?>)</th>
									<th>Current<br>Month<br>(<?php echo date("m/Y") ?>)</th>
									<th>Current<br>Year<br>(<?php echo date("Y") ?>)</th>
									<th>Previous<br>Year<br>(<?php echo date("Y",strtotime("-1 year")) ?>)</th>
								</tr>
								<tr>
									<td class="cur_td"><b>Sale</b></td>
									<td>${{$val->sale_sales}}</td>
									<td>${{$val1->sale_sales}}</td>
									<td>${{$val2->sale_sales}}</td>
									<td>${{$val3->sale_sales}}</td>
								</tr>
								<tr>
									<td class="cur_td"><b>Discount</b></td>
									<td>${{$val->dis_sales}}</td>
									<td>${{$val1->dis_sales}}</td>
									<td>${{$val2->dis_sales}}</td>
									<td>${{$val3->dis_sales}}</td>
								</tr>
								<tr>
									<td class="cur_td"><b>Adjustment</b></td>
									<td>${{$val->adj_sales}}</td>
									<td>${{$val1->adj_sales}}</td>
									<td>${{$val2->adj_sales}}</td>
									<td>${{$val3->adj_sales}}</td>
								</tr>

								<tr>
									<td class="cur_td"><b>Net Sales</b></td>
									<td>${{$val->rec_sales}}</td>
									<td>${{$val1->rec_sales}}</td>
									<td>${{$val2->rec_sales}}</td>
									<td>${{$val3->rec_sales}}</td>
								</tr>
								@endforeach
								@endforeach
								@endforeach
								@endforeach
							</table>
						</div>
					</div>
					<div class="widget-footer">
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-12">
						<h4 class="stats-row" style="float: left;">Last Week - Total Amount</h4>
						<div style="float: right; font-size: 20px;">
						<span style="font-size: 14px;">Flow Chart - </span><a href="#" class="chart-icon" style="color: #f6c622;" data-toggle="modal" data-target="#myModal"><i class="fa fa-line-chart" aria-hidden="true"></i></a>
						</div>
					</div>
				</div>
				<div class="row">
					@foreach($preWeek as $key3=>$val3)
					<div class="col-md-3">
						<div class="stats">
							<h4>Sales</h4>
							<p>${{$val3->sale_sales}}</p>
						</div>
					</div>

					<div class="col-md-3">
						<div class="stats">
							<h4>Discounts</h4>
							<p>${{$val3->dis_sales}}</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="stats">
							<h4>Adjustment</h4>
							<p>${{$val3->adj_sales}}</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="stats">
							<h4>Net Sales</h4>
							<p>${{$val3->rec_sales}}</p>
						</div>
					</div>
						@endforeach
				</div>
				<div class="row">
					<div class="col-md-12">
						<h4 class="stats-row">Last Month - Total Amount</h4>
					</div>
				</div>
				<div class="row">
					@foreach($preMonth as $key4=>$val4)
					<div class="col-md-3">
						<div class="stats">
							<h4>Sales</h4>
							<p>${{$val4->sale_sales}}</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="stats">
							<h4>Discounts</h4>
							<p>${{$val4->dis_sales}}</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="stats">
							<h4>Adjustment</h4>
							<p>${{$val4->adj_sales}}</p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="stats">
							<h4>Net Sales</h4>
							<p>${{$val4->rec_sales}}</p>
						</div>
					</div>
					@endforeach
				</div>
				<div>
				<!-- <a href="#" data-toggle="modal" data-target="#myModal"><i class="fa fa-line-chart" aria-hidden="true"></i></a> -->
				<div class="modal fade" id="myModal" role="dialog">
    				<div class="modal-dialog modal-lg">
    					 <div class="modal-content">
    					 	<div class="modal-header">
					          <button type="button" class="close" data-dismiss="modal">&times;</button>
					          <h4 class="modal-title">Month & Week - Flow Chart</h4>
					          <span style="font-size: 14px;">{{ session()->get('locationName') }}</span>
					        </div>
					        <div class="modal-body">
					        	<div class="container-fluid">
					        		<div class="row">
					        			<div class="col-md-6">
							        		<h4>Last Week - Total Amount</h4>
							        		<div id="chartdiv-pre" style="width: 100%"></div>
					        			</div>
					        			<div class="col-md-6">
							        		<h4>Last Month - Total Amount</h4>
							        		<div id="chartdiv-month"  style="width: 100%"></div>
					        			</div>
					        		</div>
					        	</div>
					        </div>
					         <div class="modal-footer">
					          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					        </div>
    					 </div>
    				</div>
    			</div>
			</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">

				<div class="widget">
					<div class="widget-header">
						<h4><span id="dynCont"></span>Total Sales by Location </h4>
					</div>
					<div class="widget-content">
						<div id="linechart-test1">	</div>
					</div>
				</div>




			</div>

			<div class="col-md-6">
				<div class="widget">
					<div class="widget-header">
				<h4>Total Units by Location</h4>
				</div>
				<div class="widget-content">
						<div id="linechart-test2"></div>
					</div>

			</div>
		</div>
	</div>
</section>
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/themes/none.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script type="text/javascript">
$(function () {
    // Initialize the multi-select dropdown independently from optional datepicker
    // plugins so a missing datepicker CDN cannot leave native list boxes visible.
    if ($.fn.multiselect) {
        $('.multi-dropdown').multiselect({
            includeSelectAllOption: true,
            selectAllText: ' Select All',
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            maxHeight: 300,
            buttonWidth: '100%',
            numberDisplayed: 1,
            nonSelectedText: 'Choose options',
            nSelectedText: 'selected',
            allSelectedText: 'All selected'
        });
    }
    if ($.fn.datetimepicker) {
        $('#weeklyDatePicker').datetimepicker({ format: 'MM-DD-YYYY' });
        $('#weeklyDatePicker1').datetimepicker({ format: 'MM-DD-YYYY' });
    }
});
// function fetch_data(fetchLink){
// }
	$(document).ready(function() {
		var strDate = $('#weeklyDatePicker').val();
		var endDate = $('#weeklyDatePicker1').val();
		var sortBy = $('#sort-by').val();
		var sortBy1 = $('#sort-by1').val();
		if(sortBy1==2){
			var sign = '';
		}
		else{
			var sign = '$';
		}
		// var locationGroupBy = $('#location-group-by').val();
		var location = ($('#loc').val()).toString()?($('#loc').val()).toString():0;
		var sale = ($('#sales').val()).toString()?($('#sales').val()).toString():0;
		var discount = ($('#discount').val()).toString()?($('#discount').val()).toString():0;
		var adjustment = ($('#adjustment').val()).toString()?($('#adjustment').val()).toString():0;
		var receipt = ($('#receipt').val()).toString()?($('#receipt').val()).toString():0;

		var url =  "{{ url('/') }}/reportingsections/printReportData/"+strDate+"/"+endDate+"/"+sortBy+"/"+sortBy1+"/"+location+"/"+sale+"/"+discount+"/"+adjustment+"/"+receipt;
		// var val =  "{{ url('/') }}/viewReportingData/"+strDate+"/"+endDate+"/"+sortBy+"/"+location+"/"+sale+"/"+discount+"/"+adjustment+"/"+receipt;
		// fetch_data(val);
    	// Add event listener for opening and closing details
    	// $.fn.DataTable.ext.pager.numbers_length = 5;
    	// $('button.multiselect').addClass('form-control');
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/chartData",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, sortBy1 : sortBy1, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt },
        success: function (response) {
        	var graphdata= response['graph_data'];
        	var graphs= response['graphs'];
        	// $("#report_data").html(response['table_data']);

        	chart(graphdata,graphs,sign);
        },
        error: function (response) {
        }
});
    	var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "post",
			dataType:   "html",
			url:        "{{ url('/') }}/viewReportingData",
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, sortBy1 : sortBy1, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt, _token : _token },
        success: function (response) {
        	$("#report_data").html(response);
        console.log(response,'+++++');
        },
        error: function (response) {
        }
    });
    	    	var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/linechartData",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, sortBy1 : sortBy1, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt, _token : _token },
        success: function (response) {
        	var result1= response['result'];
        	var graphline1= response['graphline'];
        	// $("#report_data").html(response['table_data']);


        	chartline(result1,graphline1);
        },
        error: function (response) {

        }
});
    			var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/linechartUnit",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, location : location, sale : sale, _token : _token },
        success: function (response) {
        	var result2= response['result'];
        	var graphUnit2= response['graphUnit'];
        	// $("#report_data").html(response['table_data']);


        	chartUnit(result2,graphUnit2);
        },
        error: function (response) {

        }
});
function abc(val)
{
	//alert('well'+val);
	if(val==0)
		val = 'Daily';
	if(val==1)
	val = 'Weekly';
	if(val==2)
	val = 'Monthly';
	if(val==3)
	val = 'Yearly';
	$('#dynCont').html(val+' ');
}
});

//print

	$("#print_data").click(function () {
// function genratePdf() {
		var strDate = $('#weeklyDatePicker').val();
		var endDate = $('#weeklyDatePicker1').val();
		var sortBy = $('#sort-by').val();
		var sortBy1 = $('#sort-by1').val();
		if(sortBy1==2){
			var sign = '';
		}
		else{
			var sign = '$';
		}
		// var locationGroupBy = $('#location-group-by').val();
		var location = ($('#loc').val()).toString()?($('#loc').val()).toString():0;
		var sale = ($('#sales').val()).toString()?($('#sales').val()).toString():0;
		var discount = ($('#discount').val()).toString()?($('#discount').val()).toString():0;
		var adjustment = ($('#adjustment').val()).toString()?($('#adjustment').val()).toString():0;
		var receipt = ($('#receipt').val()).toString()?($('#receipt').val()).toString():0;

    	 var url =  "{{ url('/') }}/reportingsections/printReportData/"+strDate+"/"+endDate+"/"+sortBy+"/"+sortBy1+"/"+location+"/"+sale+"/"+discount+"/"+adjustment+"/"+receipt;

			$("#print_data").attr("href",url);
    	 // console.log(url);
		 // fetch_data(val);
});
	//print
function chart(graphdata,graphs,sign) {
		var chart = AmCharts.makeChart("chartdiv", {
			"legend": {
			    "useGraphSettings": true,
			    "position": "top"
			  },
			  "type": "serial",
			     "theme": "none",
			  "categoryField": "department",
			  "rotate": false,
			  "startDuration": 1,
			  "categoryAxis": {
			    "gridPosition": "start",
			    "position": "left"
			  },
			  "trendLines": [],
			  "graphs":graphs,
			  "guides": [],
			  "valueAxes": [
			    {
			      "id": "ValueAxis-1",
			      "position": "top",
			      "axisAlpha": 0,
			      "unitPosition": "left",
			      "unit": sign

			    }
			  ],
			  "allLabels": [],
			  "balloon": {},
			  "titles": [],
			  "dataProvider": graphdata,
			    "export": {
			      "enabled": true
			     }
			});
}
var chart1 = AmCharts.makeChart("chartdiv-pre", {
	"type": "serial",
     "theme": "none",
	"categoryField": "dept",
	"rotate": true,
	"startDuration": 1,
	"categoryAxis": {
		"gridPosition": "start",
		"position": "left"
	},
	"trendLines": [],
	"graphs": [
		{
			"balloonText": "Amount:[[value]]",
			"fillAlphas": 0.8,
			"id": "AmGraph-1",
			"lineAlpha": 0.2,
			"title": "Amount",
			"type": "column",
			"valueField": "amount"
		}
	],
	"guides": [],
	"valueAxes": [
		{
			"id": "ValueAxis-1",
			"position": "top",
			"axisAlpha": 0,
			 "unitPosition": "left",
			      "unit": "$"
		}
	],
	"allLabels": [],
	"balloon": {},
	"titles": [],
	"dataProvider": [
		{
			"dept": 'Sale',
			"amount": "{{str_replace(',','',$val3->sale_sales)}}"

		},
		{
			"dept": 'Discounts',
			"amount": "{{str_replace(',','',$val3->dis_sales)}}"
		},
		{
			"dept": 'Adjustment',
			"amount": "{{str_replace(',','',$val3->adj_sales)}}"
		},
		{
			"dept": 'Net Sales',
			"amount": "{{str_replace(',','',$val3->rec_sales)}}"
		}
	],
    "export": {
    	"enabled": true
     }

});
var chart2 = AmCharts.makeChart("chartdiv-month", {
	"type": "serial",
     "theme": "none",
	"categoryField": "dept",
	"rotate": true,
	"startDuration": 1,
	"categoryAxis": {
		"gridPosition": "start",
		"position": "left"
	},
	"trendLines": [],
	"graphs": [
		{
			"balloonText": "Amount:[[value]]",
			"fillAlphas": 0.8,
			"id": "AmGraph-1",
			"lineAlpha": 0.2,
			"title": "Amount",
			"type": "column",
			"valueField": "amount"
		}
	],
	"guides": [],
	"valueAxes": [
		{
			"id": "ValueAxis-1",
			"position": "top",
			"axisAlpha": 0,
			 "unitPosition": "left",
			      "unit": "$"
		}
	],
	"allLabels": [],
	"balloon": {},
	"titles": [],
	"dataProvider": [
		{
			"dept": 'Sale',
			"amount": "{{str_replace(',','',$val4->sale_sales)}}"

		},

		{
			"dept": 'Discounts',
			"amount": "{{str_replace(',','',$val4->dis_sales)}}"
		},
		{
			"dept": 'Adjustment',
			"amount": "{{str_replace(',','',$val4->adj_sales)}}"
		},
		{
			"dept": 'Net Sales',
			"amount": "{{str_replace(',','',$val4->rec_sales)}}"
		}
	],
    "export": {
    	"enabled": true
     }
});
function chartline(result1,graphline1) {
	var chart3 = AmCharts.makeChart("linechart-test1", {

        "type": "serial",
        "theme": "none",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        "dataDateFormat": "YYYY-MM-DD",
        "valueAxes": [{
            "id":"v1",
            "axisAlpha": 0,
            "position": "left",
			  "unitPosition": "left",
			      "unit": "$"
        }],
        "graphs": graphline1,


        "chartCursor": {
            "cursorPosition": "mouse",
            "pan": true,
             "valueLineEnabled":true,
             "valueLineBalloonEnabled":true
        },
        "categoryField": "sortBy",
        "categoryAxis": {
            "parseDates": false,
            "dashLength": 1,
            "minorGridEnabled": true,
            "position": "top"
        },
        exportConfig:{
          menuRight: '20px',
          menuBottom: '50px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'
          }]
        },
        "dataProvider": result1
    }
);
}
function chartUnit(result2,graphUnit2) {
	var chart4 = AmCharts.makeChart("linechart-test2", {
        "type": "serial",
        "theme": "none",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        // "dataDateFormat": "YYYY-MM-DD",
        "valueAxes": [{
            "id":"v1",
            "axisAlpha": 0,
            "position": "left",
			"unitPosition": "right",

 "unitPosition": "Unit"

        }],
        "graphs": graphUnit2,

        "chartCursor": {
            "cursorPosition": "mouse",
            "pan": true,
             "valueLineEnabled":true,
             "valueLineBalloonEnabled":true
        },
        "categoryField": "name",
        "categoryAxis": {
            "parseDates": false,
            "dashLength": 1,
            "minorGridEnabled": true,
            "position": "bottom"
        },
        exportConfig:{
          menuRight: '20px',
          menuBottom: '50px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'
          }]
        },
        "dataProvider": result2
    }
);
}


	$("#submit_alert").click(function () {

		var strDate = $('#weeklyDatePicker').val();
		var endDate = $('#weeklyDatePicker1').val();
		var sortBy = $('#sort-by').val();
		var sortBy1 = $('#sort-by1').val();
		if(sortBy1==2){
			var sign = '';
		}
		else{
			var sign = '$';
		}
		// var locationGroupBy = $('#location-group-by').val();
		var location = ($('#loc').val()).toString()?($('#loc').val()).toString():0;
		var sale = ($('#sales').val()).toString()?($('#sales').val()).toString():0;
		var discount = ($('#discount').val()).toString()?($('#discount').val()).toString():0;
		var adjustment = ($('#adjustment').val()).toString()?($('#adjustment').val()).toString():0;
		var receipt = ($('#receipt').val()).toString()?($('#receipt').val()).toString():0;
    	 // var val =  "{{ url('/') }}/viewReportingData/"+strDate+"/"+endDate+"/"+sortBy+"/"+location+"/"+sale+"/"+discount+"/"+adjustment+"/"+receipt;
		 // fetch_data(val);
    	var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/chartData",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, sortBy1 : sortBy1, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt, _token : _token },
        success: function (response) {
        	var graphdata= response['graph_data'];
        	var graphs= response['graphs'];
        	// $("#report_data").html(response['table_data']);

        	// console.log(response);
        	chart(graphdata,graphs,sign);
        },
        error: function (response) {

        }
});
			var _token = $('input[name="_token"]').val();
  			$.ajax({
			type:       "post",
			dataType:   "html",
			url:        "{{ url('/') }}/viewReportingData",
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, sortBy1 : sortBy1, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt, _token : _token },
        success: function (response) {
        	$("#report_data").html(response);

        	console.log(response);
        },
        error: function (response) {
        }
    });
    	var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/linechartData",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, location : location, sale : sale, dis : discount, adj : adjustment, rec : receipt, _token : _token },
        success: function (response) {
        	var result1= response['result'];
        	var graphline1= response['graphline'];
        	// $("#report_data").html(response['table_data']);


        	chartline(result1,graphline1);
        },
        error: function (response) {

        }
		});
		var _token = $('input[name="_token"]').val();
    	$.ajax({
			type:       "POST",
			dataType:   "json",
			url:        "{{ url('/') }}/linechartUnit",
			context: document.body,
			global: false,
			async: true,
       		data: { strDate : strDate, endDate : endDate, sortBy : sortBy, location : location, sale : sale, _token : _token },
        success: function (response) {
        	var result2= response['result'];
        	var graphUnit2= response['graphUnit'];
        	// $("#report_data").html(response['table_data']);


        	chartUnit(result2,graphUnit2);
        },
        error: function (response) {

        }
});


});
function chart(graphdata,graphs,sign) {
	var chart = AmCharts.makeChart("chartdiv", {
		"legend": {
	    "useGraphSettings": true,
	    "position": "top"
		  },
		  "type": "serial",
		     "theme": "none",
		  "categoryField": "department",
		  "rotate": false,
		  "startDuration": 1,
		  "categoryAxis": {
		    "gridPosition": "start",
		    "position": "left"
		  },
		  "trendLines": [],
		  "graphs":graphs,
		  "guides": [],
		  "valueAxes": [
		    {
		      "id": "ValueAxis-1",
		      "position": "top",
		      "axisAlpha": 0,
			   "unitPosition": "left",
			      "unit": sign
		    }
		  ],
		  "allLabels": [],
		  "balloon": {},
		  "titles": [],
		  "dataProvider": graphdata,
		    "export": {
		      "enabled": true
		     }
		});
}
function chartline(result1,graphline1) {
	var chart3 = AmCharts.makeChart("linechart-test1", {
        "type": "serial",
        "theme": "none",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        "dataDateFormat": "YYYY-MM-DD",
        "valueAxes": [{
            "id":"v1",
            "axisAlpha": 0,
            "position": "left",
			      "unitPosition": "left",
			      "unit": "$"
        }],
        "graphs": graphline1,

        "chartCursor": {
            "cursorPosition": "mouse",
            "pan": true,
             "valueLineEnabled":true,
             "valueLineBalloonEnabled":true
        },
        "categoryField": "sortBy",
        "categoryAxis": {
            "parseDates": false,
            "dashLength": 1,
            "minorGridEnabled": true,
            "position": "bottom"
        },
        exportConfig:{
          menuRight: '20px',
          menuBottom: '50px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'
          }]
        },
        "dataProvider": result1
    }
);
}

function chartUnit(result2,graphUnit2) {
	var chart4 = AmCharts.makeChart("linechart-test2", {
        "type": "serial",
        "theme": "none",
        "pathToImages": "http://www.amcharts.com/lib/3/images/",
        // "dataDateFormat": "YYYY-MM-DD",
        "valueAxes": [{
            "id":"v1",
            "axisAlpha": 0,
            "position": "left",
			       "unitPosition": "right",
			      "unit": " Unit"
        }],
        "graphs": graphUnit2,

        "chartCursor": {
            "cursorPosition": "mouse",
            "pan": true,
             "valueLineEnabled":true,
             "valueLineBalloonEnabled":true
        },
        "categoryField": "name",
        "categoryAxis": {
            "parseDates": false,
            "dashLength": 1,
            "minorGridEnabled": true,
            "position": "bottom"
        },
        exportConfig:{
          menuRight: '20px',
          menuBottom: '50px',
          menuItems: [{
          icon: 'http://www.amcharts.com/lib/3/images/export.png',
          format: 'png'
          }]
        },
        "dataProvider": result2
    }
);
}

// chartline.addListener("rendered", zoomChart);

// zoomChart();
// function zoomChart(){
//     //chart.zoomToIndexes(chart.dataProvider.length - 40, chart.dataProvider.length - 1);
//     chart.zoomToIndexes(0, 20);
// }
function abc(val)
{
	//alert('well'+val);
	if(val==0)
		val = 'Daily';
	if(val==1)
	val = 'Weekly';
	if(val==2)
	val = 'Monthly';
	if(val==3)
	val = 'Yearly';
	$('#dynCont').html(val+' ');
}
</script>
@endsection
