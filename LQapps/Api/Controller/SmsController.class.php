<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:短信接口
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

class SmsController extends PublicController {
    protected $SMS;
    public function __construct() {
		parent::__construct();
		$this->SMS=D("Api/SmsLog");
		
		
		//免死金牌
		$action_no_login_array=array('login','register','hd_application');
		if( in_array(ACTION_NAME,$action_no_login_array) ){
			
		}else{
			//self::checkLogin();//用户认证
		}			
		
		
    }

	//首页数据包
    public function index(){
		$this->ajaxReturn($this->returnData);
	}
	
	//短信注册请求：验证码
    public function login(){
		$mobile=I("get.mobile",'');//手机号
		$code=lq_random_string(6,1);//随机码
		$tempId=125456;//模板ID
		if(!isMobile($mobile)) $this->ajaxReturn(array('status'=>0,'msg'=>'手机号不正确！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
		if(!$this->SMS->isAllowReceive($mobile,'login')){
			$this->ajaxReturn(array('status'=>0,'msg'=>'对不起，不能频繁请求操作！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
		}
				
		$sms_data=lqSendSms($mobile,array($code),$tempId);
		if($sms_data["status"]==1){
			$this->SMS->addSms('login',$mobile,$code);
			$this->returnData=array('status'=>1,'msg'=>'短信发送成功！','data' =>array(),"url"=>"","note"=>"");
		}else{
			$this->returnData=array('status'=>0,'msg'=>'短信发送失败！','data' =>array(),"url"=>"","note"=>"");
		}
		$this->ajaxReturn($this->returnData,$this->JSONP);
    }

	
	//申请设计请求：验证码
    public function hd_application(){
		$mobile=I("get.mobile",'');//手机号
		$code=lq_random_string(6,1);//随机码
		$tempId=125456;//模板ID
		if(!isMobile($mobile)) $this->ajaxReturn(array('status'=>0,'msg'=>'手机号不正确！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
		if(!$this->SMS->isAllowReceive($mobile,'hd_application')){
			$this->ajaxReturn(array('status'=>0,'msg'=>'对不起，不能频繁请求操作！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
		}
		$sms_data=lqSendSms($mobile,array($code),$tempId);//发送短信
		if($sms_data["status"]==1){//成功
			$this->SMS->addSms('hd_application',$mobile,$code);
			$this->returnData=array('status'=>1,'msg'=>'短信发送成功！','data' =>array(),"url"=>"","note"=>"");
		}else{//失败
			$this->returnData=array('status'=>0,'msg'=>'短信发送失败！','data' =>array(),"url"=>"","note"=>"");
		}
		$this->ajaxReturn($this->returnData,$this->JSONP);
    }

    //贷款请求：验证码
    public function loan_apply(){
        $mobile=I("get.mobile",'');//手机号
        $code=lq_random_string(6,1);//随机码
        $tempId=125456;//模板ID
        if(!isMobile($mobile)) $this->ajaxReturn(array('status'=>0,'msg'=>'手机号不正确！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
        if(!$this->SMS->isAllowReceive($mobile,'loan_apply')){
            $this->ajaxReturn(array('status'=>0,'msg'=>'对不起，不能频繁请求操作！','data' =>array(),"url"=>"","note"=>""),$this->JSONP);
        }
        $sms_data=lqSendSms($mobile,array($code),$tempId);//发送短信
        if($sms_data["status"]==1){//成功
            $this->SMS->addSms('loan_apply',$mobile,$code);
            $this->returnData=array('status'=>1,'msg'=>'短信发送成功！','data' =>array(),"url"=>"","note"=>"");
        }else{//失败
            $this->returnData=array('status'=>0,'msg'=>'短信发送失败！','data' =>array(),"url"=>"","note"=>"");
        }
        $this->ajaxReturn($this->returnData,$this->JSONP);
    }


}