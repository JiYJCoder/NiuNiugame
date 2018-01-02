<?php
/*
 * 通知 URL
 * 推断流  及  录制
 */
namespace Home\Controller;

use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class NotifyController extends PublicController
{
    private $model_lesson_live;
    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->model_lesson_live = D("LessonLive");
    }

    //首页
    public function index()
    {

    }

    /*
     * 推断流通知URL  获取方式 : $_GET
     * 格式：str
     * 内容：(publish) action=publish&ip=113.67.227.55&id=8_20170707&app=live.zier21.com&appname=zier21live&time=1499409789&usrargs=vhost=live.zier21.com&auth_key=1499411297-0-0-ece4301d77b30103de2ba7e5bab536ca&node=eu13
     *       (publish_done) action=publish_done&ip=113.67.227.55&id=8_20170707&app=live.zier21.com&appname=zier21live&time=1499409817&usrargs=vhost=live.zier21.com&auth_key=1499411297-0-0-ece4301d77b30103de2ba7e5bab536ca&node=eu13
     */
    public function live()
    {

        $action = I("get.action");
        $id = end(explode("_",I("get.id")));
        $time = I("get.time");

        if($action == "publish")
        {
            $this->model_lesson_live->updateTime($id,$time,1);
        } else {
            $this->model_lesson_live->updateTime($id,$time,2);
        }
        http_response_code(200);
    }

    /*
     * 视频录制通知URL  获取方式 ：$_POST
     * 格式:json
     * 内容：{"domain":"live.zier21.com","app":"zier21live","stream":"8_2017070_7","uri":"record/2017-07-07/zier21live/8_20170707/2017-07-07-14:43:09_2017-07-07-14:43:36.flv","duration":21.1,"start_time":1499409789,"stop_time":1499409821}
     */
    public function vod()
    {
        $data = object2array(json_decode($GLOBALS['HTTP_RAW_POST_DATA']));

        if($data)
        {
            $id = end(explode("_",$data['stream']));
            $url = $data['uri'];
            $this->model_lesson_live->updateVodUrl($id,$url);
            http_response_code(200);
        }
    }

    /*
     * 视频上传通知
     */
    public function vodUpload()
    {
        //lq_test($data = object2array(json_decode($GLOBALS['HTTP_RAW_POST_DATA'])));
    }
}