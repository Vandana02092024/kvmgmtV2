<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\SellerDeliveryDays;
use App\Http\Requests\OrderRequest;
use App\Category;
use App\Seller;
use App\Location;
use App\Product;
use App\Order;
use App\Order_item;
use App\Cart;
use App\Cart_item;
use App\User;
use PDF;
use Mail;
use Session;

class OrderController extends Controller
{
    protected $category;
    protected $seller;
    protected $days;

    public function __construct()
    {
        $this->middleware('auth');
        $this->category = Category::where('status', '=', 1)->pluck('category', 'id');
        $this->seller = Seller::where('status', '=', 1)->pluck('name', 'id');
        $this->users = User::where('status', '=', 1)->pluck('name', 'id');
        $this->days = array(1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$category = $this->category;
		$category->prepend('- Select Category -', 0);
		
		$seller = $this->seller;
        $seller->prepend('- Select Vendor -', 0);

        $user = $this->users;
        $user->prepend('- Select User -', 0);

        if (Auth::user()->role != 'Owner')
        {
            $orders = DB::table('orders')
            ->leftJoin('users', 'orders.order_by', '=', 'users.id')
            ->leftJoin('sellers', 'orders.vendor', '=', 'sellers.id')
            ->select('orders.*', 'users.name', 'sellers.name as vname')
            ->where('orders.location', '=', Session::get('location_id'))
			->orderBy('orders.order_date', 'DESC')
            ->get();

        } else {
			
			$orders = DB::table('orders')
            ->leftJoin('users', 'orders.order_by', '=', 'users.id')
            ->leftJoin('sellers', 'orders.vendor', '=', 'sellers.id')
            ->select('orders.*', 'users.name', 'sellers.name as vname')
			->where('orders.location', '=', Session::get('location_id'))
			->orderBy('orders.order_date', 'DESC')
            ->get();
        }

       return view('orders.index', compact('orders', 'category', 'seller', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {	
		// Session::forget('order_id');
        $category = $this->category;
		$cart = array();
              $products = array();
		if(Session::get('checkType') == 1) {
			$location  = Session::get('location_id');
			$vendorID  = Session::get('vendor_id');
			$svCartId  = "loc-".$location.".".$vendorID.".cart_id";
			if(Session::has($svCartId)) {
				$cart_id = Session::get($svCartId);
				$cart = $this->total_cart_items($cart_id);
			} 
		} else {
			Session::forget('vendor_id');
			Session::forget('category_id');
		}
		
		Session::forget('checkType');
		
		if((Session::has('vendor_id')) && (Session::get('vendor_id') > 0))
        {
            $vendor = Session::get('vendor_id');
			$category_id = Session::get('category_id');
            $vendorSel = $vendor;
			$catgorySel = $category_id;
			
            $products = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('products.code', 'products.id', 'products.name', 'products.unit', 'products.price', 'categories.category','categories.rank')
            ->where('products.vendor', '=', $vendor)
			->where('categories.id', '=', $category_id)
			->orderBy('products.rank')
            ->get();
			
			$categories = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('categories.id','categories.category')
            ->where('products.vendor', '=', $vendor)
			->groupby('categories.category')
			->orderBy('categories.rank')
			->pluck('categories.category','categories.id');
			
		}  else
        {
            $vendorSel = 0;
			$catgorySel = 0;
           	
			$categories = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('categories.id','categories.category')
            ->where('products.vendor', '=', $vendorSel)
			->groupby('categories.category')
			->orderBy('categories.rank')
			->get();
		} 
        
        $seller = $this->seller;
        $seller->prepend('- Select Vendor -', 0);
		$category->prepend('- Select Category -', 0);
		
		
		// $cart = $this->total_cart_items();
		// print_r($cart);
		// $x = $this->get_cart_detail(Session::get('order_id'), 4);
        // var_dump($findTotalVals);
        // die();
		
		
		/* $categories = \DB::table('products')
			->leftJoin('categories', 'products.category', '=', 'categories.id')
			->select('categories.category')
			->orderBy('categories.rank')
			->groupBy('categories.category')
			->pluck('categories.category','categories.category'); */
		
        return view('orders.create',compact('category','categories','catgorySel','products','seller','vendorSel','cart'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {

        $order_id = '#ORD-' . time();
        $total_qt = 0;
        $total_pr = 0;
        $rows_count = $request['rows_count'];
        

        $order = Order::create([
            'order_id'   => $order_id,
            'order_date' => date('Y-m-d', time()),
            'order_by'   => $request['order_by'],
            'vendor'     => $request['vendor'],
            'vendor_name'      => $request['vendor_name'],
            'vendor_phone'     => $request['vendor_phone'],
            'vendor_email'     => $request['vendor_email'],
            'vendor_address'   => $request['vendor_address'],
            'status'     => 'Orderd',
        ]);
        
        $idAry = array();
        $qpAry = array();

        for ($i=1; $i < $rows_count; $i++)
        { 
            if($request['qty'.$i] != '')
            {
                $purchase_price = ($request['qty'.$i] * $request['actPrc'.$i]);

                Order_item::create([
                    'order_id'  => $order_id,
                    'product'   => $request['prdId'.$i],
                    'quantity'  => $request['qty'.$i],
                    'price'     => $purchase_price,
                ]);

                $total_qt += $request['qty'.$i];
                $total_pr += $purchase_price;

                $idAry[$request['prdId'.$i]] = $request['prdId'.$i];
                $qpAry['qty'][$request['prdId'.$i]] = $request['qty'.$i];
                $qpAry['prc'][$request['prdId'.$i]] = $purchase_price;
            }            
        }

        $order->update([
            'quantity'   => $total_qt,
            'price'      => $total_pr
        ]);

        // GENERATE PDF //
        // ## generate html ## //
        $html = '<table width="100%" border="0">
                    <tr><td width="2%">&nbsp;</td><td colspan="4"><h2>Order</h2></td></tr>
                    <tr><td>&nbsp;</td><td>Vendor</td><td width="30%">'.$request['vendor_name'].'</td><td>Order Date</td><td width="30%">'.date('Y-m-d', time()).'</td></tr>
                    <tr><td>&nbsp;</td><td>Email</td><td>'.$request['vendor_email'].'</td><td>Order ID</td><td>'.$order_id.'</td></tr>
                    <tr><td>&nbsp;</td><td>Phone</td><td>'.$request['vendor_phone'].'</td><td>Order By</td><td>'.(\Auth::user()->name).'</td></tr>
                    <tr><td>&nbsp;</td><td>Address</td><td colspan="3">'.$request['vendor_address'].'</td></tr>
                    <tr><td>&nbsp;</td><td colspan="4">&nbsp;</td></tr>
                    <tr><td colspan="5"></td></tr></table>
                    <table width="100%" border="1" cellspacing="1" cellpadding="2">
                        <tr><td width="10%">Code</td><td width="30%">Category</td><td width="40%">Name</td><td width="10%">Quantity</td><td width="10%">Price</td></tr>';

        // ## fetch product detail ## //
        $products = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('products.code', 'products.id', 'products.name', 'categories.category')
            ->whereIn('products.id', $idAry)
            ->get();

        foreach ($products as $prdVl) {
            $html .= '<tr><td>'.$prdVl->code.'</td><td>'.$prdVl->category.'</td><td>'.$prdVl->name.'</td>
                    <td>'.$qpAry['qty'][$prdVl->id].'</td><td>'.$qpAry['prc'][$prdVl->id].'</td></tr>';
        }

        $html .= '<tr><td colspan="3">Total</td><td>'.$total_qt.'</td><td>'.$total_pr.'</td></tr>
                  </table>';

        PDF::SetTitle('');
        PDF::AddPage();
        PDF::writeHTML($html, true, false, true, false, '');
        PDF::Output(public_path().'/order_pdf/PDF_'.$order_id.'.pdf', 'F');
        // PDF::reset();

        $email = $request->all();
        $email['order_id'] = $order_id;

        Mail::send('emails.order', $request->all(), function($message) use ($email) {
            $message->to($email['vendor_email']);
            $message->subject('Zips Order');
            $message->attach(public_path().'/order_pdf/PDF_'.$email['order_id'].'.pdf');
        });


        return redirect()->route('order.index')->with('message','Order has been created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
		$seller = Seller::where('id', '=', $order->vendor)->get()->first();
        $category = $this->category;
        $products = \DB::table('order_items')
            ->leftJoin('products', 'order_items.product', '=', 'products.id')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('products.code', 'products.id', 'products.name', 'products.unit', 'order_items.price', 'categories.category', 'order_items.quantity')
            ->where('order_items.order_id', '=', $order->order_id)
            ->get();
			
		
        return view('orders.show',compact(['seller','order','products']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
		
		$order_item = Order_item::where('order_id', '=', $order['order_id'])->delete();
        $order->delete();
	   
       return redirect()->route('order.index')->with('message','Order has been deleted successfully!');
    }

    public function setCart() {
		 Session::put('checkType', 1);
		 return 1;
    }
    public function showSeller(Request $request)
    {
        $sellerId = $request->sellerId;
        $seller = Seller::where('id', '=', $sellerId)->first();

        return view('orders.vendor',compact('seller'));
    }

    public function searchOrder(Request $request)
    {
		$category = $this->category;
        $category->prepend('- Select Category -', 0);

        $seller = $this->seller;
        $seller->prepend('- Select Vendor -', 0);

        $user = $this->users;
        $user->prepend('- Select User -', 0);

        $another = 0;


        $product_name = trim($request->get('product_name'));
        $product_code = trim($request->get('product_code'));

        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.order_by', '=', 'users.id')
            ->leftJoin('sellers', 'orders.vendor', '=', 'sellers.id');

        if(($product_name != '') || ($product_code != '') || ($request->get('category') != 0)){
            $orders->leftJoin('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product');
        }

        if($request->get('category') != 0)
        {
            $orders->leftJoin('categories', 'categories.id', '=', 'products.category')
                    ->where('categories.id', '=', $request->get('category'));
        }

        if($request->get('seller') != 0)
        {
            $orders->where('sellers.id', '=', $request->get('seller'));
        }

        if($request->get('user') != 0)
        {
            $orders->where('orders.order_by', '=', $request->get('user'));
        }

        if(($request->get('order_from') != '') && ($request->get('order_to') != ''))
        {
            $orders->where('orders.order_date', '>=', $request->get('order_from'))
            ->where('orders.order_date', '<=', $request->get('order_to'));
        }

        if($product_name != '')
        {
            $orders->where('products.name', 'like', '%' . $product_name . '%');
            $another = 1;
            if (Auth::user()->role != 'Owner')
            $orders->where('orders.order_by', '=', Auth::user()->id);

            $orders = $orders->select('orders.order_id', 'orders.id', 'orders.status', 'orders.order_date', 'orders.order_by', 'products.name as pname', 'order_items.quantity', 'order_items.price', 'users.name', 'sellers.name as vname')
                ->groupBy('orders.order_id')->get();

            return view('orders.product_index', compact('orders', 'category', 'seller', 'user'));
        }

        // if($product_code != '')
        // {
        //     $orders->where('products.code', 'like', '%' . $product_code . '%');
        // }

        

        if (Auth::user()->role != 'Owner')
            $orders->where('orders.order_by', '=', Auth::user()->id);

        $orders = $orders->select('orders.*', 'users.name', 'sellers.name as vname')
                ->groupBy('orders.order_id')->get();

        // $request->flash();
        if($another > 0){
            return view('orders.product_index', compact('orders', 'category', 'seller', 'user'));
        }else{
            return view('orders.index', compact('orders', 'category', 'seller', 'user'));
        }
    }

    // function changeVendor(Request $request)
    // {
    //     $vendor = $request->get('vendor');
    //     Session::put('vendor_id', $vendor);
    //     return redirect()->route('order.create');
    // }
	
	function changeVendor(Request $request)
    {
		$location  = Session::get('location_id');
		//$getVendor = $location.".vendor_id";
		
		$vendor = $request->get('vendor');
        Session::put('vendor_id', $vendor);
		
		$svCartId  = "loc-".$location.".".$vendor.".cart_id";
		
		
		
		$category = $request->get('category');
        Session::put('category_id', $category);

		Session::put('checkType', 1);
        // $order_id = $this->generate_order_id();
        // Session::put('order_id', $order_id);
		//$cart = $this->total_cart_items();

        // echo "<pre>";
        // print_r($cart);
        // die();
		// if($cart['cart_count'] > 0){
           //Session::put($svCartId, $cart['id']);
        // }
		return redirect()->route('order.create');
    }

	function addToCart(Request $request)
    {
		$location  = Session::get('location_id');
		$vendor = Session::get('vendor_id');
		// variable for get and save array for particular location //
		//$svCartId  = $location.".cart_id";
		$svCartId  = "loc-".$location.".".$vendor.".cart_id";
	
		$id       = $request->get('id');
        $qty      = (($request->get('qty') == '') || ($request->get('qty') == 0))?1:$request->get('qty');
        $name     = ($request->get('name'));
        $price    = ($request->get('price') * $qty);
        $actprice = $request->get('price');
        $code     = $request->get('prdCd');
        $category = $request->get('prdCt');
        $unit     = $request->get('prdUt');
		
		// if(Session::has('cart.'.$vendor))
        if(Session::has($svCartId))
        {			
					$cart_id = Session::get($svCartId);
					$cart_vendor = $this->get_cart_vendor($cart_id);
					$cart_detail = $this->get_cart_detail($cart_id, $id, true);
					
					if($cart_detail)
					{
						$findCart = Cart_item::where('id', '=',  $cart_detail->id)->first();
						$findCart->quantity  = $qty;
						$findCart->price     = $price;
						$findCart->save();
					} else
					{
						$cart_items = Cart_item::create([
							'cart_id'       => $cart_id,
							'product'       => $id,
							'product_name'  => $name,
							'code'          => $code,
							'category'      => $category,
							'unit'          => $unit,
							'quantity'      => $qty,
							'actual_price'  => $actprice,
							'price'         => $price,
						]);
					}

					$findTotalVals = DB::table('cart_items')->where('cart_id', '=', $cart_id)
							->get( array(
							DB::raw( 'SUM(price) AS price' ),
							DB::raw( 'SUM(quantity) AS quantity' )
						));

					$findCart = Cart::where('id', '=',  $cart_id)
								->update(['quantity' => $findTotalVals[0]->quantity, 'price' => $findTotalVals[0]->price, 'last_updated_by' => Auth::user()->id]);

					$ttlcart = $this->total_cart_items($cart_id);
		} else {
				// Session::put('cart.'.$vendor, '');
				// Session::push('cart.'.$vendor, $cart);
				// $order_id = $this->generate_order_id();
				// $cart_id = Session::get('cart_id');
				
				$cart = Cart::create([
					'order_by'    => Auth::user()->id,
					'location_id' => Session::get('location_id'),
					'vendor_id'   => Session::get('vendor_id'),
					// 'cart_id'    => $cart_id,
					'quantity'    => $qty,
					'price'       => $price,
				]);        

				$cart_items = Cart_item::create([
					'cart_id' => $cart->id,
					'product'       => $id,
					'product_name'  => $name,
					'code'          => $code,
					'category'      => $category,
					'unit'          => $unit,
					'quantity'      => $qty,
					'actual_price'  => $actprice,
					'price'         => $price,
				]);
			
				Session::put($svCartId, $cart->id);
				$ttlcart = $this->total_cart_items($cart->id);
        }
		
        // return var_dump($key);
        // Session::forget("cart.2");
        // return count(Session::get('cart.'.$vendor));
        return $ttlcart['cart_count'];
    }

	function showOrder(Request $request)
    {
		$location  = Session::get('location_id');
		$VendorId  = Session::get('vendor_id');
		// variable for get and save array for particular location //
		$svCartId  = "loc-".$location.".".$VendorId.".cart_id";
		// if(!Session::has('cart'))
        // if($request->get('cart_id') == '')

        if(!Session::has($svCartId))
            return redirect()->route('order.create')->with('warning','Your cart is empty, please add item to your cart first!');

		$cart_id = Session::get($svCartId);
		$cart_vendor = $this->get_cart_vendor($cart_id);
		$orderDt = $this->get_complete_cart_detail($cart_id);
		
		$order_id = $this->generate_order_id($cart_vendor);
		
		$getDeliveryData = explode("-", $order_id);
		$deliverDay = date_format(date_create_from_format('ymd', $getDeliveryData[2]), 'l');
	    // $order_id = $this->generate_order_id();
	    $days = $this->days;
		return view('orders.showorder', compact('orderDt', 'order_id', 'days', 'deliverDay'));
    }

	function get_cart_vendor($cart_id) {
		 $cart = Cart::where('id', '=', $cart_id)->get()->first();
		 return $cart->vendor_id;
	}
	   
    // function changeCart(Request $request)
    // {
    //     $key            = $request->get('id'); 
    //     $qty            = $request->get('qty'); 
    //     $vendor         = Session::get('vendor_id');

    //     if($qty == 0)
    //     {
    //         Session::forget('cart.'.$vendor.'.'.$key);
    //         return 'removed';
    //     }else{

    //         $existing_cart  = Session::get('cart.'.$vendor);

    //         $existing_cart[$key]['qty'] = $qty;
    //         $existing_cart[$key]['price'] = ($qty * $existing_cart[$key]['actprice']);

    //         Session::put('cart.'.$vendor, $existing_cart);

    //         $ttlqty    = array_sum(array_column($existing_cart, 'qty'));
    //         $ttlprice  = array_sum(array_column($existing_cart, 'price'));

    //         return $ttlqty.'|'.$ttlprice.'|'.$existing_cart[$key]['price'];
    //     }        
    // }




    // public function saveOrder(Request $request)
    // {
    //     // print_r($request->all());
    //     // die();
    //     $vendor         = Session::get('vendor_id');
    //     $existing_cart  = Session::get('cart.'.$vendor);

    //     $ttlqty    = array_sum(array_column($existing_cart, 'qty'));
    //     $ttlprice  = array_sum(array_column($existing_cart, 'price'));


    //     try {
    //           $order = Order::create([
    //             'order_id'   => $request['order_id'],
    //             'order_date' => date('Y-m-d', time()),
    //             'order_by'   => $request['order_by'],
    //             'vendor'     => Session::get('vendor_id'),
    //             'vendor_name'      => $request['vendor_name'],
    //             'vendor_phone'     => $request['vendor_phone'],
    //             'vendor_email'     => $request['vendor_email'],
    //             'vendor_address'   => $request['vendor_address'],
    //             'location'   => $request['location'],
    //             'quantity'   => $ttlqty,
    //             'price'      => $ttlprice,
    //             'status'     => 'Orderd'
    //         ]);
    //     }
    //     catch (\Exception $e) {
    //         return redirect()->route('order.create')->with('warning','The order id already exists!!');
    //     }

        
        
    //     // GENERATE PDF //
    //     // ## generate html ## //
    //     $html = '<table width="100%" border="0">
    //                 <tr><td width="2%">&nbsp;</td><td colspan="4"><h2>Supply Order</h2></td></tr>
    //                 <tr><td>&nbsp;</td><td>Vendor</td><td width="30%">'.$request['vendor_name'].'</td><td>Order Date</td><td width="30%">'.date('m/d/Y', time()).'</td></tr>
    //                 <tr><td>&nbsp;</td><td>Email</td><td>'.$request['vendor_email'].'</td><td>Order ID</td><td>'.$request['order_id'].'</td></tr>
    //                 <tr><td>&nbsp;</td><td>Phone</td><td>'.$request['vendor_phone'].'</td><td>Order By</td><td>'.(\Auth::user()->name).'</td></tr>
    //                 <tr><td>&nbsp;</td><td>Address</td><td colspan="3">'.$request['vendor_address'].'</td></tr>
    //                 <tr><td>&nbsp;</td><td colspan="4">&nbsp;</td></tr>
    //                 <tr><td colspan="5"></td></tr></table>
    //                 <table width="100%" border="1" cellspacing="1" cellpadding="2">
    //                     <tr><td width="10%">Code</td><td width="30%">Category</td><td width="40%">Name</td><td width="10%">Quantity</td><td width="10%">Price</td></tr>';


    //     // for ($i=1; $i < $rows_count; $i++)
    //     foreach ($existing_cart as $key => $value)
    //     { 
    //         if($value['qty'] != '')
    //         {
    //             $purchase_price = $value['price'];

    //             Order_item::create([
    //                 'order_id'  => $request['order_id'],
    //                 'product'   => $value['id'],
    //                 'product_name'  => $value['name'],
    //                 'code'          => $value['code'],
    //                 'category'      => $value['category'],
    //                 'unit'          => $value['unit'],
    //                 'quantity'      => $value['qty'],
    //                 'actual_price'  => $value['actprice'],
    //                 'price'         => $value['price'],
    //             ]);

    //             $html .= '<tr><td>'.$value['code'].'</td><td>'.$value['category'].'</td><td>'.$value['name'].'</td>
    //                 <td>'.$value['qty'].'</td><td>$'.number_format($value['price'],2).'</td></tr>';
    //         }            
    //     }


    //     $html .= '<tr><td colspan="3" align="right">Total</td><td>'.$ttlqty.'</td><td>$'.number_format($ttlprice, 2).'</td></tr>
    //               </table>';

    //     PDF::SetTitle('');
    //     PDF::AddPage();
    //     PDF::SetFont('helvetica', '', 10);
    //     PDF::writeHTML($html, true, false, true, false, '');
    //     PDF::Output(public_path().'/order_pdf/'.$request['order_id'].'.pdf', 'F');
    //     // PDF::reset();

    //     $email = $request->all();
        

    //     Mail::send('emails.order', $request->all(), function($message) use ($email) {
    //         $message->from('bvago@kvmgmt.com', 'Zips');
    //         $message->to($email['vendor_email']);
    //         $message->subject(''.$email['order_id'].'');
    //         $message->attach(public_path().'/order_pdf/'.$email['order_id'].'.pdf');
    //     });

    //     Session::forget("cart");
    //     return redirect()->route('order.index')->with('message','Order has been created successfully!');
    // }


    public function emptyCart()
    {
		$location  = Session::get('location_id');
		$VendorId  = Session::get('vendor_id');
		$svCartId  = "loc-".$location.".".$VendorId.".cart_id";
		
	    // Session::forget("cart");
        $cart_id = Session::get($svCartId);
        DB::table('cart_items')->where('cart_id', $cart_id)->delete();
        DB::table('carts')->delete($cart_id);
        Session::forget($svCartId);
        return redirect()->route('order.create')->with('message','Cart deleted successfully!');
    }

	public function sendOrderAgain(Request $request)
    {
        $order_id = $request->get('order_id');
        $order = Order::where('order_id', '=', $order_id)->get()->toArray();
        $email = $order[0];
		
		Mail::send('emails.order', $email, function($message) use ($email) {
            $message->from('bvago@kvmgmt.com', 'Zips');
            $message->to($email['vendor_email']);
            $message->subject(''.$email['order_id'].'');
            $message->attach(public_path().'/order_pdf/'.$email['order_id'].'.pdf');
        });

        Session::forget("cart");
        return redirect()->route('order.index')->with('message','Email send successfully!');
    }
	
	function total_cart_items($cart_id = false)
    {
		$location  = Session::get('location_id');
		$VendorId  = Session::get('vendor_id');
		$svCartId  = "loc-".$location.".".$VendorId.".cart_id";
		
		if($cart_id)
        {
			return $cart = Cart::leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.id', '=', $cart_id)
                ->select(DB::raw("sum(cart_items.quantity) as cart_count"), 'carts.id')
                ->first()->toArray();
				
        } else if(Session::has($svCartId))
        {	
			$cart_id = Session::get($svCartId);
            return $cart = Cart::leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.id', '=', $cart_id)
                ->select(DB::raw("sum(cart_items.quantity) as cart_count"), 'carts.id')
                ->first()->toArray();
        }
		// else
        // {
			// echo "3";
            // $vendor      = Session::get('vendor_id');
            // $location_id = Session::get('location_id');
            // $user_id     = Auth::user()->id;

            // return $cart = Cart::leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                // ->where('location_id', '=', $location_id)
                // ->where('vendor_id', '=', $vendor)
                // ->select(DB::raw("sum(cart_items.quantity) as cart_count"), 'carts.id')
                // ->first()->toArray();
        // }
    }


    function generate_order_id($cartVendor = false)
    {
		if($cartVendor) {
			$vendorid   = $cartVendor;
		} else {
			$vendorid   = Session::get('vendor_id');
		}
		
		//$vendorid   = Session::get('vendor_id');
        $locationid = Session::get('location_id');
		
        $seller = SellerDeliveryDays::where('seller_id', '=', $vendorid)->where('location_id', '=', $locationid)->get()->first();
        
        $del_day = $seller->delivery_day;

        /*if($seller != NULL){}
        else{
            return redirect()->route('order.create')->with('warning','Please enter vendor delivery days')->header('Cache-Control', 'no-store, no-cache, must-revalidate');;
        }*/

        /*
		try {
            $del_day = $seller->delivery_day;
        }
        catch (\Exception $e) {

            
        }*/


        // $seller = Seller::where('id', '=', $vendorid)->get()->first();
        $days   = $this->days;

        $location   = Location::where('id', '=', $locationid)->first();
        $comingDay  = strtotime('next '.$days[$del_day]);
        $orderDay   = date('ymd', $comingDay);

        return $orderID = $location->location_code.'-'.$vendorid.'-'.$orderDay;
    }


    function get_cart_detail($cart_id, $product_id = false, $return_obj = false)
    {
        $cart = DB::table('carts')->leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id');
        $cart->where('carts.id', '=', $cart_id);

        if($product_id)
            $cart->where('cart_items.product', '=', $product_id);
       
		if($return_obj)
            $cart = $cart->select('carts.order_by', 'carts.location_id', 'carts.vendor_id', 'carts.quantity as total_qty', 'carts.price as total_price', 'carts.id as cart_id', 'cart_items.*')->first();
        else
            $cart = $cart->select('carts.order_by', 'carts.location_id', 'carts.vendor_id', 'carts.quantity as total_qty', 'carts.price as total_price', 'carts.id as cart_id', 'cart_items.*')->get();

        return $cart;

        // if($product_id)
        //     $cart->where('cart_items.product', '=', $product_id);

        // return $cart = $cart->

        /*return $cart = DB::table('carts')
            ->leftJoin('cart_items', 'cart_items.cart_id', '=', 'carts.order_id');
        if($full_detail){
            $cart->leftJoin('sellers', 'carts.vendor_id', '=', 'sellers.id')
            ->leftJoin('locations', 'carts.location_id', '=', 'locations.id')
            ->select('carts.*', 'sellers.*', 'locations.*', 'cart_items.*');
        }else{
            $cart->select('carts.*', 'cart_items.*');
        }
        if($product_id)
            $cart->where('cart_items.product', '=', $product_id);
        $cart = $cart->where('carts.id', '=', $order_id)->get();*/
    }


    public function get_complete_cart_detail($cart_id)
    {
        return $cart = DB::table('carts')
            ->leftJoin('cart_items', 'cart_items.cart_id', '=', 'carts.id')
            ->leftJoin('sellers', 'carts.vendor_id', '=', 'sellers.id')
            ->leftJoin('locations', 'carts.location_id', '=', 'locations.id')
            ->select('carts.order_by', 'carts.location_id', 'carts.vendor_id', 'carts.quantity as ttl_qty', 'carts.price as ttl_prc', 'carts.last_updated_by' , 'sellers.*', 'locations.*', 'cart_items.*')
            ->where('carts.id', '=', $cart_id)
            ->get();
    }

	
    function changeCartUpdate(Request $request)
    {
        $key            = $request->get('id'); 
        $qty            = $request->get('qty'); 
		$order_id       = $request->get('order_id'); 
        $vendor         = Session::get('vendor_id');

        if($qty == 0)
        {
            // Session::forget('cart.'.$vendor.'.'.$key);
			DB::table('order_items')->where('id', $key)->delete();
            
            $findTotalVals = DB::table('order_items')->where('order_id', '=', $order_id)
                ->get( array(
                DB::raw( 'SUM(price) AS price' ),
                DB::raw( 'SUM(quantity) AS quantity' )
             ));
			
			Order::where('order_id', '=', $order_id)
			  ->update([
				'quantity' => $findTotalVals[0]->quantity, 
				'price' => $findTotalVals[0]->price
			]);
			
			return $findTotalVals[0]->quantity.'|'.$findTotalVals[0]->price.'|0.00|removed';
			
        } else {
			
			$findCart = Order_item::where('id', '=', $key)->get()->first();
			$priceVl  = ($qty * $findCart->actual_price);
			
            Order_item::where('id', '=', $key)
            ->update([
                'quantity' => $qty, 
                'price' => $priceVl
             ]);
			 
			$findTotalVals = DB::table('order_items')->where('order_id', '=', $order_id)
                ->get( array(
                DB::raw( 'SUM(price) AS price' ),
                DB::raw( 'SUM(quantity) AS quantity' )
             ));
			
			Order::where('order_id', '=', $order_id)
			  ->update([
				'quantity' => $findTotalVals[0]->quantity, 
				'price' => $findTotalVals[0]->price
			]);
			  
			return $findTotalVals[0]->quantity.'|'.$findTotalVals[0]->price.'|'.$priceVl;
        } 
         
	}

	function changeCart(Request $request)
    {
        $key            = $request->get('id'); 
        $qty            = $request->get('qty'); 
        $vendor         = Session::get('vendor_id');

        if($qty == 0)
        {
            // Session::forget('cart.'.$vendor.'.'.$key);
            $findCart = Cart_item::where('id', '=',  $key)->first();

            DB::table('cart_items')->delete($key);

            $findTotalVals = DB::table('cart_items')->where('cart_id', '=', $findCart->cart_id)
                ->get( array(
                DB::raw( 'SUM(price) AS price' ),
                DB::raw( 'SUM(quantity) AS quantity' )
            ));

            Cart::where('id', '=',  $findCart->cart_id)
            ->update([
                'quantity' => $findTotalVals[0]->quantity, 
                'price' => $findTotalVals[0]->price, 
                'last_updated_by' => Auth::user()->id
            ]);

            return 'removed';
        } else {

            // $existing_cart  = Session::get('cart.'.$vendor);

            // $existing_cart[$key]['qty'] = $qty;
            // $existing_cart[$key]['price'] = ($qty * $existing_cart[$key]['actprice']);

            // Session::put('cart.'.$vendor, $existing_cart);

            // $ttlqty    = array_sum(array_column($existing_cart, 'qty'));
            // $ttlprice  = array_sum(array_column($existing_cart, 'price'));

            $findCart = Cart_item::where('id', '=',  $key)->first();
            $priceVl  = ($qty * $findCart->actual_price);
                $findCart->quantity  = $qty;
                $findCart->price     = $priceVl;
                $findCart->save();

            $findTotalVals = DB::table('cart_items')->where('cart_id', '=', $findCart->cart_id)
                ->get( array(
                DB::raw( 'SUM(price) AS price' ),
                DB::raw( 'SUM(quantity) AS quantity' )
            ));

            Cart::where('id', '=',  $findCart->cart_id)
            ->update([
                'quantity' => $findTotalVals[0]->quantity, 
                'price' => $findTotalVals[0]->price, 
                'last_updated_by' => Auth::user()->id
            ]);

			return $findTotalVals[0]->quantity.'|'.$findTotalVals[0]->price.'|'.$priceVl;
        } 
         
	}

	public function saveOrder(Request $request)
    {
		$location  = Session::get('location_id');
		$vendorID  = Session::get('vendor_id');
		$svCartId  = "loc-".$location.".".$vendorID.".cart_id";
		
		if(!Session::has($svCartId))
            return redirect()->route('order.create')->with('warning','Your cart is empty, please add item to your cart first!');
       
        // $cart_id = $request->get('cart_id');
        $cart_id  = Session::get($svCartId);
        $order_id = $request->get('order_id');
        $cart = $this->get_complete_cart_detail($cart_id);

        // Create Order Header //
        $orderDt = DB::table('orders')->where('order_id', $order_id)->first();
		
        if($orderDt){

            // echo "<pre>";
            // print_r($orderDt);
            // $original_order_detail = $this->get_complete_order_detail($orderDt->order_id);
            // echo "<br />";
            // print_r($original_order_detail);
            // // echo "<br />";
            // // print_r($cartDt);
            // die();

            $original_order_detail = $this->get_complete_order_detail($orderDt->order_id);
			$getDeliveryData = explode("-", $orderDt->order_id);
			$deliverDay = date_format(date_create_from_format('ymd', $getDeliveryData[2]), 'l');
			$cart_id = Session::get($svCartId);
            $orderDt = $this->get_complete_cart_detail($cart_id);
            $days    = $this->days;
			
			
            $message = 'This order has already been sent to the vendor! You can find the detail below';
			return view('orders.showcompleteorder', compact('original_order_detail', 'orderDt', 'order_id', 'days', 'message','deliverDay'));
        }else
        {
            try {
				$order = Order::create([
                    'order_id'   => $order_id,
                    'order_date' => date('Y-m-d', time()),
                    'order_by'   => Auth::user()->id,
                    'vendor'     => $cart[0]->vendor_id,
                    'vendor_name'      => $cart[0]->name,
                    'vendor_phone'     => $cart[0]->phone,
                    'vendor_email'     => $cart[0]->email,
                    'vendor_address'   => $cart[0]->address,
                    'location'   		=> $cart[0]->location_id,
                    'quantity'   => $cart[0]->ttl_qty,
                    'price'      => $cart[0]->ttl_prc,
                    'status'     => 'Ordered',
					'comments'   => $request->get('comments'),
                    'created_by' => $cart[0]->order_by,
					'last_updated_by' => $cart[0]->last_updated_by
                ]);
            }
            catch (\Exception $e) {

                // echo "<pre>";
                // echo "The exception code is: " . $e->getCode();
                // echo $e->getMessage();
                // die();
                return redirect()->route('order.create')->with('warning',$e->getMessage());
            }
		
            // GENERATE PDF //
            // ## generate html ## //
			
			$html = '<table width="100%" border="0">
						<tr><td width="2%">&nbsp;</td><td colspan="4" align="center"><h2>'.$cart[0]->location.'</h2></td></tr>
                        <tr><td width="2%">&nbsp;</td><td colspan="4" align="center"><h2>Supply Order</h2></td></tr>
                        <tr><td colspan="5" align="center">&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>Vendor</td><td width="30%">'.$cart[0]->name.'</td><td>Order Date</td><td width="30%">'.date('m/d/Y', time()).'</td></tr>
                        <tr><td>&nbsp;</td><td>Email</td><td>'.$cart[0]->email.'</td><td>Order ID</td><td>'.$order_id.'</td></tr>
                        <tr><td>&nbsp;</td><td>Phone</td><td>'.$cart[0]->phone.'</td><td>Order By</td><td>'.(Auth::user()->name).'</td></tr>
                        <tr><td>&nbsp;</td><td>Address</td><td colspan="3">'.$cart[0]->address.'</td></tr>
						<tr><td>&nbsp;</td><td colspan="4">&nbsp;</td></tr>
                        <tr><td colspan="5"></td></tr></table>
                        <table width="100%" border="1" cellspacing="1" cellpadding="2">
						<tr><td width="10%">Code</td><td width="30%">Category</td><td width="40%">Name</td><td width="10%">Quantity</td><td width="10%">Price</td></tr>';

			foreach ($cart as $key => $value)
            { 
                if($value->quantity != '')
                {
                    Order_item::create([
                        'order_id'  => $order_id,
                        'product'   => $value->product,
                        'product_name'  => $value->product_name,
                        'code'          => $value->code,
                        'category'      => $value->category,
                        'unit'          => $value->unit,
                        'quantity'      => $value->quantity,
                        'actual_price'  => $value->actual_price,
                        'price'         => $value->price,
                    ]);

                    $html .= '<tr><td>'.$value->code.'</td><td>'.$value->category.'</td><td>'.$value->product_name.'</td>
                        <td>'.$value->quantity.'</td><td>$'.number_format($value->price,2).'</td></tr>';
                }            
            }
			
			$html .= '<tr><td colspan="3" align="right">Total</td><td>'.$cart[0]->ttl_qty.'</td><td>$'.number_format($cart[0]->ttl_prc, 2).'</td></tr>
					</table><table width="100%" border="0" cellspacing="1" cellpadding="2";>
						<tr><td colspan="5"></td></tr>
					</table>
					<table width="100%" border="1" cellspacing="1" cellpadding="2"  margin-top:20px;>
						<tr><td width="30%" colspan="3" align="center">COMMENT</td><td width="70%">'.nl2br($request->get('comments')).'</td></tr>
					</table>'; 
			
			
            PDF::SetTitle('');
            PDF::AddPage();
            PDF::SetFont('helvetica', '', 10);
            PDF::writeHTML($html, true, false, true, false, '');
            PDF::Output(public_path().'/order_pdf/'.$order_id.'.pdf', 'F');
				
			$email['email']     = $cart[0]->email;
            $email['order_id']  = $order_id;
            $email['vendor_name']  = $cart[0]->name;
            $email['location']  = $cart[0]->location_code.' '.$cart[0]->location;

            Mail::send('emails.order', $email, function($message) use ($email) {
                $message->from('bvago@kvmgmt.com', 'Zips Supply Order');
                $message->to($email['email']);
                $message->subject(''.$email['order_id'].'');
                $message->attach(public_path().'/order_pdf/'.$email['order_id'].'.pdf');
            });

            Session::forget($svCartId);
			DB::table('cart_items')->where('cart_id', $cart_id)->delete();
            DB::table('carts')->delete($cart_id);

            return redirect()->route('order.index')->with('message','Order has been created successfully!');
        }
    }  
	
	public function get_complete_order_detail($order_id)
    {
        return $cart = DB::table('orders')
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->leftJoin('sellers', 'orders.vendor', '=', 'sellers.id')
            ->leftJoin('locations', 'orders.location', '=', 'locations.id')
            ->select('orders.order_by','orders.comments', 'orders.location as location_id', 'orders.vendor as vendor_id', 'orders.quantity as ttl_qty', 'orders.price as ttl_prc', 'orders.last_updated_by' , 'sellers.*', 'locations.*', 'order_items.*')
            ->where('orders.order_id', '=', $order_id)
            ->get();
    }  

	public function resendOrder(Request $request)
    {
		$location  = Session::get('location_id');
		$vendor    = $request->get('vendor_id');
		$vendorId  = Session::get('vendor_id');
		$svCartId  = "loc-".$location.".".$vendorId.".cart_id";
		
        $cart_id    = $request->get('cart_id');
        $order_id   = $request->get('order_id');
		
		if($request->get('comments') != '') {
			Order::where('order_id', '=', $order_id)
			  ->update([
				'comments' => $request->get('comments')
			]);
		}
		
		$original_order_detail = $this->get_complete_order_detail($order_id);
        $orderDt = $this->get_complete_cart_detail($cart_id);
		
		$html = '<table width="100%" border="0">
					<tr><td width="2%">&nbsp;</td><td colspan="4" align="center"><h2>'.$orderDt[0]->location.'</h2></td></tr>
                    <tr><td width="2%">&nbsp;</td><td colspan="4" align="center"><h2>Supply Order</h2></td></tr>
                    <tr><td colspan="5" align="center">(Modified)</td></tr>
                    <tr><td colspan="5" align="center">&nbsp;</td></tr>
                    <tr><td>&nbsp;</td><td>Vendor</td><td width="30%">'.$orderDt[0]->name.'</td><td>Order Date</td><td width="30%">'.date('m/d/Y', time()).'</td></tr>
                    <tr><td>&nbsp;</td><td>Email</td><td>'.$orderDt[0]->email.'</td><td>Order ID</td><td>'.$order_id.'</td></tr>
                    <tr><td>&nbsp;</td><td>Phone</td><td>'.$orderDt[0]->phone.'</td><td>Order By</td><td>'.(Auth::user()->name).'</td></tr>
                    <tr><td>&nbsp;</td><td>Address</td><td colspan="3">'.$orderDt[0]->address.'</td></tr>
					<tr><td>&nbsp;</td><td colspan="4">&nbsp;</td></tr>
                    <tr><td colspan="5"></td></tr></table>
                    <table width="100%" border="1" cellspacing="1" cellpadding="2">
                        <tr><td width="10%">Code</td><td width="30%">Category</td><td width="40%">Name</td><td width="10%">Quantity</td><td width="10%">Price</td></tr>';


        foreach ($original_order_detail as $key => $value)
        { 
			if($value->quantity != '')
            {
                $html .= '<tr><td>'.$value->code.'</td><td>'.$value->category.'</td><td>'.$value->product_name.'</td>
                    <td>'.$value->quantity.'</td><td>$'.number_format($value->price,2).'</td></tr>';
            }    
		}

        foreach ($orderDt as $key => $value)
        { 
            if($value->quantity != '')
            {
                Order_item::create([
                    'order_id'  => $order_id,
                    'product'   => $value->product,
                    'product_name'  => $value->product_name,
                    'code'          => $value->code,
                    'category'      => $value->category,
                    'unit'          => $value->unit,
                    'quantity'      => $value->quantity,
                    'actual_price'  => $value->actual_price,
                    'price'         => $value->price,
                ]);

                $html .= '<tr><td>'.$value->code.'</td><td>'.$value->category.'</td><td>'.$value->product_name.'</td>
                    <td>'.$value->quantity.'</td><td>$'.number_format($value->price,2).'</td></tr>';
            }            
        }

		$total_quantity = $orderDt[0]->ttl_qty + $original_order_detail[0]->ttl_qty;
        $total_price    = $orderDt[0]->ttl_prc + $original_order_detail[0]->ttl_prc;
		
        // $findCart       = Order::where('order_id', '=',  $original_order_detail[0]->order_id)
                        // ->update(['quantity' => $total_quantity, 'price' => $total_price, 'last_updated_by' => Auth::user()->id]);

		$findTotalVals = DB::table('order_items')->where('order_id', '=', $original_order_detail[0]->order_id)
			->get( array(
			DB::raw( 'SUM(price) AS price' ),
			DB::raw( 'SUM(quantity) AS quantity' )
		 ));
		
		Order::where('order_id', '=', $original_order_detail[0]->order_id)
		  ->update([
			'quantity' => $findTotalVals[0]->quantity, 
			'price' => $findTotalVals[0]->price,
			'last_updated_by' => Auth::user()->id
		]);
			
        $html .= '<tr><td colspan="3" align="right">Total</td><td>'.($total_quantity).'</td><td>$'.number_format($total_price, 2).'</td></tr>
                  </table><table width="100%" border="0" cellspacing="1" cellpadding="2";>
						<tr><td colspan="5"></td></tr>
					</table>
					<table width="100%" border="1" cellspacing="1" cellpadding="2"  margin-top:20px;>
						<tr><td width="30%" colspan="3" align="center">COMMENT</td><td width="70%">'.nl2br($request->get('comments')).'</td></tr>
					</table>';

        PDF::SetTitle('');
        PDF::AddPage();
        PDF::SetFont('helvetica', '', 10);
        PDF::writeHTML($html, true, false, true, false, '');
        PDF::Output(public_path().'/order_pdf/'.$order_id.'.pdf', 'F');

        $email['email']     = $orderDt[0]->email;
        $email['order_id']  = $order_id;
        $email['vendor_name']  = $orderDt[0]->name;
        $email['location']  = $orderDt[0]->location_code.' '.$orderDt[0]->location;

        Mail::send('emails.reorder', $email, function($message) use ($email) {
            $message->from('bvago@kvmgmt.com', 'Zips Supply Order');
            $message->to($email['email']);
            $message->subject(''.$email['order_id'].'');
            $message->attach(public_path().'/order_pdf/'.$email['order_id'].'.pdf');
        });

        Session::forget($svCartId);
		DB::table('cart_items')->where('cart_id', $cart_id)->delete();
        DB::table('carts')->delete($cart_id);
		return redirect()->route('order.index')->with('message','Order resend successfully!');
    }
	
	public function showCategory(Request $request)
    {
		$vendorID = $request->vendorID;
		Session::put('vendor_id', $vendorID);
		
		$location  = Session::get('location_id');
		$svCartId  = "loc-".$location.".".$vendorID.".cart_id";
		
		$cart['cart_count'] = '';
		if(Session::has($svCartId)) {
			$cart_id = Session::get($svCartId);
			$cart = $this->total_cart_items($cart_id);
		} 
		
		$vendor = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('categories.id','categories.category')
			->where('categories.status', '=', 1)
            ->where('products.vendor', '=', $vendorID)
			->orderBy('categories.rank')
			->groupby('categories.category')
			->get();
	
		$html = '';
		$html .= '<select class="form-control" id="categoryid" name="category">';
		$html .= '<option value="0">- Select Category -</option>';
		foreach($vendor as $venKey => $venVal){
			 $html .= '<option value="'.$venVal->id.'">'.$venVal->category.'</option>';
		}
		$html .= '</select>';
		
		$data = array();
		$data['categories'] = $html;
		$data['cart_count'] = $cart['cart_count'];
		return json_encode($data);
    }
}
