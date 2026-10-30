@extends('layouts.layout')

@section('content')

@if(Session::has('message'))
   <div class="alert alert-success">{{ Session::get('message') }}</div>
@endif
@if($errors->any())
   <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<style type="text/css">
   .main-content {
      padding-bottom: 0!important;
   }.widget {
      margin-bottom: 0!important;
   }.widget-content {
      padding: .5em!important;
   }.basic-table>tbody>tr>td {
      padding: 1px 4px;
      font-size: 12px;
      font-family: calibri;
   }.basic-table>tbody>tr>th {
      padding: 1px 4px;
      font-size: 12px;
      font-family: calibri;
   }.background-pink {
      background: #ffcc99;
   }.background-blue {
      background: #c0c0c0;
   }.background-yellow {
      background: #ffff00;
   }
   table.table-style>tbody>tr>td>input[type="text"] {
      background: none!important;
      box-shadow: none!important;
      border: none!important;
      width: 60px!important;
      height: 100%!important;
      padding: 5px!important;
   }

   table.table-style>tbody>tr>td {
      padding: 0!important;
   }.highlight-field {
      background: #f6c622;
      color: #382f2f;
      padding: 5px 10px;
      font-size: 13px;
   }.highlight-field textarea {
      height: 70px;
   }.highlight-field span {
      font-weight: bold;
      color: #382f2f;
   }.lighted-div {
      background: #382f2f;
      padding: 10px 10px;
      color: #fff;
   }.lighted-div input {
      height: 21px!important;
   }.highlight-field input {
      height: 21px!important;
   }.highlight-field .form-group {
      margin-bottom: 5px!important;
   }.blank_row {
      height: 10px;
   }.filter-container {
      background: #382f2f;
      padding: 6px;
      color: #fff;
   }.day-filter {
      background: #000;
      padding: 3px;
      margin-left: 5px;
   }.table-scroll {
      overflow-x: scroll;
   }
   .rmbtn{
      font-size: 10px;
      height: 17px;
      line-height: 13px;
      margin-left:-1px;
      color:red;
      background-color:transparent;
      padding: 0;
   }
   /*.bootstrap-datetimepicker-widget tr:hover {
      background-color: #808080;
   }*/
