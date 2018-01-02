<?php //银行信息表 Bank 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class BankController extends PublicController{
	public $myTable,$MemberModel;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('textShow', 'zc_member_account', "用户帐号",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('textShow', 'zc_bank_name', "银行名称",1,'{"is_data":"0","creat_hidden":"0"}'),
		array('imageShow', 'zc_member_headimg', "用户头像",1,'{"is_data":"0","tip":"如果没有用户头像请到会员管理中心处理。"}'),
		array('radio', 'zl_is_index', "推荐首页",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_contact', "联系人",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_contact_tel', "联系电话",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('editor', 'zc_content', "银行装修贷内容",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),		
		)
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
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_bank_name ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_bank_name like'".$search_content_array["fkeyword"]."%' ";
			}	
		}
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_bank_name'=>'银行','count'=>'订单数目','zl_is_index'=>'首页推荐','os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_member_account`,`zc_bank_name`,`zl_is_index`",
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

	
	// 插入/添加
    public function add() {
			$this->error(C("ALERT_ARRAY")["recordNull"]);
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
			PAGE_S("page_Bank_".intval($_POST["LQF"]["id"]),NULL);	//清除设计师缓存
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='Public/common-edit';
			
			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_bank_name");//上下页
							
			//表单数据初始化s
			$form_array=$year_array=array();
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) $form_array[$lnKey]=$laValue;
			$form_array["zc_member_headimg"]=$this->MemberModel->apiGetFieldByID($data["zn_member_id"],'zc_headimg');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }
	
	//设计师选择器
    public function window() {
		$this->assign("title",'设计师选择器');//标题		
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数
		$search_content_array=array(
			'fkeyword'=> urldecode(I('get.fkeyword',$this->keywordDefault)),
			'field'=> urldecode(I('get.field','')) ,
			'returnData'=> urldecode(I('get.returnData','')) ,
			'checkbox'=> urldecode(I('get.checkbox','0','int')),
			'quantity'=> urldecode(I('get.quantity','0','int')),
			'type'=> urldecode(I('get.type','')) ,
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			$sqlwhere_parameter.=" and zc_bank_name ='".$search_content_array["fkeyword"]."' ";
		}
		
		//首页设置
		$page_config = array(
				'field'=>"`id`,`zn_member_id`,`zc_bank_name`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
				'thinkphpurl'=>__CONTROLLER__."/",
		);
		C("PAGESIZE",12);
		//列表表单初始化****end
		
        $count = $this->myTable->alias("p")->where($sqlwhere_parameter)->count();
		$page = new \LQLibs\Util\Page($count,C("PAGESIZE"),$page_parameter);//载入分页类
        $showPage = $page->window_show();
        $this->assign("page", $showPage);
        $this->assign("list", $this->C_D->lqList($page->firstRow, $page->listRows,$page_config));
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
		$this->assign("page_config",$page_config);//列表设置赋值模板
        $this->display();
    }

}
?>