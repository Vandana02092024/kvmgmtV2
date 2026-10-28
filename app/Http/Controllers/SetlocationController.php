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
		if(Auth::user()->status == 0) {

		$locations = DB::table('user_locations')
			->join('locations', 'locations.id', '=', 'user_locations.location')
			->where('user_locations.user', '=', Auth::user()->id)
			->where('locations.status', '=', '1')
			->pluck('locations.location', 'locations.id');

			 Session::put('all_locations', $locations);

			Auth::logout();
			return \Redirect::to('/');
		}
        $locations = DB::table('user_locations')
            ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
            ->where('user_locations.user', '=', Auth::user()->id)
			->where('locations.status', '=', '1')
            ->pluck('locations.location', 'locations.id');

        Session::put('all_locations', $locations);

        if ($locations->isNotEmpty()) {
			$location = Session::get('location_id');

			if (!$locations->has($location)) {
				$location = Auth::user()->location;
			}

			if (!$locations->has($location)) {
				$location = $locations->keys()->first();
			}

			return $this->activateLocation($location);
		}

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
		$allowedLocations = DB::table('locations')
			->join('user_locations', 'user_locations.location', '=', 'locations.id')
			->where('user_locations.user', Auth::user()->id)
			->where('locations.status', '1');
		$allowed = $allowedLocations->where('locations.id', $location)->exists();
		if (!$allowed) {
			return \Redirect::back()->withErrors(['location' => 'Select a location assigned to your account.']);
		}
		$this->activateLocation($location);
		if ($request->query('dashboard')) {
			return \Redirect::to('dashboard?location_scope='.$location);
		}
		return \Redirect::back();
	}

	/**
	 * Apply the selected location to the session and load its existing notifications.
	 *
	 * @param  int  $location
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function activateLocation($location)
	{
        Session::put('location_id', $location);

		$getVendor     = $location.".vendor_id";
		$svCategoryId  = $location.".category_id";

		Session::forget('vendor_id');
		Session::forget('category_id');

		//Session::forget($getVendor);
		//Session::forget($svCategoryId);

		$locationDetails = DB::table('locations')
			->where('id', '=', $location)
			->first();

		if (!$locationDetails) {
			Session::forget('location_id');
			return \Redirect::route('setlocation.index')->withErrors([
				'location' => 'The selected location is no longer available.',
			]);
		}

		Session::put('locationName', $locationDetails->location);
		Session::put('locationCode', $locationDetails->location_code);
		Session::put('entityName', $locationDetails->entity_name);

		$services = DB::table('customer_services')
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					//->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select(DB::raw("count(customer_services.id) as total_new_csr"), 'customer_services.id as id')
					->pluck('total_new_csr');

		Session::put('total_new_csr', $services[0]);

		$all_new_csr_data = DB::table('customer_services')
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->leftJoin('locations', 'customer_servicefollows.location', '=', 'locations.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					//->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select('customer_services.customer_name as customer_name', 'customer_services.id as id', 'customer_servicefollows.followup_date', 'customer_servicefollows.followup_time','customer_servicefollows.location','locations.location as loc_name')
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
