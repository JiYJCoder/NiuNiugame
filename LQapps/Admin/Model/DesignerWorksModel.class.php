<?php //设计师作品 DesignerWorks 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class DesignerWorksModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_designer_id','lqrequire','设计师必须填写！',self::MUST_VALIDATE),
		array('zc_caption','require','作品名称必须填写！',self::MUST_VALIDATE),
		array('zn_style','lqrequire','风格必须填写！',self::MUST_VALIDATE),
		array('zn_household','lqrequire','户型必须填写！',self::MUST_VALIDATE),
		array('zn_area','lqrequire','面积必须填写！',self::MUST_VALIDATE),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_is_index', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_work_release', 'lqMktime', self::MODEL_BOTH,'function'),
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_designer_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_clicks', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_agrees', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_works_photo', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_works_photos', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_thumb', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_introduction', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "DesignerWorks/index";//列表首页
	}
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zc_thumb','zn_thumb_width','zn_thumb_height');			

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$style=F('hd_attribute_1','',COMMON_ARRAY);//风格
		$househol=F('hd_attribute_2','',COMMON_ARRAY);//户型
		$area=F('hd_attribute_3','',COMMON_ARRAY);//面积		
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        $designer = M("Designer");
        foreach ($list as $lnKey => $laValue) {
			//图册
			if($laValue["zc_works_photos"]){
				$list[$lnKey]['album'] = count(explode(",",$laValue["zc_works_photos"]));
			}else{
				$list[$lnKey]['album'] = 0;
			}
            $list[$lnKey]['designer'] = $designer->where("id=".$laValue['zn_designer_id'])->getField("zc_nickname");
			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			if(!$laValue["zc_thumb"]) $list[$lnKey]['zc_thumb'] = NO_PICTURE_ADMIN;
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['attribute'] =$style[$laValue['zn_style']]."/".$househol[$laValue['zn_household']]."/".$area[$laValue['zn_area']];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){
		if(ACTION_NAME=='add'){
			$back_url=U('add?designer='.intval($_POST["LQF"]["zn_designer_id"]));
		}else{
			$back_url='';
		}
		return $this->lqCommonSave($back_url);
	
	}

    // 插入数据前的回调方法
    protected function _after_insert($data,$options) {	
	$this->_lqthumb($data,$options);
	}
	
    // 更新成功后的回调方法
    protected function _after_update($data,$options) {
	$this->_lqthumb($data,$options);
	}
	
	//缩略图处理
	protected function _lqthumb($data,$options){
		$image_data=array();
		if($data["zc_works_photo"]) $image_data[]=array("key"=>'works',"path"=>$data["zc_works_photo"]);
		$thumb_list=lq_thumb_deal($image_data,$data["id"],'works');
		$thumb_path=$thumb_album=$updatesql='';
		if($thumb_list){
			foreach($thumb_list as $k => $v){
				if($k==0){
					$thumb_path=$v;
				}else if($k==1){
					$thumb_album=$v;
				}else{
					$thumb_album.=",".$v;
				}
			}	
		}
		$updatesql='';
		if($thumb_path!=''){
			$thumb_image = new \Think\Image();
			$thumb_width=$thumb_image->open(WEB_ROOT.$thumb_path)->width();
			$thumb_height=$thumb_image->open(WEB_ROOT.$thumb_path)->height();
			$updatesql=" zc_thumb='".$thumb_path."',zn_thumb_width='".$thumb_width."',zn_thumb_height='".$thumb_height."' ";
		}
		if($updatesql!=''){
		M()->execute("UPDATE __PREFIX__designer_works SET $updatesql WHERE id=".$data["id"]);
		}		
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
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["success"], 'url' => U("Designer/index"));
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
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U("Designer/index") );
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }	
	//单记录删除
    public function lqDelete($isTree=0) {
        $data["id"] = I("get.tnid",'0','int');
		$designer = $this->where("id=".$data["id"])->getField("zn_designer_id");
		$lc_check=$this->lqDeletCheck($data,$isTree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' =>U("DesignerWorks/index/designer/".$designer));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	
    //更改-是非首页 
    public function setProperty() {
		$lcop=I("get.tcop",'is_index');
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		if($lcop=='is_index'){
			$data['zl_is_index'] = I("get.vlaue",'0','int') == 1 ? 0 : 1;
			$op_data= array("status" => $data['zl_is_index'], "txt" => $data['zl_is_index'] == 1 ? "是首页" : "非首页" ) ;			
		}else{
			return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' =>$op_data );
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	
	
    //更改  Sort 列表
    public function lqSortList() {
        $lcsortid = addslashes(I("post.tcsortid",'',''));
		$lnresetzero = I("post.resetzero",'0','int');
		$lnok=0;
		$laSort=explode (",",$lcsortid);
		$designer = $this->where("id=".intval($laSort[0]))->getField("zn_designer_id");
		if($laSort&&$lcsortid){
			 foreach($laSort as $lnKey=>$lcValue){
				unset($data);
				$lnok=1;
				$data["id"] = $lcValue;
				if($lnresetzero==0){
					$data["zn_sort"] = $lnKey+1;
				}else{
					$data["zn_sort"] = C("COM_SORT_NUM");
				}
				$data["zn_mdate"] = NOW_TIME;
				$data= array_merge($data,array('zn_mdate'=>NOW_TIME));
				$this->save($data);
			 }
		}
        if ($lnok==1) {
			$this->lqAdminLog(array('in',$lcsortid));//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'url' =>U("DesignerWorks/index/designer/".$designer));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }		
}

?>
