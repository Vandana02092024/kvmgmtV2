@extends('layouts.layout')

@section('content')


<?php


function getDatesFromRange($start, $end, $format = 'Y-m-d') {
    $array = array();
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    return $array;
}

$dataList = array();
if (is_array($weekData) || is_object($weekData)){
foreach($weekData as $k){

	$k = (array) $k;
	$dataList[$k['name'].'-'.$k['dataDate'].'-'.$k['nameDep']] = $k;

}}

$dataListDiscount = array();
if (is_array($disData) || is_object($disData)){
foreach($disData as $k){
	$k = (array) $k;
	$dataListDiscount[$k['name'].'-'.$k['dataDate'].'-'.$k['nameDep']] = $k;
}
}

$dataListAdjust = array();
if (is_array($adjustData) || is_object($adjustData)){
foreach($adjustData as $k){

	$k = (array) $k;
	$dataListAdjust[$k['name'].'-'.$k['dataDate'].'-'.$k['nameDep']] = $k;
}
}



$dataListRec = array();
if (is_array($recData) || is_object($recData)){
foreach($recData as $k){
	$k = (array) $k;
	$dataListRec[$k['name'].'-'.$k['dataDate'].'-'.$k['nameDep']] = $k;

}
}
$dates = getDatesFromRange($sDate, $eDate);
?>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<!--<script type="text/javascript" src="http://actiknowdemo.com/timesheet-view/js/jquery-3.2.1.min.js"></script>-->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<section id="dashboard-page" class="wrap">
	<div class="container-fluid">



		@foreach(session('sales_pdf_warnings', []) as $warning)
