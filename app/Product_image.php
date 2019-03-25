<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_image extends Model
{

    public $incrementing=true;
    public $timestamps = false;
    protected $fillable = ['image_address', 'image_name'];
    protected $table = 'product_images';
}
