<?php

namespace Home\Controller;

use Think\Controller;
use Video\Api\liveApi;
use Member\Api\MemberApi as MemberApi;
use Home\ORG\CacheTalk;
use Video\Api\vodApi;

defined('in_lqweb') or exit('Access Invalid!');


class LiveController extends PublicController
{
    private $model_lesson_cat, $model_lesson_live, $D_SMS, $model_vod, $model_live, $model_lesson_vod, $model_favorite, $model_enroll;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();


        $this->model_member = new MemberApi;//实例化会员

        $this->D_SMS = D('Home/SmsLog');
        $this->model_lesson_cat = D('LessonCat');
        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->model_record = D("LiveRecord");
    }

    // live首页
    public function index()
    {
        // 获取全部正在直播数据
        $api = new liveApi();
        // 当前正在直播的流
        $liveing = $api->describeLiveStreamsOnlineList();
        $live_msg = [];// 直播列表
        foreach ($liveing as $key => $value) {
            $live_msg[$key] = $value['StreamName'];

        }
        date_default_timezone_set("PRC");
        if (lq_is_login('student')) {
            //        获取3个用户今天的直播
            $today_data = $this->model_live->get_today_live($live_msg);
            $today_count = count($today_data);
            $this->assign("today_count", $today_count);
            $this->assign('is_login', 1);
        }

        $page_parameter["s"] = $this->getSafeData('s');
        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        $table = "LiveView";
        $db = D($table);
        // 搜索条件
        $search_content_array = array(
            'fid' => I('get.fid', '0', 'int'),
            'cat_id' => I('get.cat_id', '0', 'int'),
            'school' => I('get.school', '0', 'int'),
            'orderby' => I("get.orderby", '6', 'int')
        );

        //pr($search_content_array);
        $this->assign("search_content_array", $search_content_array);

        $where['Live.zl_status'] = 6;
        $where['LessonLive.zl_status'] = 0;
        $where['LessonLive.zc_date'] = array("EGT", date("Y-m-d"));

        if ($search_content_array['cat_id']) {
            $where['Live.zn_cat_id'] = $search_content_array['cat_id'];

        }
        if ($search_content_array['school']) {
            $ids = $this->model_member->apiGetIdsByKeyword($this->login_studnet_info['zc_school'], 1);

            if(count($ids) > 0)
            {
                $where['Live.zn_teacher_id'] = array("IN", $ids);
                $whereCat['Live.zn_teacher_id'] = array("IN", $ids);
            } else {
                $where['Live.zn_teacher_id'] = array("EQ", 0);
                $whereCat['Live.zn_teacher_id'] = array("EQ", 0);
            }

        }
        if ($search_content_array['fid']) {
            $where['Live.zn_fid'] = $search_content_array['fid'];
            $this->fid_label = F('lesson_cat', '', COMMON_ARRAY)[$search_content_array['fid']]['zc_caption'];
            $catList = $this->__getLessonCat($search_content_array['fid'], $table, $where);
            $catTotal = 0;
            foreach ($catList as $cv) {
                $catTotal += $cv['total'];
            }
            $this->assign("catList", $catList);
        }

        $this->cat = $this->__getLessonCat(0, $table, $where);// 获取一级分类和数量

        $orderby = array(
            1 => "Live.zn_fav_num DESC",
            2 => "Live.zn_fav_num ASC",
            3 => "Live.zn_enroll_num DESC",
            4 => "Live.zn_enroll_num ASC",
            5 => "LessonLive.zc_date DESC",
            6 => "LessonLive.zc_date ASC",
        );

        $this->assign("orderby", $search_content_array['orderby']);
        //首页设置
        $page_config = array(
            'where' => $where,
            'order' => $orderby[$search_content_array['orderby']] . ',LessonLive.zc_date ASC',
        );
//        pr($page_config);


        $count = count($db->where($page_config["where"])->group("LessonLive.zn_cat_id")->order($page_config["order"])->select());
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['live']);//载入分页类
        $list = $db->where($page_config["where"])->group("LessonLive.zn_cat_id")->order($page_config["order"])->limit("$page->firstRow , $page->listRows")->select();

