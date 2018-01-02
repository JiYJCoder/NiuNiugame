<?php //微信机器人 WxRobot 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class WxRobotController extends PublicController{
	public $myTable,$patype;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//基本信息
		'1'=>array(
		array('select', 'zl_type', "消息类型",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":""}'),
		array('text', 'zc_label', "消息说明",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_keyword', "消息关键字",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_image', "消息图片",1,'{"type":"images","allowOpen":1,"required":"1"}'),
		array('text', 'zc_url', "消息图片链接",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('textarea', 'zc_reply', "消息内容",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		),
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
		$this->patype=array(1=>'回复消息(text)',2=>'回复图文(news)');
	}
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){}		
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		//sql合并
		$sqlwhere_parameter=" 1 ";
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and (zc_label ='".$search_content_array["fkeyword"]."') ";
			}else{
			$sqlwhere_parameter.=" and (zc_label like'%".$search_content_array["fkeyword"]."%') ";
			}
		}
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'id'=>L("LIST_ID"),'zl_type'=>'类型','zc_label'=>'消息标题','zc_keyword'=>'关键字','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zl_type`,`zc_label`,`zc_keyword`,`zl_visible`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
				
		);
		if($search_content_array["pagesize"]) C("PAGESIZE",$search_content_array["pagesize"]);
		//列表表单初始化****end
		
        $count = $this->myTable->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count,C("PAGESIZE"),$page_parameter);//载入分页类
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
			$lcdisplay='edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			if($form_array){
				$form_array["zl_type"]=1;
			}	
			$form_array["id"]='';
			$form_array["zl_type_data"]=$this->patype;
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
			
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
			$data = $this->myTable->where("id=".$this->lqgetid)->find();
			if(!$data) {$this->error(C("ALERT_ARRAY")["recordNull"]);}//无记录
			$this->pagePrevNext($this->myTable,"id","zc_label");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			if($data["zl_type"]==1){
				unset($this->myForm[1][3]);
				unset($this->myForm[1][4]);
			}
			$form_array["zl_type_data"]=$this->patype;
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
		
	//缓存数据
    public function cacheData(){}

	//排序页面
	public function sort(){}

}
?>