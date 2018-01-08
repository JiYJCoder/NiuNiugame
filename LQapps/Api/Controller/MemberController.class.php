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

defined('in_lqweb') or exit('Access Invalid!');

class MemberController extends PublicController
{
    protected $D_DESIGNER, $D_ART, $D_PRO, $D_HDDIARY, $D_SMS, $model_region, $easemob_prefix, $easemob_password, $D_LOANAPPLY;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->D_SMS = D("Api/SmsLog");//接口短信实例化

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
        }
    }
    //注册
    public  function registered()
    {
        $nikeName = I('post.nikename');
        $password = I('post.password');
        $mobile = I('post.mobile');
        $code = I('post.code');
        $data = array('zc_password'=>$password,"zc_nickname"=>$nikeName,'zc_mobile'=>$mobile);
        $yzm=$this->D_SMS->isEffective($mobile,'registered',$code);
        if(!$yzm){
            $this->ajaxReturn(array('msg'=>'验证码不正确','status'=>0));
        }
        $flag = $this->model_member->apiRegister($data);
        if($flag){
            $this->ajaxReturn(array('msg'=>'注册成功','status'=>1));
        }else{
            $this->ajaxReturn(array('msg'=>'注册失败','status'=>0));
        }
    }
    //修改密码
    public function changePassword(){
        $password = I('post.password');
        $mobile = I('post.mobile');
        $code = I('post.code');
        $yzm=$this->D_SMS->isEffective($mobile,'changePassword',$code);
        if(!$yzm){
            $this->ajaxReturn(array('msg'=>'验证码不正确','status'=>0));
        }
        $data = array('zc_password'=>md5($password));
        $flag= $this->model_member->apiSaveMember($data);
        if($flag){
            $this->ajaxReturn(array('msg'=>'修改成功','status'=>1));
        }else{
            $this->ajaxReturn(array('msg'=>'修改失败','status'=>0));
        }
    }
    //app会员登录
    public function login()
    {
        $mobile = I("post.mobile", '');//手机号码
        $password = I('post.password');
        if (!isMobile($mobile)) $this->ajaxReturn(array('status' => 0, 'msg' => '会员登录失败,手机号码不符合规则！', 'data' => ''));
        //查库有没有会员
        $member_info = $this->model_member->apiGetField("zc_mobile='" . $mobile . "'", '*');
        if (is_array($member_info)) {
            if($member_info['zc_password']==md5($password)){
                //更新信息
                $UpdateLoginData = $this->model_member->apiUpdateLogin($member_info["id"]);
                $member_info["zn_login_times"] = $UpdateLoginData["zn_login_times"];
                $member_info["zn_last_login_ip"] = $UpdateLoginData["zn_last_login_ip"];
                $member_info["zn_last_login_time"] = $UpdateLoginData["zn_last_login_time"];
                $member_info["zn_mdate"] = $UpdateLoginData["zn_mdate"];
                $member_info["zn_trylogin_times"] = $UpdateLoginData["zn_trylogin_times"];
                $member_info["zn_trylogin_lasttime"] = $UpdateLoginData["zn_trylogin_lasttime"];

                $this->login_member_info = $member_info;
                $this->login_member_info["client_type"] = 'APP';//token重要标识
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
                $this->ajaxReturn(array('status' => 1, 'msg' => '会员登录成功', 'data' => array('uid' => $this->login_member_info["id"], 'token' => $token, 'member_info' => $info), "url" => "", "note" => '会员登录'));
            }else{
                $this->ajaxReturn(array('status' => 0, 'msg' => '密码错误'));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '帐号不存在'));
        }

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
}