//        pr($list);

        if (!$catTotal) $catTotal = $count;
        foreach ($list as $key => $val) {
            $data = array(
                'zc_date' => $val['zc_date'],
                'zc_start_time' => $val['zc_start_time'],
                'zc_end_time' => $val['zc_end_time'],
                'zn_cat_id' => $val['id'],
            );
            $temp = $this->model_lesson_live->where($data)->field('id')->find();
            $list[$key]['lesson_id'] = $temp['id'];
//            $list[$key]['zc_title'] = lq_cutstr( $list[$key]['zc_title'],1);
            $list[$key]["zc_image"] = $val['zc_image'] ? API_DOMAIN . $val['zc_image'] : NO_PICTURE;
            $list[$key]["lesson_now"] = $this->model_lesson_live->lessonNow($val['id']);
            $list[$key]["cat_name"] = return_cat($val['zn_cat_id']);
            $list[$key]["fid_name"] = return_cat($val['zn_fid']);
            $list[$key]['streamname'] = $val['zn_teacher_id'] . '_' . $val['id'] . '_' . $list[$key]['lesson_id']; // 拼接出streamname
            if (in_array($list[$key]['streamname'], $live_msg)) {
                $list[$key]['living'] = 1;
            }
//            $streamName = $val['zn_teacher_id'] ."_" . $val['id'] ."_".$val['lesson_id'];
//            $list[$key]["streamName"] = $streamName;
//            if(in_array($streamName,$liveing))
//            {
//
//            } else{
//
//            }
        }
//        pr($list);

        $ad = D("AdPosition")->getAdPositionById(8)["list"]; // id=8是直播广告
        foreach ($ad as $key => $value) {
            if ($key == 0) $ad_list = $value;// 广告
        }
        $this->assign("ad", $ad_list);
//        pr($today_data);
        $showPage = $page->show();
        $this->assign("today_data", $today_data);
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->assign("count", $count);
        $this->assign("catTotal", $catTotal);
        $this->display();
    }


    /////直播介绍页
    public function livedetail()
    {
        if (!$this->lqgetid) $this->error("参数丢失");
        $detailInfo = $this->model_live->getLessonInfo($this->lqgetid);
        if (!$detailInfo) $this->error("该直播不存在或者已下架");
//        pr($detailInfo);
        $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);
        $this->assign("teacherInfo", $teacherInfo);
        $this->assign("detailInfo", $detailInfo);
