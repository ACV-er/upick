<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Evaluation;

class EvaluationController extends Controller
{
    //

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
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Object} data      后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":35}
     *
     *
     */
    /**
     * @param Request $request
     * @return array|string
     */
    public function publish(Request $request) {
        $data = $this->data_handle($request);
        if(!is_array($data)) {
            return $data;
        }

        $data = $data + ["collections" => 0, "like" => 0, "unlike" => 0, "views" => 0, "publisher" => session("uid")];
        $evaluation = new Evaluation($data);

        if($evaluation->save()) {
            // 将该评测加入我的发布
            User::query()->find(session("uid"))->add_publish($evaluation->id);

            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {post} /api/evaluation/:id 更新评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 更新评测
     *
     * @apiParam {Number} id         需要更新的测评对应的id
     * @apiParam {String} title      评测标题 长度50
     * @apiParam {String} content    评测内容 长度400
     * @apiParam {String} location   地点（联建等 长度20
     * @apiParam {String} shop_name  店名 长度20
     * @apiParam {Json}   tag        类似["不辣","汤好喝"]
     * @apiParam {Json}   img        图片数组，内为图片url（上传图片时返回）
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Object} data      后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":35}
     *
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function update(Request $request) {
        $data = $this->data_handle($request);
        if(!is_array($data)) {
            return $data;
        }
        $evaluation = Evaluation::query()->find($request->route('id'));
        if(!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        }

        $evaluation = $evaluation->update($data);
        if($evaluation) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }


    /**
     * @api {get} /api/evaluation/:id 获取评测详细信息
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取单篇评测详细信息，返回参数与发布更新同名请求参数意义一致，不同名参数已写出
     *
     * @apiParam {String} title      评测id
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     * @apiSuccess {Number} like            赞数
     * @apiSuccess {Number} unlike          踩数
     * @apiSuccess {Number} views           浏览量
     * @apiSuccess {Number} collections     收藏量
     * @apiSuccess {String} publisher       发布人标识
     * @apiSuccess {String} publisher_name  发布人姓名
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     "code":0,
     *     "status":"成功",
     *     "data":{
     *         "id":1,
     *         "publisher":1,
     *         "publisher_name":"丁浩东",
     *         "tag":"["不辣", "汤好喝"]",
     *         "views":0,
     *         "collections":0,
     *         "like":0,
     *         "unlike":0,
     *         "img":"[]",
     *         "title":"文章标题测试",
     *         "content":"这是文章内容(更新)",
     *         "location":"联建",
     *         "shop_name":"黃焖鸡米饭"
     *     }
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get(Request $request) {
        $evaluation = Evaluation::query()->find($request->route('id'));
        if(!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        }

        return msg(0, $evaluation->info());
    }

    /**
     * @api {delete} /api/evaluation/:id 用户删除评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户删除评测
     *
     * @apiParam {Number}   id   评测id
     *
     * @apiSuccess {Number} code     状态码，0：请求成功
     * @apiSuccess {String} message  提示信息
     * @apiSuccess {Object} data     后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":197}
     */
    /**
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function delete(Request $request) {
        $evaluation = Evaluation::query()->find($request->route('id'));
        if(!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        }

        // 将该评测从我的发布中删除
        User::query()->find(session("uid"))->del_publish($evaluation->id);
        $evaluation->delete();

        return msg(0, __LINE__);
    }

     /** 评测检查，成功返回data数组
     * @param Request|null $request
     * @return array|string
     */
    private function data_handle(Request $request=null) {
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

        return $data;
    }
}
