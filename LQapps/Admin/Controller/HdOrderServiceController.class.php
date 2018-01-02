<?php //银行信息表 HdOrderService 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class HdOrderServiceController extends PublicController{
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
			'status'=>I('get.status',''),	
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_member_account ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_order_no like'".$search_content_array["fkeyword"]."%' ";
			}	
		}
		
		if($search_content_array["status"]!=''){
				$sqlwhere_parameter.=" and zl_status = ".intval($search_content_array["status"]);
		}		
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
	   }		
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_order_no'=>'订单编号','zn_cdate'=>'创建时间','zc_member_account'=>'反馈会员','zc_contact_mobile'=>'会员电话','status'=>'状态','os'=>"查看");
		$page_config = array(
				'field'=>"`id`,`zn_hd_order_id`,`zc_order_no`,`zc_member_account`,`zc_contact_mobile`,`zc_description`,`zc_album`,`zl_status`,`zn_cdate`,`zn_mdate`",
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

	//查看
    public function view() {
			$album_list=array();		
			//读取数据
			$data = $this->myTable->where("id=".$this->lqgetid)->find();
			$album_data=array("status"=>0,"msg"=>"返回成功","title"=>$data["zc_description"],"id"=>$data["id"],"start"=>0,"data"=>array());
			if($data["zc_album"]){
					$album_array = explode(',',$data["zc_album"]); 
					if($album_array){
						for($index=0;$index<count($album_array);$index++) { 
							$album_list[]=array("alt"=>'',"pid"=>'',"src"=>$album_array[$index],"thumb"=>'');
						} 			
					}
					$album_data["status"]=1;
					$album_data["data"]=$album_list;
			}
			
            $this->ajaxReturn($album_data);

	}

	
	// 插入/添加
    public function add() {$this->error(C("ALERT_ARRAY")["recordNull"]);}
	// 更新/编辑
    public function edit() {$this->error(C("ALERT_ARRAY")["recordNull"]);}
	//单记录删除 
    public function opDelete($is_tree=0) {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}	
	//多记录审批 
    public function opVisibleCheckbox() {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}		
	
	

	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }
	


}
?>