<?php //粉丝管理 Follow 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use Member\Api\MemberApi as MemberApi;

class FollowModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(

	);

	/* 用户模型自动完成 */
	protected $_auto = array(

	);		
	
    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_nickname";//数据表显示标题字段
		$this->pc_index_list =  "Follow/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' zl_enabled=1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();	
        $Member = new MemberApi;
		foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['zl_type_label'] = $laValue['zl_type'] == 1 ? '关注': '页面授权';
			if($laValue["headimg"]){
				$list[$lnKey]['headimg'] = $laValue["zc_headimg_url"];
			}else{
				$list[$lnKey]['headimg'] = NO_PICTURE_ADMIN;
			}
			$list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
			$list[$lnKey]['area_label'] =$laValue['zc_country'] ." - ". $laValue['zc_province'] ." - ". $laValue['zc_city'];
			$list[$lnKey]['sex_label'] = C("_SEX")[$laValue['zn_sex']];
			$list[$lnKey]['zn_member_id_url'] = U("Member/edit?tnid=".$laValue['zn_member_id']);
			if($laValue["zn_member_id"]){
				$list[$lnKey]['zc_member_account'] = $laValue['zc_member_account'];
			}else{
				$list[$lnKey]['zc_member_account'] = "未邦定";
			}
			
			if($laValue['zl_type']==1){
				$list[$lnKey]['time'] = lq_cdate_format($laValue["zn_subscribe_time"],"Y-m-d H:i:s");
				if($laValue["zl_visible"]==0){
				$list[$lnKey]['time'] = $list[$lnKey]['time']."<br>".lq_cdate_format($laValue["zn_unsubscribe_time"],"Y-m-d H:i:s");
				}else{
					if($laValue["zn_unsubscribe_time"]==0){
						$list[$lnKey]['time'] = $list[$lnKey]['time']."<br>0000-00-00 00:00:00";
					}else{
						$list[$lnKey]['time'] = $list[$lnKey]['time']."<br>".lq_cdate_format($laValue["zn_unsubscribe_time"],"Y-m-d H:i:s");
					}
				}
				$list[$lnKey]['zl_type_label'] = '关注';
			}else{
				$list[$lnKey]['time'] = '-仅页面授权无关注-';
				$list[$lnKey]['zl_type_label'] = '页面授权';
			}
			
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
	//数据保存
	public function lqSubmit(){
		
	}

	
	
}

?>
