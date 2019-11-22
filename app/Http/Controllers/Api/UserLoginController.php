<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;

class UserLoginController extends Controller
{
    //

    /**
     * @api {post} /api/login 登陆
     * @apiGroup 用户
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户登陆，分为口令和帐号密码两种，必须有帐号密码或者有口令
     *
     * @apiParam {String} [remember]  登陆记忆口令，登陆时会返回
     * @apiParam {String} [stu_id]    学号
     * @apiParam {String} [password]  教务密码
     *
     * @apiSuccess {Number} code    状态码，0：请求成功
     * @apiSuccess {String} message   提示信息
     * @apiSuccess {Number} id    用户标识
     * @apiSuccess {String} nickname    用户名字，真名
     * @apiSuccess {String} stu_id    用户学号
     * @apiSuccess {Json} collection    用户收藏（评测标识
     * @apiSuccess {Json} upload    用户发布（评测标识
     * @apiSuccess {Json} upload    用户口令（用于登陆
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":{
     *       "id":1,
     *       "nickname":"丁浩东",
     *       "stu_id":"201705550820",
     *       "collection":"[]",
     *       "upload":"[]",
     *       "remember":"3d2b790fcc4beaff6b7097d21f033f02"
     *  }
     * }
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function login(Request $request)
    {
        session(['login' => false, 'uid' => null]);
        // 带remember的请求直接通过
        if ($request->has(["remember"])) {
            $user = User::query()->where('remember', $request->input('remember'))->first();
            if ($user) {
                return msg(0, $user->info());
            }
        }

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
        $user = User::query()->where('stu_id', $data['stu_id'])->first();

        if (!$user) { // 该用户未在数据库中 用户名错误 或 用户从未登录
            //利用三翼api确定用户账号密码是否正确
            $output = checkUser($data['stu_id'], $data['password']);
            if ($output['code'] == 0) {
                $user = new User([
                    'nickname' => $output['data']['name'], //默认信息
                    'stu_id' => $data['stu_id'],
                    'password' => md5($data['password']),
                    'upload' => '[]', //mysql 中 json 默认值只能设置为NULL 为了避免不必要的麻烦，在创建的时候赋予初始值
                    'collection' => '[]',
                    'remember' => md5($data['password'] . time() . rand(1000, 2000))
                ]);
                $result = $user->save();

                if ($result) {
                    //直接使用上面的 $user 会导致没有id  这个对象新建的时候没有id save后才有的id 但是该id只是在数据库中 需要再次查找模型
                    $user = User::query()->where('stu_id', $data['stu_id'])->first();
                    session(['login' => true, 'uid' => $user->id]);

                    return msg(0, $user->info());
                } else {
                    return msg(4, __LINE__);
                }
            }
        } else { //查询到该用户记录
            if ($user->password === md5($data['password'])) { //匹配数据库中的密码
                session(['login' => true, 'uid' => $user->id]);
                return msg(0, $user->info());
            } else { //匹配失败 用户更改密码或者 用户名、密码错误
                $output = checkUser($data['stu_id'], $data['password']);
                print_r($output);
                if ($output['code'] == 0) {
                    $user->password = md5($data['password']);
                    $user->remember = md5($data['password'] . time() . rand(1000, 2000));
                    $user->save();

                    session(['login' => true, 'uid' => $user->id]);
                    return msg(0, $user->info());
                }
            }
        }

        return msg(2, __LINE__);
    }

}
