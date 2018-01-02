<?php //文章管理 Article 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class LessonApplyModel extends PublicModel {
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('zn_lesson_id','lqrequire','必须填写！',self::MUST_VALIDATE),
		array('zn_member_id','require','必须填写',self::MUST_VALIDATE)
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('zn_status', '1', self::MODEL_INSERT),
		array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
	);
	
    /** 初始化*/
    public function __construct() {
		parent::__construct();
		$this->pc_os_label =  "zc_title";//数据表显示标题字段
		$this->pc_index_list =  "Article/index";//列表首页
	}

	//列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config=array('field'=>'*','where'=>' 1 ','order'=>'`id` DESC')) {
		$list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
		foreach ($list as $lnKey => $laValue) {

			$list[$lnKey]['zn_lesson_id_label'] = M("Live")->where("id=".$laValue['zn_lesson_id'])->getField('zc_title');
			$list[$lnKey]['zn_member_id_label'] = M("Member")->where("id=".$laValue['zn_member_id'])->getField('zc_nickname')." ( ".$laValue['zn_member_id']." ) ";
			$list[$lnKey]['status_label'] =  C("LESSON_APPLY_STATUS")[$laValue['zn_status']] ;
			$list[$lnKey]['zn_cdate'] =  date("Y-m-d H:i:s",$laValue['zn_cdate']) ;
            $list[$lnKey]['visible_button'] = $laValue['zn_status'] == 1 ?  C("ICONS_ARRAY")['unapprove']: C("ICONS_ARRAY")['approve'];
        }
        return $list;
    }


}

?>
