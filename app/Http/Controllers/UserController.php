<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Location;
use App\Http\Requests;
use Validator;
use App\User_location;
use App\User_email_location;
use App\Db;

class UserController extends Controller
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
        $users = User::all();
        // $users = \DB::table('users')
        //     ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
        //     ->leftJoin('locations', 'locations.id', '=', 'user_locations.location')
        //     ->select('users.*', 'locations.location')
        //     ->get();
        return view('users.index', compact('users'));
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
        return view('users.create',compact('roles','locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'role' => $request['role'],
			'module_access' => ((isset($request['moduleList']))?implode(',',$request['moduleList']):''), 
            'claim' => $request['claim'],
            'remember_token' => $request['_token'],
            'password' => bcrypt($request['password']),
        ]);

		$locVal = $request->get('location');
		$emailLocVal = $request->get('email_location');

        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    User_location::create([
                        'user' => $user->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                User_location::create([
                    'user' => $user->id,
                    'location' => $locVal,
                ]);
            }
        }
		
		if($emailLocVal != '')
        {
				foreach ($emailLocVal as $lv) {
                    User_email_location::create([
                        'user' => $user->id,
                        'location' => $lv,
                    ]);
                }
        }
		
		return redirect()->route('user.create')->with('message','User has been added successfully!');
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
    // public function edit(User $user)
    // {
	// 	$roles     = $this->roles;
    //     $locations = $this->locations;
	// 	$module_data = explode(',',$user['module_access']);
    //     $selLocations = User_location::where('user', '=', $user->id)->pluck('location', 'id')->toArray();
	// 	$selEmailLocations = User_email_location::where('user', '=', $user->id)->pluck('location', 'id')->toArray();		
    //     return view('users.edit',compact(['user','roles','locations','selLocations','selEmailLocations','module_data']));
    // }

    
    
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $roles = $this->roles;
        $locations = $this->locations;

        $module_data = array_filter(
            explode(',', (string) $user->module_access),
            'strlen'
        );

        $selLocations = User_location::where('user', $user->id)
            ->pluck('location', 'id')
            ->toArray();

        $selEmailLocations = User_email_location::where('user', $user->id)
            ->pluck('location', 'id')
            ->toArray();

        return view('users.edit', compact(
            'user',
            'roles',
            'locations',
            'selLocations',
            'selEmailLocations',
            'module_data'
        ));
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, User $user)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if($request['npassword'] != ''){
            $pass = bcrypt($request['npassword']);
        }else{
            $pass = $request['password'];
        }
		
		$validator = $this->edit_validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

		
		if(isset($request['moduleList']))
			$moduleList = implode(',',$request['moduleList']);
		else
			$moduleList = '';
		
        $user->update([
            'name' => $request['name'],
            'phone' => $request['phone'],
            'role' => $request['role'],
			'module_access' => $moduleList,
            'claim' => $request['claim'],
            'remember_token' => $request['_token'],
            'password' => $pass
        ]);

		$locVal = $request->get('location');
		$emailLocVal = $request->get('email_location');
		
        if($locVal != '')
        {
            if(is_array($locVal))
            {
                foreach ($locVal as $locValue) {
                    User_location::create([
                        'user' => $user->id,
                        'location' => $locValue,
                    ]);
                }
            }else{
                User_location::create([
                    'user' => $user->id,
                    'location' => $locVal,
                ]);
            }
        }
		
		if($emailLocVal != '')
        {
				\DB::table('user_email_locations')->where('user', '=', $user->id)->delete();
				foreach ($emailLocVal as $lv) {
					User_email_location::create([
						'user' => $user->id,
						'location' => $lv,
					]);
				}
        }
		
		return redirect()->route('user.index')->with('message','User has been updated successfully!');
    }

	
	public function userarchive($id , Request $request)
    {
		
		$statusCode = $request->st;
		
		
		if($statusCode == 1) {
		User::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('user.index')->with('message','Archived successfully!');
			
		} else {
		User::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('user.index')->with('message','Unarchived successfully!');
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

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'  => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role'  => 'required|max:255',
            'location'  => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    protected function edit_validator(array $data)
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
    }


    protected function removeSelectedLocation(Request $request, User $user)
    {
        $userLocation = User_location::where('id', '=', $request->id)->get()->first();
        $userLocation->delete();
        return redirect()->action(
            'UserController@edit', $request->user
        );
    }
}
