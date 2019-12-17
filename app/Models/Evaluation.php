<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    //
    protected $fillable = [
        "publisher", "tag", "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name", "nickname"
    ];

    public function like($action)
    {
        if ($action) {
            $this->like = $this->like + 1;
        }
    }

    public function info()
    {
        $publisher_name = User::query()->find($this->publisher)->nickname;

        // 未登录使用默认值
        $is_like = -1;
        $is_collection = 0;
        if (session("login")) {
            $is_like = Like::query()->where(["user"=> session("uid"), "evaluation" => $this->id])->first();
            $is_like = $is_like ? $is_like->like : -1;
            $is_collection = key_exists($this->id, json_decode(User::query()->find(session("uid"))->collection, true));
        }

        return [
            "id" => $this->id,
            "publisher" => $this->publisher,
            "publisher_name" => $publisher_name,
            "tag" => $this->tag,
            "views" => $this->views,
            "collections" => $this->collections,
            "like" => $this->like,
            "unlike" => $this->unlike,
            "img" => $this->img,
            "title" => $this->title,
            "content" => $this->content,
            "location" => $this->location,
            "shop_name" => $this->shop_name,
            "is_like" => $is_like,
            "is_collection" => $is_collection,
            "time" => date_format($this->created_at, "Y-m-d h:i:s")
        ];
    }
}
