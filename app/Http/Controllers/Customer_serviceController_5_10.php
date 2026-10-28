<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\Customer_serviceRequest;
use App\Customer_service;
use App\Customer_problem;
use App\Customer_servicefollow;
// use App\Claim;
use App\User;
use Session;
use Validator;

class Customer_serviceController extends Controller
{
    protected $users;

    public function __construct()
    {
        $this->middleware('auth');
        $this->users = User::where('status', '=', 1)->pluck('name', 'id');   
        $this->users->prepend('- Select User -', '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role != 'Owner')
        {
			
			$services = DB::table('customer_services')
						   ->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
						    ->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
						    ->where('customer_servicefollows.location', '=', Session::get('location_id'))
                            // ->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
							->select('customer_servicefollows.*','customer_services.*',DB::raw("(select (name) from users where id = `customer_servicefollows`.created_by) as created_byname "),DB::raw("(select (name)  from users where id = `customer_servicefollows`.followup_by) as followup_byname "))
							->get();

        } else {
           // $services  = Customer_service::all();
			$services = DB::table('customer_services')
						   ->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
						    ->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id )"))
						    ->where('customer_servicefollows.location', '=', Session::get('location_id'))
							->select('customer_servicefollows.*','customer_services.*', DB::raw("(select (name)  from users where id = `customer_servicefollows`.created_by) as created_byname "),DB::raw("(select (name)  from users where id = `customer_servicefollows`.followup_by) as followup_byname "))
							//->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
							->get();
        }
		$locationCode = DB::table('locations')->select('location_code')->where('id', '=', Session::get('location_id'))->pluck('location_code');
		
        return view('customer_services.index', compact('services','locationCode'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $reportIds = Customer_service::select(DB::row('CONCAT(`RPT_`," ",`id`) as reportid'),'id')->pluck('reportid', 'id');
        //$reportIds  = Customer_problem::select('id', DB::raw('CONCAT("RPT_", id) AS reportid'))->orderby('id')->pluck('reportid', 'id');
		
		$reportIds  = Customer_problem::leftJoin('locations', 'locations.id', '=', 'customer_problems.location')
            ->select('customer_problems.id','customer_problems.customer_name','customer_problems.customer_last_name','customer_problems.created_at','customer_problems.location','locations.location_code', DB::raw('CONCAT(customer_problems.customer_last_name," , "," ",customer_problems.customer_name," - ",DATE(customer_problems.created_at),"-PR-",locations.location_code,"-",customer_problems.id) AS reportid'))->orderby('reportid')->pluck('reportid', 'customer_problems.id');
			
		$reportIds->prepend('- Select Report ID -', '');
		
		//$users = $this->users;
        $users = \DB::table('users')
            ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
            ->where('users.status', '=', 1)
			->where('user_locations.location', '=', Session::get('location_id'))
			->pluck('users.name', 'users.id');
		
		return view('customer_services.create', compact('reportIds', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Customer_serviceRequest $request)
    {
       if($request->get("csr_type") == 1){
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }else{
                $reportid   = $request->get('report_id');
                // $claim      = Claim::where('report_id', '=', $reportid)->select('id')->first();
                // $claimid    = ($claim)?$claim->id:'';
                $claimid    = NULL;
            }
        }else{
            $claimid    = NULL;
            $reportid   = NULL;
        }  
        
        $insertArray = array(
            'csr_type'          => $request->get('csr_type'),
            'report_id'         => $reportid,
            'claim_id'          => $claimid,
            'created_on_date'   => date('Y-m-d', time()), 
            'created_on_time'   => date('H:i:s', time()), 
			'customer_name'     => $request->get('customer_name'),
			'customer_phone'    => $request->get('customer_phone'),
			'customer_lastname' => $request->get('customer_lastname'),
			'customer_add'      => $request->get('customer_add'),
		);
		
		

        $customer_service = Customer_service::create($insertArray);
		
		 $insertArrayNew = array(
			'customer_service_id' => $customer_service->id,
            'notes'             => $request->get('notes'), 
			'status'            => $request->get('status'),
            'created_by'        => Auth::user()->id, 
            'followup_by'       => $request->get('followup_by'), 
            'followup_date'     => $request->get('followup_date'), 
            'followup_time'     => $request->get('followup_time'), 
            'followup_notes'    => $request->get('followup_notes'), 
            'mode_of_contact'   => $request->get('mode_of_contact'), 
            'location'          => Session::get('location_id')
        );
		
		$customer_serviceFollow = Customer_servicefollow::create($insertArrayNew);

        return redirect()->route('service.index')->with('message','CSR has been added successfully!');

    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'report_id' => 'required',
        ]);
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
    public function edit(Customer_service $service)
    {
		
		 /* echo "<pre>";
		print_r($service->id);
		exit;  */
	
       // $reportIds  = Customer_problem::select('id', DB::raw('CONCAT("RPT_", id) AS reportid'))->orderby('id')->pluck('reportid', 'id');
	   
	   // $reportIds  = Customer_problem::select('id','customer_name','customer_last_name', DB::raw('CONCAT(customer_last_name,","," ",customer_name,"-",id) AS reportid'))->orderby('reportid')->pluck('reportid', 'id');
	   
	   
       // $reportIds->prepend('- Select Report ID -', '');

		$reportIds  = Customer_problem::leftJoin('locations', 'locations.id', '=', 'customer_problems.location')
            ->select('customer_problems.id','customer_problems.customer_name','customer_problems.customer_last_name','customer_problems.created_at','customer_problems.location','locations.location_code', DB::raw('CONCAT(customer_problems.customer_last_name," , "," ",customer_problems.customer_name," - ",DATE(customer_problems.created_at),"-PR-",locations.location_code,"-",customer_problems.id) AS reportid'))->orderby('reportid')->pluck('reportid', 'customer_problems.id');
			
		$reportIds->prepend('- Select Report ID -', '');
        
		$servicefollow = Customer_servicefollow::where('customer_service_id','=', $service->id)->get();
		
		
		
		foreach($servicefollow as $sf){
			
			
			//$users      = $this->users;
			$created_by = User::where('id','=', $sf->created_by)->first();
			 $viewData = array(
            'notes'             => $sf->notes, 
			'status'            => $sf->status,
            'followup_by'       => $sf->followup_by, 
            'followup_date'     => $sf->followup_date, 
            'followup_time'     => $sf->followup_time, 
            'followup_notes'    => $sf->followup_notes, 
            'mode_of_contact'   => $sf->mode_of_contact
			) ;
			
		}
		
		$history = DB::table('customer_services','customer_servicefollows','users')
							->Select(DB::raw("(select name  from users where customer_servicefollows.created_by = `users`.id) as created_name"), 'customer_services.*','customer_servicefollows.*','users.id','users.name' )
						   ->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
						   ->leftJoin('users', 'customer_servicefollows.followup_by', '=', 'users.id')
						   ->where('customer_servicefollows.customer_service_id', '=', $service->id)
						    ->where('customer_servicefollows.location', '=', Session::get('location_id'))
							
                            //->where('customer_seravicefollows.followup_by', '=', Auth::user()->id)
							->get();
		
		$users = \DB::table('users')
            ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
            ->where('users.status', '=', 1)
			->where('user_locations.location', '=', Session::get('location_id'))
			->pluck('users.name', 'users.id');
			
        return view('customer_services.edit', compact('service', 'users', 'created_by', 'reportIds','servicefollow','viewData','history'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Customer_serviceRequest $request, Customer_service $service)
    {
        // echo "<pre>";
        // print_r($request->all());
        // echo "=================================================";
        // print_r($service);
        // die();
        
        if($request->get("csr_type") == 1){
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }else{
                $reportid   = $request->get('report_id');
                // $claim      = Claim::where('report_id', '=', $reportid)->select('id')->first();
                // $claimid    = ($claim)?$claim->id:'';
                $claimid    = NULL;
            }
        }else{
            $claimid    = NULL;
            $reportid   = NULL;
        }  
        
        $updateArray = array(
            'csr_type'          => $request->get('csr_type'),
            'report_id'         => $reportid,
            'claim_id'          => $claimid,
			'customer_name'     => $request->get('customer_name'),
			'customer_phone'    => $request->get('customer_phone'),
			'customer_lastname'    => $request->get('customer_lastname'),
			'customer_add'      => $request->get('customer_add'),
			
        );

        $customer_service = Customer_service::where('id', '=', $service->id)->update($updateArray);
		
		
	$updateArrayNew = array(
	    'customer_service_id' => $service->id,
            'notes'             => $request->get('notes'), 
	    'status'            => $request->get('status'),
            'created_by'        => Auth::user()->id, 
            'followup_by'       => $request->get('followup_by'), 
            'followup_date'     => $request->get('followup_date'), 
            'followup_time'     => $request->get('followup_time'), 
            'followup_notes'    => $request->get('followup_notes'), 
            'mode_of_contact'   => $request->get('mode_of_contact'), 
            'location'          => Session::get('location_id')
        );
		
		//$customer_serviceFollow = Customer_servicefollow::where('customer_service_id', '=', $service->id)->update($updateArrayNew);
		$customer_serviceFollow = Customer_servicefollow::create($updateArrayNew);
	
	 $services = DB::table('customer_services')
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select(DB::raw("count(customer_services.id) as total_new_csr"), 'customer_services.id as id')
					->pluck('total_new_csr');
		
		Session::put('total_new_csr', $services[0]);
		
		$all_new_csr_data = DB::table('customer_services')		            
					->leftJoin('customer_servicefollows', 'customer_servicefollows.customer_service_id', '=', 'customer_services.id')
					->where('customer_servicefollows.id', '=', DB::raw("(select max(customer_servicefollows.id) from customer_servicefollows where customer_service_id = `customer_services`.id)"))
					->where('customer_servicefollows.status', '!=', 2)
					->where('customer_servicefollows.location', '=', Session::get('location_id'))
					->where('customer_servicefollows.followup_by', '=', Auth::user()->id)
					->where('customer_servicefollows.created_at', '>=', "'".date('Y-m-d')."'")
					->select('customer_services.customer_name as customer_name', 'customer_services.id as id', 'customer_servicefollows.followup_date', 'customer_servicefollows.followup_time')
					->get();
		
		
		Session::put('all_new_csr_data', $all_new_csr_data);
		
        return redirect()->route('service.index')->with('message','CSR has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer_service $service)
    {
       $service->delete();
	   $service = Customer_servicefollow::where('customer_service_id', '=', $service['id'])->delete();
       return redirect()->route('service.index')->with('message','CSR report has been deleted successfully!'); 
    }
	
	
	
	public function reportDetails(Request $request)
    {
		$customerID = $request->cID;
		
		$row = \DB::table('customer_problems')
            ->select('customer_problems.*')
            ->where('customer_problems.id', '=', $customerID)
			->get();
		echo $data = $row[0]->customer_name."|".$row[0]->customer_last_name."|".$row[0]->address."|".$row[0]->cell_phone;
    }
}
