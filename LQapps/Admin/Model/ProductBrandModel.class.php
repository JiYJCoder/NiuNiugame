<?php //产品品牌 ProductBrand 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class ProductBrandModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,50','品牌名称在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_caption', '', "品牌名称已被占用",self::MUST_VALIDATE, 'unique'),
		array('zc_company_name','1,50','厂商名称在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_logo','require','LOGO图片必须填写！'),
		array('zc_logo_bgcolor','check_hex_code','LOGO底色为十六进制值，如:#ffffff',3,'callback'),
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_recommend', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_image', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "ProductBrand/index";//列表首页
	}
	
	/**
	 * 检测十六进制值是不是合法 ^[0-9-]{6,30}$
	 */
	protected function check_hex_code($logo_bgcolor){
		if(preg_match("/^#[0-9a-fA-F]{6}$/",$logo_bgcolor)) return true;
		return false;
	}	
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			if($laValue["zc_logo"]){
				$list[$lnKey]['image'] = $laValue["zc_logo"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE_ADMIN;
			}			
			$list[$lnKey]['zl_recommend_label'] = C('YESNO_STATUS')[$laValue["zl_recommend"]];
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
	//缓存数据
	public function lqCacheData($tnReturn=0){
		$la_admin_action = M("product_brand")->field("`id`,`zc_caption`")->order('zn_sort','id desc')->where("zl_visible=1")->select();
		$array=lq_return_array_one($la_admin_action,"id","zc_caption");
		F('product_brand',$array,COMMON_ARRAY);
		if($tnReturn) return $array;
	}
	
    // 更新成功后的回调方法
    protected function _after_update($data,$options) {
		if(ACTION_NAME=='opVisible'){
			if($data["zl_visible"]==0){
				$update=M()->execute( "update __PREFIX__product set zl_visible=0 where zn_product_brand_id=".$data["id"]);
			}
		}
	}	

    //更改-是非首页 
    public function setProperty() {
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		$data['zl_recommend'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>array("status" => $data['zl_recommend'], "txt" => $data['zl_recommend'] == 1 ? "是" : "否" )  );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	
		
}

?>
