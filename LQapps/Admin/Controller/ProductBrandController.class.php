<?php //产品品牌 ProductBrand 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class ProductBrandController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'品牌详情'),
		//基本信息
		'1'=>array(
		array('text', 'zc_caption', "品牌名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_company_name', "厂商名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_logo', "LOGO图片",1,'{"type":"brand","allowOpen":1,"required":"1"}'),
		array('color', 'zc_logo_bgcolor', "LOGO底色",1,'{"required":"1","dataType":"color","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_banner', "BANNER图片",1,'{"type":"brand","allowOpen":1}'),
		array('radio', 'zl_recommend', "品牌推荐",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),
		//品牌详情
		'2'=>array(
		array('editor', 'zc_content', "品牌详情",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),	
		),		
		
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){$this->C_D->lqCacheData(1);}		
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
			$sqlwhere_parameter.=" and (zc_caption ='".$search_content_array["fkeyword"]."') ";
			}else{
			$sqlwhere_parameter.=" and (zc_caption like'%".$search_content_array["fkeyword"]."%') ";
			}
		}
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'id'=>L("LIST_ID"),'zc_logo'=>'品牌LOGO','zc_caption'=>'品牌名称','zn_sort'=>L("LIST_SOTR"),'zl_recommend'=>'品牌推荐','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_logo`,`zc_caption`,`zl_recommend`,`zn_sort`,`zl_visible`",
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
			$lcdisplay='Public/common-edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zc_logo_bgcolor"]="#ffffff";
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zl_recommend_data"]=C('YESNO_STATUS');
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
			$lcdisplay='Public/common-edit';

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_caption");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zl_recommend_data"]=C('YESNO_STATUS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
		
	//缓存数据
    public function cacheData() {
		$this->ajaxReturn($this->C_D->lqCacheData(1));
	}

	//更改字段值 
    public function opProperty() {
        $this->ajaxReturn($this->C_D->setProperty());
    }	

	//排序页面
	public function sort() {
		$list =$this->myTable->where('1')->order('zn_sort,id desc')->field("`id`,`zc_caption` as label")->select();
		$this->assign("list", $list);
		$lcdisplay='Public/common-sort';//引用模板
		$this->display($lcdisplay);
	}

}
?>