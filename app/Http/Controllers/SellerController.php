<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Seller;
use App\SellerDeliveryDays;
use App\Location;
use App\Http\Requests;
use App\Http\Requests\SellerRequest;
use Session;
class SellerController extends Controller
{
    protected $days;
    public function __construct()
    {
        $this->middleware('auth');
        $this->days = array(1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {	
	// 	$days = $this->days;
    //     // $sellers = seller::all();
		
	// 	$location = Session::get('location_id');
	// 	 $sellers = \DB::table("sellers")
	// 	  ->select("sellers.*",
	// 			  \DB::raw("(SELECT delivery_day FROM seller_delivery_days
	// 					  WHERE location_id = $location and seller_id = sellers.id
	// 					) as seller_delivery_days"))
	// 	->get();
		  
	// 	return view('sellers.index', compact('sellers','days'));
    // }

    
    public function index()
    {
        $days = $this->days;

        $location = Session::get('location_id');

       $sellers = \DB::table('sellers')
        ->leftJoin('seller_delivery_days', function ($join) use ($location) {
            $join->on('seller_delivery_days.seller_id', '=', 'sellers.id')
                ->where('seller_delivery_days.location_id', '=', $location);
        })
        ->select(
            'sellers.*',
            \DB::raw('GROUP_CONCAT(DISTINCT seller_delivery_days.delivery_day ORDER BY seller_delivery_days.delivery_day SEPARATOR ",") as seller_delivery_days')
        )
        ->groupBy('sellers.id')
        ->get();

        return view('sellers.index', compact('sellers', 'days'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $days = $this->days;
        $locations = Location::orderBy('rank')->get();
		return view('sellers.create', compact('locations', 'days'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SellerRequest $request)
    {
		$input = $request->all();
		
		// save seller data //
		$sellerData = [ 
				'name' 			=> $input['name'],
				'email' 		=> $input['email'],
				'phone' 		=> $input['phone'], 
				'address' 		=> $input['address'],
				'created_at' 	=> date('Y-m-d h:i:s'),
				'updated_at' 	=> date('Y-m-d h:i:s')
		   ];
		   
        $id = Seller::create($sellerData)->id;
	   foreach($input['delivery_day'] as $key => $value) {
			$data = [ 
				'seller_id' 	=> $id,
				'location_id'   => $value['location_id'],
				'delivery_day' 	=> $value['day'],
			 ];
			 
			SellerDeliveryDays::create($data);
		}
		return redirect()->route('seller.index')->with('message','Seller has been added successfully!');
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
	
	public function vArchive($id, Request $request)
    {
		
		$statusCode = $request->st;
		
		
		if($statusCode == 1) {
		Seller::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('seller.index')->with('message','Archived successfully!');
			
		} else {
		Seller::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('seller.index')->with('message','Unarchived successfully!');
		}
		
		
		
		
    }
	
	
	/**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	 
	 public function arrayToObject($d) {
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object) array_map(__FUNCTION__, $d);
        }
        else {
            // Return object
            return $d;
        }
    }
	
    public function edit($id)
    {
		$seller = seller::findOrFail($id);
		$sellerDates = \DB::table('seller_delivery_days')->select('location_id', 'delivery_day')->where('seller_id', $id)->get();
		
		$getDeliveryDay = array();
		
		//$faf = $this->arrayToObject($sellerDates);
		$resultArray = json_decode(json_encode($sellerDates), true);
		
		foreach($resultArray as $key => $val) {
			$getDeliveryDay[$val['location_id']]['delivery_day'] = $val['delivery_day'];
		}
		
		$days = $this->days;
		$locations = Location::orderBy('rank')->get();
		return view('sellers.edit',compact(['seller','days','locations','sellerDates', 'getDeliveryDay']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	 
	//public function update(Request $request, Seller $seller)
	
    public function update(Request $request, $id)
    {
		$input = $request->all();
	
		// save seller data //
		$sellerData = [ 
				'name' 			=> $input['name'],
				'email' 		=> $input['email'],
				'phone' 		=> $input['phone'], 
				'address' 		=> $input['address'],
				'updated_at' 	=> date('Y-m-d h:i:s')
	     ];
		 
		\DB::table('sellers')->where('id', $id)->update($sellerData);
		
		// remove than add data //
        SellerDeliveryDays::where('seller_id', '=', $id)->delete();
		foreach($input['delivery_day'] as $key => $value) {
			$data = [ 
				'seller_id' 	=> $id,
				'location_id'   => $value['location_id'],
				'delivery_day' 	=> $value['day'],
			 ];
			SellerDeliveryDays::create($data);
		}
		return redirect()->route('seller.index')->with('message','Seller has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller)
    {
        try {
            $seller->delete();
            return redirect()->route('seller.index')->with('message','Vendor has been deleted successfully!');
        }
        catch (\Exception $e) {
            return redirect()->route('seller.index')->with('message','Vendor can not be deleted, as you already have an order associated with this vendor!!');
        }
    }
}
