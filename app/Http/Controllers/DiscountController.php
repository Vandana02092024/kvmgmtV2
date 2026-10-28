<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Discount;
use App\Location;
use App\Http\Requests;
use Validator;
use App\Discount_location;
use App\Db;

class DiscountController extends Controller
{
    protected $roles;
    protected $locations;

    public function __construct()
    {
        $this->middleware('auth');
        $this->locations = Location::pluck('location', 'id');
        $this->roles     = array('Owner' => 'Owner', 'Supervisor' => 'Supervisor', 'Employee' => 'Employee');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($openAddModal = false, $openEditId = null)
    {
        $discounts = \DB::table('discounts')->select('discounts.*')->orderBy('sorting_order', 'desc')->get();
		$locations = $this->locations;
		$locationSelections = [];
		foreach (\DB::table('discount_locations')->select('discount', 'location')->distinct()->get() as $link) {
			$locationSelections[$link->discount][] = (string) $link->location;
		}
        // $users = \DB::table('users')
        //     ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
        //     ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
        //     ->select('users.*', 'locations.location')
        //     ->get();
        return view('discounts.index', compact('discounts', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
	{
        return $this->index(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $discount = Discount::create([
            'name' => $request['name'],
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    Discount_location::create([
                        'discount' => $discount->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                Discount_location::create([
                    'discount' => $discount->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('discount.index')->with('message','Discount has been added successfully!');
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
    public function edit(Discount $discount)
	{
		return $this->index(false, $discount->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Discount $discount)
    // {
        

    //     $discount->update([
    //         'name' => $request['name'],
	// 		'sorting_order' => $request['sorting_order'],
    //     ]);

	// 	$locVal = $request->input('location', []);
	// 	if (!is_array($locVal)) $locVal = [$locVal];
	// 	$existingLocations = [];
	// 	foreach (Discount_location::where('discount', $discount->id)->get(['location']) as $link) {
	// 		$existingLocations[] = (string) $link->location;
	// 	}
	// 	foreach (array_unique($locVal) as $locValue) {
	// 		if ($locValue !== '' && !in_array((string) $locValue, $existingLocations, true)) {
	// 			Discount_location::create(['discount' => $discount->id, 'location' => $locValue]);
	// 		}
	// 	}
		
		
		
	// 	return redirect()->route('discount.index')->with('message','Discount has been updated successfully!');
    // }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sorting_order' => 'nullable|integer',
            'location' => 'nullable|array',
            'location.*' => 'integer|exists:locations,id',
        ]);

        $discount = Discount::findOrFail($id);

        $discount->name = $request->input('name');
        $discount->sorting_order = $request->input('sorting_order');
        $discount->save();

        $locVal = $request->input('location', []);

        if (!is_array($locVal)) {
            $locVal = [$locVal];
        }

        $locVal = array_unique(array_filter($locVal, function ($value) {
            return $value !== null && $value !== '';
        }));

        // Existing locations assigned to this discount
        $existingLocations = Discount_location::where('discount', $discount->id)
            ->pluck('location')
            ->map(fn ($location) => (string) $location)
            ->toArray();

        // Add only newly selected locations
        foreach ($locVal as $locValue) {
            if (!in_array((string) $locValue, $existingLocations, true)) {
                Discount_location::create([
                    'discount' => $discount->id,
                    'location' => $locValue,
                ]);
            }
        }

        // Remove locations that were unselected in the form
        Discount_location::where('discount', $discount->id)
            ->whereNotIn('location', $locVal ?: [0])
            ->delete();

        return redirect()
            ->route('discount.index')
            ->with('message', 'Discount has been updated successfully!');
    }


	
	public function discountarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		Discount::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('discount.index')->with('message','Archived successfully!');			
		} else {
		Discount::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('discount.index')->with('message','Unarchived successfully!');
		}
    }
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        // $patient = Patient::where('agent', '=', $user->id)->get()->first();

        // if($patient){
        //     return redirect()->route('user.index')->with('message','User can not be deleted, as He/She is already assigned to a patient');
        // }else{
        //     $user->delete();
        //     return redirect()->route('user.index')->with('message','User has been deleted successfully!');
        // }
    }

   /* protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'  => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role'  => 'required|max:255',
            'location'  => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
    }*/

   /* protected function edit_validator(array $data)
    {
        $messsages = array(
            'npassword.min'=>'The password must be at least 6 characters.',
            'npassword.confirmed'=>'The password confirmation does not match.',
        );

        return Validator::make($data, [
            'name'  => 'required|max:255',
            'role'  => 'required|max:255',
            // 'location'  => 'required',
            'npassword' => 'min:6|confirmed',
        ], $messsages);
    }*/

	 protected function removeSelectedLocation(Request $request, Discount $discount)
    {
        $SaleLocation = Discount_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'DiscountController@edit', $request->discount
        );
    }
   
}
