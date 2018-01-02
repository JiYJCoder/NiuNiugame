<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:会员中心
家装:hd(home-decoration)
*****************************************************************************
(1) 返回内容为json格式，采用UTF-8编码。
(2) 信息内容中含有时间字段的，字段值为urlEncode格式。
(3) 返回内容{"status":0,"msg":"操作提示","data":"回调数据","url":"回调响应地址","note":"备注"};
(4) 用户请求加权文档，统一传入uid,token两值。
*****************************************************************************
*/

namespace Api\Controller;

use Think\Controller;
use LQLibs\Util\HxCall as HxCall;//环信

defined('in_lqweb') or exit('Access Invalid!');

class MemberController extends PublicController
{
    protected $D_DESIGNER, $D_ART, $D_PRO, $D_HDDIARY, $D_SMS, $model_region, $easemob_prefix, $easemob_password, $D_LOANAPPLY;

    /** 初始化*/
    public function __construct()
    {

        parent::__construct();
        $this->D_DESIGNER = D("Api/Designer");//接口设计师实例化
        $this->D_ART = D("Api/Article");//接口文章实例化
        $this->D_PRO = D("Api/Product");//接口产品实例化
        $this->D_HDDIARY = D("Api/Hddiary");//接口日志实例化
        $this->D_LOANAPPLY = D("Api/Loanapply");//贷款接口实例化
        $this->D_SMS = D("Api/SmsLog");//接口短信实例化
        $this->model_region = M("region");
        $this->easemob_prefix = 'easemob_';//环信帐号前缀
        $this->easemob_password = '123456';//环信密码


        //免死金牌
        $action_no_login_array = array('get-openid', 'wx-return-openid', 'login', 'wx-login', 'openid-login');
        if (in_array(ACTION_NAME, $action_no_login_array)) {

        } else {
            self::apiCheckToken();//用户认证
        }
    }

    /////环信注册会员
    protected function hx_register($name = '', $pwd = '123456', $address = '广州', $update = 0)
    {
        $rs = new Hxcall(C("EASEMOB"));
        $rs->register($name, $pwd, $address);
        if ($update == 1) {
            //更新接入环信会员
            $data = array();
            $data["id"] = $this->login_member_info["id"];
            $data["zc_easemob_account"] = $name;
            $data["zc_easemob_password"] = $pwd;
            $this->model_member->apiSaveMember($data);
        }
    }

