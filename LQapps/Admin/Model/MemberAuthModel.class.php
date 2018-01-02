<?php
namespace Admin\Model;
use Think\Model;


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
    ///添加数据
    public function addData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $uid = $this->add($data);
        return $uid;
    }
    ///保存数据
    public function saveData($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        $uid = $this->save($data);
        return $uid;
    }

    //数据保存
    public function lqSubmit(){
        if(ACTION_NAME=='auth'){
            $back_url=U('member/index');
        }else{
            $back_url='';
        }
        return $this->lqCommonSave($back_url);

    }
    ///更新某字段
    public function upField($field,$val){
        return $this->setField($field,$val);
    }
}

?>
