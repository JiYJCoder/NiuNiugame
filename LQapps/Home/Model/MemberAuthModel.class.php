<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;

defined('in_lqweb') or exit('Access Invalid!');

class MemberAuthModel extends PublicModel
{


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

    ////是否已有认证数据
    public function isAuth($uid)
    {
        return $this->where("zn_member_id=".$uid)->getField('id');
    }

    ///是否通过验证
    public function isAuthOk($uid)
    {
        return $this->where("zn_member_id=".$uid)->getField('zl_is_auth');
    }

    ///保存数据
    public function addData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $mid = $this->add($data);
        return $mid ? $mid : 0; //0-未知错误，大于0-注册成功
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

}

?>
