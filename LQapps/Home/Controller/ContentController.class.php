<?php
/*
*** 学生控制面版模块
*/
namespace Home\Controller;

use Home\Model\StudentModel;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class ContentController extends PublicController
{

    private $D_SMS, $role, $model_live, $model_lesson_live, $model_lesson_cat, $model_vod, $model_lesson_vod, $model_favorite, $model_enroll, $model_live_record, $model_vod_record,$model_article;


    /** 初始化*/
    public function __construct()
    {
        parent::__construct();


//        $this->model_lesson_cat = D('LessonCat');
//        $this->model_vod = D("Vod");
//        $this->model_live = D("Live");
//        $this->model_lesson_vod = D("LessonVod");
//        $this->model_favorite = D("MemberFavorite");
//        $this->model_enroll = D("MemberEnroll");
//        $this->model_lesson_live = D("LessonLive");
//        $this->model_live_record = D("LiveRecord");
//        $this->model_vod_record = D("VodRecord");
        $this->model_article = D('Article');
        $this->assign('student_information', $this->login_member_info);// 显示学生基本信息
    }



    public function index(){
        $this->redirect('help');
    }
    /*
     * 关于我们
     */
    public function about(){
        $this->display();
    }

    /*
     * 帮助中心
     */
    public function help()
    {
        $article_list = F('article_cat','',COMMON_ARRAY);
        if(!$article_list){
            $this->error('出问题了,请稍后再试.');
        }
        $this->assign('article_list',$article_list);
        $where_sql['zl_visible'] = 1;

        $cat_id = I('get.cat_id','');
        if(!$cat_id){
            $cat_id = reset($article_list)['id'];// 没有数据就默认第一个
        }
        $where_sql['zn_cat_id'] = $cat_id;
        $this->assign('cat_id',$cat_id);
        foreach($article_list as $key => $value){
            if($value['id'] == $cat_id){
                $cat_name = $value['zc_caption'];//名字
            }
        }
        $this->assign('cat_name',$cat_name);

        // 统计总数
        $count = $this->model_article->where($where_sql)->count();
        $page = new \LQLibs\Util\Page($count,C("API_PAGESIZE")['help']);//载入分页类
        // 显示分页C("API_PAGESIZE")['help']
        $showPage = $page->show();
        $this->assign("page", $showPage);

            $msg = $this->model_article
                ->where($where_sql)
                ->field('zc_title,id,zn_cat_id')
                ->order('zn_sort asc,zn_cdate desc')
                ->limit($page->firstRow, $page->listRows)
                ->select();


//        pr($msg);
//        pr($article_list);
        $this->assign('article',$msg);

        $this->display();
    }

    /*
     * 帮助中心详细页面
     */
    public function detail()
    {
        if($_GET){
            $id = I('get.id');
            if(!$id) $this->error(L('SQLIVE_NO'));//没有id跳转
            $msg = $this->model_article
                ->where(array('id' => $id,'zl_visible' => 1))
                ->field('zc_title,zn_page_view,zc_content,zn_cat_id,zd_send_time,zn_page_view')
                ->find();
            if(!$msg) $this->error(L('SQLIVE_NO'));// 没有内容跳转

            $this->model_article->where('id='.$id)->setInc('zn_page_view');// 自己增加

            $article_list = F('article_cat','',COMMON_ARRAY);// 获取列表
            foreach($article_list as $key => $value){ // 获取当前名字
                if($value['id'] == $msg['zn_cat_id']){
                    $msg['zc_name'] = $value['zc_caption'];
                }
            }
            $msg['date'] = date('Y-m-d H:i',$msg['zd_send_time']); // 时间
            $this->assign('article_msg',$msg);
            $this->assign('article_list',$article_list);
            $this->display();
        }else{
            $this->error(L('SQLIVE_NO'));
        }

    }

    /*
     * 联系我们
     */
    public function contact()
    {
        $contact = F('set_config', '', COMMON_ARRAY)['CONTACTTEXT'];
        $this->assign('contact',lq_format_content($contact));
        $this->display();
    }





}