    //会员首页
    public function index()
    {
        $data = array();
        $data["nickname"] = $this->login_member_info["zc_nickname"];
        $data["headimg"] = $this->login_member_info["zc_headimg"];
        $data["integration"] = $this->login_member_info["zn_pay_integration"];
        if ($this->model_member->apiIsAllowIntegration('sign_in', $this->login_member_info)) {
            $data["sign_in_label"] = "您今天已签到";
            $data["sign_in_status"] = 1;
        } else {
            $data["sign_in_label"] = "您今天未签到";
            $data["sign_in_status"] = 0;
        }

        $data["project_step_label"] = "方案阶段";
        $data["project_step_status"] = 2;
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $data, "url" => "", "note" => '会员首页'), $this->JSONP);
    }

    //微信授权 获得openid
    public function wx_return_openid()
    {
        if(session('openid'))
        {
            lq_header("Location:http://wx.lxjjz.cn/wx/views/my/login.html");
            die();
        }
        lq_return_openid(U('api/member/wx-return-openid'));
    }

    //openid
    public function get_openid()
    {
        if (session('openid')) {
            $this->login_member_info = $this->model_member->loginByopenid(session('openid'));
			if($this->login_member_info==-1){
				setcookie("openid",session('openid'));
            	$this->ajaxReturn(array('status' => 0, 'msg' => '获取失败', 'data' => '', "url" =>'http://wx.lxjjz.cn/wx/views/my/login.html', "note" => '获取openid'), $this->JSONP);
			}else{
				if ($this->login_member_info) $token = $this->model_member->apiGetToken($this->login_member_info["id"]);
				setcookie("openid",session('openid'));
				setcookie("uid",$token["zn_member_id"]);
				setcookie("token",$token["zc_token"]);
				$referer_url=$_COOKIE['referer'];
				if(!$referer_url) $referer_url = 'http://wx.lxjjz.cn/wx/views/my/index.html';
            	$this->ajaxReturn(array('status' => 0, 'msg' => '获取成功', 'data' => '', "url" =>$referer_url, "note" => '获取openid'), $this->JSONP);
			}
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '获取失败', 'data' => '', "url" => U('api/member/wx-return-openid'), "note" => '获取openid'), $this->JSONP);
        }
    }

    public function wx_login()
    {
        $mobile = I("get.mobile", '');//手机号码
        $check_code = I("get.check_code", '');//手机验证码
        $openid = I("get.openid", '');//openid
        if (!$openid) $this->ajaxReturn(array('status' => 0, 'msg' => 'openid失效,请刷新页面！', 'data' => '', "url" => '', "note" => '会员登录'), $this->JSONP);
        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,手机号码不正确！', 'data' => '', "url" => "", "note" => '会员登录'), $this->JSONP);
        if (!$this->D_SMS->isEffective($mobile, 'login', $check_code)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,验证码无效！', 'data' => '', "url" => "", "note" => '会员登录'), $this->JSONP);
        }
        $follow = M("follow")->field("id,zc_headimg_url,zc_nickname,zc_province,zc_city")->where("zc_openid='" . $openid . "'")->find();
        //查库有没有会员
        $member = $this->model_member->apiGetField("zc_mobile='" . $mobile . "'", '*');
        if ($member) {//登录
            $this->login_member_info = $member;
            if ($this->login_member_info["zl_role"] == 1 && $this->login_member_info["zl_is_designer"] == 0) {
                $this->login_member_info["zn_last_login_time"] = NOW_TIME;

                if ($this->login_member_info["zl_openid_bind"] == 0) {
                    $update_member = array();
                    $update_member["id"] = $member["id"];
                    $update_member["zc_openid"] = $openid;
                    $update_member["zl_openid_bind"] = 1;
                    $this->model_member->apiSaveMember($update_member);

                    $update_follow = array();
                    $update_follow["zn_member_id"] = $member["id"];
                    $update_follow["zc_member_account"] = $member["zc_account"];
                    $this->model_member->apiUpdateFollow($update_follow, $openid);
                    //更值
                    $this->login_member_info["zc_openid"] = $openid;
                }
                if ($this->login_member_info["zc_openid"] == $openid) {//MEMBER登录成功
                    $this->model_member->apiUpdateLogin($this->login_member_info["id"]);
                    $this->login_member_info["zc_openid"] = $openid;
                    $this->login_member_info["client_type"] = 'WECHAT';
                    if ($this->model_member->apiGetToken($this->login_member_info["id"])) {
                        $token = $this->model_member->apiUpdateToken($this->login_member_info);
                    } else {
                        $token = $this->model_member->apiInsertToken($this->login_member_info);
                    }
                    $this->D_SMS->updateUse($mobile, 'login', $check_code);//改变短信状态
                    $this->model_member->addMemberLog('login', $this->login_member_info);//插入会员日志
                    $this->ajaxReturn(array('status' => 1, 'msg' => '会员登录成功', 'data' => array('uid' => $this->login_member_info["id"], 'token' => $token), 'url' => 'http://wx.lxjjz.cn/wx/views/my/index.html'), $this->JSONP);

                } else {
                    $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败', 'data' => ''), $this->JSONP);
                }
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败:用户未授权', 'data' => ''), $this->JSONP);
            }

        } else {//注册

            $password = rand(100000, 999999);
            $province_id = $this->model_region->where("zc_name='" . $follow["zc_province"] . "省'")->getField("id");
            $city_id = $this->model_region->where("zc_name='" . $follow["zc_city"] . "市'")->getField("id");
            $district_id = 0;
            if ($city_id) {
                $district_id = $this->model_region->where("zn_fid=" . intval($city_id))->getField("id");
            }

            $data = array();
            $data["__hash__"] = I("post.hash", '');//表单认证
            $data["zc_openid"] = $openid;
            $data["zl_role"] = 1;
            $data["zl_is_designer"] = 0;
            $data["zc_account"] = $mobile;
            $data["zc_mobile"] = $mobile;
            $data["zc_password"] = $password;
            $data["zc_email"] = NOW_TIME . $password . "@qq.com";
            $data["zc_nickname"] = !$follow["zc_nickname"] ? $mobile : $follow["zc_nickname"];
            $data["zc_headimg"] = !$follow["zc_headimg_url"] ? '' : $follow["zc_headimg_url"];
            $data["zl_account_bind"] = 0;
            $data["zl_openid_bind"] = 1;
            $data["zl_mobile_bind"] = 1;
            $data["zl_email_bind"] = 0;
            $data["zl_sex"] = intval($follow["zn_sex"]);
            $data["zn_province"] = intval($province_id);
            $data["zn_city"] = intval($city_id);
            $data["zn_district"] = intval($district_id);
            $data["zn_login_times"] = 1;
            $data["zn_last_login_ip"] = get_client_ip(1);
            $data["zn_last_login_time"] = NOW_TIME;
            $data["zn_trylogin_times"] = 0;
            $data["zn_trylogin_lasttime"] = NOW_TIME;
            if ($follow) {
                $data["zc_area"] = $data["zc_address"] = $follow["zc_province"] . $follow["zc_city"] . "市区";
            }
            $mid = $this->model_member->apiRegister($data);
            if (preg_match('/^([1-9]\d*)$/', $mid)) {
                $this->D_SMS->updateUse($mobile, 'login', $check_code);//改变短信状态
                $data["id"] = $mid;
                $data["client_type"] = 'WECHAT';
                $token = $this->model_member->apiInsertToken($data);
                $this->model_member->addMemberLog('register', $data);//插入会员日志
                if ($follow) {
                    $update_follow = array();
                    $update_follow["zn_member_id"] = $data["id"];
                    $update_follow["zc_member_account"] = $data["zc_account"];
                    $this->model_member->apiUpdateFollow($update_follow, $openid);
                }
				//新增会员注册分享
				if(session("register_share")){
					D("Api/Activity")->addRegisterShare($data);
					if(intval(session("register_share")["type"])==3){
					$go_back_url = 'http://wx.lxjjz.cn/wx/views/my/index.html';
					}else{
					$go_back_url = 'http://wx.lxjjz.cn/wx/views/activity/goOnline.html?tnid=1&referer='.$mid.'&new_reg_id='.$mid;
					}
				}else{
					$go_back_url = 'http://wx.lxjjz.cn/wx/views/my/index.html';
				}
				
                $this->ajaxReturn(array('status' => 1, 'msg' => '会员登录成功', 'data' => array('uid' => $mid, 'token' => $token), 'url' => $go_back_url), $this->JSONP);
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid, 'data' => ''), $this->JSONP);
            }
        }
    }

    //app会员登录
    public function login()
    {
        $mobile = I("get.mobile", '');//手机号码
        $check_code = I("get.check_code", '');//手机验证码
        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,手机号码不正确！', 'data' => '', "url" => "", "note" => '会员登录'), $this->JSONP);
        if (!$this->D_SMS->isEffective($mobile, 'login', $check_code)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,验证码无效！', 'data' => '', "url" => "", "note" => '会员登录'), $this->JSONP);
        }

        //查库有没有会员
        $member_info = $this->model_member->apiGetField("zc_mobile='" . $mobile . "'", '*');
        if (is_array($member_info)) {//登录
            if ($member_info["zl_role"] == 1 && $member_info["zl_is_designer"] == 0) {
                $UpdateLoginData = $this->model_member->apiUpdateLogin($member_info["id"]);
                $member_info["zn_login_times"] = $UpdateLoginData["zn_login_times"];
                $member_info["zn_last_login_ip"] = $UpdateLoginData["zn_last_login_ip"];
                $member_info["zn_last_login_time"] = $UpdateLoginData["zn_last_login_time"];
                $member_info["zn_mdate"] = $UpdateLoginData["zn_mdate"];
                $member_info["zn_trylogin_times"] = $UpdateLoginData["zn_trylogin_times"];
                $member_info["zn_trylogin_lasttime"] = $UpdateLoginData["zn_trylogin_lasttime"];
                $no_update_sms =  array('13580934623','15625069806');
                if(!in_array($mobile,$no_update_sms)) {
                    $this->D_SMS->updateUse($mobile, 'login', $check_code);//改变短信状态
                }
                $this->login_member_info = $member_info;
                $this->login_member_info["client_type"] = 'APP';
                if ($this->model_member->apiGetToken($this->login_member_info["id"])) {
                    $token = $this->model_member->apiUpdateToken($this->login_member_info);
                } else {
                    $token = $this->model_member->apiInsertToken($this->login_member_info);
                }

                $this->model_member->addMemberLog('login', $this->login_member_info);//插入会员日志
                //返回会员信息
                $info = array();
                $info["nickname"] = $this->login_member_info["zc_nickname"];
                $info["sex"] = $this->login_member_info["zl_sex"];
                $info["zl_sex_label"] = C("_SEX")[$this->login_member_info["zl_sex"]];
                $info["pay_integration"] = $this->login_member_info["zn_pay_integration"];
                $info["rank_integration"] = $this->login_member_info["zn_rank_integration"];
                $info["headimg"] = NO_HEADIMG;
                $info["province"] = $this->model_region->where("id='" . $this->login_member_info["zn_province"] . "'")->getField("zc_name");
                $info["city"] = $this->model_region->where("id='" . $this->login_member_info["zn_city"] . "'")->getField("zc_name");
                $info["district"] = $this->model_region->where("id='" . $this->login_member_info["zn_district"] . "'")->getField("zc_name");
                $info["province_id"] = $this->login_member_info["zn_province"];
                $info["city_id"] = $this->login_member_info["zn_city"];
                $info["district_id"] = $this->login_member_info["zn_district"];
                $info["area"] = $this->login_member_info["zc_area"];
                $info["address"] = $this->login_member_info["zc_address"];


                $this->ajaxReturn(array('status' => 1, 'msg' => '会员登录成功', 'data' => array('uid' => $this->login_member_info["id"], 'token' => $token, 'member_info' => $info), "url" => "", "note" => '会员登录'), $this->JSONP);
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败', 'data' => ''), $this->JSONP);
            }

        } else {//注册

            $password = rand(100000, 999999);
            $province = I("get.province", '');//省
            $city = I("get.city", '');//市
            $district = I("get.district", '');//区
            $province_id = 0;
            $city_id = 0;
            $district_id = 0;
            $area = '';

            if ($province) {
                $province_id = $this->model_region->where("zc_name='" . $province . "'")->getField("id");
                $province_id = intval($province_id);
                $area .= $province;
            }
            if ($city) {
                $city_id = $this->model_region->where("zc_name='" . $city . "'")->getField("id");
                $city_id = intval($city_id);
                $area .= $city;
            }
            if ($district) {
                $district_id = $this->model_region->where("zc_name='" . $district . "'")->getField("id");
                $district_id = intval($district_id);
                $area .= $district;
            }


            $data = array();
            $data["__hash__"] = I("post.hash", '');//表单认证
            $data["zl_role"] = 1;
            $data["zc_openid"] = "LQ" . NOW_TIME . lq_random_string(16, 4);
            $data["zl_is_designer"] = 0;
            $data["zc_account"] = $mobile;
            $data["zc_mobile"] = $mobile;
            $data["zc_password"] = $password;
            $data["zc_email"] = NOW_TIME . $password . "@qq.com";
            $data["zc_nickname"] = $mobile;
            $data["zc_headimg"] = NO_HEADIMG;
            $data["zl_account_bind"] = 0;
            $data["zl_openid_bind"] = 0;
            $data["zl_mobile_bind"] = 1;
            $data["zl_email_bind"] = 0;
            $data["zl_sex"] = 0;
            $data["zn_province"] = $province_id;
            $data["zn_city"] = $city_id;
            $data["zn_district"] = $district_id;
            $data["zc_area"] = $area;
            $data["zc_address"] = $area;
            $data["zn_login_times"] = 1;
            $data["zn_last_login_ip"] = get_client_ip(1);
            $data["zn_last_login_time"] = NOW_TIME;
            $data["zn_trylogin_times"] = 0;
            $data["zn_trylogin_lasttime"] = NOW_TIME;
            $mid = $this->model_member->apiRegister($data);
            if (preg_match('/^([1-9]\d*)$/', $mid)) {
                $data["id"] = $mid;
                $data["client_type"] = 'APP';
                $token = $this->model_member->apiInsertToken($data);
                $this->model_member->addMemberLog('register', $data);//插入会员日志
                $this->D_SMS->updateUse($mobile, 'login', $check_code);//改变短信状态

                //返回会员信息
				$this->login_member_info=$data;
                $info = array();
                $info["nickname"] = $this->login_member_info["zc_nickname"];
                $info["sex"] = $this->login_member_info["zl_sex"];
                $info["zl_sex_label"] = C("_SEX")[$this->login_member_info["zl_sex"]];
                $info["pay_integration"] = C("LQ_MEMBER_INTEGRATION")["register"];
                $info["rank_integration"] = C("LQ_MEMBER_INTEGRATION")["register"];
                $info["headimg"] = $this->login_member_info["zc_headimg"];
                $info["province"] = $province;
                $info["city"] = $city;
                $info["district"] = $district;
                $info["province_id"] = $this->login_member_info["zn_province"];
                $info["city_id"] = $this->login_member_info["zn_city"];
                $info["district_id"] = $this->login_member_info["zn_district"];
                $info["area"] = $this->login_member_info["zc_area"];
                $info["address"] = $this->login_member_info["zc_address"];
                $info["easemob_account"] = $this->login_member_info["zc_easemob_account"];
                $info["easemob_password"] = $this->login_member_info["zc_easemob_password"];
                $this->hx_register($info["easemob_account"], $info["easemob_password"], $info["city"], 0);
                $this->ajaxReturn(array('status' => 1, 'msg' => '会员注册成功', 'data' => array('uid' => $mid, 'token' => $token, 'member_info' => $info), "url" => "", "note" => '会员注册'), $this->JSONP);
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $mid, 'data' => '', "url" => "", "note" => '会员注册'), $this->JSONP);
            }
        }
    }

    /*
     * 会员注册
     */
    public function register()
    {

    }
    /* 会员登出 */
    public function login_out()
    {
        $this->model_member->addMemberLog('loginOut', $this->login_member_info);//插入会员日志
        $this->ajaxReturn($this->model_member->apiLoginOut());
    }

    //获取会员信息
    public function get_member_info()
    {
        //返回会员信息
        $info = array();
        $info["nickname"] = $this->login_member_info["zc_nickname"];
        $info["sex"] = $this->login_member_info["zl_sex"];
        $info["zl_sex_label"] = C("_SEX")[$this->login_member_info["zl_sex"]];
        $info["pay_integration"] = $this->login_member_info["zn_pay_integration"];
        $info["rank_integration"] = $this->login_member_info["zn_rank_integration"];
        $info["headimg"] = $this->login_member_info["zc_headimg"];
        $info["province"] = $this->model_region->where("id='" . $this->login_member_info["zn_province"] . "'")->getField("zc_name");
        $info["city"] = $this->model_region->where("id='" . $this->login_member_info["zn_city"] . "'")->getField("zc_name");
        $info["district"] = $this->model_region->where("id='" . $this->login_member_info["zn_district"] . "'")->getField("zc_name");
        $info["province_id"] = $this->login_member_info["zn_province"];
        $info["city_id"] = $this->login_member_info["zn_city"];
        $info["district_id"] = $this->login_member_info["zn_district"];
        $info["area"] = $this->login_member_info["zc_area"];
        $info["address"] = $this->login_member_info["zc_address"];
        $info["mobile"] = $this->login_member_info["zc_mobile"];
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $info, "url" => "", "note" => "获取会员信息"), $this->JSONP);
    }

    //会员签到
    public function sign_in()
    {
        if (!$this->model_member->apiIsAllowIntegration('sign_in', $this->login_member_info)) {
            $this->model_member->addMemberLog('sign_in', $this->login_member_info);
            $this->ajaxReturn(array('status' => 1, 'msg' => '签到成功', 'data' => ($this->login_member_info["zn_pay_integration"] + C("LQ_MEMBER_INTEGRATION")["sign_in"]), "url" => "", "note" => "会员签到"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '今天您已签到', 'data' => '', "url" => "", "note" => "会员签到"), $this->JSONP);
        }
    }

    //设计师关注操作
    public function subscribe_designer()
    {
        $arr = $_GET;
        unset($arr);
        $_GET['tnid'] = I("get.tnid", '0', "int");
        $_GET['type'] = 5;
        if (!$this->lqgetid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '关注失败', 'data' => array(), "url" => "", "note" => "设计师关注"), $this->JSONP);
        }
        $this->set_love();
    }

    //设计师取消关注操作
    public function un_subscribe_designer()
    {
        $arr = $_GET;
        unset($arr);
        $_GET['tnid'] = I("get.tnid", '0', "int");
        $_GET['type'] = 5;
        if (!$this->lqgetid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '取消关注失败', 'data' => array(), "url" => "", "note" => "取消关注设计师"), $this->JSONP);
        }
        $this->delete_love();
    }


    //作品点赞统计
    public function works_agrees_count()
    {
        $id = $this->lqgetid;
        //设置请求记录*************start***************
        if ($this->model_member->apiIsAllowOs('works_agrees', $this->login_member_info, $id)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '您的请求次数太频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "收集咨询订单"), $this->JSONP);
        }
        //设置请求记录*************start***************
        $returnData = $this->D_DESIGNER->setAgreesCount($id);
        if ($returnData["status"]) {
            $this->model_member->addMemberLog('works_agrees', $this->login_member_info, $id);
            $info = PAGE_S("page_works_" . $id, '', $this->cache_options); //读取缓存数据
            $info["agrees"] = $returnData["data"];
            PAGE_S("page_works_" . $id, $info, $this->cache_options); //缓存数据
        }
        $this->ajaxReturn($returnData, $this->JSONP);
    }

    //修改会员头像
    public function edit_headimg()
    {
        if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "修改会员头像"), $this->JSONP);
        }
        $upfile_data = $this->opUpload('headimg', 'avatar');
        if ($upfile_data["status"] == 1) {
			
			$image_data=array();
			$image_data[]=array("key"=>'avatar',"path"=>$upfile_data["url"]);				
			$thumb_list=lq_thumb_deal($image_data,$this->login_member_info["id"],'avatar');
			$headimg_thumb = $thumb_list[0];
			if(!$headimg_thumb) $headimg_thumb = $upfile_data["url"];
			
            $data = array();
            $data["id"] = $this->login_member_info["id"];
            $data["zc_headimg"] = $upfile_data["url"];
			$data["zc_headimg_thumb"] = $headimg_thumb;
            $data["zn_mdate"] = NOW_TIME;
            $this->model_member->apiSaveMember($data);
            $this->model_member->addMemberLog('edit_member', $this->login_member_info);//添加日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '提交成功', 'data' => API_DOMAIN . $upfile_data["url"], "url" => "", "note" => "修改会员头像"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => $upfile_data["msg"], 'data' => '', "url" => "", "note" => "修改会员头像"), $this->JSONP);
        }
    }

    //修改会员昵称
    public function edit_nickname()
    {
        $nickname = I("get.nickname", '');//会员昵称
        $nickname_len = lqAbslength($nickname);
        if ($nickname_len < 1 || $nickname_len > 50) $this->ajaxReturn(array('status' => 0, 'msg' => '修改失败,请正确输入会员昵称', 'data' => '', "url" => "", "note" => '会员修改昵称'), $this->JSONP);
        if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "会员修改昵称"), $this->JSONP);
        }
        $data = array();
        $data["id"] = $this->login_member_info["id"];
        $data["zc_nickname"] = $nickname;
        $data["zn_mdate"] = NOW_TIME;
        if ($this->model_member->apiSaveMember($data)) {
            $this->model_member->addMemberLog('edit_member', $this->login_member_info);
            $this->ajaxReturn(array('status' => 1, 'msg' => '编辑成功', 'data' => '', "url" => "", "note" => "会员修改昵称"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑失败', 'data' => '', "url" => "", "note" => "会员修改昵称"), $this->JSONP);
        }
    }

    //会员修改性别
    public function edit_sex()
    {
        $sex = I("get.sex", '0', 'int');//性别
        if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "会员修改性别"), $this->JSONP);
        }
        $data = array();
        $data["id"] = $this->login_member_info["id"];
        $data["zl_sex"] = $sex;
        $data["zn_mdate"] = NOW_TIME;
        if ($this->model_member->apiSaveMember($data)) {
            $this->model_member->addMemberLog('edit_member', $this->login_member_info);
            $this->ajaxReturn(array('status' => 1, 'msg' => '编辑成功', 'data' => '', "url" => "", "note" => "会员修改性别"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑失败', 'data' => '', "url" => "", "note" => "会员修改性别"), $this->JSONP);
        }
    }

    //会员修改所在区域
    public function edit_area()
    {
        $province = I("get.province", '');//省
        $city = I("get.city", '');//市
        $district = I("get.district", '');//区
        $province_id = 0;
        $city_id = 0;
        $district_id = 0;
        $area = '';
        if (!$province || !$city || !$district) $this->ajaxReturn(array('status' => 0, 'msg' => '修改失败,请正确输入所在区域', 'data' => '', "url" => "", "note" => '修改会员所在区域'), $this->JSONP);
        if ($province) {
            $province_id = $this->model_region->where("zc_name='" . $province . "'")->getField("id");
            $province_id = intval($province_id);
            $area .= $province;
        }
        if ($city) {
            $city_id = $this->model_region->where("zc_name='" . $city . "'")->getField("id");
            $city_id = intval($city_id);
            $area .= $city;
        }
        if ($district) {
            $district_id = $this->model_region->where("zc_name='" . $district . "'")->getField("id");
            $district_id = intval($district_id);
            $area .= $district;
        }
        if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "修改会员所在区域"), $this->JSONP);
        }
        $data = array();
        $data["id"] = $this->login_member_info["id"];
        $data["zn_province"] = $province_id;
        $data["zn_city"] = $city_id;
        $data["zn_district"] = $district_id;
        $data["zc_area"] = $area;
        $data["zn_mdate"] = NOW_TIME;
        if ($this->model_member->apiSaveMember($data)) {
            $this->model_member->addMemberLog('edit_member', $this->login_member_info);
            $this->ajaxReturn(array('status' => 1, 'msg' => '编辑成功', 'data' => '', "url" => "", "note" => "修改会员所在区域"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑失败', 'data' => '', "url" => "", "note" => "修改会员所在区域"), $this->JSONP);
        }
    }

    //会员修改地址
    public function edit_address()
    {
        $nickname = I("get.address", '');//会员昵称
        $nickname_len = lqAbslength($nickname);
        if ($nickname_len < 1 || $nickname_len > 50) $this->ajaxReturn(array('status' => 0, 'msg' => '修改失败,请正确输入会员地址', 'data' => '', "url" => "", "note" => '会员修改地址'), $this->JSONP);
        if ($this->model_member->apiIsAllowOs('edit_member', $this->login_member_info)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑次数过于频繁，请歇息一下！', 'data' => '', "url" => "", "note" => "会员修改地址"), $this->JSONP);
        }
        $data = array();
        $data["id"] = $this->login_member_info["id"];
        $data["zc_address"] = $nickname;
        $data["zn_mdate"] = NOW_TIME;
        if ($this->model_member->apiSaveMember($data)) {
            $this->model_member->addMemberLog('edit_member', $this->login_member_info);
            $this->ajaxReturn(array('status' => 1, 'msg' => '编辑成功', 'data' => '', "url" => "", "note" => "会员修改地址"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '编辑失败', 'data' => '', "url" => "", "note" => "会员修改地址"), $this->JSONP);
        }
    }

    //会员反馈
    public function feedback()
    {
        $content = I("get.content", '');//请告诉我们你遇到的问题或想反馈的意见
        $contact_tel = I("get.mobile", '');//电话或邮箱方便我们联系您
        $data = array();

        if ($content == '') $this->ajaxReturn(array('status' => 0, 'msg' => '请输入反馈内容', 'data' => array(), "url" => "", "note" => "会员反馈"), $this->JSONP);
        if (!isMobile($contact_tel)) {
            if (!isEmail($contact_tel)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '请输入电话或邮箱方便我们联系您', 'data' => array(), "url" => "", "note" => "会员反馈"), $this->JSONP);
            }
            $data["zc_contact_email"] = $contact_tel;
            $data["zc_contact_tel"] = $this->login_member_info["zc_mobile"];
        } else {
            $data["zc_contact_email"] = $this->login_member_info["zc_email"];
            $data["zc_contact_tel"] = $contact_tel;
        }
        $data["zc_content"] = $content;
        $data["zc_contact_name"] = $this->login_member_info["zc_nickname"];
        $data["zn_ip"] = get_client_ip(1);
        $data["zl_is_open"] = 0;
        $data["zl_visible"] = 1;
        $data["zn_cdate"] = NOW_TIME;
        $data["zn_mdate"] = NOW_TIME;
        if (M("member_feedback")->add($data)) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '反馈成功！', 'data' => array(), "url" => "", "note" => "会员反馈"), $this->JSONP);
        }
    }

    //我喜欢
    public function mylove()
    {
        $type = I("get.type", '1', 'int') == "" ? 2 : I("get.type", '1', 'int');
        $pageno = I("get.p", '1', 'int');//页码

        ////收藏id
        $ids = $this->model_member->apiGetFavoriteIds($type, $this->login_member_info);
        $sqlwhere_parameter = " zl_visible=1 ";//sql条件

        if ($ids) {
            if (is_numeric($ids)) {
                $sqlwhere_parameter .= " and id =" . $ids;
            } else {
                $sqlwhere_parameter .= " and id IN (" . implode(',', $ids) . ")";
            }
        } else {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => '我喜欢'), $this->JSONP);
        }

        switch ($type) {
            ////日志
            case 1 :
                $sqlwhere_parameter .= " and zl_is_index = 1";

                $page_config = array(
                    'field' => "`id`,`zc_headimg` as headimg ,`zc_nickname` as nickname,`zc_image` as image,`zc_title` as title,`zn_area` as area,`zn_page_view` as page_view,`zn_agrees` as agrees,`zn_household` as household,`zc_style` as style,`zn_cdate` as cdate",
                    'where' => $sqlwhere_parameter,
                    'order' => 'zn_cdate DESC',
                );
                $count = $this->D_HDDIARY->lqCount($sqlwhere_parameter);
                $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["diary_list"]);//载入分页类
                //分页尽头
                $note = '0';
                if ($pageno >= $page->totalPages) {
                    $note = '0';
                } else {
                    if ($count == (C("API_PAGESIZE")["diary_list"] * $pageno)) {
                        $note = '0';
                    } else {
                        $note = '1';
                    }
                }
                $list = $this->D_HDDIARY->lqList($page->firstRow, $page->listRows, $page_config);

                break;
            ////案例
            case 2 :
                $page_config = array(
                    'field' => "`id`,`zn_style`,`zn_household`,`zn_designer_id` as designer_id,`zn_member_id`,`zc_designer_nickname` as designer_nickname,`zc_caption` as title,`zc_thumb` as image,`zn_thumb_width` as thumb_width,`zn_thumb_height` as thumb_height,`zn_work_release` as time,`zn_clicks` as clicks,`zn_agrees` as agrees",
                    'where' => $sqlwhere_parameter,
                    'order' => 'zn_sort ASC,zn_work_release DESC',
                );
                $count = $this->D_DESIGNER->lqWorksCount($sqlwhere_parameter);
                $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["works_list"]);//载入分页类
                //分页尽头
                if ($pageno >= $page->totalPages) {
                    $note = '0';
                } else {
                    if ($count == (C("API_PAGESIZE")["works_list"] * $pageno)) {
                        $note = '0';
                    } else {
                        $note = '1';
                    }
                }
                $list = $this->D_DESIGNER->lqWorksList($page->firstRow, $page->listRows, $page_config);

                $no_use_array = array("thumb_width", "thumb_height", "content", "content_index", "album", "album_num", "headimg", "personality_sign", "designer_name");
                $list = clean_no_use($list, $no_use_array);

                break;
            //攻略
            case 3 :
                $page_config = array(
                    'field' => "`id`,`zn_cat_id` as cat_id,`zc_title` as title,`zc_image` as image,`zc_summary` as content,`zd_send_time` as send_time,`zn_page_view` as clicks,`zn_agrees` as agrees",
                    'where' => $sqlwhere_parameter,
                    'order' => 'zn_sort ASC,zn_cdate DESC',
                );
                $count = $this->D_ART->lqCount($sqlwhere_parameter);
                $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["article_list"]);//载入分页类
                //分页尽头
                if ($pageno >= $page->totalPages) {
                    $note = '0';
                } else {
                    if ($count == (C("API_PAGESIZE")["article_list"] * $pageno)) {
                        $note = '0';
                    } else {
                        $note = '1';
                    }
                }
                $list = $this->D_ART->lqList($page->firstRow, $page->listRows, $page_config);
               // echo $this->D_ART->getLastSql();
                $no_use_array = array("send_time", "author", "url", "api_url", "webapp_url", "content");
                $list = clean_no_use($list, $no_use_array);
                break;
            //建材
            case 4 :
                $page_config = array(
                    'field' => "`id`,`zn_cat_id` as cat_id,`zc_title` as title,`zc_image` as image,`zf_shop_price` as price,`zn_cdate` as time,`zn_page_view` as clicks,`zn_agrees` as agrees",
                    'where' => $sqlwhere_parameter,
                    'order' => 'zn_sort ASC,zn_cdate DESC',
                );
                $count = $this->D_PRO->lqCount($sqlwhere_parameter);
                $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["product_list"]);//载入分页类
                //分页尽头
                if ($pageno >= $page->totalPages) {
                    $note = '0';
                } else {
                    if ($count == (C("API_PAGESIZE")["product_list"] * $pageno)) {
                        $note = '0';
                    } else {
                        $note = '1';
                    }
                }
                $list = $this->D_PRO->lqList($page->firstRow, $page->listRows, $page_config);

                $no_use_array = array("send_time", "author", "url", "api_url", "webapp_url", "content", "short_title", "thumb_width", "thumb_height", "zn_cat_id_label");
                $list = clean_no_use($list, $no_use_array);
                break;
            ////设计师
            case 5 :

                break;
            default:
                break;
        }
        if (!$list) $list = array();
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }


    //我喜欢 点赞收藏
    public function set_love()
    {
        $id = I("get.tnid", '0', "int");
        $type = I("get.type", '1', 'int');

        if (!($this->model_member->apiTestLove($id, $type, $this->login_member_info))) {
            $data = $this->model_member->apiInsertLove($id, $type, $this->login_member_info);

            if ($data) {
                switch ($type) {
                    ////日志
                    case 1 :
                        $this->D_HDDIARY->setAgreeCount($id);
                        break;
                    ////案例
                    case 2 :
                        $this->D_DESIGNER->setAgreeWorkCount($id);
                        break;
                    //攻略
                    case 3 :
                        $this->D_ART->setAgreeCount($id);
                        break;
                    //建材
                    case 4 :
                        $this->D_PRO->setAgreeCount($id);
                        break;
                    ////设计师
                    case 5 :
                        $this->D_DESIGNER->setAgreeCount($id);
                        break;
                    default:
                        break;
                }
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '操作成功', 'data' => $data, "url" => "", "note" => "点赞"), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '已关注', 'data' => array(), "url" => "", "note" => "点赞"), $this->JSONP);
        }
    }

    ////我喜欢 删除收藏
    public function delete_love()
    {
        $id = I("get.tnid", '0', "int");//请告诉我们你遇到的问题或想反馈的意见
        $type = I("get.type", '1', 'int');//电话或邮箱方便我们联系您

        if (!$id && !$type) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '操作失败', 'data' => 0, "url" => "", "note" => "删除点赞失败"), $this->JSONP);
        }
        $this->model_member->apiDeleteLove($id, $type, $this->login_member_info);
        $this->ajaxReturn(array('status' => 1, 'msg' => '操作成功', 'data' => 1, "url" => "", "note" => "删除点赞"), $this->JSONP);
    }

    /////关注设计师
    public function designer_love()
    {
        $pageno = I("get.p", '1', 'int');//页码

        $type = 5;
        $ids = $this->model_member->apiGetFavoriteIds($type, $this->login_member_info);
        $sqlwhere_parameter = " zl_visible=1 ";//sql条件

        if ($ids) {
            if (is_numeric($ids)) {
                $sqlwhere_parameter .= " and id =" . $ids;
            } else {
                $sqlwhere_parameter .= " and id IN (" . implode(',', $ids) . ")";
            }
        } else {
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }

        $page_config = array(
            'field' => "id,zc_nickname as nickname,zn_member_id,zc_personality_sign as personality_sign",
            'where' => $sqlwhere_parameter,
            'order' => 'zl_good_index ASC,zl_level ASC',
        );
        $count = $this->D_DESIGNER->lqCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, 10);//载入分页类
        //分页尽头
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["designer_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }

        $list = $this->D_DESIGNER->lqList($page->firstRow, $page->listRows, $page_config, $this->login_member_info);
        if (!$list) $list = array();
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }

    /////订单申请售后
    public function require_service()
    {
        //header('Access-Control-Allow-Origin:*');
        $note = "申请售后";

        $order_id = I("post.order_id", "0", "int");///订单编号
        $content = I("post.content", '');//问题描述

        if (!$order_id) $this->ajaxReturn(array('status' => 0, 'msg' => '订单号丢失', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$content) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入问题描述', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $upfile_data = $fileArr = array();
        if (is_weixin()) {
            $upfile_data = $this->opUploadMulti('images');

            if ($upfile_data['status'] == 1) {
                $album = implode(",", $upfile_data['url']);
            }
        } else {
            foreach ($_FILES as $k => $v) {
                $upfile_data[] = $this->opUpload($k, 'images');
            }

            foreach ($upfile_data as $lnKey => $laValue) {
                if ($laValue['status'] == 1) $fileArr[] = $laValue['url'];
            }
            $album = implode(",", $fileArr);
        }

        $order_no = M("hd_order")->where("id=" . $order_id)->getField("zc_order_no");
        if (!$order_no) $this->ajaxReturn(array('status' => 0, 'msg' => '订单丢失', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $data["zc_contact_mobile"] = $this->login_member_info["zc_mobile"];
        $data["zc_description"] = $content;
        $data["zc_album"] = $album;
        $data["zn_member_id"] = $this->login_member_info["id"];
        $data["zn_member_account"] = $this->login_member_info["zc_account"];
        $data["zl_type"] = 1;
        $data["zl_status"] = 0;
        $data["zn_hd_order_id"] = $order_id;
        $data["zc_order_no"] = $order_no;
        $data["zn_cdate"] = NOW_TIME;
        $data["zn_mdate"] = NOW_TIME;

        if (M("hd_order_service")->add($data)) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '售后申请成功！', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '售后申请失败...请重试', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 新建日记  日记属性获取
     * style : 装修风格
     * household : 户型
     */
    public function get_attribute()
    {
        $data = array();
        $style = F('hd_attribute_1', '', COMMON_ARRAY);
        $j = 0;
        foreach ($style as $k => $v) {
            $style_info[$j]['id'] = $k;
            $style_info[$j]['name'] = $v;
            $j++;
        }

        $household = F('hd_attribute_2', '', COMMON_ARRAY);
        $i = 0;
        foreach ($household as $k => $v) {
            $household_info[$i]['id'] = $k;
            $household_info[$i]['name'] = $v;
            $i++;
        }
        $data['style'] = $style_info;
        $data['household'] = $household_info;
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => '日记属性'), $this->JSONP);
    }

    /*
     * 新建日记详细  日记进度获取
     * 返回装修历程  准备。。。。入住
     */
    public function get_progress()
    {
        $data = array();
        $data = C("DIARY_STEP");
        $i = 0;
        foreach ($data as $k => $v) {
            $info[$i]['id'] = $k;
            $info[$i]['name'] = $v;
            $info[$i]['icon'] = API_DOMAIN . "/Public/Static/images/diary/step/" . $k . ".png";
            $i++;
        }

        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $info, "url" => "", "note" => '日记进度属性'), $this->JSONP);
    }

    /*
     * 会员  新建日记
     */
    public function add_diary()
    {
        $note = "新建日记";

        $title = I("post.title", '');//标题
        $area = I("post.area", '');//面积
        $household = I("post.household", '');//户型
        $style = I("post.style", '');//装修风格

        if (!$title) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入日记标题', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$area) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入您家面积', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$household) $this->ajaxReturn(array('status' => 0, 'msg' => '请选择你家的户型', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$style) $this->ajaxReturn(array('status' => 0, 'msg' => '请选择你家的装修风格', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        //会员信息
        if ($this->login_member_info['zc_headimg']) {
            if (substr($this->login_member_info['zc_headimg'], 0, 4) == 'http') {
                $member_img = $this->login_member_info['zc_headimg'];
            } else {
                $img = $this->login_member_info['zc_headimg_thumb'] ? $this->login_member_info['zc_headimg_thumb'] : $this->login_member_info['zc_headimg'];

                $member_img = API_DOMAIN . $img;
            }
        } else {
            $member_img = NO_HEADIMG;
        }

        $data["zc_title"] = $title;
        $data["zn_area"] = $area;
        $data["zc_style"] = $style;
        $data["zn_household"] = $household;
        $data["zn_member_id"] = $this->login_member_info["id"];
        $data["zc_member_account"] = $this->login_member_info["zc_account"];
        $data["zc_headimg"] = $member_img;
        $data["zc_nickname"] = $this->login_member_info["zc_nickname"];
        $data["zl_is_index"] = 0;
        $data["zl_member_apply"] = 0;
        $data["zn_cdate"] = NOW_TIME;
        $data["zn_mdate"] = NOW_TIME;

        if (M("hd_diary")->add($data)) {
            $this->model_member->addMemberLog('add_diary', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '新建日记成功！', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '新建日记失败...请重试', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 会员 添加日记详细
     */
    public function add_diary_detail()
    {
        $note = "新建日记详细";

        $diary_id = I("post.diary_id", '');//主日记id
        $title = I("post.title", '');//标题
        $content = I("post.content", '');//内容
        $progress = I("post.progress", '');//进度
        $date = I("post.date", '');//日期

        if (!$diary_id) $this->ajaxReturn(array('status' => 0, 'msg' => '参数丢失，请重试...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$title) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入日记内容', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$progress) $this->ajaxReturn(array('status' => 0, 'msg' => '请选择装修阶段', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$date) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入日期', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $upfile_data = $fileArr = array();
        if (is_weixin()) {
            $upfile_data = $this->opUploadMulti('images');

            if ($upfile_data['status'] == 1) {
                $album = implode(",", $upfile_data['url']);
            }
        } else {
            foreach ($_FILES as $k => $v) {
                $upfile_data[] = $this->opUpload($k, 'images');
            }

            foreach ($upfile_data as $lnKey => $laValue) {
                if ($laValue['status'] == 1) $fileArr[] = $laValue['url'];
            }
            $album = implode(",", $fileArr);
        }
        $album = $album ? $album : "";
        $data["zn_hd_diary_id"] = $diary_id;
        $data["zc_title"] = $title;
        $data["zc_content"] = $content;
        $data["zc_album"] = $album;
        $data["zl_order_progress"] = $progress;
        $data["zl_visible"] = 1;
        $data['zd_send_time'] = strtotime($date);
        $data["zn_cdate"] = NOW_TIME;
        $data["zn_mdate"] = NOW_TIME;

        $add_data = intval($this->D_HDDIARY->add_diary_detail($data));

        if ($add_data) {
            $this->model_member->addMemberLog('add_diary_detail', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '新建日记详细成功！', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => $add_data, 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 会员 获取日记明细内容
     */
    public function get_diary_detail()
    {
        $note = "获取日记详细";
        $tnid = $this->lqgetid;

        if (!$tnid) $this->ajaxReturn(array('status' => 0, 'msg' => '获取失败，请重试...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        $diary_info = $this->D_HDDIARY->getDiaryDetailById($tnid);

        if ($diary_info) {
            $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $diary_info, "url" => "", "note" => $note), $this->JSONP);
        } else $this->ajaxReturn(array('status' => 0, 'msg' => '获取失败，请重试...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
    }

    /*
     * 会员 编辑日记详细
     */
    public function edit_diary_detail()
    {
        //header('Access-Control-Allow-Origin:*');
        $note = "编辑日记详细";
        $tnid = I("post.tnid", '0', 'int');//日记id

        /////检测是否自己的日记
        if (!$this->D_HDDIARY->chk_diary_detail($this->login_member_info['id'], $tnid))
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法操作...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $title = I("post.title", '');//标题
        $content = I("post.content", '');//内容
        $progress = I("post.progress", '');//进度
        $date = I("post.date", '');//日期
        $old_album = I("post.old_album", '');//原来的图片

        if (!$tnid) $this->ajaxReturn(array('status' => 0, 'msg' => '参数丢失，请重试...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$title) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入日记标题', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$progress) $this->ajaxReturn(array('status' => 0, 'msg' => '请选择装修阶段', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        if (!$date) $this->ajaxReturn(array('status' => 0, 'msg' => '请输入日期', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $diary_detail = M("hd_diary_detail")->where("id=" . $tnid)->getField("zn_hd_diary_id");
        if (!$diary_detail) $this->ajaxReturn(array('status' => 0, 'msg' => '参数丢失，请重试...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $upfile_data = $fileArr = array();
        if (is_weixin()) {
            $upfile_data = $this->opUploadMulti('images');

            if ($upfile_data['status'] == 1) {
                $album = implode(",", $upfile_data['url']);
            }
        } else {
            foreach ($_FILES as $k => $v) {
                $upfile_data[] = $this->opUpload($k, 'images');
            }

            foreach ($upfile_data as $lnKey => $laValue) {
                if ($laValue['status'] == 1) $fileArr[] = $laValue['url'];
            }
            $album = implode(",", $fileArr);
        }
        if ($old_album) {
            $old_album = str_replace(API_DOMAIN, "", $old_album);

            if ($album) $album = $old_album . "," . $album;
            else $album = $old_album;
        }
        $album = $album ? $album : "";
        $data["id"] = $tnid;
        $data["zc_title"] = $title;
        $data["zc_content"] = $content;
        $data["zc_album"] = $album;
        $data["zl_order_progress"] = $progress;
        $data['zd_send_time'] = strtotime($date);
        $data["zn_mdate"] = NOW_TIME;
        /////重新编辑后，日记去首页要重新申请审核
        $data['zl_is_index'] = 0;
        $data['zl_member_apply'] = 0;

        $add_data = intval($this->D_HDDIARY->save_diary_detail($data));

        if ($add_data) {
            $this->model_member->addMemberLog('edit_diary_detail', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '日记编辑成功！', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => $add_data, 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 会员  删除日记详细
     */
    public function del_diary_detail()
    {
        $note = "删除日记详细";

        /////检测是否自己的日记
        if (!$this->D_HDDIARY->chk_diary_detail($this->login_member_info['id'], $this->lqgetid))
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法操作...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        if ($this->D_HDDIARY->delDiaryDetail($this->lqgetid)) {
            $this->model_member->addMemberLog('del_diary_detail', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '日记删除成功！', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '日记删除失败...请重试', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 我的日记 列表
     */
    public function my_diary()
    {
        $pageno = I("get.p", '1', 'int');//页码

        $sqlwhere_parameter = array(
            "zn_member_id" => $this->login_member_info["id"],
            "zl_visible" => 1
        );
        $page_config = array(
            'field' => "`id`,`zc_image` as image,`zc_title` as title,`zn_area` as area,`zn_household` as household,`zc_style` as style",
            'where' => $sqlwhere_parameter,
            'order' => 'zn_cdate DESC',
        );
        $count = $this->D_HDDIARY->lqCount($sqlwhere_parameter);
        $page = new \LQLibs\Util\Page($count, C("API_PAGESIZE")["diary_list"]);//载入分页类
        //分页尽头
        $note = '0';
        if ($pageno >= $page->totalPages) {
            $note = '0';
        } else {
            if ($count == (C("API_PAGESIZE")["diary_list"] * $pageno)) {
                $note = '0';
            } else {
                $note = '1';
            }
        }
        $list = $this->D_HDDIARY->lqList($page->firstRow, $page->listRows, $page_config);

        if (!$list) $list = array();
        else {
            foreach ($list as $lnKey => $laValue) {
                $step_info = array();
                $step_info = $this->D_HDDIARY->getLastStep($laValue["id"]);

                $list[$lnKey]["progress"] = $step_info["progress"];
                $list[$lnKey]["diary_detail_id"] = $step_info["detail_id"];
                unset($list[$lnKey]["headimg"]);
            }
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $list, "url" => "", "note" => $note), $this->JSONP);
    }

    /*
     * 日记详情
    */
    public function my_diary_detail()
    {
        $note = '个人日记详情';
        if (!$this->lqgetid) $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        /////检测是否自己的日记
        if (!$this->D_HDDIARY->chk_diary($this->login_member_info['id'], $this->lqgetid))
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法操作...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        $data = $this->D_HDDIARY->getInfoById($this->lqgetid);

        if ($data) {
            unset($data['detail']['page_view']);
            unset($data['detail']['agrees']);
            $this->ajaxReturn(array('status' => 1, 'msg' => '返回成功', 'data' => $data, "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /*
     * 个人日记  申请去首页
     */
    public function my_require()
    {
        $note = '个人日记-申请首页';
        if (!$this->lqgetid) $this->ajaxReturn(array('status' => 0, 'msg' => '返回失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        /////检测是否自己的日记
        if (!$this->D_HDDIARY->chk_diary($this->login_member_info['id'], $this->lqgetid))
            $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        /////申请过提示申请成功了
        $is_apply = M("hd_diary")->where("id=" . $this->lqgetid)->getField("zl_member_apply");
        if ($is_apply == 1) $this->ajaxReturn(array('status' => 1, 'msg' => '已申请成功，请等待管理员审核...', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);

        if (M("hd_diary")->where("id=" . $this->lqgetid)->setField("zl_member_apply", 1)) {
            $this->model_member->addMemberLog('require_diary', $this->login_member_info);//插入会员日志
            $this->ajaxReturn(array('status' => 1, 'msg' => '申请成功', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '申请失败', 'data' => array(), "url" => "", "note" => $note), $this->JSONP);
        }
    }

    /**
     *  个人贷款 进度
     */
    public function my_loan()
    {
        $sqlwhere = array(
            "zn_member_id" => $this->login_member_info['id']
        );
        $note = "我的贷款";
        $apply = $this->D_LOANAPPLY->isApply($sqlwhere);
        if (!$apply) {
            $data["is_apply"] = 0;
            $data["step"] = array();
            $this->ajaxReturn(array('status' => 1, 'msg' => '您暂时没有申请贷款，快快申请吧', 'data' => $data, "url" => "", "note" => $note), $this->JSONP);
        } else {
            $data["is_apply"] = 1;
            $data["step"] = $this->D_LOANAPPLY->getStep($apply);

            $this->ajaxReturn(array('status' => 1, 'msg' => '获取贷款进度', 'data' => $data, "url" => "", "note" => $note), $this->JSONP);
        }
    }
}