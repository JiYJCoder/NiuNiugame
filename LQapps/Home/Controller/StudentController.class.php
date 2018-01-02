<?php
/*
*** 学生控制面版模块
*/
namespace Home\Controller;

use Home\Model\StudentModel;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class StudentController extends PublicController
{

    private $D_SMS, $role, $model_live, $model_lesson_live, $model_lesson_cat, $model_vod, $model_lesson_vod, $model_favorite, $model_enroll, $model_live_record, $model_vod_record;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        // 角色是学生 / 1是学生
        $this->role = 1;
        // 没有登录就可以浏览的方法
        $action_no_login_array = array('login', 'register_step1', 'register_step2', 'register_step3', 'register_step_sub', 'send_verify', 'getgrowinfo', 'student_register', 'send_verify', 'send_email','verification_code','change_password','favorite','enroll');
        // 没有登录不在可以浏览的页面中,跳转到登录页面

        if (!in_array(ACTION_NAME, $action_no_login_array)) {
            self::checkLogin(1);
        }
        $this->D_SMS = D("SmsLog");//短信实例化
        $this->model_lesson_cat = D('LessonCat');
        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->model_live_record = D("LiveRecord");
        $this->model_vod_record = D("VodRecord");
        $this->assign('student_information', $this->login_member_info);// 显示学生基本信息
    }

    /*
     * 学生  - 首页
     */
    public function index()
    {
        // 登录后获取数据
        $student_id = $this->login_member_info['id'];
        $student_msg = M('member')->where('id=' . $student_id . ' and zl_visible = 1')->field('`id`,`zc_nickname`,`zc_headimg`')->find();
        // 获取直播数量
        $student_msg['live_num'] = $this->model_enroll->getCount("zn_member_id = " . $student_msg['id'] . " and zn_type=1");
        // 获取录播数量
        $student_msg['vod_num'] = $this->model_enroll->getCount("zn_member_id = " . $student_msg['id'] . " and zn_type=2");
        // 获取观看总时长(直播 + 录播)
        $time = $this->model_live_record->getCount($student_msg['id']);
        $time += $this->model_vod_record->getCount($student_msg['id']);
        $time = formattime($time);// 格式化时间(自定义函数)
        $total_time = explode(' ', $time);// 分开时间和时间单位
        $student_msg['time'] = $total_time[0];
        $student_msg['time_unit'] = $total_time[1];
        $student_msg['teacher_num'] = $this->model_live_record->teacherSum($student_id);// 听过老师数量
        $this->assign('student_msg', $student_msg);

        $student = new StudentModel();// 实例化
        $live_msg = $student->getLastFiveLive($student_id);// 获取最近观看的直播(5节)
        $vodLessonMsg = $student->getLastFiveVod($student_id);// 获取最近观看的录播(5节)

        $this->assign('live_msg', $live_msg);
        $this->assign('vod_msg', $vodLessonMsg);
        $this->assign('index_active', $this->nav_active);

        $this->display();
    }

    /*
    * 学生注册步骤一
    */
    public function register_step1()
    {
        $this->display();
    }

    /*
    * 学生注册步骤二
    */
    public function register_step2()
    {
        $this->display();
    }

    /*
    * 学生注册步骤三
    */
    public function register_step3()
    {
        $this->display();
    }

    public function recordlist()
    {
        $this->display();
    }

    /**
     * 学生 - 个人资料
     */
    public function my()
    {

        $student_msg = $this->login_member_info;

        // 生日
        if (!$student_msg['zc_birthday']) {
            $birthday = lqGetIDCardInfo($student_msg['zc_idcard'])['birthday'];
            $birthday = explode(' ', $birthday)[0];
            $student_msg['year'] = explode('-', $birthday)[0];
            $student_msg['month'] = explode('-', $birthday)[1];
            $student_msg['day'] = explode('-', $birthday)[2];
        } else {
            $student_msg['year'] = explode('-', $student_msg['zc_birthday'])[0];
            $student_msg['month'] = explode('-', $student_msg['zc_birthday'])[1];
            $student_msg['day'] = explode('-', $student_msg['zc_birthday'])[2];
        }

//        pr($student_msg);


        $this->assign('student_msg', $student_msg);

        $this->display();
    }

    /*
     * 学生 - 观看直播
     */
    public function broadcast()
    {
        $this->display();
    }

    /*
     * 学生 - 我的直播 - 正在学习
     */
    public function livecourse()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        // 获取全部正在学习的课程
        $count = $this->model_enroll->getCount('zn_member_id=' . $student_id . ' and zn_type=1'); // 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 1,// 1直播
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status desc,a.zn_cdate desc'
        );

        $live_msg = $this->model_enroll
            ->alias('a')->join('lq_live AS b on a.zn_object_id = b.id')
            ->lqList($page->firstRow, $page->listRows, $page_config); // model处理数据
