<?php //文章分类 ArticleCat 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件

class ArticleCatController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息'),
		//通用信息
		'1'=>array(
		array('buttonDialog', 'zn_fid', "父级分类",1,'{"required":"0","dataLength":"","readonly":1,"disabled":0,"controller":"ArticleCat","type":"tree","checkbox":"0"}'),
		array('text', 'zc_caption', "分类-标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_caption_alias', "分类-副标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_is_index', "首页分类",1,'{"class":"","required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
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
		$page_title=array('no'=>L("LIST_NO")."/".L("LIST_ID"),'zc_caption'=>'节点结构','is_index'=>'首页推送','zn_sort'=>L("LIST_SOTR"),'status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS")."(采集/复制/转移/编辑/审核/删除)");
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
    public function opDelete($is_tree=1){
            $this->ajaxReturn($this->C_D->lqDelete($is_tree));		
	}
	//更改  zlvisible 
    public function opVisible($is_tree=1){
            $this->ajaxReturn($this->C_D->lqVisible($is_tree));		
	}



	//文章分类
    public function tree() {
		$url_field=I("get.field",'','htmlspecialchars');
		$url_checkbox=I("get.checkbox",'0','int');
		$this->assign("url_field",$url_field);//打开的窗口回调处理父窗体的字段
		$this->assign("url_checkbox",$url_checkbox);//多选标识
		
		$lcdisplay='Public/common-tree';
		$list=F('article_cat','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//文章分类
		$this->assign("tree_label", "文章分类");//文章分类
        $this->display($lcdisplay);
    }


	// 采集页面
    public function collection() {
			//引用采集规则
			$collector_rule_list = M("collector_rule")->field("id,zc_caption")->where("1")->order("zn_sort,id DESC")->select();	
			$this->assign("collector_rule_str", lqCreatOption(lq_return_array_one($collector_rule_list,'id','zc_caption'), I("get.crid",'0','int') ,"选择采集规则"));//文件类型
			//文章分类
			$this->assign("cat_id_str", lqCreatOption(lq_return_array_one(F('article_cat','',COMMON_ARRAY),'id','fullname'),$this->lqgetid,"选择文章分类"));//文件类型
			$lcdisplay='collection';
            $this->display($lcdisplay);
    }
	
	//【选择器数组】说明：格式array("名称"=>array("选择器","类型"),.......),【类型】说明：值 "text" ,"html" ,"属性" 
	private function returnFields($rule_data,$field='list') {
		if($field=='list'){
			$fields=$rule_data["zc_list_fields"];
			$reg=$rule_data["zc_list_reg"];
		}else{
			$fields=$rule_data["zc_content_fields"];
			$reg=$rule_data["zc_content_reg"];			
		}
		if( empty($fields) ) return array();
		$fields=split(",",$fields);
		$reg=split(",",$reg);
		$pattern=array();
		foreach($fields as $k=>$v){
			$lcreg=$reg[$k] ? $reg[$k] :'html';
					if($v=='title'){
						$pattern[$v]=array($rule_data["zc_title_rule"],$lcreg);
					}else if($v=='href'){
						$pattern[$v]=array($rule_data["zc_href_rule"],$lcreg);
					}else if($v=='author'){
						$pattern[$v]=array($rule_data["zc_author_rule"],$lcreg);				
					}else if($v=='source'){
						$pattern[$v]=array($rule_data["zc_source_rule"],$lcreg);	
					}else if($v=='time'){
						$pattern[$v]=array($rule_data["zc_time_rule"],$lcreg);					
					}else if($v=='icon'){
						$pattern[$v]=array($rule_data["zc_icon_rule"],$lcreg);
					}else if($v=='summary'){
						$pattern[$v]=array($rule_data["zc_summary_rule"],$lcreg);	
					}else if($v=='content'){
						$pattern[$v]=array($rule_data["zc_content_rule"],$lcreg);							
					}
		}
		
		return $pattern;
		
	}

	// 采集页面
    public function queryList() {
		$lncrid=I("get.crid",'0','int');
		$lnacid=I("get.acid",'0','int');
		
		//采集规则数据
		$rule_data = M("collector_rule")->where("id=" .$lncrid)->find();
		if(!$rule_data){
			$this->ajaxReturn( array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"] , 'data' => '' ) );
		}
		$collection_url=parse_url( $rule_data["zc_collection_url"] );
		$http=$collection_url["scheme"]."://".$collection_url["host"];
		$pattern_list=$this->returnFields($rule_data,'list');
		
		//接入QueryList 采集类
		import('Vendor.phpQuery.QueryList');
		$qy_list_obj = new \QueryList($rule_data["zc_collection_url"], $pattern_list, '', '', 'utf-8');
		$list_res = $qy_list_obj->jsonArr;
		$max_k=$rule_data["zn_collection_quantity"];
		if($max_k==0) $max_k=20;
		$article_temp_array=array();
		foreach($list_res as $k=>$v){
			if($k<$max_k){
				if( !isUrl($v["href"]) ) $v["href"]=$http.$v["href"];
				$time=lqMktime( $v["time"] );
				$view=array();
				$pattern_content=$this->returnFields($rule_data,'zc_content_fields');				
				$qy_content_obj = new \QueryList($v["href"], $pattern_content, '', '', 'utf-8');
				$content_res = $qy_content_obj->jsonArr;
				$view = $qy_content_obj->jsonArr[0];
				$article_temp_array[]=array_merge($v,$view);//键入内容数据
			}
		}

		//入临时表
		$lc_article_list='';
		if($article_temp_array){
			$M_ARTICLE_TEMP=M("article_temp");
			$M_ARTICLE_TEMP->where("zn_admin_id=".$this->login_admin_info["id"]." and zn_cat_id=".$lnacid)->delete();
			foreach($article_temp_array as $key=>$value){
				$lnindex++;
				$article_temp_data=array();
				$article_temp_data["zn_admin_id"]=$this->login_admin_info["id"];
				$article_temp_data["zn_cat_id"]=$lnacid;
				$article_temp_data["zc_title"]=$value["title"] ? $value["title"] : '';
				$article_temp_data["zc_source"]=$value["source"] ? $value["source"] : '';
				$article_temp_data["zc_source_url"]=$value["href"] ? $value["href"] : '';
				$article_temp_data["zc_author"]=$value["author"] ? $value["author"] : '';
				$article_temp_data["zd_send_time"]=$value["time"]>28800 ? $value["time"] : NOW_TIME;
				$article_temp_data["zc_image"]=$value["icon"] ? $value["icon"] : '';
				$article_temp_data["zc_summary"]=$value["summary"] ? $value["summary"] : '';
				$article_temp_data["zc_content"]=$value["content"] ? preg_replace("/<A.*>|<\/a>/isU",'',$value["content"]) : '';
				if( $M_ARTICLE_TEMP->add($article_temp_data) ){
					$lc_article_list.=$lnindex.'、'.$value["title"]."\n";
				}
			}
			$this->ajaxReturn( array('status' => 1, 'msg' => "采集到".$lnindex."条数据" , 'data' => $lc_article_list ) );
		}
			$this->ajaxReturn( array('status' => 1, 'msg' => "采集到0条数据" , 'data' => '' ) );
    }
	
	// 采集临时数据 移至 文章表
    public function opCollection() {
		$lnacid=I("get.acid",'0','int');
		$llcheck=I("get.check",'0','int');
		
		//采集临时数据
		$article_temp_list = M("article_temp")->field("*")->where("zn_admin_id=".$this->login_admin_info["id"]." and zn_cat_id =".$lnacid)->order("zd_send_time DESC")->select();	
		
		if($article_temp_list){
			foreach($article_temp_list as $key=>$value){
				$lnindex++;
				//插入数据表 s
				$article_data=array();
				$article_data["zn_cat_id"]=$value["zn_cat_id"];
				$article_data["zc_title"]=$value["zc_title"];
				$article_data["zc_seo_title"]=$value["zc_title"];
				$article_data["zc_source"]=$value["zc_source"];
				$article_data["zc_source_url"]=$value["zc_source_url"];
				$article_data["zc_author"]=$value["zc_author"];
				$article_data["zd_send_time"]=$value["zd_send_time"];
				$article_data["zc_image"]=$value["zc_image"];
				$article_data["zc_summary"]=$value["zc_summary"];
				$article_data["zc_content"]=$value["zc_content"];
				$article_data["zc_relation_ids"]='';
				$article_data["zn_sort"]= C("COM_SORT_NUM");
				$article_data["zl_visible"]= $llcheck ? 0 : 1;
				$article_data["zn_cdate"]= NOW_TIME;
				$article_data["zn_mdate"]= NOW_TIME;
				M("article")->add($article_data);
				//插入数据表 e
			}
			M("article_temp")->where("zn_admin_id=".$this->login_admin_info["id"]." and zn_cat_id=".$lnacid)->delete();
			$this->ajaxReturn( array('status' => 1, 'msg' => "成功导入".$lnindex."条数据" , 'data' => $lc_article_list ) );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => "数据导入失败" , 'data' => '' ) );
		}
    }
	
	//文章分类操作列表
    public function treeWin() {
		$lcos=I("get.op",'','htmlspecialchars');
		//表单数据初始化s
		$FORM_DATA=array();
		$FORM_DATA["id"] = $this->lqgetid;
		$FORM_DATA["op"] = $lcos;

		if($lcos=='copy'){
			$FORM_DATA["tree_label"] = '分类下的文章复制';
			$FORM_DATA["td_label"] = '粘贴到';
			
		}else{
			$FORM_DATA["tree_label"] = '分类下的文章转移';
			$FORM_DATA["td_label"] = '转移到';
			
		}
		$this->assign("fdata", $FORM_DATA);
		
		
		$list=F('article_cat','',COMMON_ARRAY);
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]["id"]=$laValue["id"];
			$list[$lnKey]["fid"]=$laValue["zn_fid"];
			$list[$lnKey]["label"]=$laValue["zc_caption"];
		}
		$this->assign("list", $list);//文章分类
		if( !$FORM_DATA["id"] | !$FORM_DATA["op"] ){
			$this->assign("list", array());//文章分类
		}			
		$lcdisplay='treeWin';
        $this->display($lcdisplay);
    }
	
	//文章分类操作列表 - 操作
    public function opProperty() {
		$lcos=I("get.op",'','htmlspecialchars');
		$oid=I("get.oid",'0','int');
		$article = M("article")->where("zn_cat_id=".$oid)->select();
		if(!$article) $this->ajaxReturn( array('status' => 0, 'msg' => "无数据操作" , 'data' => '' ) );
		if($lcos=='copy'){
			foreach ($article as $lnKey => $laValue) {
				$DATA_ARRAY=array();
				foreach ($laValue as $k => $v) {
						$DATA_ARRAY[$k]=$v;
				}	
				unset($DATA_ARRAY["id"]);
				$DATA_ARRAY["zn_cat_id"]= $this->lqgetid;					
				M("article")->add($DATA_ARRAY);
			}
			$msg="成功复制".count($article)."条数据";
		}else if($lcos=='transform'){
			M()->execute( "UPDATE __PREFIX__article SET zn_cat_id = ".$this->lqgetid." WHERE zn_cat_id = ".$oid );
			$msg="成功转移".count($article)."条数据";
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => "操作失败" , 'data' => '' ) );
		}
		
		$this->ajaxReturn( array('status' => 1, 'msg' => $msg , 'data' => '' ) );
		
	}	
	
	
	//获取文章总数
    public function ajaxGetCount() {
		$tree = new \LQLibs\Util\Category('article_cat', array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($this->lqgetid,10,'zl_visible=1');
		if (ereg("^[0-9]+$", $child_ids )){
		$sqlwhere_parameter="zn_cat_id = ".intval($child_ids);
		}else{
		$sqlwhere_parameter="zn_cat_id in (".$child_ids.") ";
		}	
		$count = M("article")->where($sqlwhere_parameter)->count();
		$this->ajaxReturn( array('status' => 1, 'msg' => "获取成功" , 'data' => $count ) );
	}
	
}
?>