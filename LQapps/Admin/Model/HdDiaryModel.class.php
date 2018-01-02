<?php //装修日记 HdDiary 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class HdDiaryModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_title','1,100','标题在1~1000个字符',self::MUST_VALIDATE,'length'),
		array('zc_headimg','require','会员头像必须填写！',self::MUST_VALIDATE),
		array('zc_nickname','require','会员昵称必须填写！',self::MUST_VALIDATE),
		array('zc_style','require','风格必填！',self::MUST_VALIDATE),
		array('zn_area','lqrequire','面积必填！',self::MUST_VALIDATE),
		array('zn_household','lqrequire','户型必填！',self::MUST_VALIDATE),
		array('zc_image', 'lqCheckImage', "日记封面不能为空!", self::MUST_VALIDATE, 'callback'),
	);
	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_member_id', 0, self::MODEL_BOTH),
		array('zc_member_account', '', self::MODEL_BOTH),
		array('zn_hd_order_id', 0, self::MODEL_BOTH),
		array('zn_works_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_designer_id', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_page_view', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_area', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_agrees', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_headimg', 'lqNull', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "HdDiary/index";//列表首页
	}
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_hd_order_id','zn_designer_id');			
	//日记封面不能为空
	protected function lqCheckImage(){
		$data=I("post.LQF",'');
		if(!$data["zc_image"]&&!$data["zn_works_id"]){
			return false;
		}
		return true;
	}  

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$style=F('hd_attribute_1','',COMMON_ARRAY);//风格
		$househol=F('hd_attribute_2','',COMMON_ARRAY);//户型
		$area=F('hd_attribute_3','',COMMON_ARRAY);//面积	
		$order_progress_arr=C("DIARY_STEP");
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        $designer = M("Designer");
        $diary_detail = M("HdDiaryDetail");
        foreach ($list as $lnKey => $laValue) {
            $style = '';
            $attribute_style=F('hd_attribute_1','',COMMON_ARRAY);
            $style = explode(",",$laValue["zc_style"]);
            $style_tag = array();
            foreach ($style as $k => $v) {
                $style_tag[] = $attribute_style[$v];
            }

			$list[$lnKey]['member'] = $laValue['zn_member_id'] == 0 ? '未绑定' : $laValue['zc_member_account'];
			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			$list[$lnKey]['zl_member_apply_label'] = $laValue['zl_member_apply'] == 1 ? '是' : '否';
			if(!$laValue["zc_image"]) $list[$lnKey]['zc_image'] = NO_PICTURE_ADMIN;
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['add'] = U("HdDiaryDetail/add/diary/".$laValue['id']);
			$list[$lnKey]['list'] = U("HdDiaryDetail/index/s/".base64_encode("diary/".$laValue["id"]."/"));
            if($laValue['zn_designer_id']){
                $designer_name = $designer->where("id=".$laValue['zn_designer_id'])->getField("zc_nickname");
            }
            $list[$lnKey]['designer'] = $designer_name ? $designer_name : "未指定" ;
            $list[$lnKey]['step_num'] = $diary_detail->where("zn_hd_diary_id=".$laValue['id'])->count();
            $list[$lnKey]['style'] = implode(",",$style_tag);
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave($back_url);}

    // 插入数据前的回调方法
    protected function _after_insert($data,$options) {	
			$this->_updateAiary($data);
	}
	
    // 更新成功后的回调方法
    protected function _after_update($data,$options) {
		if(ACTION_NAME=='edit'){
			$this->_updateAiary($data);
		}
	}
    // 删除成功后的回调方法
	protected function _after_delete($data,$options) {
		$where=array();
		$where["zn_hd_diary_id"]=$data["id"];
		M("hd_diary_detail")->where($where)->delete();
	}
	
	//将设计师的数据带到主表
    protected function _updateAiary($data) {
		if($data["zn_works_id"]){
			$works = M("designer_works")->where("id=" .intval($data["zn_works_id"]))->find();
			if($works){
				M()->execute("UPDATE __PREFIX__hd_diary SET zn_designer_id='".$works["zn_designer_id"]."' WHERE id=".$data["id"]);
			}
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
	

}

?>
