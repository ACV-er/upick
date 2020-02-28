<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Exception;
use PhpMyAdmin\File;
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
            if (config("app.debug")) {
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
        $allow_ext = ['jpg', 'jpeg', 'png', 'gif'];

        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, $allow_ext)) {
            return msg(3, "非法文件");
        }
        $name = md5(session('uid') . time() . rand(1, 500));
        $all_name = $name . "." . $ext;
        $result = $file->move(storage_path('app/public/image/'), $all_name);
        if (!$result) {
            return msg(500, "图片保存失败" . __LINE__);
        }
        $pic_url = config("app.url") . "/storage/image/" . $all_name;
        $redis->hSet('food_image', $pic_url, time()); // 存储图片上传时间 外部辅助脚本过期后删除
        return msg(0, $pic_url);
    }


    /**
     * /api/image 每天将未使用的图片删除
     */
    public function delete(Request $request) {
        $Storage_files = [];
        $redis_files = [];


        $files = Storage::allFiles();   //遍历存储文件
        if (!$files){
            return msg(5,"文件仓库为空".__LINE__);
        }
        foreach ($files as $file){           //遍历结果去掉前缀
            $test = stripos($file,"jpg");
            if ($test){
                $Storage_replace = str_replace("public/image/","",$file);
                $Storage_files[] = $Storage_replace;
            }
        }

        try {                          //遍历redis
            $redis = new Redis();
            $redis->connect('image_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $files = $redis->hkeys("food_image");
        foreach ($files as $file){           //遍历结果去掉前缀
            $redis_replace = str_replace("https://test.upick.com/storage/image/","",$file);
            $redis_files[] = $redis_replace;
        }
        print_r($redis_files);

        //删除文件
        $intersection = array_diff($Storage_files,$redis_files); //找出存储但未使用的文件
        $disk = Storage::disk('img');
        foreach ($intersection as $file){   //遍历删除
            $disk->delete($file);
        }

        return msg(0,__LINE__);
    }


}
