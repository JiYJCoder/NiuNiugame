<?php

namespace Home\Controller;

use Think\Controller;
use Video\Api\vodApi;
use Member\Api\MemberApi as MemberApi;
use Home\ORG\CacheVodTalk;

defined('in_lqweb') or exit('Access Invalid!');


class VodController extends PublicController
{
    private $model_lesson_cat,$cache, $model_lesson_live, $D_SMS, $model_vod, $model_live, $model_lesson_vod, $model_favorite, $model_enroll,$model_record;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();

        $this->model_member = new MemberApi;//实例化会员
        $this->cache = S(array('prefix'=>'vod_','expire'=>3600,));
        $this->D_SMS = D('Home/SmsLog');
        $this->model_lesson_cat = D('LessonCat');
        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->model_record = D("VodRecord");

    }

    // Vod首页
    public function index()
    {
        $page_parameter["s"] = $this->getSafeData('s');

        $this->reSearchPara($page_parameter["s"]);//反回搜索数
        // 设置/获取 - 默认搜索条件
        $search_content_array = array(
            'fid' => I('get.fid', '0', 'int'), // 大课
            'cat_id' => I('get.cat_id', '0', 'int'), // 小课
            'school' => I('get.school', '0', 'int'),
            'orderby' => I("get.orderby", '1', 'int')
        );
//        pr($search_content_array);
        $this->assign("search_content_array", $search_content_array);// 显示默认数据
        $this->cat = $this->__getLessonVodCat(0);// 获取一级分类和数量
        $where['Vod.zl_status'] = 6;// 默认状态6

        // 是否接收到fid数据
        if ($search_content_array['fid']) {
            $where['Vod.zn_fid'] = $search_content_array['fid'];
            $catList = $this->__getLessonVodCat($search_content_array['fid']);// 获取分类并且获取数量
            $this->fid_label = F('lesson_cat', '', COMMON_ARRAY)[$search_content_array['fid']]['zc_caption'];// 获取二级分类并且显示
            $catTotal = 0;
            foreach($catList as $cv)
            {
                $catTotal +=  $cv['total'];// 统计总数
            }
            $this->assign("catList",$catList);
        }
        // 是否接收到cat_id数据
        if ($search_content_array['cat_id']) {
            $where['Vod.zn_cat_id'] = $search_content_array['cat_id'];
        }
        // 是否接收到school数据
        if ($search_content_array['school']) {
            $ids = $this->model_member->apiGetIdsByKeyword($this->login_studnet_info['zc_school'],1);

            if(count($ids) > 0)
            {
                $where['Vod.zn_teacher_id'] = array("IN", $ids);
            } else {
                $where['Vod.zn_teacher_id'] = array("EQ", 0);
            }
            //$where['Vod.zn_teacher_id'] = array("IN",$ids);
        }
        // 排序条件
        $orderby = array(
            1 => "Vod.zn_fav_num DESC",
            2 => "Vod.zn_fav_num ASC",
            3 => "Vod.zn_enroll_num DESC",
            4 => "Vod.zn_enroll_num ASC",
            5 => "Vod.zn_cdate DESC",
            6 => "Vod.zn_cdate ASC",
        );
        $this->assign("orderby", $search_content_array['orderby']);
        //首页设置
        $page_config = array(
            'where' => $where,
            'order' => $orderby[$search_content_array['orderby']],
        );

        $db = D("VodView");
        $count = count($db->where($page_config["where"])->order($page_config["order"])->select());// 统计总条目数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['live']);//载入分页类
        $list = $db->where($page_config["where"])->order($page_config["order"])->limit("$page->firstRow , $page->listRows")->select();// 获取数据
//pr($list);
        foreach ($list as $key => $val) {
            $list[$key]["zc_image"] = $val['zc_image'] ? API_DOMAIN . $val['zc_image'] : NO_PICTURE;
            $list[$key]['count'] = $this->model_lesson_vod->getCount('zn_cat_id='.$val['id']);
        }
        if(!$catTotal) $catTotal = $count;

        $ad = D("AdPosition")->getAdPositionById(9)["list"]; // id=9是录播广告
        foreach($ad as $key => $value){
           if($key == 0) $ad_list = $value;// 广告
        }
        $showPage = $page->show();
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->assign("catTotal",$catTotal);
        $this->assign("ad",$ad_list);
        $this->display();
    }

    /////录播介绍页
    public function videodetail()
    {

        if (!$this->lqgetid) $this->error("参数丢失");

        $detailInfo = $this->model_vod->getLessonInfo($this->lqgetid);
        if (!$detailInfo) $this->error("该录播不存在或者已下架");

        $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);
