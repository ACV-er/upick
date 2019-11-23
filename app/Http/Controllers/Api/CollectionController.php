<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    //
    /**
     * @api {post} /api/like/:id     收藏/取消收藏评测
     * @apiGroup 用户
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户赞踩评测，可取消，变换赞踩
     *
     * @apiParam {Number}  id        评测id
     * @apiParam {Number}  action    keep收藏 unkeep取消收藏
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
    public function keep(Request $request) {
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

        if($request->input("action") == "keep") {
            if($user->add_collection($evaluation_id)) {
                Evaluation::query()->find($evaluation_id)->increment("collections");
            } else {
                return msg(3, __LINE__);
            }
        } else {
            if($user->del_collection($evaluation_id)) {
                Evaluation::query()->find($evaluation_id)->decrement("collections");
            } else {
                return msg(3, __LINE__);
            }
        }

        return msg(0, __LINE__);
    }
}
