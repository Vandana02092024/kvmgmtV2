<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Requests\LaboursectionRequest;
use App\PayrollCategory;
use App\PayrollDepartment;
use App\PayrollEmployee;
use App\PayrollEmployee_location;
use App\PayrollEmployee_department;
use App\User_location;
use App\Location;
use App\Laboursection;
use App\Sale;
use App\Discount;
use App\Adjustment;
use App\Receipt;
use App\Sale_location;
use App\Discount_location;
use App\Adjustment_location;
use App\Receipt_location;
use Mail;
use Session;
use Validator;
use PDF;
use DateTime;
class LaboursectionController extends Controller
{	

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index()
    {	
	
		$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		
		$sDateStore =  strtotime($queryData[0]->startdate);	
		
		//$data = DB::select("SELECT WeekOneNumber,WeekTwoNumber, sumweekOne + weekTwoSum as FinalAmount FROM (SELECT WeekOneNumber,WeekTwoNumber, amount as sumweekOne,IFNULL(sumweektwo,0) as weekTwoSum FROM (select `weekNo` as WeekOneNumber,`weekNo`+1 as WeekTwoNumber, sum(rate) as amount,  (SELECT IFNULL(sum(rate),0) as bSum FROM laboursections WHERE weekNo = WeekTwoNumber  GROUP BY weekNo) as sumweektwo from `laboursections` where `location_id` = '".$locationId."'  GROUP BY weekNo) AS innerData) mainData");
		$data = DB::select("SELECT start_week as WeekOneNumber,end_week as WeekTwoNumber,weekNo,sum(rate) as FinalAmount FROM laboursections GROUP by start_week,end_week ORDER BY weekNo DESC");
		return view('laboursections.index', compact('data','sDateStore')); 
		
	}
	
	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {	
		//$lastSat = date('m-d-Y', strtotime("last Saturday - 7 days"));
		/* $lastFri = date('m-d-Y', strtotime("Friday"));
		$nextFriday = date('m-d-Y', strtotime("Saturday")); */
		/*$nextFri = date('m-d-Y', strtotime("Friday"));
		$lastSatFormat = date('Y-m-d', strtotime("last Saturday - 7 days"));
		$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);
		$eDateStore =  strtotime($lastSatFormat);		
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = ceil($daysStore/7);	
		$weeksStoreTwo = $weeksStoreOne + 1; */
		
		$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);
		
		$currDate = date('Y-m-d');
		$currDateSatFmt =  date("w", strtotime($currDate)); 
		$lastSatFrmt = date("Y-m-d", strtotime($currDate)); 
		
		if(Session::get('checkDateType') == 1) {
			//$currDateStamp = 
			$currDate = Session::get('checkDateTime');
			$currDateSatFmt =  date("w", strtotime($currDate)); 
		    $lastSatFrmt = date("Y-m-d", strtotime($currDate)); 
		} 
		
		
		
