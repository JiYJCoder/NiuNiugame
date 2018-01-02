<?php //产品管理 Product 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class ProductModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_cat_id','lqrequire','产品分类必须填写！',self::MUST_VALIDATE),
		array('zn_product_brand_id','lqrequire','产品品牌必须填写！',self::MUST_VALIDATE),
		array('zc_title','1,200','产品标题在1~200个字符',self::MUST_VALIDATE,'length'),
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
		array('zn_page_view','0,2147483647','访问量在0~2147483647之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_agrees', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_cat_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_product_brand_id', 'lqNumber', self::MODEL_BOTH,'function'),
		//array('zc_album', 'album_connect', self::MODEL_BOTH,'callback'),
		array('zc_image', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_thumb', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_album', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_summary', 'lqNull', self::MODEL_INSERT,'function'),
		array('zc_content', 'lqNull', self::MODEL_INSERT,'function'),
		array('zf_shop_price', 'floatval', self::MODEL_BOTH,'function'),
		array('zf_stock_price', 'floatval', self::MODEL_BOTH,'function'),
		array('zf_lowest_price', 'floatval', self::MODEL_BOTH,'function'),
		array('zf_speciality_price', 'floatval', self::MODEL_BOTH,'function'),
		array('zf_jd_price', 'floatval', self::MODEL_BOTH,'function'),
		array('zl_is_index', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_is_hot', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_is_good', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_seo_keywords', 'str_replace_keyword', self::MODEL_BOTH,'callback'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);	
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zc_thumb','zn_thumb_width','zn_thumb_height');		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "Product/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
		$product_cat=lq_return_array_one(F('product_cat','',COMMON_ARRAY),'id','zc_caption');
		$product_brand=F('product_brand','',COMMON_ARRAY);

        $model_product_cat = M("ProductCat");
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['title'] = $laValue["zc_title"];
			if($laValue["zc_thumb"]){
				$list[$lnKey]['image'] = $laValue["zc_thumb"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE_ADMIN;
			}
            $topCat_id = $model_product_cat->where("id=".$laValue['zn_cat_id'])->getField('zn_fid');

            $list[$lnKey]['zn_top_cat_label'] =$product_cat[$topCat_id];
			$list[$lnKey]['zn_cat_id_label'] =$product_cat[$laValue["zn_cat_id"]];
			$list[$lnKey]['zn_product_brand_id_label'] = $product_brand[$laValue["zn_product_brand_id"]];
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			$list[$lnKey]['zl_is_hot_label'] = $laValue['zl_is_hot'] == 1 ? '是热销' : '非热销';
			$list[$lnKey]['zl_is_good_label'] = $laValue['zl_is_good'] == 1 ? '是精品' : '非精品';
			$list[$lnKey]['url'] = "/do?g=home&m=news&a=show&tnid=".$laValue["id"];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	protected function _before_write(&$data){}	
	public function lqSubmit(){return $this->lqCommonSave();}
	
    // 插入数据前的回调方法
    protected function _after_insert($data,$options) {	
	$this->_lqthumb($data,$options);
	}
	
    // 更新成功后的回调方法
    protected function _after_update($data,$options) {
	$this->_lqthumb($data,$options);
	}
	
	//缩略图处理
	protected function _lqthumb($data,$options){
		$image_data=array();
		if($data["zc_image"]) $image_data[]=array("key"=>'products',"path"=>$data["zc_image"]);
//		if($data["zc_album"]){
//			$temp_arr=lq_thumb_format($data["zc_album"],'album');
//			$image_data= array_merge($image_data,$temp_arr);				
//		}
		
		$thumb_list=lq_thumb_deal($image_data,$data["id"],'products');
		$thumb_path=$thumb_album=$updatesql='';
		if($thumb_list){
			foreach($thumb_list as $k => $v){
				if($k==0){
					$thumb_path=$v;
				}else if($k==1){
					$thumb_album=$v;
				}else{
					$thumb_album.=",".$v;
				}
			}	
		}
		$updatesql='';
		if($thumb_path!=''){
			$thumb_image = new \Think\Image();
			$thumb_width=$thumb_image->open(WEB_ROOT.$thumb_path)->width();
			$thumb_height=$thumb_image->open(WEB_ROOT.$thumb_path)->height();
			$updatesql=" zc_thumb='".$thumb_path."',zn_thumb_width='".$thumb_width."',zn_thumb_height='".$thumb_height."' ";
		}
		if($updatesql!=''){
		M()->execute("UPDATE __PREFIX__product SET $updatesql WHERE id=".$data["id"]);
		}		
	}	
	
	//确保keyword|有效性
	protected function str_replace_keyword($value){return str_replace("，",",",$value);}	
	//图册处理
	protected function album_connect($value){
		if(!$value) return '';
		$data = $this->getSafeData('LQF',"p");
		if($data["zc_image"]){
			return $data["zc_image"].",".$value;
		}else{
			return $value;
		}
	}

    //更改-是非首页 
    public function setProperty() {
		$lcop=I("get.tcop",'is_index');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($lcop=='is_index'){
			$data['zl_is_index'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_is_index'], "txt" => $data['zl_is_index'] == 1 ? "是首页" : "非首页" ) ;			
		}elseif($lcop=='is_hot'){
			$data['zl_is_hot'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_is_hot'], "txt" => $data['zl_is_hot'] == 1 ? "是热销" : "非热销" ) ;
		}elseif($lcop=='is_good'){
			$data['zl_is_good'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_is_good'], "txt" => $data['zl_is_good'] == 1 ? "是精品" : "非精品" ) ;						
		}else{
			return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

}

?>
