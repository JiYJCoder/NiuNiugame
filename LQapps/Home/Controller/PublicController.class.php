<?php
/*
描述：公共文件
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
*/

namespace Home\Controller;

use LQPublic\Controller\Base;
use Member\Api\MemberApi as MemberApi;
use Attachment\Api\AttachmentApi as AttachmentApi;

defined('in_lqweb') or exit('Access Invalid!');

class PublicController extends Base
{
    public $lqgetid, $lqpostid, $set_config, $model_member, $login_member_info, $nav_active;

    public function __construct()
    {
        parent::__construct();
        header("Access-Control-Allow-Origin:*");

        $this->lqgetid = isset($_GET["tnid"]) ? intval($_GET["tnid"]) : 0;
        $this->lqpostid = isset($_POST["fromid"]) ? intval($_POST["fromid"]) : 0;
        $this->set_config = F('set_config', '', COMMON_ARRAY);
        if ($this->set_config["WEB_DOMAIN"] == '') {
            $this->set_config["WEB_DOMAIN"] = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        }
        $this->SET_CONFIG["SEO_COPYRIGHT"] = html_entity_decode($this->set_config["SEO_COPYRIGHT"]);
        $this->set_config["A_LOGO"] = '<a title="' . $this->set_config["WEB_ITEMSNAME"] . '" href="' . U("/") . '"><img src="' . $this->set_config["WEB_LOGO"] . '" border="0" alt="' . $this->set_config["WEB_ITEMSNAME"] . '"/></a>';

        $this->assign('empty_msg', '<div id="null_record">暂无数据</div>');
        $this->assign("SET_CONFIG", $this->set_config);//基本设置
        $this->model_member = new MemberApi;//实例化会员
        $this->nav_active = ' active';
        $nav_now = ACTION_NAME . "_active";
        $this->assign($nav_now, $this->nav_active);
        if (session('teacher_auth')["id"]) $this->assign("teacher_login", session('teacher_auth')["id"]);
        if (session('student_auth')["id"]) $this->assign("student_login", session('student_auth')["id"]);
        $this->assign("seoData", $this->getSeoData());// 网站头部信息(meta)
        $this->commonAssign();//页面公共标签赋值
        $this->auto_check_live();// 自动清除超出直播时间的直播
    }

    //获得seo数据
    protected function getSeoData($data = array())
    {
        $seo_data = array();
        $seo_data["title"] = $data["title"] == '' ? $this->set_config["SEO_TITLE"] : $data["title"];
        $seo_data["keywords"] = $data["keywords"] == '' ? $this->set_config["SEO_KEYWORDS"] : $data["keywords"];
        $seo_data["description"] = $data["description"] == '' ? $this->set_config["SEO_DESCRIPTION"] : $data["description"];
        return $seo_data;
    }

    //页面公共标签赋值
    public function commonAssign()
    {
        //过滤直接访问public
        if (CONTROLLER_NAME == 'Public') $this->redirect('home/index/index');
        $this->assign("lesson_info", $this->get_lesson_cat());// 头部数据
        if(lq_is_login('teacher')){
            $this->login_teacher_info = $this->model_member->apiGetInfo(session('teacher_auth')["id"]);
            $this->assign("member_info_teacher", $this->login_teacher_info);
        }
        if(lq_is_login('student')){
            $this->login_studnet_info = $this->model_member->apiGetInfo(session('student_auth')["id"]);
            $this->assign("member_info_student", $this->login_studnet_info);

        }
        //头部菜单
    }

    //web用户认证**************************************************
    protected function checkLogin($type = 2)
    {
        $auth = $type == 2 ? "teacher" : "student";
        if (!lq_is_login($auth)) {
            if ($type == 2) $this->redirect("/Teacher/login");
            else $this->redirect("/");
        } else {
            //会员信息
            $this->login_member_info = $this->model_member->apiGetInfo(session($auth . '_auth')["id"]);
            $this->assign("member_info", $this->login_member_info);
        }
    }
    ######################页面#####################
    //404页面  :
    public function p404()
    {
        $data = array(
            'title' => '对不起，您访问的地址不存在或网站过期  - ' . $this->set_config["WEB_ITEMSNAME"],
            'url' => U('home/index/index'),
            'step' => 5
        );
        $this->assign("data", $data);//页面数据
        $lcdisplay = 'Public/404';//引用模板
        $this->display($lcdisplay);
    }