//

        $this->display();
    }

    /*
     * 播放检测页
     * 检测：1 是否已报名
     *      2  是否已有信号
     *      3  离课堂直播开始是否半小时内
     */
    public function liveCheck()
    {
        if (session('teacher_auth')['id']) {

            $teacher_id = I("post.teacher_id", "", "int");
            $live_id = I("post.live_id", "", "int");

            $reData = array(
                "status" => 0,
                "msg" => "未检测到信号，请稍候重试...",
            );

            if (!$teacher_id || !$live_id) $this->ajaxReturn($reData);
            ////阿里信号流检测
            $liveApi = new liveApi();
            $onlineList = $liveApi->describeLiveStreamsOnlineList();
            date_default_timezone_set("PRC");
            //////当前课程是否存在信号列表中
            $streamName_s = $teacher_id . "_" . $live_id;
            $living_key = search_from_array($streamName_s, $onlineList);

            if ($living_key === 'default') {
                $this->ajaxReturn($reData);
            }

            $streamName = $onlineList[$living_key]['StreamName'];
            /////课节直播预期时间
            $liveInfo = $this->model_lesson_live->lqGetField("zn_cat_id = '" . $live_id . "' and zc_stream_name = '" . $streamName . "'", "id,zc_date,zc_start_time,zc_end_time");
            /////至多只能提前半小时进直播间
            $live_time_start = strtotime($liveInfo['zc_date'] . $liveInfo['zc_start_time']);
            $live_time_end = strtotime($liveInfo['zc_date'] . $liveInfo['zc_end_time']);

            /////直播时间结束，未进入的不能再进入
//            if (NOW_TIME > $live_time_end) {
////lq_test(NOW_TIME ."||".$live_time_end);
//                $nextLesson = $this->model_lesson_live->getNewestLesson($live_id);
//                if ($nextLesson) $say = '直播时间已到,下次直播时间为:' . $nextLesson['zc_date'] . " " . $nextLesson['zc_start_time'] . "-" . $nextLesson['zc_end_time'];
//                else $say = "该课程直播已完结，如需继续学习，请查看直播回放";
//                $reData['msg'] = $say;
//                $this->ajaxReturn($reData);
//            }

            $urlParams = "lesson_id=" . $liveInfo['id'];

            $url = U("/Live/broadcast", array("s" => think_ucenter_encrypt($urlParams, C('SYS_ENCRYPT_PWD'))));
            $reData['status'] = 1;
            $reData['msg'] = '正在进入直播间...';
            $reData['url'] = $url;
            $this->ajaxReturn($reData);
        } else {
            $teacher_id = I("post.teacher_id", "", "int");
            $live_id = I("post.live_id", "", "int");


            if (!session('student_auth')['id']) {
                $this->ajaxReturn(array("status" => 2, "msg" => "请先登录"));
            }

            ////检测是否已报名
            if (!$this->model_enroll->is_member_enroll(session('student_auth')['id'], $live_id, 1)) {
                $reData['msg'] = '抱歉,您尚未报名该课程';
                $this->ajaxReturn($reData);
            }

            $reData = array(
                "status" => 0,
                "msg" => "未检测到信号，请稍候重试...",
            );

            if (!$teacher_id || !$live_id) $this->ajaxReturn($reData);
            ////阿里信号流检测
            $liveApi = new liveApi();
            $onlineList = $liveApi->describeLiveStreamsOnlineList();
            date_default_timezone_set("PRC");
            //////当前课程是否存在信号列表中
            $streamName_s = $teacher_id . "_" . $live_id;
            $living_key = search_from_array($streamName_s, $onlineList);

            if ($living_key === 'default') {
                $this->ajaxReturn($reData);
            }
            $streamName = $onlineList[$living_key]['StreamName'];
            /////课节直播预期时间
            $liveInfo = $this->model_lesson_live->lqGetField("zn_cat_id = '" . $live_id . "' and zc_stream_name = '" . $streamName . "'", "id,zc_date,zc_start_time,zc_end_time");
            /////至多只能提前半小时进直播间
            $live_time_start = strtotime($liveInfo['zc_date'] . $liveInfo['zc_start_time']);
            $live_time_end = strtotime($liveInfo['zc_date'] . $liveInfo['zc_end_time']);
            if ($live_time_start - NOW_TIME >= 1800) {
                $reData['msg'] = '尚未到直播开始时间,请稍候进入';
                //$this->ajaxReturn($reData);
            }
            /////直播时间结束，未进入的不能再进入
            if (NOW_TIME > $live_time_end) {
//lq_test(NOW_TIME ."||".$live_time_end);
                $nextLesson = $this->model_lesson_live->getNewestLesson($live_id);
                if ($nextLesson) $say = '直播时间已到,下次直播时间为:' . $nextLesson['zc_date'] . " " . $nextLesson['zc_start_time'] . "-" . $nextLesson['zc_end_time'];
                else $say = "该课程直播已完结，如需继续学习，请查看直播回放";
                $reData['msg'] = $say;
                $this->ajaxReturn($reData);
            }

            $urlParams = "lesson_id=" . $liveInfo['id'];

            $url = U("/Live/broadcast", array("s" => think_ucenter_encrypt($urlParams, C('SYS_ENCRYPT_PWD'))));
            $reData['status'] = 1;
            $reData['msg'] = '正在进入直播间...';
            $reData['url'] = $url;
            $this->ajaxReturn($reData);
        }
    }

    ////直播播放页
    public function broadcast()
    {
        if (session('teacher_auth')['id']) {
            $s = I("get.s", "");
            $id = end(explode("=", (think_ucenter_decrypt($s, C('SYS_ENCRYPT_PWD')))));
            if (!$id) $this->error("参数丢失");

            ////课节信息
            $lesson_info = $this->model_lesson_live->find($id);
            if (!$lesson_info) $this->error("直播不存在或者已结束...");
            $this->assign('id', $lesson_info['zn_cat_id']);


            ////第几课时
            $lesson_info['lesson_now'] = $this->model_lesson_live->lessonNow($lesson_info['zn_cat_id']);
            ////检测是否已报名
            $member_id = session('teacher_auth')['id'];

            $detailInfo = $this->model_live->getLiveInfo($lesson_info['zn_cat_id']);
            if (!$detailInfo) $this->error("该直播不存在或者已下架");
//        $lesson_info['zc_pull_url'] =
//            "rtmp://live.zier21.com/zier21live/4_1_262?auth_key=1504548120-0-0-d6c7bde766fceec8a51a9b13f6095c3d";

            $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);
            $this->assign("teacherInfo", $teacherInfo);
            $this->assign("detailInfo", $detailInfo);
            $this->assign("lesson_info", $lesson_info);

            $this->countTime = 1000000;

            /////聊天人员配置
            $this->member_info = $this->model_member->apiGetInfo($member_id);
            ////当前在线人数
            $api = new liveApi();
            $playInfo = $api->DescribeLiveStreamOnlineUserNum($lesson_info['zc_stream_name']);
            $this->viewNum = object2array(json_decode($playInfo))['TotalUserNumber'];
        }
        else {
            if (!lq_is_login('student')) {
                $this->error('请先登录', U('/Index/index'));
            }
            $s = I("get.s", "");
            $id = end(explode("=", (think_ucenter_decrypt($s, C('SYS_ENCRYPT_PWD')))));

            if (!$id) $this->error("直播不存在或者已结束");

            ////课节信息
            $lesson_info = $this->model_lesson_live->find($id);
            if (!$lesson_info) $this->error("直播不存在或者已结束");
            $this->assign('id', $lesson_info['zn_cat_id']);


//        ////检测是否已报名
            if (!$this->model_enroll->is_member_enroll(session('student_auth')['id'], $lesson_info['zn_cat_id'], 1)) {
                $this->error('请先报名', U('/Live/livedetail', array('tnid' => $id)));
            }


            ////第几课时
            $lesson_info['lesson_now'] = $this->model_lesson_live->lessonNow($lesson_info['zn_cat_id']);
            ////检测是否已报名
            $member_id = session('student_auth')['id'];
            //////添加用户观看记录
            $this->model_record->addData($id, $member_id);

            $detailInfo = $this->model_live->getLiveInfo($lesson_info['zn_cat_id']);
            if (!$detailInfo) $this->error("该直播不存在或者已下架");
//        $lesson_info['zc_pull_url'] =
//            "rtmp://live.zier21.com/zier21live/4_1_262?auth_key=1504548120-0-0-d6c7bde766fceec8a51a9b13f6095c3d";

            $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);
            $this->assign("teacherInfo", $teacherInfo);
            $this->assign("detailInfo", $detailInfo);
            $this->assign("lesson_info", $lesson_info);

            $this->countTime = C('REQUEST_INTERVAL');

            /////聊天人员配置
            $this->member_info = $this->model_member->apiGetInfo($member_id);
            ////当前在线人数
            $api = new liveApi();
            $playInfo = $api->DescribeLiveStreamOnlineUserNum($lesson_info['zc_stream_name']);
            $this->viewNum = object2array(json_decode($playInfo))['TotalUserNumber'];
        }
