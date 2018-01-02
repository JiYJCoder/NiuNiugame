<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
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
use Member\Api\MemberApi as MemberApi;

defined('in_lqweb') or exit('Access Invalid!');
//首页
class IndexController extends Controller{
	public $model_member;
    /** 初始化*/
    public function __construct(){
		parent::__construct();
		$this->model_member = new MemberApi;//实例化会员
	}

	//首页
    public function index(){
		$this->ajaxReturn(array('status'=>0,'msg'=>'当前端口暂时关闭','data' =>array(),"url"=>"","note"=>""));
    }

	//清除
	public function kill_openid(){
		session('openid',NULL);
		setcookie("openid",NULL, time()-3600);
		setcookie("uid",NULL, time()-3600);
		setcookie("token",NULL, time()-3600);
		setcookie("referer",NULL, time()-3600);
		lq_header("Location:http://wx.lxjjz.cn/wx/views/index.html");
	}	
	
	//安卓的版本号更新检测
    public function android_update(){
		//设置请求记录*************start***************
		if(!check_session_request("android_update")) $this->ajaxReturn(array('status'=>0,'msg'=>'您的请求次数太频繁,请休息一会！','data' =>'',"url"=>"","note"=>''),$this->JSONP);
		set_session_request("android_update");//设置请求记录
		//设置请求记录*************start***************				
		
		$data = M("android_version")->field("zc_download_url as url,zc_version_code as version_code,zc_version_name as version_name,zc_content as message,zn_size as size")->where("zl_visible=1")->order("id desc")->limit('0,1')->find();
		if($data){
		$data["message"]=html_entity_decode($data["message"]);
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'安卓的版本号更新检测'),$this->JSONP);
		}else{
		$this->ajaxReturn(array('status'=>0,'msg'=>'返回失败','data' =>array(),"url"=>"","note"=>'安卓的版本号更新检测'),$this->JSONP);
		}
    }
	
	//售我
	public function sale_me(){
        if (session('openid')) {
            $member_id = $this->model_member->apiGetField("zc_openid='" . session('openid') . "'", "id");
			if(!$member_id) $member_id=5;
			
			//接入微信类
			import('Vendor.Wechat.TPWechat');
			$WxObj = new \Wechat(C("WECHAT"));
			$ticket_data = $WxObj->getQRCode($member_id,1,1800);//创建二维码
			echo '<img src="'.$WxObj->getQRUrl($ticket_data["ticket"]).'" width="100%">';			
			
        } else {
			cookie("referer", 'http://wx.lxjjz.cn/do?g=api&m=index&a=sale_me');
            lq_return_openid(U('api/index/sale_me'));
			die();
        }
		die();
        if (session('openid')) {
            $member_id = $this->model_member->apiGetField("zc_openid='" . session('openid') . "'", "id");
			if(!$member_id) $member_id=5;
			session("register_share",array('type' =>1, 'referer' => $member_id));			
            $url = 'http://wx.lxjjz.cn/wx/views/my/login.html';
			cookie("referer", 'http://wx.lxjjz.cn/wx/views/my/index.html');
        } else {
			cookie("referer", 'http://wx.lxjjz.cn/do?g=api&m=index&a=sale_me');
            lq_return_openid(U('api/index/sale_me'));
			die();
        }	
		//生成二维码
		Vendor('phpqrcode.phpqrcode');
		$day=date("Y-m-d");
		$errorCorrectionLevel =3 ;//容错级别 
		$matrixPointSize =10;//生成图片大小 
		$QRcodeObj = new \QRcode();
		$QRcodeObj->png($url,false, $errorCorrectionLevel, $matrixPointSize, 2); 
	}
	
	//测试
	public function test(){
		//go_error_page();
	}	
	

}
?>