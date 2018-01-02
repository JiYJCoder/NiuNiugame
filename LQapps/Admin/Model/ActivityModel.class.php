<?php //活动管理 Activity 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class ActivityModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_game_mode','lqrequire','分类必须填写！',self::MUST_VALIDATE),
		array('zc_title','require','活动标题必须填写',self::MUST_VALIDATE),
		array('zc_title','1,100','活动标题在1~100个字符',self::MUST_VALIDATE,'length'),
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_game_mode', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_page_view', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_share', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_agrees', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zd_start_time', 'lqMktime', self::MODEL_BOTH,'function'),
		array('zd_end_time', 'lqMktime', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "Activity/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        $activity_game_mode=C('ACTIVITY_GAME_MODE');
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['title'] = $laValue["zc_title"];
			if($laValue["zc_image"]){
				$list[$lnKey]['image'] = $laValue["zc_image"];
			}else{
				$list[$lnKey]['image'] = NO_PICTURE_ADMIN;
			}
			$list[$lnKey]['zn_game_mode_label'] = $activity_game_mode[$laValue["zn_game_mode"]];
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}


}

?>
