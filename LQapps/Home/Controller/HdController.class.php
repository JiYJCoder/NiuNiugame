<?php
/*
描述：公共引导页
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
*/
namespace Home\Controller;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class HdController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
		lq_set_theme("default");//转换模板
	}
	
	//美家首页
    public function index(){
		$seo_data=array();
		$seo_data["seo_title"]='美家';
		$this->assign("seo_data",$this->getSeoData($seo_data));//seo数据
		$this->assign("home_banner",D("Api/AdPosition")->getAdPositionById(1));//美家首页BANNER
		$this->assign("lq_top_button","");//背景透明
		$lcdisplay='Hd/index';//引用模板
		$this->display($lcdisplay);
    }
	//装修贷申请页面
    public function loan_period(){
		$seo_data=array();
		$seo_data["seo_title"]='装修贷申请';
		$this->assign("seo_data",$this->getSeoData($seo_data));//seo数据
		$this->assign("jy_layout_css","");//jy_layout
		$lcdisplay='Hd/loan-period';//引用模板
		$this->display($lcdisplay);
    }				

}