<?php //设计师作品 DesignerWorks 数据处理，数据回调
namespace Ucenter\Model;
use Think\Model;

class WorksModel extends PublicModel {
	protected $tableName        =   'designer_works';	
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','require','作品名称必须填写！',self::MUST_VALIDATE),
		array('zn_style','lqrequire','风格必须填写！',self::EXISTS_VALIDATE),
		array('zn_household','lqrequire','户型必须填写！',self::EXISTS_VALIDATE),
		array('zn_area','lqrequire','面积必须填写！',self::EXISTS_VALIDATE),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_is_index', 0, self::MODEL_INSERT),
		array('zn_sort', 0, self::MODEL_INSERT),
		array('zn_clicks', 0, self::MODEL_INSERT),
		array('zn_agrees', 0, self::MODEL_INSERT),
		array('zc_works_photo', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_works_photos', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_thumb', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_introduction', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_work_release', NOW_TIME, self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "Works/index";//列表首页
	}
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_member_id','zn_designer_id','zc_designer_nickname','zl_good_index','zl_is_index','zn_clicks','zn_agrees','zn_sort','zl_visible','zn_cdate','zc_thumb','zn_thumb_width','zn_thumb_height');
	
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$style=F('hd_attribute_1','',COMMON_ARRAY);//风格
		$househol=F('hd_attribute_2','',COMMON_ARRAY);//户型
		$area=F('hd_attribute_3','',COMMON_ARRAY);//面积		
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			//图册
			if($laValue["zc_works_photos"]){
				$list[$lnKey]['album'] = count(explode(",",$laValue["zc_works_photos"]));
			}else{
				$list[$lnKey]['album'] = 0;
			}
			$list[$lnKey]['edit_url'] = U("ucenter/works/edit?tnid=".$laValue["id"]);
			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			if(!$laValue["zc_thumb"]) $list[$lnKey]['zc_thumb'] = NO_PICTURE_ADMIN;
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['attribute'] =$style[$laValue['zn_style']]."/".$househol[$laValue['zn_household']]."/".$area[$laValue['zn_area']];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data) {
		if(ACTION_NAME=='add'||ACTION_NAME=='edit'){
			$data["zn_member_id"] = session('member_auth')["id"];
			$designer = M("designer")->where("zn_member_id=".session('member_auth')["id"])->find();
			if($designer){
			$data["zn_designer_id"] = $designer["id"];
			$data["zc_designer_nickname"] = $designer["zc_nickname"];	
			}
			return $data;
		}
	}
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

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
	
	//单记录删除
    public function lqDelete($isTree=0) {
		$id = I("get.tnid",'0','int');
		if($id==0) return array('status' => 0, 'msg' => C("ALERT_ARRAY")["illegal_operation"] );		
		$lc_check = $this->where("id=".$id)->getField("zl_is_index");
		if($lc_check==1) return array('status' => 0, 'msg' => '系统提示：首页推荐不能删除,请联系平台管理员！' );		
		if ($this->where("zn_member_id = ".session('member_auth')["id"]." and id = ".$id)->delete()) {
			    $this->lqMemberLog($id);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	//多记录删除
    public function lqDeleteCheckbox() {
		$data=array();
		$data["id"]  = array('in',I("get.tcid",'','lqSafeExplode'));
		$data['zn_member_id'] = session('member_auth')["id"];
		$data['zl_is_index'] = 1 ;
        $count = $this->where($sqlwhere_parameter)->count();
		if($count) return array('status' => 0, 'msg' => '首页推荐不能删除,请联系平台管理员！' );		
		unset($data['zl_is_index']);
		if ($this->where($data)->delete()) {
			    $this->lqMemberLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }		

}

?>
