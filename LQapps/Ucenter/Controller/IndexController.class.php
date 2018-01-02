<?php
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:会员后台首页
*/
namespace Ucenter\Controller;
use Think\Controller;
use Member\Api\MemberApi as MemberApi;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

//后台首页
class IndexController extends PublicController{
    public function __construct() {
		parent::__construct();
	}

	//首页
    public function index() {
        $this->display('index');
    }

	//装修贷数据汇总
	protected function getHdOrder($date='2017-01-01',&$list){
		$bank_id = M("bank")->where("zn_member_id=".$this->login_member_info["id"])->getField('id');	//银行ID
		$model_loan_apply = M("loan_apply");
		$start_time=strtotime($date." 00:00:00");
		$end_time=strtotime($date." 23:59:59");		
		$list["label"][] =$date;
		$list["datasets"]["success"][]=$model_loan_apply->where("zn_bank_id=$bank_id and zl_status=7 and zn_cdate >=".$start_time." and zn_cdate<=".$end_time)->count();
		$list["datasets"]["fail"][]=$model_loan_apply->where("zn_bank_id=$bank_id and zl_status=8 and zn_cdate >=".$start_time." and zn_cdate<=".$end_time)->count();
		$list["datasets"]["total"][]=$model_loan_apply->where("zn_bank_id=$bank_id and zn_cdate >=".$start_time." and zn_cdate<=".$end_time)->count();
		return $list;
	}
	
	//装修贷
    public function ajaxSearch(){
			$list=array();
			if($this->login_member_info["zl_role"]==1){//设计师
				$model_designer_works=M("designer_works");
				$success=$model_designer_works->where("zl_visible=1 and zn_member_id=".$this->login_member_info["id"])->count();
				$fail=$model_designer_works->where("zl_visible=0 and zn_member_id=".$this->login_member_info["id"])->count();	
				$list[]=array("value"=>intval($success),"color"=>"#95c000");
				$list[]=array("value"=>intval($fail),"color"=>"#666666");
			}elseif($this->login_member_info["zl_role"]==6){//银行
				for($index=30;$index>=0;$index--){
					$list=$this->getHdOrder(date('Y-m-d', strtotime("-$index days")),$list);//今天
				}
			}
			$this->ajaxReturn($list);	
    }		

}
?>