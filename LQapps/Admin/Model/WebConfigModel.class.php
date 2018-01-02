<?php //基本设置 WebConfig 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class WebConfigModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_label','1,50','控件标题在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_label', '', "控件标题已被占用", self::MUST_VALIDATE, 'unique'),
		array('zc_key','1,30','CONFIG_ARRAY_kEY在1~30个字符间',self::MUST_VALIDATE,'length'),
		array('zc_key', '', "CONFIG_ARRAY_kEY已被占用", self::MUST_VALIDATE, 'unique'),
		array('zn_sort','0,255','排序在0~255之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zc_key', 'strtoupper', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_label";//数据表显示标题字段
		$this->pc_index_list =  "WebConfig/index";//列表首页
	}
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['zc_type_label'] = $laValue['zc_type']=='' ? "归类" : $laValue['zc_type'] ;
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

	//缓存列表数据
    public function lqCacheData($return=0){
		$set_config=array();
		$integration_type=array();
		$condition='zl_visible=1 ';
		$list= $this->field('`id`,`zn_fid`,`zc_type`,`zc_label`')->where($condition." and zn_fid=0 ")->order('`zn_sort` ASC,`id` ASC')->select();
		foreach ($list as $lnKey => $laValue) {
			$child = $this->field('*')->where($condition." and zn_fid=".$laValue["id"])->order('`zn_sort` ASC,`id` ASC')->select();
			$list[$lnKey]["child"]= $child;
			foreach ($child as $k => $v) {
				if( $v["zc_key"] ) $set_config[$v["zc_key"]] = $v["zc_value"];
				if($laValue["id"]==44){
					$integration_type[intval(substr($v["zc_key"],17))]=array(
						'label'=>$v["zc_label"],
						'value'=>$v["zc_value"]
					);
				}
			}
		}
		F('set_config',$set_config,COMMON_ARRAY);
		F('web_config',$list,COMMON_ARRAY);
		if($return) return $list;
	}


}

?>
