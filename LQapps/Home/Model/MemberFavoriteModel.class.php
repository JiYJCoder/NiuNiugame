<?php
namespace Home\Model;
use Think\Model;

defined('in_lqweb') or exit('Access Invalid!');

class MemberFavoriteModel extends PublicModel
{
    private $model_lesson, $model_lesson_live,$model_vod_record,$model_lesson_vod;
    /* 会员模型自动验证 */
    // 静态方式：在模型类里面通过$_validate属性定义验证规则。 
    /* 
    array
    (     
        array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),     
        array(验证字段2,验证规则,错误提示,[验证条件,附加规则,验证时间]),    
        ......
     );
    */
    protected $_validate = array(
        array('zn_type', 'lqrequire', '收藏类型必须！', self::MUST_VALIDATE),
        array('zn_object_id', 'lqrequire', '收藏对象不能为空！', self::MUST_VALIDATE),
        array('zn_member_id', 'require', '参数不能为空！', self::EXISTS_VALIDATE),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
    );

    public function __construct()
    {
        parent::__construct();
        $this->model_lesson_live = D("LessonLive");
        $this->model_vod_record = D("VodRecord");
        $this->model_lesson_vod = D("LessonVod");
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

    ///统计
    public function getCount($where = '')
    {
        if ($where) return $this->where($where)->count();
        else return $this->count();

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

    /**  用户 - 查询是否与报名课程 - 有返回收藏数
     * @param $uid 用户id
     * @param string $type 1直播,2录播,可为空
     * @return mixed 收藏数
     */
    public function is_enroll($uid, $type = '')
    {
        $data['zn_member_id'] = $uid;// 用户id
        if ($type != '') {
            $data['zn_type'] = $type; // 直播/录播
        }

        $temp = $this->where($data)->count();// 查询出大课id
        return $temp;
    }

    /*
     * 查询是否收藏该课程
     * $memeber_id  用户id
     * $lesson_id  课程id
     *  $type 1直播,2录播
     */
    public function is_member_fav($member_id, $lesson_id, $type = 1)
    {
        $where['zn_member_id'] = $member_id;// 用户id
        $where['zn_object_id'] = $lesson_id;// 课程id
        $where['zn_type'] = $type;// 类型

        return $this->where($where)->getField('id') ? 1 : 0;
    }


    // 分页获取数据 - 学生 - 直播
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();

        // 遍历处理而外地信息
        foreach ($list as $key => $value) {
            $temp = D('StudentLiveView')->where('Live.id=' . $value['zn_object_id'])->find();// 获取大课信息
            if (!$temp) {
                continue; // 没有大课信息跳过本次循环
            }{
                $liveMsg[$key] = $temp;
            }
            $liveNextMsg[$key] = $this->model_lesson_live->getNewestLesson($liveMsg[$key]['live_id']);// 查询下节课信息 (如果没有?)
            if (!$liveNextMsg[$key]) {
                $liveNextMsg[$key] = array(); // 判断是否有下节课
            }
            $liveMsg[$key]['zn_cat_name'] = return_cat($liveMsg[$key]['zn_cat_id']);// 查询分类信息
            $live_msg[$key] = array_merge($liveMsg[$key], $liveNextMsg[$key]);// 合并数组(注意两个都是数组)
        }


        return $live_msg;
    }

    // 分页获取数据 - 学生 - 录播
    public function lqList_vod($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'), $student_id, $type = 1)
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        // 遍历处理而外地信息
        foreach ($list as $key => $value) {
            $temp = D('StudentVodView')->where('Vod.id=' . $value['zn_object_id'])->find();// 获取大课信息
            if (!$temp) {
                continue; // 没有大课信息跳过本次循环
            } else {
                $vodMsg[$key] = $temp;
            }
            if ($type == 1) {
                $total = $this->model_vod_record->where('zn_member_id=' . $student_id . ' and zn_vod_id = ' . $vodMsg[$key]['vod_id'])
                    ->field('zn_total,zn_lesson_id')->order('zn_cdate desc')->find();// 获取总课程 用于判断是否更新
                if (!$total) {
                    $vodMsg[$key]['total'] = 0;
                    $vodMsg[$key]['current_lesson_num'] = 0;
                    $vodMsg[$key]['total_lesson_num'] = $this->model_lesson_vod
                        ->getCount('zn_cat_id=' . $vodMsg[$key]['vod_id']);// 查询一共多少节课
                    $vodMsg[$key]['percentage'] = 0;
                } else {
                    $vodMsg[$key]['total'] = $total['zn_total']; // 观看记录里面的课程数目
                    $vodMsg[$key]['zn_lesson_id'] = $total['zn_lesson_id']; // 获取最新的小课id
                    $vodMsg[$key]['current_lesson_num'] = $this->model_lesson_vod
                        ->getCount('id<=' . $vodMsg[$key]['zn_lesson_id'] . ' and zn_cat_id=' . $vodMsg[$key]['vod_id']);// 查询当前课程是第几节课
                    $vodMsg[$key]['total_lesson_num'] = $this->model_lesson_vod
                        ->getCount('zn_cat_id=' . $vodMsg[$key]['vod_id']);// 查询一共多少节课
                    $vodMsg[$key]['percentage'] = ($vodMsg[$key]['current_lesson_num'] / $vodMsg[$key]['total_lesson_num']) * 100;// 计算百分比
                }
            }

            $vodMsg[$key]['zn_cat_name'] = return_cat($vodMsg[$key]['zn_cat_id']);// 查询分类信息
        }

        return $vodMsg;
    }


}


?>
