<?php // 会员注册分享日志 数据处理，数据回调
namespace Admin\Model;
use Think\Model;
use Member\Api\MemberApi as MemberApi;

class RegisterShareModel extends PublicModel {
	protected $MemberModel;
	/* 用户模型自动验证 exist */
	protected $_validate = array(
		array('zc_referer_accout','isMobile','请输入正确的推荐人手机号码',self::MUST_VALIDATE,'function'),
        array('zc_referer_accout', 'lqCheckReferer', "推荐人手机号码不存在或未审核", self::MUST_VALIDATE, 'callback'),
		array('zc_member_account','isMobile','请输入正确的被推荐人手机号码',self::MUST_VALIDATE,'function'),
        array('zc_member_account', 'lqCheckMember', "被推荐人手机号码不存在或未审核", self::MUST_VALIDATE, 'callback'),
        array('zc_member_account', 'lqCheckEffective', "被推荐人手机号码已存在日志里了", self::MUST_VALIDATE, 'callback'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
	);
	//保护字段（add或edit不能操作）
	protected $_protected_field=array('zn_referer_id','zn_member_id','zn_cdate','zn_mdate');
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_member_account";//数据表显示标题字段
		$this->pc_index_list =  "RegisterShare/index";//列表首页
		$this->MemberModel = new MemberApi;
	}
    //推荐人手机号码不存在或未审核
    protected function lqCheckReferer(){
        $data = I("post.LQF", '');
        if($this->MemberModel->apiGetField("zc_account='".$data["zc_referer_accout"]."'","id")) return true;
        return false;
    }	
    //被推荐人手机号码不存在或未审核
    protected function lqCheckMember(){
        $data = I("post.LQF", '');
        if($this->MemberModel->apiGetField("zc_account='".$data["zc_member_account"]."'","id"))  return true;
        return false;
    }	
    //被推荐人手机号码已存在日志里了
    protected function lqCheckEffective(){
        $data = I("post.LQF", '');
        if(M("register_share")->where("zc_referer_accout='".$data["zc_referer_accout"]."' and zc_member_account='".$data["zc_member_account"]."'")->count('id')==0) {
			return true;
		}
        return false;
    }			
		
	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
	    foreach ($list as $lnKey => $laValue) {
			$list[$lnKey]['url'] = '/sys-index.php/Member/edit/tnid/'.$laValue["zn_referer_id"];
			$list[$lnKey]['no'] = $firstRow+$lnKey+1;
        }
        return $list;
    }
	
    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data){
       $data["zn_referer_id"]=$this->MemberModel->apiGetField("zc_account='".$data["zc_referer_accout"]."'","id");
       $member_data=$this->MemberModel->apiGetField("zc_account='".$data["zc_member_account"]."'","id,zn_cdate");
	   if($member_data){
	   $data["zn_member_id"]=$member_data["id"];
	   $data["zn_mdate"]=$member_data["zn_cdate"];
	   $data["zn_cdate"]=$member_data["zn_cdate"];
	   }
	}	
	
	//数据保存
	public function lqSubmit(){return $this->lqCommonSave();}



	//单记录删除
    public function lqDelete($isTree=0) {
		$data["id"] = I("get.tnid",'0','int');
		if ($this->where($data)->delete()) {
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }
	
	//多记录删除
    public function lqDeleteCheckbox() {
		$data["id"]  = array('in',  I("get.tcid",'','lqSafeExplode') );
		if ($this->where($data)->delete()) {
				return array('status' => 1, 'msg' => C('ALERT_ARRAY')["delSuccess"], 'url' => U($this->pc_index_list));
			} else {
				return array('status' => 0, 'msg' => C('ALERT_ARRAY')["delFail"]);
		}
    }

	
}

?>
