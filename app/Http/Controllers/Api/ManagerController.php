<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Manager;
class ManagerController extends Controller
{

    /**
     * @param Request $request
     * @return string
     * @api {post} /api/manager/login 登陆
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
    public function Login(Request $request)
    {
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






        $Manager = Manager::query()->where('stu_id','=',$data['stu_id'])->first();
        if(!$Manager){
            return msg(8, "" . __LINE__);
        }else{
            if ($Manager['nick_name'] == 'never_login') { //用户从未登录
                //利用三翼api确定用户账号密码是否正确
                $output = checkUser($data['stu_id'], $data['password']);
                if ($output['code'] == 0) {
                    $data = [
                        'nickname'=>$data['nickname'],
                        'password'=>$data['password'],
                        'stu_id' => $data['stu_id'],
                        'level' => '1'
                    ];
                    $result = $Manager->update($data);

                    if ($result) {
                        //直接使用上面的 $user 会导致没有id  这个对象新建的时候没有id save后才有的id 但是该id只是在数据库中 需要再次查找模型,laravel老版本的一个bug，有兴趣可以看看
//                        $Manager = Manager::query()->where('stu_id', $data['stu_id'])->first();
                        session(['ManagerLogin' => true, 'mid' => $Manager->id]);

                        return msg(0, $Manager->info());
                    } else {
                        return msg(4, __LINE__);
                    }
                }else{
                    return msg(2,__LINE__);
                }
            } else { //查询到该用户记录
                if ($Manager->password === md5($data['password'])) { //匹配数据库中的密码
                    session(['ManagerLogin' => true, 'mid' => $Manager->id]);
                    return msg(0, $Manager->info());
                } else { //匹配失败 用户更改密码或者 用户名、密码错误
                    //利用三翼api确定用户账号密码是否正确
                    $output = checkUser($data['stu_id'], $data['password']);
                    if ($output['code'] == 0) {
                        $data = [
                            'nickname'=>$data['nickname'],
                            'password'=>$data['password'],
                            'stu_id' => $data['stu_id'],
                            'level' => '1'
                        ];
                        $result = $Manager->update($data);
                        if ($request){
                            session(['ManagerLogin' => true, 'mid' => $Manager->id]);
                            return msg(0, $Manager->info());
                        }else{
                            return msg(4,__LINE__);
                        }
                    }

                }
            }

        }
    }
    /**
     * @api {post} /api/manager/register 添加管理员
     * @apiGroup 管理员
     * @apiVersion 1.0.0
     *
     * @apiDescription 编辑管理员信息，所有内容皆不为空
     *
     * @apiParam {String} nickname   姓名
     * @apiParam {String} level      管理员级别
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
    public function register(Request $request){
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data = $data + [
                "level" => 1,
                "password"    => md5('never_login')
            ];
        $Manager = new Manager($data);
        $Manager->save();
    }



    /**
     * @api {post} /api/evaluation/:id 编辑管理员信息
     * @apiGroup 管理员
     * @apiVersion 1.0.0
     *
     * @apiDescription 编辑管理员信息，所有内容皆不为空
     *
     * @apiParam {String} nickname   姓名
     * @apiParam {String} password   密码
     *
     *
     * @apiSuccess {Number} code      状态码，0：请求成功
     * @apiSuccess {String} nickname  用户名字，真名
     * @apiSuccess {String} stu_id    用户学号
     * @apiSuccess {Json}   level        管理员等级
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "code":0,
     *  "status":"成功",
     *  "data":{
     *       "nickname":"张桂福",
     *       "stu_id":"201805710601",
     *       "password":"caifu",
     *       "level":"1",
     *  }
     * }
     *
     */
    /**
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        //检查数据类型格式是否有误，data_change在最下面
        $data = $this->data_change($request);

        if (!is_array($data)) {
            return $data;
        }
        $Manager = Manager::query()->find($data["id"]);
        //修改参数
        $data = [
            'nickname'=>$data['nickname'],
            'password'=>$data['password']
        ];
        $Manager->update($data);
        if ($Manager) {
            if ($Manager){
                return msg(0, $Manager->info());
            }else{
                return msg(4,__LINE__);
            }
        }
        return msg(4, __LINE__);
    }





    private function data_handle(Request $request=null) {
        $mod = [
            "nickname"     => ["string", "max:15"],
            "stu_id"   => ["string", "max:12"]
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

    private function data_change(Request $request=null) {
        $mod = [
            "id" => ["string",'max:3'],
            "nickname"     => ["string", "max:15"],
            "password"  => ["string", "max:20"]
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
