<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Vendor;
use App\Product;
use App\Order;
use App\Order_item;
use App\Http\Requests;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    protected $category;
    protected $vendor;

    public function __construct()
    {
        $this->middleware('auth');
        $this->category = Category::pluck('category', 'id');
        $this->vendor = Vendor::pluck('name', 'id');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = \DB::table('orders')
            ->leftJoin('users', 'orders.order_by', '=', 'users.id')
            ->leftJoin('vendors', 'orders.vendor', '=', 'vendors.id')
            ->select('orders.*', 'users.name', 'vendors.name as vname')
            ->get();
        return view('orders.index', compact('orders'));
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
        $vendor = $this->vendor;
        return view('orders.create',compact('category','products','vendor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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


    public function showVendor(Request $request)
    {
        $vendorId = $request->vendorId;
        $vendor = Vendor::where('id', '=', $vendorId)->first();

        return view('orders.vendor',compact('vendor'));
    }
}
