<?php //活动管理 Activity 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class ActivityController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'活动内容',3=>'活动数据'),
		//通用信息
		'1'=>array(
		array('select', 'zn_game_mode', "活动游戏类型",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('text', 'zc_title', "活动标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":120}'),
		array('text', 'zc_url', "活动链接",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_image', "封面图片",1,'{"type":"images","allowOpen":1}'),
		array('date', 'zd_start_time', "开始时间",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}'),
		array('date', 'zd_end_time', "结束时间",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),
		//内容
		'2'=>array(
		array('textarea', 'zc_summary', "活动摘要",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('editor', 'zc_content', "活动内容",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),		
		),
		//活动属性
		'3'=>array(
		array('text', 'zn_page_view', "访问次数统计",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_share', "分享次数统计",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_agrees', "点赞数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),		
	);
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
			'time_start'=>I('get.time_start',lq_cdate(0,0,(-2592000))),
			'time_end'=>I('get.time_end',lq_cdate(0,0)),			
			'mode'=>I('get.mode','','int'),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("zn_game_mode_str", lqCreatOption(C("ACTIVITY_GAME_MODE"),$search_content_array["cat_id"],"选择游戏类型"));//活动游戏类型		
		
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_title ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_title like'".$search_content_array["fkeyword"]."%') ";
			}	
		}
		if($search_content_array["open_time"]==1&&$search_content_array["time_start"]&&$search_content_array["time_end"]){
				$ts=strtotime($search_content_array["time_start"]." 00:00:00");
				$te=strtotime($search_content_array["time_end"]." 23:59:59");
				$sqlwhere_parameter.=" and zd_start_time >=".$ts." and zd_end_time<=".$te;	
	   }	
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_image'=>'图片','zn_cat_id'=>'活动类型','zc_title'=>'活动标题','time'=>'活动时间','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zn_game_mode`,`zc_image`,`zc_title`,`zn_sort`,`zd_start_time`,`zd_end_time`,`zl_visible`",
				'where'=>$sqlwhere_parameter,
				'order'=>'zn_sort,id DESC',
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
	
	// 插入/添加
    public function add() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='Public/common-edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zd_start_time"]=$form_array["zd_end_time"]=0;
			$form_array["zn_game_mode_data"]=C('ACTIVITY_GAME_MODE');
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_page_view"]=$form_array["zn_share"]=$form_array["zn_agrees"]=0;
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
            $this->display($lcdisplay);
        }
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
			$returnData=$this->C_D->lqSubmit();
			//D('Api/Activity')->getActivityById(intval($_POST["LQF"]["id"]),1);
            $this->ajaxReturn($returnData);
        } else {
			$lcdisplay='Public/common-edit';
			
			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_title");//上下页
							
			
			//表单数据初始化s
			$form_array=array();
			//操作时间
			$form_array["os_record_time"]=$this->osRecordTime($data);
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_game_mode_data"]=C('ACTIVITY_GAME_MODE');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

	//更改字段值 
    public function opProperty() {$this->ajaxReturn(array('status' => 0, 'msg' => "功能已关闭"));}

}
?>