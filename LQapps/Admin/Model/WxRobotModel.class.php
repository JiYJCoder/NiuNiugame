<?php //微信机器人 WxRobot 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class WxRobotModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zl_type',array(1,2),'消息类型不正确',self::MUST_VALIDATE,'in'),
		array('zc_label','1,100','消息说明在1~120个字符间',self::MUST_VALIDATE,'length'),
		array('zc_keyword', '', "消息关键字已被占用",self::MUST_VALIDATE, 'unique'),
		array('zc_keyword','1,30','消息关键字在1~30个字符间',self::MUST_VALIDATE,'length'),
		array('zc_reply','1,120','消息内容在1~120个字符间',self::MUST_VALIDATE,'length'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zc_url', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_reply', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_keyword', 'str_replace_keyword', self::MODEL_BOTH,'callback'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_version_name";//数据表显示标题字段
		$this->pc_index_list =  "WxRobot/index";//列表首页
	}
	
	//确保keyword|有效性
	protected function str_replace_keyword($value){
		if(!$value) return '';
		return str_replace("，",",",$value);
	}	
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_type_label'] = $laValue['zl_type']==1?'回复消息(text)':'回复图文(news)';
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
