<?php
/*
 * 后台公共Model
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 */
namespace Home\Model;
use LQPublic\Model\Base;

class PublicModel extends Base {
	public $set_config,$cache_options;
	protected $autoCheckFields =false; //如果定义的模型没有对应的数据表,最好设置为虚拟模型
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		$this->set_config=F('set_config','',COMMON_ARRAY);
		$this->cache_options=array('prefix'=>'page_','expire'=>$this->set_config["INT_SYSTEM_CACHE_TIME"]);
    }

}
?>