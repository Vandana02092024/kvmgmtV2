<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Adjustment;
use App\Location;
use App\Http\Requests;
use Validator;
use App\Adjustment_location;

use App\Db;

class AdjustmentController extends Controller
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
        $adjustments = \DB::table('adjustments')->select('adjustments.*')->orderBy('sorting_order', 'asc')->get();
		$locations = $this->locations;
		$locationSelections = [];
		foreach (\DB::table('adjustment_locations')->select('adjustment', 'location')->distinct()->get() as $link) {
			$locationSelections[$link->adjustment][] = (string) $link->location;
		}
        // $users = \DB::table('users')
        //     ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
        //     ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
        //     ->select('users.*', 'locations.location')
        //     ->get();
        return view('adjustments.index', compact('adjustments', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
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
        $adjustment = Adjustment::create([
            'name' => $request['name'],  
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    Adjustment_location::create([
                        'adjustment' => $adjustment->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                Adjustment_location::create([
                    'adjustment' => $adjustment->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('adjustment.index')->with('message','Adjustment has been added successfully!');
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
    public function edit(Adjustment $adjustment)
	{
		return $this->index(false, $adjustment->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Adjustment $adjustment)
    // {
        

    //     $adjustment->update([
    //         'name' => $request['name'],
	// 		'sorting_order' => $request['sorting_order'],
    //     ]);

	// 	$locVal = $request->input('location', []);
	// 	if (!is_array($locVal)) $locVal = [$locVal];
	// 	$existingLocations = [];
	// 	foreach (Adjustment_location::where('adjustment', $adjustment->id)->get(['location']) as $link) {
	// 		$existingLocations[] = (string) $link->location;
	// 	}
	// 	foreach (array_unique($locVal) as $locValue) {
	// 		if ($locValue !== '' && !in_array((string) $locValue, $existingLocations, true)) {
	// 			Adjustment_location::create(['adjustment' => $adjustment->id, 'location' => $locValue]);
	// 		}
	// 	}
		
		
		
	// 	return redirect()->route('adjustment.index')->with('message','Adjustment has been updated successfully!');
    // }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sorting_order' => 'nullable|integer',
            'location' => 'nullable',
            'location.*' => 'integer|exists:locations,id',
        ]);

        $adjustment = Adjustment::findOrFail($id);

        $adjustment->name = $request->input('name');
        $adjustment->sorting_order = $request->input('sorting_order');
        $adjustment->save();

        $locVal = $request->input('location', []);

        if (!is_array($locVal)) {
            $locVal = [$locVal];
        }

        // Remove empty values and duplicate location IDs.
        $locVal = array_values(array_unique(array_filter($locVal, function ($value) {
            return $value !== null && $value !== '';
        })));

        // Get existing locations for this adjustment.
        $existingLocations = Adjustment_location::where(
            'adjustment',
            $adjustment->id
        )->pluck('location')
        ->map(fn ($location) => (string) $location)
        ->toArray();

        // Add only newly selected locations.
        foreach ($locVal as $locValue) {
            if (!in_array((string) $locValue, $existingLocations, true)) {
                Adjustment_location::create([
                    'adjustment' => $adjustment->id,
                    'location' => $locValue,
                ]);
            }
        }

        // Remove locations that have been unselected.
        $locationQuery = Adjustment_location::where(
            'adjustment',
            $adjustment->id
        );

        if (!empty($locVal)) {
            $locationQuery->whereNotIn('location', $locVal);
        }

        if (empty($locVal)) {
            $locationQuery->delete();
        } else {
            $locationQuery->delete();
        }

        return redirect()
            ->route('adjustment.index')
            ->with('message', 'Adjustment has been updated successfully!');
    }


	
	public function adjustmentarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		Adjustment::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('adjustment.index')->with('message','Archived successfully!');			
		} else {
		Adjustment::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('adjustment.index')->with('message','Unarchived successfully!');
		}
    }
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Adjustment $adjustment)
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

	 protected function removeSelectedLocation(Request $request, Adjustment $adjustment)
    {
        $SaleLocation = Adjustment_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'AdjustmentController@edit', $request->adjustment
        );
    }
   
}
