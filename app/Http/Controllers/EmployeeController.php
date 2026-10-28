<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employee;
use App\User_location;
use App\Location;
use App\Http\Requests;
use App\Http\Requests\EmployeeRequest;


class EmployeeController extends Controller
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
        //$employees = Employee::all();
		
		
         $employees = \DB::table('employees')
            ->join('locations', 'employees.emplocation', '=', 'locations.id')            
            ->select('employees.*', 'locations.location')
            ->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles     = $this->roles;
        $locations = $this->locations;
        return view('employees.create',compact('roles','locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		
		
         Employee::create($request->all());
        return redirect()->route('employee.index')->with('message','Employee has been added successfully!');
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
    public function edit(Employee $employee)
    {
      
        $locations = $this->locations;  
 		
        return view('employees.edit',compact(['employee','locations']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
		
		
		
        
		$employee->update($request->all());
        return redirect()->route('employee.index')->with('message','Employee has been updated successfully!');
		
		
    }

	
	public function empArchive($id , Request $request)
    {
		$statusCode = $request->st;
		
		
		if($statusCode == 1) {
		Employee::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('employee.index')->with('message','Archived successfully!');
			
		} else {
		Employee::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('employee.index')->with('message','Unarchived successfully!');
		}
		
		
		
		
    }
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employee.index')->with('message','Employee has been deleted successfully!');
    }

    

  
}
