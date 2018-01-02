<?php //广告列表 AdList 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class AdListController extends PublicController{
	public $myTable,$position,$client_type_array;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('select', 'zn_ad_position_id', "类型",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择广告位置"}'),
		array('select', 'zl_client_type', "投放渠道",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择客户端类型"}'),
		array('text', 'zc_caption', "标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_link_url', "链接",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_clicks', "点击数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('image', 'zc_image', "图片",1,'{"type":"images","allowOpen":1}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
		$this->position=I("get.position",'','int');
		$this->assign("position",$this->position);
		$this->client_type_array=array(
			1=>'通用',
			2=>'WECHAT',
		);			
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
			'position'=>urldecode(I('get.position','0','int')),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$this->assign("add",U("AdList/add/position/".$search_content_array["position"]));//新增
		$this->assign("refresh",U("AdList/index/s/".base64_encode("position/".$search_content_array["position"]."/")."/"));//刷新
		
		if(!$search_content_array["position"]) $this->error('对不起，您输入的访问地址不正确，请重新输入。谢谢！'.$this->systemMsg,U("AdPosition/index"));	
		$ad_position=M("ad_position")->field('zl_type,zc_caption')->where('id='.$search_content_array["position"])->find();
		$this->assign("ad_position",$ad_position);
		
		//sql合并
		$sqlwhere_parameter=" 1 ";
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_caption ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and zc_caption like'".$search_content_array["fkeyword"]."%'";
			}
		}
		if($search_content_array["position"]){
				$sqlwhere_parameter.=" and zn_ad_position_id = '".$search_content_array["position"]."'";
		}			
		
		if($ad_position["zl_type"]==1){
				$sqlwhere_parameter.=" and zn_ad_position_id = '".intval($search_content_array["position"])."'";
				$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_image'=>'图片','zl_client_type'=>'投放渠道','zc_caption'=>'标题','zn_sort'=>L("LIST_SOTR"),'zn_clicks'=>"点击数量",'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		}else{
				$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_caption'=>'标题','zn_clicks'=>"点击数量",'zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		}
				
		//首页设置
		$page_config = array(
				'field'=>"`id`,`zn_ad_position_id`,`zl_client_type`,`zc_image` as image,`zc_caption`,`zn_sort`,`zn_clicks`,`zl_visible`",
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
			if($form_array){
				if( I("get.position",'','int')=='' ) $this->assign("position",$form_array["zn_ad_position_id"]);
			}
			$form_array["id"]='';
			$form_array["zn_ad_position_id_data"]=F('ad_position','',COMMON_ARRAY);//广告位置
			$form_array["zl_client_type_data"]=$this->client_type_array;//客户端类型
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_clicks"]=0;
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

			$this->assign("edit_index_url",U("AdList/index/s/".base64_encode("position/".$this->position."/")."/"));//返回首页
            $this->display($lcdisplay);
        }
    }
	
	// 更新/编辑
    public function edit() {
        if (IS_POST) {
            $this->ajaxReturn($this->C_D->lqSubmit());
        } else {
			$lcdisplay='edit';
			
			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->assign("position",$data["zn_ad_position_id"]);
			$this->pagePrevNext($this->myTable,"id","zc_caption",' zn_ad_position_id= '.$data["zn_ad_position_id"]." and ");//上下页
			
			//表单数据初始化s
			$form_array=array();
			$form_array["os_record_time"]=$this->osRecordTime($data);//操作时间
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_ad_position_id_data"]=F('ad_position','',COMMON_ARRAY);//广告位置
			$form_array["zl_client_type_data"]=$this->client_type_array;//客户端类型
			$Form=new Form($this->myForm,$form_array,$this->myTable->getCacheComment());
			$this->assign("LQFdata",$Form->createHtml());//表单数据
			//表单数据初始化s

			$this->assign("edit_index_url",U("AdList/index/s/".base64_encode("position/".$data["zn_ad_position_id"]."/")."/"));//返回首页
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
	
	//验证 广告类型
    public function ajaxCheckIsImage($tnid=0) {
		if( $this->lqgetid ){
			$this->ajaxReturn( array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"] , 'data' => M("ad_position")->where('id='.$this->lqgetid)->getField('zl_type') ) );
		}else{
			return M("ad_position")->where('id='.intval($tnid))->getField('zl_type');
		}
	}

}
?>