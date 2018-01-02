<?php // HdOrder 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class HdOrderController extends PublicController{
	public $myTable,$MemberModel;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'装修信息',2=>'客户联系方式',3=>'跟进人员联系方式',4=>'订单信息',5=>'工程进度状态',6=>'工程进度流程'),
		//装修信息
		'1'=>array(
		array('textShow', 'zc_address', "客户工程地址",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zn_designer_id', "签约设计师",1,'{"is_data":"1","creat_hidden":"0"}'),
		array('textShow', 'zn_acreage', "报价面积",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'house_type', "装修房屋户型",0,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_decoration_type', "装修内容-装修类型",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_decoration_else', "装修内容-其它内容",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('image', 'zc_effect_image', "设计效果图",1,'{"type":"works","allowOpen":1}'),
		),
		//客户联系方式
		'2'=>array(
		array('textShow', 'zc_area', "客户地区",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('text', 'zc_contact_address', "客户联系地址",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":100}'),
		array('text', 'zc_mobile', "客户手机号码",1,'{"required":"1","dataType":"mobile","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),
		array('text', 'zc_name', "客户姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		),
		//跟进人员联系方式
		'3'=>array(
		array('textShow', 'zn_designer_tel', "设计师",0,'{"is_data":"0","creat_hidden":"0"}'),
		array('text', 'zc_dc_name', "装修顾问姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zc_dc_mobile', "装修顾问电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),
		array('text', 'zc_pm_name', "项目经理姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zc_pm_mobile', "项目经理电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),		
		array('text', 'zc_cs_name', "监理姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zc_cs_mobile', "监理电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),		
		array('text', 'zc_qc_name', "客服姓名",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zc_qc_mobile', "客服电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":11}'),		
		),		
		//订单信息
		'4'=>array(
		array('textShow', 'zc_order_no', "订单的系统编码",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'status_label', "当前状态",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_member_account', "会员帐号",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zf_labour_fee', "人工费",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zf_material_fee', "材料费",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zf_design_fee', "设计费",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zf_qc_fee', "质检费",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zf_total_fee', "订单总价",1,'{"is_data":"0","creat_hidden":"0"}'),
		),
		//订单操作
		'5'=>array(
		array('progress_bar', 'progress_bar', "当前进度",1,'{}'),
		array('select','progress',"工程进度",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('text', 'follow_contact', "跟进人姓名",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":60}'),
		array('text', 'follow_mobile', "跟进人联系电话",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":60}'),
		array('text', 'remarks', "进度备注",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":60}'),
		),		
		//工程进度流程
		'6'=>array(
		array('timeline', 'timeline', "工程进度流程",1,'{}'),
		),		
		
	);	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
		$this->MemberModel = new MemberApi;
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
			'progress'=>I('get.progress','','int'),
			'status'=>I('get.status',''),			
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		$this->assign("zl_progress_str",lqCreatOption(C("PROJECT_PROGRESS"),$search_content_array["progress"],"请选择进度"));	
		$this->assign("zl_status_str",lqCreatOption(C("HD_ORDER_STATUS"),$search_content_array["status"],"请选择状态"));	
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_name ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_order_no like'".$search_content_array["fkeyword"]."%' or zc_mobile like'".$search_content_array["fkeyword"]."%') ";
			}	
		}
		if($search_content_array["progress"])	$sqlwhere_parameter.=" and zl_progress = ".$search_content_array["progress"];
		if($search_content_array["status"]!='')	$sqlwhere_parameter.=" and zl_status = ".intval($search_content_array["status"]);
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zn_cdate >=".$ts." and zn_cdate<=".$te;	
	   }		
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_order_no'=>'编号','house_type'=>'类别','zf_total_fee'=>'总价','zc_name'=>'客户','zc_mobile'=>'客户电话','designer'=>'设计师','zl_status'=>'状态','os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_order_no`,`zn_designer_id`,`zc_member_account`,`zc_name`,`zf_total_fee`,`zn_acreage`,`zn_room`,`zn_city`,`zn_hall`,`zn_kitchen`,`zn_toilet`,`zn_balcony`,`zc_mobile`,`zl_status`,`zl_progress`,`zn_mdate`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>"id DESC",
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
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
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';//引用模板

			//读取数据
			$data = $this->myTable->where("id=".$this->lqgetid)->find();
			if(!$data) {$this->error(C("ALERT_ARRAY")["recordNull"]);}//无记录
			$this->pagePrevNext($this->myTable,"id","zc_order_no");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$designer=array();
			$designer_array=M("designer")->field("zn_member_id,zc_nickname")->where("id=".$data["zn_designer_id"])->find();
			if($designer_array){
				$designer[$data["zn_designer_id"]]=$designer_array["zc_nickname"];
				$form_array["zn_designer_id_data"]=$designer;
				$form_array["zn_designer_tel"]=$designer[$data["zn_designer_id"]]."，".$this->MemberModel->apiGetFieldByID($designer_array["zn_member_id"],'zc_mobile');				
			}
			$capn=C("CAPITAL_NUMBER");
			$form_array['house_type'] = $capn[$data["zn_room"]]."房/".$capn[$data["zn_hall"]]."客/".$capn[$data["zn_kitchen"]]."厨/".$capn[$data["zn_toilet"]]."卫/".$capn[$data["zn_balcony"]]."阳台";			
			$form_array["status_label"]=C("HD_ORDER_STATUS")[$data["zl_status"]];
			
			//进度操作
			$form_array["progress_bar"]=$data["zl_progress"];
			$progress_bar_data=C("PROJECT_PROGRESS");
			
			$select_array=array();
			foreach ($progress_bar_data as $k => $v) {
				if($k>$data["zl_progress"]){
				$select_array[$k]=$v;
				}
			}
			$form_array["progress_bar_data"]=$progress_bar_data;
			$form_array["progress_data"]=$select_array;
			
			//测试数据
//			$form_array["follow_contact"]='张好人';
//			$form_array["follow_mobile"]='13425647971';
//			$form_array["remarks"]='备注';
			
			$this->assign("close_submit",0);
			$project_progress=C("PROJECT_PROGRESS");
			if($data["zl_progress"]==count(C("PROJECT_PROGRESS"))){
				$this->assign("close_submit",1);				
				unset($this->myForm["tab_title"][5]);
				unset($this->myForm[5]);
			}else if($data["zl_progress"]==5||$data["zl_progress"]==13||$data["zl_progress"]==18||$data["zl_progress"]==20){
				$this->myForm[5][]=array('text', 'payment', "款项",0,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":60}');
			}else if($data["zl_progress"]==6){
				
			}else{
								
			}
			
			//时间轴数据构建
			$order_progress=M("hd_progress")->field("`zl_order_progress` as progress,`zl_status` as status,zf_payment,zc_follow_contact,zc_follow_mobile,zc_remarks,zn_cdate,zn_admin_account")->where("zn_hd_order_id=".(int)$data["id"])->order("zl_order_progress ASC")->select();
			$timeline_data=array();
			foreach ($order_progress as $k => $v) {
				$payment='';
				if($v["progress"]==6||$v["progress"]==14||$v["progress"]==19||$v["progress"]==21){
					$payment="<br>已收工程款：".$v["zf_payment"];
				}
				$timeline_data[]=array(
					'no'=>($k+1),
					'status'=>$v["status"],
					'time'=>$v["zn_cdate"]==0?'':lq_cdate($v["zn_cdate"],1)." ,  操作人：".$v["zn_admin_account"],
					'title'=>C("PROJECT_PROGRESS")[$v["progress"]],
					'msg'=>$v["status"]==0?'':'跟进人：'.$v["zc_follow_contact"].'，联系电话：'.$v["zc_follow_mobile"].'，说明：'.$v["zc_remarks"].$payment,
				);
			}
			$form_array["timeline_data"]=$timeline_data;
			
			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

    public function opVisible() {$this->ajaxReturn(array('status' => 0, 'msg' =>"接口停用"));}	
    public function opDelete() {$this->ajaxReturn(array('status' => 0, 'msg' =>"接口停用"));}	
    public function opDeleteCheckbox() {$this->ajaxReturn(array('status' => 0, 'msg' =>"接口停用"));}	
    public function opVisibleCheckbox() {$this->ajaxReturn(array('status' => 0, 'msg' =>"接口停用"));}	
	
	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }	
	
}
?>