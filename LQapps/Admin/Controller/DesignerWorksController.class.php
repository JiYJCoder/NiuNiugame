<?php //设计师作品 DesignerWorks 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class DesignerWorksController extends PublicController{
	public $myTable,$MemberModel,$designer;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'作品属性',3=>'作品简介'),
		//通用信息
		'1'=>array(
		array('hidden', 'zn_member_id', "会员ID",1,''),
		array('hidden', 'zn_designer_id', "设计师ID",1,''),
		array('textShow', 'zc_designer_nickname', "设计师昵称",1,'{"is_data":"0","creat_hidden":"1"}'),
		array('text', 'zc_caption', "作品名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zn_style', "风格",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('select', 'zn_household', "户型",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('select', 'zn_area', "面积",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('image', 'zc_works_photo', "作品封面",1,'{"type":"works","allowOpen":1}'),
		array('multiImage', 'zc_works_photos', "作品图册",1,'{"type":"works","imageUploadLimit":10,"allowOpen":1,"returnData":"paths"}'),
		),
		'2'=>array(
		array('radio', 'zl_is_index', "推荐首页",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('date', 'zn_work_release', "发布日期",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"format":"day"}'),
		array('text', 'zn_clicks', "点击数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_agrees', "点赞数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		),		
		//作品简介
		'3'=>array(
		array('editor', 'zc_introduction', "作品简介",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),		
		),		
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
		$this->MemberModel = new MemberApi;
		$this->designer=I("get.designer",'0','int');
		$this->assign("designer",$this->designer);		
	}
    
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){  $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg,U("DesignerWorks/index"));	  }		
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'designer'=>urldecode(I('get.designer','0','int')),
			'style'=>urldecode(I('get.style','0','int')),
			'household'=>urldecode(I('get.household','0','int')),
			'area'=>urldecode(I('get.area','0','int')),
			'recommend'=>urldecode(I('get.recommend','0','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("add",U("DesignerWorks/add/designer/".$search_content_array["designer"]));//新增
		$this->assign("sort",U("DesignerWorks/sort/designer/".$search_content_array["designer"]));//排序
		$this->assign("refresh",U("DesignerWorks/index/s/".base64_encode("designer/".$search_content_array["designer"]."/")."/"));//刷新
		$this->assign("style_select_str", lqCreatOption(F('hd_attribute_1','',COMMON_ARRAY),$search_content_array["style"],"选择风格"));//风格		
		$this->assign("household_select_str", lqCreatOption(F('hd_attribute_2','',COMMON_ARRAY),$search_content_array["household"],"选择户型"));//户型		
		$this->assign("area_select_str", lqCreatOption(F('hd_attribute_3','',COMMON_ARRAY),$search_content_array["area"],"选择面积"));//面积
		$recommend_array=array(
			1=>'推荐首页',
		);		
		$this->assign("recommend_str",lqCreatOption($recommend_array,$search_content_array["recommend"],"请选择"));				
				
				
		//sql合并
		$sqlwhere_parameter=" 1 ";
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_caption ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_caption like'".$search_content_array["fkeyword"]."%'";
			}
		}
		if($search_content_array["designer"])	$sqlwhere_parameter.=" and zn_designer_id = ".$search_content_array["designer"];
		if($search_content_array["style"])	$sqlwhere_parameter.=" and zn_style = ".$search_content_array["style"];
		if($search_content_array["household"])	$sqlwhere_parameter.=" and zn_household = ".$search_content_array["household"];
		if($search_content_array["area"])	$sqlwhere_parameter.=" and zn_area = ".$search_content_array["area"];
		if($search_content_array["recommend"]){
			if ( $search_content_array["recommend"]==1 ){
				$sqlwhere_parameter.=" and zl_is_index =1 ";
			}else{
				$sqlwhere_parameter.="";
			}
		}		
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_works_photo'=>'作品封面','attribute'=>'户型','zc_caption'=>'作品名称','zn_clicks'=>"点击/点赞/图册",'zl_is_index'=>'首页推荐','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zn_designer_id`,`zc_thumb`,`zc_caption`,`zn_sort`,`zn_clicks`,`zn_agrees`,`zn_style`,`zn_household`,`zn_area`,`zl_visible`,`zc_works_photos`,`zl_is_index`",
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
			if(!$this->designer) $this->error('请选择设计师。谢谢！'.$this->systemMsg,U("Designer/index"));
			$designer_data = M("designer")->where("id=" .$this->designer)->find();
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zn_member_id"]=$designer_data["zn_member_id"];
			$form_array["zn_designer_id"]=$this->designer;
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_clicks"]=0;
			$form_array["zn_agrees"]=0;
			$form_array["zn_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zn_area_data"]=F('hd_attribute_3','',COMMON_ARRAY);		
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$form_array["zc_designer_nickname"]=$this->MemberModel->apiGetFieldByID($designer_data["zn_member_id"]);			
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

			$this->assign("edit_index_url",U("DesignerWorks/index/s/".base64_encode("designer/".$this->designer."/")."/"));//返回首页
            $this->display($lcdisplay);
        }
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
			$returnData=$this->C_D->lqSubmit();
			D('Api/Designer')->getWorksById(intval($_POST["LQF"]["id"]),1);
            $this->ajaxReturn($returnData);			
        } else {
			$lcdisplay='edit';

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->assign("designer",$data["zn_designer_id"]);
			$this->pagePrevNext($this->myTable,"id","zc_caption",' zn_designer_id= '.$data["zn_designer_id"]." and ");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zn_area_data"]=F('hd_attribute_3','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s
			
			$this->assign("edit_index_url",U("DesignerWorks/index/s/".base64_encode("designer/".$data["zn_designer_id"]."/")."/"));//返回首页
            $this->display($lcdisplay);
        }
    }

	//上下分页 
    protected function pagePrevNext($M_PAGE,$id,$title,$sql='') {
			$data_prev=$M_PAGE->field("`$id`,`$title`")->where($sql."$id>" .$this->lqgetid)->order("`$id` ASC")->limit("0,1")->select();
			$data_next=$M_PAGE->field("`$id`,`$title`")->where($sql."$id<" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
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
	public function sort() {
		$list =$this->myTable->where('zl_visible=1 and zn_designer_id='.$this->designer)->order('zn_sort,id desc')->field("`id`,`zc_caption` as label")->select();
		$this->assign("list", $list);
		$lcdisplay='Public/common-sort';//引用模板
		$this->display($lcdisplay);
	}
	
	
	//作品选择器
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
			'style'=>urldecode(I('get.style','0','int')),
			'household'=>urldecode(I('get.household','0','int')),
			'area'=>urldecode(I('get.area','0','int')),			
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("style_select_str", lqCreatOption(F('hd_attribute_1','',COMMON_ARRAY),$search_content_array["style"],"选择风格"));//风格		
		$this->assign("household_select_str", lqCreatOption(F('hd_attribute_2','',COMMON_ARRAY),$search_content_array["household"],"选择户型"));//户型		
		$this->assign("area_select_str", lqCreatOption(F('hd_attribute_3','',COMMON_ARRAY),$search_content_array["area"],"选择面积"));//面积
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			$sqlwhere_parameter.=" and zc_caption ='".$search_content_array["fkeyword"]."' ";
		}
		if($search_content_array["style"])	$sqlwhere_parameter.=" and zn_style = ".$search_content_array["style"];
		if($search_content_array["household"])	$sqlwhere_parameter.=" and zn_household = ".$search_content_array["household"];
		if($search_content_array["area"])	$sqlwhere_parameter.=" and zn_area = ".$search_content_array["area"];
		
		
		
		//首页设置
		$page_config = array(
				'field'=>"`id`,`zn_designer_id`,`zc_thumb`,`zc_caption`,`zn_sort`,`zn_clicks`,`zn_agrees`,`zn_style`,`zn_household`,`zn_area`,`zl_visible`,`zc_works_photos`,`zl_is_index`",
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