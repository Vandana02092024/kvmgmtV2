<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Session;
use App\Http\Requests;

class SetlocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = DB::table('user_locations')
            ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
            ->where('user_locations.user', '=', Auth::user()->id)
			->where('locations.status', '=', 1)
            ->pluck('locations.location', 'locations.id');

        Session::put('all_locations', $locations);
		return view('setlocations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $location = $request->get('location');
        Session::put('location_id', $location);
		
		$getVendor     = $location.".vendor_id";
		$svCategoryId  = $location.".category_id";
		
		Session::forget('vendor_id');
		Session::forget('category_id');
			
		//Session::forget($getVendor);
		//Session::forget($svCategoryId);
		
		$locationName = DB::table('locations')
		->select('location')
		->where('id', '=', $location)
		->pluck('location');
		
		Session::put('locationName', $locationName[0]);
		$services = DB::table('customer_services')
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select(DB::raw("count(customer_services.id) as total_new_csr"), 'customer_services.id as id')
					->pluck('total_new_csr');
		
		Session::put('total_new_csr', $services[0]);
		
		$all_new_csr_data = DB::table('customer_services')		            
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select('customer_services.customer_name as customer_name', 'customer_services.id as id', 'customer_servicefollows.followup_date', 'customer_servicefollows.followup_time')
					->get();
		
		Session::put('all_new_csr_data', $all_new_csr_data);
		
		// return redirect()->route('user.index');
        return \Redirect::to('dashboard');
    }

    // public function changeSessionValue(Request $request)
    // {
    //     $location = $request->get('location');
    //     Session::put('location_id', $location);
    //     echo Session::get('location_id');

    //     // $location = $request->get('location');
    //     // 
    //     // return 
    // }



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
}