//        pr($member_info);
        $this->display();
    }

    ////直播播放页
    public function replay()
    {
        if (!lq_is_login('student')) {
            $this->error('请先登录', U('/Index/index'));
        }
        $s = I("get.s", "");
        $live_lesson_id = end(explode("=", (think_ucenter_decrypt($s, C('SYS_ENCRYPT_PWD')))));
        // 判断是否存在
        if (!$live_lesson_id) $this->error("录播不存在,请重新尝试.");
        // 判断是有违规
        $lesson_info = $this->model_lesson_live->where('zl_visible = 1')->field('zc_title,zn_cat_id,zc_vod_url,id')->find($live_lesson_id);
        if (!$lesson_info) $this->error("回放不存在或涉嫌违规,请重新尝试");
        ////检测是否已报名
        if (!$this->model_enroll->is_member_enroll(session('student_auth')['id'], $lesson_info['zn_cat_id'], 1)) {
            $this->error('请先报名', U('/Live/livedetail', array('tnid' => $lesson_info['zn_cat_id'])));
        }
        // 获取信息
        $detailInfo = $this->model_live->getLiveInfo($lesson_info['zn_cat_id']);
        if (!$detailInfo) $this->error("该直播不存在或者已下架");
        $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);
        $this->assign('id', $lesson_info['zn_cat_id']);


        $url = C('ALI_API')['replay'] . $lesson_info['zc_vod_url']; // 播放地址
        $this->assign('url', $url);
        $this->assign('id', $lesson_info['zn_cat_id']);

        $this->assign("teacherInfo", $teacherInfo);
        $this->assign("detailInfo", $detailInfo);
        $this->assign("lesson_info", $lesson_info);
//
        $this->countTime = C('REQUEST_INTERVAL');

        $this->display();
    }

    /////学习时长 统计
    public function liveSum()
    {
        $lesson_id = I("post.lesson_id", "0");
        /////刷新当前会员某课节的观看时间  10秒
        $this->ajaxReturn($this->model_record->saveData($lesson_id, session('student_auth')['id'], C('REQUEST_INTERVAL')));
    }

    //////聊天(接口)
    public function talk()
    {
        $lesson_id = I("post.lesson_id", "");
        $talk = I("post.talk", "");
        if (!$lesson_id) {
            $this->ajaxReturn(array());
        }
        $talk_cache = new cacheTalk($lesson_id);
        $uid = session('student_auth')['id'] ? session('student_auth')['id'] : session('teacher_auth')['id']; // id
        $img = $this->model_member->apiGetFieldByID($uid, "zc_headimg"); // 头像
        if (!$img) {
            $img = NO_AVATAR;
        }
        $name = $this->model_member->apiGetFieldByID($uid); // 名字
        if ($talk) {
            $talk_cache->add_talk($uid, $img, $name, $talk);
        }
        $this->ajaxReturn($this->model_live->Chat_data($lesson_id, $uid));// 处理聊天信息
    }


}