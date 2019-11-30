<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    //$table->bigIncrements('publisher');
    //            $table->string("publisher_name",50);
    protected $fillable = [
        "food_name", "img", "location", "publisher"
    ];

}
