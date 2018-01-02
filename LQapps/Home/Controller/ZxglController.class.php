<?php
/*
描述：装修攻略
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
*/
namespace Home\Controller;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class ZxglController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
	
	//首页
    public function index(){
		$seo_data=array();
		$seo_data["seo_title"]='装修攻略';
		$this->assign("seo_data",$this->getSeoData($seo_data));//seo数据
		
		
		$lcdisplay='Hd/index';//引用模板
		$this->display($lcdisplay);
    }
			

}