</style>
<?php $ttlsalary = 0; ?>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<section id="dashboard-page" class="wrap kv-labor-report">
   <div class="container-fluid">
      <div class="row">
			<div class="col-sm-12">
				<div class="page-heading kv-page-hero kv-reports-hero">
               <h3 class="heading">
                  Bi-Weekly Labor Report
                     {!! Form::model(null, array('route' => ['laboursection.store'], 'method' => 'POST')) !!}
                     <ul class="pull-right inline quick-info">
                        <li>
                        <div class="input-group" id="">
                              <input  class="form-control" id='weeklyDatePicker' name="datefilter" autocomplete="off"/>
                           </div>
                        </li>
                        <li>
                           <div class="input-group" >
                              <input class="v-btn v-btn-primary" type="submit" name="submit" id="weekDatapick" value="FILTER" onClick= "dateData()">
                           </div>
                        </li>
                        <li>
                           <button type="button" class="v-btn v-btn-primary" data-toggle="modal" data-target="#laborExcelUploadModal"><i class="fa fa-upload"></i> UPLOAD EXCEL</button>
                        </li>
                        <li>
                           <div class="input-group" >
                              <a class="v-btn v-btn-primary" href="{{ url('/laboursection') }}"><i class="fa fa-arrow-left " style="margin-right: 5px"> </i>BACK</a>
                           </div>
                        </li>
                     </ul>
                  </form>
               </h3>
            </div>
			</div>
		</div>
   <div class="modal fade kv-location-modal" id="laborExcelUploadModal" tabindex="-1" role="dialog" aria-labelledby="laborExcelUploadModalTitle">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <form method="POST" action="{{ url('/uploads/labor-report-excel') }}" enctype="multipart/form-data">
               {{ csrf_field() }}
               <input type="hidden" name="start_week" value="{{ $weeksStoreOne }}">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="laborExcelUploadModalTitle">Upload Labor Report Excel</h4>
               </div>
               <div class="modal-body">
                  <p><strong>Selected period:</strong> {{ $sdate }} to {{ $edate }} (store weeks {{ $weeksStoreOne }} and {{ $weeksStoreTwo }}).</p>
                  <p>The Labor tab is selected automatically, including in a workbook with other tabs. A single labor sheet is also supported. Review employee mappings, rates and both weeks before saving.</p>
                  <div class="kv-location-fields">
                     <div class="form-group">
                        <label for="laborExcelFile">Excel file</label>
                        <input class="form-control" type="file" name="file" id="laborExcelFile" accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <small class="help-block">Excel files (.xls or .xlsx) only, up to 20 MB.</small>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn kv-location-cancel" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn kv-location-submit">Preview Excel</button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <input type="hidden" id="startDate" value="02-03-2018">
   <div class="row">
      <div class="col-sm-12">
         <div class="widget form-layout">
            <div class="widget-content padding">
               <div class="row">
                  <div class="col-md-12">
                     <div class="table-scroll">

                        <input type="hidden" id="weekstart" value="{{ $weeksStoreOne }}">
                        <input type="hidden" id="weekend" value="{{ $weeksStoreTwo }}">
                        <table class="table table-bordered basic-table table-style kv-labor-table">
                           <tr>
                              <th></th>
                              <th></th>
                              <th colspan="6" style="background: #ffcc99;text-align: center;"><span id="weekStoreOne">WEEK# {{ $weeksStoreOne }} </span></th>
                              <th colspan="6" style="background: #ffcc99;text-align: center;"><span id="weekStoreTwo">WEEK# {{ $weeksStoreTwo }}   </span></th>
                              <th colspan="5" style="background: #ffcc99;text-align: center;">TOTALS</th>
                              <th></th>
                           </tr>
                           <tr>

                              <th style="white-space:nowrap;text-align:center;">EMPLOYEE NAME</th>
                              <th style="white-space:nowrap;text-align:center;">PAY RATE</th>

                              @foreach($payrollCategory as $category)
                                 <input type="hidden" id="multipl-{{ $category->id }}" value="{{ $category->pay_rate_multiplier }}">
                                 <th style="white-space:nowrap;text-align:center;" class="background-pink">{{ strtoupper($category->name) }}</th>
                              @endforeach
                              <th style="white-space:nowrap;text-align:center;" class="background-pink">TOTAL</th>

                              @foreach($payrollCategory as $category)
                                 <th style="white-space:nowrap;text-align:center;" class="background-blue">{{ strtoupper($category->name) }}</th>
                              @endforeach
                              <th style="white-space:nowrap;text-align:center;" class="background-blue">TOTAL</th>

                              @foreach($payrollCategory as $category)
                                 <th style="white-space:nowrap;text-align:center;" class="background-yellow">TOTAL / {{ strtoupper($category->name) }}</th>
                              @endforeach
                              <th style="white-space:nowrap;text-align:center;">GRAND TOTALS</th>

                           </tr>

                           <?php $head = 0; ?>
                           @foreach($payrollDepartment as $department)

                              <tr>
                                 <td colspan="23" style="background-color:#dddddd82; font-weight:600; font-size:12px;" align="center">{{ $department->name }}
                                    <button class="v-btn v-btn-primary pull-left btn-xs" title="Add Employee For {{ $department->name }}" style="font-size: 10px; height: 17px;line-height: 13px;margin-left:-1px;" data-toggle="modal" data-target="#addExistingEmp" depName="{{ $department->name }}" depId="{{ $department->id }}"><i class="fa fa-plus"></i></button>
                                 </td>
                              </tr>

                              @foreach($payrollDepartmentEmps[$department->id] as $employee)

                              <input type="hidden" id="rate-{{ $employee->id }}" value="{{ $employee->rateperhour }}">
                              <tr>

                                 <td class="text-center" style="vertical-align:middle;">
                                    <?php if(isset($empsLabor[$department->id][$employee->id]) && ($headerInfo !== false)){ ?>
                                       <a class="v-btn pull-left btn-sm rmbtn" href="{{ route('removeEmployee', ['emp' => $employee->id, 'dep' => $department->id, 'hd' => $headerInfo->id]) }}"><i class="fa fa-remove"></i></a>
                                    <?php }else{ ?>
                                       <a class="v-btn pull-left btn-sm rmbtn" href="{{ route('hideEmployee', ['emp' => $employee->id]) }}"><i class="fa fa-remove"></i></a>
                                    <?php } ?>

                                    <span style="cursor: pointer;" onclick="editEmp({{ $employee->id }})">
                                       {{ $employee->name }}
                                    </span>

                                 </td>
                                 <td class="text-center" style="vertical-align:middle;">${{ $employee->rateperhour }}</td>

                                 @foreach($payrollCategory as $category)

                                    <td class="background-pink">
                                       <?php
                                       $readonly = (strtolower(trim($category->name)) == 'salary')?'readonly':'';
                                       $emplabor = (isset($empsLabor[$department->id][$employee->id][$weeksStoreOne][$category->id]))?sprintf('%0.2f', $empsLabor[$department->id][$employee->id][$weeksStoreOne][$category->id]):'';
                                      $value    = (strtolower(trim($category->name)) == 'salary')?"$".(number_format((Float)$employee->salary, 2)):$emplabor; ?>

                                       <input type="text" class="currency" weeknum="{{ $weeksStoreOne }}" cat="{{ $category->id }}" dep="{{ $department->id }}" emp="{{ $employee->id }}" onchange="addNew(this)" onkeypress="return validateFloatKeyPress(this, event);" {{ $readonly }} value="{{ $value }}">
                                    </td>

                                 @endforeach
                                 <td class="background-pink">
                                    <input type="text" id="ttl-{{ $department->id }}-{{ $employee->id }}-{{ $weeksStoreOne }}" readonly class="wkttl-{{ $employee->id }}">
                                 </td>

                                 @foreach($payrollCategory as $category)
                                    <td class="background-blue">
                                       <?php
                                       $readonly = (strtolower(trim($category->name)) == 'salary')?'readonly':'';
                                       $emplabor = (isset($empsLabor[$department->id][$employee->id][$weeksStoreTwo][$category->id]))?sprintf('%0.2f', $empsLabor[$department->id][$employee->id][$weeksStoreTwo][$category->id]):'';
                                    $value    = (strtolower(trim($category->name)) == 'salary')?"$".(number_format((Float)$employee->salary, 2)):$emplabor; ?>

                                       <input type="text" class="currency" weeknum="{{ $weeksStoreTwo }}" cat="{{ $category->id }}" dep="{{ $department->id }}" emp="{{ $employee->id }}" onchange="addNew(this)" onkeypress="return validateFloatKeyPress(this, event);" {{ $readonly }} value="{{ $value }}">

                                    </td>
                                 @endforeach
                                 <td class="background-blue">
                                    <input type="text" id="ttl-{{ $department->id }}-{{ $employee->id }}-{{ $weeksStoreTwo }}" readonly class="wkttl-{{ $employee->id }}">
                                 </td>

                                 @foreach($payrollCategory as $category)
                                    <?php $value = (strtolower(trim($category->name)) == 'salary')?'$'.number_format(((Float)$employee->salary*2), 2):''; ?>
                                    <td class="background-yellow yellow-{{ $category->id }}" id="ttl-{{ $employee->id }}-{{ $category->id }}"> {{ $value }} </td>
                                 @endforeach
                                 <td id="gdttl-{{ $employee->id }}" class="gdttl"> &nbsp; </td>

                              </tr>
                              <?php
                              // echo "<br />Emp: ".$employee->salary." : ";
                              $ttlsalary += (Float)$employee->salary; ?>
                              @endforeach

                           @endforeach


                           <tr style="font-weight: bold;">
                              <td rowspan="2" style="text-align: center;">
                                 TOTAL ALL
                              </td>
                              <td></td>
                              @foreach($payrollCategory as $category)
                                 <?php
                                 $value = (strtolower(trim($category->name)) == 'salary')?'$'.number_format((Float)$ttlsalary, 2):'';
                                 $id    = (strtolower(trim($category->name)) == 'salary')?'':'ttl-'.$weeksStoreOne.'-'.$category->id; ?>
                                 <td id="{{ $id }}">{{ $value }}</td>
                              @endforeach
                              <td id="wk{{ $weeksStoreOne }}gttl">&nbsp;</td>

                              @foreach($payrollCategory as $category)
                                 <?php
                                 $value = (strtolower(trim($category->name)) == 'salary')?'$'.number_format((Float)$ttlsalary, 2):'';
                                 $id    = (strtolower(trim($category->name)) == 'salary')?'':'ttl-'.$weeksStoreTwo.'-'.$category->id; ?>
                                 <td id="{{ $id }}">{{ $value }} </td>
                              @endforeach
                              <td id="wk{{ $weeksStoreTwo }}gttl">&nbsp;</td>

                              @foreach($payrollCategory as $category)
                                 <td id="yellow-ttl-{{ $category->id }}"> &nbsp; </td>
                              @endforeach
                              <td id="ttlgdttl"> &nbsp; </td>
                           </tr>

                           <tr style="font-weight: bold;">
                              <td> </td>

                              @foreach($payrollCategory as $category)
                                 <td>{{ strtoupper($category->name) }}</td>
                              @endforeach
                              <td>W/K TOTAL</td>

                              @foreach($payrollCategory as $category)
                                 <td>{{ strtoupper($category->name) }}</td>
                              @endforeach
                              <td>W/K TOTAL</td>

                              @foreach($payrollCategory as $category)
                                 <td>{{ strtoupper($category->name) }} TOTAL</td>
                              @endforeach
                              <td>TOTAL </td>

                           </tr>

                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<div class="modal fade kv-labor-create-employee-modal" id="createNewEmp" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
         {!! Form::model(null, array('route' => ['laboursection.store'], 'method' => 'POST', 'id' => 'pmt_frm')) !!}

         <?php if($headerInfo){ ?>
         <input type="hidden" name="header_id" value="{{ $headerInfo->id }}">
         <input type="hidden" name="cat" value="{{ $payrollCategory[0]->id }}">
         <input type="hidden" name="weeknum" value="{{ $weeksStoreOne }}">
         <?php } ?>

         <input type="hidden" name="department" class="selectEmpDep">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal">&times;</button>
               <div class="kv-create-employee-modal-title">
                  <span class="kv-create-employee-modal-icon"><i class="fa fa-user-plus"></i></span>
                  <div>
                     <h4 class="modal-title">Add New Employee</h4>
                  </div>
               </div>
            </div>
            <div class="modal-body">

               <div class="container-fluid kv-create-employee-grid">

                  <div class="kv-create-employee-field kv-create-employee-department">
                     <label>Department</label>
                     <div id="createEmpDepartmentName">&nbsp;</div>
                  </div>

                  <div class="kv-create-employee-field">
                     <label for="name">Employee Name</label>
                     <div>
                        <input type="text" autocomplete="off" name="inputname" id="name" class="form-control">
                     </div>
                  </div>

                  <div class="kv-create-employee-field">
                     <label for="rate">Employee Rate</label>
                     <div>
                        <input type="text" autocomplete="off" name="inputrate" id="rate" value="{{ $default_rate }}" class="form-control" onkeypress="return validateFloatKeyPress(this,event);">
                     </div>
                  </div>

                  <div class="kv-create-employee-field">
                     <label for="salary">Employee Salary</label>
                     <div>
                        <input type="text" autocomplete="off" name="inputsalary" id="salary" class="form-control" onkeypress="return validateFloatKeyPress(this,event);">
                     </div>
                  </div>

                  <div class="kv-create-employee-field">
                     <label for="workinghour">Employee Default Hours</label>
                     <div>
                        <input type="text" autocomplete="off" name="inputworkinghour" id="workinghour" class="form-control" onkeypress="return validateFloatKeyPress(this,event);">
                     </div>
                  </div>

               </div>

            </div>

            <div class="modal-footer">
               <button type="submit" class="btn btn-primary submitBtn" id="submitDep">SUBMIT</button>
            </div>

         </div>
      </form>
   </div>
