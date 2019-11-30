<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodChannel extends Model
{
    //
    protected $fillable = [
        "editor", "title", "url", "top"
    ];
}
