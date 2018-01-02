<?php //管理员登陆 Login 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class LoginModel extends Model {
	protected $autoCheckFields =false; //如果定义的模型没有对应的数据表,最好设置为虚拟模型
    /** 初始化*/
    public function __construct() {
		parent::__construct();
	}		
	
	

}

?>
