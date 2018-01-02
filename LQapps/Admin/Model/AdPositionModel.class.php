<?php //图册链接系统 AdPosition 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class AdPositionModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,50','位置名称在1~50个字符间',self::MUST_VALIDATE,'length'),
		array('zn_sort','0,255','排序在0~255之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zl_type', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_image_width', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_image_height', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_max_ad', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		

    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "AdPosition/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$list[$lnKey]['ad_add'] = U("adList/add/position/".$laValue['id']);
			$list[$lnKey]['ad_list'] = U("adList/index/s/".base64_encode("position/".$laValue["id"]."/"));
			$list[$lnKey]['count'] =  M("ad_list")->where("zn_ad_position_id=".$laValue["id"])->count();
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
	//缓存列表数据
    public function lqCacheData($tnReturn=0){
		$array=LQ_Return_array_one($this->field("id,zc_caption")->where(' zl_visible=1 ')->order('`id` DESC')->select(),'id','zc_caption');
		F('ad_position',$array,COMMON_ARRAY);
		if($tnReturn) return $array;
	}

    //更改  set_image_width_height 
    public function setImageWH() {
		$data=array();
        $data["id"] = I("post.tnid",'0','int');
		if( I("post.tcop",'set_width','')=='set_width' ){
			$data["zn_image_width"] = I("post.value",'0','int');
		}else{
			$data["zn_image_height"] = I("post.value",'0','int');
		}
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"]);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }
	
}

?>
