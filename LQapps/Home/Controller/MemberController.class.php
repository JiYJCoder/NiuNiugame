<?php
/*
描述：会员中心
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
*/
namespace Home\Controller;
use Think\Controller;
defined('in_lqweb') or exit('Access Invalid!');

class MemberController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
	
	//会员中心首页
    public function index(){
		$lcdisplay='Index/index';//引用模板
		$this->display($lcdisplay);
    }
	


}