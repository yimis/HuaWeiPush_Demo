<?php
/**
 *          华为消息推送
 */

class HuaWeiPush{

    public static $appId = "APPID";                 //会员中心->我的产品->移动应用详情
    public static $appSecret = "APPSECRET";         //会员中心->我的产品->移动应用详情
    public static $appPkgName = "APPPKGNAME";       //会员中心->我的产品->移动应用详情

    private $accessTokenUrl = "https://login.cloud.huawei.com/oauth2/v2/token";         //获取AccessToken URL
    private $pushUrl = "https://api.push.hicloud.com/pushsend.do";                      //消息推送服务 URL

    /**
     * 获取访问的ACCESSTOKEN
     * @return mixed
     */
    private function getAccessToken()
    {
        $request_data = "grant_type=client_credentials&client_secret=".self::$appSecret."&client_id=".self::$appId;
        $result = json_decode($this->https_request($this->accessTokenUrl,$request_data),true);
        return $result['access_token'];
    }

    /**
     * @param $message
     * @param $deviceTokens
     * @return mixed
     */
    public function send($message,$deviceTokens){

        //$deviceTokens = array();//目标设备Token

        $body = array();                                //仅通知栏消息需要设置标题和内容，透传消息key和value为用户自定义
        $body['title'] = $message['title'];             //消息标题
        $body['content'] = $message['content'];         //消息标题

        $param = array();
        $param['appPkgName'] = self::$appPkgName;       //定义需要打开的appPkgName

        $action = array();
        $action['param'] = $param;                      //消息点击动作参数
        $action['type'] = 3;                            //类型3为打开APP，其他行为请参考接口文档设置

        $msg = array();

        $msg['action'] = $action;   //消息点击动作
        $msg['type'] = 3;           //3: 通知栏消息，异步透传消息请根据接口文档设置
        $msg['body'] = $body;       //通知栏消息body内容

        $ext = array();//扩展信息，含BI消息统计，特定展示风格，消息折叠。
        $ext['biTag'] = 'Trump';//设置消息标签，如果带了这个标签，会在回执中推送给CP用于检测某种类型消息的到达率和状态
        $ext['icon'] = "";//自定义推送消息在通知栏的图标,value为一个公网可以访问的URL

        $hps = array();//华为PUSH消息总结构体
        $hps['msg'] = $msg;
        $hps['ext'] = $ext;

        $payload = array();
        $payload['hps'] = $hps;

        $postBody = 'access_token=' . urlencode($this->getAccessToken()) . '&nsp_svc=' . urlencode('openpush.message.api.send') . '&nsp_ts=' . (int)urlencode($this->msectime()/1000)
            . '&device_token_list=' . urlencode(json_encode($deviceTokens)) . '&payload=' . urlencode(json_encode($payload));
        $postUrl = $this->pushUrl . '?nsp_ctx=' . urlencode("{\"ver\":\"1\", \"appId\":\"" . self::$appId . "\"}");
        return $this->https_request($postUrl,$postBody);
    }

    function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 获取系统毫秒级时间戳
     * @return float
     */
    function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}