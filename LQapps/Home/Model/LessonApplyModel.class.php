<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;

defined('in_lqweb') or exit('Access Invalid!');

class LessonApplyModel extends PublicModel
{
    /* 会员模型自动验证 */
    protected $_validate = array(
        array('zn_lesson_id','lqrequire','课程必须！',self::MUST_VALIDATE),
        array('zn_member_id','require','申请人必须!',self::MUST_VALIDATE),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(
        array('zn_type', 1, self::MODEL_INSERT),
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
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


    ///统计
    public function getCount($where = ''){
        if($where) return $this->where($where)->count();
        else return $this->count();

    }


}

?>
