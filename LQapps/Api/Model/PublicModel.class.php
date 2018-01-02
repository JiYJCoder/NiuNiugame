<?php
/*
 * 后台公共Model
 * Author：国雾院theone（438675036@qq.com）
 * Date:2013-06-27
 */
namespace Api\Model;
use LQPublic\Model\Base;
defined('in_lqweb') or exit('Access Invalid!');

class PublicModel extends Base {
	public $set_config,$cache_options;
	protected $autoCheckFields =false; //如果定义的模型没有对应的数据表,最好设置为虚拟模	
    public function __construct() {
		parent::__construct();
		$this->set_config=F('set_config','',COMMON_ARRAY);
		$this->cache_options=array('prefix'=>'page_','expire'=>$this->set_config["INT_SYSTEM_CACHE_TIME"]);
    }
	

}
?>