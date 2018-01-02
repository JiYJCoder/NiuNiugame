<?php //短信系统 Article 数据处理，数据回调
namespace Home\Model;
use Think\Model;
use Video\Api\ossApi;

defined('in_lqweb') or exit('Access Invalid!');

class OssFileModel extends PublicModel
{

    /* 会员模型自动验证 */
    protected $_validate = array(

        // array('zn_uid', 'require ', '上传用户id错误！', self::MUST_VALIDATE),
//        array('zc_nickname', 'require ', '上传老师姓名错误！', self::MUST_VALIDATE),
//        array('zn_fid', 'lqrequire', '主课程id错误！', self::MUST_VALIDATE),
//        array('zn_lesson_id', 'lqrequire', '课节id错误', self::MUST_VALIDATE),
//        array('zc_suffix', 'require', '文件后缀类型错误', self::MUST_VALIDATE),
//        array('zn_size', 'lqrequire', '文件大小错误', self::MUST_VALIDATE),
    );

    /* 会员模型自动完成 */
    protected $_auto = array(

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


    ////获取object名
    public function getObject($file_id)
    {
        return $this->lqGetField("id=".$file_id,"zc_object");
    }

    /////获取ojbect名 ,并删除本地数据库记录
    public function getAndDelObj($file_id)
    {
        $object = $this->getObject($file_id);
        $this->where("id=".$file_id)->delete();

        $oss_api = new ossApi();
        $oss_api->deleteObject($object);
        return true;
    }

    // 获取数据
    public function lq_getmessage($sql = array('field' => '*', 'where' => ' id>1 ', 'order' => '`id` DESC'))
    {
        // 选择字段,设置条件,进行排序,分页限制,查询
        $list = $this->field($sql["field"])->where($sql["where"])->order($sql["order"])->select();

        return $list;

    }
}

?>
