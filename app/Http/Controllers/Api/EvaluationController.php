<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    //
    public function publish() {
        $publisher = session("uid");
        $mod = [
            "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name"
        ];
    }
}
