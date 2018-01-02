<?php //银行信息表 PayLog 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class PayLogModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(

	);

	/* 用户模型自动完成 */
	protected $_auto = array(

	);	
	//保护字段（add或edit不能操作）
	protected $_protected_field=array();//,'zl_level'
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_order_no";//数据表显示标题字段
		$this->pc_index_list =  "PayLog/index";//列表首页
	}


	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$pay_business=C("PAY_BUSINESS");
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"],$laValue["zn_mdate"],'');
			$list[$lnKey]['zn_pay_business_label'] = $pay_business[$laValue['zn_pay_business']];
			$list[$lnKey]['zl_is_apy_label'] = $laValue['zl_is_apy'] == 1 ? '已支付' : '未支付';
			$list[$lnKey]['zc_msg'] = $laValue['zc_description'];
			$list[$lnKey]['photos_url'] = U("view?tnid=".$laValue["id"]);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	public function lqSubmit(){$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}

}

?>
