<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;


defined('in_lqweb') or exit('Access Invalid!');

class StudentModel extends PublicModel
{
    private $model_lesson,$model_live_record,$model_vod_record, $model_favorite, $model_enroll,$model_member,$model_lesson_vod,$model_lesson_live,$model_live,$model_vod;
    /* 会员模型自动验证 */

    public function __construct()
    {
        parent::__construct();
        $this->model_vod = D("Vod");
        $this->model_live = D("Live");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_favorite = D("MemberFavorite");
        $this->model_enroll = D("MemberEnroll");
        $this->model_lesson_live = D("LessonLive");
        $this->model_member = M("Member");
        $this->model_live_record = D("LiveRecord");
        $this->model_vod_record = D("VodRecord");


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

    ////通过条件获取字段
    public function lqGetField_select($sql, $field)
    {
        $field_arr = split(',', $field);
        if ($field == "*") {
            return $this->where($sql)->field("*")->select();
        } else {
            if (count($field_arr) > 1) {
                return $this->where($sql)->field($field)->select();
            } else {
                return $this->where($sql)->Field($field)->select();
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

    ///删除数据
    public function delData($id, $zn_teacher_id)
    {
        if (empty($id)) {
            return '参数错误！';
        }
        // 判断删除的是否是自己的课程
        $data = array(
            'id' => $id,
            'zn_teacher_id' => $zn_teacher_id,
        );
        $list = $this->where($data)->find();

        if (!$list) {
            return -1; //错误详情见自动验证注释
        } else {
            $mid = $this->where($data)->delete();
            return $mid ? $mid : 0; //0-未知错误，大于0-删除成功
        }

    }


    ///统计
    public function getCount($where = '')
    {
        if ($where) return $this->where($where)->count();
        else return $this->count();

    }

    /**
     * 学生 - 个人中心 - 首页 - 获取最近五次直播信息
     * @param $student_id 学生id
     * @return mixed 数据
     */
    public function getLastFiveLive($student_id)
    {

        $sql = $this->model_live_record
            ->query("SELECT `zn_live_id` FROM lq_live_record WHERE zn_member_id = " . $student_id . " and zn_cdate IN (SELECT MAX(zn_cdate)FROM lq_live_record GROUP BY zn_live_id) ORDER BY zn_cdate desc LIMIT 5 ");// 查询最新的信息,以时间作为凭证
        foreach ($sql as $key => $value) {
            $temp = D('StudentLiveView')->where('Live.id=' . $value['zn_live_id'])->find(); // 查大课信息
            if (!$temp) {
                continue; // 没有大课信息跳过本次循环
            }else{
                $liveLessonMsg[$key] = $temp;
            }
            $liveMsg[$key] = $this->model_lesson_live->getNewestLesson($value['zn_live_id']);// 查询下节课信息 (如果没有?)
            if (!$liveMsg[$key]) {
                $liveMsg[$key] = array(); // 判断是否有下节课
            }
            $live_msg[$key] = array_merge($liveMsg[$key], $liveLessonMsg[$key]);// 合并数组(注意两个都是数组)
            $live_msg[$key]['zn_cat_name'] = return_cat($live_msg[$key]['zn_cat_id']);// 查询分类信息
        }
        return $live_msg;
    }

    /**
     * 学生 - 个人中心 - 首页 - 获取最近五次录播信息
     * @param $student_id 学生id
     * @return mixed 数据
     */
    public function getLastFiveVod($student_id)
    {
        // 获取最近观看的录播(5节)
        $vod_sql = $this->model_vod_record
            ->query("SELECT `zn_vod_id`,`zn_lesson_id`,`zn_total` FROM lq_vod_record WHERE zn_member_id = " . $student_id . " and zn_cdate IN (SELECT MAX(zn_cdate)FROM lq_vod_record GROUP BY zn_vod_id) ORDER BY zn_cdate desc LIMIT 5 ");// 查询最新的信息,以时间作为凭证
        foreach ($vod_sql as $key => $value) {
            $temp = D('VodView')->where('Vod.id=' . $value['zn_vod_id'])->find(); // 查大课信息
            if (!$temp) {
                continue; // 没有大课信息跳过本次循环
            }else{
                $vodLessonMsg[$key] = $temp;
            }
            $vodLessonMsg[$key]['zn_lesson_id'] = $value['zn_lesson_id'];// 小课id赋值
            $vodLessonMsg[$key]['zn_total'] = $value['zn_total'];// 当时课程数目赋值
            $vodLessonMsg[$key]['current_lesson_num'] = $this->model_lesson_vod
                ->getCount('id<=' . $value['zn_lesson_id'] . ' and zn_cat_id=' . $value['zn_vod_id']);// 查询当前课程
            $vodLessonMsg[$key]['total_lesson_num'] = $this->model_lesson_vod
                ->getCount('zn_cat_id=' . $value['zn_vod_id']);// 查询一共多少节课 判断是否有更新
            $vodLessonMsg[$key]['percentage'] = ($vodLessonMsg[$key]['current_lesson_num'] / $vodLessonMsg[$key]['total_lesson_num']) * 100;// 计算百分比
            $vodLessonMsg[$key]['zn_cat_name'] = return_cat($vodLessonMsg[$key]['zn_cat_id']);// 查询分类信息
        }

        return $vodLessonMsg;
    }


    // 首页 - 获取学生信息
    public function get_index_student_info($student_id)
    {
        $student_msg = M('member')->where('id=' . $student_id . ' and zl_visible = 1')->field('`id`,`zc_nickname`,`zc_headimg`')->find();

        // 获取收藏数量
        $student_msg['favorite'] = $this->model_favorite->getCount("zn_member_id = " . $student_msg['id']);
        // 获取关注数量
        $student_msg['enroll'] = $this->model_enroll->getCount("zn_member_id = " . $student_msg['id']);
        // 获取下一次要观看的直播
        $temp = $this->model_enroll->nextLive($student_id);
        if(!$temp){
            $temp = array();
        }
        $student_message = array_merge($temp, $student_msg);
//        pr($student_message);
        return $student_message;
    }



}

?>
