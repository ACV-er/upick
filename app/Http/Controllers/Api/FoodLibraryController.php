<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Redis;
use Exception;

class FoodLibraryController extends Controller
{
    //

    /**
     * @api {post} /api/food 发布美食信息
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 发布美食信息，管理员登陆可操作
     *
     * @apiParam {String} food_name  美食名称 长度40
     * @apiParam {String} location   地点（联建黄焖鸡米饭等 长度50
     * @apiParam {Json}   img        图片数组，内为图片url（上传图片时返回）
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Object} data      后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":49}
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

        $data = $data + ["publisher" => session("mid")];
        $food = new Food($data);

        if($food->save()) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {put} /api/food/:id 更新美食信息
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 更新美食信息，管理员登陆可操作
     *
     * @apiParam {Number} id         需要更新的美食信息对应的id
     * @apiParam {String} food_name  美食名称 长度40
     * @apiParam {String} location   地点（联建黄焖鸡米饭等 长度50
     * @apiParam {Json}   img        图片数组，内为图片url（上传图片时返回）
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
    public function update(Request $request) {
        $data = $this->data_handle($request);
        if(!is_array($data)) {
            return $data;
        }
        $food = Food::query()->find($request->route('id'));

        $food = $food->update($data);
        if($food) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    /**
     * @api {delete} /api/food/:id 删除美食信息
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 删除美食信息，管理员登陆可操作
     *
     * @apiParam {Number}   id   美食信息id
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
        $food = Food::query()->find($request->route('id'));

        $food->delete();

        return msg(0, __LINE__);
    }


    /**
     * @api {get} /api/food/list/:page 获取美食信息列表
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取美食信息列表，管理员登陆可操作
     *
     * @apiParam {Number} page      页码数，从1开始
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccess {String} publish_name 发布人姓名
     * @apiSuccess {String} food_name    美食名称 长度40
     * @apiSuccess {String} location     地点（联建等 长度50
     * @apiSuccess {String} shop_name    店名（黄焖鸡米饭等 长度50
     * @apiSuccess {Json}   img          图片数组，内为图片url（上传图片时返回）
     * @apiSuccess {data}   time         最后更新时间
     * @apiSuccessExample {json} Success-Response:
     * {
     *  太长 不展示了
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get_list(Request $request) {
        $offset = $request->route("page") * 7 - 7;

        $food_list = Food::query()->limit(7)->offset($offset)->orderByDesc("foods.created_at")
            ->leftJoin("managers", "foods.publisher", "=", "managers.id")
            ->get(["foods.id as id", "managers.nickname as publisher_name", "food_name", "location",
                "img", "foods.updated_at as time"])
            ->toArray();

        return msg(0, $food_list);
    }

    /**
     * @api {get} /api/food 获取推荐美食信息
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户登陆后可访问
     *
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccess {String} food_name    美食名称 长度40
     * @apiSuccess {String} location     地点（联建等 长度50
     * @apiSuccess {String} shop_name    店名（黄焖鸡米饭等 长度50
     * @apiSuccess {Json}   img          图片数组，内为图片url（上传图片时返回）
     * @apiSuccess {data}   updated_at   最后更新时间
     * @apiSuccessExample {json} Success-Response:
     * {
     *  太长 不展示了
     * }
     */
    public function get() {
        try {
            $redis = new Redis();
            $redis->connect('food_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }

        $info = $redis->hGetAll(session("uid")); // 获取该用户相关信息
        if(empty($info) || $info["date"] != date("Y-m-d")) { // 新用户，或者新的一天，刷新
            $info["date"] = date("Y-m-d");
            $info["times"] = 0;
            $info["id"] = Food::query()->inRandomOrder()->first()->id;
            $redis->hMSet(session("uid"), $info);
        }

        return msg(0, Food::query()->find($info["id"]));
    }

    /**
     * @api {get} /api/food/new 获取另一条推荐美食信息
     * @apiGroup 美食库
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户登陆后可访问，每天使用三次.次数用完后返回code为9
     *
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccess {String} food_name    美食名称 长度40
     * @apiSuccess {String} location     地点（联建等 长度50
     * @apiSuccess {String} shop_name    店名（黄焖鸡米饭等 长度50
     * @apiSuccess {Json}   img          图片数组，内为图片url（上传图片时返回）
     * @apiSuccess {data}   updated_at   最后更新时间
     * @apiSuccessExample {json} Success-Response:
     * {
     *  太长 不展示了
     * }
     */
    public function fresh() {
        try {
            $redis = new Redis();
            $redis->connect('food_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }

        $info = $redis->hGetAll(session("uid")); // 获取该用户相关信息
        if(empty($info) || $info["date"] != date("Y-m-d")) { // 新用户，或者新的一天，刷新
            $info["date"] = date("Y-m-d");
            $info["times"] = 0;
            $info["id"] = Food::query()->inRandomOrder()->first()->id;
            $redis->hMSet(session("uid"), $info);
        } else {
            if($info["times"] < 3) {
                $info["times"] += 1;
                $info["id"] = Food::query()->inRandomOrder()->first()->id;
                $redis->hMSet(session("uid"), $info);
            } else {
                return msg(9, "今日无法刷新" . __LINE__);
            }
        }

        return msg(0, Food::query()->find($info["id"]));
    }

    /** 检查，成功返回data数组
     * @param Request|null $request
     * @return array|string
     */
    private function data_handle(Request $request=null) {
        $mod = [
            "img"       => ["json"],
            "location"  => ["string", "max:50"],
            "food_name" => ["string", "max:40"],
            "shop_name" => ["string", "max:20"]
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
