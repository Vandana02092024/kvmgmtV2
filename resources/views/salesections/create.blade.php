@extends('layouts.layout')
@section('content')
<style>
   #ajaxLoadAni {
      color: #fff;
      font-weight: bold;
      position: fixed;
      top: 0;
      left: 0%;
      padding: 8px;
      width: 100%;
      height: 100%;
      z-index: 9999;
      padding: 130px 0px;
      text-align: center;
   }
   #ajaxLoadAni span {
      float: right;
      margin: 2px 0 0 0;
   }
   table.fixed-tbody>tbody {
      display:block;
      height:86px;
      overflow:auto;
   }
   table.fixed-tbody>thead, table.fixed-tbody>tfoot {
      display:block;
   }
   .custom-table>tbody>tr>th, .custom-table>tbody>tr>td, .custom-table>thead>tr>th,.custom-table>tfoot>tr>th {
   padding: 1px 2px!important;
}
.page-heading h3.heading span {
    /* margin-top: 5px; */
    margin-left: 93px;
}
</style>
<?php
   function getDatesFromRange($start_date, $end_date, $format = 'Y-m-d') {

       $dates = [];
       $current_date = strtotime($start_date);
       $end_timestamp = strtotime($end_date);

       while ($current_date <= $end_timestamp) {
          // if (date('N', $current_date) != 7) { // Check if it's not Sunday (7)
               $dates[] = date('Y-m-d', $current_date);
          // }
           $current_date = strtotime('+1 day', $current_date);
       }


       return $dates;
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
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<style>
   i.fa.fa-comment{cursor:pointer;}
</style>
<section id="dashboard-page" class="wrap kv-weekly-ledger">
<div class="container-fluid">
@if(Session::has('message'))
<div class="alert alert-success">
   {{ Session::get('message') }}
</div>
@endif
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
<section id="dashboard-page" class="wrap kv-weekly-ledger">
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-heading inline" style="margin-bottom:0;margin-top: 0">
               <h3 class="heading text-left" style="font-size: 18px;"> {{ $entity }} <!-- {{ session()->get('entityName') }} -->  Weekly Sales Report (  <span style="margin-left: 0;"> {{ session()->get('locationCode') }} - {{ session()->get('locationName') }}</span>)
               </h3>

            </div>
            <form class="pull-right inline"  method="POST" action="{{ url('/salesection   ') }}">
               {{ csrf_field() }}
               <ul class="pull-right inline quick-info">
                  <li>Calendar week</li>
                  <li><span id="weekNoData"></span></li>
                  <li>Store week</li>
                  <li>
                     <span id="storeNoData"></span>
                  </li>
                  <li>
                     <div class="input-group" id="DateDemo">
                        <input type="text" class="form-control" id='weeklyDatePicker' name="datepick" placeholder="Select Week" />
                     </div>
                  </li>
                  <input class="v-btn v-btn-primary" type="submit" name="submit" id="weekDatapick" value="FILTER">
            </form>
            <input type="button" name="" value="Labor Report" onclick="gotoLaborRep()" class="v-btn v-btn-primary pull-right bg-light">
            <a class="v-btn v-btn-primary" href="{{ url('/salesection') }}"><i class="fa fa-arrow-left " style="margin-right: 5px"> </i>BACK</a>

            </ul>
         </div>
      </div>
      <input type="hidden" id="startDate" value="{{ $startDatestore }}">
      <input type="hidden" id="endDate" value="{{ $dateInTwoWeeks }}">
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
      <div class="row">
         <div class="col-sm-12">
            <div class="widget form-layout  summary-bx">
               <div class="widget-content padding" style="padding:0;">
                  <div class="row kv-summary-grid">
                     <div class= "col-md-2">
                        <table class="table  custom-table fixed-tbody">
                           <thead>
                              <tr>
                                 <th width="200" style="text-align:center">SALES</th>
                                 <th width="100" style="text-align:center">UNIT</th>
                                 <th width="30%" style="text-align:center">AMOUNT</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php
                              $grasstot=0;
                               foreach($saleSum as $key=>$val){ ?>
                              <tr>
                                 <td width="200"><?php echo $val->name; ?></td>
                                 <td  style="text-align: right;" width="100" class="<?php echo str_replace(' ','_',$val->name).'2'; ?>"><?php echo $val->unit; ?></td>
                                 <input type="hidden" class="saleSum <?php echo str_replace(' ','_',$val->name).'1'; ?>" value="<?php echo $val->amount; $grasstot = $grasstot+$val->amount; ?>" >
                                 <td width="30%" style="text-align: right;" class="<?php echo str_replace(' ','_',$val->name); ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                              </tr>
                              <?php } ?>
                           </tbody>
                           <tfoot style="border-top: 1px solid #000;border-bottom:1px double #000">
                              <tr>
                                 <th colspan="2" width="300" style="font-size:" align="right" >Total</th>
                                 <th  width="30%" style="text-align: right;"  id="saletotal"></th>
                              </tr>
                           </tfoot>
                        </table>
                     </div>
                     <div class="col-md-2">
                        <table class="table custom-table fixed-tbody" >
                           <thead>
                              <tr>
                                 <th width="450" style="text-align:center" colspan="2">DISCOUNTS & CREDITS</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach($disSum as $key=>$val){
                                 ?>
                              <tr>
                                 <td width="250"><?php echo $val->name; ?></td>
                                 <input type="hidden" class="disSum <?php echo str_replace(' ','_',$val->name).'_73'; ?>" value="<?php echo $val->amount; ?>">
                                 <td style="color:red; float:right;" class="<?php echo str_replace(' ','_',$val->name)."_7_77"; ?>"><?php echo "($".number_format($val->amount,2).")"; ?></td>
                              </tr>
                              <?php } ?>
                           </tbody>
                           <tfoot style="border-top: 1px solid #000;border-bottom:1px double #000">
                              <tr>
                                 <th width="250">Total</th>
                                 <th style="color:red;" id="distotal"></th>
                              </tr>
                           </tfoot>
                        </table>
                     </div>
                     <div class="col-md-2">
                        <table class="table custom-table fixed-tbody">
                           <thead>
                              <tr>
                                 <th width="450" colspan="2" style="text-align:center">ADJUSTMENTS</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php
							                $sale_tax = 0;
							                foreach($adjSum as $key=>$val){
                                 $color = '';
                                 if($val->name != 'Sales Tax'){
                                  $color = 'red';
                                  $value = "($".number_format($val->amount,2).")";
                                 }else {
                                  $color = 'black';
                                  $value = "$".number_format($val->amount,2);
								                  $sale_tax = $val->amount;
                                 }
                                 ?>
                              <tr>
                                 <td width="250"><?php echo $val->name; ?></td>
                                 <input type="hidden" class="adjSum <?php echo str_replace(' ','_',$val->name).'_84'; ?>" id="<?= "adj_".str_replace(' ','_',$val->name); ?>" value="<?php echo $val->amount; ?>" >
                                 <td style="color:<?php echo $color; ?>; text-align: right;"  class="<?php echo str_replace(' ','_',$val->name)."_8"; ?>"><?php echo $value; ?></td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <th>Over/(Short) </th>
                                 <th style="text-align:right!important;" id="finalovertotal"></th>
                              </tr>
                           </tbody>
                           <tfoot style="border-top: 1px solid #000;border-bottom:1px double #000">
                              <tr>
                                 <th width="250">Total</th>
                                 <th id="adjtotal"></th>
                              </tr>
                           </tfoot>
                        </table>
                      </div>
                      <div class="col-md-2">
                        <table class="table custom-table fixed-tbody">
                           <thead>
                              <tr>
                                 <th width="450" colspan="2" style="text-align:center">RECEIPTS </th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php
                                $netsale = $lbtot= 0;
                               foreach($recSum as $key=>$val){
                                 ?>
                              <tr>
                                 <td width="250"><?php echo $val->name; ?></td>
                                 <input type="hidden" class="recSum <?php echo str_replace(' ','_',$val->name).'_95'; ?>" value="<?php echo  $val->amount; $netsale= $netsale+$val->amount; ?>">
                                 <td class="<?php echo str_replace(' ','_',$val->name)."_9"; ?>"><?php echo "$".number_format($val->amount,2); ?></td>
                              </tr>
                              <?php } ?>
                           </tbody>
                           <tfoot style="border-top: 1px solid #000;border-bottom:1px double #000">
                              <tr>
                                 <th width="250">Total</th>
                                 <th id="rectotal"></th>
                              </tr>
                           </tfoot>
                        </table>
                     </div>
                      <div class="col-md-2 kv-summary-stack">
                          <table class="table custom-table fixed-tbody kv-summary-compact">
                           <thead>
                              <tr>
                                 <th width="450" colspan="2" style="text-align:center">TOTAL SALES </th>
                              </tr>
                           </thead>
                           <tbody>
                             <tr>
                                <td width="250">Gross Sales</td>
                                <td id="grasssale"  style="text-align: right;"> ${{ number_format($grasstot,2) }}</td>
                             </tr>
                              <tr>
                                 <td width="250">Net Sales</td>
                                 <td id="netsale" style="text-align: right;">${{ number_format(($netsale-$sale_tax),2) }}  </td>
                              </tr>
                            </tbody>
                         </table>
                         <table class="table custom-table fixed-tbody kv-summary-compact kv-summary-labor">
                            <thead>
                               <tr>
                                  <th colspan="2" style="text-align:center">LABOR</th>
                               </tr>
                            </thead>
                            <tbody>
                             <tr>
                                <td>Labor %</td>
                                <?php
                                 $tot_sale = 0;
                                 if(isset($id) && $id>0){
                                   $tot_sale = getLaberRate(Session::get('location_id'),$id);
                                 }
                                ?>
                                <td id="lbper"  style="text-align: right;">@if($netsale>0 && $tot_sale >0){{ number_format(($tot_sale/$netsale)*100,2)}}% @else 0.00% @endif</td>
                             </tr>
                           </tbody>
                        </table>
                     </div>

                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="kv-ledger-viewbar" role="group" aria-label="Weekly sales entry view">
         <div class="kv-ledger-view-switch">
            <button type="button" class="is-active" data-ledger-view="daily"><i class="fa fa-calendar-check-o"></i> Daily Focus Entry <small>Recommended</small></button>
            <button type="button" data-ledger-view="week"><i class="fa fa-table"></i> Full Week Matrix</button>
         </div>
         <div class="kv-ledger-day-strip" aria-label="Choose a day"></div>
      </div>
      <div id="kv-daily-focus-panel" class="kv-daily-focus-panel" hidden>
         <div class="kv-daily-focus-heading">
            <span class="kv-daily-focus-date-icon"><i class="fa fa-calendar"></i></span>
            <div><h2 id="kv-daily-focus-title">Daily Ledger</h2><p>Enter and review this day’s store activity.</p></div>
            <span class="kv-daily-focus-status"><i class="fa fa-circle"></i> Daily entry</span>
            <button type="button" class="btn btn-primary fs-fw" data-toggle="modal" data-target="#dailyEntryPdfModal"><i class="fa fa-upload"></i> Upload PDF</button>
         </div>
         <div class="kv-daily-focus-layout">
            <div class="kv-daily-focus-sections">
               <section class="kv-focus-section kv-focus-sales" data-focus-group="sale"><h3><span>1</span> Sales &amp; Garment Services</h3><div class="kv-focus-card-grid"></div></section>
               <section class="kv-focus-section kv-focus-adjustments" data-focus-group="adj"><h3><span>3</span> Adjustments &amp; Tax</h3><div class="kv-focus-card-grid"></div></section>
            </div>
            <aside class="kv-daily-focus-side">
               <section class="kv-daily-reconciliation"><h3><i class="fa fa-calculator"></i> Day Reconciliation</h3>
                  <dl><div><dt>Gross sales</dt><dd data-day-total="sales">$0.00</dd></div><div><dt>Total receipts</dt><dd data-day-total="receipts">$0.00</dd></div><div><dt>Over / short</dt><dd data-day-total="over">$0.00</dd></div></dl>
               </section>
               <section class="kv-focus-section kv-focus-discounts" data-focus-group="dis"><h3><span>2</span> Discounts &amp; Credits</h3><div class="kv-focus-card-grid"></div></section>
               <section class="kv-focus-section kv-focus-receipts" data-focus-group="rec"><h3><span>4</span> Drawer &amp; Tender Receipts</h3><div class="kv-focus-card-grid"></div></section>
            </aside>
         </div>
         <p class="kv-daily-focus-footnote"><i class="fa fa-info-circle"></i> Use Full Week Matrix to compare or edit multiple days together.</p>
      </div>
      <div class="modal fade kv-location-modal" id="dailyEntryPdfModal" tabindex="-1" role="dialog" aria-labelledby="dailyEntryPdfModalTitle">
         <div class="modal-dialog" role="document">
            <div class="modal-content">
               <form id="sales-pdf-upload-form" method="POST" action="{{ url('/uploads/daily-entry-pdf') }}" enctype="multipart/form-data">
                  {{ csrf_field() }}
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                     <h4 class="modal-title" id="dailyEntryPdfModalTitle">Upload Daily Entry PDF</h4>
                  </div>
                  <div class="modal-body">
                     <p>Upload a PDF for <strong id="dailyEntryPdfDateLabel">the selected day</strong>.</p>
                     <input type="hidden" name="entry_date" id="dailyEntryPdfDate">
                     <div class="kv-location-fields">
                        <div class="form-group">
                           <label for="dailyEntryPdfFile">PDF file</label>
                           <input class="form-control" type="file" name="file" id="dailyEntryPdfFile" accept=".pdf,application/pdf" required>
                           <small class="help-block">One daily sales report, up to 20 MB. The PDF date must match the selected day. Review values before importing.</small>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn kv-location-cancel" data-dismiss="modal">Cancel</button>
                     <button type="submit" class="btn kv-location-submit">Read &amp; Preview PDF</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <script>
      document.getElementById('sales-pdf-upload-form').addEventListener('submit', function () {
         var button = this.querySelector('button[type="submit"]');
         button.disabled = true;
         button.textContent = 'Reading PDF... Please wait';
      });
      </script>
      <!--  <div class="filter-container">
         <div class="row">
            <div class="col-md-2">
               <label>Sales</label>
            </div>
         </div>
         </div> -->
      <!-- <div id="ajaxLoadAni" style="display:none;">
         <img  height="250px" src="http://52.43.26.102/outbrainManagement/resources/views/campaigns/loading.gif" alt="Loading..." />
         </div> -->
      <div class="row">
         <div class="col-md-12">
            <div class="widget form-layout" style="margin-bottom:10px;">
               <div class="widget-content" style="padding: 0;">
                  <div class="row">
                     <div class="col-md-12">
                        <table class="table custom-table kv-ledger-matrix" id="popover-viewport" nowrap>
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
                                 <th width="130px">Date</th>
                                 <th colspan="2" id="day1" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[0])); ?></th>
                                 <!--<th colspan="2" id="day2" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[1])); ?></th>-->
                                 <th colspan="2" id="day3" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[2]));; ?></th>
                                 <th colspan="2" id="day4" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[3]));; ?></th>
                                 <th colspan="2" id="day5" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[4]));; ?></th>
                                 <th colspan="2" id="day6" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[5]));; ?></th>
                                 <th colspan="2" id="day7" class="text-center"><?php echo date('l m-d-Y',strtotime($dates[6]));; ?></th>
                              </tr>
                              <tr>
                                 <th></th>
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                                 <!--<th class="text-center" style="width:59px;">Units</th>
                                    <th class="text-center">Amount</th>-->
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                                 <th class="text-center" style="width:59px;">Units</th>
                                 <th class="text-center">Amount</th>
                              </tr>
                           </thead>
                              <?php
                              $saleArray = [];
                              $disArray = [];
                              $adjustArray = [];
                              $receiptArray = [];

                              $weekCal = $editWkNo;
                              $storeCal = $storeWeekMatch;

                              // Sales
                              foreach ($saleData as $sd) {
                                 if ((string) $sd->saleStatus === '0') {
                                    $saleArray[] = [
                                          'id' => $sd->id,
                                          'name' => $sd->name,
                                    ];
                                 } elseif (
                                    (string) $sd->saleStatus === '1' &&
                                    $sd->storeWeekNo == $weekCal &&
                                    $sd->calWeekNo == $storeCal
                                 ) {
                                    $saleArray[] = [
                                          'id' => $sd->id,
                                          'name' => $sd->name,
                                    ];
                                 }
                              }

                              // Discounts
                              foreach ($discountData as $dd) {
                                 if ((string) $dd->disStatus === '0') {
                                    $disArray[] = [
                                          'id' => $dd->id,
                                          'name' => $dd->name,
                                    ];
                                 } elseif (
                                    (string) $dd->disStatus === '1' &&
                                    $dd->storeWeekNo == $weekCal &&
                                    $dd->calWeekNo == $storeCal
                                 ) {
                                    $disArray[] = [
                                          'id' => $dd->id,
                                          'name' => $dd->name,
                                    ];
                                 }
                              }

                              // Adjustments
                              foreach ($adjustmentData as $adj) {
                                 if ((string) $adj->adjStatus === '0') {
                                    $adjustArray[] = [
                                          'id' => $adj->id,
                                          'name' => $adj->name,
                                    ];
                                 } elseif (
                                    (string) $adj->adjStatus === '1' &&
                                    $adj->storeWeekNo == $weekCal &&
                                    $adj->calWeekNo == $storeCal
                                 ) {
                                    $adjustArray[] = [
                                          'id' => $adj->id,
                                          'name' => $adj->name,
                                    ];
                                 }
                              }

                              // Receipts
                              foreach ($receiptData as $rd) {
                                 if ((string) $rd->recStatus === '0') {
                                    $receiptArray[] = [
                                          'id' => $rd->id,
                                          'name' => $rd->name,
                                    ];
                                 } elseif (
                                    (string) $rd->recStatus === '1' &&
                                    $rd->storeWeekNo == $weekCal &&
                                    $rd->calWeekNo == $storeCal
                                 ) {
                                    $receiptArray[] = [
                                          'id' => $rd->id,
                                          'name' => $rd->name,
                                    ];
                                 }
                              }
                              ?>
                           <tbody>
                              <tr class="thead-Dark ledger-section-sales">
                                 <th colspan="15" align="center" style="padding: 0;text-align: center;font-size: 13px;">SALES<input type="submit" name="" value="Add New" class="v-btn v-btn-primary btn-xs pull-right" style="font-size:10px;font-weight:200px;" onClick="addNewCol('saleColumn','sale')"></th>
                              </tr>
                              <?php
                                 foreach($saleArray as $sd){
                                 ?>
                              <tr class="saleColumn">
                                 <th width="200px"><?php echo $sd['name'] ; ?></th>
                                 <?php
                                    $match = $sd['name'].'-'.$dates[0].'-'.'sale';
                                    $unit = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);
                                    $amount = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);
                                    $comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td>

                                    <input type="text" class="enterTab sale saturdaydate sumunit sumunitmon form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dateat = "" dayno ="day1" name="{{ $sd['name'] }}"  value="<?php  echo $unit ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale saturdaydate sumamt sumamtmon form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day1" dateat = "" name="{{ $sd['name'] }}" value="<?php  echo $amount; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day1','{{ $sd['name'] }}','{{ $comment }}')" id="sale_{{$sd['id']}}_amt_day1_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                                 <?php $match = $sd['name'].'-'.$dates[1].'-'.'sale';$unitsun = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);$amountsun = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <!-- <td>
                                 <input type="text" class="enterTab sale sundaydate sumunit sumunittue form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day2" dateat = "" name="{{ $sd['name'] }}"  value="<?php  echo $unitsun ?>" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                 </td>
                                    <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale sundaydate sumamt sumamttue form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day2" dateat = "" name="{{ $sd['name'] }}" value="<?php  echo $amountsun ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day2','{{ $sd['name'] }}','{{ $comment }}')">
                                    </td>-->
                                 <?php $match = $sd['name'].'-'.$dates[2].'-'.'sale';$unitmon = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);$amountmon = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td ><input type="text" class="enterTab sale mondaydate sumunit sumunitwed form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day3" dateat = "" name="{{ $sd['name'] }}"  value="<?php  echo $unitmon ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                 </td>
                                 <td  style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale mondaydate sumamt sumamtwed form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day3" dateat = "" name="{{ $sd['name'] }}" value="<?php  echo $amountmon ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day3','{{ $sd['name'] }}','{{ $comment }}')"  id="sale_{{$sd['id']}}_amt_day3_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                                 <?php $match = $sd['name'].'-'.$dates[3].'-'.'sale';$unittue = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);$amounttue = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td ><input type="text" class="enterTab sale tuesdaydate sumunit sumunitthr form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day4" dateat = "" name="{{ $sd['name'] }}"  value="<?php  echo $unittue ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                 </td>
                                 <td  style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale tuesdaydate sumamt sumamtthr form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day4" dateat = "" name="{{ $sd['name'] }}" value="<?php  echo $amounttue ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day4','{{ $sd['name'] }}','{{ $comment }}')" id="sale_{{$sd['id']}}_amt_day4_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                                 <?php $match = $sd['name'].'-'.$dates[4].'-'.'sale';
                                    $unitwed = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);  $amountwed = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td ><input type="text" class="enterTab sale wednesdaydate sumunit sumunitfri form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day5" dateat = "" name="{{ $sd['name'] }}"  value="<?php echo $unitwed; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                 </td>
                                 <td  style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale wednesdaydate sumamt sumamtfri form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day5" dateat = "" name="{{ $sd['name'] }}" value="<?php echo $amountwed; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day5','{{ $sd['name'] }}','{{ $comment }}')" id="sale_{{$sd['id']}}_amt_day5_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                                 <?php $match = $sd['name'].'-'.$dates[5].'-'.'sale';$unitthr = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);$amountthr = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td ><input type="text" class="enterTab sale thrusdaydate sumunit sumunitsat form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day6" dateat = "" name="{{ $sd['name'] }}"  value="<?php echo $unitthr ;?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')">
                                 </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale thrusdaydate sumamt sumamtsat form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day6" dateat = "" name="{{ $sd['name'] }}" value="<?php echo $amountthr; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('sale_{{ $sd['id'] }}','amt','day6','{{ $sd['name'] }}','{{ $comment }}')" id="sale_{{$sd['id']}}_amt_day6_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                                 <?php $match = $sd['name'].'-'.$dates[6].'-'.'sale';$unitsun = (empty($dataList[$match]['unit'])?'':$dataList[$match]['unit']);$amountsun = (empty($dataList[$match]['amount'])?'':$dataList[$match]['amount']);$comment = (empty($dataList[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataList[$match]['comment']))));?>
                                 <td>
                                    <input type="text" class="enterTab sale fridaydate sumunit sumunitsun form-control <?php echo str_replace(' ','_',$sd['name']).'2' ; ?>" style="font-size:10px;" nameId= "sale_{{ $sd['id'] }}" unit="units" dayno ="day7" dateat = "" name="{{ $sd['name'] }}"  value="<?php echo $unitsun; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="unitSum('<?php echo str_replace(' ','_',$sd['name']).'2' ; ?>')" ></td>
                                 <td  style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale fridaydate sumamt sumamtsun form-control currency <?php echo str_replace(' ','_',$sd['name']) ; ?>" nameId= "sale_{{ $sd['id'] }}" unit="amt" dayno ="day7" dateat = "" name="{{ $sd['name'] }}" value="<?php echo $amountsun; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="amountSum('<?php echo str_replace(' ','_',$sd['name']) ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick='dataGet("sale_{{ $sd["id"] }}","amt","day7","{{ $sd["name"] }}","{{ $comment }}")' id="sale_{{$sd['id']}}_amt_day7_<?= str_replace(' ','_',$sd['name']);?>">
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <th style="font-size:15px;">Total</th>
                                 <td>
                                    <input type="text" readonly name="" id="sumunitmonday" class="form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtmonday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <!--<td>
                                 <input type="text" readonly name="" id="sumunittuesday" class="sale form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                 </td>
                                    <td>
                                    $<input type="text" readonly name="" id="sumamttuesday" class="sale form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                    </td>-->
                                 <td>
                                    <input type="text" readonly name="" id="sumunitwednesday" class=" form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtwednesday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="text" readonly name="" id="sumunitthrusday" class=" form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtthrusday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="text" readonly name="" id="sumunitfriday" class=" form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtfriday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="text" readonly name="" id="sumunitsaturday" class=" form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtsaturday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="text" readonly name="" id="sumunitsunday" class=" form-control" placeholder="" style="font-weight:bold;font-size:10px;">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="sumamtsunday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                              </tr>
                              <tr class="thead-Dark ledger-section-discounts">
                                 <th colspan="15" style="padding:0;text-align:center;font-size:13px;">DISCOUNTS & CREDITS<input type="submit" name="" value="Add New" class="v-btn v-btn-primary btn-xs pull-right" style="font-size:10px;font-weight:200px;" onClick="addNewCol('disColumn','dis')"></th>
                              </tr>
                              <?php
                                 foreach($disArray as $dd){
                                 ?>
                              <tr class="disColumn">
                                 <th ><?php echo $dd['name'] ; ?></th>
                                 <?php $match = $dd['name'].'-'.$dates[0].'-'.'dis';
                                    $amount = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);
                                    $comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale saturdaydate disamt disamtmon form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day1" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amount; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day1','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day1_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                                 <?php $match = $dd['name'].'-'.$dates[1].'-'.'dis';$amountsun = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale sundaydate disamt disamttue form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day2" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amountsun; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day2','{{ $dd['name'] }}','{{ $comment }}')">
                                    </td>-->
                                 <?php $match = $dd['name'].'-'.$dates[2].'-'.'dis';$amountmon = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale mondaydate disamt disamtwed form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day3" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amountmon; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day3','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day3_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                                 <?php $match = $dd['name'].'-'.$dates[3].'-'.'dis';$amounttue = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale tuesdaydate disamt disamtthr form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day4" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amounttue; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day4','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day4_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                                 <?php $match = $dd['name'].'-'.$dates[4].'-'.'dis';$amountwed = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale wednesdaydate disamt disamtfri form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day5" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amountwed; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day5','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day5_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                                 <?php $match = $dd['name'].'-'.$dates[5].'-'.'dis';$amountthr = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale thrusdaydate disamt disamtsat form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day6" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amountthr; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day6','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day6_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                                 <?php $match = $dd['name'].'-'.$dates[6].'-'.'dis';$amountfri = (empty($dataListDiscount[$match]['amount'])?'':$dataListDiscount[$match]['amount']);$comment = (empty($dataListDiscount[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListDiscount[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;color:red;font-size:10px;" class="enterTab sale fridaydate disamt disamtsun form-control currency <?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>" unit="amt" nameId= "dis_{{ $dd['id'] }}"  dayno ="day7" dateat = "" name="{{ $dd['name'] }}" value="<?php echo $amountfri; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="disAmountSum('<?php echo str_replace(' ','_',$dd['name'])."_7" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('dis_{{ $dd['id'] }}','amt','day7','{{ $dd['name'] }}','{{ $comment }}')" id="dis_{{$dd['id']}}_amt_day7_<?= str_replace(' ','_',$dd['name']);?>">
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <th style="font-size:15px;">Total</th>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtmonday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamttuesday" class="sale form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                    </td>-->
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtwednesday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtthrusday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtfriday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtsaturday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="disamtsunday" class=" form-control" placeholder="" style="width:65px;display:inline-block;color:red;font-weight:bold;font-size:10px;">
                                 </td>
                              </tr>
                              <tr class="thead-Dark ledger-section-adjustments">
                                 <th colspan="15" style="padding:0;text-align:center;font-size:13px;">ADJUSTMENTS<input type="submit" name="" value="Add New" class="v-btn v-btn-primary btn-xs pull-right" style="font-size:10px;font-weight:200px;" onClick="addNewCol('adjColumn','adj')"></th>
                              </tr>
                              <?php
                                 foreach($adjustArray as $adj){
                                 ?>
                              <tr class="adjColumn">
                                 <th ><?php echo $adj['name'] ; ?></th>
                                 <?php
                                    $color = '';
                                    if($adj['name'] != 'Sales Tax'){
                                     $color = 'red';
                                    }else {
                                     $color = 'black';
                                    }?>
                                 <?php $match = $adj['name'].'-'.$dates[0].'-'.'adj';$amountsat1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);
                                    $comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale saturdaydate adjamt adjamtmon form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day1" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountsat1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day1','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day1_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                                 <?php $match = $adj['name'].'-'.$dates[1].'-'.'adj';$amountsun1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale sundaydate adjamt adjamttue form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day2" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountsun1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day2','{{ $adj['name'] }}','{{ $comment }}')">
                                    </td>-->
                                 <?php $match = $adj['name'].'-'.$dates[2].'-'.'adj';$amountmon1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale mondaydate adjamt adjamtwed form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day3" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountmon1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day3','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day3_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                                 <?php $match = $adj['name'].'-'.$dates[3].'-'.'adj';$amounttue1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale tuesdaydate adjamt adjamtthr form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day4" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amounttue1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day4','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day4_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                                 <?php $match = $adj['name'].'-'.$dates[4].'-'.'adj';$amountwed1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale wednesdaydate adjamt adjamtfri form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day5" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountwed1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day5','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day5_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                                 <?php $match = $adj['name'].'-'.$dates[5].'-'.'adj';$amountthr1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale thrusdaydate adjamt adjamtsat form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day6" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountthr1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day6','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day6_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                                 <?php $match = $adj['name'].'-'.$dates[6].'-'.'adj';$amountfri1 = (empty($dataListAdjust[$match]['amount'])?'':$dataListAdjust[$match]['amount']);$comment = (empty($dataListAdjust[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListAdjust[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline; color:<?php echo $color;?>;font-size:10px;" class="enterTab sale fridaydate adjamt adjamtsun form-control currency <?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>" unit="amt" nameId= "adj_{{ $adj['id'] }}" dayno ="day7" dateat = "" name="{{ $adj['name'] }}" value="<?php echo $amountfri1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="adjAmountSum('<?php echo str_replace(' ','_',$adj['name'])."_8" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('adj_{{ $adj['id'] }}','amt','day7','{{ $adj['name'] }}','{{ $comment }}')" id="adj_{{$adj['id']}}_amt_day7_<?= str_replace(' ','_',$adj['name']);?>">
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <th style="font-size:15px;">Total</th>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtmonday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td>
                                    $<input type="text" readonly name="" id="adjamttuesday" class="sale form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                    </td>-->
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtwednesday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtthrusday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtfriday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtsaturday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="adjamtsunday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                              </tr>
                              <tr class="thead-Dark ledger-section-receipts">
                                 <th colspan="15" style="padding:0;text-align:center;font-size:13px;">RECEIPTS<input type="submit" name="" value="Add New" class="v-btn v-btn-primary btn-xs pull-right" style="font-size:10px;font-weight:200px;" onClick="addNewCol('recColumn','rec')"></th>
                              </tr>
							  <pre>
                              <?php

                                 foreach($receiptArray as $rD){
                                 ?>
                              <tr class="recColumn">
                                 <th ><?php echo $rD['name'] ; ?></th>
                                 <?php $match = $rD['name'].'-'.$dates[0].'-'.'rec';$amount1 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);
                                    $comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;" >
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale saturdaydate recamt recamtmon form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day1" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount1; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day1','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day1_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                                 <?php $match = $rD['name'].'-'.$dates[1].'-'.'rec';$amount2 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale sundaydate recamt recamttue form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day2" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount2; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day2','{{ $rD['name'] }}','{{ $comment }}')">
                                    </td>-->
                                 <?php $match = $rD['name'].'-'.$dates[2].'-'.'rec';$amount3 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale mondaydate recamt recamtwed form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day3" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount3; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day3','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day3_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                                 <?php $match = $rD['name'].'-'.$dates[3].'-'.'rec';$amount4 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale tuesdaydate recamt recamtthr form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day4" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount4; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day4','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day4_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                                 <?php $match = $rD['name'].'-'.$dates[4].'-'.'rec';$amount5 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale wednesdaydate recamt recamtfri form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day5" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount5; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day5','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day5_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                                 <?php $match = $rD['name'].'-'.$dates[5].'-'.'rec';$amount6 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale thrusdaydate recamt recamtsat form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day6" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount6; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day6','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day6_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                                 <?php $match = $rD['name'].'-'.$dates[6].'-'.'rec';$amount7 = (empty($dataListRec[$match]['amount'])?'':$dataListRec[$match]['amount']);$comment = (empty($dataListRec[$match]['comment'])?'':trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $dataListRec[$match]['comment']))));?>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="position: relative; text-align:left; width: 90px; border-right: 2px solid #585454;">
                                    $<input type="text" style="width:65px; display:inline;font-size: 10px;font-weight: 800;" class="enterTab sale fridaydate recamt recamtsun form-control currency <?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>" unit="amt" nameId= "rec_{{ $rD['id'] }}" dayno ="day7" dateat = "" name="{{ $rD['name'] }}" value="<?php echo $amount7; ?>" onkeypress="return validateFloatKeyPress(this,event);" onChange="recAmountSum('<?php echo str_replace(' ','_',$rD['name'])."_9" ; ?>')">
                                    <i class="fa fa-comment getlist" style="display:inline; font-size:8px; color:<?= (!empty($comment))?'red':'green';?>;" onClick="dataGet('rec_{{ $rD['id'] }}','amt','day7','{{ $rD['name'] }}','{{ $comment }}')" id="rec_{{$rD['id']}}_amt_day7_<?= str_replace(' ','_',$rD['name']);?>">
                                 </td>
                              </tr>
                              <?php
                                 }
                                 ?>
                              <tr>
                                 <th style="font-size:15px;">Total</th>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="$45">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtmonday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td>
                                    $<input type="text" readonly name="" id="recamttuesday" class="sale form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                    </td>-->
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtwednesday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtthrusday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtfriday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtsaturday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right: 12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly name="" id="recamtsunday" class=" form-control" placeholder="" style="width:65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                              </tr>
                              <tr class="thead-Light">
                                 <th colspan="15" style="padding:0;text-align:center;font-size:13px;">OVER/(SHORT)</th>
                              </tr>
                              <tr>
                                 <th width="200px">OVER/(SHORT)</th>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="" ></td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="oversaturday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <!--<td>
                                 <input type="hidden" name="" class="form-control" placeholder="">
                                 </td>
                                    <td>
                                        <input type="text" readonly class="form-control" name="saleD" id="oversunday" value="" style="font-weight:bold;font-size:10px;">
                                    </td>-->
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="overmonday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="overtuesday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="overwednesday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="overthrusday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                                 <td>
                                    <input type="hidden" name="" class="form-control" placeholder="">
                                </td>
                                 <td style="text-align:left; padding-right:12px!important; border-right: 2px solid #585454;">
                                    $<input type="text" readonly class="form-control" name="saleD" id="overfriday" value="" style="width: 65px;display:inline-block;font-weight:bold;font-size:10px;">
                                 </td>
                              </tr>
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
   <div style="text-align: center;">
      <button type="button" id="ClickMe" class="v-btn v-btn-primary">Save Data</button>
   </div>
</section>
<!--<div class="modal fade" id="modalData" role="dialog" >
   <div class="modal-dialog " >
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
   </div>-->
<div  id="modalData" class="popupNameId" style="display:none;">
   <input type="hidden" name="popupName" id="popupName">
   <input type="hidden" name="popupUnit" id="popupUnit">
   <input type="hidden" name="popupDay" id="popupDay">
   <input type="hidden" name="popupNameId" id="popupNameId">
   <input type="hidden" name="popupdayname" id="popupdayname">
   <input type="hidden" name="popupweekNo" id="popupweekNo">
   <input type="hidden" name="popupStorNo" id="popupStorNo">
   <textarea name="comment" form="usrform" id="inputMessage" style="width:100%;height:188px;" placeholder="Enter text here..." ></textarea>
   <button type="button" class="btn btn-primary "  onclick="submitContactForm()">SUBMIT</button>
   <div class="alert alert-success message" style="display:none">Comment Saved Successfully!</div>
</div>
<!--
   <div class="modal fade" id="modalDepData" role="dialog">
       <div class="modal-dialog">

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
                        <input type="text" name="fieldValue" id="fieldValue" class="form-control">
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
   </div>-->
<style type="text/css">
   .quick-info>li{vertical-align: middle;}
   .quick-info>li>span{font-size: 20px; margin-right: 20px; font-weight:800}
   .custom-table .form-control{height: 20px; font-weight: normal; border-color: #eee; text-align: center; margin-bottom:0;}
   .custom-table{margin-bottom:0;}
   .custom-table>thead{    background-color: #382f2f;
   color: #ffffffc7;}
   .custom-table>thead>tr{border-bottom: 0;}
   .custom-table>tbody>tr>th,
   .custom-table>tbody>tr>td,
   .custom-table>thead>tr>th{padding: 4px 8px; font-size: 10px; font-weight: bold; border-bottom:0; border-top: 0; }
   /* table.table.custom-table>tbody>tr>td ,table.table.custom-table>tbody>tr>th {
   white-space: normal;
   word-break: break-all;
   word-wrap: break-word!important;
   }*/
   table.table.custom-table>tbody>tr>td:first-child, table.table.custom-table>tbody>tr>th:first-child {
   /* max-width: 133px; */
   white-space: normal;
   word-break: break-all;
   word-wrap: break-word!important;
   }
   .custom-table>tbody>tr:nth-child(odd){}
   .custom-table>thead>tr>th{border-left:1px solid #ababab}
   .thead-Dark{background-color: #909090; color: #fff;}
   .thead-Light{background-color: #382f2f59; color: #fff; }
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
   .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control {
   background-color: #fff !important;
   opacity: 1;
   }
   .comment{
   position: absolute;
   top: 8px;
   right: 0;
   }
</style>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<input type="hidden" id="hidecomment" />
<script type="text/javascript">
   $.ajaxSetup({
          headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
   });
</script>
<script type="text/javascript">
   $(window).on("load", function (e) {

      var stDate = "<?php echo $sDate; ?>";
      var eDate = "<?php echo $eDate; ?>";
      var calDate1 = "<?php echo date('m-d-Y',strtotime($sDate)); ?>";
      var calDate2 = "<?php echo date('m-d-Y',strtotime($eDate)); ?>";
      var calDate = calDate1 + ' - ' + calDate2;
      var editWkNo = <?= $editWkNo ?>;
      $("#weeklyDatePicker").val(calDate);
      $.ajax({
         type:       "POST",
         dataType:   "text",
         url:        "{{ url('/') }}/weekData",
         data: {stDate: stDate,eDate:eDate,editWkNo:editWkNo},
         success: function(data)
         {
            //alert("test");
            var value = data.split("|");
            //value = $.trim(value);
            $('#weekNoData').text($.trim(value[0]));
            $('#storeNoData').text(value[1]);
         }
      });

      var sumSale = 0;
      $(".saleSum").each(function(){
      sumSale += +$(this).val(); });
      var SaleT = numberWithCommas(sumSale.toFixed(2));
      $("#saletotal").text("$"+SaleT);

      var sumdis = 0;
      $(".disSum").each(function(){
      sumdis += +$(this).val(); });
      var DisT = numberWithCommas(sumdis.toFixed(2));
      $("#distotal").text("($"+DisT+")");


      var sumadj = 0;
      var sumAttrName = 0;
      $(".adjSum").each(function(){
         var idName = this.id;
         idName = idName.replace("adj_","");
         if(idName == 'Employee_Advance' || idName == 'Employee_Loan' || idName == 'Paid_Out') {
            sumAttrName += +$(this).val();
         } else {
            sumadj += +$(this).val();
         }
      });
      sumadj = parseFloat(sumadj) - parseFloat(sumAttrName);
      var AdjT = numberWithCommas(sumadj.toFixed(2));
      $("#adjtotal").text("$"+AdjT);


      var sumrec = 0;
      $(".recSum").each(function(){
        console.log($(this).val(),'$(this).val()');
      sumrec += +$(this).val(); });
      var RecT = numberWithCommas(sumrec.toFixed(2));
      $("#rectotal").text("$"+RecT);

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
         maxDate: moment($("#endDate").val(),'MM-DD-YYYY'), // 'now',
         // minDate: $("#startDate").val()
         minDate: moment($("#startDate").val(),'MM-DD-YYYY')
      });


      $(document).ready(function(){
         moment.locale('en', {
         week: { dow: -1 }
      });



     //Get the value of Start and End of Week
     $('#weeklyDatePicker').on('dp.change', function (e) {
      var value = $("#weeklyDatePicker").val();
      var selectedDay = new Date(value);

        //document.getElementById("demo").innerHTML = d.getTime();
      var fullDate = new Date();
      var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
      var currentDate = twoDigitMonth + "-" + fullDate.getDate() + "-" + fullDate.getFullYear();
      if(value == ''){
         var value = currentDate;
      }
      if(selectedDay.getDay() ==6){
          var firstDate = value;
          var lastDate = moment(value, "MM-DD-YYYY").day(12).format("MM-DD-YYYY");
        } else{
          var firstDate = moment(value, "MM-DD-YYYY").day(-1).format("MM-DD-YYYY");
          var lastDate =  moment(value, "MM-DD-YYYY").day(5).format("MM-DD-YYYY");
        }
      $("#weeklyDatePicker").val(firstDate + " - " + lastDate);
         /* if((firstDate != '') && (lastDate != '') ) {
            $('#weekDatapick').trigger('click');
         } */

     });




      /* $('.sale').keyup(function() {
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
               success: function(data) {
               }
            });
      });       */


      $("#ClickMe").click(function () {
         $.LoadingOverlay("show");

         //$('#ajaxLoadAni').fadeIn('slow');
           var arrnew = [];
            $(".sale").each(function() {
                  var SaleDataArray = {};
                  var name = ($(this).attr('name'));
                  var unit = ($(this).attr('unit'));
                  var dayno = ($(this).attr('dayno'));
                  var val = ($(this).val());
                  var dayname = $('#'+dayno).text();
                  var nameID = ($(this).attr('nameId'));
                  var weekNo = $('#weekNoData').text();
                  var storeNo = $('#storeNoData').text();


                     SaleDataArray['name'] = name;
                     SaleDataArray['unit'] = unit;
                     SaleDataArray['dayno'] = dayno;
                     SaleDataArray['val'] = val;
                     SaleDataArray['dayname'] = dayname;
                     SaleDataArray['nameID'] = nameID;
                     SaleDataArray['weekNo'] = weekNo;
                     SaleDataArray['storeNo'] = storeNo;

                     arrnew.push(SaleDataArray);


            });
            var jsonString = JSON.stringify(arrnew);
            $.ajax({
               type:       "POST",
               url:        "{{ url('/') }}/dataSaveSales",
               data: {allsaledata: jsonString},
               success: function(data) {
                  $.LoadingOverlay("hide");
                  $("#ClickMe").css("background-color", "green");
                  /*if(data == 1){
                     $('#ajaxLoadAni').fadeOut('slow');
                  }*/
               }
            });

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

            if(!isNaN(sum))
               $('#sumamtmonday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamttue")== true){
            $(".sumamttue").each(function() {
               sum += +this.value;
            });

            if(!isNaN(sum))
               $('#sumamttuesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamtwed")== true){
            $(".sumamtwed").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#sumamtwednesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamtthr")== true){
            $(".sumamtthr").each(function() {
               sum += +this.value;
            });
              if(!isNaN(sum))
                    $('#sumamtthrusday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamtfri")== true){
            $(".sumamtfri").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#sumamtfriday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamtsat")== true){
            $(".sumamtsat").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#sumamtsaturday').val(sum.toFixed(2));
         }
         if($(this).hasClass("sumamtsun")== true){
            $(".sumamtsun").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
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
            if(!isNaN(sum))
               $('#disamtmonday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamttue")== true){
            $(".disamttue").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamttuesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamtwed")== true){
            $(".disamtwed").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamtwednesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamtthr")== true){
            $(".disamtthr").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamtthrusday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamtfri")== true){
            $(".disamtfri").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamtfriday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamtsat")== true){
            $(".disamtsat").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamtsaturday').val(sum.toFixed(2));
         }
         if($(this).hasClass("disamtsun")== true){
            $(".disamtsun").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#disamtsunday').val(sum.toFixed(2));
         }
         calculateTotalSum();
      });

      $('.adjamt').keyup(function() {
         /*var sum = 0;
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
         } */
         adjustAmount();
         calculateTotalSum();
      });

      $('.recamt').keyup(function() {
         var sum = 0;
         if($(this).hasClass("recamtmon")== true){
            $(".recamtmon").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
              $('#recamtmonday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamttue")== true){
            $(".recamttue").each(function() {
               sum += +this.value;
            });
              if(!isNaN(sum))
                $('#recamttuesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamtwed")== true){
            $(".recamtwed").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
              $('#recamtwednesday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamtthr")== true){
            $(".recamtthr").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
              $('#recamtthrusday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamtfri")== true){
            $(".recamtfri").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
               $('#recamtfriday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamtsat")== true){
            $(".recamtsat").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
                $('#recamtsaturday').val(sum.toFixed(2));
         }
         if($(this).hasClass("recamtsun")== true){
            $(".recamtsun").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
                $('#recamtsunday').val(sum.toFixed(2));
         }
         calculateTotalSum();
      });
   });
   /* tab down function */
   $(function() {
      $('tr.saleColumn').each(function() {
         $(this).find('td').each(function(i) {
            $(this).find('input').attr('tabindex', i+1);
            /* var index = $(this).find('input').attr('tabindex');
            $(this).find('input').after( "<p>"+index+"</p>" ); */
         });
      });
      $('tr.disColumn').each(function() {
         $(this).find('td').each(function(i) {
            $(this).find('input').attr('tabindex', i+1000);
         });

       });
       $('tr.adjColumn').each(function() {
         $(this).find('td').each(function(i) {
            $(this).find('input').attr('tabindex', i+10000);
         });

      });
      $('tr.recColumn').each(function() {
         $(this).find('td').each(function(i) {
            $(this).find('input').attr('tabindex', i+100000);
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
   function validateFloatKeyPress(el, evt)
   {
      var charCode = (evt.which) ? evt.which : event.keyCode;
      // alert(charCode);
      var number  = el.value.split('.');
      var number2 = el.value.split('+');
      if (charCode != 43 && charCode != 45 && charCode != 46 && charCode > 31 && (charCode < 48 || (charCode > 57))) {
          return false;
      }
      //just one dot
      if(number.length>1 && charCode == 46 && !(number.length>1)){
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
               //$('#modalData').modal('show');
                   $("#hidecomment").val('');
               var dayname = $('#'+day).text();
               var popWeekNum = $('#weekNoData').text();
               var popStoreNum = $('#storeNoData').text()
               $('#popupName').val(name);
               $('#popupUnit').val(unit);
               $('#popupDay').val(day);
               $('#popupNameId').val(nameId);
               $('#popupdayname').val(dayname);
               $('textarea#inputMessage').val(message);
               $('#popupweekNo').val($('#weekNoData').text());
               $('#popupStorNo').val($('#storeNoData').text());

              var popOverSettings = {
                  container: 'body',
                  title: 'Submit Your Comment <a href="#" class="close" data-dismiss="alert" >×</a>',
                  content: function() {
                     return $('#modalData').html();
                  },
                  trigger:'click',
                  placement:'auto',
                  viewport:{selector: 'body'},
                  html:true
               }
               $(".getlist").popover(popOverSettings).on("show.bs.popover", function(e) {
                     $("[rel=popover]").not(e.target).popover("destroy");
                     $(".popover").remove();
               }).on('shown.bs.popover', function() {
                  if(message == '') {
                       $.ajax({
                              type:       "POST",
                              dataType:   "text",
                              url:        "{{ url('/') }}/getComment",
                              data: {name:name,unit:unit,day:day,nameId:nameId,popdayname:dayname,popupweekNo:popWeekNum,popupStorNo:popStoreNum},
                              success: function(response)
                              {
                                 $("#hidecomment").val(response);
                              }
                          });
                  }
               }).on('shown.bs.popover', function() {
                  if(message == '') {
                     message = $("#hidecomment").val();
                     //alert($("#hidecomment").val());
                  }

                  $('#popupName').val(name);
                  $('#popupUnit').val(unit);
                  $('#popupDay').val(day);
                  $('#popupNameId').val(nameId);
                  $('#popupdayname').val(dayname);
                  $('textarea#inputMessage').val(message);
                  $('#popupweekNo').val($('#weekNoData').text());
                  $('#popupStorNo').val($('#storeNoData').text());
               });



   }

   function getComment(name,unit,day,nameId,popdayname,popupweekNo,popupStorNo) {

                    var comment = function () {
                         $.ajax({
                              type:       "POST",
                              dataType:   "text",
                              url:        "{{ url('/') }}/getComment",
                              data: {name:name,unit:unit,day:day,nameId:nameId,popdayname:popdayname,popupweekNo:popupweekNo,popupStorNo:popupStorNo},
                              success: function(response)
                              {
                                  comment = response;

                               }
                          });
                         return comment;
                  }();
                  // return comment;
   }

   $(document).on("click", ".popover .close" , function(){
         $(this).parents(".popover").popover('hide');
   });

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
               //alert(nameId); return false;
                     $.ajax({
                        type:       "POST",
                        dataType:   "text",
                        url:        "{{ url('/') }}/commentSave",
                        data: {message: message,name:name,unit:unit,day:day,nameId:nameId,popdayname:popdayname,popupweekNo:popupweekNo,popupStorNo:popupStorNo},
                        success: function(data)
                        {
                              //var value = $.trim(data);
                              var value = data;

                              value = $.trim(value);
                              if(value == 'complete'){
                                 name = name.replace(/ /g,'_');
                                 if(message == ''){
                                    $("#"+nameId+"_"+unit+"_"+day+"_"+name).css('color','green');
                                 }else{
                                    $("#"+nameId+"_"+unit+"_"+day+"_"+name).css('color','red');
                                 }
                                 $("#"+nameId+"_"+unit+"_"+day+"_"+name).attr("onclick","dataGet('"+nameId+"','"+unit+"','"+day+"','"+name+"','"+message+"')");

                                 //setTimeout(function(){location.reload(); });
                                 $('.message').show();
                                 $('.popover').popover('hide');

                                    // $(this).parents(".popover").popover('hide');
                                    /*setTimeout(function () {
                                       $('.popover').popover('hide');
                                       $('.message').hide();
                                    }, 1500); */
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

      function addNewCol(dep,inputValue){
         if($('.checkRows').length > 0 ){
		  swal("Validation Error!", "Please submit record first.", "warning");
         }else{
         $("."+dep+":last").after('<tr class="'+dep+' checkRows"><form ><input type="hidden" name="depName" id="depNameStore_'+inputValue+'" value="'+inputValue+'"><input type="hidden" name="weekNoData" id="depWeekStore_'+inputValue+'"><input type="hidden" name="storeNoData" id="depCalStore_'+inputValue+'"><th><div class="row form-group"><div class="col-md-12"><input type="text" name="fieldValue" id="fieldValue_'+inputValue+'" class="form-control" style="font-size: 12px;height: 28px;padding-left: 1px;" placeholder="Add Name"></div></div></th><td colspan="14"><ul class="inline scope"><li><label>Scope of Field</label></li><li><div class="radio"><label style="margin-right: 10px;margin-left: 10px;"><input type="radio" name="optradio" value="current">Current Week</label><label style="margin-right: 10px;margin-left: 10px;"><input type="radio" name="optradio" value="permanent">Permanent Week</label></div></li><li><button type="button" class="btn btn-primary submitBtn" depnameVaue="'+inputValue+'" id="submitDep_'+inputValue+'" style="padding: 5px 10px;font-size: 10px;margin-top: 4px;">SUBMIT</button></li></ul></td></form></tr>');
         $("#depWeekStore_"+inputValue).val($('#weekNoData').text());
         $("#depCalStore_"+inputValue).val($('#storeNoData').text());
         }
      }
      $(document).on("click", ".submitBtn" , function(){
         var departmentname = $(this).attr("depnameVaue");
         var radioValue = '';
         var inputvalue = $("#fieldValue_"+departmentname).val();
         if(inputvalue == ''){
			 swal("Validation Error!", "Please insert value for field.", "warning");
           }
         if($('input:radio[name=optradio]').is(':checked')){
         radioValue = $("input[name='optradio']:checked").val();
         }else{
			swal("Validation Error!", "Please select scope of field!", "warning");
         }
         var depName = departmentname;
         var weekNo = $('#depWeekStore_'+departmentname).val();
         var calWeek = $('#depCalStore_'+departmentname).val();
         if(inputvalue != '' && radioValue!= ''){

                     $.ajax({
                        type:       "POST",
                        dataType:   "text",
                        url:        "{{ url('/') }}/newFieldSaveSales",
                        data: {inputvalue: inputvalue,radioValue:radioValue,depName:depName,weekNo:weekNo,calWeek:calWeek},
                        success: function(data)
                        {
                              var value = data;
                              if(value == 'complete'){
								  swal("success!", "Record has been added successfully!", "success");

                                   setTimeout(function(){
                                    location.reload();
                                    });
                              }
                        }
                     });
         }
      });


      /* refresh summary dynamically starts */
      function unitSum(className){
         var sumSaleValue = 0;
         $("."+className).each(function(){
         sumSaleValue += +$(this).val();
         });
         $("."+className).html(sumSaleValue);
      }

      function amountSum(className)
      {
         var sumSaleValue = 0;
         $("."+className).each(function()
         {
            // SUM NUMBERS //
               var str = $(this).val().replace(/\++$/,'');
               calAry  = str.split("+");
               if(calAry.length > 1)
               {
                  calcult = eval(calAry.join("+"));
                  $(this).val(calcult);
               }

               calAry  = str.split("-");
               if(calAry.length > 1)
               {
                  calcult = eval(calAry.join("-"));
                  $(this).val(calcult);
               }

            // SUM NUMBERS //

            sumSaleValue += +$(this).val();
         });

         $("."+className).html('$'+sumSaleValue.toFixed(2));
         $("."+className+1).val(sumSaleValue);


         var sumSale = 0;

         $(".saleSum").each(function(){
            sumSale += +$(this).val();
         });

         var SaleT = numberWithCommas(sumSale.toFixed(2));
         $("#saletotal").text("$"+SaleT);

         sumamount();
      }

      function disAmountSum(className){

         var sumDisValue = 0;
         var calcult = 0;
         $("."+className).each(function(){
           var str = $(this).val().replace(/\++$/,'');
           calAry  = str.split("+");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("+"));
              $(this).val(calcult);
               console.log(calcult,'calcult');

           }

           calAry  = str.split("-");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("-"));
              $(this).val(calcult);
           }
         sumDisValue += +calcult;
         });

         //$("."+className).val('('+sumDisValue.toFixed(2)+')');
         $("."+className+"_77").html('$('+sumDisValue.toFixed(2)+')');
         $("."+className+3).val(sumDisValue);
          var sumdis = 0;
         $(".disSum").each(function(){
         sumdis += +$(this).val(); });
         var DisT = numberWithCommas(sumdis.toFixed(2));
         $("#distotal").text("($"+DisT+")");
             disAmount();

      }
      function adjAmountSum(className){
         var sumAdjValue = 0;
         $("."+className).each(function(){
           var str = $(this).val().replace(/\++$/,'');
           calAry  = str.split("+");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("+"));
              $(this).val(calcult);
               console.log(calcult,'calcult');

           }

           calAry  = str.split("-");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("-"));
              $(this).val(calcult);
           }
         sumAdjValue += +$(this).val();
         });

         if(className == 'Employee_Advance_8' || className == 'Employee_Loan_8' || className == 'Paid_Out_8') {
            $("."+className).html('($'+sumAdjValue.toFixed(2)+')');
         } else {
            $("."+className).html('$'+sumAdjValue.toFixed(2));
         }
         $("."+className+4).val(sumAdjValue);

         var sumadj = 0;
         var sumAttrName = 0;
         $(".adjSum").each(function(){
            var idName = this.id;
            idName = idName.replace("adj_","");
            if(idName == 'Employee_Advance' || idName == 'Employee_Loan' || idName == 'Paid_Out') {
               sumAttrName += +$(this).val();
            } else {
               sumadj += +$(this).val();
            }
         });
         sumadj = parseFloat(sumadj) - parseFloat(sumAttrName);
         var AdjT = numberWithCommas(sumadj.toFixed(2));
         $("#adjtotal").text("$"+AdjT);
         adjustAmount();
      }
      function recAmountSum(className){
         var sumRecptValue = 0;
         $("."+className).each(function(){
           var str = $(this).val().replace(/\++$/,'');
           calAry  = str.split("+");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("+"));
              $(this).val(calcult);

           }

           calAry  = str.split("-");
           if(calAry.length > 1)
           {
              calcult = eval(calAry.join("-"));
              $(this).val(calcult);
           }
         sumRecptValue += +$(this).val();
         });

         $("."+className).html('$'+sumRecptValue.toFixed(2));
         $("."+className+5).val(sumRecptValue);

         var sumrec = 0;
         $(".recSum").each(function(){
         sumrec += +$(this).val(); });
         var RecT = numberWithCommas(sumrec.toFixed(2));
         $("#rectotal").text("$"+RecT);
         recAmount();
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
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtmonday').val() == '' ? '0' : $('#disamtmonday').val();

         var oversaturday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         //$('#oversaturday').val(oversaturday.toFixed(2));
         if(oversaturday.toFixed(2) >= 0){
            $('#oversaturday').val(oversaturday.toFixed(2)).css("color", "black");
            }else{
                if(!isNaN(oversaturday.toFixed(2)))
                      $('#oversaturday').val('('+(oversaturday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* sat over value end*/
         /* sun over value start*/
         var sat = tax = 0;
         $(".sundaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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

         var recmt =  $('#recamttuesday').val() == '' ? '0' : $('#recamttuesday').val();  //4948.36
         var salemt = $('#sumamttuesday').val() == '' ? '0' : $('#sumamttuesday').val(); // 4872.29
         var stax = isNaN(tax) ? '0' : tax; //277.04
         var otherpart = isNaN(sat) ? '0' : sat;
         var disamounttax = $('#disamttuesday').val() == '' ? '0' : $('#disamttuesday').val();

         var oversunday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         if(oversunday.toFixed(2) >= 0){
            $('#oversunday').val(oversunday.toFixed(2)).css("color", "black");;
            }else{
                if(!isNaN(oversunday.toFixed(2)))
                      $('#oversunday').val('('+(oversunday*(-1)).toFixed(2)+')').css("color", "red");
         }
         /* sun over value end*/
         /* mon over value start*/
         var sat = tax = 0;
         $(".mondaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtwednesday').val() == '' ? '0' : $('#disamtwednesday').val();
         var overmonday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         //$('#overmonday').val(overmonday.toFixed(2));
         if(overmonday.toFixed(2) >= 0){
            $('#overmonday').val(overmonday.toFixed(2)).css("color", "black");;
            }else{
              if(!isNaN(overmonday.toFixed(2)))
                  $('#overmonday').val('('+(overmonday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* mon over value end*/
         /* tue over value start*/
         var sat = tax = 0;
         $(".tuesdaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtthrusday').val() == '' ? '0' : $('#disamtthrusday').val();
         var overtuesday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         //$('#overtuesday').val(overtuesday.toFixed(2));
         if(overtuesday.toFixed(2) >= 0){
            $('#overtuesday').val(overtuesday.toFixed(2)).css("color", "black");;
            }else{
              if(!isNaN(overtuesday.toFixed(2)))
                  $('#overtuesday').val('('+(overtuesday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* tue over value end*/
         /* wed over value start*/
         var sat = tax = 0;
         $(".wednesdaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtfriday').val() == '' ? '0' : $('#disamtfriday').val();
         var overwednesday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         //$('#overwednesday').val(overwednesday.toFixed(2));
         if(overwednesday.toFixed(2) >= 0){
            $('#overwednesday').val(overwednesday.toFixed(2)).css("color", "black");;
            }else{
                if(!isNaN(overwednesday.toFixed(2)))
                      $('#overwednesday').val('('+(overwednesday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* wed over value end*/
         /* thr over value start*/
         var sat = tax = 0;
         $(".thrusdaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtsaturday').val() == '' ? '0' : $('#disamtsaturday').val();
         var overthrusday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);
         //$('#overthrusday').val(overthrusday.toFixed(2));
         if(overthrusday.toFixed(2) >= 0){
            $('#overthrusday').val(overthrusday.toFixed(2)).css("color", "black");;
            }else{
                if(!isNaN(overthrusday.toFixed(2)))
                    $('#overthrusday').val('('+(overthrusday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* thr over value end*/
         /* fri over value start*/
         var sat = tax = 0;
         $(".fridaydate").each(function(){
            if($(this).attr('name') == 'Sales Tax'){
              var saletax = $(this).val();
              tax += +$(this).val();
            }
            /* if($(this).attr('name') == 'Refund'){
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
            } */
            if($(this).attr('name') == 'Employee Advance'){
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
         var disamounttax = $('#disamtsunday').val() == '' ? '0' : $('#disamtsunday').val();
         var overfriday = (parseFloat(recmt) - (parseFloat(salemt) +  parseFloat(stax))) + parseFloat(otherpart) + parseFloat(disamounttax);

         //$('#overfriday').val(overfriday.toFixed(2));
         if(overfriday.toFixed(2) >= 0){
            $('#overfriday').val(overfriday.toFixed(2)).css("color", "black");;
            }else{
              if(!isNaN(overfriday.toFixed(2)))
                    $('#overfriday').val('('+(overfriday*(-1)).toFixed(2)+')').css("color", "red");
         }

         /* fri over value end*/

         //var finalover = parseFloat(oversaturday) + parseFloat(oversunday) + parseFloat(overmonday) + parseFloat(overtuesday) + parseFloat(overwednesday) + parseFloat(overthrusday) + parseFloat(overfriday);
         var finalover = parseFloat(oversaturday) + parseFloat(overmonday) + parseFloat(overtuesday) + parseFloat(overwednesday) + parseFloat(overthrusday) + parseFloat(overfriday);

         var FinalT = numberWithCommas(finalover.toFixed(2));

         if(FinalT >= 0){
            $('#finalovertotal').text("$"+FinalT);
            }else{
               var finalVal = finalover * (-1) ;
               FinalT =  numberWithCommas(finalVal.toFixed(2));
            $('#finalovertotal').text("$("+FinalT+')').css("color", "red");
         }

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
               if(!isNaN(sum))
                    $('#sumamtmonday').val(sum.toFixed(2));

            sum = 0;
               $(".sumamttue").each(function() {
                  sum += +this.value;
               });
               if(!isNaN(sum))
                    $('#sumamttuesday').val(sum.toFixed(2));

            sum = 0;
               $(".sumamtwed").each(function() {
                  sum += +this.value;
               });
               if(!isNaN(sum))
                    $('#sumamtwednesday').val(sum.toFixed(2));

            sum = 0;
               $(".sumamtthr").each(function() {
                  sum += +this.value;
               });
               if(!isNaN(sum)){
                    $('#sumamtthrusday').val(sum.toFixed(2));
               }


            sum = 0;
               $(".sumamtfri").each(function() {
                  sum += +this.value;
               });
              if(!isNaN(sum))
                    $('#sumamtfriday').val(sum.toFixed(2));

            sum = 0;
               $(".sumamtsat").each(function() {
                  sum += +this.value;
               });
               if(!isNaN(sum))
                    $('#sumamtsaturday').val(sum.toFixed(2));

            sum = 0;
            $(".sumamtsun").each(function() {
               sum += +this.value;
            });
            if(!isNaN(sum))
                  $('#sumamtsunday').val(sum.toFixed(2));
      }

      function disAmount() {
            var sum = 0;
            $(".disamtmon").each(function() {
                  sum += +this.value;
               });

            if(sum == 0){
            $('#disamtmonday').val(sum.toFixed(2)).css("color", "black");
            }else{
            if(!isNaN(sum))
                $('#disamtmonday').val(sum.toFixed(2));
            }
            sum = 0;
            $(".disamttue").each(function() {
               sum += +this.value;
            });
            //$('#disamttuesday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamttuesday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                    $('#disamttuesday').val(sum.toFixed(2));
            }

             sum = 0;
               $(".disamtwed").each(function() {
                  sum += +this.value;
               });
            //$('#disamtwednesday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamtwednesday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                    $('#disamtwednesday').val(sum.toFixed(2));
            }

             sum = 0;
               $(".disamtthr").each(function() {
                  sum += +this.value;
               });
            //$('#disamtthrusday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamtthrusday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                    $('#disamtthrusday').val(sum.toFixed(2));
            }

             sum = 0;
               $(".disamtfri").each(function() {
                  sum += +this.value;
               });
            //$('#disamtfriday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamtfriday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                  $('#disamtfriday').val(sum.toFixed(2));
            }

             sum = 0;
               $(".disamtsat").each(function() {
                  sum += +this.value;
               });
            //$('#disamtsaturday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamtsaturday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                  $('#disamtsaturday').val(sum.toFixed(2));
            }

             sum = 0;
               $(".disamtsun").each(function() {
                  sum += +this.value;
               });
            //$('#disamtsunday').val(sum.toFixed(2));
            if(sum == 0){
            $('#disamtsunday').val(sum.toFixed(2)).css("color", "black");
            }else{
              if(!isNaN(sum))
                  $('#disamtsunday').val(sum.toFixed(2));
            }

      }

      function adjustAmount() {
            var sum = 0;
            var sumAttrName = 0;
            $(".adjamtmon").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamtmonday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamttue").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamttuesday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamtwed").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamtwednesday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamtthr").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });

            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamtthrusday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamtfri").each(function() {
               var attrName = $(this).attr('name');
               if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                  sumAttrName += +this.value;
               } else {
                  sum += +this.value;
               }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamtfriday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamtsat").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                  $('#adjamtsaturday').val(sum.toFixed(2));

            sum = 0;
            sumAttrName = 0;
            $(".adjamtsun").each(function() {
                  var attrName = $(this).attr('name');
                  if(attrName == 'Employee Advance' || attrName == 'Employee Loan' || attrName == 'Paid Out') {
                     sumAttrName += +this.value;
                  } else {
                     sum += +this.value;
                  }
            });
            sum = parseFloat(sum) - parseFloat(sumAttrName);
             if(!isNaN(sum))
                $('#adjamtsunday').val(sum.toFixed(2));
      }

      function recAmount() {
            var sum = 0;

            $(".recamtmon").each(function() {
               sum += +this.value;
            });
              if(!isNaN(sum))
                  $('#recamtmonday').val(sum.toFixed(2));

            sum = 0;
               $(".recamttue").each(function() {
                  sum += +this.value;
               });
                 if(!isNaN(sum))
                      $('#recamttuesday').val(sum.toFixed(2));

            sum = 0;
               $(".recamtwed").each(function() {
                  sum += +this.value;
               });
                if(!isNaN(sum))
                    $('#recamtwednesday').val(sum.toFixed(2));

            sum = 0;
               $(".recamtthr").each(function() {
                  sum += +this.value;
               });
                if(!isNaN(sum))
                    $('#recamtthrusday').val(sum.toFixed(2));

            sum = 0;
               $(".recamtfri").each(function() {
                  sum += +this.value;
               });
            if(!isNaN(sum))
               $('#recamtfriday').val(sum.toFixed(2));

            sum = 0;
               $(".recamtsat").each(function() {
                  sum += +this.value;
               });
                if(!isNaN(sum))
                    $('#recamtsaturday').val(sum.toFixed(2));

            sum = 0;
               $(".recamtsun").each(function() {
                  sum += +this.value;
               });
               if(!isNaN(sum))
                    $('#recamtsunday').val(sum.toFixed(2));
      }



      /* $(document).ready(function() {
       $('#abcd').DataTable();
   } ); */

</script>
<script type="text/javascript">
   function gotoLaborRep()
   {
      var weekStore = $("#storeNoData").html();
      weekStore = ((weekStore % 2) == 0)?weekStore-1:weekStore;
      window.open('{{ url("/labor_report") }}/'+weekStore);
   }
</script>
<script type="text/javascript">
(function () {
   var table = document.getElementById('popover-viewport');
   var viewbar = document.querySelector('.kv-ledger-viewbar');
   var focusPanel = document.getElementById('kv-daily-focus-panel');
   if (!table || !viewbar || !focusPanel) return;
   var dayStrip = viewbar.querySelector('.kv-ledger-day-strip');
   var modeButtons = viewbar.querySelectorAll('[data-ledger-view]');
   var dayHeaders = Array.prototype.slice.call(table.querySelectorAll('thead tr:first-child th[id^="day"]'));
   var selectedStart = dayHeaders.length ? dayHeaders[0].cellIndex : 1;
   var focused = true;
   var movedNodes = [];
   var selectedDate = '';
   var selectedDayName = '';
   var totalSuffixes = {1:'saturday', 3:'monday', 5:'tuesday', 7:'wednesday', 9:'thrusday', 11:'friday'};
   var dayNumbers = {1:'day1', 3:'day3', 5:'day4', 7:'day5', 9:'day6', 11:'day7'};

   function updateDayStatuses() {
      Array.prototype.forEach.call(dayStrip.querySelectorAll('[data-start-column]'), function (button) {
         var start = parseInt(button.getAttribute('data-start-column'), 10);
         var hasValues = false;
         var dayNo = dayNumbers[start];
         var suffix = totalSuffixes[start];
         var amountSource = suffix ? document.getElementById('sumamt' + suffix) : null;
         var amount = button.querySelector('.kv-ledger-day-amount');
         if (amount && amountSource) amount.textContent = amountSource.value || '$0.00';
         var dayInputs = dayNo ? document.querySelectorAll('#popover-viewport input[dayno="' + dayNo + '"], #kv-daily-focus-panel input[dayno="' + dayNo + '"]') : [];
         Array.prototype.forEach.call(dayInputs, function (input) {
            if (input.type !== 'hidden' && input.value.trim() !== '') hasValues = true;
         });
         var status = button.querySelector('.kv-ledger-day-state');
         if (status) status.textContent = hasValues ? 'Recorded' : 'Ready';
      });
   }

   function selectDay(start, button) {
      selectedStart = start;
      Array.prototype.forEach.call(dayStrip.querySelectorAll('.kv-ledger-day'), function (dayButton) {
         dayButton.classList.toggle('is-selected', dayButton === button);
         dayButton.setAttribute('aria-pressed', dayButton === button ? 'true' : 'false');
      });
      selectedDayName = button.querySelector('.kv-ledger-day-name').textContent;
      selectedDate = button.querySelector('.kv-ledger-day-date').textContent;
      var uploadDate = document.getElementById('dailyEntryPdfDate');
      var uploadDateLabel = document.getElementById('dailyEntryPdfDateLabel');
      if (uploadDate) uploadDate.value = selectedDate;
      if (uploadDateLabel) uploadDateLabel.textContent = selectedDate;
      if (focused) renderDailyFocus();
   }

   var nextColumn = 1;
   dayHeaders.forEach(function (header) {
      var startColumn = nextColumn;
      nextColumn += parseInt(header.colSpan, 10) || 2;
      var parts = header.textContent.trim().split(/\s+/);
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'kv-ledger-day';
      button.setAttribute('data-start-column', startColumn);
      header.setAttribute('data-start-column', startColumn);
      button.setAttribute('aria-pressed', 'false');
      button.innerHTML = '<strong class="kv-ledger-day-name"></strong><span class="kv-ledger-day-date"></span><span class="kv-ledger-day-meta"><small class="kv-ledger-day-state">Ready</small><b class="kv-ledger-day-amount">$0.00</b></span>';
      button.querySelector('.kv-ledger-day-name').textContent = parts.shift() || 'Day';
      button.querySelector('.kv-ledger-day-date').textContent = parts.join(' ');
      button.addEventListener('click', function () { selectDay(startColumn, button); });
      dayStrip.appendChild(button);
   });

   function restoreMovedNodes() {
      movedNodes.forEach(function (entry) {
         if (entry.marker.parentNode) {
            entry.marker.parentNode.insertBefore(entry.node, entry.marker);
            entry.marker.parentNode.removeChild(entry.marker);
         }
      });
      movedNodes = [];
   }

   function moveCellContent(cell, target) {
      Array.prototype.slice.call(cell.childNodes).forEach(function (node) {
         if (node.nodeType === 1 && node.tagName === 'INPUT' && node.type === 'hidden') return;
         var marker = document.createComment('daily-focus-position');
         cell.insertBefore(marker, node);
         target.appendChild(node);
         movedNodes.push({node:node, marker:marker});
      });
   }

   function updateReconciliation() {
      var suffix = totalSuffixes[selectedStart];
      if (!suffix) return;
      var ids = {sales:'sumamt' + suffix, receipts:'recamt' + suffix, over:'over' + suffix};
      Object.keys(ids).forEach(function (key) {
         var source = document.getElementById(ids[key]);
         var target = focusPanel.querySelector('[data-day-total="' + key + '"]');
         if (source && target) target.textContent = source.value || '$0.00';
      });
   }

   function renderDailyFocus() {
      restoreMovedNodes();
      focusPanel.querySelector('#kv-daily-focus-title').textContent = selectedDayName + ' Ledger';
      focusPanel.querySelector('.kv-daily-focus-date-icon').textContent = selectedDayName.slice(0, 3);
      focusPanel.querySelector('.kv-daily-focus-heading p').textContent = 'Daily Operational Input Matrix · ' + selectedDate;

      var groups = [
         {key:'sale', rowClass:'saleColumn'},
         {key:'dis', rowClass:'disColumn'},
         {key:'adj', rowClass:'adjColumn'},
         {key:'rec', rowClass:'recColumn'}
      ];
      groups.forEach(function (group) {
         var section = focusPanel.querySelector('[data-focus-group="' + group.key + '"] .kv-focus-card-grid');
         section.innerHTML = '';
         Array.prototype.forEach.call(table.tBodies[0].querySelectorAll('tr.' + group.rowClass), function (row) {
            if (row.cells.length <= selectedStart + 1) return;
            var controls = [];
            [selectedStart, selectedStart + 1].forEach(function (index) {
               var cell = row.cells[index];
               if (!cell) return;
               var input = cell.querySelector('input:not([type="hidden"])');
               if (input) controls.push({cell:cell, input:input});
            });
            if (!controls.length) return;

            var card = document.createElement('article');
            card.className = 'kv-daily-entry-card';
            var title = document.createElement('h4');
            title.textContent = row.cells[0].textContent.trim();
            card.appendChild(title);
            var fields = document.createElement('div');
            fields.className = 'kv-daily-entry-fields';
            if (controls.length === 1) fields.classList.add('is-single-field');
            controls.forEach(function (control) {
               var field = document.createElement('label');
               field.className = 'kv-daily-entry-field';
               var fieldTitle = document.createElement('small');
               var unit = control.input.getAttribute('unit');
               fieldTitle.textContent = unit === 'units' ? 'Units' : 'Amount';
               var inputWrap = document.createElement('span');
               inputWrap.className = 'kv-daily-entry-input';
               moveCellContent(control.cell, inputWrap);
               field.appendChild(fieldTitle);
               field.appendChild(inputWrap);
               fields.appendChild(field);
            });
            card.appendChild(fields);
            section.appendChild(card);
         });
      });
      updateReconciliation();
   }

   function applyMode() {
      restoreMovedNodes();
      table.closest('.widget-content').classList.toggle('is-daily-focus', focused);
      table.classList.toggle('is-daily-focus', focused);
      focusPanel.hidden = !focused;
      table.closest('.widget.form-layout').classList.toggle('is-matrix-hidden', focused);
      dayStrip.hidden = !focused;
      Array.prototype.forEach.call(modeButtons, function (button) {
         var active = (button.getAttribute('data-ledger-view') === 'daily') === focused;
         button.classList.toggle('is-active', active);
         button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      if (focused) renderDailyFocus();
   }

   Array.prototype.forEach.call(modeButtons, function (button) {
      button.addEventListener('click', function () {
         focused = button.getAttribute('data-ledger-view') === 'daily';
         applyMode();
      });
   });

   updateDayStatuses();
   var firstDay = dayStrip.querySelector('[data-start-column]');
   if (firstDay) selectDay(parseInt(firstDay.getAttribute('data-start-column'), 10), firstDay);
   applyMode();
   table.addEventListener('input', updateDayStatuses);
   table.closest('.widget-content').addEventListener('input', function () {
      window.setTimeout(updateReconciliation, 50);
   });
   focusPanel.addEventListener('input', function () {
      updateDayStatuses();
      window.setTimeout(updateReconciliation, 50);
   });
}());
</script>
@endsection
