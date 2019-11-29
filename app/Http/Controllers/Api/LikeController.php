<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    /**
     * @api {post} /api/like/:id 用户赞踩评测
     * @apiGroup 评测
     * @apiVersion 1.0.0
     *
     * @apiDescription 用户赞踩评测，可取消，变换赞踩。登陆后可操作
     *
     * @apiParam {Number}  id        评测id
     * @apiParam {Number}  like      是否赞 0踩 1赞
     *
     * @apiSuccess {Number} code     状态码，0：请求成功
     * @apiSuccess {String} message  提示信息
     * @apiSuccess {Object} data     后端参考信息，前端无关
     *
     * @apiSuccessExample {json} Success-Response:
     * {"code":0,"status":"成功","data":43}
     */
    /**
     * @param Request $request
     * @return string
     */
    public function mark(Request $request)
    {
        $mod = ["like" => ["boolean"]];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        $data = ["user" => session("uid"), "evaluation" => $request->route("id"), "like" => $request->input("like")];
        // 事务处理
        DB::beginTransaction();
        try {
            $like = DB::table("likes")->where("user", session("uid"))->where("evaluation", $request->route("id"));
            $evaluation = DB::table('evaluations')->where('id', $data["evaluation"]);

            // 赞/踩
            if($data["like"] == 1) {
                if($like->count()) {
                    if($like->get("like")[0]->like == 1) { //曾经赞过则为取消赞
                        $evaluation->increment('like', -1);
                        $like->delete();
                    } else { // 踩变赞
                        $evaluation->increment('like', 1);
                        $evaluation->increment('unlike', -1);
                        $like->update(["like" => 1]);
                    }
                } else {
                    $evaluation->increment('like', 1);
                    DB::table("likes")->insert($data);
                }
            } else { // 逻辑和赞基本相同
                if($like->count()) {
                    if($like->get("like")[0]->like == 0) { //曾经踩过则为取消踩
                        $evaluation->increment('unlike', -1);
                        $like->delete();
                    } else { // 赞变踩
                        $evaluation->increment('unlike', 1);
                        $evaluation->increment('like', -1);
                        $like->update(["like" => -1]);
                    }
                } else {
                    $evaluation->increment('unlike', 1);
                    DB::table("likes")->insert($data);
                }
            }
            DB::commit();

            return msg(0, __LINE__);
        } catch (\Exception $e) {
            DB::rollBack();
            print_r($e);
            return msg(7, __LINE__);
        }

    }
}

