<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    //

    /**
     * @param Request $request
     * @return string
     * @api {post} /api/mannager/login 登陆
     * @apiGroup 管理员
     * @apiVersion 1.0.0
     *
     * @apiDescription 管理员登陆，必须有帐号密码
     *
     * @apiParam {String} stu_id     学号
     * @apiParam {String} password   教务密码
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} nickname  用户名字，真名
     * @apiSuccess {String} stu_id    用户学号
     * @apiSuccess {Json} level        管理员等级
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":{
     *       "id":1,
     *       "nickname":"丁浩东",
     *       "stu_id":"201705550820",
     *       "level":"1",
     *  }
     * }
     */
    public function login(Request $request)
    {
        session(['admin_login' => false, 'admin_id' => null]);

        $mod = array(
            'stu_id' => ['regex:/^20[\d]{8,10}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        $data = $request->only(array_keys($mod));

        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        // 是否成功
        $login = true;
        if ($login) {
            $user = User::query()->where('stu_id', $data['stu_id'])->first();
            session(['ManagerLogin' => true, 'uid' => $user->id]);
            return msg(0, "管理员数据");

        } else {
            return msg(2, "" . __LINE__);
        }
    }

    /**
     * @api {post} /api/evaluation/:id 编辑管理员信息
     * @apiGroup 编辑
     * @apiVersion 1.0.0
     *
     * @apiDescription 编辑管理员信息，所有内容皆不为空
     *
     * @apiParam {String} stu_id     学号
     * @apiParam {String} password   教务密码
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} nickname  用户名字，真名
     * @apiSuccess {String} stu_id    用户学号
     * @apiSuccess {Json} level        管理员等级
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":{
     *       "id":1,
     *       "nickname":"丁浩东",
     *       "stu_id":"201705550820",
     *       "level":"1",
     *  }
     * }
     *
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {

        $mod = array(
            'nickname' => ['regex:/^[^\s]{1,10}$/'],
            'stu_id' => ['regex:/^20[\d]{8,10}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }

        $evaluation = Manager::query()->find($request->route('id'));

        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }











    private function data_handle(Request $request=null) {
        $mod = [
            "nickname"     => ["string", "max:15"],
            "stu_id"   => ["string", "max:12"],
            "password"  => ["string", "max:20"],
            "level" => ["string", "max:2"]
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
