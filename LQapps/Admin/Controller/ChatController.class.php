<?php //银行信息表 PayLog 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class ChatController extends PublicController{
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}
    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'open_time'=>urldecode(I('get.open_time','0','int')),
			'time_start'=>I('get.time_start',lq_cdate(0,0,(-604800))),
			'time_end'=>I('get.time_end',lq_cdate(0,0)),
			'status'=>I('get.status','99','int'),	
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_member_account ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_order_no like'%".$search_content_array["fkeyword"]."' ";
			}	
		}
		if($search_content_array["status"]!=99){
				$sqlwhere_parameter.=" and zl_is_apy = ".intval($search_content_array["status"]);
		}		
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
	   }
	
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_order_no'=>'订单编号','zn_cdate'=>'创建时间','zc_pay_type'=>'支付方式','zn_pay_business'=>'支付业务','zc_member_account'=>'支付会员','zf_order_amount'=>'支付金额','zc_transaction_id'=>'业务单据','status'=>'支付状态','os'=>"操作");
		$page_config = array(
				'field'=>"`id`,`zl_is_read`,`zc_to`,`zc_from`,`zc_chat_type`,`zc_bodies`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->myTable->alias("p")->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }


	//单记录删除 
    public function opDelete($is_tree=0) {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}	
	//多记录审批 
    public function opVisibleCheckbox() {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}		
	//更改字段值 
    public function opProperty() {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}


}
?>