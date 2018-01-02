<?php //微信菜单 WxMenu 介面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class WxMenuController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
	    array('buttonDialog', 'zn_fid', "父级菜单",1,'{"controller":"WxMenu","type":"tree","checkbox":"0","title":"栏目列表","required":"0","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zc_type', "菜单接口类型",1,'{"class":"mb5","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_caption', "菜单名称",1,'{"class":"inputbox","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_link', "菜单链接",1,'{"class":"inputbox","required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "菜单排序",1,'{"class":"inputbox smallbox","required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('textShow', 'zc_key', "菜单KEY",1,'{"is_data":"0","creat_hidden":"0"}'),
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
		//首页设置s
		$page_title=array('no'=>L("LIST_NO")."/".L("LIST_ID"),'zc_caption'=>'节点结构','zn_class'=>'菜单接口类型','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'title'=>$page_title,
				'thinkphpurl'=>__CONTROLLER__."/",
		);	
		$this->assign("page_config",$page_config);//列表设置赋值模板
		$this->assign("list", $this->C_D->lqList());
		$this->assign('empty_msg',$this->tableEmptyMsg(count($page_title)));
        $this->display();
    }
	
	//生成微信菜单
    public function creatWxMenu() {
		if(session('admin_auth')["id"]!=1){$this->ajaxReturn(array('status' => 0, 'msg' => "对不起，您未授权访问该页面！" , 'data' => '' ));}		
		$wx_menulist=F('wx_menu','',COMMON_ARRAY);
		if($wx_menulist){
		
		//设置微信菜单
		import('Vendor.Wechat.TPWechat');   
		$WxObj = new \Wechat(C("WECHAT"));		
		$menu_data_array_button=array();
		$menu_data_array=array();
		$index=0;
		foreach ($wx_menulist as $k => $v) {
			if($v["zn_fid"]==0&&$v["zl_visible"]==1){
					if($v["zc_type"]=='menu'){
						$menu_data_array[$index]["name"]=$v["zc_caption"];
					}else{
						$menu_data_array[$index]["type"]=$v["zc_type"];
						$menu_data_array[$index]["name"]=$v["zc_caption"];
						if($v["zc_type"]=='view'){
							$menu_data_array[$index]["url"]=str_replace('&amp;','&',$v["zc_link"]);
						}elseif($v["zc_type"]=='click'){
							$menu_data_array[$index]["key"]=$v["zc_type"];
						}else{
							$menu_data_array[$index]["key"]=$v["zc_type"];
						}
					}
					$sub_button_array=array();
					$sub_button_list = M("wx_menu")->field("*")->where("zn_fid=".$v["id"]." and zl_visible=1")->order("zn_sort,id DESC")->select();
					foreach ($sub_button_list as $key => $value) {
						$sub_button_array[$key]["type"]=$value["zc_type"];
						$sub_button_array[$key]["name"]=$value["zc_caption"];
						if($value["zc_type"]=='view'){
							$sub_button_array[$key]["url"]=str_replace('&amp;','&',$value["zc_link"]);
						}elseif($value["zc_type"]=='click'){
							$sub_button_array[$key]["key"]=$value["zc_type"];
						}else{
							$sub_button_array[$key]["key"]=$value["zc_type"];
						}
					}
					if($sub_button_array) $menu_data_array[$index]["sub_button"]=$sub_button_array;
					$index++;
			}
		}
		$menu_data_array_button["button"]=$menu_data_array;
		$WxObj->createMenu($menu_data_array_button);
        $this->ajaxReturn(array('status' => 1, 'msg' => "生成微信菜单成功" , 'data' => '' ));
		}else{
        $this->ajaxReturn(array('status' => 0, 'msg' => "没有微信菜单可生成" , 'data' => '' ));
		}
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
			$form_array["zc_type_data"]=C('WEIXIN_MENU_TYPE');
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
			$form_array["zc_type_data"]=C('WEIXIN_MENU_TYPE');			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

            $this->display($lcdisplay);
        }
    }
	
		
	//缓存数据
    public function cacheData() {$this->ajaxReturn($this->C_D->lqCacheData(1));}
	//单记录删除 
    public function opDelectRecord($is_tree=1) {$this->ajaxReturn($this->C_D->lqDelete($is_tree));}
	//更改  zlvisible 
    public function opVisible($is_tree=1) {$this->ajaxReturn($this->C_D->lqVisible($is_tree));}

	//微信菜单
    public function tree() {
		$url_field=I("get.field",'','htmlspecialchars');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		
		$lcdisplay='Public/common-tree';
		$list=F('wx_menu','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//网站栏目
		$this->assign("tree_label", "微信菜单");//网站栏目
        $this->display($lcdisplay);
    }


}
?>