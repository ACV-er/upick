<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 利用三翼借接口验证用户名密码
 * @param $sid
 * @param $password
 * @return mixed
 */
function checkUser($sid, $password) { //登录验证
    $api_url = "https://api.sky31.com/edu-new/student_info.php";
    $api_url = $api_url . "?role=" . env('ROLE') . '&hash=' . env('HASH') . '&sid=' . $sid . '&password=' . urlencode($password);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);

    return json_decode($output, true);
}

/**
 * 设置返回值
 * @param $code
 * @param $msg
 * @return string
 */
function msg($code, $msg) {
    $status = array(
        0 => '成功',
        1 => '缺失参数',
        2 => '账号密码错误',
        3 => '错误访问',
        4 => '未知错误',
        5 => '其他错误',
        6 => '未登录',
        7 => '重复访问',
        8 => '重复添加'
    );

    $result = array(
        'code' => $code,
        'status' => $status[$code],
        'data' => $msg
    );


    return json_encode($result, JSON_UNESCAPED_UNICODE);
}
