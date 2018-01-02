<?php
/*
描述：微信公共接口
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
家居:ha(home-appliance)*/

namespace Home\Controller;
use Think\Controller;
use Member\Api\MemberApi as MemberApi;

class WechatController extends Controller {
	protected $model_member;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->model_member = new MemberApi;//实例化会员
    }
		
    public function index(){
		//接入微信类
		import('Vendor.Wechat.TPWechat');
		$WxObj = new \Wechat(C("WECHAT"));
		$WxObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		$type = $WxObj->getRev()->getRevType();
		switch($type) {
			case $WxObj::MSGTYPE_TEXT:
					$lcRevContent=$WxObj->getRev()->getRevContent();
					$lcRevContent=trim($lcRevContent);
					if($lcRevContent){
							$data=D("Api/Wxrobot")->getByKeyword($lcRevContent);
							if($data){
								if($data["type"]==1){//消息
								$WxObj->text($data["reply"])->reply();//自动回复
								}elseif($data["type"]==2){//图文
									$newsData=array(
										array(
										'Title'=>$data["title"],
										'Description'=>$data["reply"],
										'PicUrl'=>$data["image"],
										'Url'=>str_replace('&amp;','&',$data["url"])
									));
								$WxObj->news($newsData)->reply();//自动回复
								}else{
								//$WxObj->text("您好！欢迎访问狸想家。有什么可以帮到您？")->reply();//自动回复
								}
							}
					}
					break;
			case $WxObj::MSGTYPE_EVENT:
				$eventArray = $WxObj->getRev()->getRevEvent();
				switch ($eventArray['event']) {
					case $WxObj::EVENT_SUBSCRIBE:
						//扫带参数的二维码
						$qrcode_string=$WxObj->getRevSceneId();
						if($qrcode_string){
							session("register_share",array('type' =>3, 'referer' => $qrcode_string));
						}
						if(!session("register_share")) session("register_share",array('type' =>3, 'referer' =>5));
						
						$UserInfo = $WxObj->getUserInfo($WxObj->getRevFrom());
						//微信用户入库
						if($UserInfo){
							session('openid', $UserInfo["openid"]);
							session('nickname', $UserInfo["nickname"]);
							$follow_data=M("follow")->field('id,zc_openid')->where("zc_openid='".$UserInfo["openid"]."'")->find();
							if(!$follow_data){
								$data=array();
								$data["zl_type"]=1;//关注
								$data["zc_openid"]=$UserInfo["openid"];
								$data["zc_nickname"]=lq_set_nickname($UserInfo["nickname"]); 
								$data["zn_sex"]=$UserInfo["sex"];
								$data["zc_country"]=$UserInfo["country"];
								$data["zc_province"]=$UserInfo["province"];
								$data["zc_city"]=$UserInfo["city"];
								$data["zc_language"]=$UserInfo["language"];
								$data["zc_headimg_url"]=$UserInfo["headimgurl"];
								$data["zc_remark"]=$UserInfo["remark"];
								$data["zn_groupid"]=$UserInfo["groupid"];
								$data["zn_subscribe_time"]=$UserInfo["subscribe_time"];
								$data["zn_unsubscribe_time"]=0;
								$data["zl_visible"]=1;
								$data["zn_cdate"]=$UserInfo["subscribe_time"];
								$this->model_member->apiInsertFollow($data);
							}else{
								$this->model_member->apiUpdateFollow(array("zl_type"=>1,"zc_nickname"=>lq_set_nickname($UserInfo["nickname"]),"zl_visible"=>1,"zn_subscribe_time"=>$UserInfo["subscribe_time"]),$follow_data["zc_openid"]);
							}	
						}
						break;
					case $WxObj::EVENT_UNSUBSCRIBE:
						$UserInfo = $WxObj->getUserInfo($WxObj->getRevFrom());
						if($UserInfo){
							$this->model_member->apiUpdateFollow(array("zc_nickname"=>lq_set_nickname($UserInfo["nickname"]),"zl_visible"=>0,"zn_unsubscribe_time"=>NOW_TIME),$UserInfo["openid"]);
						}		
						break;
					case $WxObj::EVENT_SCAN:
						//$this->scan ();
						break;
					case $WxObj::EVENT_LOCATION:
						//$this->location ();
						break;
					case $WxObj::EVENT_MENU_VIEW:
						//$this->click ();
						break;
					case $WxObj::EVENT_MENU_CLICK:
						//$this->click ();
						break;
				}
					break;
			case $WxObj::MSGTYPE_IMAGE:
					break;
			default:
					//$WxObj->text("狸想家帮助中心")->reply();//帮助中心
		}
    }
	

		
}