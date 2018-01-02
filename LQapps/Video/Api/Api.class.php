<?php
/**
 * Date: 2017-06-22
 * Time: 10:22
 * 阿里云视频直播API 初始化
 */

namespace Video\Api;
require_cache(APP_COMMON_PATH . 'Conf/config.php');

/**
 * API调用控制器层
 */
class Api
{
    protected $apiParams;
    protected $apiDomain;
    protected $videoHost;
    protected $vhost;
    protected $appName;
    protected $privateKey;

    /**
     * 构造方法，检测相关配置
     */
    public function __construct()
    {
        date_default_timezone_set("GMT");
        //////api接口公共参数
        $apiParams['Format'] = C("ALI_API")['Format'];
        $apiParams['SignatureMethod'] = C("ALI_API")['SignatureMethod'];
        $apiParams['SignatureNonce'] = C("ALI_API")['SignatureNonce'];
        $apiParams['SignatureVersion'] = C("ALI_API")['SignatureVersion'];
        $apiParams['Timestamp'] = date('Y-m-d\TH:i:s\Z');
        $apiParams['Version'] = C("ALI_API")['Vod_Version'];
        $apiParams["AccessKeyId"] = C("ALI_API")['AccessKeyId'];
        $apiParams['Credential'] = C("ALI_API")['Credential'];

        $this->apiParams = $apiParams;
        ///接口请求地址
        //$this->apiDomain = $apiUrl;
        /////推流地址和直播地址参数
        $this->videoHost = C("ALI_API")['videoHost'];
        $this->vhost = C("ALI_API")['vhost'];
        $this->appName = C("ALI_API")['appName'];//////默认appName
        $this->privateKey = C("ALI_API")['privateKey'];
    }

    /**
     * 访问阿ali接口进行请求并返回ali返回值
     * @param array $apiParams 接口自定义参数
     */
    public function aliApi($apiParams,$apiUrl = '')
    {
        if($apiUrl == "")
        {
            $apiUrl = C('ALI_API')['apiLiveDomain'];
            $apiParams['Version'] = C("ALI_API")['Live_Version'];
        }
        $apiParams = array_merge($this->apiParams, $apiParams);
        /////计算签名认证
        $apiParams["Signature"] = $this->computeSignature($apiParams, C("ALI_API")['AccessKeySecret']);

        $requestUrl = "http://" . $apiUrl . "/?";

        foreach ($apiParams as $apiParamKey => $apiParamValue) {
            $requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        $url = substr($requestUrl, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //处理http证书问题
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        if (false === $ret) {
            $ret = curl_errno($ch);
            $this->message = 'curl方法出错，错误号：' . $ret;
            return false;
        }
        curl_close($ch);
        if ($this->format == "JSON")
            return json_decode($ret, true);
        elseif ($this->format == "XML") {
            return $this->xmlToArray($ret);
        } else
            return $ret;
    }

    /**
     * 计算签名
     * @param $credential
     * @param $parameters
     * @param $accessKeySecret
     * @return string
     */
    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = C("ALI_API")['Credential'] . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $this->signString($stringToSign, $accessKeySecret . "&");

        return $signature;
    }

    /**
     * 对待加密字符串加密
     * @param $source
     * @param $accessSecret
     * @return string
     */
    public function signString($source, $accessSecret)
    {
        return base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }

    /**
     * url编码
     * @param $str
     * @return mixed|string
     */
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * xml转成数组
     * @param $xml
     * @return mixed
     */
    function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);

        return $val;
    }
}
