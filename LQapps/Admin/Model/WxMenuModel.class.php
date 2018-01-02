<?php //微信菜单 WxMenu 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use LQLibs\Util\Category as Category;//树状分类

class WxMenuModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,20','菜单名称在1~20个字符',self::MUST_VALIDATE,'length'),
		array('zn_fid','checkFid','按钮数最多只能创建3个，子按钮数最多创建5个，否则创建失败!',0,'callback'),
		array('zc_type','1,20','菜单接口类型在1~20个字符',self::MUST_VALIDATE,'length'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_fid', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zn_sort', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zc_key', 'lqNull', self::MODEL_INSERT,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "WxMenu/index";//列表首页
	}
	
	//缓存列表数据
    public function lqCacheData($tnReturn=0){
		$condition='';
		$tree = new Category("wx_menu", array('id', 'zn_fid', 'zc_caption'));
		$treelist = $tree->getList($condition,0,' zn_fid,zn_sort,id ');               //获取分类结构
		F('wx_menu',$treelist,COMMON_ARRAY);
		return $treelist;
	}
	
	//列表页
    public function lqList($condition=array()) {
		$treelist=F('wx_menu','',COMMON_ARRAY);
		if(empty($treelist)){
				$treelist=$this->lqCacheData();
		}	
        foreach ($treelist as $lnKey => $laValue) {
            $treelist[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $treelist[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$treelist[$lnKey]['no'] = $lnKey+1;
			$treelist[$lnKey]['type_label'] = C("WEIXIN_MENU_TYPE")[$laValue['zc_type']];
        }
        return $treelist;
    }
	
	//按钮数最多只能创建3个
	protected function checkFid() {
		$zn_fid=intval($_POST["LQF"]["zn_fid"]);
		$id=intval($_POST["LQF"]["id"]);
		if($id) return true;
		if(intval($zn_fid)==0){
			if( $this->where("zn_fid=0 and zl_visible=1")->count()>=3 ){
				return false;
			}else{
				return true;
			}
		}else{
			if( $this->where("zn_fid=".$zn_fid." and zl_visible=1")->count()>=5 ){
				return false;
			}else{
				return true;
			}
		}
	}

	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

    // 插入成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_insert($data,$options) {
		//更改 zc_key 值
		$key="menu";
		if( intval($data["zn_fid"]) >0){
				$key=$key."_".$data["zn_fid"]."_".$data["id"];
		}else{
				$key=$key."_".$data["id"];
		}
		M()->execute( "update __PREFIX__wx_menu set zc_key='$key' where id=".intval($data["id"]) );
		$tree = new Category("wx_menu", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
		$this->lqCacheData();
	}	
    // 更新成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_update($data,$options) {
		if( ACTION_NAME=='edit' ){
		$tree = new Category("wx_menu", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
		}
		$this->lqCacheData();
	}	

	
}

?>
