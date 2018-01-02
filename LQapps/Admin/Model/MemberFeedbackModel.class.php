<?php // MemberFeedback 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
class MemberFeedbackModel extends PublicModel {

	/* 用户模型自动验证 */
	protected $_validate = array(

	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_operator', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_type', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
	);	
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_description";//数据表显示标题字段
		$this->pc_index_list =  "MemberFeedback/index";//列表首页
	}
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
	    foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['ip'] = long2ip($laValue["zn_ip"]);
			$list[$lnKey]['description'] = lq_cutstr($laValue["zc_content"],60);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }


	
}

?>
