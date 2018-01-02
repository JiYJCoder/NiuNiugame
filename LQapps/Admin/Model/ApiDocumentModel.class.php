<?php //接口文档说明 ApiDocument 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class ApiDocumentModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_title','1,50','接口标题在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_title', '', "接口标题已被占用",self::MUST_VALIDATE, 'unique'),
		array('zc_url','require','版本链接必须填写！'),
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_type', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_content', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "ApiDocument/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_type_label'] = C("API_DOCUMENT_TYPE")[$laValue['zl_type']];
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
