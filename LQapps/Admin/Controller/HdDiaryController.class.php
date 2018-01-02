<?php //装修日记 HdDiary 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class HdDiaryController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'作品信息'),
		//通用信息
		'1'=>array(
		array('buttonDialog', 'zn_works_id', "邦定作品",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"DesignerWorks","type":"window","checkbox":"0"}'),
		//array('buttonDialog', 'zn_designer_id', "邦定设计师",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"Designer","type":"window","checkbox":"0"}'),
		array('text', 'zc_title', "标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":100}'),
		array('image', 'zc_image', "日记封面",1,'{"required":"1","type":"works","allowOpen":1}'),
		array('image', 'zc_headimg', "会员头像",1,'{"required":"1","type":"avatar","allowOpen":1}'),
		array('text', 'zc_nickname', "会员昵称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('checkbox', 'zc_style', "风格",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0,"menu":0}'),
		array('text', 'zn_area', "面积",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zn_household', "户型",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		),
		//数据信息
		'2'=>array(
		array('radio', 'zl_is_index', "推荐首页",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_page_view', "访问数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_agrees', "点赞数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),		
		
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}
    
	//列表页
    public function index() {
		if($this->getSafeData('clearcache')){ }		
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'type'=>urldecode(I('get.type','0','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		
		//sql合并
		$sqlwhere_parameter=" 1 ";
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_nickname ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_nickname like'".$search_content_array["fkeyword"]."%'";
			}
		}
		if($search_content_array["type"]){
			if($search_content_array["type"]==1){
				$sqlwhere_parameter.=" and zl_is_index =1 ";
			}elseif($search_content_array["type"]==2){
				$sqlwhere_parameter.=" and zl_member_apply =1 ";
			}else{
				$sqlwhere_parameter.="";
			}
		}			
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_image'=>'封面图片','zc_title'=>'标题','zc_member_account'=>'会员','zc_nickname'=>'昵称','zl_is_index'=>'推荐','zl_member_apply'=>'用户申请','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zc_title`,`zn_member_id`,`zn_designer_id`,`zc_member_account`,`zc_image`,`zc_nickname`,`zl_is_index`,`zl_member_apply`,`zl_visible`,`zn_mdate`,`zn_cdate`",
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

			
			//表单数据初始化s
			$form_array=array();
			$form_array["id"]='';
			$form_array["zc_title"]="";
			$form_array["zc_nickname"]="";
			$form_array["zn_page_view"]=$form_array["zn_agrees"]=$form_array["zn_area"]=0;
			$form_array["zc_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

			$this->assign("edit_index_url",U("HdDiary/index/s/".base64_encode("designer/".$this->designer."/")."/"));//返回首页
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
			$lcdisplay='edit';//引用模板

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$lc_fid_label = M("designer_works")->where("id=" .(int)$data["zn_works_id"] )->getField("zc_caption");
			$this->pagePrevNext($this->myTable,"id","zc_nickname");//上下页
			$this->myForm[2][3]=array('textShow', 'zc_designer', "设计师",1,'{"is_data":"0","creat_hidden":"0"}');
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zc_designer"]= M("designer")->where("id=".(int)$data["zn_designer_id"])->getField("zc_nickname");			
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zn_works_id_label"]=$lc_fid_label;
			$form_array["zc_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);		
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zn_area_data"]=F('hd_attribute_3','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
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

	//排序页面
	public function sort() {
		$this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg,U("HdDiary/index"));
	}

}
?>