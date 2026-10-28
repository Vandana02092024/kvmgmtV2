<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\LocationRequest;

class Location extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    
    //
}
