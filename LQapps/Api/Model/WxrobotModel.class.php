<?php //微信机器人 数据处理，数据回调
namespace Api\Model;
defined('in_lqweb') or exit('Access Invalid!');
class WxrobotModel extends PublicModel {
	protected $model_member;
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'wx_robot';	
    public function __construct() {
		parent::__construct();
	}

	//通过关键字查找回复
	public function getByKeyword($key=''){
		$data=$this->field("`zl_type` as type,`zc_label` as title,`zc_image` as image,`zc_url` as url,`zc_reply` as reply")->where("zl_visible=1 and zc_keyword='".$key."'")->find();
		if(!$data) return 0;
		$data["image"]=API_DOMAIN.$data["image"];
		return $data;		
	}

}

?>
