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
use LQLibs\Util\Category as Category;//树状分类

defined('in_lqweb') or exit('Access Invalid!');
class ProductController extends PublicController {
	protected $D_PRO;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->D_PRO=D("Api/Product");//产品
        self::apiCheckToken(0);//用户认证
    }
	
	//首页
    public function index(){
		$data=$brand=array();
		
		//产品首页BANNER

		$data["banner"]=D("Api/AdPosition")->getAdPositionById(3)["list"];
		
		//建材推荐
		$index_product_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id,`zc_thumb` as image,`zc_title` as title,`zn_agrees` as agrees,`zf_shop_price` as price",
				'where'=>'zl_is_index=1 and zl_visible=1',
				'order'=>'zn_sort,zn_page_view desc,id desc',
		);
        $index_product=$this->D_PRO->lqList(0,6,$index_product_config);
        foreach($index_product as $lnKey => $laValue){
            $index_product[$lnKey]["is_agress"] = $this->model_member->apiTestLove($laValue["id"],4,$this->login_member_info) ? 1:0;
        }

        $data["index_product"] = $index_product;
		//品牌推荐
		$brand_product_config = array(
				'field'=>"`id`,`zc_company_name` as company_name,`zc_caption` as caption,`zc_logo` as logo,`zc_logo_bgcolor` as bgcolor",
				'where'=>'zl_recommend=1 and zl_visible=1',
				'order'=>'zn_sort,id desc',
		);	 		
		$brand_product=$this->D_PRO->brandProduct(0,C("API_PAGESIZE")["product_list"],$brand_product_config);
		$data["brand_product"]=$brand_product;
		$this->ajaxReturn(array('status'=>1,'msg'=>'首页','data' =>$data,"url"=>"","note"=>"首页"),$this->JSONP);
    }
	
	//品牌推荐列表-分页数据输出
	public function brand_product(){
		$pageno=I("get.p",'2','int');//页码
		$sqlwhere_parameter=' zl_recommend=1 and zl_visible=1 ';//sql条件
		$page_config = array(
				'field'=>"`id`,`zc_company_name` as company_name,`zc_caption` as caption,`zc_logo` as logo,`zc_logo_bgcolor` as bgcolor",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort,id desc',
		);	 
		C("PAGESIZE",6);
        $count = $this->D_PRO->brandProductCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count,C("PAGESIZE"));//载入分页类
		//分页尽头
	    if($pageno>=$page->totalPages){
				$note='0';
		}else{
			if($count==(C("PAGESIZE")*$pageno)){
				$note='0';
			}else{
				$note='1';
			}
		}
		$list=$this->D_PRO->brandProduct($page->firstRow, $page->listRows,$page_config);
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$list,"url"=>"","note"=>$note),$this->JSONP);
	}		
	
	//产品分类页
	public function all(){
		$data=$brand=array();
		$data["category"] = $this->D_PRO->catAll();
		$list=$this->D_PRO->brandAll();
		foreach ($list as $lnKey => $laValue) {
			if($laValue["recommend"]) $brand[]=$laValue;
		}
		$data["brand"] = $brand;
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'产品分类页'),$this->JSONP);
	}	
	

	//产品列表-数据输出
	public function product_list(){
		$brand= I("get.brand",'0','int');//品牌
		$catid= I("get.catid",'0','int');//分类
		$by= I("get.by",'0','int');//0综合，1人气,2价格降序,3价格升序
		$pageno=I("get.p",'1','int');//页码
		if($by==1){
			$orderby='zn_page_view desc,id desc';
		}else if($by==2){
			$orderby='zf_shop_price desc,id desc';				
		}else if($by==3){
			$orderby='zf_shop_price asc,id desc';				
		}else{
			$orderby='zn_sort asc,id desc';				
		}
		
		//作品列表
		$sqlwhere_parameter=" zl_visible=1 ";//sql条件
        $title = '';
		if($brand){
				$sqlwhere_parameter.=" and zn_product_brand_id = ".intval($brand);
                $title = M("product_brand")->where("id =".intval($brand))->getField("zc_caption");
		}
		if($catid){
			$tree = new Category('product_cat', array('id', 'zn_fid', 'zc_caption'));
			$child_ids = $tree->get_child($catid,10,'zl_visible=1');
			if (ereg("^[0-9]+$", $child_ids )){
				$sqlwhere_parameter.=" and zn_cat_id = ".intval($child_ids);
			}else{
				$sqlwhere_parameter.=" and zn_cat_id in (".$child_ids.") ";
			}

            $title = $title . " " .M("product_cat")->where("id =".intval($catid))->getField("zc_caption");
		}		
		$page_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id,`zc_thumb` as image,`zn_thumb_width` as thumb_width,`zn_thumb_height` as thumb_height,`zc_title` as title,`zf_shop_price` as price,`zn_agrees` as agrees",
				'where'=>$sqlwhere_parameter,
				'order'=>$orderby,
		);
		C("PAGESIZE",10);
        $count = $this->D_PRO->lqCount($sqlwhere_parameter);
		$page = new \LQLibs\Util\Page($count,C("PAGESIZE"));//载入分页类
		
		//分页尽头
	    if($pageno>=$page->totalPages){
				$note='0';
		}else{
			if($count==(C("PAGESIZE")*$pageno)){
				$note='0';
			}else{
				$note='1';
			}
		}
		$list=$this->D_PRO->lqList($page->firstRow, $page->listRows,$page_config);
        foreach($list as $lnKey => $laValue){
            $list[$lnKey]["is_agress"] = $this->model_member->apiTestLove($laValue["id"],4,$this->login_member_info) ? 1:0;
        }
		$this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$list,'title'=>$title,"url"=>$sqlwhere_parameter,"note"=>$note),$this->JSONP);
	}	

	//建材甄选详情-数据输出
	public function product_show(){
        $wid = I("get.wid","1","int");
		$data = $this->D_PRO->getProductById($this->lqgetid);
		if($data){
            $data["is_agress"] = $this->model_member->apiTestLove($data["id"],4,$this->login_member_info) ? 1:0;
           // if($wid == 1) $w_url = 'http://wx.lxjjz.cn/wx/views/product/detail.html?tnid='.$this->lqgetid;
            //else $w_url = 'http://wx.lxjjz.cn/wx/views/product/productList.html?catid='.$data["cat_id"];
			
			//微信的JSSDK
			if(is_weixin()){
				$wx_share_config=array("url"=>cookie('referer'),"title"=>$data["title"],"link"=>'http://wx.lxjjz.cn/wx/views/product/detail.html?tnid='.$data["id"],"imgUrl"=>$data["image"],"desc"=>lq_kill_html($data["content"],30));
				$data["wx_jssdk"]=lq_get_jssdk(C("WECHAT"),$wx_share_config);
			}
		    $this->ajaxReturn(array('status'=>1,'msg'=>'返回成功','data' =>$data,"url"=>"","note"=>'建材详情'),$this->JSONP);
		}else{
		    $this->ajaxReturn(array('status'=>0,'msg'=>'返回失败','data' =>array(),"url"=>"","note"=>'建材详情'),$this->JSONP);
		}
	}	
    public function product_display(){
		$data = $this->D_PRO->getProductById($this->lqgetid);
        $this->assign("data",$data);
		$this->display("Display/product");
    }	
	
	//产品访问统计
	public function product_view_count(){
		//设置请求记录*************start***************
		if(!check_session_request("product_view_count")) $this->ajaxReturn(array('status'=>0,'msg'=>'您的请求次数太频繁,请休息一会！','data' =>'',"url"=>"","note"=>''),$this->JSONP);
		set_session_request("product_view_count");//设置请求记录
		//设置请求记录*************start***************			
		
		$id=$this->lqgetid;
		$returnData=$this->D_PRO->setViewCount($id);
		if($returnData["status"]){
			$info=PAGE_S("page_product_".$id,'',$this->cache_options); //读取缓存数据
			$info["page_view"]=$returnData["data"];
			PAGE_S("page_product_".$id,$info,$this->cache_options); //缓存数据	
		}		
		$this->ajaxReturn($returnData,$this->JSONP);
	}

    public function p_test()
    {
        //$is_pay = M("hd_application")->where("id=11")->getField('zl_deposit_pay');
        //lq_test($is_pay);
    }


	
}