<?php //管理员 Admin 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
class AdminModel extends PublicModel {
	protected $autoCheckFields =false; //如果定义的模型没有对应的数据表,最好设置为虚拟模
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}



	//列表页
    public function list_Admin() {
		$this->setIndexUrl();//设置列表页的url
    }
	
	
}

?>
