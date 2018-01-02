<?php

namespace Home\Controller;

use Home\Model\StudentModel;
use Think\Controller;
use Video\Api\liveApi;
use Member\Api\MemberApi as MemberApi;

defined('in_lqweb') or exit('Access Invalid!');


class IndexController extends PublicController
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

    }

    //PC首页
    public function index()
    {
        // 整理第一级数据
        $lesson_msg_first = array(
            'field' => '`zc_caption`,`id`',
            'order' => 'zn_sort asc,zn_cdate asc',
            'where' => 'zn_fid = 0',
        );
        $res = $this->model_lesson_cat->lq_getmessage($lesson_msg_first, 2);
        foreach ($res as $key => $value) {
            //  整理第二级数据
            $lesson_msg_second = array(
                'where' => "zn_fid=" . $value['id'],
                'field' => "`zc_caption`,`id`",
                'order' => 'zn_sort asc,zn_cdate asc',
            );
            $sec = $this->model_lesson_cat->lq_getmessage($lesson_msg_second, 5);
            $res[$key]["DRs"] = $sec;
            foreach ($sec as $k => $v) {
                // 整理第三级数据
                $lesson_msg_third = array(
                    'where' => array(
                        "zn_cat_id" => $v['id'],
                        "zl_status" => 6, // 上线的录播
                    ),
                    'field' => "`zc_teacher_name`,`zc_title`,`zc_image`,`id`,`zn_teacher_id`,`zc_summary`",
                );
                $thr = $this->model_vod->vodList($lesson_msg_third, 10);
                $res[$key]["DRs"][$k]["thr"] = $thr;
            }
        }

        // 首页 - 今天直播预告
        $where_sql = array(
            "Live.zl_status" => 6,
            "LessonLive.zc_date" => date("Y-m-d"),// 今天
            "LessonLive.zc_start_time" => array('egt', date("H:i")),
        );
        $rs = D('LessonLiveView')->where($where_sql)->order('LessonLive.zc_start_time asc')->limit(10)->select();

        // 首页 - 大图幻灯片
        $ad_big = D("AdPosition")->getAdPositionById(4)["list"]; // id=4是首页大图幻灯片

        // 首页 - 轮播广告
        $ad = D("AdPosition")->getAdPositionById(6)["list"]; // id=6是轮播广告
        // 首页 - 判断是否登录
        if (lq_is_login('student')) {
            $student_id = session('student_auth')['id'];
            $db = new StudentModel();
            $student_message = $db->get_index_student_info($student_id);  // 登录后获取数据
            $this->assign('student_message', $student_message);// 登录为1
        }
//        pr($student_message);
        $this->assign('lesson_message', $res);
        $this->assign('today_live', $rs);
        $this->assign('ad', $ad);
        $this->assign('ad_big', $ad_big);
        $this->assign('seoData', $this->getSeoData());

        $this->display();

    }


}