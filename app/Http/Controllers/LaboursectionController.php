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
use App\PayrollDepartment_location;
use App\User_location;
use App\Location;
use App\Laboursection;
use App\Laborheadersections;
use App\Sale;
use App\Discount;
use App\Adjustment;
use App\Receipt;
use App\Sale_location;
use App\Discount_location;
use App\Adjustment_location;
use App\Receipt_location;
use App\Track_employees;
use Mail;
use Session;
use Validator;
use PDF;
use DateTime;


class LaboursectionController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
		$this->locations 	= Location::pluck('location', 'id');
		$this->departments 	= PayrollDepartment::pluck('name', 'id');
		$this->roles 		= array('Owner' => 'Owner', 'Supervisor' => 'Supervisor', 'Employee' => 'Employee');
	}

	public function index()
	{
		// say_hello($locationId); exit();
		// $this->calculate_totle(1);

		$locationId = Session::get('location_id');
		$records 	= Laborheadersections::orderBy('start_week', 'desc')->where('location_id', '=', $locationId)->get();
		 //$dateRange  = $this->create_date_range();
		return view('laboursections.index', compact('records'));
	}

	function create_date_range(Request $request){

		$locationId = Session::get('location_id');
		$location  = DB::table('locations')->select('startdate', 'rate')->where('status', '=', '1')->where('id', '=', $locationId)->first();
		$location_year = date('Y',strtotime($location->startdate));

		if($request->dateRanges){


			$sDateStore = $location->startdate;
			$weeksStoreOne = 1;
			foreach ($request->dateRanges as $dateRanges) {

				//$weeksStoreOne 	= $this->weeks_between($sDateStore, $dateRanges['startDate']);
				$weeksStoreTwo  = $weeksStoreOne + 1;
				$week_two = $weeksStoreOne.','.$weeksStoreTwo;
				$weeksStoreOne = $weeksStoreTwo+1;
				$location_daterange  = DB::table('location_daterange')->where('location_id', '=', $locationId)
				->where('week', $week_two)
				->first();

				$data= [
					'start_date'=> $dateRanges['startDate'],
					'end_date'=> $dateRanges['endDate'],
					'week'=>$week_two,
					'location_id'=>$locationId
				];


				if(empty($location_daterange)){
					// DB::table('location_daterange')
					// ->insert($data);
				}

			}
		}
		$res = ['msg'=>'recore insered'];
			return json_encode($res);

	}

		function getWeekRange($year,$ajax=true){
			$locationId = Session::get('location_id');
			$data = DB::table('location_daterange')
			->select(DB::raw('date_format(start_date, "%m-%d-%Y") as start_date'),DB::raw('date_format(end_date, "%m-%d-%Y") as end_date'))
			->where('location_id','=',$locationId)
	    ->where(DB::raw('date_format(end_date, "%Y")'), '=', $year)
	    ->get();
			if($ajax == true)
				return  json_encode($data);
			else {
				return  $data;
			}
		}

	// ######################################################## //
	// CREATE RECORDS //
		public function create()
		{
			// $locationId = Session::get('location_id');

			// $queryData  = DB::table('locations')->select('startdate', 'rate')->where('status', '=', '1')->where('id', '=', $locationId)->first();

			// $sDateStore = $queryData->startdate;
			// $default_rate = $queryData->rate;
			$locationId = Session::get('location_id');

			$queryData = DB::table('locations')
				->select('startdate', 'rate')
				->where('id', '=', $locationId)
				->first();

			if (!$queryData) {
				return redirect('/laboursection')
					->with('error', 'Selected location was not found. Please select a valid location.');
			}

			if (empty($queryData->startdate)) {
				return redirect('/laboursection')
					->with('error', 'Start date is missing for the selected location.');
			}

			$sDateStore = $queryData->startdate;
			$default_rate = $queryData->rate ?? 0;
			$currDate 	= $todayDate = date('Y-m-d');

			if(Session::get('checkDateType') == 1)
				$currDate 	= Session::get('checkDateTime');

			if(Session::get('hideEmp') !== null)
				$hideEmp 	= Session::get('hideEmp');
			else
				$hideEmp = [];

			$weeksStoreOne 	= $this->weeks_between($sDateStore, $currDate);
			$weeksStoreTwo  = $weeksStoreOne + 1;
			$addWeek = $weeksStoreOne;
			$result1 = $this->getWeekRangeNew($weeksStoreOne, $sDateStore);
			$result2 = $this->getWeekRangeNew($weeksStoreTwo, $sDateStore);

			$result1_array = explode(" to ", trim($result1));
			$result2_array = explode(" to ", trim($result2));

			$finalStartDate = trim($result1_array[0]); 
			$finalEndDate = trim($result2_array[1]);   

			$laborHeader = Laborheadersections::where('location_id', '=', $locationId)->where(function ($query) use ($weeksStoreOne) {
				$query->where('start_week', $weeksStoreOne)->orWhere('end_week', $weeksStoreOne);
			})->first();
			$headerInfo  = false;
			
			if(!is_null($laborHeader)) 
			{
				$headerInfo = $laborHeader;
				return redirect()->action(
					'LaboursectionController@edit', $laborHeader->id
				);
			}

			$payrollCategory   = DB::table('payroll_categories')->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')->where('payroll_categories.status', '=', '1')->where('payroll_category_locations.location', '=', $locationId)->select('payroll_categories.*')->orderBy('sorting_order','asc')->get();

			$payrollDepartment = DB::table('payroll_departments')->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')->where('payroll_departments.status', '=', '1')->where('payroll_department_locations.location', '=', $locationId)->select('payroll_departments.*')->orderBy('sorting_order','asc')->get();

			$payrollDepartmentEmps = array();

			foreach ($payrollDepartment as $value)
			{
				$payrollDepartmentEmps[$value->id] = PayrollEmployee::leftJoin('payroll_employee_departments', 'payroll_employees.id', '=', 'payroll_employee_departments.payrollemp_id')
					->leftJoin('payroll_employee_locations', 'payroll_employees.id', '=', 'payroll_employee_locations.payrollemp_id')
					->where('payroll_employee_departments.payrollcat_depid', '=', $value->id)
					->where('payroll_employee_departments.location', '=', $locationId)
					->where('payroll_employee_locations.payrollcat_depid', '=', $locationId);

				if(!empty($hideEmp)){
					$payrollDepartmentEmps[$value->id] = $payrollDepartmentEmps[$value->id]->whereNotIn('payroll_employees.id', $hideEmp);
				}

				$payrollDepartmentEmps[$value->id] = $payrollDepartmentEmps[$value->id]->where('payroll_employee_locations.payrollcat_depid', '=', $locationId)
					->select('payroll_employees.*')
					->get();
					
			}

			$allPayrollEmps = PayrollEmployee::leftJoin('payroll_employee_departments', 'payroll_employees.id', '=', 'payroll_employee_departments.payrollemp_id')
				->leftJoin('payroll_employee_locations', 'payroll_employees.id', '=', 'payroll_employee_locations.payrollemp_id')
				->where('payroll_employee_departments.location', '=', $locationId)
				->where('payroll_employee_locations.payrollcat_depid', '=', $locationId)
				->select('payroll_employees.*')
				->get();

			$empsLabor = array();

			if(!empty($laborHeader))
			{
				$laborDetail = Laboursection::where('header_id', '=', $laborHeader->id)->get();

				foreach ($laborDetail as $emplabor)
					$empsLabor[$emplabor->depId][$emplabor->empId][$emplabor->weekNo][$emplabor->catId] = $emplabor->rate;
			}

			$currDate = $this->add_weeks($sDateStore, $weeksStoreOne);
			if(date("w", strtotime($currDate)) < 6){
				$stdate = date('m-d-Y',strtotime('last Saturday', strtotime($currDate)));
				$sdate  = date('Y-m-d',strtotime('last Saturday', strtotime($currDate)));
			}else{
				$stdate = date('m-d-Y',strtotime('saturday this week', strtotime($currDate)));
				$sdate  = date('Y-m-d',strtotime('saturday this week', strtotime($currDate)));
			}

			$edate  = date('Y-m-d', strtotime("+13 day", strtotime($sdate)));
			$eddate = date('m-d-Y', strtotime("+13 day", strtotime($sdate)));
			$year = date('Y',strtotime("+13 day", strtotime($sdate)));
				
			// CALANDER START AND END DATE
			$location_year = date('Y',strtotime($sDateStore));
			return view('laboursections.create', compact('headerInfo', 'payrollCategory', 'payrollDepartment', 'payrollDepartmentEmps', 'empsLabor', 'allPayrollEmps', 'weeksStoreOne', 'weeksStoreTwo', 'sDateStore', 'stdate', 'eddate', 'sdate', 'edate','default_rate','year','location_year','finalStartDate','finalEndDate'));
		}

