<?php //操作行为 AdminAction 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AdminActionModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_action_key','1,30','操作行为在1~30个字符间',self::MUST_VALIDATE,'length'),
		array('zc_action_key', '', "操作行为已被占用", self::MUST_VALIDATE, 'unique'),
		array('zc_caption','1,50','操作名称在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zc_caption', '', "操作名称已被占用", self::MUST_VALIDATE, 'unique'),
		array('zn_sort','0,255','排序在0~255之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_check_action', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "AdminAction/index";//列表首页
	}
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_check_action_label'] = $laValue['zl_check_action'] == 1 ? '是' : '非';
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
	//缓存数据
	public function lqCacheData($tnReturn=0){
		$la_admin_action =$this->where('zl_check_action=1 and zl_visible=1')->order('zn_sort,id desc')->field("`zc_action_key`,`zc_caption`")->select();
		$array=lq_return_array_one($la_admin_action,"zc_action_key","zc_caption");
		F('admin_action',$array,COMMON_ARRAY);
		if($tnReturn) return $array;
	}

    //更改-是非首页 
    public function setProperty() {
		$lcop=I("get.tcop",'is_index');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($lcop=='is_check'){
			$data['zl_check_action'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_check_action'], "txt" => $data['zl_check_action'] == 1 ? "是" : "非" ) ;			
		}else{
			return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
			$this->lqCacheData();
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	
	
}

?>
