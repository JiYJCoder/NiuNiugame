<?php
namespace Api\Controller;

use Think\Controller;
use LQLibs\Util\PushEvent as PushEvent;
//defined('in_lqweb') or exit('Access Invalid!');

class TestController extends PublicController
{
    public $socket;
    /** 初始化*/
    public function __construct()
    {
        parent::__construct();

    }

    //首页数据包
    public function index()
    {
        //接入微信类
        import('Vendor.Wechat.TPWechat');

        $WxObj = new \Wechat(C("WECHAT"));
        $url=$_SERVER["REQUEST_URI"];
        $url="http://".$_SERVER['HTTP_HOST'].$url;

        if(!isset($_GET['code'])){
            $url=$WxObj->getOauthRedirect($url);
            header("Location: ".$url);
        }

        $user_oauth = $WxObj->getOauthAccessToken();

        $UserInfo=$WxObj->getUserInfo($user_oauth["openid"]);

        if($UserInfo['subscribe']!=1){
            $UserInfo=$WxObj->getOauthUserinfo($user_oauth["access_token"],$user_oauth["openid"]);
        }

        pr($UserInfo);
    }

    public function send_sys_info()
    {
        $data = array(
            "type" => 1,
            "info" => array(
                "id" => 2,
                "content" => '健康游戏'
            )
        );
        $to = array(123,11);
        $push = new PushEvent();
        $push->setUser()->setContent($data)->push();
    }

    public function client()
    {
        $this->uid = mt_rand(1,100);
        $this->display();
    }

    public function join_room()
    {
        $data = array(
            "type" => 1,
            "info" => array(
                "id" => 2,
                "content" => '健康1111游戏'
            ),
        );

        $to = array(27);
        $push = new PushEvent();
        $push->setUser($to)->setContent($data)->push();
    }

}