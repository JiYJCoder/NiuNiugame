<?php
/*
 * 调试用文件 主函数文件 LQapps/Common/functions.php, 额外开发请添加到开发模块下的Common,EX:Home/Common/functions.php
 * 几个常用方法
 * 打印数据：pr($data);
 * 在文本上记录数据:lq_test($data);(特别方便于ajax异步时执行，文本生成在根目录下，txt.txt)
 * 快捷缓存获取 :  pr(F('article_cat','',COMMON_ARRAY));  缓存目录 RunTime/Array 下，分类等常用数据不用执行sql查询，直接F()获取
 * 短信发送：lqSendSms($mobile, $datas, $tempId='SMS_11490043');
 * 还有各种验证，加密解密，更多查看  LQapps/Common/functions.php
 */
namespace Home\Controller;

use Home\Model\LiveModel;
use Think\Controller;
use Video\Api\liveApi;
use Video\Api\vodApi;
use Member\Api\MemberApi as MemberApi;
use Home\ORG\CacheTalk;
use Video\Api\ossApi;

defined('in_lqweb') or exit('Access Invalid!');


class TestController extends PublicController
{
    public $appName;
    public $startTime;
    public $endTime;
    public $streamName;
    private $D_SMS;
    private $adModel;
    private $lesson_record;
    protected $model_lesson_live;
    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->appName = 'zier21live';
        $this->startTime = date("Y-m-d", strtotime("-1 days")) . "T" . date("H:i:s") . "Z";
        $this->endTime = date("Y-m-d") . "T" . date("H:i:s") . "Z";
        $this->streamName = '8_' . date("Ymd");
        $this->model_member = new MemberApi;//实例化会员
        $this->D_SMS = D('Home/SmsLog');
        $this->adModel = D('AdPosition');
        $this->lesson_record = D("LiveRecord");
        $this->model_lesson_live = D("LessonLive");
    }

    //PC首页
    public function index()
    {
        session('a',$_POST['title']);
        session('b',$_POST['content']);

        pr(session());
        $this->display();
    }

    public function sql_test()
    {
        $postDate = array(
            'title' => '天气好',
            'content' => '今天是星期三',
        );

        $postDate = http_build_query($postDate);

        $opts = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Host:localhost\r\n" .
                    "Content-type:application/x-www-form-urlencoded\r\n" .
                    "Content-length:".strlen($postDate)."\r\n",
                'content' => $postDate,
            )
        );

        $context = stream_context_create($opts);
        file_get_contents("www.zier.com/index.php/test",false,$context);
    }

    function testTemp()
    {
        pr($this->adModel->getAdPositionById(4));
    }

    ////短信登录示例
    function testLogin()
    {
        $mobile = '13631479553';
        $check_code = lq_random_string(6, 1);//随机码
        $tempId = 'SMS_11490043';
        ///半小时内允许发送三次
        if (!$this->D_SMS->isAllowReceive($mobile, 'login')) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，不能频繁请求操作！', 'data' => array(), "url" => "", "note" => ""), $this->JSONP);
        }
        ///发送验证码
        $sms_data = lqSendSms($mobile, $check_code, $tempId);
        if ($sms_data["status"] == 1) {
            $this->D_SMS->addSms('login', $mobile, $check_code);
            lq_test('短信发送成功');
        } else {
            lq_test('短信发送失败');
        }

        if (!$this->D_SMS->isEffective($mobile, 'login', $check_code)) {
            echo '验证码无效';
        }
        //pr( $this->model_member->apiGetGrowInfo('13724749586','123456cxx',2) );
    }

    ////直播推流，拉流地址获取
    public function test()
    {
        $api = new liveApi();

        ////推流地址
        // print_r($api->getPushSteam($this->streamName));
        $this->pushUrl = $api->getPushSteam($this->streamName);
        $this->pullUrl = $api->getPullSteam('14_30_190');
        $this->display();
    }

    /////成长平台接口测试
    function testApi()
    {
        //$api = new liveApi();
        // print_r($api->DescribeLiveStreamOnlineUserNum($this->appName, $this->streamName));
        ////http://www.zier365.com/a/token?grant_type=get&client_id=13724749586&client_secret=123456cxx

        //$token = 'f9edab490bfaab1532d8a13e8fdb504a7fb7f2f0';
        $user_id = '2615';
        $secret = '123456cxx';
        $url = "http://www.zier365.com/a/index.php/?access_token=token_key";
        $pwd_str = str_replace("=", "00000", base64_encode($user_id . 'NBe6@51=fd$3%6' . $secret . date("Ymd")));
        $post_data = array(
            "act" => "normal.doStuLogin",
            "client_secret" => $pwd_str,
            "client_id" => $user_id
        );

        pr(curl_post($url, json_encode($post_data)));

    }

    public function getInfo()
    {
        $token = $_GET['token'];
        $url = "http://www.zier365.com/a/index.php/?access_token=" . $token;
        $post_data = array(
            "act" => "token.users",
            "fun" => 'userInfo',
        );
        pr(curl_post($url, json_encode($post_data)));
    }

    public function getTeacherToken()
    {
        $user_id = '13724749586';
        $secret = '123456cxx';
        $url = "http://www.zier365.com/a/index.php/?access_token=token_key";
        $pwd_str = str_replace("=", "00000", base64_encode($user_id . 'NBe6@51=fd$3%6' . $secret . date("Ymd")));
        $post_data = array(
            "act" => "normal.doTeacherLogin",
            "client_id" => $user_id,
            "client_secret" => $pwd_str,
        );

        pr(curl_post($url, json_encode($post_data)));
    }

    /////视频上传demo
    public function uploaddemo()
    {
        $api = new vodApi();
        $title = 'test';
        $filename = '1.flv';
        $filesize = '1024';

        $upload = $api->CreateUploadVideo($title, $filename, $filesize);
        $upload = object2array(json_decode($upload));
        ///UploadAuth
        $this->assign("upload", $upload);
        $this->display();
    }


    /////通过视频id获取视频
    public function getVideoUrl()
    {
        ///69d93f48642f478db0548c93c9c35ee4
        $api = new vodApi();
        $vodID = '5a93cd70dd8a4484927d14436f9be471';
        echo $api->GetPlayInfo($vodID);
    }

    public function sendmail()
    {
        $to = '532243346@qq.com';
        $name = 'zier';
        pr(lq_send_mail($to, $name, "111", "222222222"));
    }

    public function uploadfile(){
        $this->display();
    }

    public function bbb()
    {
        $id = I('id');
        $uid = I('uid');
        $this->ajaxReturn(array('status'=>1,'msg'=>'老哥真是6到不行','id'=>$id,'uid'=>$uid));
    }


    //////聊天
    public function talk()
    {
        $talk = new cacheTalk(1);
        $val = rand(1,100);
        $uid = rand(101,200);
        $img = NO_AVATAR;
        $name = 'test';
        $now = $talk->add_talk($uid,$img,$name,$val);
        pr($now);
    }
    ////获取聊天记录
        public function getTalk()
    {
        $talk = new cacheTalk(1);
        pr($talk->get_talk());
//        pr($talk->cache);
    }




    public function is_exist()
    {
        $ossApi = new ossApi();
        $obj = '2017-08-03/5982f3e08ca8a.pptx';

        pr($ossApi->doesObjectExist($obj));
    }

    public function delfile()
    {
        $obj = D("Live")->deleteFile(11,12);

        if($obj)
        {
            $oss_api = new ossApi();
            $oss_api->deleteObject($obj);
        }
    }

    public function viewtest()
    {
        $rs = D("EnrollView")->where("zn_type=1 and zn_member_id=4")->select();
        echo D("EnrollView")->getLastSql();
        $cat = F('lesson_cat','',COMMON_ARRAY);
        pr( $cat[6]['zc_caption']);
pr($rs);
    }
    public function upload()
    {
        pr($_POST);
        pr($_FILES);
    }


    public function enroll()
    {
        self::checkLogin(1);
        //pr($_SESSION);
        //$this->model_member->apiLoginSession(8,1);
       //$this->ajaxReturn( ($this->model_member->apiMemberFavorite(19,1) ));
        //$this->ajaxReturn( ($this->model_member->apiMemberEnroll(19,1) ));
    }

    public function rank()
    {
        pr(get_ranking(27,1));
    }

    public function arr()
    {
        echo date("Y-m-d H:i:s");
        $rs = M("LessonCat")->where("zn_fid=0")->select();
        foreach($rs as $k => $v)
        {
            $sec = M("LessonCat")->where("zn_fid=".$v['id'])->select();
            $rs[$k]["DRs"] = $sec;
            foreach($sec  as $sk => $sv)
            {
                $thr = M("Vod")->where("zn_cat_id=".$sv['id'])->select();
                $rs[$k]["DRs"][$sk]["thr"] = $thr;
            }
        }
$this->arr = $rs;
        //pr($rs);
        $this->display();
    }

    //////直播观看记录
    public function liveRecord()
    {
       self::checkLogin(1);
        //pr($this->login_member_info);
        $this->lesson_id = $this->lqgetid;
        ////进入直播页面就添加记录，已过滤，不会重复添加
        $this->lesson_record->addData($this->lesson_id,$this->login_member_info['id']);

        ////直播观看时间
        //echo $this->lesson_record->getCount($this->login_member_info['id']);

        ////共听多少位老师课
        //echo $this->lesson_record->teacherSum($this->login_member_info['id']);

        ////最近直播
       // pr( $this->model_lesson_live->getLastLive(19));
        $this->display();
    }
    /////刷新直播观看时长
    public function liveSum()
    {
        self::checkLogin(1);
        $lesson_id = I("post.lesson_id","0");
        /////刷新当前会员某课节的观看时间  10秒
        $this->ajaxReturn( $this->lesson_record->saveData($lesson_id,$this->login_member_info['id'],C('REQUEST_INTERVAL')));
    }

    /////最近观看直播
    public function lastLive()
    {
        self::checkLogin(1);
        $rs = D("LiveRecordView")->order("zn_cdate desc")->where("zn_member_id=".$this->login_member_info['id'])->find();

        pr($rs);
    }

    public function liveview()
    {
        $rs = D("LiveView")->select();
        echo D("LiveView")->getLastSql();
        pr($rs);
    }

    public function getGrowSchool()
    {
        $rs = $this->model_member->apiGetGrowSchoolList();
        pr($rs);
    }


    /////直播信息汇总
    public function liveInfo()
    {

        $testLive_id = I("get.tnid","0","int");

        $liveInfo = M("LessonLive")->find($testLive_id);
        $streamName = D("Live")->getStreamName($liveInfo['id'],$liveInfo['zn_cat_id']);


        $api = new liveApi();

//////当前正在直播的流
        $liveing = $api->describeLiveStreamsOnlineList();
      /*  Array
        (
            [0] => Array
            (
                [PublishTime] => 2017-09-01T09:08:51Z
            [StreamName] => 14_38_308
            [PublishUrl] => rtmp://live.zier21.com/zier21live/14_38_308
            [DomainName] => live.zier21.com
            [AppName] => zier21live
            [streamName_s] => 14_38
        )

    [1] => Array
    (
        [PublishTime] => 2017-09-01T09:08:54Z
            [StreamName] => 14_38_309
            [PublishUrl] => rtmp://live.zier21.com/zier21live/14_38_309
            [DomainName] => live.zier21.com
            [AppName] => zier21live
            [streamName_s] => 14_38
        )

)*/
//        pr($liveing);
//        lq_test("正在直播：".$liveing);

        //////在线人数
        $online_num = $api->DescribeLiveStreamOnlineUserNum($streamName);
//        lq_test("当前人数：".$online_num);

        $this->assign("liveing",$liveing);
        $this->assign("online_num",$online_num);
        $this->assign("streamName",$streamName);
        $this->assign("liveInfo",$liveInfo);
        $this->display();
    }


}