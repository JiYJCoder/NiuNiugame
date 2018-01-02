<?php //设计师作品 Works 页面操作 
namespace Ucenter\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

class WorksController extends PublicController{
	public $myTable,$designer,$C_D;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'作品简介'),
		//通用信息
		'1'=>array(
		array('text', 'zc_caption', "作品名称",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('select', 'zn_style', "风格",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('select', 'zn_household', "户型",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('select', 'zn_area', "面积",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择"}'),
		array('image', 'zc_works_photo', "作品封面",1,'{"type":"works","allowOpen":1}'),
		array('multiImage', 'zc_works_photos', "作品图册",1,'{"type":"works","imageUploadLimit":10,"allowOpen":1,"returnData":"paths"}'),
		),
		//作品简介
		'2'=>array(
		array('editor', 'zc_introduction', "作品简介",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),		
		),		
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->isDesigner();//银行认证
		$this->myTable = M("designer_works");//主表实例化
		if($this->login_member_info) $this->designer = M("designer")->where("zn_member_id=".$this->login_member_info["id"])->find();
		$this->C_D=D("Works");//实例D方法
	}
    
    public function index0() {
		$this->index(0);
	}
	//列表页
    public function index($status=1) {
		//列表表单初始化****start
		$page_parameter["lqs"]=$this->getSafeData('lqs');
		$this->reSearchPara($page_parameter["lqs"]);//反回搜索数据
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'style'=>urldecode(I('get.style','0','int')),
			'household'=>urldecode(I('get.household','0','int')),
			'area'=>urldecode(I('get.area','0','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("style_select_str", lqCreatOption(F('hd_attribute_1','',COMMON_ARRAY),$search_content_array["style"],"选择风格"));//风格		
		$this->assign("household_select_str", lqCreatOption(F('hd_attribute_2','',COMMON_ARRAY),$search_content_array["household"],"选择户型"));//户型		
		$this->assign("area_select_str", lqCreatOption(F('hd_attribute_3','',COMMON_ARRAY),$search_content_array["area"],"选择面积"));//面积
				
				
		//sql合并
		$sqlwhere_parameter=" 1 and zn_member_id = ".$this->login_member_info["id"];
		if($status==0) $sqlwhere_parameter.=" and zl_visible = 0 ";
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_caption ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_caption like'".$search_content_array["fkeyword"]."%'";
			}
		}
		if($search_content_array["style"])	$sqlwhere_parameter.=" and zn_style = ".$search_content_array["style"];
		if($search_content_array["household"])	$sqlwhere_parameter.=" and zn_household = ".$search_content_array["household"];
		if($search_content_array["area"])	$sqlwhere_parameter.=" and zn_area = ".$search_content_array["area"];
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_works_photo'=>'作品封面','attribute'=>'户型','zc_caption'=>'作品名称','zn_clicks'=>"点击/点赞/图册",'zl_is_index'=>'首页推荐','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zn_designer_id`,`zc_thumb`,`zc_caption`,`zn_sort`,`zn_clicks`,`zn_agrees`,`zn_style`,`zn_household`,`zn_area`,`zl_visible`,`zc_works_photos`,`zl_is_index`",
				'where'=>$sqlwhere_parameter,
				'order'=>'id DESC',
				'title'=>$page_title,
				'thinkphpurl'=>"/do?g=ucenter&m=works",
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
		
		$lcdisplay='index';//引用模板
        $this->display($lcdisplay);
    }
	
	// 插入/添加
    public function add() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';//引用模板
			if(!$this->designer) $this->error('数据不完善，请联系管理员！'.$this->systemMsg,U("ucenter&m=index&a=index"));
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zn_style_data"]=F('hd_attribute_1','',COMMON_ARRAY);
			$form_array["zn_household_data"]=F('hd_attribute_2','',COMMON_ARRAY);
			$form_array["zn_area_data"]=F('hd_attribute_3','',COMMON_ARRAY);		
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
			$returnData=$this->C_D->lqSubmit();
			D('Api/Designer')->getWorksById(intval($_POST["LQF"]["id"]),1);
            $this->ajaxReturn($returnData);			
        } else {
			$lcdisplay='edit';
			
			//读取数据
			$data = $this->myTable->where("zn_member_id=".$this->login_member_info["id"]." and id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->assign("designer",$data["zn_designer_id"]);
			$this->pagePrevNext($this->myTable,"id","zc_caption",'zn_member_id= '.$this->login_member_info["id"]." and ");//上下页
			
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
			
            $this->display($lcdisplay);
        }
    }

	//上下分页 
    protected function pagePrevNext($M_PAGE,$id,$title,$sql='') {
			$data_prev=$M_PAGE->field("`$id`,`$title`")->where($sql."$id>" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_next=$M_PAGE->field("`$id`,`$title`")->where($sql."$id<" .$this->lqgetid)->order("`$id` DESC")->limit("0,1")->select();
			$data_up_down_page='';
			if($data_prev){
				$data_up_down_page.='<li><a href="'.U("ucenter/works/edit?tnid=".$data_prev[0]["$id"]).'" title="上一条：'.lq_kill_html($data_prev[0]["$title"],20).'"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-left"></i> 上一条</a></li>';
			}
			if($data_next){
				$data_up_down_page.='<li><a href="'.U("ucenter/works/edit?tnid=".$data_next[0]["$id"]).'" title="下一条：'.lq_kill_html($data_next[0]["$title"],20).'"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}else{
				$data_up_down_page.='<li class="line-th"><a href="javasrctpt:;" title="空记录"><i class="fa fa-arrow-circle-right"></i> 下一条</a></li>';
			}
			$this->assign("data_up_down_page",$data_up_down_page);
    }



}
?>