</div>
<div class="modal fade" id="editEmp" role="dialog">
   <div class="modal-dialog" id="editEmpbox">
      <!-- Modal content-->
   </div>
</div>

<div class="modal fade kv-labor-employee-modal" id="addExistingEmp" role="dialog">
   <div class="modal-dialog" id="editEmpbox">
      <!-- Modal content-->
      {!! Form::model(null, array('route' => ['laboursection.store'], 'method' => 'POST')) !!}

      <?php if($headerInfo){ ?>
      <input type="hidden" name="header_id" value="{{ $headerInfo->id }}">
      <input type="hidden" name="cat" value="{{ $payrollCategory[0]->id }}">
      <input type="hidden" name="weeknum" value="{{ $weeksStoreOne }}">
      <?php } ?>
      <div class="modal-content">
         <div class="modal-header kv-employee-modal-header">
            <div class="kv-employee-modal-title">
               <span class="kv-employee-modal-icon"><i class="fa fa-user-plus"></i></span>
               <div>
                  <h4 class="modal-title">Add Existing Employee</h4>
               </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
         </div>
         <div class="modal-body">
            <div class="kv-employee-modal-toolbar">
               <div class="kv-employee-modal-copy">
                  <strong>Department</strong>
                  <span id="existingEmpDepartmentName">Select the employee’s department</span>
               </div>
               <button type="button" class="btn btn-primary submitBtn kv-create-employee" id="openCreateNewEmployee"><i class="fa fa-user-plus"></i> Create New Employee</button>
            </div>
            <div class="kv-employee-select-row">
               <label for="kvSelectEmployee">Employee</label>
               <input type="hidden" name="department" class="selectEmpDep">
               <select class="form-control" id="kvSelectEmployee" name="selectEmp">
                  @foreach($allPayrollEmps as $emp)
                     <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                  @endforeach
               </select>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn kv-employee-cancel" data-dismiss="modal">Cancel</button>
            <input type="submit" name="Add" value="Add Employee" class="btn btn-primary submitBtn kv-employee-add">
         </div>
      </div>
      </form>
   </div>
