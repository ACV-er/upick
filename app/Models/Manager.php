<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Manager extends Model
{
    //
    protected $fillable = [
        'nickname', 'stu_id', 'password', "level"
    ];
    public function info()
    {
        $level = [
            "0" => "超级管理员",
            "1" => "普通管理员"
        ];

        return [
            'id'     => $this->id,
            'nickname' => $this->nickname,
            'stu_id' => $this->stu_id,
            'level' => $level[$this->level]
        ];
    }

}