<div class="alert alert-warning">{{ $warning }}</div>
@endforeach
@if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
<div id="printablediv" style="page-break-before: always; page-break-after: always;">
	<section id="dashboard-page" class="wrap">
		<div class="container-fluid">
			<div class="row" style="display:flex; align-items:center; justify-content:between; vertical-align: middle;">
				<div class="col" style="">
					<img src="{{ asset('resources/views/Logo.png')}}" style="width:150px">
				</div>
				<div class="col"style="width: 200px;">
					<div class="page-heading" style="margin-bottom:0; text-align:center">
						<h4 class="heading"> {{ session()->get('locationCode') }} - {{ session()->get('locationName') }}</h4>
					</div>
				</div>
				<div class="col"style="width: 200px;">
					<div class="page-heading" style="margin-bottom:0; text-align:center">
						<h3 class="heading">{{ session()->get('entityName') }} - Weekly Sales Report</h3>
					</div>
				</div>
				<div class="col"style="width: 200px;">
					<div class="page-heading" style="margin-bottom:0; text-align:center">
						<h4> <span  style="float:right;"> Calender Week: {{$report_week}} </span></h4>
					</div>
				</div>
				<div class="col"style="width: 200px;">
					<div class="page-heading" style="margin-bottom:0; text-align:center">
						<h4><span style="float:right;">  Week: {{$store_week}} </span></h4>
					</div>
				</div>
					<form class="pull-right inline"  method="POST" action="{{ url('/salesection	') }}">
						{{ csrf_field() }}
					<!--<ul class="pull-right inline quick-info">

						<li>Calendar week</li>
						<li><span id="weekNoData"></span></li>
						<li>Store week</li>
						<li><span id="storeNoData"></span></li>
						<li>
							<div class="input-group" id="DateDemo">
								<input type="text" class="" id='weeklyDatePicker' name="datepick" placeholder="Select Week" />
							</div>
						</li>
						<input class="v-btn v-btn-primary" type="submit" name="submit" id="weekDatapick" value="FILTER">
					</form>
					<a class="v-btn v-btn-primary pull-right" href="{{ url('/salesection') }}">BACK</a>
					</ul>-->

			</div>
		</div>
		<input type="hidden" id="startDate" value="{{ $startDatestore }}">

   <!-- <div class="filter-container">
      <div class="row">
         <div class="col-md-2">
            <label>Calendar Week</label>
            <span style="color: #f6c622;margin-left: 5px;font-weight: bold;">1234</span>
         </div>
         <div class="col-md-2">
            <label>Store Week</label>
            <span style="color: #f6c622;margin-left: 5px;font-weight: bold;">1234</span>
         </div>
         <div class="col-md-8 text-right">
            <label >Day/Date</label>
            <select class="day-filter">
               <option>Sat &nbsp; &nbsp;23-05-2015</option>
               <option>Sun &nbsp; &nbsp;23-05-2015</option>
               <option>Mon&nbsp; &nbsp;23-05-2015</option>
            </select>
         </div>
      </div>
   </div> -->
   <style>
     .custom-table-print>tbody>tr>td>table>thead>tr>th,.custom-table-print>tbody>tr>td>table>tfoot>tr>th
	 {
		 text-align:left;
	 }
   </style>


             <table class="table custom-table-print custom-table" cellspacing="0" cellpadding="5px" border="1" style="width:100%;">
				<tr valign="top">
					<td>
                     <table class="table custom-table"   cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th width="70">SALES</th>
                              <th width="70">UNIT</th>
                              <th>AMOUNT</th>
                           </tr>
                        </thead>
                        <tbody>
						<?php
						$totalSaleValue = 0;
						foreach($saleSum as $key=>$val){
							$totalSaleValue+= $val->amount;
							?>
                           <tr>
                              <td ><?php echo $val->name; ?></td>
                              <td align="center" class="<?php echo str_replace(' ','_',$val->name).'2'; ?>"><?php echo $val->unit; ?></td>
							 <!--<input type="hidden" class="saleSum <?php echo str_replace(' ','_',$val->name).'1'; ?>" value="<?php echo $val->amount; ?>">-->
                              <td  align="right" class="<?php echo str_replace(' ','_',$val->name); ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                           </tr>
						<?php } ?>

                        </tbody>
                           <tr>
                              <td width="70">Total</td>
                              <td width="70"></td>
                              <td style="float:right!important;" id="saletotal"><?php echo "$".number_format($totalSaleValue,2); ?></td>
                           </tr>
                     </table>
					</td>
					<td>
                     <table class="table custom-table"  cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th width="150">Discount & Credit</th>
                              <td></td>
                           </tr>
                        </thead>
                        <tbody>
						   <?php
						   $totalDiscountValue = 0;
						   foreach($disSum as $key=>$val){
							   $totalDiscountValue+= $val->amount;
							?>
                           <tr>
                              <td><?php echo $val->name; ?></td>
							  <input type="hidden" class="disSum <?php echo str_replace(' ','_',$val->name).'_73'; ?>" value="<?php echo $val->amount; ?>">
                              <td style="color:red; float:right!important;" class="<?php echo str_replace(' ','_',$val->name)."_7"; ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                           </tr>
							<?php } ?>

                           <tr>
                              <td width="150">Total</td>
                              <td style="color:red; float:right!important;" id="distotal"><?php echo "$".number_format($totalDiscountValue,2); ?></td>
                           </tr>
                        </tbody>
                     </table>
					</td>
					<td>
                     <table class="table custom-table" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th width="150">ADJUSTMENTS</th>
                              <td></td>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
						   $totalAdjustValue = 0;
						   $floatRight = 'pull-right';
						   foreach($adjSum as $key=>$val){
							$totalAdjustValue+= $val->amount;
							  $color = '';

							  if($val->name != 'Sales Tax'){
								  $color = 'red';
							  }else {
								  $color = 'black';
							  }
							?>
                           <tr>
                              <td><?php echo $val->name; ?></td>
							  <input type="hidden" class="adjSum <?php echo str_replace(' ','_',$val->name).'_84'; ?>" value="<?php echo $val->amount; ?>">
                              <td style="color:<?php echo $color; ?>;float: right!important;" class="<?php echo str_replace(' ','_',$val->name)."_8"; ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                           </tr>
							<?php } ?>
                           <tr>
                              <td width="150">Total</td>
                              <td style="float:right;" id="adjtotal <?=$floatRight?>"> <?php echo "$".number_format($totalAdjustValue,2); ?></td>
                           </tr>
                           <tr>
                              <th>&nbsp;</th>
                              <th>&nbsp;</th>
                           </tr>
                           <tr>
                              <td width="150">Over/(Short) </td>
                              <td align="right" id="finalovertotalVal" ></td>
                           </tr>
                        </tbody>
                     </table>
					</td>
					<td>
                     <table class="table custom-table" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th width="150">RECEIPTS</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
							$totalRecValue = 0;
							foreach($recSum as $key=>$val){
							   $totalRecValue+= $val->amount;
							?>
                           <tr>
                              <td><?php echo $val->name; ?></td>
							  <input type="hidden" class="recSum <?php echo str_replace(' ','_',$val->name).'_95'; ?>" value="<?php echo $val->amount; ?>">
                              <td align="right" class="<?php echo str_replace(' ','_',$val->name)."_9"; ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                           </tr>
							<?php } ?>
                           <tr>
                              <td width="150">Total</td>
                              <td align="right" id="rectotal"><?php echo "$".number_format($totalRecValue,2); ?></td>
                           </tr>
                        </tbody>
                     </table>
					</td>
					<td>
                     <table class="table custom-table" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th width="150">TOTAL SALES</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                          <tr>
                             <td width="150">Gross Sales</td>
                             <td align="right" id="grasssale"  style="text-align: right;"> ${{ number_format($totalSaleValue,2) }}</td>
                          </tr>
                           <tr>
                              <td width="150">Net Sales</td>
                              <td id="netsale" style="text-align: right;">${{ number_format($totalRecValue,2) }}  </td>
                           </tr>
                        </tbody>
                     </table>
					</td>
					<td>
                     <table class="table custom-table" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px !important;font-weight:normal;">
                        <thead>
                           <tr>
                              <th>LABOR</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                          <tr>
                             <td width="150">Labor %</td>
                             <?php  $tot_sale = getLaberRate(Session::get('location_id'),$id); ?>
                             <td id="lbper"  style="text-align: right;">  @if($totalRecValue>0 && $tot_sale >0){{ number_format(($tot_sale/$totalRecValue)*100,2)}}% @else 0.00% @endif</td>
                          </tr>
                        </tbody>
                     </table>
					</td>
				</tr>
			</table>
  <!--  <div class="filter-container">
      <div class="row">
         <div class="col-md-2">
            <label>Sales</label>
         </div>
      </div>
   </div> -->

   <div class="row">
      <div class="col-md-12">
         <div class="widget form-layout" style="margin-bottom:10px;margin-top:10px;">
            <div class="widget-content" style="padding: 0;">
               <div class="row">
                  <div class="col-md-12">
                     <table class="table custom-table" border="1" cellspacing="0" style="width:100%;font-size9px;font-family:calibri;">
					  <colgroup>
						<col span="1" style="background-color:#fff">
						<col span="2" style="background-color:#eee">
						<col span="2" style="background-color:#fff">
						<col span="2" style="background-color:#eee">
						<col span="2" style="background-color:#fff">
						<col span="2" style="background-color:#eee">
					  </colgroup>
                        <thead>
                           <tr class="thead-Dark">
                              <th style="font-weight:normal;font-size:10px;">Date</th>
                              <th colspan="2" id="day1" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[0])); ?></th>
							  <!--<th colspan="2" id="day2" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[1])); ?></th>-->
							  <th colspan="2" id="day3" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[2]));; ?></th>
							  <th colspan="2" id="day4" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[3]));; ?></th>
							  <th colspan="2" id="day5" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[4]));; ?></th>
							  <th colspan="2" id="day6" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[5]));; ?></th>
							  <th colspan="2" id="day7" class="text-center" style="font-weight:normal;font-size:9px;"><?php echo date('l m-d-Y',strtotime($dates[6]));; ?></th>


                           </tr>

                           <tr>
                              <th></th>
                              <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
                              <!--<th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>-->
                              <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
                              <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
                              <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
                              <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
							  <th class="text-center" style="width:59px;font-weight:normal;font-size:10px;">Units</th>
                              <th class="text-center" style="font-weight:normal;font-size:10px;">Amount</th>
                           </tr>
                        </thead>
                        <tbody>
						<tr class="thead-Dark">
                              <th colspan="13" style="font-size:10px; justify-content:center">SALES</th>
                           </tr>
						  <?php
							$saleArray = $disArray = $adjustArray = $receiptArray = array();
							$weekCal = $calendarWeekMatch;
							$storeCal = $storeWeekMatch;
							$i = 0;
								foreach($saleData as $saleD => $sd){
										if($sd->saleStatus == '0'){
											$saleArray[$i]['id']= $sd->id;
											$saleArray[$i]['name']= $sd->name;
										}
										if($sd->saleStatus == '1' && ($sd->storeWeekNo == $weekCal) && ($sd->calWeekNo == $storeCal) ){
											$name = $sd->name;
											$saleArray[$i]['id']= $sd->id;
											$saleArray[$i]['name']=$name;
										}
										$i++;
								}
								foreach($discountData as $dD => $dd){
										if($dd->disStatus == '0'){
											$disArray[$i]['id']= $dd->id;
											$disArray[$i]['name']= $dd->name;
										}
										if($dd->disStatus == '1' && ($dd->storeWeekNo == $weekCal) && ($dd->calWeekNo == $storeCal) ){
											$name = $dd->name;
											$disArray[$i]['id']= $dd->id;
											$disArray[$i]['name']=$name;
										}
										$i++;
								}
								foreach($adjustmentData as $adjD => $adj){
										if($adj->adjStatus == '0'){
											$adjustArray[$i]['id']= $adj->id;
											$adjustArray[$i]['name']= $adj->name;
										}
										if($adj->adjStatus == '1' && ($adj->storeWeekNo == $weekCal) && ($adj->calWeekNo == $storeCal) ){
											$name = $adj->name;
											$adjustArray[$i]['id']= $adj->id;
											$adjustArray[$i]['name']=$name;
										}
										$i++;
								}

								foreach($receiptData as $recD => $rD){
										if($rD->recStatus == '0'){
											$receiptArray[$i]['id']= $rD->id;
											$receiptArray[$i]['name']= $rD->name;
										}
										if($rD->recStatus == '1' && ($rD->storeWeekNo == $weekCal) && ($rD->calWeekNo == $storeCal) ){
											$name = $rD->name;
											$receiptArray[$i]['id']= $rD->id;
											$receiptArray[$i]['name']=$name;
										}
										$i++;
								}

							?>



							<?php
							$amountSaleValueRowSaturday = $amountSaleValueRowSunday = $amountSaleValueRowMonday = $amountSaleValueRowTuesday = $amountSaleValueRowWednesday = $amountSaleValueRowThrusday = $amountSaleValueRowFriday = 0;
							$unitSaleValueRowSaturday = $unitSaleValueRowSunday = $unitSaleValueRowMonday = $unitSaleValueRowTuesday = $unitSaleValueRowWednesday = $unitSaleValueRowThrusday = $unitSaleValueRowFriday = 0;
							foreach($saleArray as $sd){
							?>
                           <tr >
							<td style="font-weight:normal;font-size:10px;"><?php echo $sd['name'] ; ?></td>
								<?php
								$match = $sd['name'].'-'.$dates[0].'-'.'sale';
								$unit = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);
								$amount = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);
								$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
								$unitSaleValueRowSaturday+= $unit;
								$amountSaleValueRowSaturday+= $amount;


								?>

							   <td style="font-size:9px;"><?php  echo $unit ?></td>
							   <td style="font-size:9px;position:relative">$ <?php  echo $amount; ?></td>


								<?php $match = $sd['name'].'-'.$dates[1].'-'.'sale';$unitsun = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amountsun = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
								$unitSaleValueRowSunday+= $unitsun;
								$amountSaleValueRowSunday+= $amountsun;
								?>
							   <!--<td style="font-size:9px;"><?php  echo $unitsun ?></td>
							   <td style="font-size:9px;position:relative">$<?php  echo $amountsun ?></td>-->

							   <?php $match = $sd['name'].'-'.$dates[2].'-'.'sale';$unitmon = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amountmon = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
							    $unitSaleValueRowMonday+= $unitmon;
								$amountSaleValueRowMonday+= $amountmon;
								?>
							   <td style="font-size:9px;"><?php  echo $unitmon ?></td>
							   <td style="font-size:9px;position:relative">$<?php  echo $amountmon ?></td>


							   <?php $match = $sd['name'].'-'.$dates[3].'-'.'sale';$unittue = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amounttue = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
							   $unitSaleValueRowTuesday+= $unittue;
							   $amountSaleValueRowTuesday+= $amounttue;
							   ?>
							   <td style="font-size:9px;"><?php  echo $unittue ?></td>
							   <td style="font-size:9px;position:relative">$<?php  echo $amounttue ?></td>

							   <?php $match = $sd['name'].'-'.$dates[4].'-'.'sale';$unitwed = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amountwed = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
							   $unitSaleValueRowWednesday+= $unitwed;
								$amountSaleValueRowWednesday+= $amountwed;
							   ?>
							   <td style="font-size:9px;"><?php echo $unitwed; ?></td>
							   <td style="font-size:9px;position:relative">$<?php echo $amountwed; ?></td>

							   <?php $match = $sd['name'].'-'.$dates[5].'-'.'sale';$unitthr = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amountthr = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
							   $unitSaleValueRowThrusday+= $unitthr;
								$amountSaleValueRowThrusday+= $amountthr;
							   ?>
							   <td style="font-size:9px;"><?php echo $unitthr ;?></td>
							   <td style="font-size:9px;position:relative">$<?php echo $amountthr; ?></td>

							   <?php $match = $sd['name'].'-'.$dates[6].'-'.'sale';$unitsun = (empty($dataList[$match]['unit'])?'0':$dataList[$match]['unit']);$amountsun = (empty($dataList[$match]['amount'])?'0':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':$dataList[$match]['comment']);
							   $unitSaleValueRowFriday+= $unitsun;
							   $amountSaleValueRowFriday+= $amountsun;
							   ?>
							   <td style="font-size:9px;"><?php echo $unitsun; ?></td>
							   <td style="font-size:9px;position:relative">$<?php echo $amountsun; ?></td>

                           </tr>
							<?php } ?>

                           <tr>
                              <th style="font-size:10px;font-weight:normal;">Total</th>
                              <td><span readonly name="" id="sumunitmonday" class="sale " placeholder="" style="font-size:10px;"><?php echo$unitSaleValueRowSaturday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtmonday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowSaturday,2); ?></span></td>

                             <!-- <td><span readonly name="" id="sumunittuesday" class="sale " placeholder="" style="font-size:10px;"><?php echo$unitSaleValueRowSunday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamttuesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowSunday,2); ?><span></td>-->

                              <td><span readonly name="" id="sumunitwednesday" class="sale " placeholder="" style="font-size:12px;"><?php echo$unitSaleValueRowMonday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtwednesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowMonday,2); ?><span></td>

                              <td><span readonly name="" id="sumunitthrusday" class="sale " placeholder="" style="font-size:10px;"><?php echo$unitSaleValueRowTuesday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtthrusday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowTuesday,2); ?><span></td>

                              <td><span readonly name="" id="sumunitfriday" class="sale " placeholder="" style="font-size:10px;"><span><?php echo$unitSaleValueRowWednesday; ?></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtfriday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowWednesday,2); ?><span></td>

                              <td><span readonly name="" id="sumunitsaturday" class="sale " placeholder="" style="font-size:10px;"><?php echo$unitSaleValueRowThrusday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtsaturday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowThrusday,2); ?><span></td>

							  <td><span readonly name="" id="sumunitsunday" class="sale " placeholder="" style="font-size:10px;"><?php echo$unitSaleValueRowFriday; ?><span></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="sumamtsunday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountSaleValueRowFriday,2); ?><span></td>
                           </tr>

						   <tr class="thead-Dark">
								<th colspan="13"  style="font-size:10px;">DISCOUNTS & CREDITS</th>

                           </tr>
                          <?php
							$amountDistValueRowSaturday = $amountDistValueRowSunday = $amountDistValueRowMonday = $amountDistValueRowTuesday = $amountDistValueRowWednesday = $amountDistValueRowThrusday = $amountDistValueRowFriday = 0;
							foreach($disArray as $dd){
							?>
                           <tr>
                              <th style="font-weight:normal;font-size:10px;"><?php echo $dd['name'] ; ?></th>
							  <?php $match = $dd['name'].'-'.$dates[0].'-'.'dis';
							  $amount = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);
							  $comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);

							  $amountDistValueRowSaturday+= $amount;
									if($dd['name'] == 'Refund'){
										$refundvalueSat = $amount;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueSat = $amount;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueSat = $amount;
									}

							  ?>
                              <td><input type="hidden" name="" class="" placeholder="$45"></td>
						      <td style="font-size:9px;position:relative">$<?php echo $amount; ?></td>

							  <?php $match = $dd['name'].'-'.$dates[1].'-'.'dis';$amountsun = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							  $amountDistValueRowSunday+= $amountsun;
							  if($dd['name'] == 'Refund'){
										$refundvalueSun = $amountsun;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueSun = $amountsun;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueSun = $amountsun;
									}

							  ?>
                              <!--<td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountsun; ?></td>-->

							  <?php $match = $dd['name'].'-'.$dates[2].'-'.'dis';$amountmon = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							  $amountDistValueRowMonday+= $amountmon;
							  if($dd['name'] == 'Refund'){
										$refundvalueMon = $amountmon;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueMon = $amountmon;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueMon = $amountmon;
									}

							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountmon; ?></td>

							  <?php $match = $dd['name'].'-'.$dates[3].'-'.'dis';$amounttue = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							  $amountDistValueRowTuesday+= $amounttue;
							  if($dd['name'] == 'Refund'){
										$refundvalueTue = $amounttue;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueTue = $amounttue;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueTue = $amounttue;
									}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amounttue; ?></td>

							  <?php $match = $dd['name'].'-'.$dates[4].'-'.'dis';$amountwed = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							  $amountDistValueRowWednesday+= $amountwed;
							  if($dd['name'] == 'Refund'){
										$refundvalueWed = $amountwed;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueWed = $amountwed;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueWed = $amountwed;
									}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountwed; ?></td>

							  <?php $match = $dd['name'].'-'.$dates[5].'-'.'dis';$amountthr = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							  $amountDistValueRowThrusday+= $amountthr;
							  if($dd['name'] == 'Refund'){
										$refundvalueThr = $amountthr;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueThr = $amountthr;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueThr = $amountthr;
									}
							  ?>
								<td><input type="hidden" name="" class="" placeholder=""></td>
								<td style="font-size:9px;position:relative">$<?php echo $amountthr; ?></td>

							 <?php $match = $dd['name'].'-'.$dates[6].'-'.'dis';$amountfri = (empty($dataListDiscount[$match]['amount'])?'0':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':$dataListDiscount[$match]['comment']);
							 $amountDistValueRowFriday+= $amountfri;
							 if($dd['name'] == 'Refund'){
										$refundvalueFri = $amountfri;
									}
									if($dd['name'] == 'Coupons'){
									$couponvalueFri = $amountfri;
									}
									if($dd['name'] == 'Final Discounts'){
									$finalDiscountvalueFri = $amountfri;
									}
							 ?>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountfri; ?></td>
                           </tr>
							<?php } ?>
                           <tr>
                              <th style="font-size:10px;font-weight:normal;">Total</th>
						      <td><input type="hidden" name="" class="" placeholder="$45"></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtmonday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowSaturday,2); ?><span></td>
                             <!-- <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamttuesday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red;font-size:10px;"><?php echo "$".number_format($amountDistValueRowSunday,2); ?><span></td>-->
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtwednesday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowMonday,2); ?><span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtthrusday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowTuesday,2); ?><span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtfriday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowWednesday,2); ?><span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtsaturday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowThrusday,2); ?><span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="disamtsunday" class="sale " placeholder="" style="width:65px;display:inline-block;color:red; float:right!important; font-size:10px;"><?php echo "$".number_format($amountDistValueRowFriday,2); ?><span></td>
                           </tr>
						   <tr class="thead-Dark">
                              <th colspan="13"  style="font-size:10px;">ADJUSTMENTS</th>
                           </tr>
						   <?php
						   $amountAdjustValueRowSaturday = $amountAdjustValueRowSunday = $amountAdjustValueRowMonday = $amountAdjustValueRowTuesday = $amountAdjustValueRowWednesday = $amountAdjustValueRowThrusday = $amountAdjustValueRowFriday = 0;
							foreach($adjustArray as $adj){
							?>
                           <tr>
                              <th style="font-weight:normal;font-size:10px;"><?php echo $adj['name'] ; ?></th>
							  <?php
							  $color = '';
							  if($adj['name'] != 'Sales Tax'){
								  $color = 'red';
							  }else {
								  $color = 'black';
							  }?>
							  <?php
							  $match = $adj['name'].'-'.$dates[0].'-'.'adj';
							  $amountsat1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);
							  $comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowSaturday+= $amountsat1;
							  if($adj['name'] == 'Sales Tax'){
								  $saleTaxvalueSat = $amountsat1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueSat = $amountsat1;
								}
								$employeeLoanvalueSat =0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueSat = $amountsat1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueSat = $amountsat1;
								}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder="$45"></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountsat1; ?></td>

							  <?php $match = $adj['name'].'-'.$dates[1].'-'.'adj';$amountsun1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowSunday+= $amountsun1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueSun = $amountsun1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueSun = $amountsun1;
								}
								$employeeLoanvalueSun  = 0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueSun = $amountsun1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueSun = $amountsun1;
								}
							  ?>
                             <!-- <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountsun1; ?></td>-->

							  <?php $match = $adj['name'].'-'.$dates[2].'-'.'adj';$amountmon1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowMonday+= $amountmon1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueMon = $amountmon1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueMon = $amountmon1;
								}
								$employeeLoanvalueMon  = 0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueMon = $amountmon1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueMon = $amountmon1;
								}

							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountmon1; ?></td>

							  <?php $match = $adj['name'].'-'.$dates[3].'-'.'adj';$amounttue1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowTuesday+= $amounttue1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueTue = $amounttue1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueTue = $amounttue1;
								}
								$employeeLoanvalueTue  = 0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueTue = $amounttue1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueTue = $amounttue1;
								}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amounttue1; ?></td>

							  <?php $match = $adj['name'].'-'.$dates[4].'-'.'adj';$amountwed1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowWednesday+= $amountwed1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueWed = $amountwed1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueWed = $amountwed1;
								}
								$employeeLoanvalueWed  =0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueWed = $amountwed1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueWed = $amountwed1;
								}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amountwed1; ?></td>

							  <?php $match = $adj['name'].'-'.$dates[5].'-'.'adj';$amountthr1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowThrusday+= $amountthr1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueThr = $amountthr1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueThr = $amountthr1;
								}
								$employeeLoanvalueThr  = 0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueThr = $amountthr1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueThr = $amountthr1;
								}
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
							  <td style="font-size:9px;position:relative">$<?php echo $amountthr1; ?></td>

							  <?php $match = $adj['name'].'-'.$dates[6].'-'.'adj';$amountfri1 = (empty($dataListAdjust[$match]['amount'])?'0':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':$dataListAdjust[$match]['comment']);
							  $amountAdjustValueRowFriday+= $amountfri1;
							  if($adj['name'] == 'Sales Tax'){
									$saleTaxvalueFri = $amountfri1;
								}
								if($adj['name'] == 'Employee Advance'){
									$employeeAdvancevalueFri = $amountfri1;
								}
								$employeeLoanvalueFri =0;
								if($adj['name'] == 'Employee Loan'){
									$employeeLoanvalueFri = $amountfri1;
								}
								if($adj['name'] == 'Paid Out'){
									$paidOutvalueFri = $amountfri1;
								}
							  ?>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
							  <td style="font-size:9px;position:relative">$<?php echo $amountfri1; ?></td>
                           </tr>
						    <?php } ?>
                           <tr>
                              <th style="font-size:10px;font-weight:normal;">Total</th>
                              <td><input type="hidden" name="" class="" placeholder="$45"></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtmonday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowSaturday,2) ?><span></td>
                              <!--<td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamttuesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowSunday,2) ?><span></td>-->
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtwednesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowMonday,2) ?><span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtthrusday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowTuesday,2) ?><span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtfriday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowWednesday,2) ?><span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtsaturday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowThrusday,2) ?><span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="adjamtsunday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountAdjustValueRowFriday,2) ?><span></td>
                           </tr>
						   <tr class="thead-Dark">
                              <th colspan="13"  style="font-size:10px;">RECEIPTS</th>
                           </tr>
						   <?php
						   $amountRecValueRowSaturday = $amountRecValueRowSunday = $amountRecValueRowMonday = $amountRecValueRowTuesday = $amountRecValueRowWednesday = $amountRecValueRowThrusday = $amountRecValueRowFriday = 0;
						   foreach($receiptArray as $rD){
						   ?>
                           <tr>
                              <th style="font-weight:normal;font-size:10px;"><?php echo $rD['name'] ; ?></th>
							  <?php $match = $rD['name'].'-'.$dates[0].'-'.'rec';$amount1 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);
							  $comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowSaturday+= $amount1;
							  ?>
                              <td><input type="hidden" name="" class="" placeholder="$45"></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount1; ?></td>

							  <?php $match = $rD['name'].'-'.$dates[1].'-'.'rec';$amount2 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowSunday+= $amount2;
							  ?>
                              <!--<td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount2; ?></td>-->

							  <?php $match = $rD['name'].'-'.$dates[2].'-'.'rec';$amount3 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowMonday+= $amount3;
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount3; ?></td>

							  <?php $match = $rD['name'].'-'.$dates[3].'-'.'rec';$amount4 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowTuesday+= $amount4;
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount4; ?></td>

							  <?php $match = $rD['name'].'-'.$dates[4].'-'.'rec';$amount5 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowWednesday+= $amount5;
							  ?>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount5; ?></td>

							  <?php $match = $rD['name'].'-'.$dates[5].'-'.'rec';$amount6 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowThrusday+= $amount6;
							  ?>
							   <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount6; ?></td>

							  <?php $match = $rD['name'].'-'.$dates[6].'-'.'rec';$amount7 = (empty($dataListRec[$match]['amount'])?'0':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':$dataListRec[$match]['comment']);
							  $amountRecValueRowFriday+= $amount7;
							  ?>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:9px;position:relative">$<?php echo $amount7; ?></td>
                           </tr>
						   <?php
						   }
						   ?>
                           <tr>
                              <th style="font-size:10px;font-weight:normal;">Total</th>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtmonday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowSaturday,2); ?></span></td>
                              <!--<td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamttuesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowSunday,2); ?></span></td>-->
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtwednesday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowMonday,2); ?></span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtthrusday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowTuesday,2); ?></span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtfriday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowWednesday,2); ?></span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtsaturday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowThrusday,2); ?></span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly name="" id="recamtsunday" class="sale " placeholder="" style="width:65px;display:inline-block;font-size:10px;"><?php echo "$".number_format($amountRecValueRowFriday,2); ?></span></td>
                           </tr>
						   <tr class="thead-Light">
                              <th style="font-size:10px;font-weight:normal;">OVER/(SHORT)</th>
                           </tr>
							<?php
								// Tax values
								$saleTaxvalueSat = $saleTaxvalueSat ?? 0;
								$saleTaxvalueSun = $saleTaxvalueSun ?? 0;
								$saleTaxvalueMon = $saleTaxvalueMon ?? 0;
								$saleTaxvalueTue = $saleTaxvalueTue ?? 0;
								$saleTaxvalueWed = $saleTaxvalueWed ?? 0;
								$saleTaxvalueThr = $saleTaxvalueThr ?? 0;
								$saleTaxvalueFri = $saleTaxvalueFri ?? 0;

								// Employee loan values
								$employeeLoanvalueSat = $employeeLoanvalueSat ?? 0;
								$employeeLoanvalueSun = $employeeLoanvalueSun ?? 0;
								$employeeLoanvalueMon = $employeeLoanvalueMon ?? 0;
								$employeeLoanvalueTue = $employeeLoanvalueTue ?? 0;
								$employeeLoanvalueWed = $employeeLoanvalueWed ?? 0;
								$employeeLoanvalueThr = $employeeLoanvalueThr ?? 0;
								$employeeLoanvalueFri = $employeeLoanvalueFri ?? 0;

								// Employee advance values
								$employeeAdvancevalueSat = $employeeAdvancevalueSat ?? 0;
								$employeeAdvancevalueSun = $employeeAdvancevalueSun ?? 0;
								$employeeAdvancevalueMon = $employeeAdvancevalueMon ?? 0;
								$employeeAdvancevalueTue = $employeeAdvancevalueTue ?? 0;
								$employeeAdvancevalueWed = $employeeAdvancevalueWed ?? 0;
								$employeeAdvancevalueThr = $employeeAdvancevalueThr ?? 0;
								$employeeAdvancevalueFri = $employeeAdvancevalueFri ?? 0;

								// Paid out values
								$paidOutvalueSat = $paidOutvalueSat ?? 0;
								$paidOutvalueSun = $paidOutvalueSun ?? 0;
								$paidOutvalueMon = $paidOutvalueMon ?? 0;
								$paidOutvalueTue = $paidOutvalueTue ?? 0;
								$paidOutvalueWed = $paidOutvalueWed ?? 0;
								$paidOutvalueThr = $paidOutvalueThr ?? 0;
								$paidOutvalueFri = $paidOutvalueFri ?? 0;

								// Distribution values
								$amountDistValueRowSaturday = $amountDistValueRowSaturday ?? 0;
								$amountDistValueRowSunday = $amountDistValueRowSunday ?? 0;
								$amountDistValueRowMonday = $amountDistValueRowMonday ?? 0;
								$amountDistValueRowTuesday = $amountDistValueRowTuesday ?? 0;
								$amountDistValueRowWednesday = $amountDistValueRowWednesday ?? 0;
								$amountDistValueRowThrusday = $amountDistValueRowThrusday ?? 0;
								$amountDistValueRowFriday = $amountDistValueRowFriday ?? 0;

								$finalDiscountvalueSat = $finalDiscountvalueSat ?? 0;
								$finalDiscountvalueSun = $finalDiscountvalueSun ?? 0;
								$finalDiscountvalueMon = $finalDiscountvalueMon ?? 0;
								$finalDiscountvalueTue = $finalDiscountvalueTue ?? 0;
								$finalDiscountvalueWed = $finalDiscountvalueWed ?? 0;
								$finalDiscountvalueThr = $finalDiscountvalueThr ?? 0;
								$finalDiscountvalueFri = $finalDiscountvalueFri ?? 0;
							?>	
                           <tr>
                             <th style="font-weight:normal;font-size:10px;">OVER/(SHORT)</th>
                              <td><input type="hidden" name="" class="" placeholder="" ></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="oversaturday" value="" style="color:red; float:right!important; font-size:10px;">
							  <?php
							//   $oversaturday = ($amountRecValueRowSaturday - ($amountSaleValueRowSaturday + $saleTaxvalueSat)) + ($employeeAdvancevalueSat
							// 						+ $employeeLoanvalueSat + $paidOutvalueSat + $refundvalueSat + $couponvalueSat + $finalDiscountvalueSat);

													$oversaturday = $amountRecValueRowSaturday - ($amountSaleValueRowSaturday + $saleTaxvalueSat) + ($amountDistValueRowSaturday + $employeeLoanvalueSat + $employeeAdvancevalueSat + $paidOutvalueSat);


									echo "$".abs(number_format($oversaturday,2));
							  ?>

							  </span></td>
                             <!-- <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="oversunday" value="" style="font-size:10px;">
							  <?php
							//   $oversunday = ($amountRecValueRowSunday - ($amountSaleValueRowSunday + $saleTaxvalueSun)) + ($employeeAdvancevalueSun
							// 						+ $employeeLoanvalueSun + $paidOutvalueSun + $refundvalueSun + $couponvalueSun + $finalDiscountvalueSun);

							$oversunday = $amountRecValueRowSunday - ($amountSaleValueRowSunday + $saleTaxvalueSun) + ($amountDistValueRowSunday + $employeeLoanvalueSun + $employeeAdvancevalueSun + $paidOutvalueSun);
									// echo "$".number_format(round($oversunday),2);
									echo "$".abs(number_format($oversunday,2));
							  ?>
							  </span></td>-->
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="overmonday" value="" style="font-size:10px;">
							  <?php
							//   $overmonday = ($amountRecValueRowMonday - ($amountSaleValueRowMonday + $saleTaxvalueMon)) + ($employeeAdvancevalueMon
							// 						+ $employeeLoanvalueMon + $paidOutvalueMon + $refundvalueMon + $couponvalueMon + $finalDiscountvalueMon);
							$overmonday = $amountRecValueRowMonday - ($amountSaleValueRowMonday + $saleTaxvalueMon) + ($amountDistValueRowMonday + $employeeAdvancevalueMon + $employeeLoanvalueMon + $paidOutvalueMon);
									echo "$".abs(number_format($overmonday,2));
							  ?>
							  </span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="overtuesday" value="" style="font-size:10px;">
							  <?php
							//   $overtuesday = ($amountRecValueRowTuesday - ($amountSaleValueRowTuesday + $saleTaxvalueTue)) + ($employeeAdvancevalueTue
							// 						+ $employeeLoanvalueTue + $paidOutvalueTue + $refundvalueTue + $couponvalueTue + $finalDiscountvalueTue);
							$overtuesday = $amountRecValueRowTuesday - ($amountSaleValueRowTuesday + $saleTaxvalueTue) + ($amountDistValueRowTuesday + $employeeAdvancevalueTue
													+ $employeeLoanvalueTue + $paidOutvalueTue);
									echo "$".abs(number_format($overtuesday,2));
							  ?>
							  </span></td>
                              <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="overwednesday" value="" style="font-size:10px;">
							  <?php
							//   $overwednesday = ($amountRecValueRowWednesday - ($amountSaleValueRowWednesday + $saleTaxvalueWed)) + ($employeeAdvancevalueWed + $employeeLoanvalueWed + $paidOutvalueWed + $refundvalueWed + $couponvalueWed + $finalDiscountvalueWed);
							$overwednesday = $amountRecValueRowWednesday - ($amountSaleValueRowWednesday + $saleTaxvalueWed) + ($amountDistValueRowWednesday + $employeeLoanvalueWed + $paidOutvalueWed + $finalDiscountvalueWed);
									echo "$".abs(number_format($overwednesday,2));
							  ?>
							  </span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="overthrusday" value="" style="font-size:10px;">
							  <?php
							//   $overthrusday = ($amountRecValueRowThrusday - ($amountSaleValueRowThrusday + $saleTaxvalueThr)) + ($employeeAdvancevalueThr
							// 						+ $employeeLoanvalueThr + $paidOutvalueThr + $refundvalueThr + $couponvalueThr + $finalDiscountvalueThr);
							$overthrusday = $amountRecValueRowThrusday - ($amountSaleValueRowThrusday + $saleTaxvalueThr) + ($amountDistValueRowThrusday
													+ $employeeLoanvalueThr + $paidOutvalueThr + $finalDiscountvalueThr);
									echo "$".abs(number_format($overthrusday,2));
							  ?>
							  </span></td>
							  <td><input type="hidden" name="" class="" placeholder=""></td>
                              <td style="font-size:10px;font-weight:normal;"><span readonly class="" name="saleD" id="overfriday" value="" style="font-size:10px;">
							  <?php
							//   $overfriday = ($amountRecValueRowFriday - ($amountSaleValueRowFriday + $saleTaxvalueFri)) + ($employeeAdvancevalueFri
							// 						+ $employeeLoanvalueFri + $paidOutvalueFri + $refundvalueFri + $couponvalueFri + $finalDiscountvalueFri);
							$overfriday = $amountRecValueRowFriday - ($amountSaleValueRowFriday + $saleTaxvalueFri) + ($amountDistValueRowFriday
													+ $employeeLoanvalueFri + $paidOutvalueFri + $finalDiscountvalueFri);


									echo "$".abs(number_format($overfriday,2));
							  ?>

							  </span></td>

                           </tr>
						   <span style="display:none;" id="finaloverTotalNewValue"><?php echo "$".number_format(round($oversaturday + $oversunday + $overmonday + $overtuesday + $overwednesday + $overthrusday + $overfriday),2);?></span>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
				<!--<div class="row">
			      <div class="col-md-12 text-center">
				    <button class="v-btn v-btn-primary" style="margin-bottom:20px;">ADD</button>
				  </div>

				</div>-->


	</div>
