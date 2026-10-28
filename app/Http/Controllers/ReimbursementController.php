<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Requests\ReimbursementRequest;
use App\Reimbursement;
use App\Reimbursement_describe_item;
use App\Customer_problem;
use App\User;
use App\User_location;
use App\Location;
use Mail;
use Session;
use Validator;
use PDF;

class ReimbursementController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
    {
		/*$var['email'] = "ynidhi90@gmail.com";
		Mail::send('emails.test', $var, function($message) use ($var) {
					$message->from('ynidhi90@gmail.com','Claims - ZIPS Reimbursement');
					$message->to($var['email']);
					$message->cc("nitiyadav062@gmail.com");
					$message->subject("tsst");
		});
		*/

		//die;

		// echo "<pre>"; echo $originalClaimValue = Auth::user()->claim; exit();

		$status = false;



		if($request->isMethod("post")){
			if($request['status'] != 'All')
				$status = $request['status'];
		}

		if (Auth::user()->role != 'Owner')
        {
			$reimburses = DB::table('reimbursements')
							->select('reimbursements.*','users.name','locations.location_code','users.role')
							->leftJoin('users', 'users.id','=','reimbursements.user_id')
							->leftJoin('locations', 'locations.id','=','reimbursements.location_id')
						    ->where('reimbursements.location_id', '=', Session::get('location_id'));
			if($status)
				$reimburses = $reimburses->where('reimbursements.status', '=', $status);

			$reimburses = $reimburses->orderby('reimbursements.id','DESC')
							->get();

        } else {
           // $services  = Customer_service::all();
		    $reimburses = DB::table('reimbursements')
							->select('reimbursements.*','users.name','locations.location_code','users.role')
							->leftJoin('users', 'users.id','=','reimbursements.user_id')
							->leftJoin('locations', 'locations.id','=','reimbursements.location_id')
							->where('reimbursements.location_id', '=', Session::get('location_id'));
			if($status)
				$reimburses = $reimburses->where('reimbursements.status', '=', $status);

			$reimburses = $reimburses->orderby('reimbursements.id','DESC')
							->get();
        }

		foreach($reimburses as $reimburse) {
				$var = (array) $reimburse;
				$val = "CF-".$reimburse->location_code."-".str_pad($reimburse->id, 4, '0', STR_PAD_LEFT);
				$resolution = ucfirst($reimburse->resolution);
				$claimValue = $reimburse->proof_purchase;
				$approvedAmount = $reimburse->approved_amount;
				$originalClaimValue = Auth::user()->claim;
				if ($originalClaimValue > $claimValue && ($reimburse->resolution == 'check') && ($reimburse->resolution == 'checkstorecredit') && ($reimburse->approved_amount != '')) {
					$status = 'Payment Pending';
				 	foreach ($usersMail as $mail){
							$var = (array) $mail;
						    $var['location_code'] = $locationCode[0]->location_code;
						    $var['location'] = $locationCode[0]->location;
							$var['customer_name'] = Auth::user()->name;
							Mail::send('emails.reimstatus', $var, function($message) use ($var) {
								$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
								$message->to($var['email']);
								$message->cc("bvago@kvmgmt.com");
								$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Payment Pending');
							});
					}
			}
		}
		$reimbursement = array();
		// var_dump(); exit();
		return view('reimbursements.index', compact('reimburses','reimbursement'));
	}

	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
		 $userRole = Auth::user()->role;
		// $reportIds  = Customer_problem::select('id','customer_name','customer_last_name', DB::raw('CONCAT(customer_last_name,","," ",customer_name,"-",id) AS reportid'))->orderby('reportid')->pluck('reportid', 'id');
		 $reportIds  = Customer_problem::leftJoin('locations', 'locations.id', '=', 'customer_problems.location')
            ->select('customer_problems.id','customer_problems.customer_name','customer_problems.customer_last_name','customer_problems.created_at','customer_problems.location','locations.location_code', DB::raw('CONCAT(if(customer_problems.customer_last_name!="",if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_last_name," , "),CONCAT(customer_problems.customer_last_name," - ")),""),if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_name," - "),""),CONCAT(DATE(customer_problems.created_at)),CONCAT("-PR-"),CONCAT(locations.location_code),CONCAT("-"),CONCAT(LPAD(customer_problems.id,4,"0"))) AS reportid'))->orderby('reportid')->pluck('reportid', 'customer_problems.id');

		$reportIds->prepend('- Select Report ID -', '');
		$location = DB::table('locations')->where('id', '=', Session::get('location_id'))->get();
		$locVal = $location[0]->location;

		return view('reimbursements.create', compact('reportIds','locVal','userRole'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReimbursementRequest $request)
    {

		$location = Session::get('location_id');
		$user_id = Auth::user()->id;
		$originalClaimValue = Auth::user()->claim;
		$status = '';
		/****condition start for status****/
		if(($originalClaimValue < $request['proof_purchase']) &&  Auth::user()->role != 'Owner') {
				$status = 'Approved Pending';
		} else if(($originalClaimValue > $request['proof_purchase']) || Auth::user()->role == 'Owner') {
			if($request['resolution'] == '') {
					$status = 'Pending';
			} elseif($request['resolution'] == 'check' && ($request['credit_amount']) != '' && ($request['resolution_check']) != ''){
					$status = 'Paid';
			} elseif($request['resolution'] == 'storeCredit' && ($request['resolution_store_credit_added']) != '' && ($request['resolution_store_credit_check_date']) != ''){
					$status = 'Paid';
			} elseif($request['resolution'] == 'checkstorecredit' && ($request['resolution_check_store_credit_added']) != '' && ($request['resolution_check_store_credit_check']) != '' && $request['resolution_check_store_credit_check_date'] != '' && $request['resolution_check_store_credit_check_credit_number'] != '' ){
					$status = 'Paid';
			} elseif($request['resolution'] == 'cash' && $request['resolution_cash_paid'] != '' && $request['resolution_cash_paid_date'] != '') {
					$status = 'Paid';
			} else {
					$status = 'Pending';
			}
		}
		/***end****/

		$insertArray = array(
			'zip_type' => $request['zip_type'],
			'status' => $status,
			'report_id' => $request['report_id'],
            'today_date' => $request['today_date'],
			'location_id' => $location,
			'user_id' => $user_id,
            'claim_check' => $request['claim_check'],
			'claim_check_date' => $request['claim_check_date'],
			'special_claim_check' => $request['special_claim_check'],
			'first_name' => ucfirst($request['first_name']),
			'last_name' => ucfirst($request['last_name']),
			'address' => ucfirst($request['address']),
			'city' => ucfirst($request['city']),
			'state' => $request['state'],
			'zip_code' => $request['zip_code'],
			'original_claim_check' => $request['original_claim_check']!= '' ? $request['original_claim_check'] : '',
			'claim_detail' => $request['claim_detail']!= '' ? $request['claim_detail'] : '',
			'compensation' => $request['compensation']!= '' ?$request['compensation'] : '',
			'item' => $request['item']!= '' ? $request['item'] : '',
			'email_address' => $request['email_address'],
			'telephone_number' => $request['telephone_number'],
			'total_desc' => $request['total_desc'],
			'proof_purchase' => $request['proof_purchase'],
			'store' => $request['store'],
			'store_owner_manager' => ucfirst($request['store_owner_manager']),
			'receipts_attached' => $request['receipts_attached']!= '' ? $request['receipts_attached'] : '',
			'claim_type' => $request['claim_type']!= '' ? $request['claim_type'] : '',
			'damaged_desc' => ucfirst($request['damaged_desc']),
			'item_return' => $request['item_return']!= '' ? $request['item_return'] : '',
			'check_amount' => $request['check_amount'],
			'credit_amount' => $request['credit_amount']!= '' ? $request['credit_amount'] : '',
			'resolution' => $request['resolution'],
			'resolution_check_pickup_mail' => $request['resolution_check_pickup_mail']!= '' ? $request['resolution_check_pickup_mail'] : '',
			'resolution_check' => $request['resolution_check']!= '' ? $request['resolution_check'] : '',
			'resolution_store_credit_added' => $request['resolution_store_credit_added']!='' ? $request['resolution_store_credit_added'] : '',
			'resolution_store_credit_check_date' =>$request['resolution_store_credit_check_date']!='' ? $request['resolution_store_credit_check_date'] : '',
			'resolution_store_credit_amount' =>$request['resolution_store_credit_amount']!='' ? $request['resolution_store_credit_amount'] : '',
			'resolution_check_store_credit_pickup_mail' => $request['resolution_check_store_credit_pickup_mail']!= '' ? $request['resolution_check_store_credit_pickup_mail'] : '',
			'resolution_check_store_credit_added' => $request['resolution_check_store_credit_added']!='' ? $request['resolution_check_store_credit_added'] : '',
			'resolution_check_store_credit_added_amount' => $request['resolution_check_store_credit_added_amount']!='' ? $request['resolution_check_store_credit_added_amount'] : '',
			'resolution_check_store_credit_check' => $request['resolution_check_store_credit_check']!= '' ? $request['resolution_check_store_credit_check'] : '' ,
			'resolution_check_store_credit_check_date' => $request['resolution_check_store_credit_check_date']!= '' ? $request['resolution_check_store_credit_check_date'] : '' ,
			'resolution_check_store_credit_check_credit_number' => $request['resolution_check_store_credit_check_credit_number']!= '' ? $request['resolution_check_store_credit_check_credit_number'] : '' ,
			'resolution_check_store_credit_check_credit_amount' => $request['resolution_check_store_credit_check_credit_amount']!= '' ? $request['resolution_check_store_credit_check_credit_amount'] : '' ,
			'resolution_cash_paid' => $request['resolution_cash_paid']!= '' ? $request['resolution_cash_paid'] : '',
			'resolution_cash_paid_date' => $request['resolution_cash_paid_date']!='' ? $request['resolution_cash_paid_date'] : '',
		    'resolution_cash_paid_amount' => $request['resolution_cash_paid_amount']!='' ? $request['resolution_cash_paid_amount'] : ''
        );

		$reimbursement = Reimbursement::create($insertArray);

		$addDesc = count($request['amount_paid']);
        for ($i=0; $i < $addDesc; $i++) {
            if(($request['item_desc'][$i] != '') || ($request['material'][$i] != '') || ($request['color'][$i] != '') || ($request['date_purchase'][$i] != '') || ($request['amount_paid'][$i] != '') || ($request['purchase_location'][$i] != '') ){
               Reimbursement_describe_item::create([
					'reimburse_id'  => $reimbursement->id,
                    'item_desc'  => ucfirst($request['item_desc'][$i]),
                    'material'   => ucfirst($request['material'][$i]),
                    'color' => ucfirst($request['color'][$i]),
					'date_purchase' => $request['date_purchase'][$i],
                    'amount_paid'   => $request['amount_paid'][$i],
					'purchase_location'   => ucfirst($request['purchase_location'][$i])

                ]);
            }
        }

		$claimValue = $request['proof_purchase'];
		$userRole = Auth::user()->role;
		$originalClaimValue = Auth::user()->claim;
		$formId = $reimbursement->id;
		$locationCode = DB::table('locations')->select('location_code','location')->where('id', '=', Session::get('location_id'))->get('location_code','location');

		if($userRole == 'Employee' && $claimValue > $originalClaimValue){
					$usersMail = DB::table('user_locations','locations')
							->select('users.*','user_locations.*','locations.*')
                            ->leftjoin('users', 'users.id', '=', 'user_locations.user')
							->leftjoin('locations', 'locations.id', '=', 'user_locations.location')
                            ->where('users.role', '=','Supervisor')
							->where('users.status', '=','1')
							->where('user_locations.location', '=', $location)
                            ->get();
					foreach ($usersMail as $mail){
						$var = (array) $mail;
						$var['form_id'] = $formId;
						//$var['customer_name'] = Auth::user()->name;
						$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
						Mail::send('emails.reimbursement', $var, function($message) use ($var) {
							$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
							$message->to($var['email']);
							//$message->cc("brettvago@gmail.com");
							$message->cc("bvago@kvmgmt.com");
							$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');
						});
					}

					$ownerMail =  DB::table('users','user_email_locations')
								->select('users.*')
								->leftjoin('user_email_locations', 'user_email_locations.user', '=', 'users.id')
								->where('users.role', '=','Owner')
								->where('user_email_locations.location', '=', Session::get('location_id'))
								->where('users.status', '=','1')
								->get();

					foreach ($ownerMail as $mail){
							$var = (array) $mail;
							$var['form_id'] = $formId;
							$var['location_code'] = $locationCode[0]->location_code;
							$var['location'] =$locationCode[0]->location;
							//$var['customer_name'] = Auth::user()->name;
							$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
							Mail::send('emails.reimbursement', $var, function($message) use ($var) {
								$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
								$message->to($var['email']);
								$message->cc("bvago@kvmgmt.com");
								$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');
						});
					}


		} else if($userRole == 'Supervisor' && $claimValue > $originalClaimValue) {
					/* $usersMail = DB::table('users')
							->select('users.*')
                            ->where('users.role', '=','Owner')
							->where('users.status', '=','1')
                            ->get(); */

					$usersMail =  DB::table('users','user_email_locations')
								->select('users.*')
								->leftjoin('user_email_locations', 'user_email_locations.user', '=', 'users.id')
								->where('users.role', '=','Owner')
								->where('user_email_locations.location', '=', Session::get('location_id'))
								->where('users.status', '=','1')
								->get();

					foreach ($usersMail as $mail) {
						$var = (array) $mail;
						$var['form_id'] = $formId;
						$var['location_code'] = $locationCode[0]->location_code;
						$var['location'] =$locationCode[0]->location;
						//$var['customer_name'] = Auth::user()->name;
						$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
							Mail::send('emails.reimbursement', $var, function($message) use ($var) {
							$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
							$message->to($var['email']);
							$message->cc("bvago@kvmgmt.com");
							$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');
						});
					}
		} else {

		}

		return redirect()->route('reimbursement.index')->with('message','Reimbursement report has been added successfully!');
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
    // public function edit(Reimbursement $reimbursement)
	public function edit($id)
    {
		// echo "<pre>";
		// print_r( $reimbursement);
		// exit; 

		$reimbursement = Reimbursement::findOrFail($id);

		$form_Id = $reimbursement->id;
		// $form_Id = $reimbursement['id'];

		// $queryRole = DB::table('users','reimbursements')
		// 					->select('users.role','reimbursements.location_id')
        //                     ->leftjoin('reimbursements', 'users.id', '=', 'reimbursements.user_id')
        //                     ->where('reimbursements.id', '=',$form_Id)
        //                     ->get();

		
		$queryRole = DB::table('users')
			->select('users.role', 'reimbursements.location_id')
			->leftJoin('reimbursements', 'users.id', '=', 'reimbursements.user_id')
			->where('reimbursements.id', '=', $form_Id)
			->get();


		// $usersLocation = DB::table('users','user_locations')
		// 					->select(DB::raw('group_concat(user_locations.location) as userloc'))
        //                     ->leftjoin('user_locations', 'users.id', '=', 'user_locations.user')
		// 					->where('users.id', '=', Auth::user()->id)
		// 					->get();

		
		$usersLocation = DB::table('users')
			->select(DB::raw('GROUP_CONCAT(user_locations.location) as userloc'))
			->leftJoin('user_locations', 'users.id', '=', 'user_locations.user')
			->where('users.id', '=', Auth::user()->id)
			->get();


		$userlocArray = explode(",", $usersLocation[0]->userloc);


		$flag = 0;
		if($queryRole[0]->role == 'Employee'){
			if(Auth::user()->role == 'Owner'){
				$flag = 1;
			}elseif((Auth::user()->role == 'Supervisor') && (in_array($queryRole[0]->location_id,$userlocArray))){
				$flag = 1;
			}
		}
		if(($queryRole[0]->role == 'Supervisor') && (Auth::user()->role == 'Owner') ){
			$flag = 1;
		}

		if(($queryRole[0]->role == 'Owner')){
			$flag = 1;
		}

		$userid = Auth::user()->name;

		$userRole = Auth::user()->role;
		$description = Reimbursement_describe_item::where('reimburse_id','=', $reimbursement->id)->get();

		//$reportIds  = Customer_problem::select('id','customer_name','customer_last_name', DB::raw('CONCAT(customer_last_name,","," ",customer_name,"-",id) AS reportid'))->orderby('reportid')->pluck('reportid', 'id');


		$reportIds  = Customer_problem::leftJoin('locations', 'locations.id', '=', 'customer_problems.location')
            ->select('customer_problems.id','customer_problems.customer_name','customer_problems.customer_last_name','customer_problems.created_at','customer_problems.location','locations.location_code', DB::raw('CONCAT(if(customer_problems.customer_last_name!="",if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_last_name," , "),CONCAT(customer_problems.customer_last_name," - ")),""),if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_name," - "),""),CONCAT(DATE(customer_problems.created_at)),CONCAT("-PR-"),CONCAT(locations.location_code),CONCAT("-"),CONCAT(LPAD(customer_problems.id,4,"0"))) AS reportid'))->orderby('reportid')->pluck('reportid', 'customer_problems.id');

        $reportIds->prepend('- Select Report ID -', '');

        return view('reimbursements.edit', compact('reimbursement','description','reportIds','userRole','flag','userid') );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request )
    {
		$location_id = Session::get('location_id');
		$user_id = Auth::user()->id;

		$queryRole = DB::table('users')
							->select('role','claim')
                            ->where('id', '=',$request['userid'])
                            ->get();

		$originalClaimValue = ($queryRole[0]->claim)?($queryRole[0]->claim):0;

		/****condition start for status****/

		if(($originalClaimValue < $request['proof_purchase']) && $request['approved_amount'] == '' && ($queryRole[0]->role != 'Owner')) {
				$status = 'Approved Pending';
		} else if(($originalClaimValue <= $request['proof_purchase']) && isset($request['approved_amount']) && $request['approved_amount'] != '' && ($queryRole[0]->role != 'Owner')) {
				$status = 'Payment Pending';
				if($request['resolution'] == 'check' && ($request['credit_amount']) != '' && ($request['resolution_check']) != ''){
						$status = 'Paid';
				} elseif($request['resolution'] == 'storeCredit' && ($request['resolution_store_credit_added']) != '' && ($request['resolution_store_credit_check_date']) != ''){
						$status = 'Paid';
				} elseif($request['resolution'] == 'checkstorecredit' && ($request['resolution_check_store_credit_added']) != '' && ($request['resolution_check_store_credit_check']) != '' && $request['resolution_check_store_credit_check_date'] != '' && $request['resolution_check_store_credit_check_credit_number'] != '' ){
						$status = 'Paid';
				} elseif($request['resolution'] == 'cash' && $request['resolution_cash_paid'] != '' && $request['resolution_cash_paid_date'] != '') {
						$status = 'Paid';
				}
		} else if(($originalClaimValue > $request['proof_purchase']) || $queryRole[0]->role == 'Owner') {
			if($request['resolution'] == '') {
					$status = 'Pending';
			} elseif($request['resolution'] == 'check' && ($request['credit_amount']) != '' && ($request['resolution_check']) != ''){
					$status = 'Paid';
			} elseif($request['resolution'] == 'storeCredit' && ($request['resolution_store_credit_added']) != '' && ($request['resolution_store_credit_check_date']) != ''){
					$status = 'Paid';
			} elseif($request['resolution'] == 'checkstorecredit' && ($request['resolution_check_store_credit_added']) != '' && ($request['resolution_check_store_credit_check']) != '' && $request['resolution_check_store_credit_check_date'] != '' && $request['resolution_check_store_credit_check_credit_number'] != '' ){
					$status = 'Paid';
			} elseif($request['resolution'] == 'cash' && $request['resolution_cash_paid'] != '' && $request['resolution_cash_paid_date'] != '') {
					$status = 'Paid';
			} else {
					$status = 'Pending';
			}
		}
		/***end****/

       $updateArray = array(
			'zip_type' => $request['zip_type'],
			'report_id' => $request['report_id'],
        'today_date' => $request['today_date'],
			'location_id' => $location_id,
			'status'	 =>	$status,
			//'user_id' => $user_id,
            'claim_check' => $request['claim_check'],
			'claim_check_date' => $request['claim_check_date'],
			'special_claim_check' => $request['special_claim_check'],
			'first_name' => ucfirst($request['first_name']),
			'last_name' => ucfirst($request['last_name']),
			'address' => ucfirst($request['address']),
			'city' => ucfirst($request['city']),
			'state' => $request['state'],
			'zip_code' => $request['zip_code'],
			'original_claim_check' => $request['original_claim_check']!= '' ? $request['original_claim_check'] : '',
			'claim_detail' => $request['claim_detail']!= '' ? $request['claim_detail'] : '',
			'compensation' => $request['compensation']!= '' ?$request['compensation'] : '',
			'item' => $request['item']!= '' ? $request['item'] : '',
			'email_address' => $request['email_address'],
			'telephone_number' => $request['telephone_number'],
			'total_desc' => $request['total_desc'],
			'proof_purchase' => $request['proof_purchase'],
			'store' => $request['store'],
			'store_owner_manager' => ucfirst($request['store_owner_manager']),
			'receipts_attached' => $request['receipts_attached']!= '' ? $request['receipts_attached'] : '',
			'claim_type' => $request['claim_type']!= '' ? $request['claim_type'] : '',
			'damaged_desc' => ucfirst($request['damaged_desc']),
			'item_return' => $request['item_return']!= '' ? $request['item_return'] : '',
			'check_amount' => $request['check_amount'],
			'credit_amount' => $request['credit_amount']!= '' ? $request['credit_amount'] : '',
			'resolution' => $request['resolution'],
			'resolution_check_pickup_mail' => $request['resolution_check_pickup_mail']!= '' ? $request['resolution_check_pickup_mail'] : '',
			'resolution_check' => $request['resolution_check']!= '' ? $request['resolution_check'] : '',
			'resolution_store_credit_added' => $request['resolution_store_credit_added']!='' ? $request['resolution_store_credit_added'] : '',
			'resolution_store_credit_check_date' =>$request['resolution_store_credit_check_date']!='' ? $request['resolution_store_credit_check_date'] : '',
			'resolution_store_credit_amount' =>$request['resolution_store_credit_amount']!='' ? $request['resolution_store_credit_amount'] : '',
			'resolution_check_store_credit_pickup_mail' => $request['resolution_check_store_credit_pickup_mail']!= '' ? $request['resolution_check_store_credit_pickup_mail'] : '',
			'resolution_check_store_credit_added' => $request['resolution_check_store_credit_added']!='' ? $request['resolution_check_store_credit_added'] : '',
			'resolution_check_store_credit_added_amount' => $request['resolution_check_store_credit_added_amount']!='' ? $request['resolution_check_store_credit_added_amount'] : '',
			'resolution_check_store_credit_check' => $request['resolution_check_store_credit_check']!= '' ? $request['resolution_check_store_credit_check'] : '' ,
			'resolution_check_store_credit_check_date' => $request['resolution_check_store_credit_check_date']!= '' ? $request['resolution_check_store_credit_check_date'] : '' ,
			'resolution_check_store_credit_check_credit_number' => $request['resolution_check_store_credit_check_credit_number']!= '' ? $request['resolution_check_store_credit_check_credit_number'] : '' ,
			'resolution_check_store_credit_check_credit_amount' => $request['resolution_check_store_credit_check_credit_amount']!= '' ? $request['resolution_check_store_credit_check_credit_amount'] : '' ,
			'resolution_cash_paid' => $request['resolution_cash_paid']!= '' ? $request['resolution_cash_paid'] : '',
			'resolution_cash_paid_date' => $request['resolution_cash_paid_date']!='' ? $request['resolution_cash_paid_date'] : '',
		    'resolution_cash_paid_amount' => $request['resolution_cash_paid_amount']!='' ? $request['resolution_cash_paid_amount'] : ''
        );


		$reimbursementSave = Reimbursement::where('id', '=', $request['id'])->update($updateArray);

		 if(isset($request['gdid']))
        {
            $gdid = $request['gdid'];

            foreach ($gdid as $key) {

					if(($request['item_desc'.$key] != '') || ($request['material'.$key] != '') || ($request['color'.$key] != '') || ($request['date_purchase'.$key] != '') || ($request['amount_paid'.$key] != '') || ($request['purchase_location'.$key] != '') || ($request['total'.$key] != '')){
                    Reimbursement_describe_item::where('id', '=', $key)->update([
					'item_desc'  => ucfirst($request['item_desc'.$key]),
                    'material'   => ucfirst($request['material'.$key]),
                    'color' => ucfirst($request['color'.$key]),
					'date_purchase' => $request['date_purchase'.$key],
                    'amount_paid'   => $request['amount_paid'.$key],
					'purchase_location'   => ucfirst($request['purchase_location'.$key])

                    ]);
                }
            }
        }

      if($request->amount_paid){
        $addDescT = count($request['amount_paid']);
            for ($i=0; $i < $addDescT; $i++) {
                if(($request['item_desc'][$i] != '') || ($request['material'][$i] != '') || ($request['color'][$i] != '') || ($request['date_purchase'][$i] != '') || ($request['amount_paid'][$i] != '') || ($request['purchase_location'][$i] != '') ){
                   Reimbursement_describe_item::create([
              'reimburse_id'  => $request['id'],
                        'item_desc'  => ucfirst($request['item_desc'][$i]),
                        'material'   => ucfirst($request['material'][$i]),
                        'color' => ucfirst($request['color'][$i]),
              'date_purchase' => $request['date_purchase'][$i],
                        'amount_paid'   => $request['amount_paid'][$i],
              'purchase_location'   => ucfirst($request['purchase_location'][$i])

                    ]);
                }
            }
      }


		$claimValue = $request['proof_purchase'];
		$userRole = Auth::user()->role;
		$originalClaimValue = Auth::user()->claim;
		$formId = $request->id;
		$locationCode = DB::table('locations')->select('location_code','location')->where('id', '=', Session::get('location_id'))->get('location_code','location');

		if(($userRole == 'Employee') && ($claimValue > $originalClaimValue))
		{
					$usersMail = DB::table('user_locations','locations')
							->select('users.*','user_locations.*','locations.*')
                            ->leftjoin('users', 'users.id', '=', 'user_locations.user')
							->leftjoin('locations', 'locations.id', '=', 'user_locations.location')
                            ->where('users.role', '=','Supervisor')
							->where('users.status', '=','1')
							->where('user_locations.location', '=', $location_id)
                            ->get();
					foreach ($usersMail as $mail){
						$var = (array) $mail;
						$var['form_id'] = $formId;
						//$var['customer_name'] = Auth::user()->name;
						$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
 						Mail::send('emails.reimbursement', $var, function($message) use ($var) {
							$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
							$message->to($var['email']);
							$message->cc("bvago@kvmgmt.com");
							$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');

						});
					}

					$ownerMail = DB::table('users','user_email_locations')
								->select('users.*')
								->leftjoin('user_email_locations', 'user_email_locations.user', '=', 'users.id')
								->where('users.role', '=','Owner')
								->where('user_email_locations.location', '=', Session::get('location_id'))
								->where('users.status', '=','1')
								->get();

					/* $ownerMail = DB::table('users')
								->select('users.*')
								->where('users.role', '=','Owner')
								->where('users.status', '=','1')
								->get(); */

					if(count($ownerMail) > 0) {
							foreach ($ownerMail as $mail) {
							$var = (array) $mail;
							$var['form_id'] = $formId;
							$var['location_code'] = $locationCode[0]->location_code;
							$var['location'] =$locationCode[0]->location;
							//$var['customer_name'] = Auth::user()->name;
							$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
								Mail::send('emails.reimbursement', $var, function($message) use ($var) {
								$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
								$message->to($var['email']);
								$message->cc("bvago@kvmgmt.com");
								$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');

								});
							}
					}
		} else if(($userRole == 'Supervisor') && ($claimValue > $originalClaimValue))
		{
			$usersMail =  DB::table('users','user_email_locations')
								->select('users.*')
								->leftjoin('user_email_locations', 'user_email_locations.user', '=', 'users.id')
								->where('users.role', '=','Owner')
								->where('user_email_locations.location', '=', Session::get('location_id'))
								->where('users.status', '=','1')
								->get();

			/* $usersMail = DB::table('users')
							->select('users.*')
                            ->where('users.role', '=','Owner')
							->where('users.status', '=','1')
                            ->get();*/
					if(count($usersMail) > 0) {
							foreach ($usersMail as $mail) {
								$var = (array) $mail;
								$var['form_id'] = $formId;
								$var['location_code'] = $locationCode[0]->location_code;
								$var['location'] =$locationCode[0]->location;
								//$var['customer_name'] = Auth::user()->name;
								$var['customer_name'] = ucfirst($request['first_name'])." ".ucfirst($request['last_name']);
									Mail::send('emails.reimbursement', $var, function($message) use ($var) {
										$message->from(Auth::user()->email,'Claims - ZIPS Reimbursement');
										$message->to($var['email']);
										$message->cc("bvago@kvmgmt.com");
										$message->subject($var['location_code'].' '.$var['location'].' Claim Form '.'#'.$var['form_id']. ' Approval Pending');
									});
							}
					}
		} else {

			// echo "Hello"; exit();

		}

	    return redirect()->route('reimbursement.index')->with('message','Reimbursement report has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reimbursement $reimbursement)
    {



       $reimbursement->delete();
	   $reimburse = Reimbursement_describe_item::where('reimburse_id', '=', $reimbursement['id'])->delete();
       return redirect()->route('reimbursement.index')->with('message','Reimbursement report has been deleted successfully!');

    }


	public function removeSub(Request $request)
    {

        $id     = $request->get('id');

       if ($id > 0)
        {
            $deleterow = Reimbursement_describe_item::where('id', '=', $id)->delete();
                return "yes";
        }

        return "no";
    }

	public function reportData(Request $request)
    {
		$customerID = $request->cID;

		$row = \DB::table('customer_problems')
            ->select('customer_problems.*')
            ->where('customer_problems.id', '=', $customerID)
			->get();
		echo $data = $row[0]->customer_name."|".$row[0]->customer_last_name."|".$row[0]->address."|".$row[0]->city."|".$row[0]->state."|".$row[0]->zip."|".$row[0]->cell_phone;
    }
	public function rembursementPdf($id)
    {
        $reimbursements = \DB::table('reimbursements')->where('id','=', $id)->get();
	    $userRole = Auth::user()->role;
 		$description = Reimbursement_describe_item::where('reimburse_id','=', $id)->get();

		if($reimbursements[0]->zip_type == 1) {
			$reportIds  = Customer_problem::leftJoin('locations', 'locations.id', '=', 'customer_problems.location')
			->select('customer_problems.id','customer_problems.customer_name','customer_problems.customer_last_name','customer_problems.created_at','customer_problems.location','locations.location_code', DB::raw('CONCAT(if(customer_problems.customer_last_name!="",if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_last_name," , "),CONCAT(customer_problems.customer_last_name," - ")),""),if(customer_problems.customer_name!="",CONCAT(customer_problems.customer_name," - "),""),CONCAT(DATE(customer_problems.created_at)),CONCAT("-PR-"),CONCAT(locations.location_code),CONCAT("-"),CONCAT(LPAD(customer_problems.id,4,"0"))) AS reportid'))
			->where('customer_problems.id', '=', $reimbursements[0]->report_id)
			->orderby('reportid')->pluck('reportid');
		}

		$html = '<body style="font-family: calibri; font-size: 10px;">
		<table style="width: 100%;" border="0" cellpadding="10">
			<thead>
				<tr>
					<th colspan="6" align="right" style="padding:10px;font-size:20px;"><b>'.$reimbursements[0]->status.'</b></th>
				</tr>
			</thead>
		</table>
		<table style="width: 100%;" border="1" cellpadding="10">
			<thead>

				<tr>
					<th colspan="3" style="padding:10px;">'.(($reimbursements[0]->zip_type == 0)?"NEW CLAIM FORM":"USE EXISTING PROBLEM REPORT").'</th>
					<th colspan="3" style="padding:10px;">'.(($reimbursements[0]->zip_type == 1)?$reportIds[0]:'').'</th>
				</tr>
			</thead>
		</table>


		<table style="width: 100%; font-size:8px;" border="1" cellpadding="2">
			<tbody>
				<tr>
					<th>Todays Date</th>
					<td>'.$reimbursements[0]->today_date.'</td>
					<th>Claim Check #</th>
					<td>'.$reimbursements[0]->claim_check.'</td>
					<th>Do you have your original Claim check?</th>
					<td>'.(($reimbursements[0]->original_claim_check == 1)?"Yes":"No").' <br>'.(($reimbursements[0]->original_claim_check == 1) ? "check No. (".$reimbursements[0]->claim_detail.")":'').'</td>
				</tr>
				<tr>
					<th>Claim Check Date</th>
					<td>'.$reimbursements[0]->claim_check_date.'</td>
					<th>Special Claim Check # (if any)</th>
					<td>'.$reimbursements[0]->special_claim_check.'</td>
					<th>Compensation</th>';
				if($reimbursements[0]->compensation == 0)
				$html .= '<td>Check</td>';
				else if($reimbursements[0]->compensation == 1)
				$html .= '<td>Store Credit</td>';
				else if($reimbursements[0]->compensation == 2)
				$html .= '<td>Split</td>';
				$html .= '</tr>
				<tr>
					<td colspan="3">
						<h4 style="font-size: 12px;">'.$reimbursements[0]->first_name.' '.$reimbursements[0]->last_name.'</h4>
						<p>'.$reimbursements[0]->address.',<br>'.$reimbursements[0]->city.'<br>'.$reimbursements[0]->state.' - '.$reimbursements[0]->zip_code.'</p>
					</td>
					<td colspan="3">
						<p><b>Email Address:</b> '.$reimbursements[0]->email_address.' </p>
						<p><b>Phone No:</b> '.$reimbursements[0]->telephone_number.'</p>
					</td>
				</tr>
			</tbody>
		</table>
		<h4>Item Description</h4>
		<table style="width: 100%; font-size:8px;" border="1" cellpadding="2">
			<thead>
				<tr>
					<th>ITEM DESCRIPTION</th>
					<th>MATERIAL</th>
					<th>COLOR</th>
					<th>DATE PURCHASED</th>
					<th>PURCHASE LOCATION</th>
					<th>AMOUNT</th>
				</tr>
			</thead>
			<tbody>';
				foreach($description as $key => $value) {
				$html .= '	<tr>
						<td>'. $value->item_desc.'</td>
						<td>'. $value->material.'</td>
						<td>'. $value->color.'</td>
						<td>'. $value->date_purchase.'</td>
						<td>'. $value->purchase_location.'</td>
						<td>'. $value->amount_paid.'</td>
					</tr>';
					}
				$html .= '	<tr>
				<td colspan="4"></td>
					<th>Total</th>
					<th>$'.$reimbursements[0]->total_desc.'</th>
				</tr>
			</tbody>
		</table>
			<h4 style="margin: 10px 0; font-size: 13px;">PROOF OF PURCHASE (one of the following): </h4>
			<ol style="padding: 0; margin: 0;">
				<li style="margin:0; padding:0">Original or duplicate receipt from store,</li>
				<li style="margin:0; padding:0">Itemized credit card receipt</li>
				<li style="margin:0; padding:0">Monthly statement from store where purchased</li>
			</ol>
			<p style="padding:0; margin:0;">After allowing for depreciation from age and wear, I would be willing to accept <b>$'.$reimbursements[0]->proof_purchase.'</b>
		as full settlement of this claim. I acknowledge that this listed amount is an offer made by me to be considered by ZIPS. Please note that once the claim is resolved, the item(s), for which you received compensation will remain in our possession.

		Customer is responsible to ensure this form is filled out completely and legibly. Store Credit will be added to Customer account or check is mailed to the home address listed above within ten (10) business days of final resolution. Copies of this document are not provided.</p>
		<table style="width: 100%; font-size:8px;" border="1" cellpadding="2">
		<thead>
			<tr>
				<th colspan="6">
					<h4 style="margin: 10px 0; font-size: 12px;">FOR OFFICE USE ONLY:</h4>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Store #</th>
				<td>'.$reimbursements[0]->store.'</td>
				<th>Store Owner/Manager:</th>
				<td>'.$reimbursements[0]->store_owner_manager.'</td>
				<th>Receipts Attached:</th>
				<td>'.(($reimbursements[0]->receipts_attached == 1)?"Yes":"No").'</td>
			</tr>
			<tr>
				<th>Claim Type:</th>';
				if($reimbursements[0]->claim_type == 1)
				$html .= '<td>Missing Item</td>';
				else if($reimbursements[0]->claim_type == 2)
				$html .= '<td>Missing Order</td>';
				else if($reimbursements[0]->claim_type == 3)
				$html .= '<td>Damaged Order</td>';

				if($reimbursements[0]->claim_type == 3)
				{
					$html .= '<th>Comments/Damage Description</th>
						<td>'.$reimbursements[0]->damaged_desc.'</td>
						<th>Item Returned to Customer</th>
						<td>'.(($reimbursements[0]->item_return == 1)?"Yes":"No").'</td>';
				} else {
					$html .= '<th colspan="2">Item Returned to Customer</th>
						<td colspan="2">'.(($reimbursements[0]->item_return == 1)?"Yes":"No").'</td>';
				}

				$html .= '
					</tr>
			<tr>
				<th>Resolution</th>';

				if($reimbursements[0]->resolution == "check")
				$html .= '<td>Check</td>';
				else if($reimbursements[0]->resolution == "storeCredit")
				$html .= '<td>Store Credit</td>';
				else if($reimbursements[0]->resolution == "checkstorecredit")
				$html .= '<td>Check and Store Credit</td>';
				else if($reimbursements[0]->resolution == "cash")
				$html .= '<td>Cash</td>';
				else
				$html .= '<td>Select</td>';

				if($reimbursements[0]->resolution == "cash" || $reimbursements[0]->resolution == "checkstorecredit") {
				$html .= '
					<th colspan="4">'.(($reimbursements[0]->resolution_check_pickup_mail == 1)?"Pick Up":"Mail").'</th>
					<td colspan="4"></td>';
				} else {
					$html .= '
					<th colspan="4"></th>
					<td colspan="4"></td>';
				}
				$html .= '</tr>';

				if($reimbursements[0]->resolution == "check") {
					$html .= '<tr>
						<th>Check Amount:</th>
						<td>'.$reimbursements[0]->check_amount.'</td>
						<th>Check Date:</th>
						<td>'.$reimbursements[0]->credit_amount.'</td>
						<th>Check#:</th>
						<td>'.$reimbursements[0]->resolution_check.'</td>
					</tr>';
				} else if($reimbursements[0]->resolution == "storeCredit") {
				$html .= '<tr>
							<th>Credit Amount:</th>
							<td>'.$reimbursements[0]->resolution_store_credit_amount.'</td>
							<th>Store Credit Added Date:</th>
							<td>'.$reimbursements[0]->resolution_store_credit_added.'</td>
							<th>Store Credit Number:</th>
							<td>'.$reimbursements[0]->resolution_store_credit_check_date.'</td>
						</tr>';
				} else if($reimbursements[0]->resolution == "checkstorecredit") {
				$html .= '<tr>
						<th>Check Amount:</th>
						<td>'.$reimbursements[0]->resolution_check_store_credit_added_amount.'</td>
						<th>Check Date:</th>
						<td>'.$reimbursements[0]->resolution_check_store_credit_added.'</td>
						<th>Check#:</th>
						<td>'.$reimbursements[0]->resolution_check_store_credit_check.'</td>
					</tr>
						<tr>
							<th>Credit Amount:</th>
							<td>'.$reimbursements[0]->resolution_check_store_credit_check_credit_amount.'</td>
							<th>Store Credit Added Date:</th>
							<td>'.$reimbursements[0]->resolution_check_store_credit_check_date.'</td>
							<th>Store Credit Number:</th>
							<td>'.$reimbursements[0]->resolution_check_store_credit_check_credit_number.'</td>
						</tr>
						';
				}
				else if($reimbursements[0]->resolution == "cash") {
				$html .= '<tr>
						<th>Check Amount:</th>
						<td>'.$reimbursements[0]->resolution_cash_paid_amount.'</td>
						<th>Check Date:</th>
						<td>'.$reimbursements[0]->resolution_cash_paid.'</td>
						<th>Check#:</th>
						<td>'.$reimbursements[0]->resolution_cash_paid_date.'</td>
					</tr>';
				}
			$html .= '</tbody>
		</table></body>';

		PDF::SetTitle('Reimbursement');

        PDF::AddPage();

		PDF::SetFont('Helvetica', 'sans serif', '10', '', 'false');
        PDF::writeHTML($html, true, false, true, false, '');
        PDF::Output('reimbursement'.$id.'.pdf', 'I');
    }

	public function approved_amount(Request $request)
    {
		$approved_amount = $request->approved_amount;
		$form_id = $request->form_id;
		$userid = $request->userid;
		$cdate = $request->cdate;
		$ctime = $request->ctime;

		$vendor = \DB::table('reimbursements')
            ->where('id', '=', $form_id)
			->update(['approved_amount' => $approved_amount,'approved_amount_user_name'=>$userid,'approved_amount_date'=>$cdate,'approved_amount_time'=>$ctime]);

		echo "1";
		exit;
    }


}
