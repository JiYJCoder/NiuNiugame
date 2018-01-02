<?php //文章采集规则管理 CollectorRule 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class CollectorRuleModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_sort','lqrequire','排序必须填写！'),
		array('zc_caption','require','规则名称必须填写！'),
		array('zc_collection_url','url','请正确输入采集地址！'),
		array('zc_list_fields','require','采集列表页字段必须填写！'),
		array('zc_list_fields','check_list_fields','请输入title,href,author,source,time,icon,summary！',3,'callback'),
		array('zc_list_reg','require','列表选择器必须填写！'),
		array('zc_list_reg','check_list_reg','列表选择器必须与采集列表页字段数目一致！',3,'callback'),
		array('zc_content_fields','require','采集内容页字段必须填写！'),
		array('zc_content_fields','check_content_fields','请输入title,href,author,source,time,icon,summary,content！',3,'callback'),
		array('zc_content_reg','require','内容选择器必须填写！'),
		array('zc_content_reg','check_content_reg','内容选择器必须与采集内容页字段数目一致！',3,'callback'),
		array('zc_href_rule','require','链接必须填写！'),
		array('zc_title_rule','require','标题规则必须填写！'),
		array('zc_content_rule','require','内容规则必须填写！'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_collection_quantity', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_list_fields', 'str_replace_tag', self::MODEL_BOTH,'callback'),
		array('zc_list_reg', 'str_replace_tag', self::MODEL_BOTH,'callback'),
		array('zc_content_fields', 'str_replace_tag', self::MODEL_BOTH,'callback'),
		array('zc_content_reg', 'str_replace_tag', self::MODEL_BOTH,'callback'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "CollectorRule/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
	//确保关键词,有效性
	public function str_replace_tag($value){return str_replace("，",",",$value);}

	//列表选择器必须与采集列表页字段数目一致
	public function check_list_reg($value){
		$list_reg_len= count( split(",",$value) );
		$list_fields_len= count( split(",",$_POST["LQF"]["zc_list_fields"]) );
		if($list_reg_len!=$list_fields_len) return false;
		return true;
	}

	//内容选择器必须与采集内容页字段数目一致
	public function check_content_reg($value){
		$content_reg_len= count( split(",",$value) );
		$content_fields_len= count( split(",",$_POST["LQF"]["zc_content_fields"]) );
		if($content_reg_len!=$content_fields_len) return false;
		return true;		
	}
	
	//检测列表字段的有效性
	public function check_list_fields($value){
		$check_array=array('title','href','author','source','time','icon','summary');
		$value_array= split(",",$value);
		foreach($value_array as $k=>$v){
			if( !in_array($v,$check_array) ) return false;
		}
		return true;
	}
	
	//检测内容字段的有效性
	public function check_content_fields($value){
		$check_array=array('title','href','author','source','time','summary','content');
		$value_array= split(",",$value);
		foreach($value_array as $k=>$v){
			if( !in_array($v,$check_array) ) return false;
		}
		return true;		
	}	
	
}

?>
