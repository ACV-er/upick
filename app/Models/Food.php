<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    //$table->bigIncrements('publisher');
    //            $table->string("publisher_name",50);
    protected $fillable = [
        "food_name", "img", "created_at", "updated_at","location","publisher","publisher_name"
    ];



}
