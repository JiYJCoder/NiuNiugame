<?php //产品系统 Product 数据处理，数据回调
namespace Api\Model;
use LQLibs\Util\Category as Category;//树状分类
defined('in_lqweb') or exit('Access Invalid!');

class ProductModel extends PublicModel {
	protected $model_brand,$model_product_cat,$product_cat;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'product';
    public function __construct() {
		parent::__construct();
		$this->model_brand=M("product_brand");//品牌模型
		$this->model_product_cat=M("product_cat");//产品分类模型
		$this->product_cat=lq_return_array_one(F('product_cat','',COMMON_ARRAY),'id','zc_caption');//产品分类
	}
	
	//产品分类页
	public function catAll() {
		$array=PAGE_S("page_product_cat_all",'',$this->cache_options); //读取缓存数据 
		if($array) return $array;
		
		$cat_list=$this->model_product_cat->field("`id`,`zc_caption`")->order('zn_sort,id desc')->where("zl_visible=1 and zn_fid=0")->select();
        $array=array();
		foreach ($cat_list as $lnKey => $laValue) {
			$child=array();
			$child_list=$this->model_product_cat->field("`id`,`zc_caption`,`zc_image`")->order('zn_sort,id desc')->where("zl_visible=1 and zn_fid=".$laValue["id"])->select();	
			foreach ($child_list as $k => $v) {
				$icon=$v["zc_image"];
				if($icon){
					$icon=API_DOMAIN.$v["zc_image"];
				}else{
					$icon=NO_PICTURE;
				}
				$child[]=array("id"=>$v["id"],"title"=>$v["zc_caption"],"icon"=>$icon);
			}
			$array[]=array("id"=>$laValue["id"],"title"=>$laValue["zc_caption"],"child"=>$child);
		}
        PAGE_S("page_product_cat_all",$array,$this->cache_options); //缓存数据
		return $array;
	}
	//品牌&商品
    public function brandAll() {
		$list=PAGE_S("page_brand_all",'',$this->cache_options); //读取缓存数据 
		if($list) return $list;
		//品牌&商品
		$list = $this->model_brand->field("`id`,`zc_company_name` as company_name,`zc_caption` as caption,`zc_logo` as logo,`zc_logo_bgcolor` as bgcolor,`zl_recommend` as recommend")->order('zn_sort','id desc')->where("zl_visible=1")->select();
        foreach ($list as $lnKey => $laValue) {
			if($laValue["logo"]){
				$list[$lnKey]['logo'] = API_DOMAIN.$laValue["logo"];
			}else{
				$list[$lnKey]['logo'] = NO_PICTURE;
			}			
        }
        PAGE_S("page_brand_all",$list,$this->cache_options); //缓存数据
		return $list;
    }	
	
	
	//品牌&商品***************************start********************************
	//记录总数
    public function brandProductCount($sqlwhere = '1'){return  $count = $this->model_brand->where($sqlwhere)->count();}	
    public function brandProduct($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_visible=1 ','order'=>'`id` DESC')) {
		//品牌&商品
		$list = $this->model_brand->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			if($laValue["logo"]){
				$list[$lnKey]['logo'] = API_DOMAIN.$laValue["logo"];
			}else{
				$list[$lnKey]['logo'] = NO_PICTURE;
			}			
			$product_list=$this->field("`id`,`zn_cat_id` as cat_id,`zc_thumb` as image,`zc_title` as title,`zf_shop_price` as price")->order('zn_sort,id desc')->where("zl_visible=1 and zn_product_brand_id=".$laValue["id"])->limit(6)->select();
			foreach ($product_list as $k => $v) {
				if($v["image"]){
					$product_list[$k]['image'] = API_DOMAIN.$v["image"];
				}else{
					$product_list[$k]['image'] = NO_PICTURE;
				}				
				$product_list[$k]['short_title'] = lq_cutstr($v["title"],30,0,'UTF-8','...');
				$product_list[$k]['cat_id_label'] =$this->product_cat[$v["cat_id"]];
			}
			$list[$lnKey]['product_list'] = $product_list;
        }
		return $list;
    }
	//品牌&商品***************************end*******************************
	
	
	//列表页
    public function lqCount($sqlwhere = '1'){return  $count = $this->where($sqlwhere)->count();}	
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_visible=1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        $product_cat = $this->product_cat;
        foreach ($list as $lnKey => $laValue) {
			if($laValue["image"]){
				$list[$lnKey]['image'] = API_DOMAIN.$laValue["image"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE;
			}

			$list[$lnKey]['short_title'] = lq_cutstr($laValue["title"],30,0,'UTF-8','...');
			$list[$lnKey]['zn_cat_id_label'] =$product_cat[$laValue["cat_id"]];
            $list[$lnKey]['zn_id_label'] =$product_cat[$laValue["cat_id"]];
			$list[$lnKey]['thumb_width'] = intval($laValue["thumb_width"]);
			if($list[$lnKey]['thumb_width']==0) $list[$lnKey]['thumb_width']=357;
			$list[$lnKey]['thumb_height'] = intval($laValue["thumb_height"]);
			if($list[$lnKey]['thumb_height']==0) $list[$lnKey]['thumb_height']=357;
            $list[$lnKey]['time'] = date("Y-m-d H:i:s",$laValue["time"]);

			//$list[$lnKey]['love'] = rand(100,99999);
        }
        return $list;
    }

	
	//通过ID获取产品数据 $id 产品ID ,$mustCache后台控制必须缓存
    public function getProductById($id,$mustCache=0) {
		if($mustCache==0){
		$info=PAGE_S("page_product_".$id,'',$this->cache_options); //读取缓存数据
		if($info) return $info;
		}
		$data = $this->where(" zl_visible=1 and id=" .$id)->find();
		if(!$data)  return 0;

		$info=array();
		$info['id'] = $data["id"];
        $info['cat_id'] = $data['zn_cat_id'];
		$info['title'] = $data["zc_title"];
		$info['price'] = $data["zf_shop_price"];
		$info['love'] = $data["zn_agrees"];
		$info['short_title'] = lq_cutstr($data["zc_title"],30,0,'UTF-8','...');
		if($data["zc_image"]) {
			$info["image"] = API_DOMAIN.$data["zc_image"];
		}else{
			$info["image"] = NO_PICTURE;
		}
		$info['seo_title'] = $data["zc_seo_title"]=='' ? $data["zc_title"] : $data["zc_seo_title"];
		$info['seo_keywords'] = $data["zc_seo_keywords"];
		$info['seo_description'] = $data["zc_seo_description"];
		$info["content"]=lq_format_content($data["zc_content"]);
		$info['page_view'] = $data["zn_page_view"];
		$info['api_display'] = U("api/product/product-display?tnid=".$data["id"],'',true,true);
		$info['agrees'] = $data['zn_agrees'];
		$album=$data["zc_album"];
		//图册
		if($data["zc_album"]){
			$album=explode(",",$data["zc_album"]);
			foreach ($album as $k => $v) {
				$album[$k]=API_DOMAIN.$v;
			}
			$info["album"] = $album;		
		}else{
			$info["album"] = 0;
		}
		//分类
		$info['cat_label'] =$this->product_cat[$data["zn_cat_id"]];
		//同类下的产品
		$page_config = array(
				'field'=>"`id`,`zn_cat_id` as cat_id,`zc_thumb` as image,`zc_title` as title,`zf_shop_price` as price",
				'where'=>" zl_visible=1 and zl_is_good=1 and zn_cat_id=".$data["zn_cat_id"],
				'order'=>'zn_page_view desc,id desc',
		);		
		$info['cat_product'] =$this->lqList(0,8,$page_config);
		
		//品牌
		$info['brand'] = $this->model_brand->field("`id`,`zc_company_name` as company_name,`zc_caption` as caption,`zc_logo` as logo,`zc_banner` as banner")->where("id=".intval($data["zn_product_brand_id"]))->find();	
		if($info['brand']){
			if($info['brand']["logo"]){
				$info['brand']["logo"]=API_DOMAIN.$info['brand']["logo"];
			}else{
				$info['brand']["logo"]='0';
			}
			if($info['brand']["banner"]){
				$info['brand']["banner"]=API_DOMAIN.$info['brand']["banner"];
			}else{
				$info['brand']["banner"]='0';
			}
			
		}
		//上下页
		$data_prev=$this->field("`id`,`zc_title`")->where(" zl_visible=1 and zn_product_brand_id=".$data['zn_product_brand_id']." and id>".$id)->order('zn_sort,id desc')->limit("0,1")->find();
		$data_next=$this->field("`id`,`zc_title`")->where(" zl_visible=1 and zn_product_brand_id=".$data['zn_product_brand_id']." and id<".$id)->order('zn_sort,id desc')->limit("0,1")->find();	

			if($data_prev["id"]){
				$info['prev_id']=$data_prev["id"];
				$info['prev_title']=$data_prev["zc_title"];
			}else{
				$info['prev_id']=0;
				$info['prev_title']='没有了';
			}
			if($data_next["id"]){
				$info['next_id']=$data_next["id"];
				$info['next_title']=$data_next["zc_title"];
			}else{
				$info['next_id']=0;
				$info['next_title']='没有了';
			}
			PAGE_S("page_product_".$id,$info,$this->cache_options); //缓存数据
			return $info;
	}
	
	//访问统计
	public function setViewCount($id){
		$this->where('zl_visible=1 and id='.$id)->setInc('zn_page_view',1);
		$page_view = $this->where('zl_visible=1 and id='.$id)->getField('zn_page_view');
		return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $page_view );
	}

    //点赞数量统计
    public function setAgreeCount($id){
        $this->where('zl_visible=1 and id='.$id)->setInc('zn_agrees',1);
        return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] ,'data' => $this->where('zl_visible=1 and id='.$id)->getField('zn_agrees') );
    }

}

?>
