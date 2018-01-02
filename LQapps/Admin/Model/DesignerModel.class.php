<?php //设计师信息表 Designer 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use Member\Api\MemberApi as MemberApi;

class DesignerModel extends PublicModel {
	protected $model_member;
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_join_year','4','入行年份4个字符',self::MUST_VALIDATE,'length'),
		array('zc_personality_sign','1,250','个性签名在1~250个字符',self::MUST_VALIDATE,'length'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_subscribe', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_is_index', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),
		array('zl_level', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_resume', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_personality_sign', 'lqNull', self::MODEL_BOTH,'function'),
		array('zc_style_tag', 'lqNull', self::MODEL_BOTH,'function'),
	);	
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_member_id','zc_member_account','zl_good_index');//,'zl_level'
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_nickname";//数据表显示标题字段
		$this->pc_index_list =  "Designer/index";//列表首页
		$this->model_member = new MemberApi;
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
		foreach ($list as $lnKey => $laValue) {
			if(ACTION_NAME=='window'){
			$list[$lnKey]['headimg'] = $this->model_member->apiGetFieldByID($laValue["zn_member_id"],'zc_headimg');
			}

            $style = '';
            $attribute_style=F('hd_attribute_1','',COMMON_ARRAY);
            $style = explode(",",$laValue["zc_style_tag"]);
            $style_tag = array();
            foreach ($style as $k => $v) {
                $style_tag[] = $attribute_style[$v];
            }
            $list[$lnKey]['style_tag'] = $laValue["zc_style_tag"] ? implode(",",$style_tag) : "个人风格未定";

			$list[$lnKey]['zl_is_index_label'] = $laValue['zl_is_index'] == 1 ? '是首页' : '非首页';
			$list[$lnKey]['zl_level_label'] = C("DESIGNER_LEVEL")[$laValue["zl_level"]];
			$list[$lnKey]['zl_good_index_label'] = C("INDEX")[$laValue["zl_good_index"]];
			$list[$lnKey]['count'] =  M("designer_works")->where("zn_designer_id=".$laValue["id"])->count();
            $list[$lnKey]['count_visible'] =  M("designer_works")->where("zl_visible = 1 and zn_designer_id=".$laValue["id"])->count();
            $list[$lnKey]['application_num'] = M("hd_order")->where("zn_designer_id=".$laValue["id"])->count();
			$list[$lnKey]['works_add'] = U("DesignerWorks/add/designer/".$laValue['id']);
			$list[$lnKey]['works_list'] = U("DesignerWorks/index/s/".base64_encode("designer/".$laValue["id"]."/"));
            $list[$lnKey]['is_visible'] =  M("member")->where("id=".$laValue["id"])->getField('zl_visible') == 1 ? '正常' : '锁定';
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }

	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}


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