//        pr($live_msg);
        $this->assign('live_msg', $live_msg);
        $this->assign('livecourse_active', $this->nav_active);
        $this->display();
    }

    /*
     *学生 - 我的直播 - 已过期
     */
    public function liveexpire()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        // 获取全部正在学习的课程
        $count = $this->model_enroll
            ->alias('a')->join('lq_live AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=1 and b.zl_status<3')
            ->count();// 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 1,// 1直播
                "b.zl_status" => array("LT", 3)
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status asc,a.zn_cdate desc'
        );

        $live_msg = $this->model_enroll
            ->alias('a')->join('lq_live AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=1 and b.zl_status<3')
            ->lqList($page->firstRow, $page->listRows, $page_config); // model处理数据
//        pr($live_msg);
        $this->assign('live_msg', $live_msg);
        $this->assign('livecourse_active', $this->nav_active);
        $this->display();

    }

    /*
     * 学生 - 我的直播 - 收藏
     */
    public function livestar()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        // 获取全部正在学习的课程
        $count = $this->model_favorite->getCount('zn_member_id=' . $student_id . ' and zn_type=1'); // 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 1,// 1直播
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status desc,a.zn_cdate desc'
        );

        $live_msg = $this->model_favorite
            ->alias('a')->join('lq_live AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=1')
            ->lqList($page->firstRow, $page->listRows, $page_config); // model处理数据
//        pr($live_msg);
        $this->assign('live_msg', $live_msg);
        $this->assign('livecourse_active', $this->nav_active);
        $this->display();
    }

    /*
     *  学生 - 我的录播 - 正在学习
     */
    public function vodcourse()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        $update_num = $this->model_vod_record->returnLesson($student_id);// 获取更新数目
        $this->assign('update_num', $update_num);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 2,// 2录播
                "b.zl_status" => 6,
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status desc,a.zn_cdate desc'
        );

        // 获取全部正在学习的课程
        $count = $this->model_enroll
            ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
            ->where($page_config['where'])
            ->count(); // model处理数据 // 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);



        $vod_msg = $this->model_enroll
            ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
            ->lqList_vod($page->firstRow, $page->listRows, $page_config, $student_id); // model处理数据
//        pr($vod_msg);
        $this->assign('vod_msg', $vod_msg);
        $this->assign('vodcourse_active', $this->nav_active);
        $this->display();
    }

    /*
     * 学生 - 我的录播 - 已过期
     */
    public function vodexpire()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        $update_num = $this->model_vod_record->returnLesson($student_id);// 获取更新数目
        $this->assign('update_num', $update_num);

        // 获取全部已过期的课程
        $count = $this->model_enroll
            ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=2 and b.zl_status<3')
            ->count();// 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 2,// 2录播
                "b.zl_status" => array("LT", 3)
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status asc,a.zn_cdate desc'
        );

        $vod_msg = $this->model_enroll
            ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=2 and b.zl_status<3')
            ->lqList_vod($page->firstRow, $page->listRows, $page_config, $student_id, 2); // model处理数据
