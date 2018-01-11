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

        $to = array(93);
        $push = new PushEvent();
        $push->setUser($to)->setContent($data)->push();
    }

}