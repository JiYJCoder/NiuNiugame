<?php //地区管理 Region 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use LQLibs\Util\Category as Category;//树状分类

class RegionModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_name','require','地区名称必须填写！'),
		array('zc_name', '', "地区名称被占用", self::EXISTS_VALIDATE, 'unique'), //地区页面标题被占用
		array('zc_lable','require','地区页面标题必须填写！'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_fid', 'LQ_number', self::MODEL_BOTH,'function'),
		array('zn_sort', 'LQ_number', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_name";//数据表显示标题字段
		$this->pc_index_list =  "Region/index";//列表首页
	}
	
	//缓存列表数据
    public function lqCacheData($return=0){
		$tntopid=1;
		if($return==1){
			$condition=" zn_class in (1,2,3)  "  ;
			$tree = new Category("region", array('id', 'zn_fid', 'zc_name'));
			$treelist = $tree->getList($condition,$tntopid,' zn_fid,zn_sort,id desc ');//获取分类结构
			$province=array();
			foreach ($treelist as $lnKey => $laValue) {
				if($laValue["zn_fid"]==1) $province[$laValue["id"]]=$laValue["zc_name"];
			}
			F('region_tree',$treelist,COMMON_ARRAY);//地区数据
			F('province',$province,COMMON_ARRAY);//省份
			return $treelist;
		}else{
			$condition=" zn_class in (1)  "  ;
			$tree = new Category("region", array('id', 'zn_fid', 'zc_name'));
			$treelist = $tree->getList($condition,$tntopid,' zn_fid,zn_sort,id desc ');//获取分类结构
			$province=array();
			foreach ($treelist as $lnKey => $laValue) {
				$province[$laValue["id"]]=$laValue["zc_name"];
			}
			F('province',$province,COMMON_ARRAY);//省份
		}
	}
	
	//列表页
    public function lqList($condition=array(),$tntopid=0,$tcaction='index') {
		$tree = new Category("region", array('id','zn_fid','zc_name'));
		$treelist = $tree->getList($condition,$tntopid,' zn_fid,zn_sort,id desc ');               //获取分类结构
		foreach ($treelist as $lnKey => $laValue) {
			$treelist[$lnKey]['class_label'] = C("ROOT_CLASS")[$laValue['zn_class']];
            $treelist[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $treelist[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$treelist[$lnKey]['no'] = $lnKey+1;
			if($treelist[$lnKey]['no']==1&&$laValue['zn_class']>0){
				$data_up = $this->field("zn_fid")->where("id=" .$laValue["id"])->find();
				$treelist[$lnKey]['up_page_url'] =__CONTROLLER__.'/'.$tcaction.'/tnfid/'.$data_up["zn_fid"].'/tntclass/'.($laValue['zn_class']-1);
			}else{
				$treelist[$lnKey]['up_page_url']='';	
			}
        }
		return $treelist;
    }


	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
    //更改  zl_visible 
    public function lqVisible($is_tree=0) {
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
        //记录使用状态提示
        $la_child=M()->query("select queryChildren".CONTROLLER_NAME."(".$data["id"].") as Child_Ids");
        $data["id"] = array('exp',' IN ('.$la_child[0]["child_ids"].') ');
        $url=U($this->pc_index_list);
        $data['zl_visible'] = I("get.status",'0','int') == 1 ? 0 : 1;
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"],'url' =>$url, 'data' => array("status" => $data['zl_visible'], "txt" => $data['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0] ));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }	

	//单记录删除
    public function lqDelete($is_tree=0) {
        $data["id"] = I("get.tnid",'0','int');
		$lc_check=$this->lqDelete_Check($data,$is_tree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		$la_child=M()->query("select queryChildren".CONTROLLER_NAME."(".$data["id"].") as Child_Ids");
		$data["id"] = array('exp',' IN ('.$la_child[0]["child_ids"].') ');
		setcookie(CONTROLLER_NAME.'_cookie_data',"");//清除录入操作的记忆
		if ($this->where($data)->delete()) {
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list."?clearcache=1"));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
			
    // 插入成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_insert($data,$options) {
		$tree = new Category("region", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
	}	
    // 更新成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_update($data,$options) {
		if( ACTION_NAME=='edit' ){
		$tree = new Category("region", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
		}
		$this->lqCacheData();			
	}

}

?>
