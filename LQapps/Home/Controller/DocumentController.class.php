<?php

namespace Home\Controller;
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class DocumentController extends PublicController{

    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}
		
    public function index(){
		$list = M("api_document")->field("*")->where("zl_visible=1")->order("zl_type desc,zn_sort asc,id desc")->select();
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['no'] = $lnKey+2;
		}
        $this->assign("list",$list);
		$this->assign("title",'移动数据API文档');//seo数据
		$this->display();
    }


}