//        pr($teacherInfo);
//        pr($detailInfo);
        $this->assign("teacherInfo", $teacherInfo);
        $this->assign("detailInfo", $detailInfo);


        $this->display();
    }

    /*
 * 播放检测页
 * 检测：1 是否已报名
 *      2  是否已有信号
 */
    public function vodCheck()
    {
        $teacher_id = I("post.teacher_id", "", "int");
        $vod_id = I("post.vod_id", "", "int");
        $vod_lesson_id = I("post.vod_lesson_id", "", "int");

        if(!session('student_auth')['id']){
            $this->ajaxReturn(array("status" => 2,"msg" => "请先登录"));
        }

        ////检测是否已报名
        if (!$this->model_enroll->is_member_enroll(session('student_auth')['id'], $vod_id, 2)) {
            $reData['msg'] = '抱歉,您尚未报名该课程';
            $this->ajaxReturn($reData);
        }

        $reData = array(
            "status" => 0,
            "msg" => "视频信息丢失，请稍候重试...",
        );
        if (!$teacher_id || !$vod_id || !$vod_lesson_id) $this->ajaxReturn($reData);

        $urlParams = "lesson_id=" . $vod_lesson_id;
        $url = U("/Vod/broadcast", array("s" => think_ucenter_encrypt($urlParams, C('SYS_ENCRYPT_PWD'))));
        $reData['status'] = 1;
        $reData['msg'] = '正在进入录播间...';
        $reData['url'] = $url;
        $this->ajaxReturn($reData);
    }


    ////录播播放页
    public function broadcast()
    {
        // 登录判断
        if (!lq_is_login('student')) {
            $this->error('请先登录',U('/Index/index'));
        }
        $s = I("get.s", "");
        $id = end(explode("=", (think_ucenter_decrypt($s, C('SYS_ENCRYPT_PWD')))));

        if (!$id) $this->error("录播不存在,请重新尝试.");

        ////课节信息
        $lesson_info = $this->model_lesson_vod->where('zl_visible = 1')->field('zc_title,zn_cat_id,zc_vod_info,id as vod_id')->find($id);
        if (!$lesson_info) $this->error("录播不存在或涉嫌违规,请重新尝试");

        ////检测是否已报名
        $member_id = session('student_auth')['id'];
        if (!$this->model_enroll->is_member_enroll($member_id, $lesson_info['zn_cat_id'], 2)) {
            $this->error("抱歉,您尚未报名该课程",U('/Vod/videodetail',array('tnid' => $lesson_info['zn_cat_id'])));
        }
        $this->assign('id',$lesson_info['zn_cat_id']);

        //////添加用户观看记录
        $this->model_record->addData($id, $member_id);

        $detailInfo = $this->model_vod->getVodInfo($lesson_info['zn_cat_id']);
        if (!$detailInfo) $this->error("该录播不存在或者已下架");

//        $rsObj = object2array(json_decode($this->aliLive->aliApi($apiParams)));
        // 获取录播视频id
        $vod_info = end(explode('|||',$lesson_info['zc_vod_info']));

        $vod = new vodApi();
        $vodInfo = $vod->GetPlayInfo($vod_info);

        $vodInfo = object2array(json_decode($vodInfo));

        $playurl = $vodInfo['PlayInfoList']['PlayInfo'][1]['PlayURL']; // 播放地址
        $teacherInfo = $this->model_member->apiGetTeacherInfo($detailInfo['zn_teacher_id']);//老师信息


//        pr($vod_info);
//        pr($vodInfo);
//        pr($lesson_info);

        $this->assign("vodInfo", $vodInfo);
        $this->assign("playurl", $playurl);
        $this->assign("teacherInfo", $teacherInfo);
        $this->assign("detailInfo", $detailInfo);
        $this->assign("lesson_info", $lesson_info);

        $this->countTime = C('REQUEST_INTERVAL');

        /////聊天人员配置
        $this->member_info = $this->model_member->apiGetInfo($member_id);
//        pr($member_info);
        $this->display();
    }


    /////学习时长 统计
    public function vodSum()
    {
        $lesson_id = I("post.lesson_id", "0");
        /////刷新当前会员某课节的观看时间  10秒
        $this->ajaxReturn($this->model_record->saveData($lesson_id, session('student_auth')['id'], C('REQUEST_INTERVAL')));
    }

    //////聊天(接口)
    public function talk()
    {
        $lesson_id = I("post.lesson_id","");
        $talk = I("post.talk","");
        if(!$lesson_id ) {
            return false;
        }
        $talk_cache = new cacheVodTalk($lesson_id);
//        $uid = session('student_auth')['id']; // id
        $uid = 15;
        $img = $this->model_member->apiGetFieldByID($uid,"zc_headimg"); // 头像
        if(!$img){
            $img = NO_AVATAR;
        }
        $name = $this->model_member->apiGetFieldByID($uid); // 名字
        if($talk){
            $talk_cache->add_talk($uid, $img, $name, $talk);
        }
        $this->ajaxReturn($this->model_vod->Chat_data($lesson_id,$uid));// 处理聊天信息
    }

    /////异步获取播放url
    public function getPlayUrl()
    {
        $vodID = I("get.vod_id","");

    }

}