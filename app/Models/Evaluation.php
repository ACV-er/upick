<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    //
    protected $fillable = [
        "publisher", "tag", "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name"
    ];
}
