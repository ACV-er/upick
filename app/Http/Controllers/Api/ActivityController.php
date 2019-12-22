<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    //
    /**
     * @api {post} /api/activity 发布商家活动信息
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 发布商家活动信息，管理员登陆可操作。默认状态为非置顶
     *
     * @apiParam {String} activity_info  商家活动名称 长度30
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Object} data      后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":49}
     *
     */
    /**
     * @param Request $request
     * @return array|string
     */
    public function publish(Request $request)
    {
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data = $data + ["top" => 0];
        $activity = new Activity($data);

        if ($activity->save()) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {put} /api/activity/:id 更新商家活动信息
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 更新商家活动信息，管理员登陆可操作
     *
     * @apiParam {Number} id         需要更新的商家活动对应的id
     * @apiParam {String} activity_info  商家活动名称 长度30
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Object} data      后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":89}
     *
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }

        $activity = Activity::query()->find($request->route('id'));
        if(!$activity) {
            return msg(3, "目标不存在" . __LINE__);
        }

        $activity = $activity->update($data);
        if ($activity) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {delete} /api/activity/:id 删除商家活动信息
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 删除商家活动信息，管理员登陆可操作
     *
     * @apiParam {Number}   id   商家活动信息id
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
    public function delete(Request $request)
    {
        $activity = Activity::query()->find($request->route('id'));
        if(!$activity) {
            return msg(3, "目标不存在" . __LINE__);
        }

        $activity->delete();

        return msg(0, __LINE__);
    }



    /**
     * @api {get} /api/activity/list/:page 获取商家活动信息列表
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取商家活动信息列表，管理员登陆可操作
     *
     * @apiParam {Number} page      页码数，从1开始
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     * @apiSuccess {Number} id              该条商家活动对应id
     * @apiSuccess {Number} top             是否置顶
     * @apiSuccess {String} activity_info   商家活动信息
     * @apiSuccess {String} time            商家活动发布时间
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":[
     *      {
     *          "id":1,
     *          "activity_info":"蜜雪冰城买二送一",
     *          "top":1,
     *          "updated_at":"2019-11-30 11:03:31"
     *      },
     *      {
     *          "id":2,
     *          "activity_info":"蜜雪冰城买一送一",
     *          "top":0,
     *          "updated_at":"2019-11-30 11:03:08"
     *      }
     *  ]
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get_list(Request $request)
    {
        $offset = $request->route("page") * 7 - 7;

        $activity_list = Activity::query()->limit(7)->offset($offset)->orderByDesc("updated_at")
            ->get(["id", "activity_info", "top", "updated_at"])
            ->toArray();

        return msg(0, $activity_list);
    }

    /**
     * @api {put} /api/activity/top/:id  商家活动置顶
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 使对应id商家活动置顶，即被展示。并取消其他置顶。管理员登陆可操作
     *
     * @apiParam {Number} id      该条商家活动对应id
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":197}
     */
    /**
     * @param Request $request
     * @return string
     */
    public function top(Request $request)
    {
        $old = Activity::query()->where("top", "=", "1")->first();

        $activity = Activity::query()->find($request->route("id"));
        if (!$activity) {
            return msg(3, "目标不存在" . __LINE__);
        }

        if($old) {
            $old->update(["top" => 0]);
        }

        if ($activity->update(["top" => 1])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {put} /api/activity/untop/:id  商家活动取消置顶
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription 使对应id商家活动取消其他置顶。管理员登陆可操作
     *
     * @apiParam {Number} id      该条商家活动对应id
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":197}
     */
    /**
     * @param Request $request
     * @return string
     */
    public function untop(Request $request)
    {
        $activity = Activity::query()->find($request->route("id"));
        if (!$activity) {
            return msg(3, "目标不存在" . __LINE__);
        }


        if ($activity->update(["top" => 0])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {get} /api/activity/top  获取置顶商家活动
     * @apiGroup 商家活动
     * @apiVersion 1.0.0
     *
     * @apiDescription    获取置顶商家活动
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     * @apiSuccess {Number} id              该条活动id
     * @apiSuccess {String} activity_info   商家活动内容
     * @apiSuccess {Number} top             是否被置顶，1为置顶
     * @apiSuccess {String} updated_at      最后更新时间
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":{
     *      "id":1,
     *      "activity_info":"蜜雪冰城买二送一",
     *      "top":1,
     *      "updated_at":"2019-11-30 11:03:31"
     *  }
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get_top(Request $request)
    {
        $activity = Activity::query()->where("top", "=", "1")->first(["id", "activity_info", "top", "updated_at"]);
        if (!$activity) {
            return msg(3, "目标不存在" . __LINE__);
        }

        return msg(0, $activity);
    }

    /** 检查，成功返回data数组
     * @param Request|null $request
     * @return array|string
     */
    private function data_handle(Request $request = null)
    {
        $mod = [
            "activity_info" => ["string", "max:30"],
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
