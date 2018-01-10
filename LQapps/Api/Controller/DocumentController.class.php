<?php
/*
描述：接口文档说明
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
家居:ha(home-appliance)*/
namespace Api\Controller;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class DocumentController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
		
    public function index(){

		$list = M("api_document")->field("*")->where("zl_visible=1")->order("zn_sort asc,id desc")->select();
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['no'] = $lnKey+2;
		}
        $this->assign("list",$list);
		$this->assign("title",'移动数据API文档');//seo数据
		$this->display();
    }


}