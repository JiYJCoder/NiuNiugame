<?php //家装属性类别 HdAttribute 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class HdAttributeModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_type','lqrequire','属性类别必须填写！',self::MUST_VALIDATE),
		array('zc_caption','1,100','类别名称在1~100个字符间',self::MUST_VALIDATE,'length'),
		array('zc_caption', '', "类别名称已被占用",self::MUST_VALIDATE, 'unique'),
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "HdAttribute/index";//列表首页
	}
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zn_type_label'] = C("HD_TYPE")[$laValue['zn_type']];
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
		$la_hd_attribute_1 =$this->where('zl_visible=1 and zn_type=1')->order('zn_sort,id desc')->field("`id`,`zc_caption`")->select();
		if($la_hd_attribute_1) F('hd_attribute_1',lq_return_array_one($la_hd_attribute_1,"id","zc_caption"),COMMON_ARRAY);
		$la_hd_attribute_2 =$this->where('zl_visible=1 and zn_type=2')->order('zn_sort,id desc')->field("`id`,`zc_caption`")->select();
		if($la_hd_attribute_2) F('hd_attribute_2',lq_return_array_one($la_hd_attribute_2,"id","zc_caption"),COMMON_ARRAY);		
		$la_hd_attribute_3 =$this->where('zl_visible=1 and zn_type=3')->order('zn_sort,id desc')->field("`id`,`zc_caption`")->select();
		if($la_hd_attribute_3) F('hd_attribute_3',lq_return_array_one($la_hd_attribute_3,"id","zc_caption"),COMMON_ARRAY);		
	}
	
}

?>
