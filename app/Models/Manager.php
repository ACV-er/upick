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
        return [

            'nickname' => $this->nickname,
            'stu_id' => $this->stu_id,
            'password' => $this->password,
            'level' => $this->level
        ];
    }

}
