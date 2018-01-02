<?php //美家风格 Article 数据处理，数据回调
namespace Api\Model;
defined('in_lqweb') or exit('Access Invalid!');

class HdAttributeModel extends PublicModel {
    // 模型名称 - 数据表名（不包含表前缀）
	protected $tableName        =   'hd_attribute';	
    public function __construct() {
		parent::__construct();
	}

	//获取美家风格缓存数据
    public function getAttributeCache() {
		$list=array();
		$list["style"]=F('hd_attribute_1','',COMMON_ARRAY);//风格
		$list["household"]=F('hd_attribute_2','',COMMON_ARRAY);//户型
		$list["area"]=F('hd_attribute_3','',COMMON_ARRAY);//面积
        return $list;
    }

	
}
?>
