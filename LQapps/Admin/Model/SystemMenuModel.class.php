<?php //系统架构 SystemMenu 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use LQLibs\Util\Category as Category;//树状分类

class SystemMenuModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,100','菜单名称在1~100个字符间',self::MUST_VALIDATE,'length'),
		array('zn_fid', 'lqCheckFid', "父级菜单不合法", self::MUST_VALIDATE, 'callback'), //分类父级
		array('zn_sort','0,255','排序在0~255之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_fid', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_class', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_check_pop', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_is_menu', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "SystemMenu/index";//列表首页
	}
	//防止自己日自己
	protected function lqCheckFid($fid){
		if($_POST["LQF"]["zc_caption"]==$_POST["LQL"]["zn_fid_label"])
			return false;
		else
			return true;
	}	
	//缓存列表数据
    public function lqCacheData($tnReturn=0){
		$condition='';
		$cat = new Category("system_menu", array('id', 'zn_fid', 'zc_caption'));
		$list = $cat->getList($condition,0,' zn_fid,zn_sort ASC,id ASC');               //获取分类结构
		$systemid_module=array();//模块
		$sysmuen_list=array();//树状数据
		foreach ($list as $lnKey => $laValue) {
			if($laValue["zc_run_table"]){
				F($laValue["zc_run_table"].C("S_PREFIX").'data_sys_current',$laValue,C(SYSTEM_MENU_CURRENT));//写缓存
				$lclocation = $cat->lqSysmuenRootfind($laValue["id"],10);//菜单往根遍历
				F($laValue["zc_run_table"].C("S_PREFIX").'syslocation',$lclocation,C(SYSTEM_MENU_CURRENT));
			}
			if($laValue["zl_visible"]){
				$sysmuen_list[]=$laValue;
			}
			if($laValue["zn_fid"]==1)  $systemid_module[$laValue["id"]]=$laValue;
		}
		F('SystemMenu',$list,COMMON_ARRAY);
		F('sysmuen_tree',$sysmuen_list,COMMON_ARRAY);
		$accessControl=$accessControlCheckPop=$system_controller_to_table=array();
		foreach ($list as $k => $v) {
			if($v["zc_run_table"]){
				$accessControl[$v["zc_run_table"]]=$v["id"];
				$accessControlCheckPop[$v["zc_run_table"]]=$v["zl_check_pop"];
			}
		}
		foreach ($accessControl as $k => $v) {
			$system_controller_to_table[system_controller_to_key($k)]=$k;
		}
		$accessControl["controller_to_table"]=$system_controller_to_table;
		$accessControl["access_check_pop"]=$accessControlCheckPop;	
		//检测访问权限
		F('accessControl',$accessControl,COMMON_ARRAY);
		if($tnReturn) return $list;
	}
	
	//列表页
    public function lqList($condition=array()) {
		$catlist=F('SystemMenu','',COMMON_ARRAY);
		if(empty($catlist)){
				$catlist=$this->lqCacheData(1);
		}	
        foreach ($catlist as $lnKey => $laValue) {
			$catlist[$lnKey]['pop_label'] = $laValue['zl_check_pop'] == 1 ? C("POPLABEL")[1] : C("POPLABEL")[0];
            $catlist[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $catlist[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
            $catlist[$lnKey]['model'] = C("SYSMUEN_MODEL")[$laValue['zn_type']];
			$catlist[$lnKey]['no'] = $lnKey+1;
        }
        return $catlist;
    }


	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}
	
    // 插入成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_insert($data,$options) {
		$category = new Category("system_menu", array('id', 'zn_fid', 'zc_caption'));
		$category->set_class($data["id"],$data["id"],10);
		$this->lqCacheData();
	}	
    // 更新成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_update($data,$options) {
		if( ACTION_NAME=='edit' ){
		$category = new Category("system_menu", array('id', 'zn_fid', 'zc_caption'));
		$category->set_class($data["id"],$data["id"],10);
		}
		$this->lqCacheData();
	}
	
	//以下为检测方法*******************************************************************************************************************
	public function set_deleteone_chkFun($data) {//单项删除检测
		$data_sysmuenchild_test = $this->where("zn_fid=" .$data['id'] )->limit(1)->select();
		$laFilterID=array(1,2,3,4,5);//系统必要节点，不得删除
		if(in_array($data['id'],$laFilterID)){
			return json_encode(array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]."--系统必要节点，不得删除"));
		}
		if($data_sysmuenchild_test){
		  return json_encode(array('status' => 0, 'msg' => C('ALERT_ARRAY')["delfailchild"]));
		}else{
	      return 1;
		}
	}


    //更改属性  类型 
    public function lqSetProperty() {
		$data=array();
        $data["id"] = I("post.tnid",'0','int');
        $data["zn_type"] = I("post.tntype",'0','int');
		$data= array_merge($data,array('zn_mdate'=>NOW_TIME));
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' =>C("SYSMUEN_MODEL")[$data['zn_type']] , 'url' => U(CONTROLLER_NAME.'/index/clearcache/1'));
        } else {
            return array('status' => 0, 'msg' =>'' , 'url' => U(CONTROLLER_NAME.'/index/clearcache/1'));
        }
    }

}

?>
