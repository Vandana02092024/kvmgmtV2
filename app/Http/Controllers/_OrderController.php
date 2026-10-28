<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\OrderRequest;
use App\Category;
use App\Seller;
use App\Product;
use App\Order;
use App\Order_item;
use App\User;
use PDF;
use Mail;

class OrderController extends Controller
{
    protected $category;
    protected $seller;

    public function __construct()
    {
        $this->middleware('auth');
        $this->category = Category::pluck('category', 'id');
        $this->seller = Seller::pluck('name', 'id');
        $this->users = User::pluck('name', 'id');
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
            ->where('orders.order_by', '=', Auth::user()->id)
            ->get();

        }else{
            $orders = DB::table('orders')
            ->leftJoin('users', 'orders.order_by', '=', 'users.id')
            ->leftJoin('sellers', 'orders.vendor', '=', 'sellers.id')
            ->select('orders.*', 'users.name', 'sellers.name as vname')
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
        $category = $this->category;
        $products = \DB::table('products')
            ->leftJoin('categories', 'products.category', '=', 'categories.id')
            ->select('products.code', 'products.id', 'products.name', 'products.unit', 'products.price', 'categories.category')
            ->get();
        $seller = $this->seller;
        return view('orders.create',compact('category','products','seller'));
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
            $message->attach(public_path().'\order_pdf\PDF_'.$email['order_id'].'.pdf');
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
    public function destroy($id)
    {
        //
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

        if($product_name != '')
        {
            $orders->where('products.name', 'like', '%' . $product_name . '%');
        }

        if($product_code != '')
        {
            $orders->where('products.code', 'like', '%' . $product_code . '%');
        }

        if(($request->get('order_from') != '') && ($request->get('order_to') != ''))
        {
            $orders->where('orders.order_date', '>=', $request->get('order_from'))
            ->where('orders.order_date', '<=', $request->get('order_to'));
        }

        if (Auth::user()->role != 'Owner')
            $orders->where('orders.order_by', '=', Auth::user()->id);

        $orders = $orders->select('orders.*', 'users.name', 'sellers.name as vname')
                ->groupBy('orders.order_id')->get();

        // $request->flash();
        return view('orders.index', compact('orders', 'category', 'seller', 'user'));
    }
}
