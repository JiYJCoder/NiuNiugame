<?php

namespace Home\Controller;

use Home\Model\SearchModel;
use Think\Controller;
use Video\Api\liveApi;
use Member\Api\MemberApi as MemberApi;

defined('in_lqweb') or exit('Access Invalid!');


class SearchController extends PublicController
{
    private $member, $model_lesson_live, $D_SMS, $model_vod, $model_live, $model_lesson_vod, $model_favorite, $model_enroll;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();

        $this->model_member = new MemberApi;//实例化会员

        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->member = M('Member');

    }

    /**
     *   搜索首页 - 老师
     */
    public function index()
    {
        // 获取 - 搜索条件
           $kw = I('get.kw', '');
            $this->assign('kw',$kw);

            $teacher_info = $this->model_member->apiGetIdsByKeyword($kw);// 返回符合的id
            if(!$teacher_info){
                $count = 0;
            }else{
                $count = count($teacher_info);// 符合条件的总数
                $where_sql['id'] = array('IN', $teacher_info);// 设置条件 - 搜索id - 老师
                $field_sql = '`zc_nickname`,`zc_school`,`zc_headimg`,`id`';  // 老师字段

                $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['search']);//载入分页类
                $showPage = $page->show();
                $list = $this->member->where($where_sql)->field($field_sql)->limit("$page->firstRow , $page->listRows")->select();// 获取数据

                $model = new SearchModel();
                $result = $model->teacher_search($list);
            }
        $this->assign("page", $showPage);
        $this->assign("count", $count);
        $this->assign("teacher_search_info", $result);
        $this->display();
    }

    /**
     *   搜索 - 直播
     */
    public function search_live()
    {
        pr( return_cat());
        // 获取 - 搜索条件
        $kw = I('get.kw', '');
        $this->assign('kw',$kw);

        $live_info = $this->model_live->getIdsByKeyword($kw);// 返回符合的id
        $this->vod_num = count($this->model_vod->getIdsByKeyword($kw));

        if(!$live_info){
            $count = 0;
        }else{
            $count = count($live_info);// 符合条件的总数
            $where_sql['live.id'] = array('IN', $live_info);// 设置条件 - 搜索id - 直播
            $field_sql = 'live.zc_teacher_name,live.zc_image,live.zn_cat_id,live.zc_title,live.zn_enroll_num,live.zn_fav_num,live.id,member.zc_school';
            $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['search']);//载入分页类
            $showPage = $page->show();

            $list = $this->model_live
                ->alias('live')
                ->join('lq_member as member on member.id = live.zn_teacher_id')
                ->where($where_sql)
                ->field($field_sql)
                ->limit("$page->firstRow , $page->listRows")
                ->select();// 获取数据

            $model = new SearchModel();
            $result = $model->live_search($list);
        }

        $this->assign("page", $showPage);
        $this->assign("count", $count);
        $this->assign("live_search_info", $result);
        $this->display();

    }

    /**
     *   搜索 - 录播
     */
    public function search_vod()
    {
        // 获取 - 搜索条件
        $kw = I('get.kw', '');
        $this->assign('kw',$kw);
        $vod_info = $this->model_vod->getIdsByKeyword($kw);// 返回符合的id
        $this->live_num = count($this->model_live->getIdsByKeyword($kw)); // 直播符合条件数目

        if(!$vod_info){
            $count = 0;
        }else{
            $count = count($vod_info);// 符合条件的总数
            $where_sql['vod.id'] = array('IN', $vod_info);// 设置条件 - 搜索id - 直播
            $field_sql = 'vod.zc_teacher_name,vod.zc_image,vod.zn_cat_id,vod.zc_title,vod.zn_enroll_num,vod.zn_fav_num,vod.id,member.zc_school';
            $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['search']);//载入分页类
            $showPage = $page->show();

            $list = $this->model_vod
                ->alias('vod')
                ->join('lq_member as member on member.id = vod.zn_teacher_id')
                ->where($where_sql)
                ->field($field_sql)
                ->limit("$page->firstRow , $page->listRows")
                ->select();// 获取数据

            $model = new SearchModel();
            $result = $model->vod_search($list);
        }
//        pr($result);
        $this->assign("page", $showPage);
        $this->assign("count", $count);
        $this->assign("vod_search_info", $result);
        $this->display();
    }

    /*
     *   搜索  -  老师详情
     */
    public function teacherdetail()
    {
        if (!$this->lqgetid) $this->error("参数丢失");

        $teacherInfo = $this->model_member->apiGetTeacherInfo($this->lqgetid); // 获取老师信息
        if(!$teacherInfo){
            $this->error("参数丢失");
        }
        $teacherInfo['zc_good_at'] = explode(',',$teacherInfo['zc_good_at']); // 切割老师擅长
        $this->assign('teacher_msg',$teacherInfo);

        $sql = array(
            'Vod.zn_teacher_id' => $teacherInfo['id'], //老师id
            'Vod.zl_status' => array('in','1,6'), // 大课完结和更新
            'LessonVod.zl_visible' => 1,
        );
        $db = new SearchModel();
        // 录播分页
        $count = $this->model_lesson_vod
            ->alias('LessonVod')
            ->join('lq_vod AS Vod on LessonVod.zn_cat_id = Vod.id')
            ->where($sql)
            ->count(); // 查询全部录播小课的数目
        $this->assign('count',$count);
        $count_vod = $this->model_vod->where(array('zn_teacher_id'=>$teacherInfo['id'],'zl_status' => ['in','1,6']))->count();
        $page = new \LQLibs\Util\Page($count_vod, C("API_PAGESIZE")['teacher_detail_vod']);//载入分页类 (录播)
        $showPage = $page->show();
        $this->assign("page", $showPage);

        $vod_msg = $db->get_vod($teacherInfo['id'],$page->firstRow , $page->listRows);// 获取录播信息
        $this->assign('vod_msg',$vod_msg);

        // 直播分页
        $live_count = $this->model_live->where(array('zn_teacher_id'=>$teacherInfo['id']))->count();
        $page_live = new \LQLibs\Util\Page($live_count, C("API_PAGESIZE")['teacher_detail_live']);//载入分页类 (录播)
        $showPage_live = $page_live->show();
        $this->assign("page_live", $showPage_live);

        $live_msg = $db->get_live($teacherInfo['id'],$page_live->firstRow , $page_live->listRows); //获取直播信息
        $this->assign('live_msg',$live_msg);

//        pr($live_msg);
//        pr($teacherInfo);

        $this->display();
    }

}