<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodChannel;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodChannelController extends Controller
{
    //
    /**
     * @api {post} /api/foodchannel 发布美食专栏信息
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiDescription 发布美食专栏信息，管理员登陆可操作。默认状态为非置顶
     *
     * @apiParam {String} title  美食专栏标题 长度30
     * @apiParam {String} url    目标文章链接  度50
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
        $data = $data + ["top" => 0,
                "editor" => Manager::query()->find(session("mid"))->nickname];
        $foodchannel = new FoodChannel($data);

        if ($foodchannel->save()) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {put} /api/foodchannel/:id 更新美食专栏信息
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} id         需要更新的美食专栏对应的id
     *
     * @apiParam {String} title  美食专栏标题 长度30
     * @apiParam {String} url    目标文章链接  度50
     *
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

        $data = $data + ["editor" => Manager::query()->find(session("mid"))->nickname];
        $foodchannel = FoodChannel::query()->find($request->route('id'));
        if(!$foodchannel) {
            return msg(3, "目标不存在" . __LINE__);
        }

        if ($foodchannel->update($data)) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {delete} /api/foodchannel/:id 删除美食专栏信息
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiDescription 删除美食专栏信息，管理员登陆可操作
     *
     * @apiParam {Number}   id   美食专栏信息id
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
        $foodchannel = FoodChannel::query()->find($request->route('id'));
        if(!$foodchannel) {
            return msg(3, "目标不存在" . __LINE__);
        }

        $foodchannel->delete();

        return msg(0, __LINE__);
    }



    /**
     * @api {get} /api/foodchannel/list/:page 获取美食专栏信息列表,每页10条
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取美食专栏信息列表，管理员登陆可操作
     *
     * @apiParam {Number} page      页码数，从1开始
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     * @apiSuccess {Number} id              该条美食专栏对应id
     * @apiSuccess {Number} top             是否置顶
     * @apiSuccess {String} foodchannel_info   美食专栏信息
     * @apiSuccess {String} time            美食专栏发布时间
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *   "code": 0,
     *   "status": "成功",
     *   "data": [
     *         {
     *             "total": 2,
     *             "list": [
     *                 [
     *                     {
     *                         "id": 1,
     *                         "editor": "张桂福",
     *                         "title": "我爱联建小蛋糕",
     *                         "url": "www.sky31.com/xxx",
     *                         "top": 0,
     *                         "updated_at": "2020-01-29 03:13:04"
     *                     },
     *                     {
     *                         "id": 2,
     *                         "editor": "张桂福",
     *                         "title": "我爱联建小蛋糕",
     *                         "url": "www.sky31.com",
     *                         "top": 0,
     *                         "updated_at": "2020-01-29 02:48:50"
     *                     }
     *                 ]
     *             ]
     *         }
     *     ]
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get_list(Request $request)
    {
        $offset = $request->route("page") * 10 - 10;
        $limit = 10;
        $foodchannel_list = [];

        if($request->route("page") == 1) { //第一页先带上置顶
            $top = FoodChannel::query()->where("top", "=", "1")->first(["id","editor" , "title", "url", "top", "updated_at"]);
            if($top) { //如果存在置顶 则加入第一条
                $top = $top->toArray();
                $foodchannel_list[] = $top;
                $limit = 9; // 如果已经有一条置顶在列表中，则下面只获取9条数据即可
            }
        }

        $foodchannel_list[] = FoodChannel::query()->limit($limit)->offset($offset)->where("top", "=", "0")
                                                                    ->orderByDesc("updated_at")
                                                                    ->get(["id","editor" , "title", "url", "top", "updated_at"])
                                                                    ->toArray();
        $list_count = FoodChannel::query()->count();
        $message[] = [
            'total'=>$list_count,
            'list'=>$foodchannel_list
        ];
        return msg(0, $message);
    }

    /**
     * @api {put} /api/foodchannel/top/:id  美食专栏置顶
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiDescription 使对应id美食专栏置顶，即被展示。并取消其他置顶。管理员登陆可操作
     *
     * @apiParam {Number} id      该条美食专栏对应id
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
        $old = FoodChannel::query()->where("top", "1")->first();

        $foodchannel = FoodChannel::query()->find($request->route("id"));
        if (!$foodchannel) {
            return msg(3, "目标不存在" . __LINE__);
        }

        if($old) {
            $old->update(["top" => 0]);
        }

        if ($foodchannel->update(["top" => 1])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {put} /api/foodchannel/untop/:id  美食专栏取消置顶
     * @apiGroup 美食专栏
     * @apiVersion 1.0.0
     *
     * @apiDescription 使对应id美食专栏取消其他置顶。管理员登陆可操作
     *
     * @apiParam {Number} id      该条美食专栏对应id
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
        $foodchannel = FoodChannel::query()->find($request->route("id"));
        if (!$foodchannel) {
            return msg(3, "目标不存在" . __LINE__);
        }

        if ($foodchannel->update(["top" => 0])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /** 检查，成功返回data数组
     * @param Request|null $request
     * @return array|string
     */
    private function data_handle(Request $request = null)
    {
        $mod = [
            "title" => ["string", "max:30"],
            "url"   => ["string", "max:50"]
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