//        pr($vod_msg);
        $this->assign('vod_msg', $vod_msg);
        $this->assign('vodcourse_active', $this->nav_active);
        $this->display();
    }

    /*
    * 学生 - 我的录播 - 取消收藏
    */
    public function vodstar()
    {
        $student_id = $this->login_member_info['id'];// 获取学生id

        $update_num = $this->model_vod_record->returnLesson($student_id);// 获取更新数目
        $this->assign('update_num', $update_num);

        // 获取全部正在学习的课程
        $count = $this->model_favorite->getCount('zn_member_id=' . $student_id . ' and zn_type=2'); // 统计总数
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")['student_live']);//载入分页类
        $showPage = $page->show();// 显示分页
        $this->assign("page", $showPage);

        $page_config = array( // 组成数据
            'where' => array(
                "a.zn_member_id" => $student_id,
                "a.zn_type" => 2,// 1直播
            ),
            'field' => 'a.zn_object_id',
            'order' => 'b.zl_status desc,a.zn_cdate desc'
        );

        $vod_msg = $this->model_favorite
            ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
            ->where('a.zn_member_id=' . $student_id . ' and a.zn_type=2')
            ->lqList_vod($page->firstRow, $page->listRows, $page_config, $student_id); // model处理数据
//        pr($vod_msg);
        $this->assign('vod_msg', $vod_msg);
        $this->assign('vodcourse_active', $this->nav_active);
        $this->display();
    }


    // *************************************接口***********************************
    /*
     * 学生登录
     * $username  用户名
     * password  密码
     * $type = 1 帐号密码 2 手机
     */
    public function login()
    {
        if (IS_POST) {
            $type = I("post.type", "1", "int");

            $returnArray = array('status' => 1, 'msg' => C('ALERT_ARRAY')["loginSuccess"]);
            if ($type == 1 || $type == 2) {
                $username = I("post.username", '');//帐号
                $password = I("post.password", '');//密码

                $mid = $this->model_member->apiLogin($username, $password, $this->role, 1);
            } elseif ($type == 3) {
                $mobile = I("post.mobile", '');//手机号码
                $check_code = I("post.check_code", '');//手机验证码
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

            if ($mid > 0) { //MEMBER登录成功
                $data = $this->model_member->apiLoginSession($mid, $this->role);//注册session
                $this->model_member->addMemberLog("login");//写入日志
                $this->ajaxReturn(array('status' => 1, 'msg' => '会员登录成功！'));
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

            if (lq_is_login('student')) {
                $this->redirect('index');
            }
            $this->display();
        }
    }

    /*
     * 学生注册接口
     */
    public function student_register()
    {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $real_name = I("post.real_name", "");//姓名
            $school = I("post.school", "");//所在学校
            $idcard = I("post.idcard");// 身份证
            $email = I("post.email", ""); // 获取邮箱
            $mobile = I("post.mobile", ""); // 获取手机
            $check_code = I("post.check_code", ""); //获取验证码
            $type = I("post.type", "0"); // 1为邮箱,2位手机,0是错误
            $password = I("post.password", '');//密码
            $img = NO_AVATAR_STUDENT;// 头像

            // 检查验证码
            if ($type == 2) {
                // 手机
                if (!$this->D_SMS->isEffective($mobile, 'register', $check_code) || !$check_code) {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '验证码无效'));
                } else {
                    // 手机绑定
                    $zl_mobile_bind = 1;
                    $zl_email_bind = 0;
                    // 组成数据(手机)
                    $data = array(
                        "zc_account" => $mobile, // 账号是手机号
                        "zc_mobile" => $mobile, // 手机号
                        "zc_nickname" => $real_name,//姓名
                        "zc_school" => $school, // 学校
                        "zc_idcard" => $idcard, // 身份证
                        "zl_role" => 1, //1 代表角色是学生
                        "zc_password" => $password, // 密码
                        "zc_headimg" => $img, //默认头像
                        "zn_reg_ip" => get_client_ip(1), // 获取注册ip
                        "zn_cdate" => NOW_TIME, // 记录时间
                        "zl_mobile_bind" => $zl_mobile_bind,
                        "zl_email_bind" => $zl_email_bind,
                    );
                    $upField = $mobile;
                }
            } else if ($type == 1) {
                // 邮箱
                if (!$this->D_SMS->isEffective($email, 'register', $check_code) || !$check_code) {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '验证码无效'));
                } else {
                    // 邮箱绑定
                    $zl_email_bind = 1;
                    $zl_mobile_bind = 0;

                    // 组成数据
                    $data = array(
                        "zc_account" => $email, // 账号是邮箱
                        "zc_nickname" => $real_name,//姓名
                        "zc_school" => $school, // 学校
                        "zc_idcard" => $idcard, // 身份证
                        "zl_role" => 1, //1 代表角色是学生
                        "zc_password" => $password, // 密码
                        "zc_headimg" => $img, //默认头像
                        "zn_reg_ip" => get_client_ip(1), // 获取注册ip
                        "zn_cdate" => NOW_TIME, // 记录时间
                        "zl_mobile_bind" => $zl_mobile_bind,
                        "zl_email_bind" => $zl_email_bind,
                        "zc_email" => $email, // 邮箱
                    );
                    $upField = $email;
                }
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '未知错误,请重试'));
            }

            $mid = $this->model_member->apiRegister($data);
            if (preg_match('/^([1-9]\d*)$/', $mid)) {
                $data["id"] = $mid;
                $this->model_member->apiLoginSession($mid, $this->role);//注册session
                $this->model_member->addMemberLog('register', $data);//插入会员日志

                /////更新短信使用状态
                M()->execute("update `__PREFIX__sms_log` set zl_use = 1 where zc_mobile='" . $upField . "' and zc_action='register'");

                $this->ajaxReturn(array('status' => 1, 'msg' => '会员注册成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }


    /* 会员登出 */
    public function loginOut()
    {
        $this->model_member->addMemberLog('loginOut', $this->login_member_info);//插入会员日志
        $this->model_member->apiLoginOut();
//        $this->success('退出成功', U('/Index/index'), 1);
        $this->ajaxReturn(array("status"=>1,"msg"=>'退出登录成功',"url"=>U('/')));
    }

    /*
     * 发送手机验证码
     */
    public function send_verify()
    {
        $mobile = I("get.mobile", "");
        $check_code = lq_random_string(6, 1);//随机码
        $type = I("get.type", "1"); // 1是注册 2 登陆
        if($mobile && $check_code) $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，发送错误！'));

        $list = array(
            1 => 'register',
            2 => 'login',
        );
        $tempId = 'SMS_11490043';
        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员注册失败,手机号码不正确！'));
        ///半小时内允许发送三次
        if (!$this->D_SMS->isAllowReceive($mobile, $list[$type])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，不能频繁请求操作！'));
        }

        ///发送验证码
        $sms_data = lqSendSms($mobile, $check_code, $tempId);
        if ($sms_data["status"] == 1) {
            $this->D_SMS->addSms($list[$type], $mobile, $check_code);
            $this->ajaxReturn(array('status' => 1, 'msg' => '短信发送成功', "code" => $check_code));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，短信发送失败，请重试...！'));
        }
    }

    /*
     *  发送邮箱验证码
     */
    public function send_email()
    {
        // 获取验证码
        $email = I("get.email", "");
        $name = I('get.name', "");// 昵称
        // 判断名字是否为空
        if ($name == '') {
            $name = '亲爱的用户';
        }
        $check_code = lq_random_string(6, 1);//随机码

        $message = '[孜尔教育]  你好,你的验证码是 ' . $check_code . ' ,有效期是10min,请尽快注册.';
//		$tempId = 'SMS_11490043';

        if (!isEmail($email)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员注册失败,邮箱不正确！'));
        ///半小时内允许发送三次
        if (!$this->D_SMS->isAllowReceive($email, 'register')) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，不能频繁请求操作！'));
        }
        ///发送验证码
        $sms_data = lq_send_mail($email, $name, '孜尔教育', $message);
        if ($sms_data === true) {
            $this->D_SMS->addSms('register', $email, $check_code);
            $this->ajaxReturn(array('status' => 1, 'msg' => '邮箱发送成功', "code" => $check_code));
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '对不起，邮箱发送失败，请重试...！'));
        }
    }

    /*
     * 学生 - 收藏(接口)
     */
    public function favorite()
    {
        if (!session('student_auth')['id']) $this->ajaxReturn(array("status" => 0, "msg" => '请先登录'));
        $id = I('post.id', '0', 'int');// 小节id
        $type = I('post.type', '1', 'int'); // 类型 1直播 2录播
        $uid = $this->login_member_info['id'];
        $this->ajaxReturn($this->model_member->apiMemberFavorite($id, $type));
    }

    /*
     * 学生 - 报名(接口)
     */
    public function enroll()
    {
        if (!session('student_auth')['id']) $this->ajaxReturn(array("status" => 0, "msg" => '请先登录'));
        $id = I('post.id', '0', 'int');// 小节id
        $type = I('post.type', '1', 'int'); // 类型 1直播 2录播
        $this->ajaxReturn($this->model_member->apiMemberEnroll($id, $type));

    }

    /*
     * 学生 - 取消收藏(接口)
     */
    public function cancel_favorite()
    {
        if (!session('student_auth')['id']) $this->ajaxReturn(array("status" => 0, "msg" => '请先登录'));
        $id = I('post.id', '0', 'int');// 小节id
        $type = I('post.type', '1', 'int'); // 类型 1直播 2录播
        $this->ajaxReturn($this->model_member->apiDelMemberFavorite($id, $type));
    }

    /*
     * 学生 - 修改个人信息(接口)
     */
    public function modify_personal_information()
    {
        if (IS_POST) {
            $data['id'] = $this->login_member_info['id'];

            $headimg = I("post.headimg", ""); // 头像
            if ($headimg) {
                $save = save_base64_img($headimg);
                if ($save) $data["zc_headimg"] = $save;
            }
            if(I('post.school','')){
                $data['zc_school'] = I('post.school');// 学校
            }
            if( I('post.birthday', '')){
                $data['zc_birthday'] = I('post.birthday', '');//生日
            }
            if(I('post.sex', '')){
                $data['zl_sex'] = I('post.sex', ''); // 性别
            }
            $data['zn_mdate'] = time();

            $mid = $this->model_member->apiSaveMember($data);

            if (intval($mid) > 0) {
                $this->model_member->addMemberLog('edit_member', $this->login_member_info);//添加日志

                $this->model_member->apiCacheInfo($mid); //缓存当前会员信息
                $this->ajaxReturn(array('status' => 1, 'msg' => '个人设置修改成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    /*
     * 学生 - 修改密码(接口)
     */
    public function change_password()
    {
        if (IS_POST) {
            $time = S('try_change_password_times');// 获取尝试修改次数
            if ($time && $time > 4) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '修改密码次数过多,请稍后再尝试'));
            }
            $id = session("student_auth")['id'];// id

            $password = I('post.password', ''); // 旧密码
            $new_password = I('post.new_password', ''); // 新密码
            $check_password = M('Member')->where('id = ' . $id)->field('zc_password')->find();// 查原密码
            if (ucenter_md5($password) === $check_password['zc_password']) {
                $data['id'] = $id;
                $data['zc_password'] = ucenter_md5($new_password);
                $data['zn_mdate'] = NOW_TIME;
                $res = M('Member')->save($data);// 更新密码
                if ($res) {
                    $this->model_member->addMemberLog('edit_pass', $this->login_member_info);//添加日志
                    $this->ajaxReturn(array('status' => 1, 'msg' => '更新成功'));
                }
            } else {
                $change = 5 - $time;
                S('try_change_password_times', intval($time + 1), 1200);// 缓存失败次数
                $this->ajaxReturn(array('status' => 0, 'msg' => '更新失败,密码错误,还有' .$change  . '次机会'));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }

    }

    /*
     *  学生 - 验证验证码(接口)
     */
    public function verification_code()
    {
        if (IS_POST) {
            $account = I('post.account',''); // 账号
            $res = M('Member')->where(array('zc_account'=>$account))->field('zc_mobile,zc_email')->find();
            if(!$res){
                $this->ajaxReturn(array('status' => 0, 'msg' => '手机或邮箱不存在'));
            }
            $identify = I('post.identify',''); //手机或邮箱
            $check_code = I('post.code',''); // 验证码
            // 验证是否存在相对应的手机或邮箱
            if($res['zc_mobile'] == $identify || $res['zc_email'] == $identify){
                if(check_code($identify,$check_code,1)){
                    $this->ajaxReturn(array('status' => 1, 'msg' => '验证通过'));
                }else{
                    $this->ajaxReturn(array('status' => 0, 'msg' => '验证码错误'));
                }
            }else{
                $this->ajaxReturn(array('status' => 0, 'msg' => '此账号未绑定该手机或邮箱'));
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    }



