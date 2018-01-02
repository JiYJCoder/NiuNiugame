<?php //角色管理 AdminRole 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AdminRoleModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,20','角色名称在1~20个字符间',self::MUST_VALIDATE,'length'),
		array('zc_caption', '', "角色名称已被占用", self::MUST_VALIDATE, 'unique'),
		array('zc_description','1,200','角色说明在1~200个字符间',self::MUST_VALIDATE,'length'),
		array('zn_sort','0,255','排序在0~255之间',self::MUST_VALIDATE,'between'),
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
		$this->pc_index_list =  "AdminRole/index";//列表首页
	}
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}


	//缓存数据
	public function lqCacheData($return=0){
		$array=array();
		$la_cache_data = $this->field("`id`,`zc_caption`,`zc_action_list`")->order('id asc')->where("zl_visible=1")->select();
		foreach ($la_cache_data as $lnKey => $laValue) {
			$array[$laValue["id"]]=array(
				"id"=>$laValue["id"],
				"title"=>$laValue["zc_caption"],
				"action_list"=>$laValue["zc_action_list"]
			);
		}
		F("admin_role",$array,COMMON_ARRAY);
		if($return) return $array;
	}
	//***********************数据表常用操作************end**************************************************
	
}
?>
