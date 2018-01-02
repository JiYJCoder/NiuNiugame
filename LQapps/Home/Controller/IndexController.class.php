<?php
/*
描述：首页
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
家居:ha(home-appliance)*/
namespace Home\Controller;
use Think\Controller;
defined('in_lqweb') or exit('Access Invalid!');

class IndexController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
	
	//PC首页
    public function index(){
		$lcdisplay='Index/index';//引用模板
		$this->display($lcdisplay);
    }
	
	//应用宝地址
    public function app_download(){
		exit('<!DOCTYPE html><head><meta charset="utf-8"><title>转载中</title>
				<meta name="viewport" content="initial-scale=1, maximum-scale=1"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black"></head><body>
				</body></html>
				<script type="text/javascript">
					location.href="http://a.app.qq.com/o/simple.jsp?pkgname=com.dreamhome.jianyu.dreamhome";
				</script>');
    }	
	
	//获得openid
    public function return_openid(){
			if(!session('openid')){
				lq_return_openid(U('home/index/return_openid'));
			}else{
				lq_header("Location:http://wx.lxjjz.cn/wx/views/my/login.html");
			}
    }


}