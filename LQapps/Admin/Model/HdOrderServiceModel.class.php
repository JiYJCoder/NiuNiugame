<?php //银行信息表 HdOrderService 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class HdOrderServiceModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(

	);

	/* 用户模型自动完成 */
	protected $_auto = array(

	);	
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_member_id','zc_member_account','zn_mdate');//,'zl_level'
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_order_no";//数据表显示标题字段
		$this->pc_index_list =  "HdOrderService/index";//列表首页
	}


	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
		foreach ($list as $lnKey => $laValue) {
			if($laValue["zl_status"]==3||$laValue["zl_status"]==7||$laValue["zl_status"]==8){
			$list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"],$laValue["zn_mdate"],'');
			}else{
			$list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"],NOW_TIME,'');
			}
			$list[$lnKey]['zl_status_label'] = $laValue['zl_status'] == 1 ? '已处理' : '未处理';
			$list[$lnKey]['zc_msg'] = $laValue['zc_description'];
			$list[$lnKey]['photos_url'] = U("view?tnid=".$laValue["id"]);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

	//多记录删除
    public function lqDeleteCheckbox() {
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }

    //更改-是非首页 
    public function setProperty() {
		$lcop=I("get.tcop",'is_index');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($lcop=='is_deal'){
			$data['zl_status'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_status'], "txt" => $data['zl_status'] == 1 ? "已处理" : "未处理" ) ;			
		}else{
			return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }

}

?>
