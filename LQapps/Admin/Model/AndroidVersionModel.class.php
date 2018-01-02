<?php //安卓历史版本 AndroidVersion 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AndroidVersionModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_version_code','1,50','版本号在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_version_code', '', "版本号已被占用",self::MUST_VALIDATE, 'unique'),
		array('zc_version_name','1,50','版本名称在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_download_url','require','版本链接必须填写！'),
		array('zn_size','10,999999999','版本大小在10~999999999之间',self::MUST_VALIDATE,'between'),
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
		$this->pc_os_label =  "zc_version_name";//数据表显示标题字段
		$this->pc_index_list =  "AndroidVersion/index";//列表首页
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
	

}

?>