    //上传操作 - 单文件
    protected function opUpload($key = 'image', $type = 'images')
    {
        ob_end_clean();
        //file表单控件名
        $file_widget = $_FILES[$key];
        if ($file_widget['size'] == 0) {
            return array('status' => 0, 'msg' => '提交失败:上传的文件不存在或为空', "url" => "");
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->rootPath = './' . C("UPLOAD_PATH")["folder"];//文件上传保存的根路径
        $upload->maxSize = C("UPLOAD_MAX_SIZE")[$type];// 设置附件上传大小
        $upload->exts = C("UPLOAD_EXT")[$type];// 设置附件上传类型
        $upload->savePath = C("UPLOAD_PATH")["list"][$type];//上传目录
        $upload->subName = array('date', 'Ymd');//上传目录
        if ($upfile_info = $upload->uploadOne($file_widget)) {// 上传错误提示错误信息
            $Attachment = new AttachmentApi;
            $lc_table = "attachment";
            $lc_folder_path = $upload->rootPath . $upfile_info["savepath"];
            $lc_folder_path = substr($lc_folder_path, 1);
            $upfile_data = array(
                'zn_uid' => intval($this->login_member_info["id"]),
                'zc_account' => $this->login_member_info["zc_account"],
                'zc_table' => $lc_table,
                'zc_controller' => CONTROLLER_NAME,
                'zn_type' => in_array($upfile_info["ext"], array('jpg', 'gif', 'png', 'jpeg')) ? 0 : 1,
                'zn_user_type' => 2,
                'zc_original_name' => str_replace("." . $upfile_info["ext"], "", $upfile_info["name"]),
                'zc_sys_name' => $upfile_info["savename"],
                'zc_folder_path' => $lc_folder_path,
                'zc_file_path' => $lc_folder_path . $upfile_info["savename"],
                'zc_suffix' => strtolower($upfile_info['ext']),
                'zn_size' => $upfile_info["size"],
                'zc_folder' => $type,
                'zn_day' => strtotime(C("LQ_TIME_DAY")),
                'zn_cdate' => NOW_TIME
            );
            $lnLastInsID = $Attachment->insertAttachment($lc_table, $upfile_data);
            return array('status' => 1, 'msg' => '上传成功', "url" => $upfile_data["zc_file_path"]);

        } else {
            return array('status' => 0, 'msg' => '上传失败:' . $upload->getError(), "url" => "");
        }
    }

    /////获取分类并且获取数量
    public function __getLessonCat($cat_id,$table = 'LiveView',$where)
    {
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            $db = D($table);
            $preTable = "Vod";
            $preCat = 'LessonVod';
            if($table == 'LiveView')
            {
                $preTable = "Live";
                $preCat = 'LessonLive';
            }

            if ($val['zn_fid'] == $cat_id) {
                $where[$preTable.'.zn_cat_id'] = $val['id'];
                $liveNum = count($db->where($where)->group($preCat.".zn_cat_id")->select());

                $cat_arr[] = array("id" => $val['id'], "title" => $val['zc_caption'], "total" => intval($liveNum));
            }
        };
        return $cat_arr;
    }

    public function __getLessonVodCat($cat_id)
    {
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == $cat_id) {
                $where['zn_cat_id'] = $val['id'];
                $where['zl_status'] = 6;
                $liveNum = M("Vod")->where($where)->count();

                $cat_arr[] = array("id" => $val['id'], "title" => $val['zc_caption'], "total" => intval($liveNum));
            }
        };
        return $cat_arr;
    }

    // 获取课程分类(已经分开大课和小课)
    public function get_lesson_cat()
    {
        $list = F('lesson_cat', '', COMMON_ARRAY);
        $big_lesson = array();
        foreach($list as $key => $value) {
            if($value['zn_fid'] == 0) {
                $big_lesson[] = $list[$key];
            }
        }
        foreach($list as $key => $value) {
            foreach($big_lesson as $k => $val){
                if($val['id'] == $value['zn_fid']){
                    $big_lesson[$k]['small_lesson'][] = $list[$key];
                }
            }
        }
        return $big_lesson;
    }

    /*
     * 自动检查直播是否完成
     */
    public function auto_check_live()
    {
        $list = M('LessonLive')->field('`id`,`zc_date`,`zc_end_time`')->select();
        foreach($list as $key => $value)
        {
            if(time() > (strtotime($value['zc_date'] . $value['zc_end_time']) + 1000 )){
                $id_set[] = $value['id'];
            }
        }
        $condition['id'] = array("IN",$id_set); // 未直播id
        $data['zl_status'] = -1; // -1未直播
        M('LessonLive')->where($condition)->save($data);


        $temp = M('LessonLive')->field('`zn_cat_id`')->group('zn_cat_id')->where('zl_status = 0')->select();
        foreach($temp as $key => $value){
            $cat_id_set[] = $value['zn_cat_id'];
        }
//        pr($temp);
        $live_condition['id'] = array("not in",$cat_id_set);
        $live_data['zl_status'] = 1; // 1直播完结
        M('Live')->where($live_condition)->save($live_data);


    }
}

?>