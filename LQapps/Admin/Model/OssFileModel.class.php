<?php //文章管理 Article 数据处理，数据回调
namespace Admin\Model;
use Think\Model;

class OssFileModel extends PublicModel
{
    /* 用户模型自动验证 */
    protected $_validate = array();

    /* 用户模型自动完成 */
    protected $_auto = array(

        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
    );

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();

    }

    //列表页
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' 1 ', 'order' => '`id` DESC'))
    {
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        // $cat = F('lesson_cat', '', COMMON_ARRAY);
        $live = M("Live");
        $vod = M("Vod");
        $member = M("Member");
        foreach ($list as $lnKey => $laValue) {
            if ($laValue['zn_type'] == 1) $table = $live;
            else $table = $vod;

            $lesson = $table->where("id=" . $laValue['zn_fid'])->getField('zc_title');
            $list[$lnKey]['title'] = $lesson ? $lesson : '<a>课程不存在或已删除</a>';
            $list[$lnKey]['size'] = formatsize($laValue['zn_size']);
            $list[$lnKey]['user'] = $member->where("id=" . $laValue['zn_uid'])->getField('zc_nickname') . " ( " . $laValue['zn_uid'] . " ) ";
            $list[$lnKey]['zn_cdate_label'] = lq_cdate_format($laValue['zn_cdate']);

            $list[$lnKey]['no'] = $firstRow + $lnKey + 1;
        }
        return $list;
    }

    //数据保存
    protected function _before_write(&$data)
    {
    }

    public function lqSubmit()
    {
        return $this->lqCommonSave();
    }

    //确保tag|有效性
    protected function str_replace_tag($value)
    {
        return str_replace("｜", "|", $value);
    }

    //确保keyword|有效性
    protected function str_replace_keyword($value)
    {
        return str_replace("，", ",", $value);
    }


    //更改-是非首页 
    public function setProperty()
    {
        $lcop = I("get.tcop", 'is_index');
        $data = array();
        $data["id"] = I("get.tnid", '0', 'int');
        if ($lcop == 'is_index') {
            $data['zl_is_index'] = I("get.vlaue", '0', 'int') == 1 ? 0 : 1;
            $op_data = array("status" => $data['zl_is_index'], "txt" => $data['zl_is_index'] == 1 ? "是首页" : "非首页");
        } elseif ($lcop == 'is_good') {
            $data['zl_is_good'] = I("get.vlaue", '0', 'int') == 1 ? 0 : 1;
            $op_data = array("status" => $data['zl_is_good'], "txt" => $data['zl_is_good'] == 1 ? "是精品" : "非精品");
        } else {
            return array('status' => 0, 'msg' => L("ALERT_ARRAY")["dataOut"]);
        }
        $data['zn_mdate'] = NOW_TIME;
        if ($this->save($data)) {
            $this->lqAdminLog($data["id"]);//写入日志
            return array('status' => 1, 'msg' => C("ALERT_ARRAY")["success"], 'data' => $op_data);
        } else {
            return array('status' => 0, 'msg' => C("ALERT_ARRAY")["fail"]);
        }
    }

}

?>
