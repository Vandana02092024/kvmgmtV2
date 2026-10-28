<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use App\Http\Requests\SalesectionRequest;
use App\User;
use App\User_location;
use App\Location;
use App\Salesection;
use App\Sale;
use App\Discount;
use App\Adjustment;
use App\Receipt;
use App\Sale_location;
use App\Discount_location;
use App\Adjustment_location;
use App\Receipt_location;
use Mail;
use Session;
use Validator;
use PDF;
use Storage;

class ReportingsectionController extends Controller
{
        /**
     * Display a listing of the resource.
     *discounts
     * @return \Illuminate\Http\Response


     */

public function index()
    {
		$locationId = Session::get('location_id');
        $sales = Sale::where('status', '=', 1)->pluck('name','id');
        $discount = Discount::where('status', '=', 1)->pluck('name','id');
        $adjustment = Adjustment::where('status', '=', 1)->pluck('name','id');
        $receipt = Receipt::where('status', '=', 1)->pluck('name','id');
        $location = Location::where('status', '=', 1)->pluck('location','id');
		$sorting = array('0'=>'Day','1'=>'Week','2'=>'Month','3'=>'Year');
		$sorting1 = array('0'=>'Amount & Unit','1'=>'Amount','2'=>'Unit');
        // $result = "SELECT amount,nameDep, dataDate FROM `salesections` WHERE dataDate BETWEEN 'strdate' AND location_id = '4' GROUP BY nameDep";

        $curr_date = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where dataDate = CURRENT_DATE() and location_id IN (".$locationId.")) as total";


        $currentData = DB::SELECT($curr_date);

