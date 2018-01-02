<?php
/*
描述：公共文件
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
家居:ha(home-appliance)*/

namespace Home\Controller;
use LQPublic\Controller\Base;
use Member\Api\MemberApi as MemberApi;
defined('in_lqweb') or exit('Access Invalid!');

class PublicController extends Base{
	public $lqgetid,$lqpostid,$set_config;
    public function __construct() {
		parent::__construct();
		$this->lqgetid=isset($_GET["tnid"])?intval($_GET["tnid"]):0;
		$this->lqpostid=isset($_POST["fromid"])?intval($_POST["fromid"]):0;
		$this->set_config=F('set_config','',COMMON_ARRAY);
		if($this->set_config["WEB_DOMAIN"]==''){$this->set_config["WEB_DOMAIN"]='http://'.$_SERVER['HTTP_HOST'].'/';}
		$this->set_config["SEO_COPYRIGHT"] = html_entity_decode($this->set_config["SEO_COPYRIGHT"]);
		$this->set_config["A_LOGO"] = '<a title="'.$this->set_config["WEB_ITEMSNAME"].'" href="'.U("home/index/index").'"><img src="'.$this->set_config["WEB_LOGO"].'" border="0" alt="'.$this->set_config["WEB_ITEMSNAME"].'"/></a>';
		$this->assign('empty_msg','<div id="null_record">暂无数据</div>');
		$this->assign("SET_CONFIG",$this->set_config);//基本设置
		
		lq_set_theme("pc");//转换模板
		$this->commonAssign();//页面公共标签赋值
    }
	
	//获得seo数据
    protected function getSeoData($data=array()){
		$seo_data=array();
		$seo_data["title"]= $data["seo_title"]=='' ? $this->set_config["SEO_TITLE"] : $data["seo_title"];
		$seo_data["keywords"]= $data["seo_keywords"]=='' ? $this->set_config["SEO_KEYWORDS"] : $data["seo_keywords"];
		$seo_data["description"]= $data["seo_description"]=='' ? $this->set_config["SEO_DESCRIPTION"] : $data["seo_description"];
		return $seo_data;
    }

	//页面公共标签赋值
	public function commonAssign(){
		//过滤直接访问public
		if(CONTROLLER_NAME=='Public') $this->redirect('home/index/index');

		//头部菜单
		

						
	}	
	
	######################页面#####################
	//404页面  : 
	public function p404(){
		$data=array(
			'title'=>'对不起，您访问的地址不存在或网站过期  - '.$this->set_config["WEB_ITEMSNAME"],
			'url'=>U('home/index/index'),
			'step'=>5
		);
		$this->assign("data",$data);//页面数据
		$lcdisplay='Public/404';//引用模板
		$this->display($lcdisplay);		
	}


	
}
?>