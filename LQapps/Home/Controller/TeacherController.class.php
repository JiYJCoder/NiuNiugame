<?php
/*
 * 老师控制面版模块
*/
namespace Home\Controller;

use Think\Controller;
use Video\Api\liveApi;
use Video\Api\vodApi;
use Video\Api\ossApi;

defined('in_lqweb') or exit('Access Invalid!');

class TeacherController extends PublicController
{
    private $D_SMS, $role, $model_auth, $model_live, $model_enroll,$model_favorite, $model_Apply, $model_vod, $model_lesson_live, $model_lesson_vod, $model_oss;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->D_SMS = D("SmsLog");//短信实例化
        $this->role = 2;
        //$this->model_member->apiLoginSession(943);
        $action_no_login_array = array('login', 'register_step1', 'register_step2', 'register_step3', 'register_step4', 'register_step5', 'register_step_sub', 'send_verify', 'getgrowinfo');
        if (!in_array(ACTION_NAME, $action_no_login_array)) {
            self::checkLogin();
        }

        $this->model_auth = D("MemberAuth");
        $this->model_live = D("Live");
        $this->model_vod = D("Vod");
        $this->model_Apply = D("LessonApply");
        $this->model_lesson_live = D("LessonLive");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_oss = D("OssFile");
        $this->model_enroll = D("MemberEnroll");
        $this->model_favorite = D("MemberFavorite");

    }

    /*
     * 老师 - 老师首页
     */
    public function index()
    {
        // sql where条件
        $sqlwhere_parameter = array(
            "zn_teacher_id" => $this->login_member_info['id'],
            "zl_status" => array("IN", "3,5,6"),
        );
        // sql搜索条件, 字段/where条件/排序
        // 字段有 zn_cat_id  二级课程,  zc_title  课题标题, zc_image  课程图片, zl_status  课程状态
        $page_config = array(
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_title`,`zc_image` as image,`zl_status`,`zc_reason`",
            'where' => $sqlwhere_parameter,
            'order' => 'zl_status desc,zn_cdate asc',
        );
        $message = $this->model_live->lqList(0, 2, $page_config);
        // 判断是否有课程
        if (empty($message)) {
            // 没有课程就是0
            $this->assign('has_Mylive', 0);
        } else {
            $this->assign('has_Mylive', 1);
            $this->assign("message", $message);
        }

        // sql where - 录播 - vod
        $vod_where_sql = array(
            "zn_teacher_id" => $this->login_member_info['id'],
            "zl_status" => array("IN", "3,4,6"),
        );
        $vod_sql = array(
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_title`,`zc_image` as image,`zl_status`,`zc_reason`",
            'where' => $vod_where_sql,
            'order' => 'zl_status desc,zn_cdate asc',
        );
        $vod_message = $this->model_vod->recordedList(0, 5, $vod_sql);
        // 判断是否有录播
        if ($vod_message) {
            $this->assign('has_vod', 1);
            $this->assign("vod_message", $vod_message);
        } else {
            // 没有录播就是0
            $this->assign('has_vod', 0);
        }
//        pr($message);
//        pr($vod_message);
        $this->display();
    }

    /*
    * 老师 - 注册步骤一
    */
    public function register_step1()
    {
        $this->display();
    }

    /*
    * 老师 - 注册步骤二
    */
    public function register_step2()
    {
        $this->display();
    }

    /*
    * 老师 - 注册步骤三
    */
    public function register_step3()
    {
        $this->display();
    }

    /*
   * 老师 - 注册步骤四
   */
    public function register_step4()
    {
        $this->display();
    }

    /*
   * 老师 - 注册步骤五
   */
    public function register_step5()
    {
        // 从session中拿id并且获取会员信息
        $memberInfo = $this->model_member->apiGetInfo(session('teacher_auth')['id']);
        $this->assign("memberInfo", $memberInfo);
        $this->display();
    }

    /*
    * 老师 - 个人资料
    */
    public function myset()
    {
        $seoData['title'] = '个人设置';
        $this->assign("seoData", $this->getSeoData($seoData));

        ////擅长领域
        if ($this->login_member_info['zc_good_at']) $this->goodAt = explode(",", $this->login_member_info['zc_good_at']);
        // 查询身份证
        $memberIdCard = $this->model_auth->lqGetField("zn_member_id=" . $this->login_member_info['id'], "zc_idcard");
        $this->assign('memberIdCard', $memberIdCard);
        $this->display();
    }

    /*
     * 老师 - 身分认证
     */
    public function certification()
    {
        $seoData['title'] = '个人身份认证';
        $this->assign("seoData", $this->getSeoData($seoData));

        $this->authInfo = $this->model_auth->lqGetField("zn_member_id=" . $this->login_member_info['id'], "*");
        $this->display();
    }


    // **********************直播*************************
    /*
    * 直播 - 判断是否直播完成
    */
    public function is_live_done($id)
    {
        // 条件
        $data['zn_teacher_id'] = $id;
        $data['zl_status'] = 6; // 状态是直播中

        $live_id = $this->model_live->lqGetField_select($data, '`id`');
        foreach ($live_id as $key => $value) {
            $where_sql['zn_cat_id'] = $value['id'];// 获取id
            $where_sql['zl_status'] = 0; // 0是未直播, 1是已经直播完
            $no_live_num = $this->model_lesson_live->getCount($where_sql, '`id`');// 获取未直播的数量
            if ($no_live_num == 0) {
                // 已经直播完但是状态还是直播中 -> 更新状态
                $live_data['id'] = $value['id'];
                $live_data['zl_status'] = 1;// 改变状态为1(完结)
                $live_data['zn_mdate'] = time(); //当前时间
                $this->model_live->save($live_data);
            }
        }
    }

    /*
     * 直播 - 申请直播
     */
    public function sqlive()
    {
        // 只能同时存在2个未结课,获取生效中的直播课(不符合要求的用户禁止访问这个页面)
        $sql = array(
            "zn_teacher_id" => $this->login_member_info['id'],
            "zl_status" => array("IN", "4,5,6"),
        );
        $no_end_lesson = $this->model_live->getCount($sql);
        if ($no_end_lesson > 1) {
            $this->error(L('SQLIVE_TIP'));// 如果大于2节报错
        }

        // 判断是否已经通过验证
        $is_pass = $this->model_auth->isAuthOk($this->login_member_info['id']);
        if (!$is_pass) {
            $this->error(L('SQLIVE_PASS'));
        }
        // 筛选出一级分类的名称
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == 0) $parentCat[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };
        $this->assign("parentCat", $parentCat);
        $this->assign('mylive_active', $this->nav_active);
        $this->display();
    }

    /*
    * 直播 - 我的直播
    */
    public function mylive()
    {
        // 设置标题
        $seoData['title'] = '我的直播';
        // 审核状态: 1 => "完结", 2 => "管理员下架", 3 => "审核未通过", 4 => "审核中", 5 => "审核通过", 6 => "上线状态"
        $sqlwhere_parameter = array(
            "zn_teacher_id" => $this->login_member_info['id'],
        );
        // sql搜索条件, 字段/where条件/排序
        $page_config = array(
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_title`,`zc_image` as image,`zl_status`,`zc_reason`",
            'where' => $sqlwhere_parameter,
            'order' => 'zl_status desc,zn_cdate asc',
        );
        // 统计总数
        $count = $this->model_live->alias("p")->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['live_list']);//载入分页类
        // 显示分页
        $showPage = $page->show();
        $this->assign("page", $showPage);
        // 调用Model方法 lqList列模板
        $message = $this->model_live->lqList($page->firstRow, $page->listRows, $page_config);
        if (empty($message)) {
            // 没有课程就是0
            $this->assign('has_Mylive', 0);
        } else {
            $this->assign('has_Mylive', 1);
            $this->assign("message", $message); // 直播信息
//                    pr($message);
        }

        // 只能同时存在2个未结课,获取生效中的直播课
        $sql = array(
            "zn_teacher_id" => $this->login_member_info['id'],
            "zl_status" => array("IN", "4,5,6"),
        );
        $no_end_lesson = $this->model_live->getCount($sql);

        $this->assign("no_end_lesson", $no_end_lesson);
        $this->assign("seoData", $this->getSeoData($seoData));
        $this->display();
    }

    /*
    * 直播 - 修改课程资料
    */
    public function revisecourse()
    {
        // 设置标题
        $seoData['title'] = '修改课程资料';
        // 筛选出一级分类的名称
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == 0) $parentCat[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };
        $this->assign("parentCat", $parentCat);
        // 获取id
        $id = I('id', '', int);
        // 写where查询条件
        $sql_where = array(
            'id' => $id,
            "zn_teacher_id" => $this->login_member_info['id'],
        );
        // 写完整查询信息
        $sql = array(
            'where' => $sql_where,
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_title`,`zc_image` as image,`zl_status`,`zn_fid`,`zc_summary`",
        );
        //查询id的课程和当前用户
        $message = $this->model_live->lq_getmessage($sql);
        // 查询课程表
        $where_sql = array(
            'zl_status' => 0,
            'zn_cat_id' => $id,
        );
        $sql = array(
            "field" => '`id`,`zc_title`,`zc_date`,`zc_start_time`,`zc_end_time`',
            "order" => 'zc_date asc,zc_start_time asc',
            "where" => $where_sql,
        );

        // 获取课程表相应的字段信息
        $message2 = $this->model_lesson_live->lq_getmessage($sql);

        $this->assign('mylive_active', $this->nav_active);
        $this->assign('message', $message);
        $this->assign('message2', $message2);
        $this->assign('sqlive_id', $id);
        $this->assign("seoData", $this->getSeoData($seoData));
        $this->display('sqlivechange');
    }

    /*
     * 直播 - 完善课程资料
     */
    public function completecourse()
    {
        // 设置标题
        $seoData['title'] = '完善课程资料';
        // 筛选出一级分类的名称
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == 0) $parentCat[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };
        $this->assign("parentCat", $parentCat);
        // 获取id
        $id = I('id', '', int);
        // 写where查询条件
        $sql_where = array(
            'id' => $id,
            "zn_teacher_id" => $this->login_member_info['id'],
        );
        // 写完整查询信息
        $sql = array(
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_content`,`zc_title`,`zc_image` as image,`zl_status`,`zn_fid`,`zc_summary`,`zc_file`",
            'where' => $sql_where,
        );
        //查询id的课程和当前用户
        $message = $this->model_live->lq_getmessage($sql);
        // 查询课程表
        $data = array(
            'zl_status' => 0,
            'zn_cat_id' => $id,
        );
        $lesson_sql = array(
            'where' => $data,
            'order' => 'zc_date asc,zc_start_time asc',
            'field' => '`zc_title`,`zc_date`,`zc_start_time`,`zc_end_time`,`id`,`zc_file`',
        );
        // 获取课程表相应的字段信息
        $message2 = $this->model_lesson_live->lq_getmessage($lesson_sql);
        $ossData = array(
            "zn_lesson_type" => 2, // 类型2为其他
            "zn_uid" => $this->login_member_info['id'], // 老师id
            "zn_fid" => $message['id'], // 课程id
            'id' => array("IN", $message['zc_file']),
        );
        $oss_sql = array(
            'field' => '`zc_file_name`,`id`',
            'where' => $ossData,
            'order' => 'zn_cdate asc',
        );

        // 获取附件表相应的字段信息
        $message3 = $this->model_oss->lq_getmessage($oss_sql);
        $this->assign('message', $message);
        $this->assign('message2', $message2);
        $this->assign('message3', $message3);
        $this->assign("seoData", $this->getSeoData($seoData));
        $this->assign('mylive_active', $this->nav_active);
        $this->display();
    }

    // *********************录播************************
    /*
    * 录播 - 录播申请
    */
    public function sqbroadcast()
    {
        // 判断是否已经通过验证
        $is_pass = $this->model_auth->isAuthOk($this->login_member_info['id']);
        if (!$is_pass) {
            $this->error(L('SQLIVE_PASS'));
        }
        // 判断是否可以申请录播
        $vod_count = $this->model_vod->where(array('zn_teacher_id' => $this->login_member_info['id'], 'zl_status' => 6))->count();
        if ($vod_count > 5) {
            $this->error(L('SQLIVE_VOD_TIP'));
        }

        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == 0) $parentCat[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };
        $this->assign("parentCat", $parentCat);
        $this->assign('myrecorded_active', $this->nav_active);
        $this->display();
    }

    /*
     * 录播 - 我的录播
     */
    public function myrecorded()
    {
        // 判断是否可以申请录播
        $vod_count = $this->model_vod->where(array('zn_teacher_id' => $this->login_member_info['id'], 'zl_status' => 6))->count();
        if ($vod_count < 5) {
            $this->assign('vod_count', 1);
        }

        // 设置标题
        $seoData['title'] = '我的录播';
        // 具体的where搜索条件
        $sqlwhere_parameter = array(
            "zn_teacher_id" => $this->login_member_info['id'],
        );
        // sql搜索条件, 字段/where条件/排序
        // 字段有 zn_cat_id  二级课程,  zc_title  课题标题, zc_image  课程图片, zl_status  课程状态 zc_expect_lesson预计上传多少节课
        $page_config = array(
            'field' => "`id`,`zn_cat_id`,`zn_teacher_id`,`zc_reason`,`zc_title`,`zc_expect_lesson`,`zc_image` as image,`zl_status`",
            'where' => $sqlwhere_parameter,
            'order' => 'zl_status desc,zn_cdate desc',
        );
        // 统计总数
        $count = $this->model_vod->alias("p")->where($sqlwhere_parameter)->count();
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['live_list']);//载入分页类
        // 显示分页
        $showPage = $page->show();
        $this->assign("page", $showPage);
        // 调用Model方法 recordedList列模板
        $message = $this->model_vod->recordedList($page->firstRow, $page->listRows, $page_config);
        if (empty($message)) {
            // 没有课程就是0
            $this->assign('has_myrecorded', 0);
        } else {
            $this->assign('has_Myrecorded', 1);
            $this->assign("message", $message);
        }
        $this->assign("seoData", $this->getSeoData($seoData));
        $this->display();
    }

    /*
    * 录播 - 修改录程资料
    */
    public function sqbroadchange()
    {
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == 0) $parentCat[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };

        $id = I('id', '1', int);
        // 整理sql - vod
        $vod_sql = array(
            'where' => array(
                'id' => $id,
                'zn_teacher_id' => $this->login_member_info['id'],
            ),
            'field' => '`zn_fid`,`zn_cat_id`,`zc_title`,`zc_image`,`zc_expect_lesson`,`zc_summary`',
        );
        // 获取数据
        $voddata = $this->model_vod->vod_getmessage($vod_sql);

        // 整理数据 - lesson_vod
        $lesson_vod_sql = array(
            "zl_visible" => 1,// 1为显示
            "zn_cat_id" => $id,
        );
        $lesson_vod_field = '`id`,`zc_title`';

        $vod_message = $this->model_lesson_vod->lqGetField_select($lesson_vod_sql, $lesson_vod_field);

        $this->assign("parentCat", $parentCat);
        $this->assign("vod_id", $id);
        $this->assign("voddata", $voddata);
        $this->assign("vod_message", $vod_message);
        $this->assign('myrecorded_active', $this->nav_active);
        $this->display();
    }

    /*
    *  录播 - 完善录播资料
    */
    public function myrecorded_complete()
    {
        $id = I("id", ''); // 获取录播id
        // 写where查询条件
        $sql_where = array(
            'id' => $id,
            "zn_teacher_id" => $this->login_member_info['id'],
        );
        // 写完整查询信息 - vod
        $sql = array(
            'field' => "`id`,`zn_fid`,`zn_cat_id`,`zc_title`,`zc_image`,`zc_summary`,`zc_content`,`zc_file`,`zc_expect_lesson`",
            'where' => $sql_where,
        );
        //查询录播数据
        $vod_message = $this->model_vod->vod_getmessage($sql);
        // 查询录播视频表 - lesson_vod
        $data = array(
            "zn_teacher_id" => $this->login_member_info['id'],
            'zn_cat_id' => $id,
            "zl_visible" => 1,//1是显示
        );
        $vod_sql = array(
            'where' => $data,
            'order' => 'zn_cdate desc',
            'field' => '`zc_title`,`id`',
        );
        // 获取课程表相应的字段信息
        $lesson_vod_message = $this->model_lesson_vod->lq_getmessage($vod_sql);

        $ossData = array(
            "zn_lesson_type" => 2, // 类型2为其他
            "zn_uid" => $this->login_member_info['id'], // 老师id
            "zn_fid" => $id, // 录播id
            'id' => array("IN", $vod_message['zc_file']),
        );
        $oss_sql = array(
            'field' => '`zc_file_name`,`zc_original_name`,`id`',
            'where' => $ossData,
            'order' => 'zn_cdate asc',
        );
//         获取附件表相应的字段信息
        $vod_oss_message = $this->model_oss->lq_getmessage($oss_sql);
//        pr($lesson_vod_message);
        $this->assign('id', $id);
        $this->assign('vod_message', $vod_message);
        $this->assign('lesson_vod_message', $lesson_vod_message);
        $this->assign('vod_oss_message', $vod_oss_message);
        $this->assign('myrecorded_active', $this->nav_active);
        $this->display();
    }

    /*
     * 直播/录播 - 查看学生名单
     */
    public function courselist()
    {
        $lesson_id = I("get.id", "0");
        $type = I("get.type", "1");
        if (!$lesson_id) $this->error("参数错误...");

        if ($type == 1) {
            $lessonInfo = $this->model_live->field('zn_fid,id,zn_cat_id,zc_title')->find($lesson_id); // 课程信息(直播
            $this->assign('mylive_active', $this->nav_active); // 模板焦点
        } else {
            $lessonInfo = $this->model_vod->field('zn_fid,id,zn_cat_id,zc_title')->find($lesson_id); // 课程信息(录播
            $this->assign('myrecorded_active', $this->nav_active); // 模板焦点
        }

        $this->assign("lessonInfo", $lessonInfo);

        $cat = F('lesson_cat', '', COMMON_ARRAY); // 分类信息
        $this->catInfo = $cat[$lessonInfo['zn_fid']]['zc_caption'] . " -> " . $cat[$lessonInfo['zn_cat_id']]['zc_caption'];

        $sqlStr = array(
            "zn_type" => $type,// 1直播 2录播
            "zn_object_id" => $lesson_id
        );
        $db = D("EnrollView");
        $count = $db->where($sqlStr)->count();


        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['course_list']);//载入分页类
        // 显示分页
        $showPage = $page->show();
        $this->assign("page", $showPage);

        $courseList = $db->where($sqlStr)->order("zn_cdate desc")->limit($page->firstRow, $page->listRows)->select();

        if ($type == 1) {
            $model_live_record = D("LiveRecord");
        } else {
            $model_live_record = D("VodRecord");
        }

        foreach ($courseList as $key => $val) {
            $courseList[$key]['enroll_date'] = date("Y-m-d", $val['zn_cdate']);
            $courseList[$key]['view_total'] = lq_format_sec(intval($model_live_record->getCount($val['zn_member_id'], 0, $lesson_id)));
            if ($type == 1) {
                $lesson_total = $this->model_lesson_live->getCount("zn_cat_id=" . $lesson_id);/////当前课程总课节
            } else {
                $lesson_total = $this->model_lesson_vod->getCount("zn_cat_id=" . $lesson_id);/////当前课程总课节
            }


            $view_total = $model_live_record->getNumCount($val['zn_member_id'], $lesson_id);////会员参与课节

            $courseList[$key]['view_percent'] = round($view_total / $lesson_total, 2) * 100;
        }
//pr($courseList);
        $this->assign("list", $courseList);
        $this->display();
    }


    /*********************************************异步请求接口 S ************************************************/

    // **********************老师**********************
    /*
     * 老师 - 登录
     */
    public function login()
    {
        if (IS_POST) {
            $type = I("post.type", "1", "int");//1 帐号密码 3 手机

            $returnArray = array('status' => 1, 'msg' => C('ALERT_ARRAY')["loginSuccess"]);
            if ($type == 1) {
                $username = I("post.username", '');//帐号
                $password = I("post.password", '');//密码

                $mid = $this->model_member->apiLogin($username, $password, $this->role, 1);
            } elseif ($type == 3) {
                $mobile = I("post.mobile", '');//手机号码
                $check_code = I("get.check_code", '');//手机验证码
                if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,手机号码不正确！'));
                ///半小时内允许发送三次
                if (!$this->D_SMS->isAllowReceive($mobile, 'login')) {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，不能频繁请求操作！'));
                }

                if (!$this->D_SMS->isEffective($mobile, 'login', $check_code)) {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,验证码无效！'));
                }

                $mid = $this->model_member->apiLogin($mobile, $check_code, $this->role, 3);
            }

            if ($mid > 0) { //老师登录成功
                // 自动更改完结直播状态
                $this->is_live_done($mid['id']);
                $data = $this->model_member->apiLoginSession($mid);//注册session
                $this->model_member->addMemberLog("login");//写入日志

            } else { //登录失败
                switch ($mid) {
                    case -1:
                        $error = ':用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:
                        $error = ':密码错误！';
                        break;
                    case -3:
                        $error = ":已超系统登陆限定“" . C("WEB_SYS_TRYLOGINTIMES") . "”尝试次数，请在" . (intval(C("WEB_SYS_TRYLOGINAFTER")) / 3600) . "小时后再尝试等陆。<br>" . $this->systemMsg;
                        break;
                    default:
                        $error = ':未知错误！';
                        break;
                }
                $returnArray = array('status' => 0, 'msg' => C('ALERT_ARRAY')["loginFail"] . $error);
            }
            $this->ajaxReturn($returnArray);
        } else {
            //已登陆的,不能停留在此页面。
            $seoData["seo_title"] = '注册 | 登录';
            $this->assign("seoData", $this->getSeoData($seoData));

            if (lq_is_login('teacher')) {
                $this->redirect('index');
            }


            $this->display();
        }
    }

    /*
     * 老师 - 退出登录
    */
    public function loginOut()
    {
        $this->model_member->addMemberLog('loginOut', $this->login_member_info);//插入会员日志
        $this->model_member->apiLoginOut();
//        $this->success('退出成功', U('login'), 1);
        $this->ajaxReturn(array("status"=>1,"msg"=>'退出登录成功',"url"=>U('login')));
    }

    /*
     * 老师 - 个人资料更新
     */
    public function myset_update()
    {
        if (IS_POST) {
            $data = array();
            $data["id"] = $this->login_member_info["id"];

            $headimg = I("post.headimg", "");
            if ($headimg) {
                $save = save_base64_img($headimg);
                if ($save) $data["zc_headimg"] = $save;
            }

            $data['zc_intro'] = I("post.intro");
            $data['zc_good_at'] = I("post.good_at");
            $data["zn_mdate"] = NOW_TIME;
            $mid = $this->model_member->apiSaveMember($data);

            if (intval($mid) > 0) {
                $this->model_member->addMemberLog('edit_member', $this->login_member_info);//添加日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '资料修改成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    /*
     * 老师 - 认证提交
     */
    public function auth_update()
    {
        if (IS_POST) {
            $data = array();
            $data["zn_member_id"] = $this->login_member_info["id"];

            $cert_img = I("post.cert_img", "");
            $idcard_img = I("post.idcard_img", "");
            if ($cert_img) {
                $save = save_base64_img($cert_img, 'auth');
                if ($save) $data["zc_cert_img"] = $save;
            }
            if ($idcard_img) {
                $save = save_base64_img($idcard_img, 'auth');
                if ($save) $data["zc_idcard_img"] = $save;
            }

            $idcard = I("post.idcard", "");
            if ($idcard) {
                if (!validation_filter_id_card($idcard)) $this->ajaxReturn(array('status' => 0, 'msg' => '身份证号码不正确，请重新输入...'));
                $data['zc_idcard'] = $idcard;
            }

            ////无提交任何资料
            if (!$cert_img && !$idcard_img && !$idcard) $this->ajaxReturn(array('status' => 0, 'msg' => '资料提交失败，请重试...'));

            $isAuth = $this->model_auth->isAuth($this->login_member_info['id']);
            if ($isAuth) {
                $data['id'] = $isAuth;
                $data["zn_mdate"] = NOW_TIME;
                $mid = $this->model_auth->saveData($data);
            } else {
                $data['zn_cdate'] = NOW_TIME;
                $mid = $this->model_auth->addData($data);

            }

            if (intval($mid) > 0) {
                $this->model_member->addMemberLog('auth', $this->login_member_info);//添加日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '认证资料提交成功，我们会尽快审核...'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '资料提交失败，请重试...'));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    /*
     * 老师 - 获取月份数据
     */
    public function link_data()
    {
        if (IS_POST) {
            $teacher_id = $this->login_member_info['id']; // 老师id
            $type = I('post.type', '1', 'int'); // 1直播 2录播
            $course_id = I('post.course_id', ''); // 课程id
            $where_sql = [
                "zn_type" => $type, //  1直播 2录播
                "zn_object_id" => $course_id, // 课程id
                "zn_teacher_id" => $teacher_id, // 老师id
            ];
            $mouth = date('Y-m');// 年月
            $days = date("t"); // 当月多少天


            $enroll = 0;
            $fav = 0;

            for ($i = 1; $i <= $days; $i++) {
                $start_time = strtotime($mouth . '-' . $i); // 获取时间范围上限
                $end_time = strtotime('next day', $start_time);// 获取时间范围下限
                $where_sql['zn_cdate'] = array('between', array($start_time, $end_time)); // 条件 时间
                $list_enroll[$i] = $this->model_enroll->where($where_sql)->count(); // 统计当天报名人数
                $list_fav[$i] = $this->model_favorite->where($where_sql)->count(); // 统计当天收藏人数
            }
//            pr($list_enroll);
//            pr($list_fav);
            $this->ajaxReturn(array('status' => 1, 'msg_enroll' =>$list_enroll ,'msg_fav'=>$list_fav));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }


    }

    // *********************成长平台****************************
    /*
     * 成长平台 - 提交注册信息
    */
    public function register_step_sub()
    {
        // 老师数据同步成长平台，接口获取 若没有数据，重新注册
        if (IS_POST) {
            C('TOKEN_ON', false);
            $check_code = I("post.verify_code", "");
            $mobile = I("post.mobile", "");
            $type = I("post.type", "1");
            if ($type == 2) {
                if (!$this->D_SMS->isEffective($mobile, 'login', $check_code) || !$check_code) {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '验证码无效'));
                }
            }
            $nickname = I("post.nickname", ""); // 昵称
            $zc_school = I("post.school", ""); // 任职学校
            $password = I("post.password", ""); // 密码
            $idcard = I("post.idcard", "");// 身份证
            $avatar = I("post.headimg", ""); // 头像
            if ($avatar) $save = curl_get_pic($avatar); // 判断是否有头像
            else $save = NO_AVATAR;

            $data = array();
//            $data["__hash__"] = I("post.hash", '');//表单认证
            $data["zl_role"] = $this->role;
            $data["zc_account"] = $mobile;
            $data["zc_idcard"] = $idcard;
            $data["zc_mobile"] = $mobile;
            $data["zc_password"] = $password;
            $data["zc_school"] = $zc_school;
            $data["zc_nickname"] = $nickname;
            $data["zc_headimg"] = $save;
            $data["zl_account_bind"] = 0;
            $data["zl_openid_bind"] = 0;
            $data["zl_mobile_bind"] = 1;
            $data["zl_email_bind"] = 0;
            $data["zl_sex"] = 0;
            $data["zl_visible"] = 1;
            $data["zn_login_times"] = 1;
            $data["zn_last_login_ip"] = get_client_ip(1);
            $data["zn_last_login_time"] = NOW_TIME;
            $data["zn_trylogin_times"] = 0;
            $data["zn_trylogin_lasttime"] = NOW_TIME;

            $mid = $this->model_member->apiRegister($data);

            if (preg_match('/^([1-9]\d*)$/', $mid)) {
                $data["id"] = $mid;
                $this->model_member->apiLoginSession($mid);//注册session
                $this->model_member->addMemberLog('register', $data);//插入会员日志
                /////更新短信使用状态
                M()->execute("update `__PREFIX__sms_log` set zl_use = 1 where zc_mobile='" . $mobile . "' and zc_action='register'");
                $this->ajaxReturn(array('status' => 1, 'msg' => '会员注册成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    /*
    * 成长平台 - 数据请求
    */
    public function getGrowInfo()
    {
        $client_id = I("get.client_id", ""); // 成长平台用户帐号
        $secret = I("get.password", ""); // 成长平台用户密码
        $info = $this->model_member->apiGetGrowInfo($client_id, $secret, $this->role);// $role: 1学生,2老师

        if ($info['status'] == 0) {
            $returnData = array(
                "status" => 0,
                "data" => ""
            );
        } else {
            $returnData = array(
                "status" => $info['status'],
                "data" => array(
                    "password" => $secret,
                    "accout" => $client_id,
                    "nickname" => $info['data']['user_nicename'],
                    "mobile" => $info['data']['mobile'],
                    "school" => $info['data']['school_name'],
                    "headimg" => $info['data']['avatar']
                )
            );
        }
        $this->ajaxReturn($returnData);
    }

    /*
     * 成长平台 - 发送手机验证码
     */
    public function send_verify()
    {
        $mobile = I("get.mobile", "");
        $check_code = lq_random_string(6, 1);//随机码
        $tempId = 'SMS_11490043';

        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,手机号码不正确！'));
        ///半小时内允许发送三次
        if (!$this->D_SMS->isAllowReceive($mobile, 'login')) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，不能频繁请求操作！'));
        }
        ///发送验证码
        $sms_data = lqSendSms($mobile, $check_code, $tempId);
        if ($sms_data["status"] == 1) {
            $this->D_SMS->addSms('login', $mobile, $check_code);
            $this->ajaxReturn(array('status' => 1, 'msg' => '短信发送成功', "code" => $check_code));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，短信发送失败，请重试...！'));
        }
    }

    // **************************直播****************************
    /*
     * 直播 - 申请直播课程
     */
    public function sqlive_sub()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $fid = I('post.fid', "0");
            $cat_id = I('post.cat_id', "0");
            $title = I('post.title', "");
            $image = I('post.image', "");
            $summary = I('post.summary', "");

            if ($image) {
                $image = save_base64_img($image, 'image');
            }
            lq_test($image);
            // 判断课程简历是否为空
            if ($summary == '') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '请填写课程简介'));
            }

            $liveData = array(
                "zn_fid" => $fid,
                "zn_cat_id" => $cat_id,
                "zc_title" => $title,
                "zl_status" => 4, // 状态
                "zc_image" => $image,
                "zc_summary" => $summary,
                "zn_teacher_id" => $this->login_member_info['id'],
                "zc_teacher_name" => $this->login_member_info['zc_nickname'] // 老师名称
            );

            $live_id = $this->model_live->addData($liveData);

            if (intval($live_id) > 0) {
                $lesson = I("post.lesson");
                $api = new liveApi();
                foreach ($lesson as $lk => $lv) {
                    $addData = array();
                    $addData['zn_cat_id'] = $live_id;
                    $addData['zc_title'] = $lv['lesson_title'];
                    $addData['zc_date'] = $lv['lesson_date'];
                    $addData['zc_start_time'] = $lv['lesson_start_time'];
                    $addData['zc_end_time'] = $lv['lesson_end_time'];

                    $lesson_id = $this->model_lesson_live->addData($addData);

                    if ($lesson_id) {
                        $streamName = $this->login_member_info['id'] . "_" . $live_id . "_" . $lesson_id;
                        $expireTime = strtotime($lv['lesson_date'] . $lv['lesson_end_time']) + C('ALI_API')['expireTime'];
                        ////推流,拉流地址
                        $pushUrl = $api->getPushSteam($streamName, $expireTime);
                        $pullUrl = $api->getPullSteam($streamName, $expireTime);

                        $updata = array(
                            "id" => $lesson_id,
                            "zc_stream_name" => $streamName,
                            "zc_push_url" => $pushUrl,
                            "zc_pull_url" => $pullUrl
                        );
                        $this->model_lesson_live->save($updata);
                    }
                }
                $this->model_member->addMemberLog('sqlive_sub', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '课程申请成功，请等待管理员审核'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $live_id));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '课程申请失败，请重试...'));
        }
    }

    /*
     *  直播 - 修改直播课程
     */
    public function sqlive_change()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $id = I('post.id', "0");
            $fid = I('post.fid', "0");
            $cat_id = I('post.cat_id', "0");
            $title = I('post.title', "");
            $image = I('post.image', "");
            $summary = I('post.summary', "");
            if ($image) {
                $image = save_base64_img($image, 'image');
                $liveData["zc_image"] = $image;
            }
            // 判断课程简历是否为空
            if ($summary == '') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '请填写课程简介'));
            }
            $liveData["id"] = $id;
            $liveData["zn_fid"] = $fid;
            $liveData["zn_cat_id"] = $cat_id;
            $liveData["zc_title"] = $title;
            $liveData["zl_status"] = 4;
            $liveData["zc_summary"] = $summary;
            $liveData["zn_mdate"] = time();
            $liveData["zn_teacher_id"] = $this->login_member_info['id'];

            $live_id = $this->model_live->save($liveData);

            if (intval($live_id) > 0) {
                $lesson = I("post.lesson");

                // 判断是否有添加课程
                if ($lesson) {
                    foreach ($lesson as $key => $value) {
                        // 获取数据
                        $zc_title = $value['lesson_title'];
                        $zc_date = $value['lesson_date'];
                        $zc_start_time = $value['lesson_start_time'];
                        $zc_end_time = $value['lesson_end_time'];
                        // 判断是否有新增课程
                        if (!$value['id']) {
                            $upData = array(
                                "zn_cat_id" => $id, //所属课程
                                "zc_title" => $zc_title, // 标题
                                "zc_date" => $zc_date, // 日期
                                "zc_start_time" => $zc_start_time, // 开始时间
                                "zc_end_time" => $zc_end_time, // 结束时间
                            );
                            $lesson_id = $this->model_lesson_live->addData($upData);

                            // 获取推流拉流地址
                            if ($lesson_id) {
                                $api = new liveApi();
                                $streamName = $this->login_member_info['id'] . "_" . $live_id . "_" . $lesson_id;
                                $expireTime = strtotime($zc_date . $zc_end_time) + C('ALI_API')['expireTime'];
                                ////推流,拉流地址
                                $pushUrl = $api->getPushSteam($streamName, $expireTime);
                                $pullUrl = $api->getPullSteam($streamName, $expireTime);

                                $updata = array(
                                    "id" => $lesson_id,
                                    "zc_push_url" => $pushUrl,
                                    "zc_pull_url" => $pullUrl
                                );
                                $this->model_lesson_live->save($updata);
                            }
                        } else {
                            $upData = array(
                                "id" => $value['id'], // id
                                "zn_cat_id" => $id, //所属课程
                                "zc_title" => $zc_title, // 标题
                                "zc_date" => $zc_date, // 日期
                                "zc_start_time" => $zc_start_time, // 开始时间
                                "zc_end_time" => $zc_end_time, // 结束时间
                                "zn_mdate" => time(), //修改时间
                            );
                            $lesson_id = $this->model_lesson_live->save($upData);
                        }
                    }

                }
                $this->model_member->addMemberLog('sqlive_change', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '课程修改成功，请等待管理员审核', 'url' => U('teacher/index')));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $live_id));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '课程修改失败，请重试...'));
        }

    }

    /*
     *  直播 - 修改课程资料 - 删除课程表
     */
    public function live_del_course()
    {
        $where_sql['id'] = I('post.id');// 获取id
        $res = $this->model_lesson_live->where($where_sql)->delete();
        if ($res) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败，请重试...'));
        }
    }

    /*
     *   直播 -  完善直播课程
     */
    public function sqlive_complete()
    {
        if (IS_POST) {
            // 获取数据 判断是否添加课程
            C('TOKEN_ON', false);
            $id = I('post.id', "0"); // 课程id
            $zc_content = I('post.zc_content', "");// 课程描述

            // 课程描述不能为空 进行判断
            if ($zc_content == '') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '课程描述不能为空，请重新填写...'));
            }

            // 整理数据
            $liveData = array(
                "zl_status" => 6,// 状态上线
                "zc_content" => $zc_content, // 描述
                "zn_mdate" => time(),
            );
            $where_sql = array(
                "id" => $id,
                "zn_teacher_id" => $this->login_member_info['id'], // 老师id
            );
            $live_id = $this->model_live->where($where_sql)->save($liveData); // save()返回值是影响的记录数，需要用恒等来判断结果。
            // 判断是否插入成功
            if ($live_id < 1) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '课程修改失败，请重试...'));
            }
            // 获取数据
            $lesson = I('post.lesson', '');

            // 判断是否有添加课程
            if ($lesson) {
                foreach ($lesson as $key => $value) {
                    // 获取数据
                    $zc_title = $value['lesson_title'];
                    $zc_date = $value['lesson_date'];
                    $zc_start_time = $value['lesson_start_time'];
                    $zc_end_time = $value['lesson_end_time'];
                    // 判断是否有新增课程
                    if (!$value['id']) {
                        $upData = array(
                            "zn_cat_id" => $id, //所属课程
                            "zc_title" => $zc_title, // 标题
                            "zc_date" => $zc_date, // 日期
                            "zc_start_time" => $zc_start_time, // 开始时间
                            "zc_end_time" => $zc_end_time, // 结束时间

                        );
                        $lesson_id = $this->model_lesson_live->addData($upData);

                        // 获取推流拉流地址
                        if ($lesson_id) {
                            $api = new liveApi();
                            $streamName = $this->login_member_info['id'] . "_" . $id . "_" . $lesson_id;
                            $expireTime = strtotime($zc_date . $zc_end_time) + C('ALI_API')['expireTime'];
                            ////推流,拉流地址
                            $pushUrl = $api->getPushSteam($streamName, $expireTime);
                            $pullUrl = $api->getPullSteam($streamName, $expireTime);

                            $updata = array(
                                "id" => $lesson_id,
                                "zc_push_url" => $pushUrl,
                                "zc_pull_url" => $pullUrl,
                                "zc_stream_name" => $streamName,
                            );
                            $this->model_lesson_live->save($updata);
                        }
                    } else {
                        $api = new liveApi();
                        $streamName = $this->login_member_info['id'] . "_" . $id . "_" . $lesson_id;
                        $expireTime = strtotime($zc_date . $zc_end_time) + C('ALI_API')['expireTime'];
                        ////推流,拉流地址
                        $pushUrl = $api->getPushSteam($streamName, $expireTime);
                        $pullUrl = $api->getPullSteam($streamName, $expireTime);

                        $upData = array(
                            "id" => $value['id'], // id
                            "zn_cat_id" => $id, //所属课程
                            "zc_title" => $zc_title, // 标题
                            "zc_date" => $zc_date, // 日期
                            "zc_start_time" => $zc_start_time, // 开始时间
                            "zc_end_time" => $zc_end_time, // 结束时间
                            "zn_mdate" => time(), //修改时间
                            "zc_push_url" => $pushUrl, // 推流
                            "zc_pull_url" => $pullUrl, // 拉流
                            "zc_stream_name" => $streamName,
                        );

                        $lesson_id = $this->model_lesson_live->save($upData);
                    }
                }
            }
            $this->model_member->addMemberLog('sqlive_complete', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '完善课程成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '完善课程失败，请重试...'));
        }
    }

    /*
     * 直播 - 申请结束课程
     */
    public function end_apply()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            // 获取课程id
            $lesson_id = I('post.lesson_id', "", 'int');

            $data['zn_lesson_id'] = $lesson_id;
            $data['zn_member_id'] = $this->login_member_info['id'];

            // 判断是否在审核
            if ($this->model_Apply->getCount($data) > 0) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '请不要重复申请'));
            }

            // 完善数据
            $vodData = array(
                "zn_lesson_id" => $lesson_id,// 课程id
                "zn_member_id" => $this->login_member_info['id'],// 操作的人
                "zn_cdate" => time(),// 当前时间
                "zn_type" => 1// 1是直播
            );
            $insertID = $this->model_Apply->addData($vodData);
            if (intval($insertID) > 0) {
                $this->model_member->addMemberLog('cancel_lesson', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '申请成功，请等待管理员审核'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $insertID));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败，请重试...'));
        }
    }

    // **********************录播**************************

    /*
     *  录播 - 完善录播资料
     */
    public function recorded_complete()
    {
        if (IS_POST) {
            // 获取数据 判断是否添加课程
            C('TOKEN_ON', false);
            $id = I('post.id', "0"); // 视频id
            $zc_content = I('post.zc_content', "");// 课程描述

            // 课程描述不能为空 进行判断
            if (!$zc_content) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '描述不能为空，请重新填写...'));
                exit();
            }
            // 整理数据 - vod
            $liveData = array(
                "zl_status" => 6,// 状态上线
                "zc_content" => $zc_content, // 描述
                "zn_mdate" => time(),//时间
            );
            $where_sql = array(
                "id" => $id,
                "zn_teacher_id" => $this->login_member_info['id'], // 老师id
            );
            $live_id = $this->model_vod->where($where_sql)->save($liveData);
            // 判断是否保存成功
            if ($live_id < 1) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '完善课程失败，请重试...'));
            }

            $this->model_member->addMemberLog('sqlive_complete', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '完善课程成功', 'url' => U('teacher/myrecorded')));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '完善课程失败，请重试...'));
        }
    }

    /*
    * 录播 - 完善资料 - 取消视频上传
    */
    public function del_vod_upload()
    {
        // 获取并整理数据
        $id = I("post.id", "0");
        $data['id'] = $id;
        $data['zn_teacher_id'] = $this->login_member_info['id'];
        $res = $this->model_lesson_vod->lqGetField($data, '`zc_vod_info`');
        // 判断 验证数据
        if ($res) {
            $vod_id = end(explode('|||', $res));
            // 删除阿里云的视频
            $vodApi = new vodApi();
            $vodApi->DeleteVideo($vod_id);
            // 删数据库数据
            $this->model_lesson_vod->where($data)->delete();
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败'));
        }
    }

    /*
     * 录播 - 视频上传接口
     */
    public function apiUpload()
    {
        $api = new vodApi();
        $title = I("get.file_title");
        $filename = I("get.file_name");
        $filesize = I("get.file_size");

        $upload = $api->CreateUploadVideo($title, $filename, $filesize);
        $upload = object2array(json_decode($upload));
        if ($upload['VideoId']) {
            $this->ajaxReturn(array("status" => 1, "msg" => $upload));
        } else  $this->ajaxReturn(array("status" => 0, "msg" => $upload));
    }

    /*
     * 录播 - 视频上传完成后插入数据库
     */
    public function vod_insert()
    {
        // 获取数据
        $UploadAddress = I("post.UploadAddress");
        $UploadAuth = I("post.UploadAuth");
        $VideoId = I("post.VideoId");
        $zn_cat_id = I("post.zn_cat_id", "0"); // 录播课程id
        $zc_title = I("post.zc_title", "");// 上传视频的名称
        // 整理数据
        $addData['zn_teacher_id'] = $this->login_member_info['id'];
        $addData['zn_cat_id'] = $zn_cat_id;
        $addData['zc_title'] = $zc_title;
        $addData['zc_vod_info'] = $UploadAddress . "|||" . $UploadAuth . "|||" . $VideoId;
        $addData['zc_vod_id'] = $VideoId;
        // 插入数据

        $res = $this->model_lesson_vod->addData($addData);
        if ($res) {
            $this->ajaxReturn(array("status" => 1, 'msg' => '成功', "val_id" => $res));
        } else {
            $this->ajaxReturn(array("status" => 0, "msg" => '失败'));
        }
    }

    /*
     * 录播 - 视频信息提交
     */
    public function sqbroadcast_sub()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $fid = I('post.fid', "0");
            $cat_id = I('post.cat_id', "0");
            $title = I('post.title', "");
            $image = I('post.image', "");
            $summary = I('post.summary', "");
            $exp_lesson = I('post.exp_lesson', "0", 'int');
            if ($image) {
                $image = save_base64_img($image, 'image');
            }
            // $summary课程介绍不能为空 进行判断
            if ($summary == '') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '录播简介不能为空，请重新填写...'));
                exit();
            }
            $vodData = array(
                "zn_fid" => $fid,
                "zn_cat_id" => $cat_id,
                "zc_title" => $title,
                "zc_image" => $image,
                "zl_status" => 4,
                "zc_summary" => $summary,
                'zc_expect_lesson' => $exp_lesson,
                "zc_teacher_name" => $this->login_member_info['zc_nickname'], // 老师名称
                "zn_teacher_id" => $this->login_member_info['id']
            );

            $insertID = $this->model_vod->addData($vodData);

            if (intval($insertID) > 0) {
                $lesson = I("post.lesson");
                if ($lesson) {
                    foreach ($lesson as $lk => $lv) {
                        $addData = array();
                        $addData['zn_cat_id'] = $insertID;
                        $addData['zc_title'] = $lv['lesson_title'];
                        $addData['zn_teacher_id'] = $this->login_member_info['id'];
                        $addData['zc_vod_info'] = $lv['UploadAddress'] . "|||" . $lv['UploadAuth'] . "|||" . $lv['VideoId'];
                        $this->model_lesson_vod->addData($addData);
                    }
                }
                $this->model_member->addMemberLog('sqbroadcast_sub', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '申请成功，请等待管理员审核'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $insertID));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败，请重试...'));
        }
    }

    /*
    * 录播 - 视频信息修改
     */
    public function sqbroadcast_change()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $id = I('post.id', "0");
            $fid = I('post.fid', "0");
            $cat_id = I('post.cat_id', "0");
            $title = I('post.title', "");
            $image = I('post.image', "");
            $summary = I('post.summary', "");
            $exp_lesson = I('post.exp_lesson', "0", 'int');
            // $summary课程介绍不能为空 进行判断
            if ($summary == '') {
                $this->ajaxReturn(array('status' => 0, 'msg' => '录播简介不能为空，请重新填写...'));
                exit();
            }
            $where_sql = array(
                'id' => $id,
                "zn_teacher_id" => $this->login_member_info['id']
            );
            if ($image) {
                $image = save_base64_img($image, 'image');
                $vodData = array(
                    "zn_fid" => $fid,
                    "zn_cat_id" => $cat_id,
                    "zc_title" => $title,
                    "zc_image" => $image,
                    "zn_mdate" => time(), // 修改时间
                    "zl_status" => 4,
                    "zc_summary" => $summary,
                    'zc_expect_lesson' => $exp_lesson,
                );
            } else {
                $vodData = array(
                    "zn_fid" => $fid,
                    "zn_cat_id" => $cat_id,
                    "zn_mdate" => time(),// 修改时间
                    "zc_title" => $title,
                    "zl_status" => 4,
                    "zc_summary" => $summary,
                    'zc_expect_lesson' => $exp_lesson,
                );
            }
            $insertID = $this->model_vod->where($where_sql)->save($vodData);
            if (intval($insertID) > 0) {
                $this->model_member->addMemberLog('sqbroadcast_sub', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '修改成功，请等待管理员审核', 'url' => U('teacher/myrecorded')));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $insertID));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '修改失败，请重试...'));
        }
    }

    /*
     * 录播 - 停止更新(接口)
     */
    public function delrecorded()
    {
        $id = I('post.del_id'); // 要停止更新的录播id
        $where_data['id'] = $id;
        $where_data['zn_teacher_id'] = $this->login_member_info['id'];
        $data['zl_status'] = 1;
        $data['zn_mdate'] = time();
        $res = $this->model_vod->where($where_data)->save($data);
        if ($res) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '停止更新成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '停止更新失败，请重试...'));
        }
    }

    // **********************直播/录播************************
    /*
     * 直播/录播 附件上传接口(只能是单文件)
     */
    public function file_upload()
    {

        // 获取数据
        $zn_fid = I('post.fid_id', ''); // 主课程id
        $zn_lesson_id = I('post.lesson_id', ''); //课节id
        $zn_lesson_type = I('post.type', ''); // type = 1 是ppt或pptx(区分小课件还是其他)
        $zc_file_name = I('post.file_name', ''); // 文件名称
        $zn_type = I('post.zn_type', '1'); // 1是直播,2是录播
        $localPath = RUNTIME_PATH . 'Oss/';
        // 判断是否目录存在(不存在就创建
        if (!is_dir($localPath)) {
            mkdir($localPath);
        };
        $upload = new \Think\Upload();
        $upload->maxSize = 100 * 1024 * 1024;// 设置附件上传大小 100M
//        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        if ($zn_lesson_type == 1) $upload->exts = array('ppt', 'pptx');// 设置附件上传类型
        $upload->rootPath = $localPath; // 设置附件上传根目录
        $upload->saveName = array('uniqid', '');
        $info = $upload->upload();
        if (!$info) {
            $data = array(
                "status" => 0,
                "msg" => '上传失败,请重试...'
            );
            $this->ajaxReturn($data);
        } else {// 上传成功
            $upload = new ossApi();
        }
        try {
            // 遍历忽略名字
            foreach ($info as $key => $val) {
                $object = date('Y-m-d') . '/' . $val['savename'];//想要保存文件的名称
                $file = $localPath . $val['savepath'] . $val['savename'];//文件路径，必须是本地的。
                $zc_suffix = $val['ext'];// 文件后缀类型
                $zn_size = $val['size'];// 文件大小
                $zc_original_name = $val['name']; // 课程原来的名称
            }
            // 文件上传
            $reData = $upload->ossUpload($object, $file);// 返回1表示成功上传

            if ($reData['status'] == 1) {
                $url = $reData['data'];
            } else {
                $this->ajaxReturn($reData);
            }
            // 判断是否有上传的名字
            if (!$zc_file_name) {
                $zc_file_name = $zc_original_name;
            }
            // 整理数据,准备入库
            $data1 = array(
                "zn_uid" => $this->login_member_info['id'], // 上传用户id
                "zc_nickname" => $this->login_member_info['zc_nickname'], // 上传老师姓名
                "zn_lesson_type" => $zn_lesson_type, //   文件类别:1是ppt-2是其他
                "zn_fid" => $zn_fid,// 主课程id
                "zn_lesson_id" => $zn_lesson_id, // 课节id
                "zc_file_path" => $url, // url地址
                "zc_suffix" => $zc_suffix, // 文件后缀类型
                "zn_size" => $zn_size, // 文件大小
                "zn_cdate" => NOW_TIME, // 记录当前时间
                "zc_original_name" => $zc_original_name, // 课程原来的名称
                "zc_object" => $object, // oss上的名称
                "zc_file_name" => $zc_file_name, // 文件名称
                "zn_type" => $zn_type,// 1是直播 2 是录播
            );

            $new_id = $this->model_oss->addData($data1);
            unlink($file);
            // 判断是否插入成功
            if ($new_id < 1) {
                $this->ajaxReturn(array("status" => 0, "msg" => '数据插入错误'));
            }
            if ($zn_lesson_type == 1) {
                // 传入的是ppt  整理数据
                $msg['id'] = $zn_lesson_id;// 课节id
                // 要先判断表lq_lesson_live 里面是否有课件字段
                $res = $this->model_lesson_live->lqGetField($msg, 'zc_file');
                // 如果有就要删除原先的os文件
                if ($res != null) {
                    $this->model_oss->getAndDelObj($res);
                }
                $msg['zc_file'] = $new_id; // 课件id
                $msg['zc_file_name'] = $object; // oss名称
                $list = $this->model_lesson_live->saveData($msg);
            } else {
                // 传入的是其他文件
                $message['id'] = $zn_fid; // 主课程id
                // 判断是录播还是直播
                if ($zn_type == 1) {
                    // 获取zc_file
                    $result = $this->model_live->lqGetField($message, 'zc_file');
                    // 判断zc_file是否为空
                    if ($result == null) {
                        $message['zc_file'] = $new_id;
                        $list = $this->model_live->saveData($message);
                    } else {
                        // 不为空就压入数据
                        $message['zc_file'] = $result . ',' . $new_id;
                        $list = $this->model_live->saveData($message);
                    }
                } else {
                    // 获取zc_file
                    $result = $this->model_vod->lqGetField($message, 'zc_file');
                    // 判断zc_file是否为空
                    if ($result == null) {
                        $message['zc_file'] = $new_id;
                        $list = $this->model_vod->saveData($message);
                    } else {
                        // 不为空就压入数据
                        $message['zc_file'] = $result . ',' . $new_id;
                        $list = $this->model_vod->saveData($message);
                    }
                }
            }

            // 判断数据是否插入成功
            if ($list < 1) {
                $this->ajaxReturn(array("status" => 0, "msg" => '上传失败'));
            }
            $this->ajaxReturn(array("status" => 1, "msg" => '附件上传成功...', "data" => $new_id, "file_name" => $zc_file_name));
        } catch (OssException $e) {
            $data['msg'] = $e->getMessage();
            $this->ajaxReturn($data);
        }
    }

    /*
    * 直播/录播 删除课程接口
    */
    public function delcourse()
    {
        if (IS_POST) {
            $id = I('post.id', '');
            $type = I('post.type', '1'); //  1直播,2录播
            $zn_teacher_id = $this->login_member_info['id'];
            if ($type == 1) {
                $insertID = $this->model_live->delData($id, $zn_teacher_id); // 删除大节
                $this->model_lesson_live->where('zn_cat_id = ' . $id)->delete();// 删除小节
                $this->model_Apply->where('zn_lesson_id =' . $id)->delete();//删除申请
            } else {
                $insertID = $this->model_vod->delData($id, $zn_teacher_id);// 删除大节
                $this->model_lesson_vod->where('zn_cat_id = ' . $id)->delete();// 删除小节
            }
            if (intval($insertID) > 0) {
                $this->model_member->addMemberLog('op_delete', $this->login_member_info);//插入会员日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $insertID));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败，请重试...'));
        }
    }

    /*
    * 直播/录播 - 取消附件上传
    */
    public function delupload()
    {
        $live_id = I('post.live_id', '');// 课程id
        $file_id = I('post.file_id', '');// 要删除的文件id
        $type = I('post.type', '1'); // 1直播,2录播
        if ($type == 1) {
            $res = $this->model_live->deleteFile($live_id, $file_id);
        } else {
            $res = $this->model_vod->deleteFile($live_id, $file_id);
        }
        if ($res) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败，请重试...'));
        }
    }


}