<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Exception;
use \Redis;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * @api {post} /api/image 图片上传
     * @apiGroup 图片
     * @apiName 图片上传
     * @apiVersion 1.0.0
     *
     * @apiDescription 上传图片，返回url。登陆后可操作
     *
     * @apiParam {File}  image  图片文件
     *
     * @apiSuccess {Number} code    状态码，0：请求成功
     * @apiSuccess {String} status   状态信息
     * @apiSuccess {String} data    图片url
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":"https://upick.acver.xyz/image/123.jpg"}
     *
     * @apiErrorExample {json} Error-Response:
     * {"code":500,"status":"上传失败"}
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function upload(Request $request) {
        if (!$request->hasFile('image')) {
            return msg(1, "缺失参数" . __LINE__);
        }
        $data = $request->only('image');
        $validator = Validator::make($data, [ // 图片文件小于10M
            'image' => 'max:10240'
        ]);
        if ($validator->fails()) {
            if (env('APP_DEBUG')) {
                return msg(1, '非法参数' . __LINE__ . $validator->errors());
            }
            return msg(1, '非法参数' . __LINE__);
        }
        // 如果redis连接失败 中止保存
        try {
            $redis = new Redis();
            $redis->connect('image_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension(); // 获取后缀
        $name = md5(session('uid') . time() . rand(1, 500));
        $all_name = $name . "." . $ext;
        $result = $file->move(storage_path('app/public/image/'), $all_name);
        if (!$result) {
            return msg(500, "图片保存失败" . __LINE__);
        }
        $pic_url = env('APP_URL') . "/storage/image/" . $all_name;
        $redis->hSet('food_image', $pic_url, time()); // 存储图片上传时间 外部辅助脚本过期后删除
        return msg(0, $pic_url);
    }

}