// 		function getWeekRangeByDate(Request $request){
// 			//dd($request->selected_date);
// 			$locationId = Session::get('location_id');

// 		$results = DB::select("
//     SELECT date_format(start_date, '%m-%d-%Y') as start_date,date_format(end_date, '%m-%d-%Y') as end_date
//     FROM location_daterange
//     WHERE date_format('$request->selected_date', '%Y-%m-%d')
//           BETWEEN date_format(start_date, '%Y-%m-%d')
//           AND date_format(end_date, '%Y-%m-%d')
//           AND location_id = 5
// ");

// 			return 	json_encode($results[0]);
// 		}

		function getWeekRangeByDate(Request $request){
			$locationId = Session::get('location_id');

			$queryData  = DB::table('locations')->select('startdate', 'rate')->where('status', '=', '1')->where('id', '=', $locationId)->first();

			$sDateStore = $queryData->startdate;
			$stdate = $request->selected_date;
			$weeksStore 	= $this->weeks_between($sDateStore, $stdate);
			$divide =$weeksStore % 2;
			if ($divide == 0) { 
				$weeksStoreOne = $weeksStore - 1;
				$weeksStoreTwo = $weeksStore;
			} else { 
				$weeksStoreOne = $weeksStore;
				$weeksStoreTwo = $weeksStore + 1;
			}
			
			$result1 = $this->getWeekRangeNew($weeksStoreOne, $sDateStore);
			$result2 = $this->getWeekRangeNew($weeksStoreTwo, $sDateStore);

			$result1_array = explode(" to ", trim($result1));
			$result2_array = explode(" to ", trim($result2));

			$finalStartDate = trim($result1_array[0]); 
			$result1_end = trim($result1_array[1]);   

			$result2_start = trim($result2_array[0]); 
			$finalEndDate = trim($result2_array[1]);   
			
            $results = ["start_date"=>$finalStartDate,"end_date"=>$finalEndDate];

			return 	json_encode($results);
		}

		public function store(Request $request)
		{
			$locationId = Session::get('location_id');

			$method = $request->method();

			if($request->isMethod('post'))
			{
				// echo "<pre>"; // print_r($request->all()); /*print_r($add['payrollemp_id']); //*/ exit();

				if(isset($request['datefilter']))
				{

					$datefilter = explode(" - ", $request['datefilter']);
					$stdate = trim($datefilter[0]);
					$eddate = trim($datefilter[1]);
					
					// echo "<pre>";
					//  print_r($eddate);die;
					$stdate = explode("-", $stdate);
					$stdate = $stdate[2]."-".$stdate[0]."-".$stdate[1];
					$eddate = explode("-", $eddate);
					$eddate = $eddate[2]."-".$eddate[0]."-".$eddate[1];

					$locationId = Session::get('location_id');
					$queryData  = DB::table('locations')->select('startdate')->where('status', '=', '1')->where('id', '=', $locationId)->first();
					$sDateStore = $queryData->startdate;

					$weeksStoreOne 	= $this->weeks_between($sDateStore, $stdate);

					$laborHeader 	= Laborheadersections::where('location_id', '=', $locationId)->where(function ($query) use ($weeksStoreOne) {
						$query->where('start_week', $weeksStoreOne)->orWhere('end_week', $weeksStoreOne);
					})->first();

					if(is_null($laborHeader))
					{
						Session::put('checkDateType', 1);
						Session::put('checkDateTime', $stdate);
						return redirect()->action('LaboursectionController@create');
					}else{
						return redirect()->action(
							'LaboursectionController@edit', $laborHeader->id
						);
					}
					// print_r($stdate); print_r($eddate); var_dump($laborHeader); exit();
				}else
				{
					// If already existing employee added
					if(isset($request['selectEmp']))
					{
						$add['payrollemp_id'] 	 = $empid = $request['selectEmp'];
						$add['payrollcat_depid'] = $request['department'];
						$add['location'] 		 = Session::get('location_id');

						$laborHeader = PayrollEmployee_department::where($add)->first();

						// var_dump($laborHeader); exit();

						// if(count($laborHeader) == 0)
						if(is_null($laborHeader))
						{
							$add['satus'] = 0;
							$depDetail = PayrollEmployee_department::create($add);

							// NEW DEPARTMENT ADDED //
							$departmentInfo = PayrollDepartment::where('id', '=', $request['department'])->first();
							$employeeInfo   = PayrollEmployee::where('id', '=', $empid)->first();
							$new_record = 'Employee '.$employeeInfo->name.': New Department added: '.$departmentInfo->name;
							track_record($new_record, 3);
						}
						else if(Session::get('hideEmp') !== null)
						{
							$hideEmp = Session::get('hideEmp');

							if (($key = array_search($add['payrollemp_id'], $hideEmp)) !== false) {
								unset($hideEmp[$key]);
								// echo "<pre>"; print_r($hideEmp); exit();
								Session::put('hideEmp', $hideEmp);
							}
						}
					}
					// If new employee added
					else if(isset($request['inputname']))
					{
						$locationId  = Session::get('location_id');
						$queryData   = DB::table('locations')->select('rate')->where('status', '=', '1')->where('id', '=', $locationId)->first();
						$empName 	 = $request['inputname'];
						$rate 		 = (($request['inputrate'] == '')?$queryData->rate:$request['inputrate']);
						$salary 	 = $request['inputsalary'];
						$workinghour = $request['inputworkinghour'];
						$depId 	 	 = $request['department'];

						if(!empty($empName) && (!empty($rate)) )
						{
							$employee = PayrollEmployee::create([
								'name' 			 => $empName,
								'rateperhour' 	 => $rate,
								'salary' 		 => $salary,
								'working_hour' 	 => $workinghour,
								'employeeStatus' => 1,
							]);


							$lastInsertId = $empid = $employee->id;

							if($lastInsertId != '')
							{
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
							/*

							$ad['start_week']   = $request['wks'];
							$ad['end_week']     = $request['wke'];
							$ad['location_id']  = Session::get('location_id');
							$this->createLabourHeader(['week'=>10],['week'=>10]);
							die;
							*/
							// TRACK RECORDS //
							// $departmentInfo = PayrollDepartment::where('id', '=', $depId)->first();
							// $employee->department = $departmentInfo->name;
							track_record("Employee ".$empName. " added");
						}
					}

					if(isset($request['header_id']))
					{

						$req['cat'] = $request['cat'];
						$req['dep'] = $request['department'];
						$req['emp'] = $empid; // $request['selectEmp'];
						$req['weeknum'] = $request['weeknum'];
						$req['rate'] 	= 0;


						DB::transaction(function () use ($req, $request, $locationId) {
							DB::table('locations')->where('id', $locationId)->lockForUpdate()->first();
							Laborheadersections::where('id', $request['header_id'])->where('location_id', $locationId)->firstOrFail();
							$this->create_labor($req, $request['header_id']);
							$grdTotal = $this->calculate_totle($request['header_id']);
							Laborheadersections::where('id', $request['header_id'])->update(['total_amount' => $grdTotal]);
						});
					}
				}


			}

			return redirect()->route('laboursection.create');
		}

		public function hideEmployee(Request $request)
		{
			if(Session::get('hideEmp') !== null)
			{
				$hideEmp = Session::get('hideEmp');
				array_push($hideEmp, $request['emp']);
			}else
				$hideEmp = array($request['emp']);

			Session::put('hideEmp', $hideEmp);

			return redirect()->route('laboursection.create');

			// $b = Session::get('testing');
			// echo "<pre>"; print_r($b); exit();
		}

		public function dataSave(Request $request)
		{

			// echo "<pre>"; $x = $request->all(); var_dump($x); exit();

			$ad = array();
			$ad['start_week']   = $request['wks'];
			$ad['end_week']     = $request['wke'];
			$ad['location_id']  = Session::get('location_id');

		$result = 	$this->createLabourHeader($ad,$request);
			return json_encode($result);
		}

		function createLabourHeader($ad,$request){
			return DB::transaction(function () use ($ad, $request) {
			// Serialize manual saves with Excel import for this store/period.
			DB::table('locations')->where('id', $ad['location_id'])->lockForUpdate()->first();
			$header = Laborheadersections::where($ad)->first();

			if(is_null($header))
			{
				$ad['total_amount'] = $request['ttl'];
				$ad['start_date'] 	= $request['sdate'];
				$ad['end_date'] 	= $request['edate'];
				$header = Laborheadersections::create($ad);
				$cr_rec = $this->create_labor($request, $header->id);
				Laborheadersections::where('id', $header->id)->update(['total_amount' => $this->calculate_totle($header->id)]);
			}
			// RECORD EXISTS //
			else{
				$cr_rec   = $this->create_labor($request, $header->id);

				$grdTotal = $this->calculate_totle($header->id);

				$headerup = Laborheadersections::where('id','=', $header->id)->update(['total_amount' => $grdTotal]);

			}
			return 	$cr_rec;
			});
		}

	// CREATE RECORDS //
	// ######################################################## //




	// ######################################################## //
	// EDIT RECORDS //
		public function edit($id)
		{


			$locationId = Session::get('location_id');
			$queryData  = DB::table('locations')->select('rate','startdate')->where('status', '=', '1')->where('id', '=', $locationId)->first();
			abort_unless($queryData, 404);

			$default_rate = $queryData->rate;
			$header 	= Laborheadersections::where('id', "=" , $id)->where('location_id', $locationId)->firstOrFail();
			//dd($header );
		 	$weeksStoreOne = $header->start_week;
			$weeksStoreTwo = $header->end_week;
			$sdate = $header->start_date;
			$edate = $header->end_date;

			$finalStartDate = date("m-d-Y", strtotime($sdate));
			$finalEndDate = date("m-d-Y", strtotime($edate));
			$year = date('Y', strtotime($header->end_date));
			$location_year = date('Y',strtotime($queryData->startdate));
			$sDateStore  = $queryData->startdate;

			$payrollCategory   = DB::table('payroll_categories')
			->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')
			->where('payroll_categories.status', '=', '1')->where('payroll_category_locations.location', '=', $locationId)
			->select('payroll_categories.*')->orderBy('sorting_order','asc')->get();

			$payrollDepartment = DB::table('payroll_departments')
			->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')
			->where('payroll_departments.status', '=', '1')->where('payroll_department_locations.location', '=', $locationId)
			->select('payroll_departments.*')->orderBy('sorting_order','asc')->get();

			// dd($payrollDepartment);

			$empsLabor = array();
			$payrollDepartmentEmps = array();

			foreach ($payrollDepartment as $department)
			{
				$laborRec = Laboursection::where('header_id', '=', $header->id)
				->where('depId', '=', $department->id)->whereIn('weekNo' , [$header->start_week,$header->end_week])->orderBy('empId','asc')->get();

				$empid = array();
				foreach ($laborRec as $emplabor)
				{
					$empid[$emplabor->empId] = $emplabor->empId;
					$empsLabor[$emplabor->depId][$emplabor->empId][$emplabor->weekNo][$emplabor->catId] = $emplabor->rate;
				}


				$payrollDepartmentEmps[$department->id] = PayrollEmployee::whereIn('id', $empid)->get();
			}

			// echo "<pre>"; print_r($payrollDepartmentEmps); exit();

			$allPayrollEmps = PayrollEmployee::leftJoin('payroll_employee_departments', 'payroll_employees.id', '=', 'payroll_employee_departments.payrollemp_id')
				->leftJoin('payroll_employee_locations', 'payroll_employees.id', '=', 'payroll_employee_locations.payrollemp_id')
				->where('payroll_employee_departments.location', '=', $locationId)
				->where('payroll_employee_locations.payrollcat_depid', '=', $locationId)
				->select('payroll_employees.*')
				->get();

			$headerInfo = $header;


			Session::forget('checkDateType');
			Session::forget('checkDateTime');


			return view('laboursections.create', compact('headerInfo', 'payrollCategory', 'payrollDepartment', 'payrollDepartmentEmps', 'empsLabor', 'allPayrollEmps', 'weeksStoreOne', 'weeksStoreTwo', 'finalStartDate', 'finalEndDate', 'sdate', 'edate','default_rate','year','location_year','sDateStore'));
		}

		public function removeEmployee(Request $request)
		{
			// echo "<pre>"; print_r($request->all()); exit();
			$emp = $request['emp'];
			$dep = $request['dep'];
			$hd  = $request['hd'];

			DB::transaction(function () use ($emp, $dep, $hd) {
				DB::table('locations')->where('id', Session::get('location_id'))->lockForUpdate()->first();
				Laborheadersections::where('id', $hd)->where('location_id', Session::get('location_id'))->firstOrFail();
				Laboursection::where('empId', $emp)->where('depId', $dep)->where('header_id', $hd)->delete();

			$grdTotal = 	$this->calculate_totle($hd);
				$headerup = Laborheadersections::where('id','=', $hd)->update(['total_amount' => $grdTotal]);
			});

			return redirect()->action(
				'LaboursectionController@edit', $hd
			);
		}

		public function editEmployee(Request $request)
		{
			$locationId 	= Session::get('location_id');
			$emp_id  		= $request->emp_id;
			$headerid  		= $request->headerid;

			$empData 		= PayrollEmployee::where('id', '=', $emp_id)->get()->first();
			$departments 	= PayrollDepartment::leftJoin('payroll_department_locations', 'payroll_department_locations.payrolldep_id', '=', 'payroll_departments.id')->where('payroll_department_locations.location', '=', $locationId)->pluck('payroll_departments.name', 'payroll_departments.id'); // $this->departments;
			$selDepartments = PayrollEmployee_department::where('payrollemp_id', '=', $empData->id)->pluck('payrollcat_depid', 'id')->toArray();
			$locations 		= $this->locations;
			$sellocation 	= PayrollEmployee_location::where('payrollemp_id', '=', $empData->id)->pluck('payrollcat_depid', 'id')->toArray();
			$view 	 		= view("laboursections.edit-employee-box", compact('empData','departments','selDepartments','locations','sellocation', 'headerid'))->render();

			return response()->json(['html' => $view]);
		}

		public function updateEmp(Request $request)
		{
			// echo "<pre>"; print_r($request->all()); exit();

			$locationId 		= Session::get('location_id');
			$up['name'] 		= $request['name'];
			$up['salary'] 		= $request['salary'];
			$up['rateperhour'] 	= $request['rateperhour'];
			$up['sorting_order']= $request['sorting_order'];
			// $departmentAry 		= $request['department'];
			$header_id  = $request['header_id'];

			$old_record = PayrollEmployee::where('id', '=', $request['id'])->get()->first();
			PayrollEmployee::where('id', '=', $request['id'])->update($up);
			$new_record = PayrollEmployee::where('id', '=', $request['id'])->get()->first();

			track_record($new_record, 2, $old_record);

			if($header_id == 0)
				return redirect()->action(
					'LaboursectionController@create'
				);
			else
				return redirect()->action(
					'LaboursectionController@edit', $header_id
				);
		}
	// EDIT RECORDS //
	// ######################################################## //





	function weeks_between($datefrom, $dateto)
    {

     	$dayWeek 		= date('w', strtotime($datefrom));

		if((Int)$dayWeek > 5)
			$datefrom 	= date("Y-m-d", strtotime("+1 week", strtotime($datefrom)));

		$currWeek 	= date("w", strtotime($dateto));
		if((Int)$currWeek > 5)
			$dateto = date("Y-m-d", strtotime("+1 week", strtotime($dateto)));

		$datefrom 	= DateTime::createFromFormat('Y-m-d', $datefrom);
		$dateto 	= DateTime::createFromFormat('Y-m-d', $dateto);
		$interval 	= $datefrom->diff($dateto);

		$week_total = $interval->format('%a')/7;

		$weeksCount = (floor($week_total) + 1); // (floor($week_total)+1);

		if(($weeksCount%2 == 0))
			$weeksCount = (floor($week_total));

		return $weeksCount;
    }

    function add_weeks($date, $weeks)
    {
		$mydate  = strtotime('+'.($weeks-1).' weeks', strtotime($date));
		$dayWeek = date('w', strtotime($date));

		if($dayWeek != 6)
			$mydate = strtotime('previous saturday', $mydate);

		return date("Y-m-d", $mydate);
    }

    function calculate_totle($header_id)
    {
    	$locationId = Session::get('location_id');
			$payrollDepartment = DB::table('payroll_departments')->Join('payroll_department_locations', 'payroll_departments.id', '=', 'payroll_department_locations.payrolldep_id')->where('payroll_departments.status', '=', '1')->where('payroll_department_locations.location', '=', $locationId)->select('payroll_departments.*')->orderBy('sorting_order','asc')->get();

		// echo "<pre>";
		$grdTotal = 0;
		foreach ($payrollDepartment as $department)
		{
			$laborRec = Laboursection::leftJoin('payroll_categories', 'payroll_categories.id', '=', 'laboursections.catId')
				->leftJoin('payroll_employees', 'payroll_employees.id', '=', 'laboursections.empId')
				->select('laboursections.*', 'payroll_categories.*', 'payroll_employees.*', 'payroll_categories.name as catName')
				->where('header_id', '=', $header_id)
				->where('depId', '=', $department->id)
				->orderBy('empId','asc')
				->get();


			foreach ($laborRec as $emplabor)
			{

			 	$salary = ($emplabor->salary)?(Float)$emplabor->salary:"0.0";
				$total[$emplabor->depId][$emplabor->empId] = (isset($total[$emplabor->depId][$emplabor->empId]))?$total[$emplabor->depId][$emplabor->empId]:($salary * 2);


				$catname = strtolower(trim($emplabor->catName));
				if($catname == 'overtime'){
					$rate = $emplabor->rate?$emplabor->rate:0;
					$total[$emplabor->depId][$emplabor->empId] = ($total[$emplabor->depId][$emplabor->empId]) +  (($emplabor->rateperhour + ($emplabor->rateperhour / 2)) * $rate );
				}
          else if($catname == 'salary'){
          }
          else{

				 	$rate = $emplabor->rate?$emplabor->rate:0.0;
						$total[$emplabor->depId][$emplabor->empId] = $total[$emplabor->depId][$emplabor->empId] + ($rate  * $emplabor->rateperhour);


					}
			}

			if(isset($total[$department->id])){
				$grdTotal += array_sum($total[$department->id]);
			}

		}
		return $grdTotal;
    }



    protected function create_labor($request, $id)
    {
    	$ad = array();
		$ad['catId'] = $request['cat'];
		$ad['depId'] = $request['dep'];
		$ad['empId'] = $request['emp'];
		$ad['weekNo'] 	 = $request['weeknum'];
		$ad['header_id'] = $id;

		// echo "<pre>"; print_r($ad); /*exit();*/

		$rec = Laboursection::where($ad)->first();

		if(is_null($rec)){
			$ad['rate'] = $request['rate'];
			$cr_rec = Laboursection::create($ad);
		}
		else
		{
			$ad['rate'] = $request['rate'];
			Laboursection::where('id','=', $rec->id)->update($ad);
			$cr_rec = Laboursection::where('id','=', $rec->id)->first();
		}

		// var_dump($cr_rec); exit();

		return $cr_rec;
    }

    public function	history()
    {
    	$locationId = Session::get('location_id');
    	$track_emp = Track_employees::where("location_id", '=', $locationId)->orderBy('id', 'desc')->get();
    	return view('laboursections.history', compact('track_emp'));
    }

    // public function labor_report($weeknum)
    // {
    // 	$locationId = Session::get('location_id');
    // 	$queryData  = DB::table('locations')->select('startdate')->where('status', '=', '1')->where('id', '=', $locationId)->first();
	// 	$sDateStore = $queryData->startdate;

	// 	$stdate = $this->add_weeks($sDateStore, $weeknum);

    // 	Session::put('checkDateType', 1);

	// 	Session::put('checkDateTime', $stdate);
	// 	return redirect()->action('LaboursectionController@create');
    // }

	
	public function labor_report($weeknum)
	{
		$locationId = Session::get('location_id');

		$queryData = DB::table('locations')
			->select('startdate')
			->where('id', $locationId)
			->where('status', '=', '1')
			->first();

		if (!$queryData || empty($queryData->startdate)) {
			return response(
				'Labor Report cannot open: valid location start date not found.',
				422
			);
		}

		$sDateStore = $queryData->startdate;

		$stdate = $this->add_weeks($sDateStore, (int) $weeknum);

		Session::put('checkDateType', 1);
		Session::put('checkDateTime', $stdate);

		return redirect()->action([
			\App\Http\Controllers\LaboursectionController::class,
			'create'
		]);
	}

	function getWeekRangeNew($week, $startDate) {
	
		$timestamp = strtotime($startDate);
	
		$weekStart = strtotime("+".($week - 1)." weeks", $timestamp);
		
		$saturday = strtotime("last Saturday", $weekStart);
		
		$friday = strtotime("+6 days", $saturday);
	
		$firstDate = date("m-d-Y", $saturday);
		$lastDate = date("m-d-Y", $friday);
	
		return $firstDate . " to " . $lastDate;
	}
	
}
