<?php
/*
 * 老师控制面版模块
*/
namespace Home\Controller;

use Think\Controller;
use Video\Api\liveApi;
use Video\Api\vodApi;
use Video\Api\ossApi;



class ForgetController extends PublicController
{
    private  $model_auth, $model_live, $model_Apply, $model_vod, $model_lesson_live, $model_lesson_vod, $model_oss;

    /** 初始化*/
    public function __construct()
    {
        parent::__construct();
        $this->model_live = D("Live");
        $this->model_vod = D("Vod");

        $this->model_lesson_live = D("LessonLive");
        $this->model_lesson_vod = D("LessonVod");
        $this->model_oss = D("OssFile");

    }


    /*
     * 忘记密码步骤一
     */
    public function step1()
    {

        $this->display();
    }


    /*
 * 忘记密码步骤二
 */
    public function step2()
    {
        $this->display();
    }

    /*
* 忘记密码步骤三(1)
*/
    public function step3_1()
    {
        $this->display();
    }

    /*
* 忘记密码步骤三(2)
*/
    public function step3_2()
    {
        $this->display();
    }

    /*
* 忘记密码步骤四
*/
    public function step4()
    {
        $this->display();
    }




    // ******************************************接口*********************************************8

    /*
 *  学生 - 查询用户名是否存在(接口)
 */
    public function forget_account()
    {
        if (IS_POST) {
        $data['zc_account|zc_mobile|zc_email'] = I('post.account','');
        $res = M('Member')->where($data)->count();
            if($res){
                $this->ajaxReturn(array('status' => 1, 'msg' => '账号存在'));
            }else{
                $this->ajaxReturn(array('status' => 0, 'msg' => '账号不存在'));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }

    /*
  * 学生 - 忘记密码 - 修改密码(接口)
  */
    public function change_password()
    {
        if (IS_POST) {
            $zc_account = I('post.account','');// 账号
            if(!$zc_account) $this->ajaxReturn(array('status' => 0 , 'msg' => '未知错误'));
            $new_password = I('post.password', ''); // 新密码
                $where_sql['zc_account'] = $zc_account;
                $data['zc_password'] = ucenter_md5($new_password);
                $data['zn_mdate'] = NOW_TIME;
                $res = M('Member')->where($where_sql)->save($data);// 更新密码
                if ($res) {
                    $this->ajaxReturn(array('status' => 1, 'msg' => '密码修改成功' ,'url' => U('/Index/index')));
                }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '非法访问'));
        }
    }


}