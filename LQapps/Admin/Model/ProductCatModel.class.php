<?php //产品分类 ProductCat 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use LQLibs\Util\Category as Category;//树状分类

class ProductCatModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zc_caption','1,100','分类-标题在1~100个字符间',self::MUST_VALIDATE,'length'),
		array('zc_caption', '', "分类-标题被占用", self::MUST_VALIDATE, 'unique'), //分类页面标题被占用
		array('zn_fid', 'lqCheckFid', "父级分类不合法", self::MUST_VALIDATE, 'callback'), //分类父级
		array('zn_sort','0,65535','排序在0~65535之间',self::MUST_VALIDATE,'between'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zc_image', 'lqNull', self::MODEL_BOTH,'function'),
		array('zn_fid', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_is_index', 'lqNumber', self::MODEL_BOTH,'function'),
		array('zl_visible', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
		array('zn_mdate', NOW_TIME, self::MODEL_BOTH),	
	);		
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_caption";//数据表显示标题字段
		$this->pc_index_list =  "ProductCat/index";//列表首页
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
		$tree = new Category("product_cat", array('id', 'zn_fid', 'zc_caption'));
		$treelist = $tree->getList($condition,0,' zn_fid,zn_sort,id ');               //获取分类结构
		$array=array();
		foreach ($treelist as $lnKey => $laValue) {
		$laValue["top_id"] = $tree->get_top_node($laValue["id"],10);//得到根节点
		$laValue['child_ids'] = $tree->get_child($laValue["id"],10,'zl_visible=1');
		$laValue['current']=$this->recursion_root($laValue["id"],10);
		$array[$laValue["id"]]=$laValue;
		}
		F('product_cat',$array,COMMON_ARRAY);
		if($tnReturn) return $array;
	}


	//类别往根遍历
	public function recursion_root($tnid,$tnLevel)
	{	
		//防止无限递归
		if($tnLevel<=0){return false;}
		$lcSubstr="";
		//获取$parent
		$ladata = $this->field("`id`,`zc_caption`,`zn_fid`")->where("zl_visible=1 and id=" .(int)$tnid)->find();
		//得到每个节点
		if($ladata)
		{
			$lcSubstr.="|@1@|".$ladata["id"]."|@2@|".$ladata["zc_caption"];
			//再次调用这个函数获取这个父节点
			$lcSubstr.=$this->recursion_root($ladata["zn_fid"],$tnLevel-1);
		}
		return $lcSubstr;
	}	
	
	
	//列表页
    public function lqList($condition=array()) {
		$treelist=F('product_cat','',COMMON_ARRAY);
		if(empty($treelist)){
				$treelist=$this->lqCacheData();
		}	
        foreach ($treelist as $lnKey => $laValue) {
			$index++;
            $treelist[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $treelist[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
			$treelist[$lnKey]['is_index_label'] = C("YESNO_STATUS")[$laValue['zl_is_index']];
			$treelist[$lnKey]['no'] = $index;
        }
        return $treelist;
    }

    public function lqListTotal($page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
        $treelist = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->select();
        $list = array();
        $model_product = M("Product");
        foreach ($treelist as $lnKey => $laValue) {
            $underList =  $this->field($page_config["field"])->where("zn_fid = ".$laValue['id'])->order($page_config["order"])->select();
            foreach($underList as $k => $v){
                $underList[$k]['p_title'] = $laValue['zc_caption'];
                $underList[$k]['visible_label'] = $v['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
                $underList[$k]['product_num'] = $model_product->where("zn_cat_id = ".intval($v['id']))->count();
            }
            $list = array_merge($list,$underList);

        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}

    // 插入成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_insert($data,$options) {
		$tree = new Category("product_cat", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
		$this->lqCacheData();
	}
	
    // 更新成功后的回调方法 - 设置树状结构的纵向层级 - (相对于根级别，比如：中国(根),广东(一级),广州(二级),越秀(三级))
    protected function _after_update($data,$options) {
		if(ACTION_NAME=='edit'){
		$tree = new Category("product_cat", array('id', 'zn_fid', 'zc_caption'));
		$tree->set_class($data["id"],$data["id"],10);
		}
		$this->lqCacheData();
	}	
	
    //单记录审批
    public function lqVisible($isTree=1) {
		$data=array();
        $data["id"] = I("get.tnid",'0','int');
		//记录使用状态提示
		$tree = new \LQLibs\Util\Category(CONTROLLER_TO_TABLE(CONTROLLER_NAME), array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($data["id"],10,'');
		$data["id"] = array('exp',' IN ('.$child_ids.') ');
		$url=U($this->pc_index_list);
        $data['zl_visible'] = I("get.status",'0','int') == 1 ? 0 : 1;
		$data['zn_mdate'] =NOW_TIME ;
        if ($this->save($data)) {
			$this->lqAdminLog($data["id"]);//写入日志
			if($data["zl_visible"]==0){
				$update=M()->execute( "update __PREFIX__product set zl_visible=0 where zn_cat_id in($child_ids)");
			}			
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"],'url' =>$url, 'data' => array("status" => $data['zl_visible'], "visible_label" => $data['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0] ));
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }
	
	//单记录删除
    public function lqDelete($is_tree=0) {
		$data["id"] = I("get.tnid",'0','int');
		$lc_check=$this->lqDeletCheck($data,$is_tree);//检查
		if($lc_check) return array('status' => 0, 'msg' => $lc_check );
		$tree = new Category("product_cat", array('id', 'zn_fid', 'zc_caption'));
		$child_ids = $tree->get_child($data["id"],10,'');
		$data["id"] = array('exp',' IN ('.$child_ids.') ');
		setcookie(CONTROLLER_NAME.'_cookie_data',"");//清除录入操作的记忆
		if ($this->where($data)->delete()) {
				$this->lqCacheData();
			    $this->lqAdminLog($data["id"]);//写入日志
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}		
    }
	
}

?>
