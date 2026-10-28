<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\PayrollDepartment;
use App\Location;
use App\Http\Requests;
use Validator;
use App\PayrollDepartment_location;
use App\Db;

class PayrollDepartmentController extends Controller
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
        $payrolldepartments = \DB::table('payroll_departments')->select('payroll_departments.*')->orderBy('sorting_order', 'asc')->get();
		
        $locations = $this->locations;
        $locationSelections = [];
        foreach ($payrolldepartments as $department) {
            $locationSelections[$department->id] = PayrollDepartment_location::where('payrolldep_id', '=', $department->id)->pluck('location')->toArray();
        }
        $openAddModal = false;
        $openEditId = null;
        return view('payrolldepartments.index', compact('payrolldepartments', 'locations', 'locationSelections', 'openAddModal', 'openEditId'));
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
        return view('payrolldepartments.index', (array) $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		
        $discount = PayrollDepartment::create([
            'name' => $request['name'],
			'sorting_order' => $request['sorting_order'],	
        ]);
		$locVal = $request['location'];
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    PayrollDepartment_location::create([
                        'payrolldep_id' => $discount->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                PayrollDepartment_location::create([
                    'payrolldep_id' => $discount->id,
                    'location' => $locVal,
                ]);
            }
        }		
		return redirect()->route('payrolldepartment.index')->with('message','Payroll Department has been added successfully!');
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
    // public function edit(PayrollDepartment $payrolldepartment)
    // {       
    //     $data = $this->index()->getData();
    //     $data['openEditId'] = $payrolldepartment->id;
    //     return view('payrolldepartments.index', (array) $data);
    // }

    
   
    public function edit($id)
    {
        // Get all departments for the listing.
        $payrolldepartments = \DB::table('payroll_departments')
            ->select('payroll_departments.*')
            ->orderBy('sorting_order', 'asc')
            ->get();

        // Get available locations.
        $locations = $this->locations;

        // Get selected locations for each department.
        $locationSelections = [];

        foreach ($payrolldepartments as $department) {
            $locationSelections[$department->id] =
                PayrollDepartment_location::where(
                    'payrolldep_id',
                    $department->id
                )->pluck('location')->toArray();
        }

        // Find the department being edited.
        $payrolldepartment = PayrollDepartment::findOrFail($id);

        // Tell the shared modal partial which modal to open.
        $openAddModal = false;
        $openEditId = $payrolldepartment->id;

        return view('payrolldepartments.index', compact(
            'payrolldepartments',
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
    // public function update(Request $request, PayrollDepartment $payrolldepartment)
    // {
        

    //     $payrolldepartment->update([
    //         'name' => $request['name'],
	// 		'sorting_order' => $request['sorting_order']
    //     ]);

	// 	$locVal = (array) $request->input('location', []);
	// 	if (count($locVal)) {
	// 	    PayrollDepartment_location::where('payrolldep_id', '=', $payrolldepartment->id)->whereNotIn('location', $locVal)->delete();
	// 	} else {
	// 	    PayrollDepartment_location::where('payrolldep_id', '=', $payrolldepartment->id)->delete();
	// 	}
	// 	foreach ($locVal as $locValue) {
	// 	    PayrollDepartment_location::firstOrCreate(['payrolldep_id' => $payrolldepartment->id, 'location' => $locValue]);
	// 	}
		
		
		
	// 	return redirect()->route('payrolldepartment.index')->with('message','Department has been updated successfully!');
    // }

    
    public function update(Request $request, $id)
    {
        // Find the department using the ID from the URL.
        $payrolldepartment = PayrollDepartment::findOrFail($id);

        // Update department details.
        $payrolldepartment->update([
            'name' => $request->input('name'),
            'sorting_order' => $request->input('sorting_order'),
        ]);

        // Get selected locations.
        $locVal = (array) $request->input('location', []);

        // Remove existing locations that are no longer selected.
        if (!empty($locVal)) {
            PayrollDepartment_location::where(
                'payrolldep_id',
                $payrolldepartment->id
            )->whereNotIn('location', $locVal)->delete();
        } else {
            PayrollDepartment_location::where(
                'payrolldep_id',
                $payrolldepartment->id
            )->delete();
        }

        // Add selected locations if they do not already exist.
        foreach ($locVal as $locValue) {
            PayrollDepartment_location::firstOrCreate([
                'payrolldep_id' => $payrolldepartment->id,
                'location' => $locValue,
            ]);
        }

        return redirect()
            ->route('payrolldepartment.index')
            ->with('message', 'Department has been updated successfully!');
    }


	
	public function payrolldepartmentarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		if($statusCode == 1) {
		PayrollDepartment::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('payrolldepartment.index')->with('message','Archived successfully!');			
		} else {
		PayrollDepartment::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('payrolldepartment.index')->with('message','Unarchived successfully!');
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

	 protected function removeSelectedLocation(Request $request, PayrollDepartment $payrolldepartment)
    {
		
        $SaleLocation = PayrollDepartment_location::where('id', '=', $request->id)->get()->first();
        $SaleLocation->delete();
        return redirect()->action(
            'PayrollDepartmentController@edit', $request->payrolldepartment
        );
    }
   
}
