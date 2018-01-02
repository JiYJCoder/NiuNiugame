<?php //地区管理 Region 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class RegionController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(						 
		array('buttonDialog', 'zn_fid', "父级地区",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"Region","type":"tree","checkbox":"0"}'),
		array('text', 'zc_name', "菜单名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
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
		if($this->getSafeData('clearcache')){$this->ajaxReturn($this->C_D->lqCacheData(1));}
		
		//首页设置s
		$page_title=array('no'=>L("LIST_NO"),'id'=>L("LIST_ID"),'zc_name'=>'节点结构','zn_class'=>'相于根级别','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);	
		$this->assign("page_config",$page_config);//列表设置赋值模板
		
		$lnfid=intval($_GET["tnfid"]);
		if(!$lnfid) $lnfid=1;
		$data_region = $this->myTable->field("`zn_fid`")->where("id=".$lnfid)->find();
		$lntclass=intval($_GET["tntclass"]);
		$lnclass_temp=$lntclass.','.($lntclass+1).','.($lntclass+2);
		$lawhere=" zn_fid=$lnfid and zn_class in ($lnclass_temp)  or id = $lnfid ";
		$this->assign("list", $this->C_D->lqList($lawhere,$data_region["zn_fid"],'index'));
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
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_class_data"]=C('ROOT_CLASS');
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
			$lc_fid_label = $this->myTable->where("id=" .(int)$data["zn_fid"] )->getField("zc_name");
			if(!$lc_fid_label) $lc_fid_label=L("LABEL_TOP_FID");
			$this->pagePrevNext($this->myTable,"id","zc_name");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_fid_label"]=$lc_fid_label;
			$form_array["zn_class_data"]=C('ROOT_CLASS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
	
		
	//缓存数据
    public function cacheData(){$this->ajaxReturn($this->C_D->lqCacheData(1));}
	//单记录删除 
    public function opDelectRecord($is_tree=1){$this->ajaxReturn($this->C_D->lqDelete($is_tree));}
	//更改  zlvisible 
    public function opVisible($is_tree=1){$this->ajaxReturn($this->C_D->lqVisible($is_tree));}

	//节点树状
    public function tree() {
		$url_field=I("get.field",'','htmlspecialchars');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		
		$lcdisplay='Public/common-tree';
		$list=F('region_tree','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_name"];
		}
		$this->assign("list", $list);//地区列表
		$this->assign("tree_label", "地区列表");//标题
        $this->display($lcdisplay);
    }

}
?>