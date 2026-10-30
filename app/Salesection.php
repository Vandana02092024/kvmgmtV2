<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Salesection extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nameId','nameDep','dataDate','unit','amount','dataDay','weekNo','storeNo','location_id','comment',
        'ez_drop_signups','new_customers'
    ];

    protected $casts = ['ez_drop_signups' => 'integer', 'new_customers' => 'integer'];

   
}
