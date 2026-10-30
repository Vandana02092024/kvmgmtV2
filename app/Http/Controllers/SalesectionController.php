<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Requests\SalesectionRequest;
use App\User;
use App\User_location;
use App\Location;
use App\Salesection;
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

class SalesectionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {


    // 	$locationId = Session::get('location_id');
    //   DB::connection()->enableQueryLog();

	//      $weekStartSql = 'DATE_SUB(dataDate, INTERVAL MOD(DAYOFWEEK(dataDate), 7) DAY)';
	//      $query = DB::table('salesections')
	//         ->select(['location_id', DB::raw('MAX(id) as id'), DB::raw('MAX(dataDate) as dataDate'), DB::raw('SUM(amount) as amount'), DB::raw($weekStartSql.' as periodStart')])
    //     ->where('location_id', '=', $locationId)
    //     ->where('nameDep', '=', 'sale')
	//         ->groupBy('location_id', DB::raw($weekStartSql))
	//         ->orderBy('periodStart', 'DESC')
	//         ->orderBy('dataDate', 'DESC');

	//     // $data = $query->paginate(10);
	// 	$data = $query->get();
	// 	$totalRecords = $data->count();
	//     foreach ($data as $weeklyReport) {
	//         $weekValues = explode('|', $this->weekDataValue($weeklyReport->periodStart));
	//         $weeklyReport->storeNo = isset($weekValues[1]) ? $weekValues[1] : 0;
	//         $weeklyReport->calendarWeek = getWeekByDate($weeklyReport->periodStart);
	//     }
    //   $queries = DB::getQueryLog();
    //   $last_query = end($queries);
	// 	//dd($last_query);

    //   	// $startDate = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status','=',1)->get();
    // 	// 	$sDateStore = strtotime($startDate[0]->startdate);

		
	// 	$startDate = DB::table('locations')
	// 		->select('startdate')
	// 		->where('id', '=', $locationId)
	// 		->where('status', '=', 1)
	// 		->first();

	// 	if (!$startDate || empty($startDate->startdate)) {
	// 		$sDateStore = null;
	// 	} else {
	// 		$sDateStore = strtotime($startDate->startdate);
	// 	}


	// 	return view('salesections.index', compact('data','sDateStore','totalRecords'));
	//   }


	// public function index()
    // {


    // 	$locationId = Session::get('location_id');
    //   DB::connection()->enableQueryLog();

	//      $weekStartSql = 'DATE_SUB(dataDate, INTERVAL MOD(DAYOFWEEK(dataDate), 7) DAY)';
	//      $query = DB::table('salesections')
	//         ->select(['location_id', DB::raw('MAX(id) as id'), DB::raw('MAX(dataDate) as dataDate'), DB::raw('SUM(amount) as amount'), DB::raw($weekStartSql.' as periodStart')])
    //     ->where('location_id', '=', $locationId)
    //     ->where('nameDep', '=', 'sale')
	//         ->groupBy('location_id', DB::raw($weekStartSql))
	//         ->orderBy('periodStart', 'DESC')
	//         ->orderBy('dataDate', 'DESC');

	//     // $data = $query->paginate(10);
	// 	$data = $query->paginate(10);
	// 	$totalRecords = $data->total();
	//     foreach ($data as $weeklyReport) {
	//         $weekValues = explode('|', $this->weekDataValue($weeklyReport->periodStart));
	//         $weeklyReport->storeNo = isset($weekValues[1]) ? $weekValues[1] : 0;
	//         $weeklyReport->calendarWeek = getWeekByDate($weeklyReport->periodStart);
	//     }
    //   $queries = DB::getQueryLog();
    //   $last_query = end($queries);
	// 	//dd($last_query);

    //   	// $startDate = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status','=',1)->get();
    // 	// 	$sDateStore = strtotime($startDate[0]->startdate);

		
	// 	$startDate = DB::table('locations')
	// 		->select('startdate')
	// 		->where('id', '=', $locationId)
	// 		->where('status', '=', 1)
	// 		->first();

	// 	if (!$startDate || empty($startDate->startdate)) {
	// 		$sDateStore = null;
	// 	} else {
	// 		$sDateStore = strtotime($startDate->startdate);
	// 	}


	// 	return view('salesections.index', compact('data','sDateStore','totalRecords'));
	// }

	public function index()
	{
		$locationId = Session::get('location_id');

		if (empty($locationId)) {
			return redirect()->back()->with(
				'message',
				'Please select a location first.'
			);
		}

		// Fetch the location start date only once.
		$location = DB::table('locations')
			->select('startdate')
			->where('id', $locationId)
			->where('status', '1')
			->first();

		$sDateStore = (
			$location && !empty($location->startdate)
		) ? strtotime($location->startdate) : null;

		// Group sales records by week.
		$weekStartSql = '
			DATE_SUB(
				dataDate,
				INTERVAL MOD(DAYOFWEEK(dataDate), 7) DAY
			)
		';

		$data = DB::table('salesections')
			->select([
				'location_id',
				DB::raw('MAX(id) as id'),
				DB::raw('MAX(dataDate) as dataDate'),
				DB::raw('SUM(amount) as amount'),
				DB::raw($weekStartSql . ' as periodStart')
			])
			->where('location_id', $locationId)
			->where('nameDep', 'sale')
			->groupBy(
				'location_id',
				DB::raw($weekStartSql)
			)
			->orderBy('periodStart', 'DESC')
			->orderBy('dataDate', 'DESC')
			->paginate(10);

		// Prepare the weekly report data.
		foreach ($data as $weeklyReport) {
			$weekValues = explode(
				'|',
				$this->weekDataValue($weeklyReport->periodStart)
			);

			$weeklyReport->storeNo = isset($weekValues[1])
				? $weekValues[1]
				: 0;

			$weeklyReport->calendarWeek = getWeekByDate(
				$weeklyReport->periodStart
			);
		}

		app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->attachWeekly($data, $locationId);
		return view(
			'salesections.index',
			compact('data', 'sDateStore')
		);
	}



