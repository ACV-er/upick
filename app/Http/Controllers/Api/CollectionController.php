<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    //
    /**
     * @api {post} /api/keep/:id     收藏/取消收藏评测
     * @apiGroup 用户
     * @apiVersion 1.0.0
     *
     * @apiDescription 收藏/取消收藏评测
     *
     * @apiParam {Number}  id        评测id
     * @apiParam {String}  action    keep收藏 unkeep取消收藏
     *
     * @apiSuccess {Number} code     状态码，0：请求成功
     * @apiSuccess {String} message  提示信息
     * @apiSuccess {Object} data     后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":43}
     */
    /**
     * @param Request $request
     * @return string
     */
    public function keep(Request $request)
    {
        if (!$request->has('action')) {
            return msg(1, "缺失参数");
        }
        $mod = ['action' => ["regex:/^keep$|^unkeep$/"]];

        $data = $request->only(array_keys($mod));
        $validator = Validator::make($data, $mod);
        if ($validator->fails()) {
            return msg(1, '非法参数' . __LINE__);
        }

        $user = User::query()->find(session("uid"));
        $evaluation_id = $request->route("id");
        $evaluation = Evaluation::query()->find($evaluation_id);

        if ($request->input("action") == "keep") {
            if ($user->add_collection($evaluation_id)) {
                $evaluation->increment("collections");
                $evaluation->increment("score");
            } else {
                return msg(3, __LINE__);
            }
        } else {
            if ($user->del_collection($evaluation_id)) {
                $evaluation->decrement("collections");
                $evaluation->decrement("score");
            } else {
                return msg(3, __LINE__);
            }
        }

        return msg(0, __LINE__);
    }

    /**
     * @api {get} /api/user/:uid/keep     获取用户收藏列表
     * @apiGroup 用户
     * @apiVersion 1.0.0
     *
     * @apiDescription      获取用户收藏列表,需登陆。参数解释见评测详细信息同名返回参数
     *
     * @apiParam {Number}  uid       目标用户id
     *
     * @apiSuccess {Number} code     状态码，0：请求成功
     * @apiSuccess {String} message  提示信息
     * @apiSuccess {Object} data     返回信息
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":[
     *      "total":2,
     *      "list":
     *      {
     *          "id":2,
     *          "publisher_name":"丁浩东",
     *          "tag":"["不辣", "汤好喝"]",
     *          "views":0,
     *          "collections":1,
     *          "img":"[]",
     *          "title":"文章标题测试",
     *          "location":"联建",
     *          "shop_name":"黃焖鸡米饭",
     *          "time":"2019-11-23 05:07:23"
     *      },
     *      {
     *          "id":3,
     *          "publisher_name":"丁浩东",
     *          "tag":"["不辣", "汤好喝"]",
     *          "views":0,
     *          "collections":1,
     *          "img":"[]",
     *          "title":"文章标题测试",
     *          "location":"联建",
     *          "shop_name":"黃焖鸡米饭",
     *          "time":"2019-11-23 05:07:23"
     *      }
     *  ]
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get_user_collection_list(Request $request)
    {
        //
        $user_id = $request->route("uid");
        $user = User::query()->find($user_id);
        if (!$user) {
            return msg(3, "目标不存在" . __LINE__);
        }
        $collection_id_list = array_keys(json_decode($user->collection, true));

        $collection_list = DB::table("evaluations")->whereIn("evaluations.id", $collection_id_list)
            ->get(["id", "nickname as publisher_name", "tag", "views",
                "collections", "img", "title", "location", "shop_name", "created_at as time"])
            ->toArray();
        $list_count = count($collection_list);
        $message = ['total'=>$list_count,'list'=>$collection_list];
        return msg(0, $message);
    }
}