		$dayWeek =  date('w', $sDateStore);
		$lastSatFormat =  date("Y-m-d", $sDateStore); 
		if($dayWeek != 6)
			$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore)); 
		
		//$lastSatFormat = "2019-03-02";
		
		/*$currDateSatFmt =  date("w", strtotime($currDate)); 
		if($currDateSatFmt != 6)
			$lastSatFrmt = date("Y-m-d", strtotime("previous saturday", strtotime($currDate))); 
		else
			$lastSatFrmt = date("Y-m-d", strtotime($currDate)); */
		
		
		$eDateStore =  strtotime($currDate);
		$sDateStore = strtotime($lastSatFormat);		
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = (int)($daysStore/7);
		$weeksStoreOne = $weeksStoreOne + 1;

		if($weeksStoreOne%2 == 0){
			 $weeksdays = round($daysStore);
			 $weeksStoreOne = $weeksStoreOne - 1;
			 $weeksStoreTwo = $weeksStoreOne + 1;
			
			if($currDateSatFmt != 6) { 
				$weeksdays = round($daysStore) - ($currDateSatFmt + 8);
			} else if($currDateSatFmt == 6){
				$weeksdays = round($daysStore) - 7;
			}
			
		} else {
			$weeksdays = round($daysStore);
			if($currDateSatFmt != 6) { 
				$weeksdays = round($daysStore) - ($currDateSatFmt + 1);
			}
			
			$weeksStoreOne = $weeksStoreOne;
			$weeksStoreTwo = $weeksStoreOne + 1;
		}
		
	    $lastSat = date("m-d-Y", strtotime($lastSatFormat. ' + '.$weeksdays.' days')); 
		$nextFri = date("m-d-Y", strtotime($lastSatFormat. ' + '.($weeksdays + 13).' days'));
		
		$countLastWeekEntry = DB::table('laboursections')
							->select('start_week')
							->where('end_week', '=', $weeksStoreOne)
							->groupBy('end_week')
							->get();
							
		  
           if (count($countLastWeekEntry) > 0){		
			$countLastWeekEntryValue = 	$countLastWeekEntry[0]->start_week;					
			$countValue =  count($countLastWeekEntry);		
			if($countValue > 0) {
				return redirect()->route('laboursection.edit', ['id' => $countLastWeekEntryValue]);
			}
		}
		$startDatestore = date('m-d-Y',strtotime($lastSatFormat));
		
		$payrollCategory = DB::table('payroll_categories')
				->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')
				->where('payroll_categories.status', '=', '1')
				->where('payroll_category_locations.location', '=', $locationId)
				->select('payroll_categories.*')
				->orderBy('sorting_order','asc')
				->get();	
        
		
		$payrollDepartment = DB::table('payroll_departments')
							->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')
							->where('payroll_departments.status', '=', '1')
							->where('payroll_department_locations.location', '=', $locationId)
							->select('payroll_departments.*')
							->orderBy('sorting_order','asc')
							->get();
		
		
		$dataEmp = array();
		foreach($payrollDepartment as $pD){		
			$payrollEmployee =  DB::select("SELECT c.*, a.id as pedid FROM `payroll_employee_departments` a inner join payroll_employee_locations b ON a.payrollemp_id = b.payrollemp_id  INNER JOIN `payroll_employees` c ON c.id = b.payrollemp_id where a.status = 0 AND b.status = 0 AND a.payrollcat_depid = '".$pD->id."' and b.payrollcat_depid = '".$locationId."' AND c.employeeStatus IN ('0','1') and `c`.`weekStart` is null and `c`.`weekEnd` is null AND c.status= '1'  OR  (c.weekStart = '".$weeksStoreOne."' AND c.weekEnd = '".$weeksStoreTwo."' AND c.status= '1') ORDER BY c.sorting_order asc");
								
								foreach($payrollEmployee as $pE){
										$dataEmp[$pD->name][$pE->id] = array($pE->name,$pE->rateperhour,$pE->salary,$pE->working_hour,$pE->id,$pE->pedid);									
								}	    
		}
		
		$currentdata = DB::select("SELECT ls.rate,ls.weekNO,pe.name as empName,pc.name as catName,pd.name as depName FROM laboursections ls INNER JOIN payroll_employees pe ON ls.empId = pe.id INNER JOIN payroll_categories pc ON ls.catId = pc.id  INNER JOIN payroll_departments pd ON ls.depId = pd.id WHERE ls.weekNo BETWEEN '".$weeksStoreOne."' AND '".$weeksStoreTwo."' AND location_id = '".$locationId."' ");
	 
		Session::forget('checkDateType');
		Session::forget('checkDateTime');
		
		return view('laboursections.create', compact('payrollCategory','payrollDepartment','dataEmp','lastSat','nextFri','weeksStoreOne','weeksStoreTwo','currentdata','startDatestore','lastSatFormat','sDateStore'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$locationId = Session::get('location_id');
		$requestDate = explode(' - ',$request['datepick']);	
		$lastSat = $requestDate[0];
		//$nextFri = $requestDate[1];
		$dateDataEnd = explode('-',$requestDate[0]);
		$dateend = $dateDataEnd[2]."-".$dateDataEnd[0]."-".$dateDataEnd[1];			
		Session::put('checkDateType', 1);
		Session::put('checkDateTime', $dateend);
		
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);
			
		$eDateStore = strtotime($dateend) ;
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = ceil($daysStore/7);	
		$weeksStoreTwo = $weeksStoreOne + 1;
		
		$startDateLocation = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status','=',1)->get();	
		$startDatestore = date('m-d-Y',strtotime($startDateLocation[0]->startdate));
		
		$payrollCategory = DB::table('payroll_categories')
				->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')
				->where('payroll_categories.status', '=', '1')
				->where('payroll_category_locations.location', '=', $locationId)
				->select('payroll_categories.*')
				->orderBy('sorting_order','asc')
				->get();	
        
		
		$payrollDepartment = DB::table('payroll_departments')
							->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')
							->where('payroll_departments.status', '=', '1')
							->where('payroll_department_locations.location', '=', $locationId)
							->select('payroll_departments.*')
							->orderBy('sorting_order','asc')
							->get();
		
		
		$dataEmp = array();
		foreach($payrollDepartment as $pD){				
			$payrollEmployee =  DB::select("SELECT c.*, a.id as pedid FROM `payroll_employee_departments` a inner join payroll_employee_locations b ON a.payrollemp_id = b.payrollemp_id  INNER JOIN `payroll_employees` c ON c.id = b.payrollemp_id where a.status = 0 AND b.status = 0 AND a.payrollcat_depid = '".$pD->id."' and b.payrollcat_depid = '".$locationId."' AND c.employeeStatus IN ('0','1') and `c`.`weekStart` is null and `c`.`weekEnd` is null AND c.status= '1'  OR  (c.weekStart = '".$weeksStoreOne."' AND c.weekEnd = '".$weeksStoreTwo."' AND c.status= '1') ORDER BY c.sorting_order asc");
								
								foreach($payrollEmployee as $pE){
										$dataEmp[$pD->name][] = array($pE->name,$pE->rateperhour,$pE->salary,$pE->working_hour,$pE->id,$pE->pedid);									
								}		    
		}
		$currentdata = DB::select("SELECT ls.rate,ls.weekNO,pe.name as empName,pc.name as catName,pd.name as depName FROM laboursections ls INNER JOIN payroll_employees pe ON ls.empId = pe.id INNER JOIN payroll_categories pc ON ls.catId = pc.id  INNER JOIN payroll_departments pd ON ls.depId = pd.id WHERE ls.weekNo BETWEEN '".$weeksStoreOne."' AND '".$weeksStoreTwo."' AND location_id = '".$locationId."' ");
		
		return redirect()->route('laboursection.create');	 
		//return view('laboursections.create', compact('payrollCategory','payrollDepartment','dataEmp','lastSat','nextFri','weeksStoreOne','weeksStoreTwo','currentdata','startDatestore'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
   
	   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {	
		$week = $id;				
		$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);	
		$dayWeek =  date('w', $sDateStore);
		$lastSatFormat =  date("Y-m-d", $sDateStore); 
		if($dayWeek != 6)
			$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore)); 
		
		$days = $week * 7;
		
		$dateDiff = date('Y-m-d', strtotime("+$days days", strtotime($lastSatFormat)));
		
		/* $dateend =	date('Y-m-d', strtotime("last Saturday", strtotime($dateDiff)));
		$eDateStore = strtotime($dateend) ;
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = ceil($daysStore/7);	
		$weeksStoreTwo = $weeksStoreOne + 1; */
		
		$startDateLocation = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status','=',1)->get();	
		$startDatestore = date('m-d-Y',strtotime($startDateLocation[0]->startdate));
		$weeksStoreOne = $id;	
		$weeksStoreTwo = $id + 1;	
		/*$lastSat = date('m-d-Y', strtotime($dateDiff));
		$nextFri = date('m-d-Y', strtotime("Friday + 7 days", strtotime($dateDiff))); */
			
		$lastDate = date("Y-m-d", strtotime($lastSatFormat. ' + '.$days.' days')); 
		 
		$eDateStore =  strtotime($lastDate);
		$sDateStore = strtotime($lastSatFormat);		
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		
		$weeksStoreOne = (int)($daysStore/7);
		 $weeksStoreOne = $weeksStoreOne + 1;
		 $currDateSatFmt =  date('w', strtotime($lastDate));
		
		// $weeksStoreOne = $week;
		 if($weeksStoreOne%2 == 0){
			 $weeksdays = round($daysStore);
			 $weeksStoreOne = $weeksStoreOne - 1;
			 $weeksStoreTwo = $weeksStoreOne + 1;
			
			if($currDateSatFmt != 6) { 
				$weeksdays = round($daysStore) - ($currDateSatFmt + 8);
			} else if($currDateSatFmt == 6){
				$weeksdays = round($daysStore) - 7;
			}
			
		} else {
			$weeksdays = round($daysStore);
			if($currDateSatFmt != 6) { 
				$weeksdays = round($daysStore) - ($currDateSatFmt + 1);
			}
			
			$weeksStoreOne = $weeksStoreOne;
			$weeksStoreTwo = $weeksStoreOne + 1;
		}
		
		$lastSat = date("m-d-Y", strtotime($lastSatFormat. ' + '.$weeksdays.' days')); 
		$nextFri = date("m-d-Y", strtotime($lastSatFormat. ' + '.($weeksdays + 13).' days'));
		
		$payrollCategory = DB::table('payroll_categories')
				->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')
				->where('payroll_categories.status', '=', '1')
				->where('payroll_category_locations.location', '=', $locationId)
				->select('payroll_categories.*')
				->orderBy('sorting_order','asc')
				->get();	
        
		
		$payrollDepartment = DB::table('payroll_departments')
							->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')
							->where('payroll_departments.status', '=', '1')
							->where('payroll_department_locations.location', '=', $locationId)
							->select('payroll_departments.*')
							->orderBy('sorting_order','asc')
							->get();
		
		/* 
		SELECT c.* FROM `payroll_employee_departments` a inner join payroll_employee_locations b ON a.payrollemp_id = b.payrollemp_id  INNER JOIN `payroll_employees` c ON c.id = b.payrollemp_id where a.payrollcat_depid = '".$pD->id."' and b.payrollcat_depid = '".$locationId."' AND c.employeeStatus IN ('0','1') and `c`.`weekStart` is null and `c`.`weekEnd` is null AND c.status= '1'  OR  (c.weekStart = '".$weeksStoreOne."' AND c.weekEnd = '".$weeksStoreTwo."') */
		
		$dataEmp = array();
		// print_r($payrollDepartment);exit;
		foreach($payrollDepartment as $pD){	
		
		$payrollEmployee =  DB::select("SELECT x.*, y.id  as pedid FROM payroll_employees x INNER JOIN (SELECT a.id, a.payrollemp_id as empId,a.payrollcat_depid as empDep,b.payrollcat_depid as empLoc FROM `payroll_employee_departments` a INNER JOIN payroll_employee_locations b ON a.payrollemp_id = b.payrollemp_id WHERE a.status = 0 AND b.status=0 AND a.payrollcat_depid = '".$pD->id."' and b.payrollcat_depid = '".$locationId."') y ON x.id = y.empId where x.status = '1' AND x.employeeStatus IN ('0','1') and `x`.`weekStart` is null and `x`.`weekEnd` is null OR  (x.weekStart = '".$weeksStoreOne."' AND x.weekEnd = '".$weeksStoreTwo."' AND status = '1') GROUP BY x.id ORDER BY x.sorting_order asc");
								foreach($payrollEmployee as $pE){
										$dataEmp[$pD->name][$pE->id] = array($pE->name,$pE->rateperhour,$pE->salary,$pE->working_hour,$pE->id,$pE->pedid);									
								}		 					
		}
		/* $currentdata = DB::select("SELECT ls.rate,ls.weekNO,pe.name as empName,pc.name as catName,pd.name as depName FROM laboursections ls INNER JOIN payroll_employees pe ON ls.empId = pe.id INNER JOIN payroll_categories pc ON ls.catId = pc.id  INNER JOIN payroll_departments pd ON ls.depId = pd.id WHERE ls.weekNo BETWEEN '".$weeksStoreOne."' AND '".$weeksStoreTwo."' AND location_id = '".$locationId."' "); */
		
		$currentdata = DB::select("SELECT ls.rate,ls.weekNO,pe.name as empName,pc.name as catName,pd.name as depName FROM laboursections ls INNER JOIN payroll_employees pe ON ls.empId = pe.id INNER JOIN payroll_categories pc ON ls.catId = pc.id  INNER JOIN payroll_departments pd ON ls.depId = pd.id WHERE ls.weekNo BETWEEN '".$weeksStoreOne."' AND '".$weeksStoreTwo."' AND location_id = '".$locationId."' ");
		
		$sDateStore = strtotime($lastSatFormat);
		
		return view('laboursections.create', compact('payrollCategory','payrollDepartment','dataEmp','lastSat','nextFri','weeksStoreOne','weeksStoreTwo','currentdata','startDatestore','lastSatFormat','sDateStore'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request )
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Laboursection $salesection)
    {	
	
	}
	
	public function weekNumber(Request $request)
    {
		$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', '1')->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);
		$storeStartWeek =  date('w', $sDateStore);
		$lastSatFormat =  date("Y-m-d", strtotime($queryData[0]->startdate)); 
		if($storeStartWeek != 6)
			$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore)); 
		
		$dateData = explode('-',$request['firstDate']);
		$dateD = $dateData[2]."-".$dateData[0]."-".$dateData[1];
		$dayWeek =  date('w', strtotime($dateD));
		
		/*if($dayWeek != 6)
			$dateD = date("Y-m-d", strtotime("previous saturday", strtotime($dateD)));  */
		
		$eDateStore = strtotime($dateD);
		$sDateStore = strtotime($lastSatFormat);
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = (int)($daysStore/7);
		$weeksStoreOne = $weeksStoreOne + 1;

		/*if($weeksStoreOne%2 == 0){
			$weeksStoreOne = $weeksStoreOne - 1;
			$weeksStoreTwo = $weeksStoreOne + 1;
		} else {
			$weeksStoreOne = $weeksStoreOne;
			$weeksStoreTwo = $weeksStoreOne + 1;
		} */
		
		if($weeksStoreOne%2 == 0){
			 $weeksStoreOne = $weeksStoreOne - 1;
			 $weeksStoreTwo = $weeksStoreOne + 1;
			
			
		} else {
			
			$weeksStoreOne = $weeksStoreOne;
			$weeksStoreTwo = $weeksStoreOne + 1;
		}
		
		/*$locationId = Session::get('location_id');
		$queryData = DB::table('locations')->select('startdate')->where('status', '=', '1')->where('id', '=', $locationId)->get();
		$sDateStore =  strtotime($queryData[0]->startdate);

		$dateData = explode('-',$request['firstDate']);
		$dateD = $dateData[2]."-".$dateData[0]."-".$dateData[1];
		$eDateStore = strtotime($dateD) ;
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));
		$weeksStoreOne = ceil($daysStore/7);	
		$weeksStoreTwo = $weeksStoreOne + 1; */
		return $weeksStoreOne."|".$weeksStoreTwo;	
	}
	public function dataSave(Request $request)
    {
		$locationId = Session::get('location_id');
		$empName = $request['empname'];
		$catName = $request['catname'];
		$depName = $request['depname'];
		$weekNum = trim($request['weekNumber']);
		$weekNumOne = trim($request['weekNumberOne']);
		$weekNumTwo = trim($request['weekNumberTwo']);
		$value = str_replace('$ ','',$request['val']);
		
		$empIdquery = DB::table('payroll_employees')->select('id')->where('name', '=', $empName)->get();
		$empId = $empIdquery[0]->id;
		$catIdquery = DB::table('payroll_categories')->select('id')->where('name', '=', $catName)->get();
		$catId = $catIdquery[0]->id;
		$depIdquery = DB::table('payroll_departments')->select('id')->where('name', '=', $depName)->get();
		$depId = $depIdquery[0]->id;
		
		$rowCOllection = DB::table('laboursections')->select('id')->where('empId', $empId)->where('catId', $catId)->where('depId', $depId)->where('weekNo', $weekNum)->where('location_id', $locationId)->get();			
			$rowCount = count($rowCOllection);			
			if($rowCount > 0){
				$wordCount = $rowCOllection[0]->id;			
				$updateArray = array(		
					'rate' => $value
					);	
				$salesection = Laboursection::where('id','=', $wordCount)->update($updateArray);
			}else{					
			$insertArray = array(		
				'empId' => $empId,
				'catId' => $catId,
				'depId' => $depId,
				'weekNo' => $weekNum,
				'rate' => $value,
				'location_id' => $locationId,
				'start_week' => $weekNumOne,
				'end_week' => $weekNumTwo	
			);	
			
			$salesection = Laboursection::create($insertArray);
			}
			exit;
	}
	
	public function newFieldSave(Request $request)
		{	
			$locationId = Session::get('location_id');
			$empName = $request['inputname'];
			$rate = $request['inputrate'];
			$salary = $request['inputsalary'];
			$workinghour = $request['inputworkinghour'];
			$depName = $request['department'];
			$weekOneNo = $request['weekOneNo'];
			$weekTwoNo = $request['weekTwoNo'];
			
			$depIdquery = DB::table('payroll_departments')->select('id')->where('name', '=', $depName)->get();
			$depId = $depIdquery[0]->id;
			
			if(!empty($empName) && (!empty($rate)) ){
			$employee = PayrollEmployee::create([
            'name' => $empName,            
			'rateperhour' => $rate,
			'salary' => $salary,
			'working_hour' => $workinghour,
			'employeeStatus' => 1,
			'weekStart' => $weekOneNo,
			'weekEnd' => $weekTwoNo,
			]);
			$last = DB::table('payroll_employees')->latest()->first();
			$lastInsertId = $last->id;
			if($lastInsertId != ''){
						PayrollEmployee_department::create([
                        'payrollemp_id' => $lastInsertId,
						'payrollcat_depid' => $depId,
                        'location' => $locationId,
                    ]);
					
					PayrollEmployee_location::create([
                        'payrollemp_id' => $lastInsertId,
						'payrollcat_depid' => $locationId
                        
                    ]);
			}
		}
				return "complete";
		}
		
		public function hideEmployee(Request $request)
		{	
		$locationId = Session::get('location_id');
		$empId = $request->emp_id;
		$catName = $request->catName;
		$depIdquery = DB::table('payroll_departments')->select('id')->where('name', '=', $catName)->get();
		$depId = $depIdquery[0]->id;		
		$updateEmployee  = PayrollEmployee::where('id', '=', $empId)->update(['status'   => 2]); 
		$updateEmployee  = PayrollEmployee_department::where('payrollemp_id', '=', $empId)->where('payrollcat_depid', '=', $depId)->where('location', '=', $locationId)->update(['status'   => 1]); 
		$updateEmployee  = PayrollEmployee_location::where('payrollemp_id', '=', $empId)->where('payrollcat_depid', '=', $locationId)->update(['status'   => 1]); 			
		echo 1;							
		}



		function removeSelectedDepartment(Request $request)
	    {
			// echo "<pre>"; print_r($request->id); exit();
	        $departmentD = PayrollEmployee_department::where('id', '=', $request->id)->get()->first();
	        $departmentD->delete();
	        echo 1;
	    }
		
	
}