</div>

<script type="text/javascript">
var stDate = "{{ $finalStartDate }}";
var enDate = "{{ $finalEndDate }}";
let year =  "{{ $year }}";
let LocationYear = "{{$location_year}}";
let sDateStore = "{{date('m-d-Y',strtotime($sDateStore))}}";
function getYearList() {
   let  currentYear = new Date().getFullYear();
    let selcted = '';
    for (let i = LocationYear; i <= currentYear + 5; i++) {
    if(year == i )
        selcted ='selected'
    else
        selcted =''
    $('#year').append(`<option value='${i}' ${selcted}>${i}</option>`)
  }
}

//getYearList();
function getLastSaturdayOfYear(inputDate) {
  const date = new Date(inputDate);

  // Move to the last day of the year
  date.setMonth(11, 31);

  // If the last day is a Saturday, return it
  if (date.getDay() === 6) {
    return date.toISOString().split('T')[0];
  }

  // If not, find the last Saturday of the year
  date.setDate(date.getDate() - date.getDay()); // Move to the beginning of the current week
  date.setDate(date.getDate() - 1); // Move to the last day of the previous week (Saturday)
 //date.toISOString().split('T')[0]
  return date;
}
function getDateRangesByYear(year) {
  const dateRanges = [];

  // Set the start date to the last Saturday of the previous year
  let startDate1 = new Date(year - 1, 11, 31);

  let startDate = new Date(getLastSaturdayOfYear(startDate1));
  if(LocationYear === year){

    startDate = new Date(getLastSaturdayOfStoreYear(new Date(sDateStore)));
  }

 startDate.setDate(startDate.getDate() + (6 - startDate.getDay() + 7) % 7);

  // Iterate through the weeks spanning the previous year, current year, and next year
  for (let i = 0; i < 27; i++) {
    const endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 13);

    dateRanges.push({
      // startDate: formatDate(startDate,2),
      // endDate: formatDate(endDate,2)
      startDate: formatDate(startDate),
       endDate: formatDate(endDate)
     });

    //   savelocation(data);
    startDate.setDate(startDate.getDate() + 14);
    //console.log(startDate);
  }
