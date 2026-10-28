<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\PayrollCategory;
use App\Location;
use App\Http\Requests;
use Validator;
use App\PayrollCategory_location;
use App\Db;

class PayrollCategoryController extends Controller
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
    public function index()
    {
        $payrollcategories = \DB::table('payroll_categories')->select('payroll_categories.*')->orderBy('sorting_order', 'asc')->get();
	    $locations = $this->locations;
        $locationSelections = [];
        foreach ($payrollcategories as $category) {
            $locationSelections[$category->id] = PayrollCategory_location::where('payrollcat_id', '=', $category->id)->pluck('location')->toArray();
        }
        $openAddModal = false;
        $openEditId = null;
        return view('payrollcategories.index', compact('payrollcategories', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
	{        
        $data = $this->index()->getData();
        $data['openAddModal'] = true;
        return view('payrollcategories.index', (array) $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		
        $discount = PayrollCategory::create([
            'name' => $request['name'], 
			'pay_rate_multiplier' => $request['pay_rate_multiplier'],
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    PayrollCategory_location::create([
                        'payrollcat_id' => $discount->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                PayrollCategory_location::create([
                    'payrollcat_id' => $discount->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('payrollcategory.index')->with('message','Payroll Category has been added successfully!');
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
    // public function edit(PayrollCategory $payrollcategory)
    // {       
    //     $data = $this->index()->getData();
    //     $data['openEditId'] = $payrollcategory->id;
    //     return view('payrollcategories.index', (array) $data);
    // }

    
   
    public function edit($id)
    {
        $payrollcategories = \DB::table('payroll_categories')
            ->select('payroll_categories.*')
            ->orderBy('sorting_order', 'asc')
            ->get();

        $locations = $this->locations;

        $locationSelections = [];

        foreach ($payrollcategories as $category) {
            $locationSelections[$category->id] =
                PayrollCategory_location::where('payrollcat_id', $category->id)
                    ->pluck('location')
                    ->toArray();
        }

        // Confirm the requested category exists.
        $payrollcategory = PayrollCategory::findOrFail($id);

        $openAddModal = false;
        $openEditId = $payrollcategory->id;

        return view('payrollcategories.index', compact(
            'payrollcategories',
            'locations',
            'locationSelections',
            'openAddModal',
            'openEditId'
        ));
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, PayrollCategory $payrollcategory)
    // {
        

    //     $payrollcategory->update([
    //         'name' => $request['name'],
	// 		'pay_rate_multiplier' => $request['pay_rate_multiplier'],
	// 		'sorting_order' => $request['sorting_order'],
    //     ]);

	// 	$locVal = (array) $request->input('location', []);
	// 	if (count($locVal)) {
	// 	    PayrollCategory_location::where('payrollcat_id', '=', $payrollcategory->id)->whereNotIn('location', $locVal)->delete();
	// 	} else {
	// 	    PayrollCategory_location::where('payrollcat_id', '=', $payrollcategory->id)->delete();
	// 	}
	// 	foreach ($locVal as $locValue) {
	// 	    PayrollCategory_location::firstOrCreate(['payrollcat_id' => $payrollcategory->id, 'location' => $locValue]);
	// 	}
		
		
		
	// 	return redirect()->route('payrollcategory.index')->with('message','Payroll Category has been updated successfully!');
    // }
    
    public function update(Request $request, $id)
    {
        // Find the payroll category explicitly using its ID.
        $payrollcategory = PayrollCategory::findOrFail($id);

        // Update payroll category details.
        $payrollcategory->update([
            'name' => $request->input('name'),
            'pay_rate_multiplier' => $request->input('pay_rate_multiplier'),
            'sorting_order' => $request->input('sorting_order'),
        ]);

        // Get selected locations safely.
        $locVal = (array) $request->input('location', []);

        // Remove location assignments that are no longer selected.
        if (count($locVal) > 0) {
            PayrollCategory_location::where(
                'payrollcat_id',
                $payrollcategory->id
            )->whereNotIn('location', $locVal)->delete();
        } else {
            // No locations selected: remove all existing assignments.
            PayrollCategory_location::where(
                'payrollcat_id',
                $payrollcategory->id
            )->delete();
        }

        // Add any newly selected locations.
        foreach ($locVal as $locValue) {
            PayrollCategory_location::firstOrCreate([
                'payrollcat_id' => $payrollcategory->id,
                'location' => $locValue,
            ]);
        }

        return redirect()
            ->route('payrollcategory.index')
            ->with('message', 'Payroll Category has been updated successfully!');
    }


	
	public function payrollcategoryarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		PayrollCategory::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('payrollcategory.index')->with('message','Archived successfully!');			
		} else {
		PayrollCategory::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('payrollcategory.index')->with('message','Unarchived successfully!');
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

	 protected function removeSelectedLocation(Request $request, PayrollCategory $payrollcategory)
    {
		
        $SaleLocation = PayrollCategory_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'PayrollCategoryController@edit', $request->payrollcategory
        );
    }
   
}
