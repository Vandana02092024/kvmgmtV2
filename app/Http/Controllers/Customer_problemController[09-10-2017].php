<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\Customer_problemRequest;
use App\User;
use App\Brand;
use App\Color;
use App\Material;
use App\Size;
use App\Type;
use App\Garment_description;
use App\Search_result;
use App\Customer_notified;
use App\Customer_problem;
use App\ProblemReportLogs;
use Session;
use Validator;

class Customer_problemController extends Controller
{
    protected $users;
    protected $problem;
    protected $status;
    protected $brand;
    protected $color;
    protected $material;
    protected $size;
    protected $type;

    public function __construct()
    {
        $this->middleware('auth');
        $this->users = User::where('role', '!=', 'Owner')->where('status', '=', 1)->pluck('name', 'id');   
        $this->users->prepend('- Select User -', '');
		
        $this->problem = Customer_problem::distinct()->pluck('problem', 'problem');
        $this->problem->prepend('- Select Problem -', '');
        $this->status  = array(-1 => '- Select Status -', 'Not Resolved', 'Resolved', 'N/A');

        $this->brand = Brand::orderBy('brand')->pluck('brand','id');
		$this->brand->prepend('- Please Select -', '');
		
        $this->color = Color::pluck('color','id');
		$this->color->prepend('- Please Select -', '');
		
        $this->material = Material::orderBy('material')->pluck('material','id');
		$this->material->prepend('- Please Select -', '');
		
        $this->size = Size::orderBy('size')->pluck('size','id');
		$this->size->prepend('- Please Select -', '');
		
        $this->type = Type::orderBy('type')->pluck('type','id');
		$this->type->prepend('- Please Select -', '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if (Auth::user()->role != 'Owner')
        // {
			$locationCode = DB::table('locations')->select('location_code')->where('id', '=', Session::get('location_id'))->pluck('location_code');

            $reports = DB::table('customer_problems')->leftJoin('users', 'users.id','=','customer_problems.created_by')->where('customer_problems.location', '=', Session::get('location_id'))->orderBy('customer_problems.created_at', 'DESC')->get();
			
        // }else{
        //     $reports = DB::table('customer_problems')->get();
        // }
		// $problem = Customer_problem::select('problem')->groupBy('problem')->get();
		
        $problem = $this->problem;
        $status  = $this->status;
		$users   = $this->users;
		
	    // echo "<pre>";
        // echo Auth::user()->role;
        // print_r($reports);
        // die();

        return view('customer_reports.index', compact('reports', 'problem', 'status','locationCode'));
    }


    public function searchReport(Request $request)
    {
        $another = 0;
		$problem = trim($request->get('problem'));
        $status = trim($request->get('status'));

        $report = DB::table('customer_problems');

        if($problem != '')
        {
            $report->where('problem', '=', $request->get('problem'));
        }

        if($status >= 0)
        {
            $status = ($status == 2)?'':$status;
            $report->where('resolution', '=', $status);
        }

		if(($request->get('filled_from') != '') && ($request->get('filled_to') != ''))
        {
            $from = date('Y-m-d', strtotime($request->get('filled_from')));
            $to = date('Y-m-d', strtotime($request->get('filled_to')));
            $report->where('filled_date', '>=', $from)
            ->where('filled_date', '<=', $to);
        }

        if (Auth::user()->role != 'Owner')
            $report->where('location', '=', Session::get('location_id'));

        $reports = $report->get();

        // echo "<pre>";
        // print_r($reports);
        // die();
        $problem = $this->problem;
        $status  = $this->status;
        return view('customer_reports.index', compact('reports', 'problem', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		$usersList   = User::pluck('name','name');
		$addUserList = Search_result::where('by_who', '!=', ' ')->pluck('by_who','by_who');
		$allUsers =  \DB::table('users')
                     ->select('name');
         
		$custmerUser = \DB::table('customer_notifieds')
                     ->select('by_who');
				
		$searchUser = \DB::table('search_results')
						->select('by_who')
						->where('by_who', '!=', '');
						
		$users = $allUsers->union($custmerUser)->union($searchUser)->orderBy('name')->pluck('name','name');
	
        $brand = $this->brand;
        $color = $this->color;
        $material = $this->material;
        $size = $this->size;
        $type = $this->type;
        return view('customer_reports.create', compact('users', 'brand', 'color', 'material', 'size', 'type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Customer_problemRequest $request)
    {
		$validator = $this->validator($request->all());
		if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }
		
		
		$insertArray = array(
            'filled_date' => date('Y-m-d',strtotime($request['filled_date'])),
            'clerk' => $request['clerk'],
            'customer_name' => $request['customer_name'],
            'customer_last_name' => $request['customer_last_name'],
            // 'home_phone' => $request['home_phone'],
            // 'work_phone' => $request['work_phone'],
            // 'cell_phone' => $request['cell_phone'],
			'phone1'         => $request['phone1'],
            'phone2'         => $request['phone2'],
            'phone1_type'    => $request['phone1_type'],
			'phone2_type'    => $request['phone2_type'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'zip' => $request['zip'],
           // 'problem' => ($request['problem'] == 'Other')?$request['other_problem']:$request['problem'],
           'problem'        => $request['problem'],
			'other_problem'  => $request['other_problem'],
            'missing_date' => date('Y-m-d',strtotime($request['missing_date'])),
            'invoice_tag' => $request['invoice_tag'],
            'tag_no'   => $request['tag_no'],
            'location' => Session::get('location_id'),
			'assigned_to' => Auth::user()->id,
            'created_by' => Auth::user()->id
        );

        if(isset($request['resolution']))
        {
			if($request['resolution'] == 1)
				$insertArray['status'] = "Closed - Resolved";
			else
				$insertArray['status'] = "Closed - Not Resolved";
			
            $insertArray['resolution'] = $request['resolution'];
            $insertArray['resolved_as']     = $request['resolved_as'];

            if(isset($request['resolved_as']) && $request['resolution'] == 1){
                $insertArray['resolved_as'] = $request['resolved_as'];
            
                switch ($request['resolved_as']) {
                    case 'Found':
                        $insertArray['resolved_date']   = ($request['found_date'] != '')?date('Y-m-d',strtotime($request['found_date'])):'';
                        $insertArray['new_location']    = $request['new_location'];
                        break;

                    case 'Fixed':
                        $insertArray['resolved_date']   = ($request['fixed_date'] != '')?date('Y-m-d',strtotime($request['fixed_date'])):'';
                        break;

                    case 'Claim':
                        $insertArray['resolved_date']           = ($request['clim_date'] != '')?date('Y-m-d',strtotime($request['clim_date'])):'';
                        $insertArray['claim_submitted_date']    =  ($request['claim_submitted_date'] != '')?date('Y-m-d',strtotime($request['claim_submitted_date'])):'';
                        break;
                    
                }
            }else{
                $insertArray['closed_date']    = ($request['closed_date'] != '')?date('Y-m-d',strtotime($request['closed_date'])):'';
            }
        } else {
			$insertArray['status'] = "Open - Submitted";
		}

		if($request['claim_check'] == 1){
            $insertArray['claim_check']         = $request['claim_check'];
            $insertArray['claim_check_val']     = $request['claim_check_val'];
        }else{
            $insertArray['claim_check']    = $request['claim_check'];
        }

		
        $customer_problem = Customer_problem::create($insertArray);

		ProblemReportLogs::create([
                    'problem_id'	 => $customer_problem->id,
                    'action_dt'      => date("Y-m-d"),
                    'action'         => 'created',
                    'user_name' 	 => Auth::user()->id
		]); 
				

        // Garment Description //
        $gd = count($request['type']);
        for ($i=0; $i < $gd; $i++) { 

        if(($request['type'][$i] != '') || ($request['size'][$i] != '') || ($request['brand_name'][$i] != '') || ($request['color'][$i] != '') || ($request['material'][$i] != '')){
                Garment_description::create([
                    'problem_id' => $customer_problem->id,
                    'type'       => $request['type'][$i],
                    'size'       => $request['size'][$i],
                    'brand_name' => $request['brand_name'][$i],
                    'color'      => $request['color'][$i],
                    'material'   => $request['material'][$i]
                ]);    
            }            
         }


        // Search Result //
        $sr = count($request['search_date']);
        for ($i=0; $i < $sr; $i++) { 
			if(($request['search_date'][$i] != '') || ($request['search_by_who'][$i] != '') || ($request['action'][$i] != '') || ($request['resolved'][$i] != '')){
                Search_result::create([
                    'problem_id'  => $customer_problem->id,
                    'search_date' => date('Y-m-d',strtotime($request['search_date'][$i])),
                    'by_who'      => $request['search_by_who'][$i],
                    'action'      => $request['action'][$i],
                    'resolved'    => $request['resolved'][$i],
                    'resolved_status'   => 1
                ]);    
            }            
        }


        // Customer Notified //
        $cn = count($request['customer_notified_date']);
        for ($i=0; $i < $cn; $i++) { 

            if(($request['customer_notified_date'][$i] != '') || ($request['by_who'][$i] != '') || ($request['comments'][$i] != '') || ($request['manager_action'][$i] != '')){
                Customer_notified::create([
                    'problem_id'  => $customer_problem->id,
                    'customer_notified_date' => date('Y-m-d',strtotime($request['customer_notified_date'][$i])),
                    'by_who'   => $request['by_who'][$i],
                    'comments' => $request['comments'][$i],
                    'action'   => $request['manager_action'][$i]
                ]);    
            }            
        }

        return redirect()->route('report.index')->with('message','Problem report has been added successfully!');
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
    public function edit(Customer_problem $report)
    {
        // echo "<pre>";
        // print_r($report);
        // die();

        $brand = $this->brand;
        $color = $this->color;
        $material = $this->material;
        $size = $this->size;
        $type = $this->type;

		$garment_description = Garment_description::where('problem_id','=', $report->id)->get();
        $search_result       = Search_result::where('problem_id','=', $report->id)->get();
        $customer_notified   = Customer_notified::where('problem_id','=', $report->id)->get();
		
		$allUsers = \DB::table('users')
                ->select('name');
         
		$custmerUser = \DB::table('customer_notifieds')
                ->select('by_who');
				
		$searchUser = \DB::table('search_results')
						->select('by_who')
						->where('by_who', '!=', '');
						
		$users = $allUsers->union($custmerUser)->union($searchUser)->orderBy('name')->pluck('name','name');
		
		$problemLogs = \DB::table('problem_report_logs')
						->leftJoin('users', 'problem_report_logs.user_name', '=', 'users.id')
						->select('problem_report_logs.*', 'users.name as name')
						->where('problem_id', '=', $report->id)
						->orderBy('created_at')
						->get();
						
        return view('customer_reports.edit', compact('users', 'problemLogs', 'report', 'garment_description', 'search_result', 'customer_notified', 'brand', 'color', 'material', 'size', 'type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }
		
		// if($request['phone1_type'] == "Home")
			// $home_phone = $request['phone1'];
		// else if($request['phone1_type'] == "Work")
			// $work_phone =  $request['phone1'];
		// else if($request['phone1_type'] == "Ceil")
			// $ceil_phone = $request['phone1'];
		
		// if($request['phone2_type'] == "Home")
			// $home_phone = $request['phone2'];
		// else if($request['phone2_type'] == "Work")
			// $work_phone =  $request['phone2'];
		// else if($request['phone2_type'] == "Ceil")
			// $ceil_phone = $request['phone2'];

        $updateArray = array(
            'filled_date'   => date('Y-m-d',strtotime($request['filled_date'])),
            'clerk'         => $request['clerk'],
            'customer_name' => $request['customer_name'],
            'customer_last_name' => $request['customer_last_name'],
            'phone1'         => $request['phone1'],
            'phone2'         => $request['phone2'],
            'phone1_type'    => $request['phone1_type'],
			'phone2_type'    => $request['phone2_type'],
            'address'        => $request['address'],
            'city'			 => $request['city'],
            'state'			 => $request['state'],
            'zip' 			 => $request['zip'],
           // 'problem'        => ($request['problem'] == 'Other')?$request['other_problem']:$request['problem'],
           'problem'        => $request['problem'],
		'other_problem'  => $request['other_problem'],
            'claim_check'    => $request['claim_check'],
            'missing_date'   => date('Y-m-d',strtotime($request['missing_date'])),
            'invoice_tag'    => $request['invoice_tag'],
            'tag_no'         => $request['tag_no'],
            'location'       => Session::get('location_id'),
        );
		
		
        if(isset($request['resolution']))
        {
				
			if($request['resolution'] == 1)
				$updateArray['status'] = "Closed - Resolved";
			else
				$updateArray['status'] = "Closed - Not Resolved";
			
			 ProblemReportLogs::create([
						'problem_id'	 => $request['report_id'],
						'action_dt'      => date("Y-m-d"),
						'action'         => 'closed',
						'user_name' 	 => Auth::user()->id
			  ]); 
			  
            $updateArray['resolution'] = $request['resolution'];
            $updateArray['resolved_as']     = $request['resolved_as'];
			
			
            if(isset($request['resolved_as']) && $request['resolution'] == 1)
            {
                $updateArray['resolved_as'] = $request['resolved_as'];
            
                switch ($request['resolved_as'])
                {
                    case 'Found':
                        $updateArray['resolved_date']   = ($request['found_date'] != '')?date('Y-m-d',strtotime($request['found_date'])):'';
                        $updateArray['new_location']    = $request['new_location'];
                        break;

                    case 'Fixed':
                        $updateArray['resolved_date']       = ($request['fixed_date'] != '')?date('Y-m-d',strtotime($request['fixed_date'])):'';
                        break;

                    case 'Claim':
                        $updateArray['resolved_date']           = ($request['clim_date'] != '')?date('Y-m-d',strtotime($request['clim_date'])):'';
                        $updateArray['claim_submitted_date']    = ($request['claim_submitted_date'] != '')?date('Y-m-d',strtotime($request['claim_submitted_date'])):'';
                        break;
                }
			} else
            {	
				$updateArray['closed_date']    = ($request['closed_date'] != '')?date('Y-m-d',strtotime($request['closed_date'])):'';
            }
        } else {
				$updateArray['status'] = "Open – Updated";
				ProblemReportLogs::create([
						'problem_id'	 => $request['report_id'],
						'action_dt'      => date("Y-m-d"),
						'action'         => 'edited',
						'user_name' 	 => Auth::user()->id
				]); 
				
		}

        if($request['claim_check'] == 1){
            $updateArray['claim_check']         = $request['claim_check'];
            $updateArray['claim_check_val']     = $request['claim_check_val'];
        }else{
            $updateArray['claim_check']    = $request['claim_check'];
        }
		
		$customer_problem = Customer_problem::where('id', '=', $request['report_id'])->update($updateArray);
		
		// Garment Description //
        // Update GD //
        if(isset($request['gdid']))
        {
            $gdid = $request['gdid'];
            foreach ($gdid as $key) {
                if(($request['type'.$key] != '') || ($request['size'.$key] != '') || ($request['brand_name'.$key] != '') || ($request['color'.$key] != '') || ($request['material'.$key] != '')){
                    Garment_description::where('id', '=', $key)->update([
                        'type'       => $request['type'.$key],
                        'size'       => $request['size'.$key],
                        'brand_name' => $request['brand_name'.$key],
                        'color'      => $request['color'.$key],
                        'material'   => $request['material'.$key]
                    ]);    
                }
            }
        }

        $gd = count($request['type']);
        for ($i=0; $i < $gd; $i++) { 

            if(($request['type'][$i] != '') || ($request['size'][$i] != '') || ($request['brand_name'][$i] != '') || ($request['color'][$i] != '') || ($request['material'][$i] != '')){
                Garment_description::create([
                    'problem_id' => $request['report_id'],
                    'type'       => $request['type'][$i],
                    'size'       => $request['size'][$i],
                    'brand_name' => $request['brand_name'][$i],
                    'color'      => $request['color'][$i],
                    'material'   => $request['material'][$i]
                ]);    
            }            
        }

        // Search Result //
        // Update SR //
        if(isset($request['srid']))
        {
            $srid = $request['srid'];
            foreach ($srid as $key) {
                if(($request['search_date'.$key] != '') || ($request['search_by_who'.$key] != '') || ($request['action'.$key] != '') || ($request['resolved'.$key] != '')){
                    Search_result::where('id', '=', $key)->update([
                        'search_date' => date('Y-m-d',strtotime($request['search_date'.$key])),
                        'by_who'      => $request['search_by_who'.$key],
                        'action'      => $request['action'.$key],
                        'resolved'    => $request['resolved'.$key],
                        'resolved_status'   => 1
                    ]);    
                }
            }
        }

        $sr = count($request['search_date']);
		for ($i=0; $i < $sr; $i++) { 

            if(($request['search_date'][$i] != '') || ($request['search_by_who'][$i] != '') || ($request['action'][$i] != '') || ($request['resolved'][$i] != '')){
                Search_result::create([
                    'problem_id'  => $request['report_id'],
                    'search_date' => date('Y-m-d',strtotime($request['search_date'][$i])),
                    'by_who'      => $request['search_by_who'][$i],
                    'action'      => $request['action'][$i],
                    'resolved'    => $request['resolved'][$i],
                    'resolved_status'   => 1
                ]);    
            }            
        }

		// Customer Notified //
        // Update CN //
        if(isset($request['cnid']))
        {
            $cnid = $request['cnid'];
            foreach ($cnid as $key) {
                if(($request['customer_notified_date'.$key] != '') || ($request['by_who'.$key] != '') || ($request['comments'.$key] != '') || ($request['manager_action'.$key] != '')){
                    Customer_notified::where('id', '=', $key)->update([
                        'customer_notified_date' => date('Y-m-d',strtotime($request['customer_notified_date'.$key])),
                        'by_who'   => $request['by_who'.$key],
                        'comments' => $request['comments'.$key],
                        'action'   => $request['manager_action'.$key]
                    ]);    
                }  
            }
        }

        $cn = count($request['customer_notified_date']);
        for ($i=0; $i < $cn; $i++) { 

            if(($request['customer_notified_date'][$i] != '') || ($request['by_who'][$i] != '') || ($request['comments'][$i] != '') || ($request['manager_action'][$i] != '')){
                Customer_notified::create([
                    'problem_id'  => $request['report_id'],
                    'customer_notified_date' => date('Y-m-d',strtotime($request['customer_notified_date'][$i])),
                    'by_who'   => $request['by_who'][$i],
                    'comments' => $request['comments'][$i],
                    'action'   => $request['manager_action'][$i]
                ]);    
            }            
        }

        return redirect()->route('report.index')->with('message','Problem report has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer_problem $report)
    {	
		  $a = Garment_description::where('problem_id', '=', $report['id'])->delete();
		  $b = Search_result::where('problem_id', '=', $report['id'])->delete();
		  $c = Customer_notified::where('problem_id', '=', $report['id'])->delete();
		  $report->delete();
		  
		  return redirect()->route('report.index')->with('message','Problem report has been deleted successfully!');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'filled_date'  => 'required|date',
            'customer_name' => 'required|max:255',
            'clerk'  => 'required|max:255',
            'phone1'  => 'required',
			'claim_check' => 'required',
            'missing_date' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required|digits:5',
        ]);
    }

	public function removeProblemSub(Request $request)
    {
		
		$option = $request->get('op');
        $id     = $request->get('id');
        switch($option)
        {
            case 1:
                Garment_description::where('id', '=', $id)->delete();
                return "yes";
            break;

            case 2:
                Search_result::where('id', '=', $id)->delete();
                return "yes";
            break;

            case 3:
                Customer_notified::where('id', '=', $id)->delete();
                return "yes";
            break;
        }

        return "no";
    }



    public function searchGarments(Request $request)
    {

        $reslult = '';
        $search  = DB::table('garment_descriptions')
            ->leftJoin('customer_problems', 'customer_problems.id', '=', 'garment_descriptions.problem_id');

        $ids = $request->get('id');
        $idx = array_filter($ids);

        if(!empty($idx)){

            foreach ($ids as $key => $value)
            {
                if($value != '')
                {
                    switch ($key) {
                        case 0:
                            $search->orwhere('garment_descriptions.type', 'like', '%'.$value.'%');
                            break;

                        case 1:
                            $search->orwhere('garment_descriptions.size', 'like', '%'.$value.'%');
                            break;

                        case 2:
                            $search->orwhere('garment_descriptions.brand_name', 'like', '%'.$value.'%');
                            break;

                        case 3:
                            $search->orwhere('garment_descriptions.color', 'like', '%'.$value.'%');
                            break;

                        case 4:
                            $search->orwhere('garment_descriptions.material', 'like', '%'.$value.'%');
                            break;
                    }
                    
                }
            }

            if (Auth::user()->role != 'Owner')
            {
                $search->where('customer_problems.location', '=', Session::get('location_id'));
            }

            $search = $search->select('customer_problems.customer_name', 'customer_problems.problem', 
                'customer_problems.id', 'customer_problems.filled_date',  'garment_descriptions.type','garment_descriptions.size', 
                'garment_descriptions.brand_name', 'garment_descriptions.color', 'garment_descriptions.material')->get();

            $reslult .= '<ul>';
            foreach ($search as $searchVal) {
                $reslult .= '<li>Customer '.$searchVal->customer_name.' '.$searchVal->problem.' ';
                $reslult .= ($searchVal->type != '')?' Type: '.$searchVal->type.', ':'';
                $reslult .= ($searchVal->size != '')?' Size: '.$searchVal->size.', ':'';
                $reslult .= ($searchVal->brand_name != '')?' Brand: '.$searchVal->brand_name.', ':'';
                $reslult .= ($searchVal->color != '')?' Color: '.$searchVal->color.', ':'';
                $reslult .= ($searchVal->material != '')?' Material: '.$searchVal->material:'';
                $reslult .= ' on '.date('m/d/Y', strtotime($searchVal->filled_date)).' Problem Report #RPT_'
                        .$searchVal->id.' is generated. </li>';
            }
            $reslult .= '</ul>';
            echo $reslult;
        }else{
            echo "";
        }
    }


    public function add_more(Request $request)
    {
        $brand = $this->brand;
        $color = $this->color;
        $material = $this->material;
        $size = $this->size;
        $type = $this->type;

        $id = $request->get('id'); 
        return view('customer_reports.addmore', compact('id', 'brand', 'color', 'material', 'size', 'type'));
    }
	
	public function addNew(Request $request)
    {
        $add_new = $request->get('add_new');
        $add     = $request->get('add');

        if ($add_new != '') {
            switch ($add) {
                case 1:
                    $id = Type::create(['type' => $add_new])->id;
                    break;
                case 3:
                    //Brand::create(['brand' => $add_new]);
					 $id = Brand::create(['brand' => $add_new])->id;
                    break;

                case 4:
                     $id = Color::create(['color' => $add_new])->id;
                    break;

                case 5:
                     $id = Material::create(['material' => $add_new])->id;
                    break;

                case 2:
                    $id = Size::create(['size' => $add_new])->id;
                    break;
            }
			
			 return '<option value='.$id.'>'.$add_new.'</option>';
        }
    }

}
