<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;


defined('in_lqweb') or exit('Access Invalid!');

class VodRecordModel extends PublicModel
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
        $this->model_lesson_vod = M("LessonVod");
        $this->model_enroll = M("MemberEnroll");
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
            $teacher_id = D("LessonVodView")->where("LessonVod.id=" . $lesson_id)->getField("zn_teacher_id");
            if(!$teacher_id) return;
            $obj_id = $this->model_lesson_vod->where("id=" . $lesson_id)->getField("zn_cat_id");
            $count = $this->model_lesson_vod->where('zn_cat_id ='.$obj_id)->count();

            $addData = array(
                "zn_lesson_id" => $lesson_id,
                "zn_member_id" => $member_id,
                "zn_total" => $count,
                "zn_vod_id" => $obj_id,
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

            $data['zn_cdate'] = time();
            $where_sql = array(
                "zn_lesson_id" => $lesson_id,
                "zn_member_id" => $member_id,
            );
            $this->where($where_sql)->save($data);
            return $this->where($where_sql)->setInc("zc_vod_time", $step);
        }
    }


    /*
     * 会员直播时长观看统计
     * $lesson_id 统计具体课节
     */
    public function getCount($member_id, $lesson_id = '', $vod_id = '')
    {
        if ($lesson_id) return $this->where("zn_member_id=" . $member_id . " and zn_lesson_id=" . $lesson_id)->sum("zc_vod_time");
        elseif ($vod_id) return $this->where("zn_member_id=" . $member_id . " and zn_vod_id=" . $vod_id)->sum("zc_vod_time");
        else return $this->where("zn_member_id=" . $member_id . "")->sum("zc_vod_time");

    }

    /*
     * 课节参与统计
     * $lesson_id 统计具体课节
     */
    public function getNumCount($member_id,  $vod_id = '')
    {
        if ($vod_id) return $this->where("zn_member_id=" . $member_id . " and zn_vod_id=" . $vod_id)->count();
        else return $this->where("zn_member_id=" . $member_id . "")->count();

    }
    /*
     * 共听多少位老师讲课
     * return num
     */
    public function teacherSum($member_id)
    {
        $db = new model();
        $rs = $db->query("select count(id) as total from (select id from `lq_vod_record` where zn_member_id = " . $member_id . " group by zn_teacher_id) a");

        return $rs[0]['total'];
    }

    /*
     * 获取更新了多少节课
     * return num
     */
    public function returnLesson($student_id)
    {
        // 获取最近观看的录播
        $db = new model();
        $vod_sql = $db->query("SELECT a.zn_vod_id,a.zn_lesson_id,a.zn_total FROM lq_vod_record AS a LEFT JOIN lq_vod AS b ON a.zn_vod_id = b.id WHERE a.zn_member_id = " . $student_id . " and b.zl_status=6 and a.zn_cdate IN (SELECT MAX(zn_cdate)FROM lq_vod_record GROUP BY zn_vod_id) ORDER BY a.zn_cdate desc");// 查询最新的信息,以时间作为凭证
        $total = 0;
        $count = count($vod_sql);

        foreach ($vod_sql as $key => $value) {
            $zn_total= $value['zn_total'];// 当时课程数目赋值
            unset($total_lesson_num);
            $total_lesson_num = $this->model_lesson_vod
                ->where('zn_cat_id=' . $value['zn_vod_id'])->count();// 查询一共多少节课 判断是否有更新

            if(($total_lesson_num - $zn_total) > 0){// 如果有更新 更新总数+1
                $total += 1;
            }
        }
        $where_sql = array(
            "a.zn_member_id" => $student_id,
            "a.zn_type" => 2,// 2录播
            "b.zl_status" => 6,
        );
        $initial = $this->model_enroll
                ->alias('a')->join('lq_vod AS b on a.zn_object_id = b.id')
                ->where($where_sql)->count();
        $real_total = $initial - $count + $total;
        return $real_total > 0 ? $real_total : 0;


    }

}

?>
