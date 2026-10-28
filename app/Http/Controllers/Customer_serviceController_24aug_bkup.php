<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\Customer_serviceRequest;
use App\Customer_service;
use App\Customer_problem;
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
        $this->users = User::where('role', '!=', 'Owner')->pluck('name', 'id');   
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
            $services = DB::table('customer_services')->where('location', '=', Session::get('location_id'))
                            ->where('created_by', '=', Auth::user()->id)
                            ->orwhere('followup_by', '=', Auth::user()->id)
                            ->get();

        }else{
            $services  = Customer_service::all();
        }
        
        return view('customer_services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $reportIds = Customer_service::select(DB::row('CONCAT(`RPT_`," ",`id`) as reportid'),'id')->pluck('reportid', 'id');
        $reportIds  = Customer_problem::select('id', DB::raw('CONCAT("RPT_", id) AS reportid'))->orderby('id')->pluck('reportid', 'id');
        $reportIds->prepend('- Select Report ID -', '');
        $users      = $this->users;
        
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
            'notes'             => $request->get('notes'), 
            'created_by'        => Auth::user()->id, 
            'followup_by'       => $request->get('followup_by'), 
            'followup_date'     => date('Y-m-d', strtotime($request->get('followup_date'))), 
            'followup_time'     => date('H:i:s', strtotime($request->get('followup_date').' '.$request->get('followup_time'))), 
            'followup_notes'    => $request->get('followup_notes'), 
            'mode_of_contact'   => $request->get('mode_of_contact'), 
            'location'          => Session::get('location_id')
        );

        $customer_service = Customer_service::create($insertArray);

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


        $reportIds  = Customer_problem::select('id', DB::raw('CONCAT("RPT_", id) AS reportid'))->orderby('id')->pluck('reportid', 'id');
        $reportIds->prepend('- Select Report ID -', '');


        $users      = $this->users;
        $created_by = User::where('id','=', $service->created_by)->first();


        return view('customer_services.edit', compact('service', 'users', 'created_by', 'reportIds'));
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
            'notes'             => $request->get('notes'), 
            'followup_by'       => $request->get('followup_by'), 
            'followup_date'     => date('Y-m-d', strtotime($request->get('followup_date'))), 
            'followup_time'     => date('H:i:s', strtotime($request->get('followup_date').' '.$request->get('followup_time'))), 
            'followup_notes'    => $request->get('followup_notes'), 
            'mode_of_contact'   => $request->get('mode_of_contact'), 
        );

        $customer_service = Customer_service::where('id', '=', $service->id)->update($updateArray);

        return redirect()->route('service.index')->with('message','CSR has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
