<?php // HdOrder 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
class HdOrderModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_name','1,10','申请人姓名在1~10个字符间',self::MUST_VALIDATE,'length'),
		array('zc_mobile','isMobile','请输入正确的手机号码',self::MUST_VALIDATE,'function'),
		array('zc_contact_address','1,100','申请人详细地址1~100个字符间',self::MUST_VALIDATE,'length'),
		array('status', 'lqCheckProgress', "选择的工程进度不正确，请选择最前面的一步", self::MUST_VALIDATE, 'callback'),
		array('status', 'lqCheckPerfected', "工程进度内容不能为空", self::MUST_VALIDATE, 'callback'),
		array('status', 'lqCheckModify', "订单已处理完成，不能修改了", self::MUST_VALIDATE, 'callback'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_acreage','zn_room','zn_hall','zn_kitchen','zn_toilet','zn_balcony','zf_labour_fee','zf_material_fee','zf_design_fee','zf_qc_fee','zf_total_fee','zc_decoration_type','zc_decoration_else');	
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_name";//数据表显示标题字段
		$this->pc_index_list =  "HdApplication/index";//列表首页
	}
	//工程进度内容不能为空
	protected function lqCheckPerfected(){
		$progress=I("post.progress",'0','int');
		$follow_contact=I("post.follow_contact",'');
		$follow_mobile=I("post.follow_mobile",'');
		$remarks=I("post.remarks",'');
		if($progress){
			if(!$follow_contact|!$follow_mobile|!$remarks) return false;
		}
		return true;
	}
	//跟进人与备注不能为空
	protected function lqCheckProgress(){
		$data=I("post.LQF",'');
		$progress=I("post.progress",'0','int');
		if($progress){
			$progress_now=intval($this->where("id=".(int)$data["id"])->getField("zl_progress"));
			if(($progress_now+1)!=$progress){
				return false;
			}
		}
		return true;
	}
	//订单已处理完成，不能修改了
	protected function lqCheckModify(){
		$data=I("post.LQF",'');
		$status=intval($this->where("id=".(int)$data["id"])->getField("zl_status"));
		if($status==1||$status==3||$status==4){
			return false;
		}
		return true;
	}	
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$model_designer=M("designer");
		$capn=C("CAPITAL_NUMBER");
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
	    foreach ($list as $lnKey => $laValue) {
			$member_account =!$laValue["zc_member_account"]?'未邦定':$laValue["zc_member_account"];
			$list[$lnKey]['zc_member_account'] =$member_account;
			$list[$lnKey]['zl_type_label'] = $laValue["zl_type"]==1?'一键报价':'申请设计';
			$nickname=$model_designer->where("id=".(int)$laValue["zn_designer_id"])->getField("zc_nickname");
			$list[$lnKey]['designer'] = !$nickname?'无':$nickname;
			$list[$lnKey]['zn_city_label']=M("region")->where("id=".$laValue["zn_city"])->getField("zc_name");
            $list[$lnKey]['zl_status_label']=C("HD_ORDER_STATUS")[$laValue["zl_status"]]." - ".C("PROJECT_PROGRESS")[$laValue["zl_progress"]];
			$list[$lnKey]['house_type'] = "面积:".$laValue["zn_acreage"]."㎡/".$capn[$laValue["zn_room"]]."房/".$capn[$laValue["zn_hall"]]."客/".$capn[$laValue["zn_kitchen"]]."厨/".$capn[$laValue["zn_toilet"]]."卫/".$capn[$laValue["zn_balcony"]]."阳台";			
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

    // 更新成功后的回调方法
    protected function _after_update($data,$options) {
		$sql=$log='';
		$log_arr=array();
		$progress=I("post.progress",'0','int');//工程进度
		$follow_contact=I("post.follow_contact",'');
		$follow_mobile=I("post.follow_mobile",'');
		$remarks=I("post.remarks",'');
		$payment=I("post.payment",'0','float');//款项
		if(C("PROJECT_PROGRESS")[$progress]){
		$update=M()->execute( "update __PREFIX__hd_progress set zl_status=1,zc_follow_contact='$follow_contact',zc_follow_mobile='$follow_mobile',
		zc_remarks='$remarks',zf_payment='$payment',zn_admin_id=".session('admin_auth')["id"].",zn_admin_account='".session('admin_auth')["zc_account"]."',zn_cdate=".NOW_TIME." where zl_order_progress=".$progress." and  zn_hd_order_id=".$data["id"]);
		
			if($update){//修改订单状态
				$order_update_sql='';
				if($progress==21){
					$order_update_sql=',zl_status=1';
				}else{
					$order_update_sql=',zl_status=2';
				}
				M()->execute( "update __PREFIX__hd_order set zl_progress='$progress'".$order_update_sql." where id=".$data["id"]);
			}
		}
	}
	
    public function setProperty() {
        $op = I("get.op",'');
		$lnid=I("get.tnid",'0','int');
		if($op=="recycle"){
			$status=intval($this->where("id=".$lnid)->getField("zl_status"));
			if($status==1||$status==3||$status==4) return array('status' => 0, 'msg' => "订单已完成，不能再操作了！");
				
			$data=array();
			$data["id"] = $lnid;
			$data['zl_status'] = 4;
			$data['zn_mdate'] =NOW_TIME ;
		}else{
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
		}
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => "订单取消成功",'data'=>'','url'=>U("HdOrder/index"));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }		
	}		
	
	
	
}

?>
