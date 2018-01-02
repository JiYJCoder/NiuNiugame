<?php //图册链接系统 AdPosition 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class AdPositionController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('text', 'zc_caption', "位置名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_type', "位置类别",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_image_width', "图册图片的宽度",1,'{"required":"0","dataType":"number","dataLength":"","readonly":1,"disabled":0}'),
		array('text', 'zn_image_height', "图册图片的高度",1,'{"required":"0","dataType":"number","dataLength":"","readonly":1,"disabled":0}'),
            array('text', 'zn_scroll_time', "轮播时间",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_max_ad', "最多数目",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
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
			$sqlwhere_parameter.=" and zc_caption ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_caption like'".$search_content_array["fkeyword"]."%'";
			}
		}
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','id'=>'ID','zc_caption'=>'位置标题','count'=>'广告数目','zn_image_width'=>'图片的宽度(px)','zn_image_height'=>'图片的高度(px)','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_caption`,`zn_sort`,`zn_image_width`,`zn_image_height`,`zl_visible`",
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
			$lcdisplay='edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zl_type_data"]=C('AD_POSITION_TYPE');
			$form_array["zl_type"]=0;			
			$form_array["zn_image_width"]=0;
			$form_array["zn_image_height"]=0;
			$form_array["zn_sort"]=C("COM_SORT_NUM");
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
			$lcdisplay='edit';

			//读取数据s
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_caption");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zl_type_data"]=C('AD_POSITION_TYPE');			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("data",$form_array);//记录数据
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

	//排序页面
	public function sort() {
		$list =$this->myTable->where('zl_visible=1')->order('zn_sort,id desc')->field("`id`,`zc_caption` as label")->select();
		$this->assign("list", $list);
		$lcdisplay='Public/common-sort';//引用模板
		$this->display($lcdisplay);
	}

	//设置宽度高度
    public function opProperty() {$this->ajaxReturn($this->C_D->setImageWH());}
	
}
?>