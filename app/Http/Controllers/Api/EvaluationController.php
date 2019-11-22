<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Evaluation;

/**
 * @api {post} /api/evaluation 发布评测
 * @apiGroup 评测
 * @apiVersion 1.0.0
 *
 * @apiDescription 发布评测
 *
 * @apiParam {String} title      评测标题 长度50
 * @apiParam {String} content    评测内容 长度400
 * @apiParam {String} location   地点（联建等 长度20
 * @apiParam {String} shop_name  店名 长度20
 * @apiParam {Json}   tag        类似["不辣","汤好喝"]
 * @apiParam {Json}   img        图片数组，内为图片url（上传图片时返回）
 *
 * @apiSuccess {Number} code    状态码，0：请求成功
 * @apiSuccess {String} message   提示信息
 * @apiSuccess {Object} data    后端参考信息，前端无关
 *
 * @apiSuccessExample {json} Success-Response:
 * {"code":0,"status":"成功","data":35}
 *
 *
 */

class EvaluationController extends Controller
{
    //
    public function publish(Request $request) {
        $mod = [
            "img"       => ["json"],
            "title"     => ["string", "max:50"],
            "content"   => ["string", "max:400"],
            "location"  => ["string", "max:20"],
            "shop_name" => ["string", "max:20"],
            "tag"       => ["json"]
        ];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        $data = $data + ["collections" => 0, "like" => 0, "unlike" => 0, "views" => 0, "publisher" => session("uid")];
        $evaluation = new Evaluation($data);
        $evaluation = $evaluation->save();
        if($evaluation) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }
}
