<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $primaryKey = 'product_id';

    public $incrementing=false;
    public $timestamps = false;
    protected $table = 'products';
    public function product_images()
    {
        return $this->hasMany('App\Product_image','product_id');

    }
}