//  let dateRanges1 = dateRanges.splice(500,200);
  return dateRanges;
}

function savelocation(){
  let dateRanges = getDateRangesByYear(LocationYear);
  console.log(dateRanges);
  $.ajax({
    headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
        type:       "POST",
        dataType:   "json",

        url:        "https://kvmgmt.net/create_date_range",
        data:{ dateRanges:dateRanges},
        success: function(data)
        {
          console.log(data);
        }
     });
}

function getWeekRangeListByYear(year){
  $.ajax({
    headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
        type:       "GET",
        dataType:   "json",
        url:        "{{url('getWeekRange/')}}/"+year,
      //  data:{ year:year},
        success: function(data)
        {
          $('#datefilter').empty();
          data.forEach((item, i) => {
              $('#datefilter').append(`<option value='${item.start_date} -  ${item.end_date}' >${item.start_date} - ${item.end_date}</option>`)
          });
        }
     });
}

//savelocation();

function formatDate(date,type) {
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const day = date.getDate().toString().padStart(2, '0');
  if(type ==='1'){
    return year;
  }else if(type ===2){

        return `${year}-${month}-${day}`;
    }
  else{
    return `${month}-${day}-${year}`;
  }
}

function getLastSaturdayOfStoreYear(store_date){
  let storeDateObj = new Date(store_date);

// Calculate the day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
let dayOfWeek = storeDateObj.getDay();

// Calculate the difference between the current day and Saturday
let difference = dayOfWeek + (dayOfWeek === 0 ? -6 : 1);

// Adjust the date to the previous Saturday
storeDateObj.setDate(storeDateObj.getDate() - difference);

// Format the result as "MM-DD-YYYY"
let previousSaturday = (storeDateObj.getMonth() + 1).toString().padStart(2, '0') + '-' +
    (storeDateObj.getDate()).toString().padStart(2, '0') + '-' +
    storeDateObj.getFullYear();
    return previousSaturday;
}
// Example usage for the year 2024

