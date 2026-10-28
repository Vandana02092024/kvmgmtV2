<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Receipt;
use App\Location;
use App\Http\Requests;
use Validator;
use App\Receipt_location;
use App\Db;

class ReceiptController extends Controller
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
        $receipts =  \DB::table('receipts')->select('receipts.*')->orderBy('sorting_order', 'asc')->get();
		$locations = $this->locations;
		$locationSelections = [];
		foreach (\DB::table('receipt_locations')->select('receipt', 'location')->distinct()->get() as $link) {
			$locationSelections[$link->receipt][] = (string) $link->location;
		}
        // $users = \DB::table('users')
        //     ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
        //     ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
        //     ->select('users.*', 'locations.location')
        //     ->get();
        return view('receipts.index', compact('receipts', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
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
        $receipt = Receipt::create([
            'name' => $request['name'],   
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    Receipt_location::create([
                        'receipt' => $receipt->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                Receipt_location::create([
                    'receipt' => $receipt->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('receipt.index')->with('message','Receipt has been added successfully!');
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
    public function edit(Receipt $receipt)
	{
		return $this->index(false, $receipt->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Receipt $receipt)
    // {
        

    //     $receipt->update([
    //         'name' => $request['name'],
	// 		'sorting_order' => $request['sorting_order'],
    //     ]);

	// 	$locVal = $request->input('location', []);
	// 	if (!is_array($locVal)) $locVal = [$locVal];
	// 	$existingLocations = [];
	// 	foreach (Receipt_location::where('receipt', $receipt->id)->get(['location']) as $link) {
	// 		$existingLocations[] = (string) $link->location;
	// 	}
	// 	foreach (array_unique($locVal) as $locValue) {
	// 		if ($locValue !== '' && !in_array((string) $locValue, $existingLocations, true)) {
	// 			Receipt_location::create(['receipt' => $receipt->id, 'location' => $locValue]);
	// 		}
	// 	}
		
		
		
	// 	return redirect()->route('receipt.index')->with('message','Receipt has been updated successfully!');
    // }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sorting_order' => 'nullable|integer',
            'location' => 'nullable|array',
            'location.*' => 'integer|exists:locations,id',
        ]);

        $receipt = Receipt::findOrFail($id);

        // Update receipt details.
        $receipt->name = $request->input('name');
        $receipt->sorting_order = $request->input('sorting_order');
        $receipt->save();

        // Get selected locations.
        $locVal = $request->input('location', []);

        if (!is_array($locVal)) {
            $locVal = [$locVal];
        }

        $locVal = array_values(array_unique(array_filter($locVal, function ($value) {
            return $value !== null && $value !== '';
        })));

        // Synchronize receipt locations.
        foreach ($locVal as $locValue) {
            Receipt_location::updateOrCreate(
                [
                    'receipt' => $receipt->id,
                    'location' => $locValue,
                ],
                []
            );
        }

        // Remove locations that are no longer selected.
        $query = Receipt_location::where('receipt', $receipt->id);

        if (!empty($locVal)) {
            $query->whereNotIn('location', $locVal);
        }

        $query->delete();

        return redirect()
            ->route('receipt.index')
            ->with('message', 'Receipt has been updated successfully!');
    }

	
	public function receiptarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		Receipt::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('receipt.index')->with('message','Archived successfully!');			
		} else {
		Receipt::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('receipt.index')->with('message','Unarchived successfully!');
		}
    }
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
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

	 protected function removeSelectedLocation(Request $request, Receipt $receipt)
    {
        $SaleLocation = Receipt_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'ReceiptController@edit', $request->receipt
        );
    }
   
}
