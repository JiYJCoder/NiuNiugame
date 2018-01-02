<?php // Loan 数据处理，数据回调
namespace Ucenter\Model;
use Think\Model;
class LoanModel extends PublicModel {
	protected $tableName        =   'loan_apply';	
    protected $model_loan_progress;
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_name";//数据表显示标题字段
		$this->pc_index_list =  "Loan/index";//列表首页
        $this->model_loan_progress = M("loan_apply_progress");//装修贷申请进度 - 模型
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
	public function lqSubmit($bank_id){
		$data=I("post.LQF",'');
		$loan_apply_id=$data["id"];
   		$rules_progress = array(
			array('zl_progress',array(1,2,3),'操作进度必填', self::MUST_VALIDATE,'in'),//进度
			array('zl_status','0,9','进度状态必填',self::MUST_VALIDATE,'between'),
			array('zc_remarks', '1,100', '操作备注在1~100个字符之间', self::MUST_VALIDATE, 'length'),
    	);
		$test_status=intval($this->where("zn_bank_id=".$bank_id." and id=".(int)$data["id"])->getField("zl_status"));
		if($test_status==3||$test_status==7||$test_status==8||$test_status==0){
		return array('status' => 0, 'msg' =>C('ALERT_ARRAY')["error"] , 'url' =>U('Ucenter/Loan/index'));
		}
        $data = $this->model_loan_progress->validate($rules_progress)->create($data);
        if (!$data) {
            $msg=$this->model_loan_progress->getError();
			return array('status' => 0, 'msg' =>$this->model_loan_progress->getError() , 'url' =>U('Ucenter/Loan/edit?tnid='.$loan_apply_id));			
        }
		
		$data_progress=array(
			'zl_role'=>6,
			'zn_operate_id'=>session('member_auth')["id"],
			'zc_operate_account'=>session('member_auth')["zc_account"],
			'zl_status'=>$data["zl_status"],
			'zc_remarks'=>$data["zc_remarks"],
			'zn_cdate'=>NOW_TIME,
			'zn_mdate'=>NOW_TIME,
		);
	
		if($data["zl_status"]==2&&$data["zl_progress"]==2){//银行已受理
       		 $data_progress["zl_status"] = 4;
			 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=2")->save($data_progress);
			 $data_progress["zl_status"] = 2;
			 unset($data_progress["zl_role"]);
			 unset($data_progress["zn_operate_id"]);
			 unset($data_progress["zc_operate_account"]);			 
			 unset($data_progress["zn_cdate"]);
			 unset($data_progress["zc_remarks"]);
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=1")->save($data_progress);
			 //更新主表
       		 $this->where("id=".$data["id"])->save(array('zl_status'=>4,'zn_mdate'=>NOW_TIME));		
		}elseif($data["zl_status"]==3&&$data["zl_progress"]==2){//审批拒绝
			 $data_progress["zl_status"] = 3;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=2")->save($data_progress);		
			 $data_progress["zl_status"] = 7;
			 $data_progress["zc_remarks"] = C("LOAN_STATUS")[10];
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=3")->save($data_progress);		
			 unset($data_progress["zl_role"]);
			 unset($data_progress["zn_operate_id"]);
			 unset($data_progress["zc_operate_account"]);
			 unset($data_progress["zn_cdate"]);
			 unset($data_progress["zc_remarks"]);
			 $data_progress["zl_status"] = 2;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=1")->save($data_progress);	
			 //更新主表
       		 $this->where("id=".$data["id"])->save(array('zl_status'=>3,'zn_mdate'=>NOW_TIME));				 			 
		}elseif($data["zl_status"]==5&&$data["zl_progress"]==3){//审批通过
			 $data_progress["zl_status"] = 8;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=3")->save($data_progress);	
			 unset($data_progress["zn_cdate"]);
			 unset($data_progress["zc_remarks"]);
			 $data_progress["zl_status"] = 5;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=2")->save($data_progress);	
			 //更新主表
       		 $this->where("id=".$data["id"])->save(array('zl_status'=>8,'zn_mdate'=>NOW_TIME));				
		}elseif($data["zl_status"]==6&&$data["zl_progress"]==3){//审批未通过
			 $data_progress["zl_status"] = 7;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=3")->save($data_progress);	
			 unset($data_progress["zn_cdate"]);
			 unset($data_progress["zc_remarks"]);
			 $data_progress["zl_status"] = 6;
       		 $this->model_loan_progress->where("zn_loan_apply_id=".$data["id"]." and zl_progress=2")->save($data_progress);	
			 //更新主表
       		 $this->where("id=".$data["id"])->save(array('zl_status'=>7,'zn_mdate'=>NOW_TIME));			
		}
		return array('status' => 1, 'msg' =>C('ALERT_ARRAY')["saveOk"] , 'url' =>U('Ucenter/Loan/edit?tnid='.$loan_apply_id));
	
	}


	
}

?>
