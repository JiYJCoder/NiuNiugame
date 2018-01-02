<?php //系统架构 SystemMenu 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
class SystemMenuController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(							 
		array('buttonDialog', 'zn_fid', "父级菜单",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"SystemMenu","type":"tree","checkbox":"0"}'),
		array('text', 'zc_caption', "菜单标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_run', "执行文件路径",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_run_table', "执行数据主表",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zc_target', "目标窗口",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zn_type', "类型",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('radio', 'zl_check_pop', "是否需要检查权限",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_is_menu', "左则菜单",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('checkbox', 'zc_index_lock', "列表锁",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"menu":1}'),
		),
	);	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		if(session('admin_auth')["id"]!=1){$this->error('对不起，您未授权访问该页面！'.$this->systemMsg, U("/Index"));}		
		$this->myTable = M($this->pcTable);//主表实例化
	}

	//缓存数据
    public function cacheData(){$this->ajaxReturn($this->C_D->lqCacheData(1));}
	//更改类型 
    public function opProperty(){$this->ajaxReturn($this->C_D->lqSetProperty());}
	//单记录删除 
    public function opDelete($is_tree=1){$this->ajaxReturn($this->C_D->lqDelete($is_tree));}
	//更改  zlvisible 
    public function opVisible($is_tree=1){$this->ajaxReturn($this->C_D->lqVisible($is_tree));}	
	    
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){$this->C_D->lqCacheData(1);}		
		$this->assign("list", $this->C_D->lqList());
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
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
			if(!$form_array){
				$form_array["zc_target"]="_self";
				$form_array["zn_type"]="";
				$form_array["zn_fid"]=0;
			}	
			
			$form_array["id"]='';
			$form_array["zc_target"]='_self';
			$form_array["zc_index_lock"]='0|0|0|0|0|0';
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zl_check_pop"]=1;
			$form_array["zl_is_menu"]=1;
			$form_array["zn_type_data"]=C('SYSMUEN_MODEL');
			$form_array["zc_target_data"]=C('ARRAY_TARGET');
			$form_array["zl_check_pop_data"]=C('YESNO_STATUS');
			$form_array["zl_is_menu_data"]=C('YESNO_STATUS');
			$form_array["zc_index_lock_data"]=C('INDEX_LOCK');
			
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
			$lcdisplay='Public/common-edit';//引用模板

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_caption");//上下页
			$lc_fid_label = $this->myTable->where("id=" .(int)$data["zn_fid"] )->getField("zc_caption");
			if(!$lc_fid_label) $lc_fid_label=L("LABEL_TOP_FID");
			
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_fid_label"]=$lc_fid_label;
			$form_array["zn_type_data"]=C('SYSMUEN_MODEL');
			$form_array["zc_target_data"]=C('ARRAY_TARGET');
			$form_array["zl_check_pop_data"]=C('YESNO_STATUS');
			$form_array["zl_is_menu_data"]=C('YESNO_STATUS');
			$form_array["zc_index_lock_data"]=C('INDEX_LOCK');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据			
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }

	//节点树状
    public function tree() {
		$url_field=I("get.field",'','htmlspecialchars');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		$this->assign("refurbish_url",__CONTROLLER__."/window/field/".$url_field."/checkbox/".$url_checkbox);//打开的窗口回调处理父窗体的
		
		$lcdisplay='Public/common-tree';
		$list=F('sysmuen_tree','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//系统架构
		$this->assign("tree_label", "系统架构");//系统架构
        $this->display($lcdisplay);
    }

	
}
?>