	public function filterdata(Request $request , $count = null)
    {

    	$search = $request->query('search');
    	$from_date = $request->query('from');
    	$to_date = $request->query('to');

    	$count = empty($count) ? 10 : $count;
    	$locationId = Session::get('location_id');

	     $weekStartSql = 'DATE_SUB(dataDate, INTERVAL MOD(DAYOFWEEK(dataDate), 7) DAY)';
	     $query = DB::table('salesections')
	        ->select(['location_id', DB::raw('MAX(id) as id'), DB::raw('MAX(dataDate) as dataDate'), DB::raw('SUM(amount) as amount'), DB::raw($weekStartSql.' as periodStart')])
        ->where('location_id', '=', $locationId)
        ->where('nameDep', '=', 'sale')
	        ->groupBy('location_id', DB::raw($weekStartSql))
	        ->orderBy('periodStart', 'DESC')
        ->orderBy('dataDate', 'DESC');

         if (!empty($search)) {
	        $locationStartDate = DB::table('locations')->where('id', $locationId)->value('startdate');
	        $query->whereRaw('FLOOR(DATEDIFF('.$weekStartSql.', ?)/7)+1 = ?', [$locationStartDate, $search]);
	    }
	    if (!empty($from_date) && !empty($to_date)) {
	        $query->whereRaw($weekStartSql.' between ? and ?', [$from_date, $to_date]);
	    }
	    $data = $query->paginate($count);
	    foreach ($data as $weeklyReport) {
	        $weekValues = explode('|', $this->weekDataValue($weeklyReport->periodStart));
	        $weeklyReport->storeNo = isset($weekValues[1]) ? $weekValues[1] : 0;
	        $weeklyReport->calendarWeek = getWeekByDate($weeklyReport->periodStart);
	    }


		$startDate = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status', '=', '1')->first();
		$sDateStore = $startDate && !empty($startDate->startdate) ? strtotime($startDate->startdate) : null;

		app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->attachWeekly($data, $locationId);
    		return view('salesections.index', compact('data','sDateStore','count','from_date','to_date'));
	}

	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {

	// 	// $locationId = Session::get('location_id');
	// 	// $startDate  = DB::table('locations')->select('startdate','entity_name')->where('id', '=', $locationId)->where('status','=',1)->get();

	// 	// $startDatestore = date('m-d-Y',strtotime($startDate[0]->startdate));
	// 	// $dateInTwoWeeks = strtotime('+2 weeks');
	// 	// $wday = date('w', strtotime($dateInTwoWeeks));

	// 	// if($wday < 6)
	// 	// 	$dateInTwoWeeks = date('m-d-Y', strtotime('next Friday', $dateInTwoWeeks));
	// 	// else if($wday > 6)
	// 	// 	$dateInTwoWeeks = date('m-d-Y', strtotime('last Friday', $dateInTwoWeeks));
	// 	// else
	// 	// 	$dateInTwoWeeks = date('m-d-Y', strtotime($dateInTwoWeeks));

	// 	// $entity = $startDate[0]->entity_name;

		
	// 	// $locationId = Session::get('location_id');

	// 	$startDate = DB::table('locations')
	// 		->select('startdate', 'entity_name')
	// 		->where('id', $locationId)
	// 		->where('status', 1)
	// 		->first();

	// 	// $startDate = DB::table('locations')
	// 	// 	->select('startdate', 'entity_name')
	// 	// 	->where('id', '=', $locationId)
	// 	// 	->where('status', '=', 1)
	// 	// 	->first();

	// 	// if (!$startDate || empty($startDate->startdate)) {
	// 	// 	return redirect()->route('salesection.index')
	// 	// 		->with('error', 'The selected location is missing or has no start date configured.');
	// 	// }

	// 	// $startDatestore = date('m-d-Y', strtotime($startDate->startdate));

	// 	$startDate = DB::table('locations')
	// 		->select('startdate', 'entity_name')
	// 		->where('id', $locationId)
	// 		->where('status', 1)
	// 		->first();

	// 	if (!$startDate || empty($startDate->startdate)) {
	// 		return redirect('/salesection')
	// 			->with('message', 'Selected location is missing or its start date is not configured.');
	// 	}

	// 	$startDatestore = date('m-d-Y', strtotime($startDate->startdate));
	// 	$entity = $startDate->entity_name;

	// 	$dateInTwoWeeks = strtotime('+2 weeks');
	// 	$wday = date('w', $dateInTwoWeeks);

	// 	if ($wday < 6) {
	// 		$dateInTwoWeeks = date('m-d-Y', strtotime('next Friday', $dateInTwoWeeks));
	// 	} elseif ($wday > 6) {
	// 		$dateInTwoWeeks = date('m-d-Y', strtotime('last Friday', $dateInTwoWeeks));
	// 	} else {
	// 		$dateInTwoWeeks = date('m-d-Y', $dateInTwoWeeks);
	// 	}

	// 	$entity = $startDate->entity_name;


	// 	$saleData = DB::table('sales')->join('sale_locations', 'sales.id', '=', 'sale_locations.sale')->select('sales.id','sales.name','sales.status','sale_locations.sale','sale_locations.location','sale_locations.calWeekNo','sale_locations.storeWeekNo','sale_locations.saleStatus')->where('sales.status', '=', 1)->where('sale_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

	// 	$discountData = DB::table('discounts')->join('discount_locations', 'discounts.id', '=', 'discount_locations.discount')->select('discounts.id','discounts.name','discounts.status','discount_locations.discount','discount_locations.location','discount_locations.calWeekNo','discount_locations.storeWeekNo','discount_locations.disStatus')->where('discounts.status', '=', 1)->where('discount_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

	// 	$adjustmentData = DB::table('adjustments')->join('adjustment_locations', 'adjustments.id', '=', 'adjustment_locations.adjustment')->select('adjustments.id','adjustments.name','adjustments.status','adjustment_locations.adjustment','adjustment_locations.location','adjustment_locations.calWeekNo','adjustment_locations.storeWeekNo','adjustment_locations.adjStatus')->where('adjustments.status', '=', 1)->where('adjustment_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

	// 	$receiptData = DB::table('receipts')->join('receipt_locations', 'receipts.id', '=', 'receipt_locations.receipt')->select('receipts.id','receipts.name','receipts.status','receipt_locations.receipt','receipt_locations.location','receipt_locations.calWeekNo','receipt_locations.storeWeekNo','receipt_locations.recStatus')->where('receipts.status', '=', 1)->where('receipt_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();
	// 	$editWkNo = 0;

	// 	if(Session::has('checkType')) {

	// 	} else {
	// 		Session::forget('fetchData');
	// 		Session::forget('disData');
	// 		Session::forget('adjustData');
	// 		Session::forget('recData');
	// 		Session::forget('saleSum');
	// 		Session::forget('disSum');
	// 		Session::forget('adjSum');
	// 		Session::forget('recSum');
	// 	}
	// 	if(Session::has('fetchData')) {
	// 		$weekData = Session::get('fetchData');
	// 		$disData = Session::get('disData');
	// 		$adjustData = Session::get('adjustData');
	// 		$recData = Session::get('recData');
	// 		$saleSum = Session::get('saleSum');
	// 		$disSum = Session::get('disSum');
	// 		$adjSum = Session::get('adjSum');
	// 		$recSum = Session::get('recSum');

	// 		$sDate = Session::get('sDate');
	// 		$eDate = Session::get('eDate');
    //   if(!empty($weekData)){
    //     	$editWkNo = $weekData[0]->weekNo;
    //   }


	// 		Session::forget('checkType');
	// 	} else{

	// 		$sDate = date('Y-m-d',strtotime('last Saturday'));
	// 		$eDate = date('Y-m-d',strtotime('Friday'));
	// 		$weekData = DB::table('salesections')->join('sales', 'salesections.nameId', '=', 'sales.id')->select('salesections.*','sales.name')->whereBetween('salesections.dataDate',[$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'sale')->orderBy('sorting_order','asc')->get();

	// 		$disData = DB::table('salesections')->join('discounts', 'salesections.nameId', '=', 'discounts.id')->select('salesections.*','discounts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'dis')->orderBy('sorting_order','asc')->get();

	// 		$adjustData = DB::table('salesections')->join('adjustments', 'salesections.nameId', '=', 'adjustments.id')->select('salesections.*','adjustments.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'adj')->orderBy('sorting_order','asc')->get();

	// 		$recData = DB::table('salesections')->join('receipts', 'salesections.nameId', '=', 'receipts.id')->select('salesections.*','receipts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'rec')->orderBy('sorting_order','asc')->get();

	// 		$saleSum = DB::select("select `sales`.`name`, `sales`.`status`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `sales` left join `salesections` on `salesections`.`nameId` = `sales`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'sale') where sales.status = '1' group by `sales`.`name` order by `sales`.`sorting_order` asc");

	// 		$disSum = DB::select("select `discounts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `discounts` left join `salesections` on `salesections`.`nameId` = `discounts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'dis') where discounts.status = '1'  group by `discounts`.`name` order by `sorting_order` asc");

	// 		$adjSum = DB::select("select `adjustments`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `adjustments` left join `salesections` on `salesections`.`nameId` = `adjustments`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'adj') where adjustments.status = '1'  group by `adjustments`.`name` order by `sorting_order` asc");

	// 		$recSum = DB::select("select `receipts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `receipts` left join `salesections` on `salesections`.`nameId` = `receipts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'rec') where receipts.status = '1'  group by `receipts`.`name` order by `sorting_order` asc");

	// 	}

	// 	$queryMatch = $this->weekDataValue($sDate);
	// 	$queryMatchData = explode('|',$queryMatch);
	// 	$calendarWeekMatch = $queryMatchData[0];
	// 	$storeWeekMatch = $queryMatchData[1];


	// 	return view('salesections.create',compact('saleData','discountData','adjustmentData','receiptData','startDatestore','weekData','disData','adjustData','recData','sDate','eDate','saleSum','disSum','adjSum','recSum','calendarWeekMatch','storeWeekMatch','dateInTwoWeeks','entity','editWkNo'));
	// }

	
	public function create()
	{
		// Get the selected location from the session
		$locationId = Session::get('location_id');

		// Fetch location details
		$startDate = DB::table('locations')
			->select('startdate', 'entity_name')
			->where('id', $locationId)
			->first();

		// Validate location and start date
		if (!$startDate || empty($startDate->startdate)) {
			return redirect('/salesection')
				->with(
					'message',
					'Location not found or start date is not configured.'
				);
		}

		$startDatestore = date(
			'm-d-Y',
			strtotime($startDate->startdate)
		);

		$entity = $startDate->entity_name;

		// Calculate date two weeks from now
		$dateInTwoWeeks = strtotime('+2 weeks');
		$wday = date('w', $dateInTwoWeeks);

		if ($wday < 6) {
			$dateInTwoWeeks = date(
				'm-d-Y',
				strtotime('next Friday', $dateInTwoWeeks)
			);
		} elseif ($wday > 6) {
			$dateInTwoWeeks = date(
				'm-d-Y',
				strtotime('last Friday', $dateInTwoWeeks)
			);
		} else {
			$dateInTwoWeeks = date(
				'm-d-Y',
				$dateInTwoWeeks
			);
		}

		// Fetch active sales for the selected location
		// $saleData = DB::table('sales')
		// 	->join(
		// 		'sale_locations',
		// 		'sales.id',
		// 		'=',
		// 		'sale_locations.sale'
		// 	)
		// 	->select(
		// 		'sales.id',
		// 		'sales.name',
		// 		'sales.status',
		// 		'sale_locations.sale',
		// 		'sale_locations.location',
		// 		'sale_locations.calWeekNo',
		// 		'sale_locations.storeWeekNo',
		// 		'sale_locations.saleStatus'
		// 	)
		// 	->where('sales.status', '=', 1)
		// 	->where('sale_locations.location', '=', $locationId)
		// 	->orderBy('sorting_order', 'asc')
		// 	->get();

		$saleData = DB::table('sales')
			->join('sale_locations', 'sales.id', '=', 'sale_locations.sale')
			->select(
				'sales.id',
				'sales.name',
				'sales.status',
				'sale_locations.sale',
				'sale_locations.location',
				'sale_locations.calWeekNo',
				'sale_locations.storeWeekNo',
				'sale_locations.saleStatus'
			)
			->where('sales.status', '=', '1')
			->where('sale_locations.location', '=', $locationId)
			->orderBy('sales.sorting_order', 'asc')
			->get();

		$discountData = DB::table('discounts')
			->join(
				'discount_locations',
				'discounts.id',
				'=',
				'discount_locations.discount'
			)
			->select(
				'discounts.id',
				'discounts.name',
				'discounts.status',
				'discount_locations.discount',
				'discount_locations.location',
				'discount_locations.calWeekNo',
				'discount_locations.storeWeekNo',
				'discount_locations.disStatus'
			)
			->where('discounts.status', '=', '1')
			->where('discount_locations.location', '=', $locationId)
			->orderBy('discounts.sorting_order', 'asc')
			->get();

		$adjustmentData = DB::table('adjustments')
				->join(
					'adjustment_locations',
					'adjustments.id',
					'=',
					'adjustment_locations.adjustment'
				)
				->select(
					'adjustments.id',
					'adjustments.name',
					'adjustments.status',
					'adjustment_locations.adjustment',
					'adjustment_locations.location',
					'adjustment_locations.calWeekNo',
					'adjustment_locations.storeWeekNo',
					'adjustment_locations.adjStatus'
				)
				->where('adjustments.status', '=', '1')
				->where('adjustment_locations.location', '=', $locationId)
				->orderBy('adjustments.sorting_order', 'asc')
				->get();

		$receiptData = DB::table('receipts')
			->join(
				'receipt_locations',
				'receipts.id',
				'=',
				'receipt_locations.receipt'
			)
			->select(
				'receipts.id',
				'receipts.name',
				'receipts.status',
				'receipt_locations.receipt',
				'receipt_locations.location',
				'receipt_locations.calWeekNo',
				'receipt_locations.storeWeekNo',
				'receipt_locations.recStatus'
			)
			->where('receipts.status', '=', '1')
			->where('receipt_locations.location', '=', $locationId)
			->orderBy('receipts.sorting_order', 'asc')
			->get();

		// Fetch active discounts for the selected location
		// $discountData = DB::table('discounts')
		// 	->join(
		// 		'discount_locations',
		// 		'discounts.id',
		// 		'=',
		// 		'discount_locations.discount'
		// 	)
		// 	->select(
		// 		'discounts.id',
		// 		'discounts.name',
		// 		'discounts.status',
		// 		'discount_locations.discount',
		// 		'discount_locations.location',
		// 		'discount_locations.calWeekNo',
		// 		'discount_locations.storeWeekNo',
		// 		'discount_locations.disStatus'
		// 	)
		// 	->where('discounts.status', '=', 1)
		// 	->where('discount_locations.location', '=', $locationId)
		// 	->orderBy('sorting_order', 'asc')
		// 	->get();

		// // Fetch active adjustments for the selected location
		// $adjustmentData = DB::table('adjustments')
		// 	->join(
		// 		'adjustment_locations',
		// 		'adjustments.id',
		// 		'=',
		// 		'adjustment_locations.adjustment'
		// 	)
		// 	->select(
		// 		'adjustments.id',
		// 		'adjustments.name',
		// 		'adjustments.status',
		// 		'adjustment_locations.adjustment',
		// 		'adjustment_locations.location',
		// 		'adjustment_locations.calWeekNo',
		// 		'adjustment_locations.storeWeekNo',
		// 		'adjustment_locations.adjStatus'
		// 	)
		// 	->where('adjustments.status', '=', 1)
		// 	->where('adjustment_locations.location', '=', $locationId)
		// 	->orderBy('sorting_order', 'asc')
		// 	->get();

		// // Fetch active receipts for the selected location
		// $receiptData = DB::table('receipts')
		// 	->join(
		// 		'receipt_locations',
		// 		'receipts.id',
		// 		'=',
		// 		'receipt_locations.receipt'
		// 	)
		// 	->select(
		// 		'receipts.id',
		// 		'receipts.name',
		// 		'receipts.status',
		// 		'receipt_locations.receipt',
		// 		'receipt_locations.location',
		// 		'receipt_locations.calWeekNo',
		// 		'receipt_locations.storeWeekNo',
		// 		'receipt_locations.recStatus'
		// 	)
		// 	->where('receipts.status', '=', 1)
		// 	->where('receipt_locations.location', '=', $locationId)
		// 	->orderBy('sorting_order', 'asc')
		// 	->get();

		$editWkNo = 0;

		// Clear old session data when no checkType is present
		if (Session::has('checkType')) {

		} else {
			Session::forget('fetchData');
			Session::forget('disData');
			Session::forget('adjustData');
			Session::forget('recData');
			Session::forget('saleSum');
			Session::forget('disSum');
			Session::forget('adjSum');
			Session::forget('recSum');
		}

		// Restore data from session if available
		if (Session::has('fetchData')) {

			$weekData = Session::get('fetchData');
			$disData = Session::get('disData');
			$adjustData = Session::get('adjustData');
			$recData = Session::get('recData');

			$saleSum = Session::get('saleSum');
			$disSum = Session::get('disSum');
			$adjSum = Session::get('adjSum');
			$recSum = Session::get('recSum');

			$sDate = Session::get('sDate');
			$eDate = Session::get('eDate');

			// if (!empty($weekData)) {
			// 	$editWkNo = $weekData[0]->weekNo;
			// }

							
				if ($weekData->isNotEmpty()) {
					$editWkNo = $weekData->first()->weekNo;
				}


			Session::forget('checkType');

		} else {

			// Default reporting week: Saturday to Friday
			$sDate = date('Y-m-d', strtotime('last Saturday'));
			$eDate = date('Y-m-d', strtotime('Friday'));

			// Fetch weekly sales rows
			$weekData = DB::table('salesections')
				->join(
					'sales',
					'salesections.nameId',
					'=',
					'sales.id'
				)
				->select('salesections.*', 'sales.name')
				->whereBetween('salesections.dataDate', [$sDate, $eDate])
				->where('salesections.location_id', '=', $locationId)
				->where('salesections.nameDep', '=', 'sale')
				->orderBy('sorting_order', 'asc')
				->get();

			// Fetch weekly discount rows
			$disData = DB::table('salesections')
				->join(
					'discounts',
					'salesections.nameId',
					'=',
					'discounts.id'
				)
				->select('salesections.*', 'discounts.name')
				->whereBetween('salesections.dataDate', [$sDate, $eDate])
				->where('salesections.location_id', '=', $locationId)
				->where('salesections.nameDep', '=', 'dis')
				->orderBy('sorting_order', 'asc')
				->get();

			// Fetch weekly adjustment rows
			$adjustData = DB::table('salesections')
				->join(
					'adjustments',
					'salesections.nameId',
					'=',
					'adjustments.id'
				)
				->select('salesections.*', 'adjustments.name')
				->whereBetween('salesections.dataDate', [$sDate, $eDate])
				->where('salesections.location_id', '=', $locationId)
				->where('salesections.nameDep', '=', 'adj')
				->orderBy('sorting_order', 'asc')
				->get();

			// Fetch weekly receipt rows
			$recData = DB::table('salesections')
				->join(
					'receipts',
					'salesections.nameId',
					'=',
					'receipts.id'
				)
				->select('salesections.*', 'receipts.name')
				->whereBetween('salesections.dataDate', [$sDate, $eDate])
				->where('salesections.location_id', '=', $locationId)
				->where('salesections.nameDep', '=', 'rec')
				->orderBy('sorting_order', 'asc')
				->get();

			// Sales totals
			$saleSum = DB::select(
				"SELECT sales.name, sales.status,
					IFNULL(SUM(salesections.unit), 0) AS unit,
					IFNULL(SUM(salesections.amount), 0) AS amount
				FROM sales
				LEFT JOIN salesections
					ON salesections.nameId = sales.id
					AND salesections.dataDate BETWEEN ? AND ?
					AND salesections.location_id = ?
					AND salesections.nameDep = 'sale'
				WHERE sales.status = '1'
				GROUP BY sales.name
				ORDER BY sales.sorting_order ASC",
				[$sDate, $eDate, $locationId]
			);

			// Discount totals
			$disSum = DB::select(
				"SELECT discounts.name,
					IFNULL(SUM(salesections.unit), 0) AS unit,
					IFNULL(SUM(salesections.amount), 0) AS amount
				FROM discounts
				LEFT JOIN salesections
					ON salesections.nameId = discounts.id
					AND salesections.dataDate BETWEEN ? AND ?
					AND salesections.location_id = ?
					AND salesections.nameDep = 'dis'
				WHERE discounts.status = '1'
				GROUP BY discounts.name
				ORDER BY discounts.sorting_order ASC",
				[$sDate, $eDate, $locationId]
			);

			// Adjustment totals
			$adjSum = DB::select(
				"SELECT adjustments.name,
					IFNULL(SUM(salesections.unit), 0) AS unit,
					IFNULL(SUM(salesections.amount), 0) AS amount
				FROM adjustments
				LEFT JOIN salesections
					ON salesections.nameId = adjustments.id
					AND salesections.dataDate BETWEEN ? AND ?
					AND salesections.location_id = ?
					AND salesections.nameDep = 'adj'
				WHERE adjustments.status = '1'
				GROUP BY adjustments.name
				ORDER BY adjustments.sorting_order ASC",
				[$sDate, $eDate, $locationId]
			);

			// Receipt totals
			$recSum = DB::select(
				"SELECT receipts.name,
					IFNULL(SUM(salesections.unit), 0) AS unit,
					IFNULL(SUM(salesections.amount), 0) AS amount
				FROM receipts
				LEFT JOIN salesections
					ON salesections.nameId = receipts.id
					AND salesections.dataDate BETWEEN ? AND ?
					AND salesections.location_id = ?
					AND salesections.nameDep = 'rec'
				WHERE receipts.status = '1'
				GROUP BY receipts.name
				ORDER BY receipts.sorting_order ASC",
				[$sDate, $eDate, $locationId]
			);
		}

		// Get calendar and store week numbers
		$queryMatch = $this->weekDataValue($sDate);
		$queryMatchData = explode('|', $queryMatch);

		$calendarWeekMatch = $queryMatchData[0];
		$storeWeekMatch = $queryMatchData[1];

		// Return the create report view
		return view(
			'salesections.create',
			compact(
				'saleData',
				'discountData',
				'adjustmentData',
				'receiptData',
				'startDatestore',
				'weekData',
				'disData',
				'adjustData',
				'recData',
				'sDate',
				'eDate',
				'saleSum',
				'disSum',
				'adjSum',
				'recSum',
				'calendarWeekMatch',
				'storeWeekMatch',
				'dateInTwoWeeks',
				'entity',
				'editWkNo'
			)
		)->with('dailyMetrics', app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->week($locationId, $sDate, $eDate));
	}


	function getDuplicate(){
		$result = DB::select("SELECT * FROM `salesections` where location_id =9 and nameDep != 'sale' and dataDate BETWEEN '2023-10-14' and '2023-10-20' ORDER BY `salesections`.`dataDate` DESC");

	$duplicateObjects = $this->findDuplicateObjects($result);
	foreach ($duplicateObjects as $value) {
			DB::table('salesections')
			->where('id', $value->id)
			->delete();
	}


	}


	function findDuplicateObjects($array) {
		$keys = [];
		$duplicates = [];

		foreach ($array as $object) {
			$key = $object->nameId . '|' . $object->nameDep . '|' . $object->dataDate . '|' . $object->dataDay;

			// If the key is already encountered, it's a duplicate
			if (isset($keys[$key])) {
				$duplicates[] = $object;
			} else {
				$keys[$key] = true;
			}
		}

		return $duplicates;
	}
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SalesectionRequest $request)
    {

		Session::forget('fetchData');
		Session::forget('disData');
		Session::forget('adjustData');
		Session::forget('recData');

		Session::forget('saleSum');
		Session::forget('disSum');
		Session::forget('adjSum');
		Session::forget('recSum');
		Session::forget('sDate');
		Session::forget('eDate');
		$locationId = Session::get('location_id');
		$dates = explode(" - ",$request['datepick']);
		$firstDate = explode('-',$dates[0]);
		$sDate = $firstDate[2]."-".$firstDate[0]."-".$firstDate[1];
		if(isset($dates[1])) {
			$lastDate = explode('-',$dates[1]);
			$eDate = $lastDate[2]."-".$lastDate[0]."-".$lastDate[1];
		} else {
			$eDate = date("Y-m-d", strtotime($sDate. ' + 6 days'));
		}

		$fetchData = DB::table('salesections')->join('sales', 'salesections.nameId', '=', 'sales.id')->select('salesections.*','sales.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'sale')->orderBy('sorting_order','asc')->get();

		$disData = DB::table('salesections')->join('discounts', 'salesections.nameId', '=', 'discounts.id')->select('salesections.*','discounts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'dis')->orderBy('sorting_order','asc')->get();

		$adjustData = DB::table('salesections')->join('adjustments', 'salesections.nameId', '=', 'adjustments.id')->select('salesections.*','adjustments.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'adj')->orderBy('sorting_order','asc')->get();

		$recData = DB::table('salesections')->join('receipts', 'salesections.nameId', '=', 'receipts.id')->select('salesections.*','receipts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'rec')->orderBy('sorting_order','asc')->get();

		$saleSum = DB::select("select `sales`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `sales` left join `salesections` on `salesections`.`nameId` = `sales`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'sale') where sales.status = '1'  group by `sales`.`name` order by `sorting_order` asc");

		$disSum = DB::select("select `discounts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `discounts` left join `salesections` on `salesections`.`nameId` = `discounts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'dis') where discounts.status = '1' group by `discounts`.`name` order by `sorting_order` asc");

		$adjSum = DB::select("select `adjustments`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `adjustments` left join `salesections` on `salesections`.`nameId` = `adjustments`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'adj') where adjustments.status = '1'  group by `adjustments`.`name` order by `sorting_order` asc");

		$recSum = DB::select("select `receipts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `receipts` left join `salesections` on `salesections`.`nameId` = `receipts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'rec') where receipts.status = '1' group by `receipts`.`name` order by `sorting_order` asc");


		Session::put('fetchData', $fetchData);
		Session::put('disData', $disData);
		Session::put('adjustData', $adjustData);
		Session::put('recData', $recData);
		Session::put('saleSum', $saleSum);
		Session::put('disSum', $disSum);
		Session::put('adjSum', $adjSum);
		Session::put('recSum', $recSum);
		Session::put('sDate', $sDate);
		Session::put('eDate', $eDate);
		Session::put('checkType', 1);

		return redirect()->route('salesection.create');

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
		// Search/preview data is stored in session by the new-report form. Never
		// let that temporary date range override the persisted store-week being edited.
		foreach (['fetchData', 'disData', 'adjustData', 'recData', 'saleSum', 'disSum', 'adjSum', 'recSum', 'sDate', 'eDate', 'checkType'] as $sessionKey) {
			Session::forget($sessionKey);
		}

		$locationId 	= Session::get('location_id');
		// New list links use an explicit record- prefix so row IDs can never be
		// confused with numeric store-week numbers (which frequently overlap).
		$recordLink = strpos((string) $id, 'record-') === 0;
		$selectedSaleQuery = DB::table('salesections')->where('location_id', $locationId)->where('nameDep', 'sale');
		if ($recordLink) {
			$selectedSaleQuery->where('id', (int) substr($id, 7));
		} else {
			// Legacy numeric links continue to mean store-week, scoped to location.
			$selectedSaleQuery->where('storeNo', $id)->orderBy('dataDate', 'desc');
		}
		$selectedSale = $selectedSaleQuery->first();
		if (!$selectedSale) {
			return \Redirect::route('salesection.index')->with('message', 'Weekly sales report not found for the selected location.');
		}
		$selectedPeriod = getStartAndEndDatebyweek(getWeekByDate($selectedSale->dataDate), $selectedSale->dataDate);
		$salesection = DB::table('salesections')->where('location_id', $locationId)
			->where('nameDep', 'sale')
			->whereBetween('dataDate', [$selectedPeriod['start_date'], $selectedPeriod['end_date']])
			->orderBy('dataDate', 'desc')->get();
		// $startDate 		= DB::table('locations')->select('startdate','entity_name')->where('id', '=', $locationId)->where('status','=',1)->get();

		// $entity 		= $startDate[0]->entity_name;
		// $sDateStore 	= strtotime($startDate[0]->startdate);



		// $startDatestore = date('m-d-Y',strtotime($startDate[0]->startdate));

		
		$startDate = DB::table('locations')
			->select('startdate', 'entity_name')
			->where('id', $locationId)
			->first();

		if (!$startDate || empty($startDate->startdate)) {
			return redirect('/salesection')
				->with('message', 'Location not found or start date is not configured.');
		}

		$entity = $startDate->entity_name;
		$sDateStore = strtotime($startDate->startdate);
		$startDatestore = date('m-d-Y', $sDateStore);

		$dateInTwoWeeks = strtotime('+2 weeks');
		$wday = date('w', strtotime($dateInTwoWeeks));

		if($wday < 6)
			$dateInTwoWeeks = date('m-d-Y', strtotime('next Friday', $dateInTwoWeeks));
		else if($wday > 6)
			$dateInTwoWeeks = date('m-d-Y', strtotime('last Friday', $dateInTwoWeeks));
		else
			$dateInTwoWeeks = date('m-d-Y', strtotime($dateInTwoWeeks));

		// $saleData = DB::table('sales')->join('sale_locations', 'sales.id', '=', 'sale_locations.sale')->select('sales.id','sales.name','sales.status','sale_locations.sale','sale_locations.location','sale_locations.calWeekNo','sale_locations.storeWeekNo','sale_locations.saleStatus')->where('sales.status', '=', 1)->where('sale_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();



		// $discountData = DB::table('discounts')->join('discount_locations', 'discounts.id', '=', 'discount_locations.discount')->select('discounts.id','discounts.name','discounts.status','discount_locations.discount','discount_locations.location','discount_locations.calWeekNo','discount_locations.storeWeekNo','discount_locations.disStatus')->where('discounts.status', '=', 1)->where('discount_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		// $adjustmentData = DB::table('adjustments')->join('adjustment_locations', 'adjustments.id', '=', 'adjustment_locations.adjustment')->select('adjustments.id','adjustments.name','adjustments.status','adjustment_locations.adjustment','adjustment_locations.location','adjustment_locations.calWeekNo','adjustment_locations.storeWeekNo','adjustment_locations.adjStatus')->where('adjustments.status', '=', 1)->where('adjustment_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		// $receiptData = DB::table('receipts')->join('receipt_locations', 'receipts.id', '=', 'receipt_locations.receipt')->select('receipts.id','receipts.name','receipts.status','receipt_locations.receipt','receipt_locations.location','receipt_locations.calWeekNo','receipt_locations.storeWeekNo','receipt_locations.recStatus')->where('receipts.status', '=', 1)->where('receipt_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		$saleData = DB::table('sales')
			->join('sale_locations', 'sales.id', '=', 'sale_locations.sale')
			->select(
				'sales.id',
				'sales.name',
				'sales.status',
				'sale_locations.sale',
				'sale_locations.location',
				'sale_locations.calWeekNo',
				'sale_locations.storeWeekNo',
				'sale_locations.saleStatus'
			)
			->where('sales.status', '=', '1')
			->where('sale_locations.location', '=', $locationId)
			->orderBy('sales.sorting_order', 'asc')
			->get();

		$discountData = DB::table('discounts')
			->join(
				'discount_locations',
				'discounts.id',
				'=',
				'discount_locations.discount'
			)
			->select(
				'discounts.id',
				'discounts.name',
				'discounts.status',
				'discount_locations.discount',
				'discount_locations.location',
				'discount_locations.calWeekNo',
				'discount_locations.storeWeekNo',
				'discount_locations.disStatus'
			)
			->where('discounts.status', '=', '1')
			->where('discount_locations.location', '=', $locationId)
			->orderBy('discounts.sorting_order', 'asc')
			->get();

		$adjustmentData = DB::table('adjustments')
				->join(
					'adjustment_locations',
					'adjustments.id',
					'=',
					'adjustment_locations.adjustment'
				)
				->select(
					'adjustments.id',
					'adjustments.name',
					'adjustments.status',
					'adjustment_locations.adjustment',
					'adjustment_locations.location',
					'adjustment_locations.calWeekNo',
					'adjustment_locations.storeWeekNo',
					'adjustment_locations.adjStatus'
				)
				->where('adjustments.status', '=', '1')
				->where('adjustment_locations.location', '=', $locationId)
				->orderBy('adjustments.sorting_order', 'asc')
				->get();

		$receiptData = DB::table('receipts')
			->join(
				'receipt_locations',
				'receipts.id',
				'=',
				'receipt_locations.receipt'
			)
			->select(
				'receipts.id',
				'receipts.name',
				'receipts.status',
				'receipt_locations.receipt',
				'receipt_locations.location',
				'receipt_locations.calWeekNo',
				'receipt_locations.storeWeekNo',
				'receipt_locations.recStatus'
			)
			->where('receipts.status', '=', '1')
			->where('receipt_locations.location', '=', $locationId)
			->orderBy('receipts.sorting_order', 'asc')
			->get();

		if(Session::has('checkType')) {

		} else {
			Session::forget('fetchData');
			Session::forget('disData');
			Session::forget('adjustData');
			Session::forget('recData');
			Session::forget('saleSum');
			Session::forget('disSum');
			Session::forget('adjSum');
			Session::forget('recSum');
		}

		if(Session::has('fetchData')) {
			$weekData = Session::get('fetchData');
			$disData = Session::get('disData');
			$adjustData = Session::get('adjustData');
			$recData = Session::get('recData');
			$saleSum = Session::get('saleSum');
			$disSum = Session::get('disSum');
			$adjSum = Session::get('adjSum');
			$recSum = Session::get('recSum');

			$sDate = Session::get('sDate');
			$eDate = Session::get('eDate');
			Session::forget('checkType');
		} else
		{
			$week = $id;
			/*$year = date("Y",strtotime($salesection[0]->dataDate));
			$time = strtotime("1 January $year", time());
			$day = date('w', $time);
			$time += ((7*$week)+1-$day)*24*3600;
			$dates[0] = date('Y-n-j', $time);
			$time += 6*24*3600;
			$dates[1] = date('Y-n-j', $time);
			$startWeek = strtotime ( '-2 day' , strtotime ( $dates[0] ) ) ;
			$endWeek = strtotime ( '-2 day' , strtotime ( $dates[1] ) ) ;
			$sDate = date ( 'Y-m-d' , $startWeek );
			$eDate = date ( 'Y-m-d' , $endWeek ); */


			$period = getStartAndEndDatebyweek(getWeekByDate($salesection[0]->dataDate), $salesection[0]->dataDate);
			$sDate = $period['start_date'];
			$eDate = $period['end_date'];
			/*$dayWeek =  date('w', $sDateStore);
	     	$addWeek = $week;
			$lastSatFormat =  date("Y-m-d", $sDateStore);
		    if($dayWeek != 6)
			{
				if($dayWeek == '0')
				{
					$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore));
					$new_date= date("Y-m-d", strtotime("$lastSatFormat +".($addWeek)." week"));
				}
				else
				{
					$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore));
					$new_date= date("Y-m-d", strtotime("$lastSatFormat +".($addWeek - 1)." week"));
				}

				// $new_date= date("Y-m-d", strtotime("$lastSatFormat +".($addWeek)." week"));
        	}
			else
			{
				$addWeek = $week-1;
				$new_date= date("Y-m-d", strtotime("$lastSatFormat +".($addWeek-1)." week"));
          	}

			$sDate = date ( 'Y-m-d' , strtotime($new_date));
			$eDate = date ( 'Y-m-d' , strtotime("$sDate + 6 day"));*/

			//$sDate = date('Y-m-d',strtotime('last Saturday'));
			//$eDate = date('Y-m-d',strtotime('Friday'));
		$weekData = DB::table('salesections')->join('sales', 'salesections.nameId', '=', 'sales.id')->select('salesections.*','sales.name')->whereBetween('salesections.dataDate',[$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'sale')->orderBy('sorting_order','asc')->get();

			$disData = DB::table('salesections')->join('discounts', 'salesections.nameId', '=', 'discounts.id')->select('salesections.*','discounts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'dis')->orderBy('sorting_order','asc')->get();

			$adjustData = DB::table('salesections')->join('adjustments', 'salesections.nameId', '=', 'adjustments.id')->select('salesections.*','adjustments.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'adj')->orderBy('sorting_order','asc')->get();

			$recData = DB::table('salesections')->join('receipts', 'salesections.nameId', '=', 'receipts.id')->select('salesections.*','receipts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'rec')->orderBy('sorting_order','asc')->get();
     //DB::enableQueryLog();

			$saleSum = DB::select("select `sales`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `sales` left join `salesections` on `salesections`.`nameId` = `sales`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'sale')  where sales.status = '1' group by `sales`.`name` order by `sales`.`sorting_order` asc");
		//  dd(DB::getQueryLog());

			$disSum = DB::select("select `discounts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `discounts` left join `salesections` on `salesections`.`nameId` = `discounts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'dis') where discounts.status = '1'  group by `discounts`.`name` order by `sorting_order` asc");

			$adjSum = DB::select("select `adjustments`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `adjustments` left join `salesections` on `salesections`.`nameId` = `adjustments`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'adj') where adjustments.status = '1'  group by `adjustments`.`name` order by `sorting_order` asc");

			$recSum = DB::select("select `receipts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `receipts` left join `salesections` on `salesections`.`nameId` = `receipts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'rec') where receipts.status = '1'  group by `receipts`.`name` order by `sorting_order` asc");

		}

		$payrollCategory   = DB::table('payroll_categories')->Join('payroll_category_locations', 'payroll_categories.id', '=', 'payroll_category_locations.payrollcat_id')->where('payroll_categories.status', '=', '1')->where('payroll_category_locations.location', '=', $locationId)->select('payroll_categories.*')->orderBy('sorting_order','asc')->get();

		$queryMatch 		= $this->weekDataValue($sDate);
		$queryMatchData 	= explode('|',$queryMatch);
		$calendarWeekMatch 	= getWeekByDate($sDate);
		$storeWeekMatch 	= $queryMatchData[1];
		$id = $storeWeekMatch;


		$editWkNo = getWeekByDate($salesection[0]->dataDate) ;
		//echo "<pre>"; print_r($editWkNo); exit();
		 return view('salesections.create',compact('payrollCategory','saleData','discountData','adjustmentData','receiptData','startDatestore','weekData','disData','adjustData','recData','sDate','eDate','saleSum','disSum','adjSum','recSum','calendarWeekMatch','storeWeekMatch','entity','dateInTwoWeeks','editWkNo','id'))->with('dailyMetrics', app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->week($locationId, $sDate, $eDate));

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
    public function destroy(Salesection $salesection)
    {
       $reimbursement->delete();
	   $reimburse = Reimbursement_describe_item::where('reimburse_id', '=', $reimbursement['id'])->delete();
       return redirect()->route('reimbursement.index')->with('message','Reimbursement report has been deleted successfully!');
    }

	// public function weekDataChanged(Request $request)
 //    {

	// 	/* calendar week */
	// 	/*$curYear = date("Y");
	// 	$sDate =  strtotime("$curYear-01-01");
	// 	$eDate =  strtotime($request['stDate']);
	// 	$datediff = $eDate - $sDate;
	// 	$days = ($datediff / (60 * 60 * 24));
	// 	$weeks = ceil($days/7); */

	// 	/* $weeks = date('W', strtotime($request['stDate']));
	// 	$locationId = Session::get('location_id');
	// 	$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
	// 	$sDateStore =  strtotime($queryData[0]->startdate);
	// 	$eDateStore =  strtotime($request['stDate']);
	// 	$datediffStore = $eDateStore - $sDateStore;
	// 	$daysStore = ($datediffStore / (60 * 60 * 24));
	// 	$weeksStore = ceil($daysStore/7); */

	// 	$curYear 	= date("Y");
	// 	$sDate 		= strtotime("$curYear-01-01");
	// 	$eDate 		= strtotime($request['stDate']);
	// 	$datediff 	= $eDate - $sDate;
	// 	$days 		= ($datediff / (60 * 60 * 24));
	// 	$weeks 		= ceil($days/7);
	// 	/* end */
	// 	/* store week no */

	// 	$locationId = Session::get('location_id');
	// 	$queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
	// 	$sDateStore = strtotime($queryData[0]->startdate);



	// 	$dayWeek =  date('w', $sDateStore);
	// 	// echo $dayWeek; exit();
	// 	$lastSatFormat =  date("Y-m-d", $sDateStore);

	// 	// if($dayWeek > 5)
	// 	// 	$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", strtotime($sDateStore)));







	// 	$eDateStore =  strtotime($request['stDate']);

	// 	if($dayWeek > 5){
	// 		$minDay = $dayWeek - 5;
	// 		$xDateStore = strtotime ( '-'.($minDay+1).' day' , $sDateStore );
	// 		$datediffStore = $eDateStore - $xDateStore;
	// 		// echo $request['stDate'];
	// 		// echo date("Y-m-d", $sDateStore); exit();
	// 	}else{
	// 		$datediffStore = $eDateStore - $sDateStore;
	// 		// echo $request['stDate']; // exit();
	// 		// echo $queryData[0]->startdate; exit();
	// 	}


	// 	$daysStore = ($datediffStore / (60 * 60 * 24));

	// 	$weeksStoreOne = (int)($daysStore/7);
	// 	$weeksStore = $weeksStoreOne + 1;

	// 	/*$addWeek = $weeksStore-1;
	// 	$new_date= date("Y-m-d", strtotime("$lastSatFormat +".$addWeek." week"));*/
	// 	$weeks = date('W', strtotime($request['stDate']));
	// 	return $weeks."|".$weeksStore;

	// }


  	function weeks_between($datefrom, $dateto)
      {
      //  echo $datefrom.'==='.$dateto; die;
     	$dayWeek 		= date('w', strtotime($datefrom));
  		if((Int)$dayWeek > 5){
      // $datefrom 	= date("Y-m-d", strtotime("+1 week", strtotime($datefrom)));
      }

  		$currWeek 	= date("w", strtotime($dateto));
  		if((Int)$currWeek > 5){
       $dateto = date("Y-m-d", strtotime("+1 week", strtotime($dateto)));
      }


  		$datefrom 	= DateTime::createFromFormat('Y-m-d', $datefrom);
  		$dateto 	= DateTime::createFromFormat('Y-m-d', $dateto);
  		$interval 	= $datefrom->diff($dateto);
  		$week_total = $interval->format('%a')/7;
//echo $week_total.'----'.floor($week_total);


  		if(($week_total%2 == 0)){
          $weeksCount = (floor($week_total));
      }else {
         $weeksCount = (floor($week_total)+1); // (floor($week_total)+1);
      }

  		return $weeksCount;
      }

	public function weekData(Request $request)
    {
		$endDate = $request['eDate'];
		return $queryMatch 		= $this->weekDataValue($endDate);
		// $locationId = Session::get('location_id');
		// $queryData 	= DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->first();
		// $sDateStore = strtotime($queryData->startdate);
    	// $weeks = date('W', strtotime($request['eDate']));
    	// $newStoreWeek = $this->weeks_between($queryData->startdate,$request->stDate);
		// return $weeks."|".$newStoreWeek;

		# kajal Experiment
		// $locationId = Session::get('location_id');
		// $endDate = $request['eDate'];
		// $queryData = DB::table('location_daterange')->where('location_id', $locationId)->whereRaw('? between start_date and end_date', [$endDate])->first();

		// $newStoreWeek = 0;
		// if ($queryData) {
		// 	if ($endDate == $queryData->end_date) {
		// 		$weeksArray   = explode(',', $queryData->week);
		// 		$newStoreWeek = isset($weeksArray[1]) ? $weeksArray[1] : 0;
		// 	} elseif ($endDate < $queryData->end_date) {
		// 	$weeksArray = explode(',', $queryData->week);
		// 	$newStoreWeek = isset($weeksArray[0]) ? $weeksArray[0] : 0;
		// 	}
		// }

		// $weeks = date('W', strtotime($endDate));
		// return $weeks."|".$newStoreWeek;
		# End

	}

	public function dataSaveSales(Request $request)
		{
			if (($request->has('allsaledata') && !is_string($request->input('allsaledata'))) ||
				($request->has('daily_metrics') && !is_string($request->input('daily_metrics')))) {
				return response()->json(['error' => 'Invalid daily sales or customer counts.'], 422);
			}
			$allsaledata = json_decode(stripslashes($request->input('allsaledata', '[]')));
			$metricEntries = $request->has('daily_metrics') ? json_decode($request->input('daily_metrics'), true) : null;
			if (!is_array($allsaledata) || ($request->has('daily_metrics') && !is_array($metricEntries))) {
				return response()->json(['error' => 'Invalid daily sales or customer counts.'], 422);
			}
			// Share the import's store lock before looking up or creating daily
			// rows, including when a day has no entries yet.
			try {
			return DB::transaction(function () use ($allsaledata, $metricEntries) {
			$locationId = Session::get('location_id');
			$location = DB::table('locations')->where('id', $locationId)->where('status', '1')->lockForUpdate()->first();
			abort_unless($location && Auth::user() && Auth::user()->status &&
				DB::table('user_locations')->where('user', Auth::id())->where('location', $locationId)->exists(), 403);
			if ($metricEntries !== null) {
				app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->saveManual($locationId, Auth::id(), $metricEntries);
			}

			// echo "<pre>"; print_r($allsaledata);exit;
			foreach($allsaledata as $sk => $sv) {
				if (!is_object($sv) || !isset($sv->nameID, $sv->unit, $sv->dayname, $sv->val) ||
					!is_string($sv->nameID) || !preg_match('/^(sale|dis|adj|rec)_[0-9]+$/', $sv->nameID) ||
					!in_array($sv->unit, ['units', 'amt'], true)) {
					throw new \RuntimeException('Invalid sales field. Customer counts must use their dedicated fields.');
				}
				$locationId = Session::get('location_id');
				$unitval = $amtval = '';

				 $name = $sv->name;
				 $unit = $sv->unit;
				 $dayno = $sv->dayno;
				 $val = $sv->val;

				$dayname = explode(' ',$sv->dayname);
				$dateData = explode('-',$dayname[1]);
				$dateD = $dateData[2]."-".$dateData[0]."-".$dateData[1];

				$nameID = explode('_',$sv->nameID);
				if($unit == 'units'){
					$unitval = $sv->val;
				}
				if($unit == 'amt'){
					$amtval = $sv->val;
				}

				$weekNo  = $sv->weekNo;
				$storeNo = $sv->storeNo;

				$rowCOllection = DB::table('salesections')->select('id')->where('nameId', $nameID[1])->where('nameDep', $nameID[0])->where('dataDate', $dateD)->where('storeNo', $storeNo)->where('location_id', $locationId)->get();

				$rowCount = count($rowCOllection);
				if($rowCount > 0){
					 $wordCount = $rowCOllection[0]->id;
					if($unit == 'units'){
						$updateArray = array(
						'unit' => $unitval,
						'weekNo' => $weekNo
						);
					}
					if($unit == 'amt'){
						$updateArray = array(
						'amount' => $amtval,
						'weekNo' => $weekNo
						);
					}
				//$salesection = Salesection::where('id','=', $wordCount)->update($updateArray);
				// echo $nameID[0]; exit;
					// DB::enableQueryLog();

				$salesection = Salesection::where('nameId', $nameID[1])->where('nameDep', $nameID[0])->where('dataDate', $dateD)->where('storeNo', $storeNo)->where('storeNo', $storeNo)->where('location_id', $locationId)->update($updateArray);

				// $query = DB::getQueryLog();
				// $lastQuery = end($query);
				// print_r($lastQuery);

				} else if($val != '') {
					$insertArray = array(
						'nameDep' => $nameID[0],
						'nameId' => $nameID[1],
						'dataDate' => $dateD,
						'dataDay' => $dayname[0],
						'unit' => $unitval,
						'amount' => $amtval,
						'weekNo' => $weekNo,
						'storeNo' => $storeNo,
						'location_id' => $locationId
					);


					$salesection = Salesection::create($insertArray);
				}
			}
			return response('1');
			});
			} catch (\Throwable $exception) {
				if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) throw $exception;
				\Log::warning('Sales daily save failed', ['reason' => $exception->getMessage()]);
				$message = $exception instanceof \RuntimeException && !($exception instanceof \Illuminate\Database\QueryException)
					? $exception->getMessage() : 'The report could not be saved. No changes were stored.';
				return response()->json(['error' => $message], 422);
			}
		}


		public function commentSave(Request $request)
		{
			$locationId = Session::get('location_id');
			$message = $request['message'];
			$name = $request['name'];
			$unit = $request['unit'];
			$day = $request['day'];

			$nameId = explode('_',$request['nameId']);
			$weekNo = $request['popupweekNo'];
			$storeNo = $request['popupStorNo'];
			$dayname = explode(' ',$request['popdayname']);
			$dayNamefull = $dayname[0];
			$dateData = explode('-',$dayname[1]);
			$dateD = $dateData[2]."-".$dateData[0]."-".$dateData[1];
			$unitval = $amtval = '';
			$rowCOllection = DB::table('salesections')->select('id')->where('nameId', $nameId[1])->where('nameDep', $nameId[0])->where('dataDate', $dateD)->where('dataDay', $dayNamefull)->where('weekNo', $weekNo)->where('storeNo', $storeNo)->where('location_id', $locationId)->get();
			$rowCount = count($rowCOllection);
			if($rowCount > 0){
					$Id = $rowCOllection[0]->id;
					$updateArray = array(
					'comment' => $message
					);


				$salesection = Salesection::where('id','=', $Id)->update($updateArray);
			}else {
			$insertArray = array(
				'nameDep' => $nameId[0],
				'nameId' => $nameId[1],
				'dataDate' => $dateD,
				'dataDay' => $dayNamefull,
				'unit' => $unitval,
				'amount' => $amtval,
				'weekNo' => $weekNo,
				'storeNo' => $storeNo,
				'location_id' => $locationId,
				'comment' => $message
			);

			$salesection = Salesection::create($insertArray);
			}
			return "complete";

		}

		public function getComment(Request $request)
		{
			error_reporting(0);
			$locationId = Session::get('location_id');
			$name = $request['name'];
			$unit = $request['unit'];
			$day = $request['day'];

			$nameId = explode('_',$request['nameId']);
			$weekNo = $request['popupweekNo'];
			$storeNo = $request['popupStorNo'];
			$dayname = explode(' ',$request['popdayname']);
			$dayNamefull = $dayname[0];
			$dateData = explode('-',$dayname[1]);
			$dateD = $dateData[2]."-".$dateData[0]."-".$dateData[1];
			$unitval = $amtval = '';
			$rowCOllection = DB::table('salesections')->select('comment')->where('nameId', $nameId[1])->where('nameDep', $nameId[0])->where('dataDate', $dateD)->where('dataDay', $dayNamefull)->where('weekNo', $weekNo)->where('storeNo', $storeNo)->where('location_id', $locationId)->get();

			$rowCount = count($rowCOllection);
			if($rowCount > 0){
				$comment = $rowCOllection[0]->comment;
			} else {
				$comment = '';
			}
			return $comment;

		}

		public function newFieldSaveSales(Request $request)
		{

			$locationId = Session::get('location_id');
			$inputvalue = $request['inputvalue'];
			$radioValue = $request['radioValue'];
			$depName = $request['depName'];
			$weekNo = $request['weekNo'];
			$calWeekNo = $request['calWeek'];

			if($radioValue == 'permanent' && $depName == 'sale'){
					$sale = Sale::create([
					'name' => $inputvalue,
					]);
					$locVal = $locationId;
						if($locVal != '')
							{
								Sale_location::create([
									'sale' => $sale->id,
									'location' => $locVal,
								]);
							}
			}
			if($radioValue == 'permanent' && $depName == 'dis'){
						$discount = Discount::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Discount_location::create([
									'discount' => $discount->id,
									'location' => $locVal,
								]);
							}
			}
			if($radioValue == 'permanent' && $depName == 'adj'){
					$adjustment = Adjustment::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Adjustment_location::create([
									'adjustment' => $adjustment->id,
									'location' => $locVal,
								]);
							}
			}
			if($radioValue == 'permanent' && $depName == 'rec'){
					$receipt = Receipt::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Receipt_location::create([
									'receipt' => $receipt->id,
									'location' => $locVal,
								]);
							}
			}

			if($radioValue == 'current' && $depName == 'sale'){
					$sale = Sale::create([
					'name' => $inputvalue,
					]);
					$locVal = $locationId;
						if($locVal != '')
							{
								Sale_location::create([
									'sale' => $sale->id,
									'location' => $locVal,
									'calWeekNo' => $calWeekNo,
									'storeWeekNo' => $weekNo,
									'saleStatus' => 1,
								]);
							}
			}
			if($radioValue == 'current' && $depName == 'dis'){
					$discount = Discount::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Discount_location::create([
									'discount' => $discount->id,
									'location' => $locVal,
									'calWeekNo' => $calWeekNo,
									'storeWeekNo' => $weekNo,
									'disStatus' => 1,
								]);
							}
			}
			if($radioValue == 'current' && $depName == 'adj'){
				$adjustment = Adjustment::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Adjustment_location::create([
									'adjustment' => $adjustment->id,
									'location' => $locVal,
									'calWeekNo' => $calWeekNo,
									'storeWeekNo' => $weekNo,
									'adjStatus' => 1,
								]);
							}
			}
			if($radioValue == 'current' && $depName == 'rec'){
					$receipt = Receipt::create([
							'name' => $inputvalue,
						]);
						$locVal = $locationId;
						if($locVal != '')
							{
								Receipt_location::create([
									'receipt' => $receipt->id,
									'location' => $locVal,
									'calWeekNo' => $calWeekNo,
									'storeWeekNo' => $weekNo,
									'recStatus' => 1,
								]);
							}
			}
		return "complete";
		}

	public function weekDataValue($startDate)
    {
		/* calendar week */
			$curYear = date("Y");
			$sDate =  strtotime("$curYear-01-01");
			$eDate =  strtotime($startDate);
			$datediff = $eDate - $sDate;
			$days = ($datediff / (60 * 60 * 24));
			$weeks = ceil($days/7);
		/* end */
		/* store week no */
		// $locationId = Session::get('location_id');
		// $queryData = DB::table('locations')->select('startdate')->where('status', '=', 1)->where('id', '=', $locationId)->get();
		// $sDateStore =  strtotime($queryData[0]->startdate);

		$locationId = Session::get('location_id');

		$queryData = DB::table('locations')
			->select('startdate')
			->where('id', $locationId)
			->where('status', '1')
			->first();

		if (!$queryData || empty($queryData->startdate)) {
			return $weeks . '|0';
		}

		$sDateStore = strtotime($queryData->startdate);

		$dayWeek =  date('w', $sDateStore);
		$lastSatFormat =  date("Y-m-d", $sDateStore);
		if($dayWeek > 5)
			$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore));

		$eDateStore =  strtotime($startDate);
		$datediffStore = $eDateStore - $sDateStore;
		$daysStore = ($datediffStore / (60 * 60 * 24));

		$weeksStoreOne = (int)($daysStore/7);
		$weeksStore = $weeksStoreOne + 1;


		$addWeek = $weeksStore-1;
		$new_date= date("Y-m-d", strtotime("$lastSatFormat +".$addWeek." week"));
		$weeks = date ( 'W' , strtotime($new_date));

		//$weeksStore = ceil($daysStore/7);
		/* end */
		//
		return $weeks."|".$weeksStore;

	}
	public function printData($id){

		$locationId = Session::get('location_id');
		// $startDate = DB::table('locations')->select('startdate')->where('id', '=', $locationId)->where('status','=',1)->get();
		// $sDateStore = strtotime($startDate[0]->startdate);
		// $startDatestore = date('m-d-Y',strtotime($startDate[0]->startdate));

		$startDate = DB::table('locations')
			->select('startdate')
			->where('id', $locationId)
			->where('status', '1')
			->first();

		if (!$startDate || empty($startDate->startdate)) {
			return redirect('/salesection')->with(
				'message',
				'Location not found or start date is not configured.'
			);
		}

		$sDateStore = strtotime($startDate->startdate);
		$startDatestore = date('m-d-Y', $sDateStore);

		$saleData = DB::table('sales')->join('sale_locations', 'sales.id', '=', 'sale_locations.sale')->select('sales.id','sales.name','sales.status','sale_locations.sale','sale_locations.location','sale_locations.calWeekNo','sale_locations.storeWeekNo','sale_locations.saleStatus')->where('sales.status', '=', 1)->where('sale_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		$discountData = DB::table('discounts')->join('discount_locations', 'discounts.id', '=', 'discount_locations.discount')->select('discounts.id','discounts.name','discounts.status','discount_locations.discount','discount_locations.location','discount_locations.calWeekNo','discount_locations.storeWeekNo','discount_locations.disStatus')->where('discounts.status', '=', 1)->where('discount_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		$adjustmentData = DB::table('adjustments')->join('adjustment_locations', 'adjustments.id', '=', 'adjustment_locations.adjustment')->select('adjustments.id','adjustments.name','adjustments.status','adjustment_locations.adjustment','adjustment_locations.location','adjustment_locations.calWeekNo','adjustment_locations.storeWeekNo','adjustment_locations.adjStatus')->where('adjustments.status', '=', 1)->where('adjustment_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		$receiptData = DB::table('receipts')->join('receipt_locations', 'receipts.id', '=', 'receipt_locations.receipt')->select('receipts.id','receipts.name','receipts.status','receipt_locations.receipt','receipt_locations.location','receipt_locations.calWeekNo','receipt_locations.storeWeekNo','receipt_locations.recStatus')->where('receipts.status', '=', 1)->where('receipt_locations.location', '=', $locationId)->orderBy('sorting_order','asc')->get();

		if(Session::has('checkType')) {

		} else {
			Session::forget('fetchData');
			Session::forget('disData');
			Session::forget('adjustData');
			Session::forget('recData');
			Session::forget('saleSum');
			Session::forget('disSum');
			Session::forget('adjSum');
			Session::forget('recSum');
		}
		if(Session::has('fetchData')) {
			$weekData = Session::get('fetchData');
			$disData = Session::get('disData');
			$adjustData = Session::get('adjustData');
			$recData = Session::get('recData');
			$saleSum = Session::get('saleSum');
			$disSum = Session::get('disSum');
			$adjSum = Session::get('adjSum');
			$recSum = Session::get('recSum');

			$sDate = Session::get('sDate');
			$eDate = Session::get('eDate');
			Session::forget('checkType');
		} else{
									/*$week = $id;
									$salesection = DB::select("select *  from `salesections` WHERE `weekNo` = '".$id."' group by `weekNo`");
									$year = date("Y",strtotime($salesection[0]->dataDate));
									$time = strtotime("1 January $year", time());
									$day = date('w', $time);
									$time += ((7*$week)+1-$day)*24*3600;
									$dates[0] = date('Y-n-j', $time);
									$time += 6*24*3600;
									$dates[1] = date('Y-n-j', $time);
									$startWeek = strtotime ( '-2 day' , strtotime ( $dates[0] ) ) ;
									$endWeek = strtotime ( '-2 day' , strtotime ( $dates[1] ) ) ;
									$sDate = date ( 'Y-m-d' , $startWeek );
									$eDate = date ( 'Y-m-d' , $endWeek );*/

									$week = $id;
                  $report_week='';
                  $store_week='';
									$salesection = DB::select("select *  from `salesections` WHERE `storeNo` = '".$id."' group by `weekNo`");
                  if(!empty($salesection)){
                  $report_week =  getWeekByDate($salesection['0']->dataDate);
                  $store_week  = $salesection['0']->storeNo;
                  }
                	$dayWeek =  date('w', $sDateStore);
									$lastSatFormat =  date("Y-m-d", $sDateStore);
									if($dayWeek > 5)
										$lastSatFormat =  date("Y-m-d", strtotime("previous saturday", $sDateStore));

									// $addWeek = $week-1;
									$addWeek = $week;
									$new_date= date("Y-m-d", strtotime("$lastSatFormat +".$addWeek." week"));

									$sDate = date ( 'Y-m-d' , strtotime($new_date));
									$eDate = date ( 'Y-m-d' , strtotime("$sDate + 6 day"));



			//$sDate = date('Y-m-d',strtotime('last Saturday'));
			//$eDate = date('Y-m-d',strtotime('Friday'));
			$weekData = DB::table('salesections')->join('sales', 'salesections.nameId', '=', 'sales.id')->select('salesections.*','sales.name')->whereBetween('salesections.dataDate',[$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'sale')->orderBy('sorting_order','asc')->get();

			$disData = DB::table('salesections')->join('discounts', 'salesections.nameId', '=', 'discounts.id')->select('salesections.*','discounts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'dis')->orderBy('sorting_order','asc')->get();

			$adjustData = DB::table('salesections')->join('adjustments', 'salesections.nameId', '=', 'adjustments.id')->select('salesections.*','adjustments.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'adj')->orderBy('sorting_order','asc')->get();

			$recData = DB::table('salesections')->join('receipts', 'salesections.nameId', '=', 'receipts.id')->select('salesections.*','receipts.name')->whereBetween('salesections.dataDate', [$sDate,$eDate])->where('salesections.location_id', '=', $locationId)->where('salesections.nameDep', '=', 'rec')->orderBy('sorting_order','asc')->get();

			$saleSum = DB::select("select `sales`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `sales` left join `salesections` on `salesections`.`nameId` = `sales`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'sale') where sales.status = '1' group by `sales`.`name` order by `sales`.`sorting_order` asc");

			$disSum = DB::select("select `discounts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `discounts` left join `salesections` on `salesections`.`nameId` = `discounts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'dis') where discounts.status = '1'  group by `discounts`.`name` order by `sorting_order` asc");

			$adjSum = DB::select("select `adjustments`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `adjustments` left join `salesections` on `salesections`.`nameId` = `adjustments`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'adj')where adjustments.status = '1'  group by `adjustments`.`name` order by `sorting_order` asc");

			$recSum = DB::select("select `receipts`.`name`, IFNULL(sum(salesections.unit),0) as unit, IFNULL(sum(salesections.amount),0) as amount from `receipts` left join `salesections` on `salesections`.`nameId` = `receipts`.`id` and (`salesections`.`dataDate` between '".$sDate."' and '".$eDate."' and `salesections`.`location_id` = '".$locationId."' and `salesections`.`nameDep` = 'rec') where receipts.status = '1'  group by `receipts`.`name` order by `sorting_order` asc");

		}

		$queryMatch = $this->weekDataValue($sDate);
		$queryMatchData = explode('|',$queryMatch);
		$calendarWeekMatch = $queryMatchData[0];
		$storeWeekMatch = $queryMatchData[1];

		return view('salesections.printData',compact('report_week','store_week','saleData','discountData','adjustmentData','receiptData','startDatestore','weekData','disData','adjustData','recData','sDate','eDate','saleSum','disSum','adjSum','recSum','calendarWeekMatch','storeWeekMatch','id'))->with('dailyMetrics', app(\App\Services\SalesReport\SalesReportDailyMetrics::class)->week($locationId, $sDate, $eDate));
		//$contents = $view->render();
	}


}
