<?php
/*后台首页
****
*/
namespace Admin\Controller;

use Think\Controller;
use LQLibs\Util\BootstrapForm as Form;//表单填充插件
use Member\Api\MemberApi as MemberApi;

//后台首页
class IndexController extends PublicController
{
    protected $userForm = array(
        'tab_title' => array(1 => '修改用户信息'),
        '1' => array(
            array('textShow', 'zc_account', "用户帐号", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('text', 'zc_email', "用户邮箱", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
            array('text', 'zc_mobile', "紧急联系电话", 1, '{"required":"1","dataType":"","dataLength":"","readonly":0,"disabled":0}'),
        ),
    );
    protected $passForm = array(
        'tab_title' => array(1 => '修改用户密码'),
        '1' => array(
            array('textShow', 'zc_account', "用户帐号", 1, '{"is_data":"0","creat_hidden":"0"}'),
            array('password', 'zc_password', "用户密码", 1, '{"required":"1","dataType":"password","dataLength":"","readonly":0,"disabled":0}'),
            array('password', 'zc_password_chk', "确认密码", 0, '{"required":"1","dataType":"","dataLength":"","confirm":"zc_password","readonly":0,"disabled":0}'),
        ),
    );

    public function __construct()
    {

        parent::__construct();
    }

    //首页
    public function index()
    {
        $this->display('index');
    }

    //粉丝与会员数据汇总
    protected function getDataFollow($date = '2017-01-01', &$list)
    {
        $start_time = strtotime($date . " 00:00:00");
        $end_time = strtotime($date . " 23:59:59");

        $sqlwhere = "zn_cdate >=" . $start_time . " and zn_cdate<=" . $end_time;
        $this->model_member = new MemberApi;//实例化会员
        $member = $this->model_member->apiListCount($sqlwhere);
        $room = M("Room")->where($sqlwhere)->count();
        $gamelog = M("GameLog")->where($sqlwhere)->count();
        $recharge = M("Recharge")->where($sqlwhere)->count();
        $consume = M("CardLog")->where($sqlwhere)->count();
        $chat = M("Chat")->where($sqlwhere)->count();


        $list["label"][] = $date;
        $list["datasets"]["member"][] = $member;
        $list["datasets"]["room"][] = $room;
        $list["datasets"]["gamelog"][] = $gamelog;
        $list["datasets"]["recharge"][] = $recharge;
        $list["datasets"]["consume"][] = $consume;
        $list["datasets"]["chat"][] = $chat;

        $datasets = array();
        $datasets["member"] = $member;
        $datasets["room"] = $room;
        $datasets["gamelog"] = $gamelog;
        $datasets["recharge"] = $recharge;
        $datasets["consume"] = $consume;
        $datasets["chat"] = $chat;

        return array('list' => $list, 'datasets' => $datasets);
    }

    //家装订单数据汇总
    protected function getHdOrder($date = '2017-01-01', &$list)
    {
        $start_time = strtotime($date . " 00:00:00");
        $end_time = strtotime($date . " 23:59:59");

        $list["label"][] = $date;
        $list["datasets"]["hd_application"][] = M("hd_application")->where("zn_cdate >=" . $start_time . " and zn_cdate<=" . $end_time)->count();
        $list["datasets"]["hd_order"][] = M("hd_order")->where("zn_cdate >=" . $start_time . " and zn_cdate<=" . $end_time)->count();
        $list["datasets"]["hd_order_service"][] = M("hd_order_service")->where("zn_cdate >=" . $start_time . " and zn_cdate<=" . $end_time)->count();

        return $list;
    }

    public function ajaxSearch()
    {
        $lcop = I("get.tcop", 'text');

        if ($lcop == 'text') {
            //一周数据
            $list = $new_list = array();
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-6 days')), $list);//6天前
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-5 days')), $data["list"]);//5天前
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-4 days')), $data["list"]);//4天前
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-3 days')), $data["list"]);//3天前
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-2 days')), $data["list"]);//2天前
            $data = $this->getDataFollow(date('Y-m-d', strtotime('-1 days')), $data["list"]);//1天前
            $yesterday_datasets = $data["datasets"];//昨日关注指数
            $data = $this->getDataFollow(date('Y-m-d'), $data["list"]);//今天
            $today_datasets = $data["datasets"];//今日关注指数
            $new_list = $data["list"];
            $new_list["yesterday_datasets"] = $yesterday_datasets;
            $new_list["today_datasets"] = $today_datasets;
            $this->ajaxReturn($new_list);
        } elseif ($lcop == 'hdorder') {
            //订单数据
            $list = array();
            for ($index = 30; $index >= 0; $index--) {
                $list = $this->getHdOrder(date('Y-m-d', strtotime("-$index days")), $list);//今天
            }
            $this->ajaxReturn($list);
        }
    }

    //修改当前用户信息
    public function modifyMyself()
    {
        //操作标识
        $action = I("get.action", 'info');
        if (IS_POST) {
            if ($action == 'pass') {
                $data = $this->UserModel->apiEditPass($this->login_admin_info["id"]);
            } elseif ($action == 'info') {
                $data = $this->UserModel->apiUpdateAdmin($this->login_admin_info["id"]);
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => "你妹的"));
            }
            if (preg_match('/^([1-9]\d*)$/', $data)) {
                if ($action == 'pass') {
                    $this->ajaxReturn($this->UserModel->apiLoginOut());
                } else {
                    $this->ajaxReturn(array('status' => 1, 'msg' => C('ALERT_ARRAY')["saveOk"], 'url' => U(CONTROLLER_NAME . '/modifyMyself/action/info')));
                }

            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => $data));
            }
        } else {

            //表单数据初始化s
            $form_array = array();
            $form_array["os_record_time"] = $this->osRecordTime($this->login_admin_info);//操作时间
            foreach ($this->login_admin_info as $lnKey => $laValue) {
                $form_array[$lnKey] = $laValue;
            }
            if ($action == 'info') {//修改用户信息
                $Form = new Form($this->userForm, $form_array, $this->UserModel->apiGetCacheComment());


            } elseif ($action == 'pass') {//修改用户密码
                $Form = new Form($this->passForm, $form_array, $this->UserModel->apiGetCacheComment());
            }

            $this->assign("LQFdata", $Form->createHtml());//表单数据
            //表单数据初始化e


            $this->display('admin-edit');
        }
    }

    public function clearCache()
    {
        $la_cache_array = array(
            1 => array("id" => 1, "title" => '模版缓存(Cache)'),
            2 => array("id" => 2, "title" => '数据缓存目录(Temp)'),
            3 => array("id" => 3, "title" => '日志目录(Logs)'),
            4 => array("id" => 4, "title" => '数据目录(Data)'),
            5 => array("id" => 5, "title" => '后台树装路径缓存目录(SYSTEM_CURRENT)'),
            6 => array("id" => 6, "title" => '后台用户数据缓存目录(SYSTEM_USER)'),
            7 => array("id" => 7, "title" => '公共数据集(COMMON_ARRAY)'),
        );
        $la_cache_dir = array(
            1 => RUNTIME_PATH . "Cache",
            2 => RUNTIME_PATH . "Temp",
            3 => RUNTIME_PATH . "Logs",
            4 => RUNTIME_PATH . "Data",
            5 => SYSTEM_CURRENT_PATH,
            6 => SYSTEM_USER_PATH,
            7 => COMMON_ARRAY,
        );

        //清除处理s
        if (IS_POST) {
            $Dir = new \LQLibs\Util\Dir();
            $dirs = $_POST['ids'];
            $lcmsg = '';
            @unlink(RUNTIME_PATH . "runCache.php");
            foreach ($dirs as $value) {
                $folder_path = $la_cache_dir[$la_cache_array[$value]["id"]];
                $Dir::delDir($folder_path);
                $lcmsg .= "成功清理 " . $la_cache_array[$value]["title"] . " 文件夹</br>";
                if (!is_dir($folder_path)) {
                    @mkdir($folder_path, 0777);
                    @copy(TPL_DEFAULT_HTML, $folder_path . "/index.html");
                }
            }

            //生成上传目录
            if (session('admin_auth')["id"] == 1) {
                $folder = WEB_ROOT . C("UPLOAD_PATH")["folder"];//上传文件夹
                @copy(TPL_DEFAULT_HTML, $folder . "/index.html");
                if (!LQFileExist($folder, 0)) {
                    @mkdir($folder, 0777);
                    @copy(TPL_DEFAULT_HTML, $folder . "index.html");
                }
                $files = C("UPLOAD_PATH")["list"];// uploadfiles 文件列表
                if ($files) {
                    foreach ($files as $Key => $Value) {
                        $current_dir = $folder . $Key;
                        if (!LQFileExist($current_dir, 0)) {
                            if (!is_dir($current_dir)) {
                                @mkdir($current_dir, 0777);
                                @mkdir($current_dir . "/_thumb/", 0777);
                                @copy(TPL_DEFAULT_HTML, $current_dir . "/index.html");
                            }
                        }
                    }
                }
            }

            if (in_array(7, $dirs)) {
                //生成所有表名
                $lqtables = M()->query('SHOW TABLE STATUS');
                $la_cache_table = array();
                foreach ($lqtables as $lnKey => $laValue) {
                    $la_cache_table[substr($laValue["name"], 3)] = $laValue["comment"];
                }
                F('sys_table', $la_cache_table, COMMON_ARRAY);
                D('Admin/AdminAction')->lqCacheData();//缓存操作数组
                D('Admin/SystemMenu')->lqCacheData();//后台架构管理数组缓存
                D('Admin/AdminRole')->lqCacheData();//缓存角色数组
                $this->comCacheData();//通用缓存

            }
            //首页系统菜单 s
            $this->UserModel->apiGetMenus(session('admin_auth')["id"]);

            //写入日志
            $log_data = array(
                'id' => "",
                'action' => ACTION_NAME,
                'table' => CONTROLLER_NAME,
                'url' => $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"],
                'operator' => session('admin_auth')["id"],
            );
            $this->UserModel->addAdminLog($log_data);
            $this->success($lcmsg, U("Index/index"));
        }//清除处理e


        $this->assign("cache_item", $la_cache_array);
        $this->display();
    }

    //ajax清缓存
    public function quickClearCache()
    {
        $Dir = new \LQLibs\Util\Dir();
        $la_cache_dir = array(
            1 => RUNTIME_PATH . "Cache",
            2 => RUNTIME_PATH . "Temp",
            3 => RUNTIME_PATH . "Logs",
            4 => RUNTIME_PATH . "Data",
        );
        foreach ($la_cache_dir as $k => $v) {
            $Dir::delDir($la_cache_dir[$k]);
        }
        @unlink(RUNTIME_PATH . 'runCache.php');

        //检查缓存目录(Runtime) 如果不存在则自动创建
        $Build = new \Think\Build;
        $Build::buildRuntime();
        $Build::buildDirSecure($dirs);

        //写入日志
        $log_data = array(
            'id' => "",
            'action' => ACTION_NAME,
            'table' => CONTROLLER_NAME,
            'url' => $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"],
            'operator' => session('admin_auth')["id"],
        );
        $this->UserModel->addAdminLog($log_data);

        //首页系统菜单 s
        $this->UserModel->apiCacheInfo(session('admin_auth')["id"]);
        $this->UserModel->apiGetMenus(session('admin_auth')["id"]);
        //首页系统菜单 e


        D('Admin/SystemMenu')->lqCacheData();//后台架构管理数组缓存
        $this->comCacheData();//通用缓存

        $this->ajaxReturn(array('status' => 1, 'msg' => "成功清除缓存", 'url' => __APP__));
    }

    //通用缓存
    private function comCacheData()
    {
        //清记忆 S
        lq_post_memory_data(1);
        session('index_current_url', NULL);
        //清记忆 E
        D('Admin/WebConfig')->lqCacheData();//基本设置
        D('Admin/AdPosition')->lqCacheData();//图册位置
        D('Admin/ArticleCat')->lqCacheData();//文章分类
        D('Admin/Region')->lqCacheData();//缓存区域数组
    }

}

?>