<?php
namespace App\Lib;
use Redis;
use Exception;
/** 微信小程序相关接口调用封装
 * Class WeChat
 * 需要指定一个端口开启redis 用来存储 access token
 */
class WeChat {
    private $ch = null;
    private $redis = null;
    private static $_instance = null;
    /**
     * WeChat constructor.
     * @throws Exception
     */
    private function __construct() {  // 私有构造函数，禁止外部实例化
        try {
            $this->redis = new Redis();
            $this->redis->connect('wechat_redis_db', 6379);
        } catch (Exception $e) {
            throw new Exception("Redis 连接失败_$e");
        }
    }
    private function addParams(string $url, array $params) {  // 将get参数加在url后面
        $tmp = [];
        foreach ($params as $key => $value) {
            array_push($tmp, "$key=" . urlencode($value));
        }
        $url = $url . "?" . join("&", $tmp);
        return $url;
    }
    private function curlInit() {
        static $flag = true;
        if ($flag) {
            $flag = false;
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
    }
    public static function getWeChat() {  //单例，减少WeChat实例
        if (is_null(self::$_instance)) {
            self::$_instance = new WeChat();
        }
        return self::$_instance;
    }
    /**对照微信官方文档，此处仅做封装，方法内逻辑为调用官方接口并自动维护
     * @param string $appid
     * @param string $secret
     * @param bool|null $expired
     * @return bool|string
     */
    public function getAccessToken(string $appid, string $secret, ?bool $expired = false) { // 获取全局唯一 access_token
        $token = $this->redis->get("access_token");
        if (!$expired) {  // 不确定是否过期 先检验
            $expired_time = $this->redis->get("expired");
            // XXX 可以改成脚本形式，由一个脚本定时刷新
            if (time() < $expired_time - rand(0, 100)) {  // 100秒随机时间，防止高并发下多次获取token
                return $token;
            }
        }
        // 锁 防止多次获取token
        $lock = $this->redis->get("get_access_token_lock");
        if ($lock) {
            return $token;
        } else {
            $this->redis->set("get_access_token_lock", true);
        }
        // 过期检验未通过或调用时指出token过期
        try {  // 此处异常处理作用为：最终锁要被去除
            $this->curlInit();
            $params = [
                'grant_type' => 'client_credential',
                'appid' => $appid,
                'secret' => $secret
            ];
            $target_url = "https://api.weixin.qq.com/cgi-bin/token";
            curl_setopt($this->ch, CURLOPT_URL, $this->addParams($target_url, $params));
            $result = json_decode(curl_exec($this->ch), true);
            $token = $result['access_token'];
            $time = $result['expires_in'];
            $this->redis->set("access_token", $token);
            $this->redis->set("expired", time() + $time - 1000);  // 1000秒缓冲时间
        } catch (Exception $e) {
            // TODO 错误处理
        } finally {
            $this->redis->set("get_access_token_lock", false);
        }
        return $token;
    }
    public function code2Session(string $appid, string $secret, string $js_code, ?string $grant_type = "authorization_code") {
        $this->curlInit();
        $target_url = "https://api.weixin.qq.com/sns/jscode2session";
        $params = [
            'appid' => $appid,
            'secret' => $secret,
            'js_code' => $js_code,
            'grant_type' => $grant_type
        ];
        curl_setopt($this->ch, CURLOPT_URL, $this->addParams($target_url, $params));
        $result = curl_exec($this->ch);
        return $result;
    }
    /**获取用户信息
     * @param string $access_token
     * @param string $openid
     * @param string|null $transaction_id
     * @param string|null $mch_id
     * @param string|null $out_trade_no
     * @return bool|string
     */
    public function getPaidUnionId(string $access_token, string $openid, ?string $transaction_id = null,
                                   ?string $mch_id = null, ?string $out_trade_no = null) {
        $this->curlInit();
        $target_url = "https://api.weixin.qq.com/wxa/getpaidunionid";
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
            'transaction_id' => $transaction_id,
            'mch_id' => $mch_id,
            'out_trade_no' => $out_trade_no
        ];
        curl_setopt($this->ch, CURLOPT_URL, $this->addParams($target_url, $params));
        $result = curl_exec($this->ch);
        return $result;
    }

    /** Upick定制
     * @param string $appid
     * @param string $secret
     * @param string $path 为定制
     */
    public function get_page_QRcode(string $appid, string $secret, string $id) {
        $this->curlInit();
        $access_token = $this->getAccessToken($appid, $secret);
        $target_url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit";
        $params = [
            'access_token' => $access_token,
        ];
        $data = [
            "scene" => "id=" . $id,
            "page" => "pages/review-content/review-content"
        ];
        $defaults = [
            CURLOPT_URL => $this->addParams($target_url, $params),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type:' => 'application/json',
            ]
        ];
        curl_setopt_array($this->ch, $defaults);
        $result = curl_exec($this->ch);
        if(strlen($result) < 100) {
            return msg(4, $result . __LINE__);
        } else {
            file_put_contents(storage_path('app/public/image/') . $id . ".png", $result);
            return msg(0, ["code_url" => env("APP_URL") . "/storage/image/" . $id . ".png"]);
        }
    }

    public function __destruct() {
        if (!is_null($this->ch)) {
            curl_close($this->ch);
        }
        if (!is_null($this->redis)) {
            $this->redis->close();
        }
    }
}
