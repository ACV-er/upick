<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'stu_id', 'password', 'collection', 'publish', "remember"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function info() {
        return [
            'id' => $this->id,
            'nickname'=> $this->nickname,
            'stu_id'=> $this->stu_id,
            'collection' => $this->collection,
            'publish' => $this->publish,
            'remember' => $this->remember
        ];
    }

    public function add_publish($evaluation_id) {
        $publish_list = json_decode($this->publish, true);
        if(!key_exists($evaluation_id, $publish_list)) {
            $publish_list[$evaluation_id] = 1;
        }
        $this->publish = json_encode($publish_list);
        $this->save();
    }

    public function del_publish($evaluation_id) {
        $publish_list = json_decode($this->publish, true);
        if(key_exists($evaluation_id, $publish_list)) {
            unset($publish_list[$evaluation_id]);
        }
        $this->publish = json_encode($publish_list);
        $this->save();
    }
}