        $curr_month = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where month(dataDate) = MONTH(CURRENT_DATE()) and location_id IN (".$locationId.")) as total";

        $currentMonth = DB::SELECT($curr_month);


        $curr_year = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where year(dataDate) =
        YEAR(CURRENT_DATE()) and location_id IN (".$locationId.")) as total";

        $currentYear = DB::SELECT($curr_year);

         $pre_week = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where week(dataDate) = WEEK(CURRENT_DATE - INTERVAL 1 WEEK) and location_id IN (".$locationId.")) as total";

        $preWeek = DB::SELECT($pre_week);

        $pre_month = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where month(dataDate) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) and location_id IN (".$locationId.")) as total";
        $preMonth = DB::SELECT($pre_month);
        $pre_year = "SELECT FORMAT(IFNULL(dis_sales,0),2) as dis_sales, FORMAT(IFNULL(sale_sales,0),2) as sale_sales, FORMAT(IFNULL(adj_sales,0),2) as adj_sales, FORMAT(IFNULL(rec_sales,0),2) as rec_sales from (SELECT sum(case when(nameDep = 'dis') then amount end) as dis_sales, sum(case when(nameDep = 'sale') then amount end) as sale_sales, sum(case when(nameDep = 'adj') then amount end) as adj_sales, sum(case when(nameDep = 'rec') then amount end ) as rec_sales FROM `salesections` where YEAR(dataDate) = YEAR(CURRENT_DATE - INTERVAL 1 YEAR) and location_id IN (".$locationId.")) as total";
        $preYear = DB::SELECT($pre_year);
		// $selectedLocation = array(3,4,5);
		return view('reportingsections.index', compact('sales', 'discount', 'adjustment', 'receipt','location','locationId','sorting','sorting1','currentData','currentMonth', 'currentYear', 'preWeek', 'preMonth','preYear'));
	}

    public function create()
    {

		return view('reportingsections.create');
	}
    public function store(SalesectionRequest $request)
    {
	return redirect()->route('reportingsection.create');

    }
    public function show($id)
    {
    }
    public function edit($id)
    {
		return view('reportingsections.create');
    }
    public function update(Request $request )
    {

    }
    public function destroy(Salesection $salesection)
    {
      return redirect()->route('reportingsection.index');
    }

	public function chartData(Request $request){
    $abc=[];
	 $startDate = $endDate = $sortBy = $sortBy1 = $location = $saleId = $disId = $adjId = $recId = $saleCon = $disCon = $adjCon = $recCon = '';
		$x = explode("-",$request['strDate']);
		$startDate = $x[2]."-".$x[0]."-".$x[1];
		$y = explode("-",$request['endDate']);
		$endDate = $y[2]."-".$y[0]."-".$y[1];
		$sortBy = $request['sortBy'];
		$sortBy1 = $request['sortBy1'];
		$saleId = $request['sale'];
		$disId = $request['dis'];
		$adjId = $request['adj'];
		$recId = $request['rec'];
		$location = $request['location'];
		$where = '';
		$groupBy = '';
		$orderBY = '';
		if($sortBy != '')
		{
			if($sortBy == 0) {
			$groupBy = "dt";
			}
			if($sortBy == 1) {
			$groupBy = "wk";
			}

			if($sortBy == 2) {
			$groupBy = "month_no, yr";
			}

			if($sortBy == 3) {
			$groupBy = "yr";
			}
		}
		$groupBy=   "location_name";
        	if($startDate != '' && $endDate != '')
        		{
					$where .= " and dataDate BETWEEN '".$startDate."' AND '".$endDate."'";
				}
				$where .= " and location_id IN (".$location.")";
				if($saleId != '') {
					$saleCon =  $saleId;
				}

				if($disId != '') {
					$disCon = $disId;
				}

				if($adjId != '') {
					$adjCon = $adjId;
				}

				if($recId != '') {
					$recCon = $recId;
				}
				if($sortBy1 == 1){
					// echo "amount";exit;
					$saleQry = "SELECT round(sum(case when(nameDep = 'dis' and nameId IN (".$disCon.")) then amount end),2) as dis_sales, round(sum(case when(nameDep = 'sale' and nameId IN (".$saleCon.")) then amount end),2) as sale_sales,  round(sum(case when(nameDep = 'adj' and nameId IN (".$adjCon.")) then amount end),2) as adj_sales,  round(sum(case when(nameDep = 'rec' and nameId IN (".$recCon.")) then amount end ),2) as rec_sales, year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by ".$groupBy."";
						if($saleId == '' && $disId == '' && $adjId == '' && $recId == '') {
						$saleQry = "SELECT  round(sum(case when(nameDep = 'sale') then amount end),2) as sale_sales,round(sum(case when(nameDep = 'dis') then amount end),2) as dis_sales,  round(sum(case when(nameDep = 'adj') then amount end),2) as adj_sales,  round(sum(case when(nameDep = 'rec') then amount end ),2) as rec_sales, year(dataDate) as yr, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by  ".$groupBy."";
						}

					}elseif($sortBy1 == 0){
						// echo "both";exit;
							$saleQry = "SELECT round(sum(case when(nameDep = 'dis' and nameId IN (".$disCon.")) then amount end),2) as dis_sales, round(sum(case when(nameDep = 'sale' and nameId IN (".$saleCon.")) then amount end),2) as sale_sales, round(sum(case when(nameDep = 'sale' and nameId IN (".$saleCon.")) then unit end),2) as sale_sales_unit, sum(case when(nameDep = 'sale' and nameId IN (".$saleCon.")) then unit end) as unit_sales,  round(sum(case when(nameDep = 'adj' and nameId IN (".$adjCon.")) then amount end),2) as adj_sales,  round(sum(case when(nameDep = 'rec' and nameId IN (".$recCon.")) then amount end ),2) as rec_sales, year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by ".$groupBy."";
							if($saleId == '' && $disId == '' && $adjId == '' && $recId == '') {
							$saleQry = "SELECT  round(sum(case when(nameDep = 'sale') then amount end),2) as sale_sales,round(sum(case when(nameDep = 'dis') then amount end),2) as dis_sales,  round(sum(case when(nameDep = 'adj') then amount end),2) as adj_sales,  round(sum(case when(nameDep = 'rec') then amount end ),2) as rec_sales, year(dataDate) as yr, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by  ".$groupBy."";

							}

					}elseif($sortBy1 == 2){
						// echo "unit";exit;
							$saleQry = "SELECT round(sum(case when(nameDep = 'dis' and nameId IN (".$disCon.")) then unit end),2) as dis_sales, round(sum(case when(nameDep = 'sale' and nameId IN (".$saleCon.")) then unit end),2) as sale_sales,  round(sum(case when(nameDep = 'adj' and nameId IN (".$adjCon.")) then unit end),2) as adj_sales,  round(sum(case when(nameDep = 'rec' and nameId IN (".$recCon.")) then unit end ),2) as rec_sales, year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by ".$groupBy."";
							if($saleId == '' && $disId == '' && $adjId == '' && $recId == '') {
							$saleQry = "SELECT  round(sum(case when(nameDep = 'sale') then unit end),2) as sale_sales,round(sum(case when(nameDep = 'dis') then unit end),2) as dis_sales,  round(sum(case when(nameDep = 'adj') then unit end),2) as adj_sales,  round(sum(case when(nameDep = 'rec') then unit end ),2) as rec_sales, year(dataDate) as yr, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep, location_name FROM `salesections` s inner join (select id, location as location_name from locations) l on l.id = s.location_id where 1=1 ".$where." GROUP BY  ".$groupBy." order by  ".$groupBy."";
							}
					}
					if($groupBy == '')
			          $groupBy = "year(dataDate)";
				    $query = DB::SELECT($saleQry);
				    $allDepart = array('Sales','Discount','Adjustments','Receipt');
				    $getGraphData = array();
				    foreach($query  as $k=> $v){
				    	// echo "<pre>";print_r($v);
				    	if($sortBy1 == 0){
							$getGraphData[$v->location_name]['Sales'] = $v->sale_sales.','.$v->sale_sales_unit;
				    	}
				    	else{
							$getGraphData[$v->location_name]['Sales'] = $v->sale_sales;
				    	}

						//$getGraphData[$v->location_name]['SalesUnit'] = $v->sale_sales_unit;
				    	$getGraphData[$v->location_name]['Discount'] = $v->dis_sales;
				    	$getGraphData[$v->location_name]['Adjustments'] =$v->adj_sales;
				    	$getGraphData[$v->location_name]['Receipt'] = $v->rec_sales;

					 }
					 $i= 0;
					 foreach($allDepart as $dval) {
							$graphData[$i]['department'] = $dval;
							foreach($getGraphData as $dk=>$dv) {
								$value = $dv[$dval];
                $abc=array();
								if($dval=="Sales"){
									$abc = explode(',',$dv[$dval]);
                  if(!empty($abc)&& $abc!=null){
                   $value = $abc[0];
                  }

								}
								$graphData[$i][$dk] = $value;
							}
							if($dval=="Sales" && $sortBy1 == 0){
                  if(!empty($abc) && $abc!=null){
                    	$graphData[$i]['unitno'] = $abc[1];
                  }

							}
							$i++;
					 }
					     	$grCnt = 0;
					     	$graphs = array();
							foreach($getGraphData as $dk=>$dv) {
								if($sortBy1 == 0){
									$graphs1[$grCnt]['balloonText'] = $dk.":$[[value]]";
								}elseif ($sortBy1 == 1) {
									$graphs1[$grCnt]['balloonText'] = $dk.":$[[value]]";
								}elseif ($sortBy1 == 2) {
									$graphs1[$grCnt]['balloonText'] = $dk.":[[value]]";
								}

								$graphs1[$grCnt]['fillAlphas'] = 0.8;
								// $graphs1[$grCnt]['id'] = "AmGraph-".$grCnt;
								$graphs1[$grCnt]['lineAlpha'] = 0.2;
								if($sortBy1 == 2){
								$graphs1[$grCnt]['title'] = $dk.' - Unit';
								}else{
								$graphs1[$grCnt]['title'] = $dk.' - Amount';
								}
								$graphs1[$grCnt]['type'] =  "column";
								$graphs1[$grCnt]['valueField'] = $dk;


								$graphs2[$grCnt]['balloonText'] = $dk.' unit'.":[[value]]";
								$graphs2[$grCnt]['fillAlphas'] = 0.8;
								// $graphs2[$grCnt]['id'] = "AmGraph-".$grCnt;
								$graphs2[$grCnt]['lineAlpha'] = 0.2;
								$graphs2[$grCnt]['title'] = $dk.' - Unit';
								// $graphs2[$grCnt]['clustered'] = false;
								$graphs2[$grCnt]['type'] =  "column";
								$graphs2[$grCnt]['valueField'] = 'unitno';

								if($sortBy1 == 0){
								$graphs = array_merge($graphs1, $graphs2);
								}
								else{
									$graphs = $graphs1;
								}


								$grCnt++;
							}
							// echo "<pre>"; print_r($getGraphData);exit;
							// echo "<pre>"; print_r($graphData);exit;
							// echo "<pre>"; print_r($graphs);exit;
						 $result['graph_data'] = $graphData;
						 $result['graphs'] = $graphs;
						 // echo json_encode($result);exit;
						 return json_encode($result);
	}

	public function viewReportingData(Request $request)
	{ $id='';
	 $locationId='';
    $sales=[];
    $Sales=[];
    $discount=[];
    $Discounts=[];
    $adjustment=[];
    $Adjustments=[];
    $receipt=[];
    $Receipts=[];
    $sorting=[];
    $Sortings=[];


		$result = array();
		$x = explode("-",$request['strDate']);
		$startDate = $x[2]."-".$x[0]."-".$x[1];
		$y = explode("-",$request['endDate']);
		$endDate = $y[2]."-".$y[0]."-".$y[1];
		$sortBy = $request['sortBy'];
		$sortBy1 = $request['sortBy1'];
		$saleId = $request['sale'];
		$disId = $request['dis'];
		$adjId = $request['adj'];
		$recId = $request['rec'];
		$location = $request['location'];
		$where 		= '';
		$groupBy 	= '';
		$orderBY 	= '';
		if($sortBy != '') {
		if($sortBy == 0) {
		$groupBy = "dt";
		$orderBy = "dt DESC";
		}
		if($sortBy == 1) {
		$groupBy = "wk";
		$orderBy = "wk DESC";
		}
		if($sortBy == 2) {
		$groupBy = "month_no, yr";
		$orderBy = "month_no DESC, yr DESC";
		}
		if($sortBy == 3) {
		$groupBy = "yr";
		$orderBy = "yr DESC";
		}
		}
        $groupBy =  "location_id, ". $groupBy;
        if($startDate != '' && $endDate != ''){
			$where .= " dataDate BETWEEN '".$startDate."' AND '".$endDate."'";
		}
		$where .= " and location_id IN (".$location.")";
	 		$allSalesQry = "SELECT * FROM `sales` where id IN(".$saleId.")";
			$allSalesDep = DB::SELECT($allSalesQry);
			$allDisDep = "";
			if(!empty($disId)){
				$allDisQry = "SELECT * FROM `discounts` where id IN(".$disId.")";
				$allDisDep = DB::SELECT($allDisQry);
			}
			$allAdjQry = "SELECT * FROM `adjustments` where id IN(".$adjId.")";
			$allAdjDep = DB::SELECT($allAdjQry);
			$allRecQry = "SELECT * FROM `receipts` where id IN(".$recId.")";
			$allRecDep = DB::SELECT($allRecQry);
			$locationQry = "SELECT * from locations where id IN(".$location.")";
	 		$allLocations = DB::SELECT($locationQry);
			$con = '';
			$locid = '';
			foreach($allLocations as $key=>$val)
	        {
	        	$locid = $val->id;
	        	$con .= " FORMAT(IFNULL(sum(case when location_id = ".$locid." then amount end),0),2) as location_".$locid.",";
	        		// $locid = $val->id;
	        		// if($sortBy1 == 1){
	        		// 	$con .= " FORMAT(IFNULL(sum(case when location_id = ".$locid." then amount end),0),2) as location_".$locid.",";

	        		// }elseif ($sortBy1 == 2) {
	        		// 	$con .= " FORMAT(IFNULL(sum(case when location_id = ".$locid." then unit end),0),2) as location_".$locid.",";
	        		// }elseif ($sortBy1 == 0) {
	        		// 	$con .= " FORMAT(IFNULL(sum(case when location_id = ".$locid." then amount end),0),2) as location_".$locid.",";
	        		// }
			}
    		$saleQry = "SELECT  ".$con."  year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep ,locations.location FROM `salesections` join locations on salesections.location_id = locations.id where ".$where." GROUP BY ".$groupBy." order by ".$orderBy." ";

      	$filterdata = DB::SELECT($saleQry);
        		$i = 0;

			foreach($filterdata as $k =>$v)
			{	$result['data'][$i]['locID'] =  "$location";
				$result['data'][$i]['dt'] =  $v->dt;

				foreach($allLocations as $key=>$val)
					{
						$loc = 'location_'.$val->id;
						$result['data'][$i]['location_'.$val->id] = "$".$v->$loc;
						if ($sortBy==0)
						{
							$chWhere  = " and date(dataDate) = '".$filterdata[$i]->dt."'";
							$key = $filterdata[$i]->dt;
							$result['data'][$i]['sortBy'] = $filterdata[$i]->dt;
						} else if ($sortBy==1)
						{
							$chWhere  = " and week(dataDate) =".$filterdata[$i]->wk." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key= $this->getWeekDates($filterdata[$i]->yr,$filterdata[$i]->wk,true);
							$result['data'][$i]['sortBy'] = $key;
						} else if($sortBy==2)
						{
							$chWhere  = " and month(dataDate) = ".$filterdata[$i]->month_no." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key = date("M, Y", strtotime($filterdata[$i]->dt));
							$result['data'][$i]['sortBy'] = $key;
						} else if ($sortBy==3)
						{
							$key = $filterdata[$i]->yr;
							$result['data'][$i]['sortBy'] = $key;
							$chWhere  = " and year(dataDate)  = ". $filterdata[$i]->yr."";
						}


						$deptQry = DB::SELECT("select name, 'Sales' as parent,sales.id,FORMAT((case when(nameDep = 'sale') then amount else 0 end),2) as amount, FORMAT((case when(nameDep = 'sale') then unit else 0 end),2) as unit, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from sales left join salesections on sales.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$saleId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
									    if($sortBy1 == 0){
									    	// echo "both";exit;
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] = 'Amt- $'.$dp->amount." , ". 'Unit- '.$dp->unit;
									    }elseif ($sortBy1 == 1) {
									    	// echo "amount";exit;
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] = $dp->amount;
									    }elseif ($sortBy1 == 2) {
									    	// echo "unit";exit;
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] = $dp->unit;
									    }
										$Sales[$ky] = $dp->name;
								}
						} else {
								foreach($allSalesDep as $dp) {
										$ky = str_replace(" ", "_", $dp->name);
										if($sortBy1 == 0){
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] =0.00;
									    }elseif ($sortBy1 == 1) {
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] =0.00;
									    }elseif ($sortBy1 == 2) {
									    	$result['data'][$i]['location_'.$val->id.'_Sales'][$ky] =0.00;
									    }
							    }
						}
						$deptQry = DB::SELECT("select name, 'Discounts' as parent,discounts.id,FORMAT((case when(nameDep = 'dis') then amount else 0 end),2) as amount, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from discounts left join salesections on discounts.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$disId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
										$result['data'][$i]['location_'.$val->id.'_Discounts'][$ky] = $dp->amount;
										$Discounts[$ky] = $dp->name;
								}
						} else {
							if(!empty($allDisDep)){
								foreach($allDisDep as $dp) {
										$ky = str_replace(" ", "_", $dp->name);
										$result['data'][$i]['location_'.$val->id.'_Discounts'][$ky] =0.00;
							    }
							}

						}
						$deptQry = DB::SELECT("select name, 'Adjustments' as parent,adjustments.id,FORMAT((case when(nameDep = 'adj') then amount else 0 end),2) as amount, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from adjustments left join salesections on adjustments.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$adjId.") AND".$where." ".$chWhere."  GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
										$result['data'][$i]['location_'.$val->id.'_Adjustments'][$ky] = $dp->amount;
										$Adjustments[$ky] = $dp->name;
								}
						} else {
								foreach($allAdjDep as $dp) {
										$ky = str_replace(" ", "_", $dp->name);
										$result['data'][$i]['location_'.$val->id.'_Adjustments'][$ky] =0.00;
							    }
						}
						$deptQry = DB::SELECT("select name, 'Receipts' as parent,receipts.id,FORMAT((case when(nameDep = 'rec') then amount else 0 end),2) as amount, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from receipts left join salesections on receipts.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$recId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
									foreach($deptQry as $dp) {
										    $ky = str_replace(" ", "_", $dp->name);
											$result['data'][$i]['location_'.$val->id.'_Receipts'][$ky] = $dp->amount;
											$Receipts[$ky] = $dp->name;
									}
						} else {
								foreach($allRecDep as $dp) {
										$ky = str_replace(" ", "_", $dp->name);
										$result['data'][$i]['location_'.$val->id.'_Receipts'][$ky] =0.00;
							    }
						}
					}
				$i++;
			}


			$result = json_encode($result);
			$filename = "/var/www/html/kvmgmt_new/reportSectionData.txt";
			file_put_contents($filename, '');
			file_put_contents($filename, $result);
			$departmentArr = array("Sales","Discounts","Adjustments","Receipts");
			 return view('reportingsections.table', compact('id', 'sales', 'discount', 'adjustment', 'receipt','location','locationId','sorting','allLocations','departmentArr','Sales','Discounts','Adjustments','Receipts'));
    }
	public function linechartData(Request $request){
			$result = array();
			$graphline = array();
			$x = explode("-",$request['strDate']);
			$startDate = $x[2]."-".$x[0]."-".$x[1];
			$y = explode("-",$request['endDate']);
			$endDate = $y[2]."-".$y[0]."-".$y[1];
			$sortBy = $request['sortBy'];
			$saleId = $request['sale'];
			$disId = $request['dis'];
			$adjId = $request['adj'];
			$recId = $request['rec'];
			$location = $request['location'];
			$where 		= '';
			$groupBy 	= '';
			$orderBY 	= '';
			if($sortBy != '') {
				if($sortBy == 0) {
					$groupBy = "dt";
					$orderBy = "dt DESC";
				}
				if($sortBy == 1) {
					$groupBy = "wk";
					$orderBy = "wk DESC";
				}
				if($sortBy == 2) {
					$groupBy = "month_no, yr";
					$orderBy = "month_no DESC, yr DESC";
				}
				if($sortBy == 3) {
					$groupBy = "yr";
					$orderBy = "yr DESC";
				}
			}
        	$groupBy =  "location_id, ". $groupBy;
	        if($startDate != '' && $endDate != ''){
					$where .= " dataDate BETWEEN '".$startDate."' AND '".$endDate."'";
			}
			 $where .= " and location_id IN (".$location.")";
		 		$allSalesQry = "SELECT * FROM `sales` where id IN(".$saleId.")";
				$allSalesDep = DB::SELECT($allSalesQry);
				$allDisDep = "";
				if(!empty($disId)){
					$allDisQry = "SELECT * FROM `discounts` where id IN(".$disId.")";
					$allDisDep = DB::SELECT($allDisQry);
				}
				$allAdjQry = "SELECT * FROM `adjustments` where id IN(".$adjId.")";
				$allAdjDep = DB::SELECT($allAdjQry);
				$allRecQry = "SELECT * FROM `receipts` where id IN(".$recId.")";
				$allRecDep = DB::SELECT($allRecQry);
				$locationQry = "SELECT * from locations where id IN(".$location.")";
		 		$allLocations = DB::SELECT($locationQry);
				 $con = '';
				 $locid = '';

				foreach($allLocations as $key=>$val)
		        {
		        		$locid = $val->id;
						$con .= "IFNULL(sum(case when location_id = ".$locid." then amount end),0) as location_".$locid.",";

				}
        		$saleQry = "SELECT  ".$con."  year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep ,locations.location FROM `salesections` join locations on salesections.location_id = locations.id where ".$where." GROUP BY ".$groupBy." order by ".$orderBy." limit 7";
        		$filterdata = DB::SELECT($saleQry);
        		$i = 0;

				foreach($filterdata as $k =>$v)
				{	$result2[$i]['locID'] =  "$location";
					$result2[$i]['dt'] =  $v->dt;

					foreach($allLocations as $key=>$val)
					{
						$loc = 'location_'.$val->id;
						$result[$i]['location_'.$val->id] = round($v->$loc,2);
						if ($sortBy==0)
						{
							$chWhere  = " and date(dataDate) = '".$filterdata[$i]->dt."'";
							$key = $filterdata[$i]->dt;
							$result[$i]['sortBy'] = $filterdata[$i]->dt;
						} else if ($sortBy==1)
						{
							$chWhere  = " and week(dataDate) =".$filterdata[$i]->wk." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key= $this->getWeekDates($filterdata[$i]->yr,$filterdata[$i]->wk,true);
							$result[$i]['sortBy'] = $key;
						} else if($sortBy==2)
						{
							$chWhere  = " and month(dataDate) = ".$filterdata[$i]->month_no." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key = date("M, Y", strtotime($filterdata[$i]->dt));
							$result[$i]['sortBy'] = $key;
						} else if ($sortBy==3)
						{
							$key = $filterdata[$i]->yr;
							$result[$i]['sortBy'] = $key;
							$chWhere  = " and year(dataDate)  = ". $filterdata[$i]->yr."";
						}


					}
				$i++;
				$grCnt = 0;


							foreach($allLocations as $key=>$val) {
								$graphline[$grCnt]['id'] = "A".$key;
								$graphline[$grCnt]['bullet'] = "round";
								$graphline[$grCnt]['bulletBorderAlpha'] = 1;
								$graphline[$grCnt]['bulletColor'] = "#FFFFFF";
								$graphline[$grCnt]['bulletSize'] = 5;
								$graphline[$grCnt]['hideBulletsCount'] = 50;
								$graphline[$grCnt]['lineThickness'] = 2;
								$graphline[$grCnt]['title'] = "red line";
								$graphline[$grCnt]['useLineColorForBulletBorder'] = true;
								$graphline[$grCnt]['valueField'] = "location_".$val->id;
								$grCnt++;
							}
				}

				// $result = json_encode($result);
				 $result1['result'] = $result;
				 $result1['graphline'] = $graphline; //$finalStr; //
				 return json_encode($result1);
				// $filename = "/home/kvmgmt/public_html/chartSectionData.txt";
				// file_put_contents($filename, '');
				// file_put_contents($filename, $result);
				// $departmentArr = array("Sales","Discounts","Adjustments","Receipts");
				//  return view('reportingsections.index', compact('id', 'sales', 'discount', 'adjustment', 'receipt','location','locationId','sorting','allLocations','Sales','Discounts','Adjustments','Receipts','result'));
         }

	public function linechartUnit(Request $request){
    $graphUnit=[];
			$result = array();
			$x = explode("-",$request['strDate']);
			$startDate = $x[2]."-".$x[0]."-".$x[1];
			$y = explode("-",$request['endDate']);
			$endDate = $y[2]."-".$y[0]."-".$y[1];
			$sortBy = $request['sortBy'];
			$saleId = $request['sale'];
			// $disId = $request['dis'];
			// $adjId = $request['adj'];
			// $recId = $request['rec'];
			$location = $request['location'];

			$where 		= '';
			$groupBy 	= '';
			$orderBY 	= '';
			if($sortBy != '') {
				if($sortBy == 0) {
					$groupBy = "dt";
					$orderBy = "dt DESC";
				}
				if($sortBy == 1) {
					$groupBy = "wk";
					$orderBy = "wk DESC";
				}
				if($sortBy == 2) {
					$groupBy = "month_no, yr";
					$orderBy = "month_no DESC, yr DESC";
				}
				if($sortBy == 3) {
					$groupBy = "yr";
					$orderBy = "yr DESC";
				}
			}
        	$groupBy =  "location_id, ". $groupBy;
	        if($startDate != '' && $endDate != ''){
					$where .= " dataDate BETWEEN '".$startDate."' AND '".$endDate."'";
			}
			 $where .= " and location_id IN (".$location.")";

		 	// 	$allSalesQry = "SELECT * FROM `sales` where id IN(".$saleId.")";
				// $allSalesDep = DB::SELECT($allSalesQry);
				// $allDisDep = "";
				// if(!empty($disId)){
				// 	$allDisQry = "SELECT * FROM `discounts` where id IN(".$disId.")";
				// 	$allDisDep = DB::SELECT($allDisQry);
				// }
				// $allAdjQry = "SELECT * FROM `adjustments` where id IN(".$adjId.")";
				// $allAdjDep = DB::SELECT($allAdjQry);
				// $allRecQry = "SELECT * FROM `receipts` where id IN(".$recId.")";
				// $allRecDep = DB::SELECT($allRecQry);
				$locationQry = "SELECT * from locations where id IN(".$location.")";
		 		$allLocations = DB::SELECT($locationQry);

				 $con = '';
				 $locid = '';
				foreach($allLocations as $key=>$val)
		        {
		        		$locid = $val->id;
						$con .= "IFNULL(sum(case when location_id = ".$locid." then amount end),0) as location_".$locid.",";

				}
        		$saleQry = "SELECT name,sales.id,".$con." year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from sales left join salesections on sales.id = salesections.nameId where  nameId IN (".$saleId.") AND ".$where." GROUP BY sales.id, location_id";

        		$filterdata = DB::SELECT($saleQry);
        		$i = 0;

				foreach($filterdata as $k =>$v) {

				// {	$result2[$i]['locID'] =  "$location";
				// 	$result2[$i]['dt'] =  $v->dt;
					$result[$i]['name'] =  $v->name;

					foreach($allLocations as $key=>$val)
					{
						$loc = 'location_'.$val->id;
              $result[$i]['location_'.$val->id] = $v->$loc;


				$grCnt = 0;

								$graphUnit[$grCnt]['id'] = "A".$key;
								$graphUnit[$grCnt]['bullet'] = "round";
								$graphUnit[$grCnt]['bulletBorderAlpha'] = 1;
								$graphUnit[$grCnt]['bulletColor'] = "#FFFFFF";
								$graphUnit[$grCnt]['bulletSize'] = 5;
								$graphUnit[$grCnt]['hideBulletsCount'] = 50;
								$graphUnit[$grCnt]['lineThickness'] = 2;
								$graphUnit[$grCnt]['title'] = "red line";
								$graphUnit[$grCnt]['useLineColorForBulletBorder'] = true;
								$graphUnit[$grCnt]['valueField'] = "location_".$val->id;
								$grCnt++;
							}
                	$i++;


				}

				// $result = json_encode($result);
				 $result1['result'] = $result;
				 $result1['graphUnit'] = $graphUnit; //$finalStr; //
				 return json_encode($result1);
				// $filename = "/home/kvmgmt/public_html/chartSectionData.txt";
				// file_put_contents($filename, '');
				// file_put_contents($filename, $result);
				// $departmentArr = array("Sales","Discounts","Adjustments","Receipts");
				//  return view('reportingsections.index', compact('id', 'sales', 'discount', 'adjustment', 'receipt','location','locationId','sorting','allLocations','Sales','Discounts','Adjustments','Receipts','result'));
         }
	public function printReportData($strDate,$edDate,$sortBy,$sortBy1,$location=0,$sales=0,$discount=0,$adjustment=0,$receipt=0){
        $Sales = $Discounts = $Receipts =$Adjustments =[];
		    $startDate = $endDate = $saleId = $disId = $adjId = $recId = $saleCon = $disCon = $adjCon = $recCon = '';
		    $result = array();
        $x = explode("-",$strDate);
        $startDate = $x[2]."-".$x[0]."-".$x[1];
        $y = explode("-",$edDate);
        $endDate = $y[2]."-".$y[0]."-".$y[1];
        $saleId = $sales;
        $disId = $discount;
        $adjId = $adjustment;
        $recId = $receipt;
			$where 		= '';
			$groupBy 	= '';
			$orderBY 	= '';
			if($sortBy != '') {
				if($sortBy == 0) {
					$groupBy = "dt";
					$orderBy = "dt ASC";

				}
				if($sortBy == 1) {
					$groupBy = "wk";
					$orderBy = "wk ASC";
				}
				if($sortBy == 2) {
					$groupBy = "month_no, yr";
					$orderBy = "month_no ASC, yr ASC";
				}
				if($sortBy == 3) {
					$groupBy = "yr";
					$orderBy = "yr ASC";
				}
			}
        	$groupBy =  "location_id, ". $groupBy;
        if($startDate != '' && $endDate != ''){
				$where .= " dataDate BETWEEN '".$startDate."' AND '".$endDate."'";
		}

		    $where .= " and location_id IN (".$location.")";
		 		$allSalesQry = "SELECT * FROM `sales` where id IN(".$saleId.")";
				$allSalesDep = DB::SELECT($allSalesQry);
				$allDisQry = "SELECT * FROM `discounts` where id IN(".$disId.")";
				$allDisDep = DB::SELECT($allDisQry);
				$allAdjQry = "SELECT * FROM `adjustments` where id IN(".$adjId.")";
				$allAdjDep = DB::SELECT($allAdjQry);
				$allRecQry = "SELECT * FROM `receipts` where id IN(".$recId.")";
				$allRecDep = DB::SELECT($allRecQry);
				$locationQry = "SELECT * from locations where id IN(".$location.")";
		 		$allLocations = DB::SELECT($locationQry);
				 $con = '';
				 $locid = '';
				foreach($allLocations as $key=>$val)
		        {
		        		$locid = $val->id;
						$con .= " FORMAT(IFNULL(sum(case when location_id = ".$locid." then amount end),0),2) as location_".$locid.",";

				}
        		$saleQry = "SELECT  ".$con."  year(dataDate) as yr, monthname(dataDate) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt, nameDep ,locations.location FROM `salesections` join locations on salesections.location_id = locations.id where ".$where." GROUP BY ".$groupBy." order by ".$orderBy." limit 7";
        		$filterdata = DB::SELECT($saleQry);
        		$i = 0;

				foreach($filterdata as $k =>$v)
				{	$result[$i]['locID'] =  "$location";
					$result[$i]['dt'] =  $v->dt;
					foreach($allLocations as $key=>$val)
					{
						$loc = 'location_'.$val->id;
						$result[$i]['location_'.$val->id] = "$".$v->$loc;
						if ($sortBy==0)
						{
							$chWhere  = " and date(dataDate) = '".$filterdata[$i]->dt."'";
							$key = $filterdata[$i]->dt;
							$result[$i]['sortBy'] = $filterdata[$i]->dt;
						} else if ($sortBy==1)
						{
							$chWhere  = " and week(dataDate) =".$filterdata[$i]->wk." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key= $this->getWeekDates($filterdata[$i]->yr,$filterdata[$i]->wk,true);
							$result[$i]['sortBy'] = $key;
						} else if($sortBy==2)
						{

							$chWhere  = " and month(dataDate) = ".$filterdata[$i]->month_no." and year(dataDate)  = ". $filterdata[$i]->yr."";
							$key = date("M, Y", strtotime($filterdata[$i]->dt));
							$result[$i]['sortBy'] = $key;
						} else if ($sortBy==3)
						{
							$key = $filterdata[$i]->yr;
							$result[$i]['sortBy'] = $key;
							$chWhere  = " and year(dataDate)  = ". $filterdata[$i]->yr."";
						}
						$deptQry = DB::SELECT("select name, 'Sales' as parent,sales.id,FORMAT((case when(nameDep = 'sale') then amount else 0 end),2) as amount, FORMAT((case when(nameDep = 'sale') then unit else 0 end),2) as unit, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from sales left join salesections on sales.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$saleId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
										$resData['location_'.$val->id.'_Sales_'.$dp->dt][$ky] = $dp->amount;
										$resDataun['location_'.$val->id.'_Sales_'.$dp->dt][$ky] = $dp->unit;
										$Sales[$ky] = $dp->name;
								}
						}
						$deptQry = DB::SELECT("select name, 'Discounts' as parent,discounts.id,FORMAT((case when(nameDep = 'dis') then amount else 0 end),2) as amount, FORMAT((case when(nameDep = 'dis') then unit else 0 end),2) as unit, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from discounts left join salesections on discounts.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$disId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
										$resData['location_'.$val->id.'_Discounts_'.$dp->dt][$ky] = $dp->amount;
										$resDataun['location_'.$val->id.'_Discounts_'.$dp->dt][$ky] = $dp->unit;
										$Discounts[$ky] = $dp->name;
								}
						}
						$deptQry = DB::SELECT("select name, 'Adjustments' as parent,adjustments.id,FORMAT((case when(nameDep = 'adj') then amount else 0 end),2) as amount, FORMAT((case when(nameDep = 'adj') then unit else 0 end),2) as unit, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from adjustments left join salesections on adjustments.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$adjId.") AND".$where." ".$chWhere."  GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
								foreach($deptQry as $dp) {
									    $ky = str_replace(" ", "_", $dp->name);
										$resData['location_'.$val->id.'_Adjustments_'.$dp->dt][$ky] = $dp->amount;
										$resDataun['location_'.$val->id.'_Adjustments_'.$dp->dt][$ky] = $dp->unit;
										$Adjustments[$ky] = $dp->name;
								}
						}
						$deptQry = DB::SELECT("select name, 'Receipts' as parent,receipts.id,FORMAT((case when(nameDep = 'rec') then amount else 0 end),2) as amount, FORMAT((case when(nameDep = 'rec') then unit else 0 end),2) as unit, year(dataDate) as yr, concat(monthname(dataDate),',' , year(dataDate)) as mn, month(dataDate) as month_no, week(dataDate) as wk, date(dataDate) as dt from receipts left join salesections on receipts.id = salesections.nameId where location_id = '".$val->id."' and nameId IN (".$recId.") AND ".$where." ".$chWhere." GROUP BY nameId, ".$groupBy);
						if(!empty($deptQry)) {
									foreach($deptQry as $dp) {
										    $ky = str_replace(" ", "_", $dp->name);
											$resData['location_'.$val->id.'_Receipts_'.$dp->dt][$ky] = $dp->amount;
											$resData_un['location_'.$val->id.'_Receipts_'.$dp->dt][$ky] = $dp->unit;
											$Receipts[$ky] = $dp->name;
									}
						}
					}
				$i++;
				}

				$departmentArr = array("Sales","Discounts","Adjustments","Receipts");
				 return view('reportingsections.printReportData', compact('Sales','Discounts','Receipts','Adjustments','sales', 'discount', 'adjustment', 'receipt','location','sortBy','sortBy1','allLocations','departmentArr','result'));
         }
	public function getWeekDates($year, $week, $start=true)
		{
			$week = $week+1;
			$from = date("Y-m-d", strtotime("{$year}-W{$week}-1")); //Returns the date of monday in week
			$to = date("Y-m-d", strtotime("{$year}-W{$week}-7"));   //Returns the date of sunday in week

			if($start) {
				return $from;
			} else {
				return $to;
			}
			// return "Week {$week} in {$year} is from {$from} to {$to}.";
		}

	}
