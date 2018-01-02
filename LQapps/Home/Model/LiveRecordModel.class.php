<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;


defined('in_lqweb') or exit('Access Invalid!');

class LiveRecordModel extends PublicModel
{
    private $model_lesson, $model_favorite, $model_enroll;
    /* 会员模型自动验证 */
    protected $_validate = array(
        array('zn_lesson_id', 'lqrequire', '课节必须！', self::MUST_VALIDATE),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
    );

    public function __construct()
    {
        parent::__construct();
        $this->model_lesson_live = M("LessonLive");
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


    /*
     * 添加直播观看记录
     * params $leson_id 课节id
     * params $member_id  当前学生id
     */
    public function addData($lesson_id, $member_id)
    {
        if (!$lesson_id || !$member_id) {
            return '参数错误！';
        }

        if ($this->isRecord($lesson_id, $member_id)) {
            return;
        } else {
            $teacher_id = D("LessonLiveView")->where("LessonLive.id=" . $lesson_id)->getField("zn_teacher_id");
            if(!$teacher_id) return;
            $addData = array(
                "zn_lesson_id" => $lesson_id,
                "zn_member_id" => $member_id,
                "zn_teacher_id" => $teacher_id,
                "zn_live_id" => $this->model_lesson_live->where("id=" . $lesson_id)->getField("zn_cat_id"),
                "zn_cdate" => NOW_TIME
            );

            $mid = $this->add($addData);
            return $mid ? $mid : 0;
        }
    }

    /*
     * 检测会员对应课节是否已加入记录
     * 记录存在返回真
     */
    public function isRecord($lesson_id, $member_id)
    {
        return $this->where(array("zn_lesson_id" => $lesson_id, "zn_member_id" => $member_id))->getField("id") ? 1 : 0;
    }


    /*
     * 刷新会员对应课节 观看时长
     * params $lesson_id  课节id
     * params $member_id  会员
     * params $step  步长  默认10秒
     */
    public function saveData($lesson_id, $member_id, $step = '10')
    {
        if (!$lesson_id || !$member_id) {
            return '参数错误！';
        }

        if ($this->isRecord($lesson_id, $member_id)) {
            return $this->where(array("zn_lesson_id" => $lesson_id, "zn_member_id" => $member_id))->setInc("zn_total_time", $step);
        }
    }


    /*
     * 会员直播时长观看统计
     * $lesson_id 统计具体课节
     */
    public function getCount($member_id, $lesson_id = '', $live_id = '')
    {
        if ($lesson_id) return $this->where("zn_member_id=" . $member_id . " and zn_lesson_id=" . $lesson_id)->sum("zn_total_time");
        elseif ($live_id) return $this->where("zn_member_id=" . $member_id . " and zn_live_id=" . $live_id)->sum("zn_total_time");
        else return $this->where("zn_member_id=" . $member_id . "")->sum("zn_total_time");

    }

    /*
     * 课节参与统计
     * $lesson_id 统计具体课节
     */
    public function getNumCount($member_id,  $live_id = '')
    {
        if ($live_id) return $this->where("zn_member_id=" . $member_id . " and zn_live_id=" . $live_id)->count();
        else return $this->where("zn_member_id=" . $member_id . "")->count();

    }
    /*
     * 共听多少位老师讲课
     * return num
     */
    public function teacherSum($member_id)
    {
        $db = new model();
        $rs = $db->query("select count(id) as total from (select id from `lq_live_record` where zn_member_id = " . $member_id . " group by zn_teacher_id) a");

        return $rs[0]['total'];
    }

}

?>
