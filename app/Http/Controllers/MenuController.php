<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Menu;
use App\MenuLocation;
use Illuminate\Support\Facades\DB;
use Session;
class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('menus.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('menus.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
      $locationId = Session::get('location_id');
      $locationIds = DB::table('user_locations')
        ->join('locations', 'locations.id', '=', 'user_locations.location')
        ->where('user_locations.user', \Auth::user()->id)
        ->where('locations.status', '1')->pluck('locations.id')->all();
      if (empty($locationIds) && $locationId) $locationIds = [$locationId];
      $requestedLocation = request()->query('location_scope');
      $dashboardIsCompany = !$requestedLocation || !in_array($requestedLocation, $locationIds);
      if (!$dashboardIsCompany) $locationIds = [$requestedLocation];
      $today = new \DateTimeImmutable('today');
      $daysSinceSaturday = ((int) $today->format('w') + 1) % 7;
      $weekStart = $today->modify('-'.$daysSinceSaturday.' days')->format('Y-m-d');
      $weekEnd = (new \DateTimeImmutable($weekStart))->modify('+6 days')->format('Y-m-d');

      $data['menus']=  MenuLocation::select(['menus.id','menus.name','menus.icon','menus.route'])->distinct()->join('menus', 'menus.id', '=', 'menu_locations.menu_id')
        ->join('locations', 'locations.id', '=', 'menu_locations.location_id')->whereIn('menu_locations.location_id',$locationIds)
          ->where('locations.status','1')
          ->where('menus.status',1)
        ->where('menu_locations.status',1)
        ->get();

      // Read-only values for the dashboard presentation. Operational writes remain
      // in their existing feature controllers and routes.
      $data['dashboardWeekStart'] = $weekStart;
      $data['dashboardWeekEnd'] = $weekEnd;
      $data['dashboardCalendarWeek'] = $today->format('W');
      $currentStoreWeek = DB::table('salesections')
        ->whereIn('location_id', $locationIds)->where('nameDep', 'sale')
        ->whereBetween('dataDate', [$weekStart, $weekEnd])
        ->orderBy('dataDate', 'desc')->value('storeNo');
      $data['dashboardStoreWeek'] = $currentStoreWeek;
      $data['dashboardGrossSales'] = DB::table('salesections')
        ->whereIn('location_id', $locationIds)->where('nameDep', 'sale')
        ->whereBetween('dataDate', [$weekStart, $weekEnd])->sum('amount');

      $netSales = $saleTax = 0;
      foreach ($locationIds as $dashboardLocationId) {
        if (function_exists('getNetSale')) $netSales += getNetSale($dashboardLocationId, $weekStart, $weekEnd);
        if (function_exists('getSaleTax')) $saleTax += getSaleTax($dashboardLocationId, $weekStart, $weekEnd);
      }
      $laborTotal = 0;
      if (function_exists('getLaberRate')) {
        foreach ($locationIds as $dashboardLocationId) {
          $locationStoreWeek = DB::table('salesections')->where('location_id', $dashboardLocationId)
            ->where('nameDep', 'sale')->whereBetween('dataDate', [$weekStart, $weekEnd])
            ->orderBy('dataDate', 'desc')->value('storeNo');
          if ($locationStoreWeek) $laborTotal += getLaberRate($dashboardLocationId, $locationStoreWeek);
        }
      }
      $data['dashboardLaborTotal'] = $laborTotal;
      $data['dashboardLaborRatio'] = ($netSales - $saleTax) > 0
        ? ($laborTotal / ($netSales - $saleTax)) * 100
        : 0;

      $data['dashboardOpenProblems'] = DB::table('customer_problems')
        ->whereIn('location', $locationIds)->where('status', 'Not Resolved')->count();
      $data['dashboardPendingClaims'] = DB::table('reimbursements')
        ->whereIn('location_id', $locationIds)
        ->whereIn('status', ['Pending', 'Approved Pending', 'Approval Pending', 'Payment Pending'])
        ->count();

      $dailySalesRows = DB::table('salesections')
        ->select('dataDate', DB::raw('SUM(amount) as total'))
        ->whereIn('location_id', $locationIds)->where('nameDep', 'sale')
        ->whereBetween('dataDate', [$weekStart, $weekEnd])
        ->groupBy('dataDate')->get();
      $dailySales = [];
      for ($day = 0; $day < 7; $day++) {
        $date = (new \DateTimeImmutable($weekStart))->modify('+'.$day.' days');
        $dailySales[$date->format('Y-m-d')] = [
          'label' => $date->format('D'),
          'amount' => 0,
        ];
      }
      foreach ($dailySalesRows as $dailySalesRow) {
        $date = date('Y-m-d', strtotime($dailySalesRow->dataDate));
        if (isset($dailySales[$date])) {
          $dailySales[$date]['amount'] = (float) $dailySalesRow->total;
        }
      }
      $data['dashboardDailySales'] = array_values($dailySales);

      $data['dashboardCategoryRevenue'] = DB::table('salesections')
        ->join('sales', 'sales.id', '=', 'salesections.nameId')
        ->select('sales.name', DB::raw('SUM(salesections.amount) as amount'))
        ->whereIn('salesections.location_id', $locationIds)->where('salesections.nameDep', 'sale')
        ->whereBetween('salesections.dataDate', [$weekStart, $weekEnd])
        ->groupBy('sales.id', 'sales.name')->orderBy('amount', 'desc')->limit(4)->get();

      $data['dashboardPayrollHistory'] = DB::table('laborheadersections')
        ->select('start_week', 'end_week', 'start_date', 'end_date', 'total_amount')
        ->whereIn('location_id', $locationIds)->orderBy('start_week', 'desc')->limit(4)->get();
      $data['dashboardRecentProblems'] = DB::table('customer_problems')
        ->join('locations', 'locations.id', '=', 'customer_problems.location')
        ->select('customer_problems.id', 'customer_problems.location as location_id', 'locations.location as locationName', 'locations.location_code as locationCode', 'customer_problems.customer_name', 'customer_problems.customer_last_name', 'customer_problems.problem', 'customer_problems.status', 'customer_problems.filled_date', 'customer_problems.created_at')
        ->whereIn('customer_problems.location', $locationIds)->orderBy('customer_problems.created_at', 'desc')->limit(4)->get();
      $data['dashboardSalesHistory'] = DB::table('salesections')
        ->join('locations', 'locations.id', '=', 'salesections.location_id')
        ->select('salesections.location_id', 'locations.location as locationName', 'salesections.storeNo', DB::raw('MAX(salesections.dataDate) as dataDate'), DB::raw('SUM(salesections.amount) as amount'))
        ->whereIn('salesections.location_id', $locationIds)->where('salesections.nameDep', 'sale')
        ->groupBy('salesections.location_id', 'locations.location', 'salesections.storeNo', DB::raw('DATE_SUB(salesections.dataDate, INTERVAL MOD(DAYOFWEEK(salesections.dataDate), 7) DAY)'))
        ->orderBy('dataDate', 'desc')->limit(4)->get();

      $data['dashboardLocationCount'] = count($locationIds);
      $data['dashboardIsCompany'] = $dashboardIsCompany;
      $data['dashboardLocations'] = DB::table('locations')->whereIn('id', $locationIds)->orderBy('location')->get();


        return view('home',$data);
    }
}
