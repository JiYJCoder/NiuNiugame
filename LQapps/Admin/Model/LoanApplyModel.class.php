<?php // LoanApply 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
class LoanApplyModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_name','1,20','申请人姓名在1~20个字符间',self::MUST_VALIDATE,'length'),
		array('zc_mobile','isMobile','请输入正确的手机号码',self::MUST_VALIDATE,'function'),
		array('zc_area','1,50','装修贷申请人所在地在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('status', 'lqCheckModify', "订单已处理完成，不能修改了", self::MUST_VALIDATE, 'callback'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_bank_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_name";//数据表显示标题字段
		$this->pc_index_list =  "LoanApply/index";//列表首页
	}
	//订单已处理完成，不能修改了
	protected function lqCheckModify(){
		$data=I("post.LQF",'');
		$status=intval($this->where("id=".(int)$data["id"])->getField("zl_status"));
		if($status==3||$status==7||$status==8){
			return false;
		}
		return true;
	}	
		
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
	    foreach ($list as $lnKey => $laValue) {
			$member_account =!$laValue["zc_member_account"]?'未邦定':$laValue["zc_member_account"];
			$list[$lnKey]['zc_member_account'] =$member_account;
			if($laValue["zl_status"]==5||$laValue["zl_status"]==6){
			$list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"],$laValue["zn_mdate"],'');
			}else{
			$list[$lnKey]['time_diff'] = lq_time_diff($laValue["zn_cdate"],NOW_TIME,'');
			}
			$list[$lnKey]['zl_status_label'] = C("LOAN_STATUS")[$laValue["zl_status"]];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}


	//单记录删除
    public function lqDelete($isTree=0) {
		$data["id"] = I("get.tnid",'0','int');
		if ($this->where($data)->delete()) {
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list."?clearcache=1"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	
	//多记录删除
    public function lqDeleteCheckbox() {
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		if ($this->where($data)->delete()) {
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }

    // 更新成功后的回调方法
    protected function _after_update($data,$options){}	
	
}

?>
