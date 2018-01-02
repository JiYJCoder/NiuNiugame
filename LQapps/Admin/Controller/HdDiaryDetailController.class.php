<?php //日记进度 HdDiaryDetail 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class HdDiaryDetailController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('hidden', 'zn_hd_diary_id', "日记ID",1,''),
		array('textShow', 'diary_nickname', "绑定的装修日记",1,'{"is_data":"0","creat_hidden":"1"}'),
		array('text', 'zc_title', "标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":100}'),
		array('textarea', 'zc_content', "内容",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":65000,"rows":6}'),
		array('select', 'zl_order_progress', "进度",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('multiImage', 'zc_album', "图册",1,'{"required":"1","type":"images","imageUploadLimit":10,"allowOpen":1,"returnData":"paths"}'),
		array('date', 'zd_send_time', "时间",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"Y-m-d H:i"}'),
		)
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
		$this->diary=I("get.diary",'0','int');
		$this->assign("diary",$this->diary);		
	}
    
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){$this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg,U("HdDiaryDetail/index"));}		
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'diary'=>urldecode(I('get.diary','0','int')),
		);
		$diary_data = M("hd_diary")->where("id=" .$search_content_array["diary"])->find();
		if(!$diary_data) {$this->error(C("ALERT_ARRAY")["recordNull"],U("HdDiary/index"));}//无记录
		$this->assign("diary_data", $diary_data);
		
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("add",U("HdDiaryDetail/add/diary/".$search_content_array["diary"]));//新增
		$this->assign("refresh",U("HdDiaryDetail/index/s/".base64_encode("diary/".$search_content_array["diary"]."/")."/"));//刷新
				
		//sql合并
		$sqlwhere_parameter=" 1 ";
		if($search_content_array["diary"])	$sqlwhere_parameter.=" and zn_hd_diary_id = ".$search_content_array["diary"];
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zd_send_time'=>'发布日期','zl_order_progress'=>'进度','zc_title'=>'标题','zl_visible'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_title`,`zd_send_time`,`zl_order_progress`,`zl_visible`,`zn_mdate`,`zn_cdate`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id ASC',
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);			
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->myTable->alias("p")->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count, C("PAGESIZE"), $page_parameter);//载入分页类
        $showPage = $page->admin_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows,$page_config,$search_content_array));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }
	
	// 插入/添加
    public function add() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';//引用模板
			
			if(!$this->diary) $this->error('请选择装修日记。谢谢！'.$this->systemMsg,U("HdDiary/index"));
			$diary_data = M("hd_diary")->where("id=" .$this->diary)->find();
			
			//表单数据初始化s
			$form_array=array();
			$form_array["id"]='';
			$form_array["diary_nickname"]=$diary_data["zc_nickname"];
			$form_array["zn_hd_diary_id"]=$diary_data["id"];
			$form_array["zl_order_progress_data"]=C("DIARY_STEP");
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化e

			$this->assign("edit_index_url",U("HdDiaryDetail/index/s/".base64_encode("diary/".$this->diary."/")."/"));//返回首页
            $this->display($lcdisplay);
        }
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());			
        } else {
			$lcdisplay='edit';//引用模板

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$diary_data = M("hd_diary")->where("id=" .$data["zn_hd_diary_id"])->find();
			$this->pagePrevNext($this->myTable,"id","zc_title",' zn_hd_diary_id= '.$data["zn_hd_diary_id"]." and ");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			//$this->myForm[1][4]=array('textShow', 'zl_order_progress', "进度",1,'{"is_data":"1","creat_hidden":"1"}');
			$form_array["diary_nickname"]=$diary_data["zc_nickname"];
			$form_array["zn_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$form_array["zl_order_progress_data"]=C('DIARY_STEP');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
			
			$this->assign("edit_index_url",U("HdDiaryDetail/index/s/".base64_encode("diary/".$data["zn_hd_diary_id"]."/")."/"));//返回首页
            $this->display($lcdisplay);
        }
    }

	//上下分页 
    protected function pagePrevNext($M_PAGE,$id,$title,$sql='') {
			$data_prev=$M_PAGE->field("`$id`,`$title`")->where($sql."$id<" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_next=$M_PAGE->field("`$id`,`$title`")->where($sql."$id>" .$this->lqgetid)->order("`$id` ASC")->limit("0,1")->select();
			$data_up_down_page='';
			if($data_prev){
				$data_up_down_page.='<li><a href="'.__ACTION__."/tnid/".$data_prev[0]["$id"].'" title="上一条：'.lq_kill_html($data_prev[0]["$title"],20).'"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}
			if($data_next){
				$data_up_down_page.='<li><a href="'.__ACTION__."/tnid/".$data_next[0]["$id"].'" title="下一条：'.lq_kill_html($data_next[0]["$title"],20).'"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}
			$this->assign("data_up_down_page",$data_up_down_page);
    }
	
	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }	

	//排序页面
	public function sort() {}
	
	

}
?>