</section>
</div>
<div class="modal fade" id="modalData" role="dialog" >
    <div class="modal-dialog " >

      <!-- Modal content-->

      <div class="modal-content">
        <div class="modal-header" >
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" >Submit Your Comment</h4>
        </div>
        <div class="modal-body">
		<input type="hidden" name="popupName" id="popupName">
		<input type="hidden" name="popupUnit" id="popupUnit">
		<input type="hidden" name="popupDay" id="popupDay">
		<input type="hidden" name="popupNameId" id="popupNameId">
		<input type="hidden" name="popupdayname" id="popupdayname">
		<input type="hidden" name="popupweekNo" id="popupweekNo">
		<input type="hidden" name="popupStorNo" id="popupStorNo">
          <textarea name="comment" form="usrform" id="inputMessage" style="width:100%;height:188px;" placeholder="Enter text here..." ></textarea>
        </div>
        <div class="modal-footer" >
          <button type="button" class="btn btn-primary submitBtn"  onclick="submitContactForm()">SUBMIT</button>
		  </div>
      </div>

    </div>
</div>



<div class="modal fade" id="modalDepData" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
	<form method="post" id="depData">
		<div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">&times;</button>
			  <h4 class="modal-title">Add New Field</h4>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
				<input type="hidden" name="depName" id="depNameStore">
				<input type="hidden" name="weekNoData" id="depWeekStore">
				<input type="hidden" name="storeNoData" id="depCalStore">
					<div class="row form-group">
						<label class="col-md-4">Field Name: </label>
						<div class="col-md-8">
							<input type="text" name="fieldValue" id="fieldValue" class="">
						</div>
					</div>
					<div class="row form-group">
						<label class="col-md-4">Scope of Field </label>
						<div class="col-md-8">
							<div class="radio">
								<label><input type="radio" name="optradio" value="current">Current Week</label>
								<label><input type="radio" name="optradio" value="permanent">Permanent Week</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
			  <button type="button" class="btn btn-primary submitBtn"  id="submitDep">SUBMIT</button>

			 </div>
		</div>
	</form>
    </div>
