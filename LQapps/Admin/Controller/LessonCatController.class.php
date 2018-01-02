<?php //课程分类 LessonCat 页面操作
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class LessonCatController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('buttonDialog', 'zn_fid', "父级分类",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"LessonCat","type":"tree","checkbox":"0"}'),
		array('text', 'zc_caption', "分类标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_is_index', "首页推荐",1,'{"class":"","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_image', "分类图标",1,'{"type":"products","allowOpen":1}'),
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
		//首页设置
		$page_title=array('no'=>L("LIST_NO")."/".L("LIST_ID"),'zc_caption'=>'节点结构','is_index'=>'首页推送','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS")."(编辑/审核/删除)");
		$page_config = array(
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);	
		$this->assign("page_config",$page_config);//列表设置赋值模板
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
			$form_array["id"]='';
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');	
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
			$lc_fid_label = $this->myTable->where("id=" .(int)$data["zn_fid"] )->getField("zc_caption");
			if(!$lc_fid_label) $lc_fid_label=L("LABEL_TOP_FID");
			$this->pagePrevNext($this->myTable,"id","zc_caption");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_fid_label"]=$lc_fid_label;
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
	
		
	//缓存数据
    public function cacheData(){$this->ajaxReturn($this->C_D->lqCacheData(1));}
	//单记录删除 
    public function opDelete($is_tree=1){$this->ajaxReturn($this->C_D->lqDelete($is_tree));}
	//更改  zlvisible 
    public function opVisible($is_tree=1){$this->ajaxReturn($this->C_D->lqVisible($is_tree));}

	//产品分类
    public function tree() {
		$url_field=I("get.field",'','htmlspecialchars');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		
		$lcdisplay='Public/common-tree';
		$list=F('lesson_cat','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//产品分类
		$this->assign("tree_label", "产品分类");//产品分类
        $this->display($lcdisplay);
    }

	//产品分类操作列表
    public function treeWin() {
		$lcos=I("get.op",'','htmlspecialchars');
		//表单数据初始化s
		$FORM_DATA=array();
		$FORM_DATA["id"] = $this->lqgetid;
		$FORM_DATA["op"] = $lcos;

		if($lcos=='copy'){
			$FORM_DATA["tree_label"] = '分类下的产品复制';
			$FORM_DATA["td_label"] = '粘贴到';
			
		}else{
			$FORM_DATA["tree_label"] = '分类下的产品转移';
			$FORM_DATA["td_label"] = '转移到';
			
		}
		$this->assign("fdata", $FORM_DATA);
		
		$list=F('lesson_cat','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//产品分类
		if( !$FORM_DATA["id"] | !$FORM_DATA["op"] ){
			$this->assign("list", array());//产品分类
		}			
		$lcdisplay='treeWin';
        $this->display($lcdisplay);
    }
	
	//产品分类操作列表 - 操作
    public function opProperty() {
		$lcos=I("get.op",'','htmlspecialchars');
		$oid=I("get.oid",'0','int');
		
		$product = M("lesson")->where("zn_cat_id=".$oid)->select();
		if(!$product) $this->ajaxReturn( array('status' => 0, 'msg' => "无数据操作" , 'data' => '' ) );
		if($lcos=='copy'){
			foreach ($product as $lnKey => $laValue) {
				$DATA_ARRAY=array();
				foreach ($laValue as $k => $v) {
						$DATA_ARRAY[$k]=$v;
				}	
				unset($DATA_ARRAY["id"]);
				$DATA_ARRAY["zn_cat_id"]= $this->lqgetid;					
				M("product")->add($DATA_ARRAY);
			}
			$msg="成功复制".count($product)."条数据";
		}else if($lcos=='transform'){
			M()->execute( "UPDATE __PREFIX__product SET zn_cat_id = ".$this->lqgetid." WHERE zn_cat_id = ".$oid );
			$msg="成功转移".count($product)."条数据";
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => "操作失败" , 'data' => '' ) );
		}
		
		$this->ajaxReturn( array('status' => 1, 'msg' => $msg , 'data' => '' ) );
		
	}	
		

	//获取产品总数
    public function ajaxGetCount() {
		$tree = new \LQLibs\Util\Category('lesson_cat', array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($this->lqgetid,10,'zl_visible=1');
		if (ereg("^[0-9]+$", $child_ids )){
		$sqlwhere_parameter=" zn_cat_id = ".intval($child_ids);
		}else{
		$sqlwhere_parameter=" zn_cat_id in (".$child_ids.") ";
		}	
		$count = M("lesson")->where($sqlwhere_parameter)->count();
		$this->ajaxReturn( array('status' => 1, 'msg' => "获取成功" , 'data' => $count ) );
	}


	
}
?>