<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    //
    protected $fillable = [
        "publisher", "tag", "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name"
    ];

    public function like($action) {

        if($action) {
            $this->like = $this->like + 1;
        }
    }

    public function info() {
        $publisher_name = User::query()->find($this->publisher)->nickname;
        return [
            "id"             => $this->id,
            "publisher"      => $this->publisher,
            "publisher_name" => $publisher_name,
            "tag"            => $this->tag,
            "views"          => $this->views,
            "collections"    => $this->collections,
            "like"           => $this->like,
            "unlike"         => $this->unlike,
            "img"            => $this->img,
            "title"          => $this->title,
            "content"        => $this->content,
            "location"       => $this->location,
            "shop_name"      => $this->shop_name,
        ];
    }
}
