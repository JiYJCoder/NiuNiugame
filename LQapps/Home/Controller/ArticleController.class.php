<?php

namespace Home\Controller;
use LQLibs\Util\Category as Category;//树状分类
use Think\Controller;

defined('in_lqweb') or exit('Access Invalid!');

class ArticleController extends PublicController{
	protected $D_ART;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->D_ART=D("Api/Article");
	}
	
	//首页
    public function index(){
		
    }
	
	###################装修攻略############################
	//首页
    public function zxgl(){
		$seo_data=array();
		$seo_data["seo_title"]='装修攻略';
		$this->assign("seo_data",$this->getSeoData($seo_data));//seo数据

		//装修攻略-精品文章
		$this->assign("good_article",$this->D_ART->getGoodArticle(5));
		//装修攻略-分类下的首页文章
		$this->assign("cat_index_article",$this->D_ART->getCatIndexArticle(5,'zxgl-list'));
		
		$lcdisplay='Article/zxgl-index';//引用模板
		$this->display($lcdisplay);		
    }	
	//列表页
	 public function zxgl_list(){
		$cat=$this->D_ART->getCatById($this->lqgetid);
		if(!$cat) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录		
		$this->assign("cat",$cat);
		$seo_data=array();
		$seo_data["seo_title"]=$cat["zc_caption"];
		$this->assign("seo_data",$this->getSeoData($seo_data));//seo数据		
		
		$sqlwhere_parameter=" zl_visible=1 ";//sql条件
		$tree = new Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($this->lqgetid,10,'zl_visible=1');
		if (ereg("^[0-9]+$", $child_ids )){
				$sqlwhere_parameter.=" and zn_cat_id = ".intval($child_ids);
		}else{
				$sqlwhere_parameter.=" and zn_cat_id in (".$child_ids.") ";
		}
				 
		$page_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id ,`zc_image` as image,`zc_title` as title,`zd_send_time` as send_time,`zc_summary` as summary,`zn_page_view` as page_view,`zn_share` as share,`zc_author`,`zc_source`",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort ASC,zd_send_time DESC',
		);			 
		$list=$this->D_ART->lqList(0,C("API_PAGESIZE")["article_list"],$page_config);
		$this->assign("list",$list);
		
		
		$lcdisplay='Article/zxgl-list';
		$this->display($lcdisplay);		
	 }
	###################装修攻略############################
	
	
		
	//展示页
    public function show(){
		//读取文章数据
		$data = $this->D_ART->getArticleById($this->lqgetid);
		if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录		
		$this->assign("seo_data",$this->getSeoData($data));//seo数据
		$this->assign("data",$data);//文章数据
		
		$lcdisplay='Article/zxgl-detail';//引用模板
		$this->display($lcdisplay);
    }	
	//访问统计
	public function set_view(){$this->ajaxReturn($this->D_ART->setViewCount($this->lqgetid));}		
	
	

}