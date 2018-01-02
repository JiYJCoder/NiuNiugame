<?php //日记进度 HdDiaryDetail 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class HdDiaryDetailModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_hd_diary_id','lqrequire','日记ID不能为空！',self::MUST_VALIDATE),
		array('zc_title','1,100','标题在1~100个字符之间',self::MUST_VALIDATE,'length'),
		array('zc_content','1,65000','内容在1~65000个字符之间',self::MUST_VALIDATE,'length'),
		array('zl_order_progress','lqrequire','进度必须填写！',self::EXISTS_VALIDATE),
		array('zl_order_progress', 'lqCheckProgress', "当前进度内容发布已超过系统内置的次数了！", self::MUST_VALIDATE, 'callback'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_order_progress', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_album', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zd_send_time', 'lqMktime', self::MODEL_BOTH,'function'),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zl_visible');			
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "HdDiary/index";//列表首页
	}
	//当前进度不能超过
	protected function lqCheckProgress(){
		$data=I("post.LQF",'');
		$diary_detail_count = M("hd_diary_detail")->where("zn_hd_diary_id=".$data["zn_hd_diary_id"]." and zl_order_progress = ".$data["zl_order_progress"])->count();
		if($diary_detail_count>C("REQUEST_SESSION")["hd_diary_detail"]){
			return false;
		}
		return true;
	}		

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$order_progress_arr=C("DIARY_STEP");
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_order_progress_label'] = $order_progress_arr[$laValue["zl_order_progress"]];
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){
		if(ACTION_NAME=='add'){
			$back_url=U('add?diary='.intval($_POST["LQF"]["zn_hd_diary_id"]));
		}else{
			$back_url='';
		}
		return $this->lqCommonSave($back_url);
	
	}

    // 插入数据前的回调方法
    protected function _after_insert($data,$options) {	}
	


	//单记录删除
    public function lqDelete($isTree=0) {
        $data["id"] = I("get.tnid",'0','int');
		$lc_check=$this->lqDeletCheck($data,$isTree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		$diary_id = $this->where("id=" .$data["id"] )->getField("zn_hd_diary_id");
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U("HdDiaryDetail/index/s/".base64_encode("diary/".$diary_id."/")."/"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }

	//多记录审批
    public function lqVisibleCheckbox() {
		$data['zl_visible'] = I("get.status",'0','int') == 1 ? 1 : 0;
		$data['zn_mdate'] = NOW_TIME ;
		$data["id"]  = array('in', I("get.tcid",'','lqSafeExplode'));
		$_data = $this->field("zn_hd_diary_id")->where(array("id"=>$data["id"]))->limit(0,1)->select();	
		$diary_id=intval($_data[0]["zn_hd_diary_id"]);
		if( $this->save($data)) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("HdDiaryDetail/index/s/".base64_encode("diary/".$_data[0]["zn_hd_diary_id"]."/")."/"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["fail"]);
		}  	
    }
	
	//多记录删除
    public function lqDeleteCheckbox() {
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		$lc_check=$this->lqDelectCheckboxCheck($data);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		$_data = $this->field("zn_hd_diary_id")->where(array("id"=>$data["id"]))->limit(0,1)->select();	
		$diary_id=intval($_data[0]["zn_hd_diary_id"]);
		if($this->where($data)->delete()){
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U("HdDiaryDetail/index/s/".base64_encode("diary/".$diary_id."/")."/"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }	

}

?>
