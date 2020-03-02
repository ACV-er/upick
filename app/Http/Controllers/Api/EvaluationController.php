<?php

namespace App\Http\Controllers\Api;

use \Redis;
use App\Http\Controllers\Controller;
use App\Lib\WeChat;
use App\Models\Evaluation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    //

    /**
     * @api {post}
     * 发布评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 发布评测，登陆后可操作
     *
     * @apiParam {String} title      评测标题 长度50
     * @apiParam {String} content    评测内容 长度400
     * @apiParam {String} location   地点（联建等 长度20
     * @apiParam {String} shop_name  店名 长度20
     * @apiParam {String} nickname   昵称 长度10
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
    public function publish(Request $request)
    {
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data = $data + ["top" => 0, "collections" => 0, "like" => 0, "unlike" => 0, "views" => 0, "publisher" => session("uid")];
        $evaluation = new Evaluation($data);

        $imgs = json_decode($data['img']);
        try {
            $redis = new Redis();
            $redis->connect('image_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        foreach ($imgs as $i) {
            $redis->hDel('food_image', $i);
        }

        if ($evaluation->save()) {
            // 将该评测加入我的发布
            User::query()->find(session("uid"))->add_publish($evaluation->id);

            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }


    /**
     * @api {put} /api/evaluation/:id 更新评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 更新评测，登陆后可操作,用户只能编辑自己的评测 管理员可编辑任何评测
     *
     * @apiParam {Number} id         需要更新的测评对应的id
     * @apiParam {String} title      评测标题 长度50
     * @apiParam {String} content    评测内容 长度400
     * @apiParam {String} location   地点（联建等 长度20
     * @apiParam {String} shop_name  店名 长度20
     * @apiParam {String} nickname   昵称 长度10
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
    public function update(Request $request)
    {
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $evaluation = Evaluation::query()->find($request->route('id'));
        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }


    /**
     * @api {get} /api/evaluation/:id 获取评测详细信息
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取单篇评测详细信息， 会计算浏览量。返回参数与发布更新同名请求参数意义一致，不同名参数已写出
     *
     * @apiParam {Number} id      评测id
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiSuccess {String} publisher       发布人标识
     * @apiSuccess {String} publisher_name  发布人姓名
     * @apiSuccess {Number} views           浏览量
     * @apiSuccess {Number} like            赞数
     * @apiSuccess {Number} unlike          踩数
     * @apiSuccess {Number} collections     收藏量
     * @apiSuccess {Number} is_like         是否赞踩 -1无 0踩 1赞
     * @apiSuccess {Number} is_collection   是否收藏 0否 1是
     * @apiSuccess {String} time            首次发布时间
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
     *         "shop_name":"黃焖鸡米饭",
     *         "is_like":-1,
     *         "is_collection":0,
     *         "time": "2019-11-23 05:25:09"
     *     }
     * }
     */
    /**
     * @param Request $request
     * @return string
     */
    public function get(Request $request)
    {
        $evaluation = Evaluation::query()->find($request->route('id'));
        if (
            !session()->has("mark" . $request->route('id'))
            || session("mark" . $request->route('id')) + 1800 < time()
        ) {
            $evaluation->increment("views");
            session(["mark" => time()]);
        }

        return msg(0, $evaluation->info());
    }

    /**
     * @api {delete} /api/evaluation/:id 删除评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户删除评测，登陆后可操作。管理员可删除任何评测
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
    public function delete(Request $request)
    {
        $evaluation = Evaluation::query()->find($request->route('id'));

        // 将该评测从我的发布中删除
        User::query()->find($evaluation->publisher)->del_publish($evaluation->id);
        $evaluation->delete();

        return msg(0, __LINE__);
    }


    /**
     * @api {get} /api/evaluation/list/:page 获取评测列表
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取评测列表
     *
     * @apiParam {Number} page      页码数，从1开始
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     *
     * @apiHeaderExample {json} Headers:
     * {
     *       "Cookie":"laravel_session=xxxxxxx"
     * }
     * @apiSuccess {String} publisher       发布人标识
     * @apiSuccess {String} publisher_name  发布人姓名
     * @apiSuccess {Number} views           浏览量
     * @apiSuccess {Number} like            赞数
     * @apiSuccess {Number} unlike          踩数
     * @apiSuccess {Number} collections     收藏量
     * @apiSuccess {Number} is_like         是否赞踩 -1无 0踩 1赞
     * @apiSuccess {Number} is_collection   是否收藏 0否 1是
     * @apiSuccess {String} time            首次发布时间
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *    "code": 0,
     *    "status": "成功",
     *    "data": {
     *          "total": 2,
     *          "list": [
     *              {
     *                  "id": 6,
     *                  "publisher_name": "我爱小蛋糕",
     *                  "tag": "[\"不辣\", \"汤好喝\"]",
     *                  "views": 0,
     *                  "collections": 0,
     *                  "img": "不知道是啥",
     *                  "title": "我爱联建小蛋糕",
     *                  "location": "联建",
     *                  "shop_name": "天香林90",
     *                  "time": "2020-02-02 13:48:33"
     *                  },
     *              {
     *                  "id": 1,
     *                  "publisher_name": "我爱小蛋糕",
     *                  "tag": "[\"不辣\", \"汤好喝\"]",
     *                  "views": 0,
     *                  "collections": 0,
     *                  "img": "不知道是啥",
     *                  "title": "我爱联建小蛋糕",
     *                  "location": "联建",
     *                  "shop_name": "树香林",
     *                  "time": "2020-02-02 13:28:59"
     *              }
     *          ]
     *       }
     * }
     */
    public function get_list(Request $request)
    {
        $offset = $request->route("page") * 10 - 10;

        $evaluation_list = Evaluation::query()->limit(10)->offset($offset)->orderByDesc("created_at")
            ->get([
                "id", "nickname as publisher_name", "tag", "views",
                "collections", "img", "title", "location", "shop_name", "created_at as time"
            ])
            ->toArray();
        if ($request->route("page") == 1) {
            $evaluation_list = array_merge($this->get_orderBy_score_list(), $evaluation_list);
        }
        $list_count = Evaluation::query()->count();
        $message = ['total'=>$list_count,'list'=>$evaluation_list];
        return msg(0, $message);
    }



    /**
     * @api {put} /api/evaluation/top/:id  评测置顶
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 使对应id评测置顶，即被展示。并取消其他置顶。管理员登陆可操作
     *
     * @apiParam {Number} id      该条评测对应id
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
        $old = Evaluation::query()->where("top", "=", "1")->first();
        if ($old) {
            $old->update(["score" => 0]);
        }

        $evaluation = Evaluation::query()->find($request->route("id"));
        if (!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        }

        if ($evaluation->update(["top" => 1])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }




    /**
     * @api {get} /api/evaluation/:id/share_code 获取评测页二维码
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 获取评测页二维码
     *
     * @apiParam {Number} id      页码数，从1开始
     *
     * @apiSuccess {Number} code            状态码，0：请求成功
     * @apiSuccess {String} message         提示信息
     * @apiSuccess {Object} data            返回参数
     * @apiSuccess {String} code_url        二维码链接
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code": 0,
     *  "status": "成功",
     *  "data": {
     *      "code_url": "http://upick.myweb.com/storage/image/4.png"
     *  }
     * }
     */
    public function get_share_code(Request $request)
    {
        if (file_exists(storage_path('app/public/image/') . $request->route("id") . ".png")) {
            return msg(0, ["code_url" => config("app.url") . "/storage/image/" . $request->route("id") . ".png"]);
        } else {
            try {
                $wechat = WeChat::getWeChat();
                return $wechat->get_page_QRcode(config("sky31.appid"), config("sky31.secret"), $request->route("id"));
            } catch (\Exception $e) {
                $message = [];
                preg_match("/(^[.]{20})/", $e->getMessage(), $message);
                return msg(4, (isset($message[1]) ? $message[1] : "未知") . __LINE__);
            }
        }
    }

    private function get_orderBy_score_list()
    {
        $list = Evaluation::query()->limit(20)->orderByDesc("score")
            ->where("top", "=", "0")
            ->get([
                "id", "nickname as publisher_name", "tag", "views",
                "collections", "top", "img", "title", "location", "shop_name", "created_at as time"
            ])
            ->toArray();

        $new_list = [];
        $begin = rand(0, 20);
        for ($i = 0; $i < 3; $i += 1) {
            $new_list[] = $list[($begin + $i * 6) % count($list)];
        }

        return $new_list;
    }

    /** 评测检查，成功返回data数组
     * @param Request|null $request
     * @return array|string
     */
    private function data_handle(Request $request = null)
    {
        $mod = [
            "img" => ["json"],
            "title" => ["string", "max:50"],
            "content" => ["string", "max:400"],
            "location" => ["string", "max:20"],
            //            "shop_name" => ["string", "max:20"],
            "tag" => ["json"],
            "nickname" => ["string", "max:10"]
        ];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if ((empty($data["nickname"]) || $data["nickname"] === "")) {
            if ($request->routeIs("evaluation_update")) {
                $uid = Evaluation::query()->find($request->route('id'))->publisher;
            } else {
                $uid = session("uid");
            }
            $data["nickname"] = User::query()->find($uid)->nickname;
        }
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        if (empty($data["shop_name"]) || $data["shop_name"] === ""){
                $data["shop_name"] = NULL;
        }
        return $data;
    }
}
