<?php // Loan 页面操作 
namespace Ucenter\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class LoanController extends PublicController{
	public $myTable,$bank_id;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'订单信息',2=>'订单操作',3=>'订单进度'),
		//通用信息
		'1'=>array(
		array('textShow', 'zc_order_no', "订单的系统编码",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'status_label', "当前状态",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_name', "申请人姓名",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_mobile', "申请人手机号码",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_area', "装修贷申请人所在地",1,'{"is_data":"0","creat_hidden":"0"}'),
		),
		//订单操作
		'2'=>array(
		array('select','zl_progress',"操作进度",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('select','zl_status',"进度状态",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('textarea', 'zc_remarks', "操作备注",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":65000,"rows":4}'),
		),		
		//操作日志
		'3'=>array(
		array('timeline', 'timeline', "操作日志",1,'{}'),
		),		
		
	);	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->isBank();//银行认证
		$this->myTable = M("loan_apply");//主表实例化
		$this->C_D=D("Loan");//实例D方法
		//银行ID
		$this->bank_id = M("bank")->where("zn_member_id=".$this->login_member_info["id"])->getField('id');	
	}

    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["lqs"]=$this->getSafeData('lqs');
		$this->reSearchPara($page_parameter["lqs"]);//反回搜索数
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
		$sqlwhere_parameter=" 1 and zn_bank_id = ".$this->bank_id;
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_mobile ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_name like'".$search_content_array["fkeyword"]."%' or zc_mobile like'".$search_content_array["fkeyword"]."%') ";
			}	
		}
		if($search_content_array["status"]!=''){
			if($search_content_array["status"]==999){
				$sqlwhere_parameter.=" and zl_status in(6,7,8) ";
			}else{
				$sqlwhere_parameter.=" and zl_status = ".intval($search_content_array["status"]);
			}
		}
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
	   }
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_order_no'=>'编号','time_diff'=>'申请时间','zc_name'=>'申请人','zc_mobile'=>'手机','zl_status'=>'状态','os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_order_no`,`zn_bank_id`,`zc_bank_name`,`zn_member_id`,`zc_member_account`,`zc_name`,`zc_mobile`,`zl_status`,`zn_mdate`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>"id DESC",
				'title'=>$page_title,
				'thinkphpurl'=>"/do?g=ucenter&m=loan",
		);		
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->myTable->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }

	// 更新/编辑
    public function edit() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit($this->bank_id));
        } else {
			$lcdisplay='edit';//引用模板
			
			//读取数据
			$data = $this->myTable->where("id=".$this->lqgetid)->find();
			if(!$data) {$this->error(C("ALERT_ARRAY")["recordNull"]);}//无记录
			$this->pagePrevNext($this->myTable,"id","zc_order_no");//上下页
			if($data["zl_status"]==3||$data["zl_status"]==7||$data["zl_status"]==8){
				unset($this->myForm["tab_title"][2]);
				unset($this->myForm[2]);
				$this->assign("close_submit",1);				
			}else{
				$this->assign("close_submit",0);				
			}
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["status_label"]=C("LOAN_STATUS")[$data["zl_status"]];
			
			
			$loan_progress=C("LOAN_PROGRESS");
			$loan_status=C("LOAN_STATUS");
			$loan_status_ =array();
			$last_progress = M("loan_apply_progress")->field("zl_progress")->where("zl_status>0 and zn_loan_apply_id=".$data["id"])->order("zl_status DESC")->find();
			if($last_progress["zl_progress"]==1){
				unset($loan_progress[1]);
				unset($loan_progress[3]);
			}elseif($last_progress["zl_progress"]==2){
				unset($loan_progress[1]);
				unset($loan_progress[2]);
			}
			$form_array["zl_progress_data"]=$loan_progress;
			$form_array["zl_status_data"]=C("LOAN_PROGRESS_STEP")[$last_progress["zl_progress"]+1];
			$form_array["zc_remarks"]='';
			
			//时间轴数据构建
			$order_progress=M("loan_apply_progress")->field("zl_role,`zl_progress` as progress,`zl_status` as status,zc_remarks,zn_cdate,zc_operate_account")->where("zn_loan_apply_id=".(int)$data["id"])->order("zl_progress ASC")->select();
			$timeline_data=array();
			foreach ($order_progress as $k => $v) {
				if($v["status"]>0){
					$progress_status=1;
				}else{
					$progress_status=0;
				}
				if($v["status"]==0){
					$role = '';
					$status='';
				}else{
					$status=" - ".C("LOAN_STATUS")[$v["status"]];
					if($v["zl_role"]==1){
						$role = '业主';
					}else if($v["zl_role"]==6){
						$role = '银行';
					}
				}
				$timeline_data[]=array(
					'no'=>($k+1),
					'status'=>$progress_status,
					'time'=>$v["zn_cdate"]==0?'':lq_cdate($v["zn_cdate"],1)." ,  操作人：".$v["zc_operate_account"]." ".$role ,
					'title'=>C("LOAN_PROGRESS")[$v["progress"]].$status,
					'msg'=>$v["status"]==0?'':'备注：'.$v["zc_remarks"],
				);
			}
			$form_array["timeline_data"]=$timeline_data;
			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
            $this->display($lcdisplay);
        }
    }

	//更改  zlvisible 
    public function opVisible() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("Ucenter/Index/index"));
    }	
	//更改字段值 
    public function opProperty() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("Ucenter/Index/index"));
    }
	//更改 Label 
    public function op_label() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("Ucenter/Index/index"));
    }
	//单记录删除 
    public function op_delete($is_tree=0) {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("Ucenter/Index/index"));
    }	
	//多记录删除
    public function op_delete_checkbox() {
        $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->tellAdmin, U("Ucenter/Index/index"));
    }	
	//上下分页 
    protected function pagePrevNext($M_PAGE,$id,$title,$sql='') {
			$data_prev=$M_PAGE->field("`$id`,`$title`")->where($sql."$id>" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_next=$M_PAGE->field("`$id`,`$title`")->where($sql."$id<" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_up_down_page='';
			if($data_prev){
				$data_up_down_page.='<li><a href="'.U("Ucenter/Loan/edit?tnid=".$data_prev[0]["$id"]).'" title="上一条：'.lq_kill_html($data_prev[0]["$title"],20).'"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}
			if($data_next){
				$data_up_down_page.='<li><a href="'.U("Ucenter/Loan/edit?tnid=".$data_next[0]["$id"]).'" title="下一条：'.lq_kill_html($data_next[0]["$title"],20).'"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}
			$this->assign("data_up_down_page",$data_up_down_page);
    }	
	
	
}
?>