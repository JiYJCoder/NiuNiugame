<?php //产品管理 Product 页面操作 
namespace Admin\Controller;
use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use LQLibs\Util\Category as Category;//树状分类

class ProductController extends PublicController{
	public $myTable;
	protected $myForm = array(
		//标题
		'tab_title'=>array(1=>'基本信息',2=>'产品内容',3=>'产品属性',4=>'产品SEO设置'),
		//通用信息
		'1'=>array(
		array('buttonDialog', 'zn_cat_id', "产品分类",1,'{"required":"1","dataLength":"","readonly":1,"disabled":0,"controller":"ProductCat","type":"tree","checkbox":"0"}'),
		array('select', 'zn_product_brand_id', "产品品牌",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"please":"请选择品牌"}'),
		array('text', 'zc_title', "产品标题",1,'{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0,"maxl":120}'),
		array('image', 'zc_image', "产品图片",1,'{"type":"products","allowOpen":1}'),
		array('multiImage', 'zc_album', "产品图册",1,'{"type":"products","imageUploadLimit":10,"allowOpen":1,"returnData":"paths"}'),
		array('text', 'zn_sort', "排序",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),
		//内容
		'2'=>array(
		array('textarea', 'zc_summary', "产品摘要",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('editor', 'zc_content', "产品内容",1,'{"required":"0","model":"1","ext":"LQF","width":"100%","height":"300px"}'),		
		),
		//产品属性
		'3'=>array(
		array('text', 'zf_shop_price', "商城价格",1,'{"required":"1","dataType":"float","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zf_stock_price', "进货价格",1,'{"required":"1","dataType":"float","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zf_lowest_price', "最低价格",1,'{"required":"1","dataType":"float","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zf_speciality_price', "专买店价格",1,'{"required":"1","dataType":"float","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('text', 'zf_jd_price', "京东价格",1,'{"required":"1","dataType":"float","dataLength":"","readonly":0,"disabled":0,"maxl":10}'),
		array('radio', 'zl_is_index', "推荐首页",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_is_hot', "推荐热销",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('radio', 'zl_is_good', "推荐精品",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_page_view', "访问量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zn_agrees', "点赞数量",1,'{"required":"1","dataType":"number","dataLength":"","readonly":0,"disabled":0}'),
		),		
		//产品SEO设置
		'4'=>array(
		array('text', 'zc_seo_title', "title",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),	
		array('text', 'zc_seo_keywords', "keywords",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		array('text', 'zc_seo_description', "description",1,'{"required":"0","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
		),	
	);
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->myTable = M($this->pcTable);//主表实例化
	}
    
	//列表页
    public function index() {
		//列表表单初始化****start
		$page_parameter["s"]=$this->getSafeData('s');
		$this->reSearchPara($page_parameter["s"]);//反回搜索数
		$search_content_array=array(
			'pagesize'=>urldecode(I('get.pagesize','0','int')),
			'fkeyword'=>trim(urldecode(I('get.fkeyword',$this->keywordDefault))),
			'keymode'=>urldecode(I('get.keymode','1','int')),
			'cat_id'=>I('get.cat_id','','int'),
			'product_brand_id'=>I('get.product_brand_id','','int'),
			'recommend'=>I('get.recommend','','int'),
		);
		$this->assign("search_content",$search_content_array);//搜索表单赋值
		$catList=F('product_cat','',COMMON_ARRAY);
		foreach ($catList as $k => $v) {
			if($v["zl_visible"]==0) unset($catList[$k]);
		}
		$this->assign("zn_cat_id_str", lqCreatOption(lq_return_array_one($catList,'id','fullname'),$search_content_array["cat_id"],"选择分类"));//文件类型
		$recommend_array=array(
			1=>'推荐首页',
			2=>'推荐热销',
			3=>'推荐精品',
		);		
		$this->assign("recommend_str",lqCreatOption($recommend_array,$search_content_array["recommend"],"请选择推荐"));
		$this->assign("product_brand_str",lqCreatOption(F('product_brand','',COMMON_ARRAY),$search_content_array["product_brand_id"],"请选择品牌"));
		
		//sql合并
		$sqlwhere_parameter=" 1 ";//sql条件
		if($search_content_array["fkeyword"]&&$search_content_array["fkeyword"]!=$this->keywordDefault){
			if($search_content_array["keymode"]==1){
			$sqlwhere_parameter.=" and zc_title ='".$search_content_array["fkeyword"]."' ";
			}else{
			$sqlwhere_parameter.=" and (zc_title like'".$search_content_array["fkeyword"]."%' or zc_seo_title like'".$search_content_array["fkeyword"]."%') ";
			}	
		}
		if($search_content_array["cat_id"]){
			$tree = new Category('product_cat', array('id', 'zn_fid', 'zc_caption'));
			$child_ids = $tree->get_child($search_content_array["cat_id"],10,'zl_visible=1');
			if (ereg("^[0-9]+$", $child_ids )){
				$sqlwhere_parameter.=" and zn_cat_id = ".intval($child_ids);
			}else{
				$sqlwhere_parameter.=" and zn_cat_id in (".$child_ids.") ";
			}
		}
		if($search_content_array["product_brand_id"]){
				$sqlwhere_parameter.=" and zn_product_brand_id = ".$search_content_array["product_brand_id"];
		}		
		if($search_content_array["recommend"]){
			if ( $search_content_array["recommend"]==1 ){
				$sqlwhere_parameter.=" and zl_is_index =1 ";
			}else{
				$sqlwhere_parameter.="";
			}
		}
		
		//首页设置
		$page_title=array('checkbox'=>'checkbox','no'=>L("LIST_NO"),'zc_image'=>'产品图片','zn_cat_id'=>'产品分类','zn_product_brand_id'=>'产品品牌','zc_title'=>'产品标题','zn_sort'=>L("LIST_SOTR"),'zn_spe'=>'推荐','status'=>L("LIST_STAYUS"),'os'=>L("LIST_OS"));
		$page_config = array(
				'field'=>"`id`,`zn_cat_id`,`zn_product_brand_id`,`zc_thumb`,`zc_title`,`zl_is_index`,`zl_is_hot`,`zl_is_good`,`zl_visible`,`zf_shop_price`,`zn_sort`",
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
			$lcdisplay='Public/common-edit';//引用模板
			
			//表单数据初始化s
			$form_array=lq_post_memory_data();//获得上次表单的记忆数据
			$form_array["id"]='';
			$form_array["zf_shop_price"]=$form_array["zf_stock_price"]=$form_array["zf_lowest_price"]=$form_array["zf_speciality_price"]=$form_array["zf_jd_price"]='0.00';
			$form_array["zn_product_brand_id_data"]=F('product_brand','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$form_array["zl_is_hot_data"]=C('YESNO_STATUS');
			$form_array["zl_is_good_data"]=C('YESNO_STATUS');
			$form_array["zn_sort"]=C("COM_SORT_NUM");
			$form_array["zn_page_view"]=$form_array["zn_agrees"]=0;
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
			D('Api/Product')->getProductById(intval($_POST["LQF"]["id"]),1);
            $this->ajaxReturn($returnData);
        } else {
			$lcdisplay='Public/common-edit';

			//读取数据
			$data = $this->myTable->where("id=" .$this->lqgetid)->find();
			if(!$data) {  $this->error(C("ALERT_ARRAY")["recordNull"]);  }//无记录
			$this->pagePrevNext($this->myTable,"id","zc_title");//上下页
							
			
			//表单数据初始化s
			$form_array=array();
			//操作时间
			$form_array["os_record_time"]=$this->osRecordTime($data);
			foreach ($data as $lnKey => $laValue) {
				$form_array[$lnKey]=$laValue;
			}
			$form_array["zn_cat_id_label"]=lq_return_array_one(F('product_cat','',COMMON_ARRAY),'id','zc_caption')[$data["zn_cat_id"]];
			$form_array["zn_product_brand_id_data"]=F('product_brand','',COMMON_ARRAY);
			$form_array["zl_is_index_data"]=C('YESNO_STATUS');
			$form_array["zl_is_hot_data"]=C('YESNO_STATUS');
			$form_array["zl_is_good_data"]=C('YESNO_STATUS');
			$form_array["zl_is_good_data"]=C('YESNO_STATUS');
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
	

}
?>