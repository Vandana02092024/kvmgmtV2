<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Sale;
use App\Location;
use App\Http\Requests;
use Validator;
use App\Sale_location;
use App\User_email_location;
use App\Db;

class SaleController extends Controller
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
        $sales = \DB::table('sales')->select('sales.*')->orderBy('sorting_order', 'asc')->get();
		$locations = $this->locations;
		$locationSelections = [];
		foreach (\DB::table('sale_locations')->select('sale', 'location')->distinct()->get() as $link) {
			$locationSelections[$link->sale][] = (string) $link->location;
		}
        // $users = \DB::table('users')
        //     ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
        //     ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
        //     ->select('users.*', 'locations.location')
        //     ->get();
        return view('sales.index', compact('sales', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
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
        $sale = Sale::create([
            'name' => $request['name'],  
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    Sale_location::create([
                        'sale' => $sale->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                Sale_location::create([
                    'sale' => $sale->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('sale.index')->with('message','Sale has been added successfully!');
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
    public function edit(Sale $sale)
	{
		return $this->index(false, $sale->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Sale $sale)
    // {
        

    //     $sale->update([
    //         'name' => $request['name'],
	// 		'sorting_order' => $request['sorting_order'],
    //     ]);

	// 	$locVal = $request->input('location', []);
	// 	if (!is_array($locVal)) $locVal = [$locVal];
	// 	$existingLocations = [];
	// 	foreach (Sale_location::where('sale', $sale->id)->get(['location']) as $link) {
	// 		$existingLocations[] = (string) $link->location;
	// 	}
	// 	foreach (array_unique($locVal) as $locValue) {
	// 		if ($locValue !== '' && !in_array((string) $locValue, $existingLocations, true)) {
	// 			Sale_location::create(['sale' => $sale->id, 'location' => $locValue]);
	// 		}
	// 	}
		
		
		
	// 	return redirect()->route('sale.index')->with('message','Sale has been updated successfully!');
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sorting_order' => 'nullable|integer',
            'location' => 'nullable|array',
            'location.*' => 'integer|exists:locations,id',
        ]);

        $sale = Sale::findOrFail($id);

        $sale->name = $request->input('name');
        $sale->sorting_order = $request->input('sorting_order');
        $sale->save();

        $locVal = $request->input('location', []);

        if (!is_array($locVal)) {
            $locVal = [$locVal];
        }

        $locVal = array_values(array_unique(array_filter($locVal, function ($value) {
            return $value !== null && $value !== '';
        })));

        foreach ($locVal as $locValue) {
            $saleLocation = new Sale_location();
            $saleLocation->sale = $sale->id;
            $saleLocation->location = $locValue;
            $saleLocation->save();
        }

        $query = Sale_location::where('sale', $sale->id);

        if (!empty($locVal)) {
            $query->whereNotIn('location', $locVal);
        }

        $query->delete();

        return redirect()->route('sale.index')
            ->with('message', 'Sale has been updated successfully!');
    }

	
	public function salearchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		Sale::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('sale.index')->with('message','Archived successfully!');			
		} else {
		Sale::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('sale.index')->with('message','Unarchived successfully!');
		}
    }
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
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

	 protected function removeSelectedLocation(Request $request, Sale $sale)
    {
        $SaleLocation = Sale_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'SaleController@edit', $request->sale
        );
    }
   
}