</div>

<style type="text/css" media="print">
@page {
 size: portrait;
}
body {
 writing-mode: tb-rl;
}
</style>
<style type="text/css">
   .quick-info>li{vertical-align: middle;}
   .quick-info>li>span{font-size: 20px; margin-right: 20px; font-weight:800}
   .custom-table .{height: 20px; font-weight: normal; border-color: #eee; text-align: center; margin-bottom:0;}
   .custom-table{margin-bottom:0;}
   .custom-table>thead{    background-color: #382f2f;
    color: #ffffffc7;}
   .custom-table>thead>tr{border-bottom: 0;}

   .custom-table>tbody>tr>th,
   .custom-table>tbody>tr>td,
   .custom-table>thead>tr>th{    padding: 4px 10px!important; font-size: 12px; font-weight: bold; border-bottom:0; border-top: 0; }
   .custom-table>tbody>tr:nth-child(odd){}


    .custom-table>thead>tr>th{border-left:1px solid #ababab}

   .thead-Dark{background-color: #909090; color: #fff;}
   .thead-Light{background-color: #382f2f; color: #fff; }

   .filter-container
   {
   background: #382f2f;
   padding: 6px;
   color: #fff;
   }
   .day-filter {
   background: #000;
   padding: 3px;
   margin-left: 5px;
   }

    .summary-bx{margin-bottom:10px; border: 1px solid #ddd;
    padding: 10px;
    background-color: #d8d8d8;}

	.text-red{color:red}

	 .nowrap {
        white-space:nowrap;
    }

	.[disabled], .[readonly], fieldset[disabled] . {
     background-color: #fff !important;
    opacity: 1;
}
.comment{
	       position: absolute;
    top: 8px;
    right: 0;
}

</style>
<script type="text/javascript">
	$.ajaxSetup({
        headers: {
	            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	        }
	});
</script>
<script type="text/javascript">
	$(window).on("load", function (e) {
		var finalTotal = $("#finaloverTotalNewValue").text();
		var finTotVal =  $("#finalovertotalVal").text(finalTotal);
		$("#test_default").trigger("onclick");



		var stDate = "<?php echo $sDate; ?>";
		var calDate1 = "<?php echo date('m-d-Y',strtotime($sDate)); ?>";
		var calDate2 = "<?php echo date('m-d-Y',strtotime($eDate)); ?>";
		var calDate = calDate1 + ' - ' + calDate2;
		$("#weeklyDatePicker").val(calDate);
		$.ajax({
			type:       "POST",
			dataType:   "text",
			url:        "{{ url('/') }}/weekData",
			data: {stDate: stDate},
			success: function(data)
			{
				//alert("test");
				var value = data.split("|");
				$('#weekNoData').text(value[0]);
				$('#storeNoData').text(value[1]);
			}
		});

		/* var sumSale = 0;
		$(".saleSum").each(function(){
		sumSale += +$(this).val(); });
		var SaleT = numberWithCommas(sumSale.toFixed(2));
		$("#saletotal").text("$"+SaleT);

		var sumdis = 0;
		$(".disSum").each(function(){
		sumdis += +$(this).val(); });
		var DisT = numberWithCommas(sumdis.toFixed(2));
		$("#distotal").text("$"+DisT);

		var sumadj = 0;
		$(".adjSum").each(function(){
		sumadj += +$(this).val(); });
		var AdjT = numberWithCommas(sumadj.toFixed(2));
		$("#adjtotal").text("$"+AdjT);

		var sumrec = 0;
		$(".recSum").each(function(){
		sumrec += +$(this).val(); });
		var RecT = numberWithCommas(sumrec.toFixed(2));
		$("#rectotal").text("$"+RecT); */

		// $('.sumunit').trigger('keyup');
		// $('.sumamt').trigger('keyup');
		// $('.disamt').trigger('keyup');
		// $('.adjamt').trigger('keyup');
		// $('.recamt').trigger('keyup');
		sumunitmon();
		sumamount();
		disAmount();
		adjustAmount();
		recAmount();
		calculateTotalSum();

	});

   //Initialize the datePicker(I have taken format as mm-dd-yyyy, you can     //have your owh)
   $("#weeklyDatePicker").datetimepicker({
      format: 'MM-DD-YYYY',
	  maxDate: 'now',
	  minDate: $("#startDate").val()
   });
	$(document).ready(function(){
	moment.locale('en', {
	week: { dow: -1 }

	});


   //Get the value of Start and End of Week
   $('#weeklyDatePicker').on('dp.change', function (e) {
		var value = $("#weeklyDatePicker").val();
		var fullDate = new Date();
		var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
		var currentDate = twoDigitMonth + "-" + fullDate.getDate() + "-" + fullDate.getFullYear();
		if(value == ''){
			var value = currentDate;
		}
		var firstDate = moment(value, "MM-DD-YYYY").day(-1).format("MM-DD-YYYY");
		var lastDate =  moment(value, "MM-DD-YYYY").day(5).format("MM-DD-YYYY");
		$("#weeklyDatePicker").val(firstDate + " - " + lastDate);
			/* if((firstDate != '') && (lastDate != '') ) {
				$('#weekDatapick').trigger('click');
			} */

   });
		$('.sale').keyup(function() {
			if($(this).val()!="") {
				var name = ($(this).attr('name'));
				var unit = ($(this).attr('unit'));
				var dayno = ($(this).attr('dayno'));
				var val = ($(this).val());
				var dayname = $('#'+dayno).text();
				var nameID = ($(this).attr('nameId'));
				var weekNo = $('#weekNoData').text();
				var storeNo = $('#storeNoData').text();
				$.ajax({
					type:       "POST",
					dataType:   "text",
					url:        "{{ url('/') }}/dataSaveSales",
					data: {name: name,unit:unit,dayno:dayno,val:val,dayname:dayname,nameID:nameID,weekNo:weekNo,storeNo:storeNo},
					success: function(data)	{
					}
				});
			}
		});

		$('.sumunit').keyup(function() {
			var sum = 0;
			if($(this).hasClass("sumunitmon")== true){
				$(".sumunitmon").each(function() {
					sum += +this.value;
				});
				$('#sumunitmonday').val(sum);
			}
			if($(this).hasClass("sumunittue")== true){
				$(".sumunittue").each(function() {
					sum += +this.value;
				});
			$('#sumunittuesday').val(sum);
			}
			if($(this).hasClass("sumunitwed")== true){
				$(".sumunitwed").each(function() {
					sum += +this.value;
				});
			$('#sumunitwednesday').val(sum);
			}
			if($(this).hasClass("sumunitthr")== true){
				$(".sumunitthr").each(function() {
					sum += +this.value;
				});
			$('#sumunitthrusday').val(sum);
			}
			if($(this).hasClass("sumunitfri")== true){
				$(".sumunitfri").each(function() {
					sum += +this.value;
				});
			$('#sumunitfriday').val(sum);
			}
			if($(this).hasClass("sumunitsat")== true){
				$(".sumunitsat").each(function() {
					sum += +this.value;
				});
			$('#sumunitsaturday').val(sum);
			}
			if($(this).hasClass("sumunitsun")== true){
				$(".sumunitsun").each(function() {
					sum += +this.value;
				});
			$('#sumunitsunday').val(sum);
			}
			calculateTotalSum();
		});

		$('.sumamt').keyup(function() {
			var sum = 0;
			if($(this).hasClass("sumamtmon")== true){
				$(".sumamtmon").each(function() {
					sum += +this.value;
				});
			$('#sumamtmonday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamttue")== true){
				$(".sumamttue").each(function() {
					sum += +this.value;
				});
			$('#sumamttuesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamtwed")== true){
				$(".sumamtwed").each(function() {
					sum += +this.value;
				});
			$('#sumamtwednesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamtthr")== true){
				$(".sumamtthr").each(function() {
					sum += +this.value;
				});
			$('#sumamtthrusday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamtfri")== true){
				$(".sumamtfri").each(function() {
					sum += +this.value;
				});
			$('#sumamtfriday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamtsat")== true){
				$(".sumamtsat").each(function() {
					sum += +this.value;
				});
			$('#sumamtsaturday').val(sum.toFixed(2));
			}
			if($(this).hasClass("sumamtsun")== true){
				$(".sumamtsun").each(function() {
					sum += +this.value;
				});
			$('#sumamtsunday').val(sum.toFixed(2));
			}
			calculateTotalSum();
		});



		$('.disamt').keyup(function() {
			var sum = 0;
			if($(this).hasClass("disamtmon")== true){
				$(".disamtmon").each(function() {
					sum += +this.value;
				});
			$('#disamtmonday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamttue")== true){
				$(".disamttue").each(function() {
					sum += +this.value;
				});
			$('#disamttuesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamtwed")== true){
				$(".disamtwed").each(function() {
					sum += +this.value;
				});
			$('#disamtwednesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamtthr")== true){
				$(".disamtthr").each(function() {
					sum += +this.value;
				});
			$('#disamtthrusday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamtfri")== true){
				$(".disamtfri").each(function() {
					sum += +this.value;
				});
			$('#disamtfriday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamtsat")== true){
				$(".disamtsat").each(function() {
					sum += +this.value;
				});
			$('#disamtsaturday').val(sum.toFixed(2));
			}
			if($(this).hasClass("disamtsun")== true){
				$(".disamtsun").each(function() {
					sum += +this.value;
				});
			$('#disamtsunday').val(sum.toFixed(2));
			}
			calculateTotalSum();
		});

		$('.adjamt').keyup(function() {
			var sum = 0;
			if($(this).hasClass("adjamtmon")== true){
				$(".adjamtmon").each(function() {
					sum += +this.value;
				});
			$('#adjamtmonday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamttue")== true){
				$(".adjamttue").each(function() {
					sum += +this.value;
				});
			$('#adjamttuesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamtwed")== true){
				$(".adjamtwed").each(function() {
					sum += +this.value;
				});
			$('#adjamtwednesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamtthr")== true){
				$(".adjamtthr").each(function() {
					sum += +this.value;
				});
			$('#adjamtthrusday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamtfri")== true){
				$(".adjamtfri").each(function() {
					sum += +this.value;
				});
			$('#adjamtfriday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamtsat")== true){
				$(".adjamtsat").each(function() {
					sum += +this.value;
				});
			$('#adjamtsaturday').val(sum.toFixed(2));
			}
			if($(this).hasClass("adjamtsun")== true){
				$(".adjamtsun").each(function() {
					sum += +this.value;
				});
			$('#adjamtsunday').val(sum.toFixed(2));
			}
			calculateTotalSum();
		});

		$('.recamt').keyup(function() {
			var sum = 0;
			if($(this).hasClass("recamtmon")== true){
				$(".recamtmon").each(function() {
					sum += +this.value;
				});

			$('#recamtmonday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamttue")== true){
				$(".recamttue").each(function() {
					sum += +this.value;
				});
			$('#recamttuesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamtwed")== true){
				$(".recamtwed").each(function() {
					sum += +this.value;
				});
			$('#recamtwednesday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamtthr")== true){
				$(".recamtthr").each(function() {
					sum += +this.value;
				});
			$('#recamtthrusday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamtfri")== true){
				$(".recamtfri").each(function() {
					sum += +this.value;
				});
			$('#recamtfriday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamtsat")== true){
				$(".recamtsat").each(function() {
					sum += +this.value;
				});
			$('#recamtsaturday').val(sum.toFixed(2));
			}
			if($(this).hasClass("recamtsun")== true){
				$(".recamtsun").each(function() {
					sum += +this.value;
				});
			$('#recamtsunday').val(sum.toFixed(2));
			}
			calculateTotalSum();
		});
	});
	/* tab down function */
	$(function() {
		$('tr').each(function() {
			$(this).find('td').each(function(i) {
				$(this).find('input').attr('tabindex', i+1);
			});
		});
		   /* enter as tab */
			 $('.enterTab').keydown(function (e) {
				 if (e.which === 13) {
					 var index = $('.enterTab').index(this) + 1;
					 $('.enterTab').eq(index).focus();
				 }
			});
			/* enter as tab ends */

	});
	/* ends */

	/* number format function */
	function numberWithCommas(number) {
		var parts = number.toString().split(".");
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		return parts.join(".");
	}
	/* ends */
	/* only numbers */
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
    /* var caratPos = getSelectionStart(el);
    var dotPos = el.value.indexOf(".");
    if( caratPos > dotPos && dotPos>-1 && (number[1].length > 1)){
        return false;
    } */
    return true;
	}
	/* ends */



</script>


<script>
/* comment section */
function dataGet(nameId,unit,day,name,message){
				$('#modalData').modal('show');
				var dayname = $('#'+day).text();
				$('#popupName').val(name);
				$('#popupUnit').val(unit);
				$('#popupDay').val(day);
				$('#popupNameId').val(nameId);
				$('#popupdayname').val(dayname);
				$('textarea#inputMessage').val(message);
				$('#popupweekNo').val($('#weekNoData').text());
				$('#popupStorNo').val($('#storeNoData').text());
}

function submitContactForm(){
				//$('#modalData').modal('hide');
				var message = $('#inputMessage').val();
				var name = $('#popupName').val();
				var unit = $('#popupUnit').val();
				var day = $('#popupDay').val();
				var nameId = $('#popupNameId').val();
				var popdayname = $('#popupdayname').val();
				var popupweekNo = $('#popupweekNo').val();
				var popupStorNo = $('#popupStorNo').val();
						$.ajax({
							type:       "POST",
							dataType:   "text",
							url:        "{{ url('/') }}/commentSave",
							data: {message: message,name:name,unit:unit,day:day,nameId:nameId,popdayname:popdayname,popupweekNo:popupweekNo,popupStorNo:popupStorNo},
							success: function(data)
							{
									var value = data;
									if(value == 'complete'){
										setTimeout(function(){
											location.reload();
											});
									}
							}
						});
}
/* ends */
		/* currency format */
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

		$('.currency').each(function() {
				if(!isNaN( parseFloat(this.value) ) ) {
					this.value =  parseFloat(this.value).toFixed(2);
				} else {
					this.value =  '';
				}
				return this;

		});
	/* end */

	function addNewCol(dep){
		$('#modalDepData').modal('show');
		$('#depNameStore').val(dep);
		$('#depWeekStore').val($('#weekNoData').text());
		$('#depCalStore').val($('#storeNoData').text());
	}
	$("#submitDep").click(function()
	{
		var inputvalue = $("#fieldValue").val();
		if(inputvalue == ''){
               alert("Please insert value for field.")
        }
		if($('input:radio[name=optradio]').is(':checked')){
		var radioValue = $("input[name='optradio']:checked").val();
		}else{
		alert('Please select scope of field');
		}
		var depName = $('#depNameStore').val();
		var weekNo = $('#depWeekStore').val();
		var calWeek = $('#depCalStore').val();
						$.ajax({
							type:       "POST",
							dataType:   "text",
							url:        "{{ url('/') }}/newFieldSave",
							data: {inputvalue: inputvalue,radioValue:radioValue,depName:depName,weekNo:weekNo,calWeek:calWeek},
							success: function(data)
							{
									var value = data;
									if(value == 'complete'){
										setTimeout(function(){
											location.reload();
											});
									}
							}
						});


	});


	/* refresh summary dynamically starts */
	function unitSum(className){
		var sumSaleValue = 0;
		$("."+className).each(function(){
		sumSaleValue += +$(this).val();
		});
		$("."+className).html(sumSaleValue);
	}
	function amountSum(className){
		var sumSaleValue = 0;
		$("."+className).each(function(){
		sumSaleValue += +$(this).val();
		});
		$("."+className).html('$'+sumSaleValue.toFixed(2));
		$("."+className+1).val(sumSaleValue);


		var sumSale = 0;
		$(".saleSum").each(function(){
		sumSale += +$(this).val(); });
		var SaleT = numberWithCommas(sumSale.toFixed(2));
		$("#saletotal").text("$"+SaleT);

	}
	function disAmountSum(className){
		var sumDisValue = 0;
		$("."+className).each(function(){
		sumDisValue += +$(this).val();
		});

		$("."+className).html('$'+sumDisValue.toFixed(2));
		$("."+className+3).val(sumDisValue);

		 var sumdis = 0;
		$(".disSum").each(function(){
		sumdis += +$(this).val(); });
		var DisT = numberWithCommas(sumdis.toFixed(2));
		$("#distotal").text("$"+DisT);

	}
	function adjAmountSum(className){
		var sumAdjValue = 0;
		$("."+className).each(function(){
		sumAdjValue += +$(this).val();
		});

		$("."+className).html('$'+sumAdjValue.toFixed(2));
		$("."+className+4).val(sumAdjValue);

		var sumadj = 0;
		$(".adjSum").each(function(){
		sumadj += +$(this).val(); });
		var AdjT = numberWithCommas(sumadj.toFixed(2));
		$("#adjtotal").text("$"+AdjT);

	}
	function recAmountSum(className){
		var sumRecptValue = 0;
		$("."+className).each(function(){
		sumRecptValue += +$(this).val();
		});

		$("."+className).html('$'+sumRecptValue.toFixed(2));
		$("."+className+5).val(sumRecptValue);

		var sumrec = 0;
		$(".recSum").each(function(){
		sumrec += +$(this).val(); });
		var RecT = numberWithCommas(sumrec.toFixed(2));
		$("#rectotal").text("$"+RecT);

	}
	/* ends */
	// Calculate over value
	function calculateTotalSum() {
		/* sat over value start*/
		var sat = tax = 0;
		$(".saturdaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});
		var recmt =  $('#recamtmonday').val() == '' ? '0' : $('#recamtmonday').val();
		var salemt = $('#sumamtmonday').val() == '' ? '0' : $('#sumamtmonday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;

		var oversaturday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#oversaturday').val(oversaturday.toFixed(2));

		/* sat over value end*/
		/* sun over value start*/
		var sat = tax = 0;
		$(".sundaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamttuesday').val() == '' ? '0' : $('#recamttuesday').val();
		var salemt = $('#sumamttuesday').val() == '' ? '0' : $('#sumamttuesday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;

		var oversunday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#oversunday').val(oversunday.toFixed(2));
		/* sun over value end*/
		/* mon over value start*/
		var sat = tax = 0;
		$(".mondaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamtwednesday').val() == '' ? '0' : $('#recamtwednesday').val();
		var salemt = $('#sumamtwednesday').val() == '' ? '0' : $('#sumamtwednesday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;
		var overmonday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#overmonday').val(overmonday.toFixed(2));
		/* mon over value end*/
		/* tue over value start*/
		var sat = tax = 0;
		$(".tuesdaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamtthrusday').val() == '' ? '0' : $('#recamtthrusday').val();
		var salemt = $('#sumamtthrusday').val() == '' ? '0' : $('#sumamtthrusday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;
		var overtuesday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#overtuesday').val(overtuesday.toFixed(2));
		/* tue over value end*/
		/* wed over value start*/
		var sat = tax = 0;
		$(".wednesdaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamtfriday').val() == '' ? '0' : $('#recamtfriday').val();
		var salemt = $('#sumamtfriday').val() == '' ? '0' : $('#sumamtfriday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;

		var overwednesday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#overwednesday').val(overwednesday.toFixed(2));
		/* wed over value end*/
		/* thr over value start*/
		var sat = tax = 0;
		$(".thrusdaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamtsaturday').val() == '' ? '0' : $('#recamtsaturday').val();
		var salemt = $('#sumamtsaturday').val() == '' ? '0' : $('#sumamtsaturday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;

		var overthrusday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#overthrusday').val(overthrusday.toFixed(2));
		/* thr over value end*/
		/* fri over value start*/
		var sat = tax = 0;
		$(".fridaydate").each(function(){
			if($(this).attr('name') == 'Sales Tax'){
			  var saletax = $(this).val();
			  tax += +$(this).val();
			}
			if($(this).attr('name') == 'Refund'){
				var refund = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Coupons'){
				var coupon = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Final Discounts'){
				var fdiscount = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Advancee'){
				var empadv = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Employee Loan'){
				var emploan = $(this).val();
				sat += +$(this).val();
			}
			if($(this).attr('name') == 'Paid Out'){
				var paidout = $(this).val();
				sat += +$(this).val();
			}
		});

		var recmt =  $('#recamtsunday').val() == '' ? '0' : $('#recamtsunday').val();
		var salemt = $('#sumamtsunday').val() == '' ? '0' : $('#sumamtsunday').val();
		var stax = isNaN(tax) ? '0' : tax;
		var otherpart = isNaN(sat) ? '0' : sat;
		var overfriday = (parseInt(recmt) - (parseInt(salemt) +  parseInt(stax))) + parseInt(otherpart);
		$('#overfriday').val(overfriday.toFixed(2));
		/* fri over value end*/

		var finalover = parseInt(oversaturday) + parseInt(oversunday) + parseInt(overmonday) + parseInt(overtuesday) + parseInt(overwednesday) + parseInt(overthrusday) + parseInt(overfriday);
		var FinalT = numberWithCommas(finalover.toFixed(2));
		//$("#finalovertotal").text("$"+FinalT);

	}

	function sumunitmon() {
			var sum = 0;
			$(".sumunitmon").each(function() {
				sum += +this.value;
			});
			$('#sumunitmonday').val(sum);

			 sum = 0;
			$(".sumunittue").each(function() {
				sum += +this.value;
			});
			$('#sumunittuesday').val(sum);

			 sum = 0;
				$(".sumunitwed").each(function() {
					sum += +this.value;
				});
			$('#sumunitwednesday').val(sum);

			sum = 0;
				$(".sumunitthr").each(function() {
					sum += +this.value;
				});
			$('#sumunitthrusday').val(sum);

			 sum = 0;
				$(".sumunitfri").each(function() {
					sum += +this.value;
				});
			$('#sumunitfriday').val(sum);

			sum = 0;
				$(".sumunitsat").each(function() {
					sum += +this.value;
				});
			$('#sumunitsaturday').val(sum);

			sum = 0;
				$(".sumunitsun").each(function() {
					sum += +this.value;
				});
			$('#sumunitsunday').val(sum);
	}

	function sumamount() {
			var sum = 0;
			$(".sumamtmon").each(function() {
					sum += +this.value;
				});
			$('#sumamtmonday').val(sum.toFixed(2));

			sum = 0;
				$(".sumamttue").each(function() {
					sum += +this.value;
				});
			$('#sumamttuesday').val(sum.toFixed(2));

			sum = 0;
				$(".sumamtwed").each(function() {
					sum += +this.value;
				});
			$('#sumamtwednesday').val(sum.toFixed(2));

			sum = 0;
				$(".sumamtthr").each(function() {
					sum += +this.value;
				});
			$('#sumamtthrusday').val(sum.toFixed(2));

			sum = 0;
				$(".sumamtfri").each(function() {
					sum += +this.value;
				});
			$('#sumamtfriday').val(sum.toFixed(2));

			sum = 0;
				$(".sumamtsat").each(function() {
					sum += +this.value;
				});
			$('#sumamtsaturday').val(sum.toFixed(2));

			sum = 0;
			$(".sumamtsun").each(function() {
				sum += +this.value;
			});
			$('#sumamtsunday').val(sum.toFixed(2));
	}

	function disAmount() {
			var sum = 0;
			$(".disamtmon").each(function() {
					sum += +this.value;
				});
			$('#disamtmonday').val(sum.toFixed(2));

				sum = 0;
				$(".disamttue").each(function() {
					sum += +this.value;
				});
				$('#disamttuesday').val(sum.toFixed(2));

			 sum = 0;
				$(".disamtwed").each(function() {
					sum += +this.value;
				});
			$('#disamtwednesday').val(sum.toFixed(2));

			 sum = 0;
				$(".disamtthr").each(function() {
					sum += +this.value;
				});
			$('#disamtthrusday').val(sum.toFixed(2));

			 sum = 0;
				$(".disamtfri").each(function() {
					sum += +this.value;
				});
			$('#disamtfriday').val(sum.toFixed(2));

			 sum = 0;
				$(".disamtsat").each(function() {
					sum += +this.value;
				});
			$('#disamtsaturday').val(sum.toFixed(2));

			 sum = 0;
				$(".disamtsun").each(function() {
					sum += +this.value;
				});
			$('#disamtsunday').val(sum.toFixed(2));

	}

	function adjustAmount() {
			var sum = 0;
			$(".adjamtmon").each(function() {
					sum += +this.value;
			});
			$('#adjamtmonday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamttue").each(function() {
				sum += +this.value;
			});
			$('#adjamttuesday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamtwed").each(function() {
				sum += +this.value;
			});
			$('#adjamtwednesday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamtthr").each(function() {
				sum += +this.value;
			});
			$('#adjamtthrusday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamtfri").each(function() {
				sum += +this.value;
			});
			$('#adjamtfriday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamtsat").each(function() {
				sum += +this.value;
			});
			$('#adjamtsaturday').val(sum.toFixed(2));

			sum = 0;
			$(".adjamtsun").each(function() {
				sum += +this.value;
			});
			$('#adjamtsunday').val(sum.toFixed(2));
	}

	function recAmount() {
			var sum = 0;

			$(".recamtmon").each(function() {
				sum += +this.value;
			});
			$('#recamtmonday').val(sum.toFixed(2));

			sum = 0;
				$(".recamttue").each(function() {
					sum += +this.value;
				});
			$('#recamttuesday').val(sum.toFixed(2));

			sum = 0;
				$(".recamtwed").each(function() {
					sum += +this.value;
				});
			$('#recamtwednesday').val(sum.toFixed(2));

			sum = 0;
				$(".recamtthr").each(function() {
					sum += +this.value;
				});
			$('#recamtthrusday').val(sum.toFixed(2));

			sum = 0;
				$(".recamtfri").each(function() {
					sum += +this.value;
				});
			$('#recamtfriday').val(sum.toFixed(2));

			sum = 0;
				$(".recamtsat").each(function() {
					sum += +this.value;
				});
			$('#recamtsaturday').val(sum.toFixed(2));

			sum = 0;
				$(".recamtsun").each(function() {
					sum += +this.value;
				});
			$('#recamtsunday').val(sum.toFixed(2));
	}
	</script>
<style type="text/css" media="print">
header nav, footer {
display: none;
}

</style>
<script language="Javascript">
function printDiv(divID) {
//Get the HTML of div
var divElements = document.getElementById(divID).innerHTML;
var contents = divElements;
var frame1 = $('<iframe />');
frame1[0].name = "frame1";
frame1.css({"position": "absolute", "top": "-1000000px",  });
$("body").append(frame1);
var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
frameDoc.document.open();
frameDoc.document.write('<html><head><style type="text/css" media="print"> @page {  margin: 0mm;} body {  margin: 10mm;  }  </style><title></title>');
frameDoc.document.write('</head><body>');
frameDoc.document.write(contents);
frameDoc.document.write('</body></html>');
frameDoc.document.close();
setTimeout(function () {
window.frames["frame1"].focus();
window.frames["frame1"].print();
frame1.remove();
window.history.go(-1);
}, 500);

}
</script>
<input name="print" TYPE="button" id="test_default" class="button" VALUE="Print Order" onclick="javascript:printDiv('printablediv')">



@endsection
