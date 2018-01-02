<?php //通行ip库 AdminAllowIp 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AdminAllowIpModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_ip','require','操作行为必须填写！'),
		array('zc_ip', '', "操作行为已被占用", self::MUST_VALIDATE, 'unique'),
		array('zc_ip', 'isIp',"不是一个有效的IP地址。", self::MUST_VALIDATE, 'function'), //用户名规则
		array('zc_caption','require','操作名称必须填写！',self::MUST_VALIDATE),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "AdminAllowIp/index";//列表首页
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
	
	//缓存数据
	public function lqCacheData($return=0){
		$la_admin_allow_ip=array();
		$list = $this->field("`zc_ip`")->order('zn_sort,id desc')->where("zl_visible=1")->select();
		foreach ($list as $lnKey => $laValue){
			$la_admin_allow_ip[ip2long($laValue["zc_ip"])]=$laValue["zc_ip"];
		}
		F('admin_allow_ip',$la_admin_allow_ip,COMMON_ARRAY);
		if($return) return $array;
	}
	
}

?>
