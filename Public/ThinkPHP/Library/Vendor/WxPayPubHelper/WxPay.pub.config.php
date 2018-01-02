<?php
/**
* 	配置账号信息
*/
class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wx29dedd3e19a1aa33';
	//受理商ID，身份标识
	const MCHID = '1413591402';

    //app端appid
    const APP_APPID = 'wxf0c582220ac9657d';
    //app端受理商ID，
    const APP_MCHID = '1418990302';

	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'JykjLxj20160928hzqjnddnrxzxA2010';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = 'c130c60afacbf09ee2c7103bb6427cb9';
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://wx.lxjjz.cn/do?g=home&m=index&a=test_wxpay';
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = WECHAT_SSLCERT_PATH;
	const SSLKEY_PATH = WECHAT_SSLKEY_PATH;
    //app 证书路径,注意应该填写绝对路径
    const APP_SSLCERT_PATH = APP_WECHAT_SSLCERT_PATH;
    const APP_SSLKEY_PATH = APP_WECHAT_SSLKEY_PATH;
	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>