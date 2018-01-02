<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;

defined('in_lqweb') or exit('Access Invalid!');

class LessonLiveModel extends PublicModel
{
    /* 会员模型自动验证 */
    protected $_validate = array(
        array('zn_cat_id', 'lqrequire', '所属课程必须！', self::MUST_VALIDATE),
        array('zc_title', 'require', '课节标题必须填写', self::MUST_VALIDATE),
        array('zc_title', '1,100', '课节标题在1~100个字符', self::MUST_VALIDATE, 'length'),
        array('zc_date', 'require', '课节日期必须', self::MUST_VALIDATE),
        array('zc_start_time', 'require', '直播开始时间必须', self::MUST_VALIDATE),
        array('zc_end_time', 'require', '直播结束时间必须', self::MUST_VALIDATE),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
        array('zn_mdate', NOW_TIME, self::MODEL_BOTH)
    );

    public function __construct()
    {
        parent::__construct();
    }

    ////通过条件获取字段
    public function lqGetField($sql, $field)
    {
        $field_arr = split(',', $field);
        if ($field == "*") {
            return $this->where($sql)->field("*")->find();
        } else {
            if (count($field_arr) > 1) {
                return $this->where($sql)->field($field)->find();
            } else {
                return $this->where($sql)->getField($field);
            }
        }
    }


    ////通过条件获取字段(增强版)
    public function lqGetField_select($sql, $field)
    {
        $field_arr = split(',', $field);
        if ($field == "*") {
            return $this->where($sql)->field("*")->select();
        } else {
            if (count($field_arr) > 1) {
                return $this->where($sql)->field($field)->select();
            } else {
                return $this->where($sql)->getField($field);
            }
        }
    }

    ///添加数据
    public function addData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $data = $this->create($data);//验证
        if (!$data) {
            return $this->getError(); //错误详情见自动验证注释
        } else {
            $mid = $this->add($data);
            return $mid ? $mid : 0; //0-未知错误，大于0-注册成功
        }

    }

    ///保存数据
    public function saveData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }

        $mid = $this->save($data);
        return $mid ? $mid : 0; //0-未知错误，大于0-注册成功


    }


    /////更新直播开始，结束时间
    public function updateTime($id, $time, $type = 1)
    {
        if ($type == 1) $field = 'zc_live_start';
        else $field = 'zc_live_end';

        $isTime = $this->field($field)->find($id);
        if ($isTime[$field]) $newTime = $isTime[$field] . "||" . $time;
        else $newTime = $time;

        $save = array($field => $newTime, "zl_status" => 1);
        $ok = $this->where("id=" . $id)->setField($save);
        if ($ok) {
            $fid = $this->where(array("id" => $id))->getField("zn_cat_id");
            M("Live")->where(array("id" => $fid))->setField("zc_newest_time", $time);
        }
    }

    ////视频直播转录播地址
    public function updateVodUrl($id, $url)
    {
        $isUrl = $this->field('zc_vod_url')->find($id);
        if ($isUrl['zc_vod_url']) $newUrl = $isUrl['zc_vod_url'] . "||" . $url;
        else $newUrl = $url;

        $save = array('zc_vod_url' => $newUrl, "zl_status" => 2);
        $this->where("id=" . $id)->setField($save);
    }

    ///统计  
    public function getCount($where = '')
    {
        if ($where) return $this->where($where)->count();
        else return $this->count();

    }

    // 获取直播课程数据
    public function lq_getmessage($sql = array('field' => '*', 'where' => ' zl_visible=1 ', 'order' => '`id` DESC'), $limit = '')
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($sql["field"])->where($sql["where"])->order($sql["order"])->limit($limit)->select();

        return $list;

    }

    /*
     * 获取最后直播课程记录
     * params $live_id  课程id
    */
    public function getLastLive($live_id)
    {
        if (!$live_id) return false;
        $where = array(
            'zl_visible' => 1,
            "zn_cat_id" => $live_id,
            "zl_status" => array("GT", 0)
        );

        $lastInfo = $this->field(array("id", "zc_title", "zc_date", "zc_live_start", "zc_live_end"))->where($where)->order("zc_live_start desc")->find();
        if ($lastInfo) return $lastInfo;
        else return false;
    }

    /*
     * 直播到第几场次
     */
    public function lessonNow($live_id)
    {
        if (!$live_id) return false;
        $where = array(
            'zl_visible' => 1,
            "zn_cat_id" => $live_id,
            "zl_status" => array("EQ", 1)
        );
        $count = $this->where($where)->count();
        return $count + 1;
    }

    /*
     * 下次直播场次
     */
    public function getNewestLesson($live_id)
    {
        if (!$live_id) return false;
        $where = array(
            'zl_visible' => 1,
            "zn_cat_id" => $live_id,
            "zc_date" => array("GT", date("Y-m-d")),
            "zl_status" => array("EQ", 0),
        );
        $lastInfo = $this->field(array("id", "zc_title", "zc_date", "zc_start_time", "zc_end_time"))->where($where)->order("zc_date asc,zc_start_time asc,zc_end_time asc")->find();
        return $lastInfo;
    }

    /** 用户 - 学习 - 最近观看直播
     * @param $id 用户id
     * return 最近直播数据
     */
    public function lastLive($id)
    {
        $rs = D("LiveRecordView")->order("LiveRecord.zn_cdate desc")->where("LiveRecord.zn_member_id=" . $id)->find();
        return $rs;
    }

    /*
     * 课程剩余课节
     * 统计stauts == 0 章节，包括缺课统计
     */
    public function unLiveLession($live_id)
    {
        if (!$live_id) return 0;
        $where = array(
            "zn_cat_id" => $live_id,
            "zl_status" => 0
        );
        return $this->where($where)->count();
    }

}

?>
