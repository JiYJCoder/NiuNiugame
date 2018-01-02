<?php //广告列表 AdList 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AdListModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_sort','lqrequire','排序必须填写！',self::MUST_VALIDATE),
		array('zn_ad_position_id','lqrequire','广告分类必须填写！',self::MUST_VALIDATE),
		array('zl_client_type','lqrequire','客户端类型必须填写！',self::MUST_VALIDATE),
		array('zc_caption','require','广告名称必须填写！',self::MUST_VALIDATE),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_ad_position_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_client_type', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_clicks', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "AdList/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_client_type_label'] = $laValue['zl_client_type'] == 1 ? '通用' : 'WECHAT';
			if(!$laValue["image"]) $list[$lnKey]['image'] = NO_PICTURE_ADMIN;
				$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){
		if(ACTION_NAME=='add'){
			$back_url=U('add?position='.intval($_POST["LQF"]["zn_ad_position_id"]));
		}else{
			$back_url='';
		}
		return $this->lqCommonSave($back_url);
	
	}

	//多记录审批
    public function lqVisibleCheckbox(){
		$ids=I("get.tcid",'','lqSafeExplode');
		$ids_arr = explode(",",$ids);
		if(!$ids_arr) return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		$data['zl_visible'] = I("get.status",'0','int') == 1 ? 1 : 0;
		$data['zn_mdate'] = NOW_TIME ;
		$data["id"]  = array('in',$ids);
		if($this->save($data)) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("AdPosition/index"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		}  	
    }
	//多记录删除
    public function lqDeleteCheckbox() {
		$ids=I("get.tcid",'','lqSafeExplode');
		$ids_arr = explode(",",$ids);		
		$data["id"]  = array('in',$ids);
		$lc_check=$this->lqDelectCheckboxCheck($data);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U("AdPosition/index") );
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }	
	//单记录删除
    public function lqDelete($isTree=0) {
        $data["id"] = I("get.tnid",'0','int');
		$position = $this->where("id=".$data["id"])->getField("zn_ad_position_id");
		$lc_check=$this->lqDeletCheck($data,$isTree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' =>U("AdList/index/position/".$position));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
}
?>
