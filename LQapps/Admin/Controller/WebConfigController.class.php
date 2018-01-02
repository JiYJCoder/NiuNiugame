<?php //基本设置 WebConfig 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class WebConfigController extends PublicController{
	public $myTable;
	//表单构建参数
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('buttonDialog', 'zn_fid', "控件分类",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"WebConfig","type":"tree","checkbox":"0"}'),
		array('select', 'zc_type', "控件类型",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择控件类型"}'),
		array('text', 'zc_label', "控件标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_description', "控件描述",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_key', "KEY",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_required', "是否必填",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),
	);

    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}
	//编辑列表
    public function config() {
        if (IS_POST) {
			$post_data=I("post.");
			unset($post_data["LQF"]["id"]);
			unset($post_data["LQF"]["__hash__"]);
			foreach ( $post_data["LQF"] as $key => $value) {
				if(substr($key,0,4)=='INT_'){
				M()->execute( "UPDATE __PREFIX__web_config SET zc_value = '".intval($value)."' WHERE zc_key = '".$key."'" );
				}else{
				M()->execute( "UPDATE __PREFIX__web_config SET zc_value = '".$value."' WHERE zc_key = '".$key."'" );
				}
			}
			F('set_config',$post_data["LQF"],COMMON_ARRAY);
			$this->C_D->lqCacheData();//清缓存
			$this->ajaxReturn(array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]));
        } else {		
			$this->assign("sys_heading",'基本设置.编辑列表');//当前标题
			if($this->getSafeData('clearcache')){$this->C_D->lqCacheData(1);}
				
			//表单构建参数
			$config_form = array();
			$comment =  array();
			$list = F('web_config','',COMMON_ARRAY);

			foreach ($list as $lnKey => $laValue) {
			$config_form["tab_title"][$lnKey+1] = $laValue["zc_label"];
			
				$temp=array();
				if( $laValue["child"] ){
					foreach ($laValue["child"] as $k => $v) {
						$comment[$v["zc_key"]]=$v["zc_description"];
						$form_array[$v["zc_key"]]=$v["zc_value"];
						switch ($v["zc_type"]) {
							case 'text':
								$temp[]=array('text',$v["zc_key"], $v["zc_label"],1,'{"required":"'.$v["zl_required"].'","dataType":"","dataLength":"","readonly":0,"disabled":0}');
							break;	
							case 'select':
								$temp[]=array('select',$v["zc_key"], $v["zc_label"],1,'{"required":"'.$v["zl_required"].'","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择'.$v["zc_label"].'"}');
							break;	
							case 'file':
								$temp[]=array('image',$v["zc_key"], $v["zc_label"] ,1,'{"type":"images","allowOpen":1}');
							break;	
							case 'radio':
								$temp[]=array('radio',$v["zc_key"], $v["zc_label"],1,'{"required":"'.$v["zl_required"].'","dataType":"","dataLength":"","readonly":0,"disabled":0}');
								$form_array[$v["zc_key"]."_data"]=C('YESNO_STATUS');
							break;								
							case 'textarea':
								$temp[]=array('textarea',$v["zc_key"], $v["zc_label"],1,'{"required":"'.$v["zl_required"].'","dataType":"","dataLength":"","readonly":0,"disabled":0,"rows":"3"}');
							break;
							case 'editor':
								$temp[]=array('editor',$v["zc_key"], $v["zc_label"],1,'{"model":"1","ext":"LQF","width":"100%","height":"300px"}');	
							break;							
							
						}
					}
				}
				$config_form[$lnKey+1] = $temp;
			}

			//标明引用标签
			$editMsg='';
			foreach ($comment as $k => $v) {
				$editMsg.=$v.',#引用标签：{$SET_CONFIG.'.$k.'}'."\n";
			}			
			$form_array["editMsg"]=$editMsg;
			$form_array["THUMB_TYPE_data"]=C('THUMB_TYPE_DATA');
			$form_array["THUMB_WATER_TYPE_data"]=C('THUMB_WATER_TYPE');				

			$form_max=count($config_form["tab_title"])+1;
			$config_form["tab_title"][$form_max]='前端引用备注';
			$config_form[$form_max]=array(array('editMsg', 'editMsg', "前端引用",0,'{"required":"0","dataType":"","dataLength":"","readonly":1,"disabled":0,"rows":"20"}'),);
			$Form=new Form($config_form,$form_array,$comment);
			$this->assign("LQFdata",$Form->createHtml());//表单数据

			$lcdisplay='config';//引用模板
			$this->display($lcdisplay);
		}
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
				$sqlwhere_parameter.=" and (zc_label ='".$search_content_array["fkeyword"]."' or zc_key ='".$search_content_array["fkeyword"]."') ";
				}else{
				$sqlwhere_parameter.=" and (zc_label like'".$search_content_array["fkeyword"]."%' or zc_key like'".$search_content_array["fkeyword"]."%') ";
				}
			}
		
			$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'id'=>L("LIST_ID"),'zc_type'=>'控件类型','zc_label'=>'控件标题','zn_sort'=>L("LIST_SOTR"),'zc_key'=>'CONFIG_ARRAY_KEY','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
			$page_config = array(
					'field'=>'`id`,`zc_type`,`zc_label`,`zc_key`,`zn_sort`,`zl_visible`',
					'where'=>$sqlwhere_parameter,
					'order'=>'zn_sort,id DESC',
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
	
	// 插入/添加
    public function add() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zc_key"]=NOW_TIME;
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zc_type_data"]=C('FORM_CONTROLS');
			$form_array["zl_required_data"]=C('YESNO_STATUS');			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			$this->assign("data",$form_array);//表单数据
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
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$lc_fid_label = $this->myTable->where("id=" .(int)$data["zn_fid"] )->getField("zc_label");
			if(!$lc_fid_label) $lc_fid_label=L("LABEL_TOP_FID");			
			$this->pagePrevNext($this->myTable,"id","zc_label");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			
			//数据源 
			$form_array["zn_fid_label"]=$lc_fid_label;
			$form_array["zc_type_data"]=C('FORM_CONTROLS');
			$form_array["zl_required_data"]=C('YESNO_STATUS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			$this->assign("data",$form_array);//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
	
		
	//缓存数据
    public function cacheData(){$this->ajaxReturn($this->C_D->lqCacheData(1));}


	//设置归类
    public function tree(){
		$url_field=I("get.field",'');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		$lcdisplay='Public/common-tree';
		$list= $this->myTable->field('`id`,`zn_fid`,`zc_type`,`zc_label`')->where("zn_fid=0")->select();
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_label"];
			$list[$lnKey]["fullname"]=$laValue["zc_label"];
			$list[$lnKey]["zl_visible"]=1;
		}
		$this->assign("list", $list);//设置归类
		$this->assign("tree_label", "设置归类");//设置归类
        $this->display($lcdisplay);
    }

	
}
?>