const currentYear = new Date().getFullYear();
//getWeekRangeList(year);

function getWeekRangeList(year){
  const dateRanges = getDateRangesByYear(year);
    $('#datefilter').empty();
//    console.log(dateRanges);
      //  console.log(sDateStore,'startDate');
        dateRanges.forEach(({ startDate, endDate }) => {

        // if( new Date(sDateStore)<=  new Date(startDate))
        // {

            let range =   `${startDate} - ${endDate}`;
            let selcted = '';
            // $('#datefilter').append(`<option value='${range}' ${selcted}>${range}</option>`)
   //}

      });
}



   $("#weeklyDatePicker").datetimepicker({
       format: 'MM-DD-YYYY',
        minDate: new Date(sDateStore),
      //   maxDate:new Date(max_year),
       locale: moment.locale('en', {
           week: { dow: 6 }
       }),
       ignoreReadonly: true, // Add this line to prevent the calendar from opening on input click
   });

  $("#weeklyDatePicker").val(stDate + ' - ' + enDate);

   $('#weeklyDatePicker').on('dp.hide', function (e) {
          $('#weekDatapick').attr('disabled','disabled');
          var value = $("#weeklyDatePicker").val();
          let date = moment(value, "MM-DD-YYYY").format("YYYY-MM-DD");
          $.ajax({
             type:       "POST",
             dataType:   "json",
             url:        "{{ url('/') }}/getWeekRangeByDate",
             data: {selected_date:date},
             success: function(response)
             {
              $("#weeklyDatePicker").val(response.start_date + "   -   " + response.end_date);
                $('#weekDatapick').removeAttr('disabled');
             }
          });


   });



   /*ADD RECORD*/
   function addNew(e)
   {
      var cat = $(e).attr("cat");
      var dep = $(e).attr("dep");
      var emp = $(e).attr("emp");

      var wks = $("#weekstart").val();
      var wke = $("#weekend").val();
      var rate    = $(e).val();
      var weeknum = $(e).attr("weeknum");

      var ttl = sumRow(e);
      sumColumn(e);

      // alert(ttl);

      $.ajax({
         type:       "POST",
         dataType:   "json",
         url:        "{{ url('/') }}/dataSave",
         data: {cat:cat,dep:dep,emp:emp,wks:wks,wke:wke,rate:rate,weeknum:weeknum,ttl:ttl,sdate:"{{$sdate}}",edate:"{{$edate}}"},
         success: function(data)
         {
            if(data > 0){

            }
         }
      });
   }

   function sumColumn(e)
   {
      var cat = $(e).attr("cat");
      var wek = $(e).attr("weeknum");
      var emp = $(e).attr("emp");

      var cattotal = parseFloat(0);
      var catemptotal = parseFloat(0);
      var wkgttl = parseFloat(0);

      $("input[cat='"+cat+"']").each(function()
      {
         var catval = parseFloat($(this).val());
         var thswek = $(this).attr("weeknum");
         var thsemp = $(this).attr("emp");

         if((!isNaN(catval)) && (thsemp == emp))
            catemptotal = catemptotal + catval;

         if((!isNaN(catval)) && (thswek == wek))
            cattotal = cattotal + catval;
      });

      $("#ttl-"+wek+"-"+cat).html(+addCommas(cattotal.toFixed(2)));
	  if(cat ==4){
		        $("#ttl-"+emp+"-"+cat).html('$'+addCommas(catemptotal.toFixed(2)));
	  }else{
		  		        $("#ttl-"+emp+"-"+cat).html(addCommas(catemptotal.toFixed(2)));

	  }

      // CALCULATE W/K TOTAL //
      $("input[cat='"+cat+"']").each(function()
      {
         var thsemp = $(this).attr("emp");
         var thswek = $(this).attr("weeknum");
         var thsdep = $(this).attr("dep");

         var tval = $("#ttl-"+thsdep+"-"+thsemp+"-"+wek).val();
         tval = tval.replace(',','');
         tval = parseFloat(tval.replace('$',''));

         if((!isNaN(tval)) && (thswek == wek))
            wkgttl = wkgttl + tval;
      });

      $("#wk"+wek+"gttl").html('$'+addCommas(wkgttl.toFixed(2)));
      // CALCULATE W/K TOTAL //
   }

   function sumRow(e)
   {
      var wek = $(e).attr("weeknum");
      var emp = $(e).attr("emp");
      var dep = $(e).attr("dep");
      var emprate = parseFloat($("#rate-"+emp).val());

      // console.log(wek);

      var cattotal = parseFloat(0);

      var catAry = {};
      <?php foreach($payrollCategory as $category){ ?>
         catAry[{{$category->id}}] = "{{ trim(strtolower($category->name)) }}";
      <?php } ?>


      $("input[emp='"+emp+"']").each(function()
      {
         var catval  = $(this).val();
         var catval  = catval.replace(',','');
         var catval  = parseFloat(catval.replace('$',''));
         var thswek  = $(this).attr("weeknum");
         var thscat  = $(this).attr("cat");

         var thsdep  = $(this).attr("dep");

         if((!isNaN(catval)) && (thswek == wek) && (thsdep == dep)){

            if(catAry[thscat] == 'overtime')
               cattotal = (cattotal + ((emprate + (emprate / 2)) * catval));

            else if(catAry[thscat] == 'salary')
               cattotal = cattotal + catval;

            else
               cattotal = cattotal + (catval * emprate);
         }

         var yellowsum = parseFloat(0);
         $(".yellow-"+thscat).each(function()
         {
            var tval = $(this).html();
            tval = tval.replace(',','');
            tval = parseFloat(tval.replace('$',''));

            if(!isNaN(tval))
               yellowsum += tval;  // Or this.innerHTML, this.innerText
         });
		if(thscat ==4)
			$("#yellow-ttl-"+thscat).html('$'+addCommas(yellowsum.toFixed(2)))
		else
			$("#yellow-ttl-"+thscat).html(addCommas(yellowsum.toFixed(2)))

      });

      $("#ttl-"+dep+"-"+emp+"-"+wek).val('$'+addCommas(cattotal.toFixed(2)));


      // CALCULATE GRAND TOTAL //
      var grdttl = parseFloat(0);
      $("input[class='wkttl-"+emp+"']").each(function()
      {
         var tval = $(this).val();
         tval = tval.replace(',','');
         tval = parseFloat(tval.replace('$',''));

         if(!isNaN(tval))
            grdttl = grdttl + tval;
      });
      $("#gdttl-"+emp).html('$'+addCommas(grdttl.toFixed(2)));
      // CALCULATE GRAND TOTAL //


      // CALCULATE TOTAL OF GRAND TOTAL //
      var ttlgdttl = parseFloat(0);
      $("td[class='gdttl']").each(function()
      {
         var tval = $(this).html();
         tval = tval.replace(',','');
         tval = parseFloat(tval.replace('$',''));

         if(!isNaN(tval))
            ttlgdttl = ttlgdttl + tval;
      });
      $("#ttlgdttl").html('$'+addCommas(ttlgdttl.toFixed(2)));
      // CALCULATE TOTAL OF GRAND TOTAL //

      return ttlgdttl;
   }


   $('#addExistingEmp').on('show.bs.modal', function (event) {
      var button  = $(event.relatedTarget); // Button that triggered the modal
      // var modal       = $(this);
      var title = button.attr('depName'); // alert(title);
      var depId = button.attr('depId'); // alert(title);

      $(".selectEmpDep").val(depId);
      $("#existingEmpDepartmentName, #createEmpDepartmentName").html(title)
      // modal.find('.modal-title').text(title)
   });

   $('#openCreateNewEmployee').on('click', function () {
      $('#addExistingEmp').one('hidden.bs.modal', function () {
         $('#createNewEmp').modal('show');
      });
      $('#addExistingEmp').modal('hide');
   });

   function editEmp(id)
   {
      var headerid = <?=(($headerInfo)?$headerInfo->id:0);?>;
      $.ajax({
         type:       "POST",
         dataType:   "json",
         url:        "{{ url('/') }}/editEmployee",
         data: {emp_id: id, headerid: headerid},
         success: function(data)
         {
            $('#editEmpbox').html(data['html']);
            $('#editEmp').modal('show');
         }
      });
   }


   $( document ).ready(function(){

      <?php // if(!empty($empsLabor)){ ?>
      $(".currency").each(function() {
         sumColumn(this);
         var x = sumRow(this);
      });
      <?php // } ?>
   });

</script>

<script type="text/javascript">
   /* useful functions start*/
      function addCommas(nStr)
      {
         nStr += '';
         x  = nStr.split('.');
         x1 = x[0];
         x2 = x.length > 1 ? '.' + x[1] : '';
         var rgx = /(\d+)(\d{3})/;

         while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
         }
         return x1 + x2;
      }


      function validateFloatKeyPress(el, evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode;
         var number = el.value.split('.');
         if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
         }

         //just one dot
         if(number.length>1 && charCode == 46){
            return false;
         }

         return true;
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
   /* useful function ends */
</script>
<script type="text/javascript">
   $("#pmt_frm").submit(function(e){

      var rate = $("#rate").val();
      var salary = $("#salary").val();
      var workinghour = $("#workinghour").val();

      if((salary == '') && (rate == '')){
         alert("Please fill rate or salary");
         return  false;
      }else if((salary != '') && (workinghour == '')){
         alert("Please fill working hours!");
         return  false;
      }
      else{
         return true;
      }
    });
</script>
@endsection
