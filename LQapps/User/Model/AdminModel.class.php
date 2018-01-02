<?php
/*
用户模型（admin）
*/
namespace User\Model;

use Think\Model;

/**
 * 会员模型
 */
class AdminModel extends Model
{
    protected $model_log;
    //protected $connection =ADMIN_DB_DSN;//数据库连接信息

    //菜单表
    public $menu = "";

    /* 用户模型自动验证 */
    protected $_validate = array(
        /* 验证用户名 */
        array('zc_account', 'checkAccountRule', "用户名不合法:仅允许英文字母及数字或@.[4-30个字符]", self::EXISTS_VALIDATE, 'callback'), //用户名规则
        array('zc_account', '', '用户名被占用', self::EXISTS_VALIDATE, 'unique'), //用户名被占用
        /* 验证密码 */
        array('zc_password', 'checkPasswordRule', "用户密码不合法:仅允许英文字母，（@#$!*）及数字[6-30个字符]", self::EXISTS_VALIDATE, 'callback'), //密码规则
        /* 验证邮箱 */
        array('zc_email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE), //邮箱格式不正确
        array('zc_email', '1,32', '邮箱长度不合法', self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
        array('zc_email', '', "邮箱被占用", self::EXISTS_VALIDATE, 'unique'), //邮箱被占用
        /* 验证手机号码 */
        array('zc_mobile', '//', '手机格式不正确', self::EXISTS_VALIDATE), //手机格式不正确 TODO:
        array('zc_mobile', '', '手机号被占用', self::EXISTS_VALIDATE, 'unique'), //手机号被占用
        array('zc_name', 'require', '姓名必须填写！', self::EXISTS_VALIDATE),

        /* 其他 */
        array('zn_role_id', 'number', '用户角色要为数字', self::EXISTS_VALIDATE),
        array('zn_role_id', '1,3', '用户角色长度不合法', self::EXISTS_VALIDATE, 'length'),
        array('zn_login_times', 'number', '登录次数要为数字', self::EXISTS_VALIDATE),
        array('zn_login_times', '1,11', '登录次数长度不合法', self::EXISTS_VALIDATE, 'length'),
        array('zn_trylogin_times', 'number', '尝试登陆次数要为数字', self::EXISTS_VALIDATE),
        array('zn_trylogin_times', '1,11', '尝试登陆次数长度不合法', self::EXISTS_VALIDATE, 'length'),
        array('zn_last_login_time', 'number', '最后登录时间要为数字', self::EXISTS_VALIDATE),
        array('zn_trylogin_lasttime', 'number', '最后尝试登陆时间要为数字', self::EXISTS_VALIDATE),
        array('zl_visible', 'number', 'zl_visible要为数字', self::EXISTS_VALIDATE),
    );

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('zc_password', 'lq_ucenter_md5', self::MODEL_INSERT, 'function', SALT),
        array('zc_salt', SALT, self::MODEL_INSERT),
        array('zc_popedom', 'lqNull', self::MODEL_INSERT, 'function'),
        array('zn_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('zl_visible', '1', self::MODEL_INSERT),
        array('zn_cdate', NOW_TIME, self::MODEL_INSERT),
        array('zn_mdate', NOW_TIME, self::MODEL_BOTH),);

    public function __construct()
    {
        parent::__construct();
        $this->model_log = M("admin_log");//日志-模型
    }

    //获取表单数据
    protected function getPostData()
    {
        //表单数据构建
        $data = I("post.LQF");
        if ($data["id"]) {
            //记录更新时间
            $data = array_merge($data, array('zn_mdate' => NOW_TIME));
        } else {
            //记录插入时间
            $data = array_merge($data, array('zn_cdate' => NOW_TIME, 'zn_mdate' => NOW_TIME));
        }
        return $data;
    }

    /**
     * 检测用户名是不是合法
     * @param  string $zc_account 用户名
     * @return boolean          ture - 可用，false - 不可用
     */
    protected function checkAccountRule($zc_account)
    {
        return isAccount($zc_account);
    }

    /**
     * 检测密码是不是合法
     * @param  string $zc_account 密码
     * @return boolean          ture - 可用，false - 不可用
     */
    protected function checkPasswordRule($zc_password)
    {
        return isPassword($zc_password);
    }

    /**
     * 检测用户信息
     * @param  string $field 用户名
     * @param  integer $type 用户名类型 1-用户名，2-用户邮箱，3-用户电话
     * @return integer         错误编号
     */
    public function checkField($field, $type = 1)
    {
        $data = array();
        switch ($type) {
            case 1:
                $data['zc_account'] = $field;
                break;
            case 2:
                $data['zc_email'] = $field;
                break;
            case 3:
                $data['zc_mobile'] = $field;
                break;
            default:
                return 0; //参数错误
        }
        return $this->create($data) ? 1 : $this->getError();
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email 用户邮箱
     * @param  string $mobile 用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email, $mobile)
    {
        $data = array(
            'zc_account' => $username,
            'zc_password' => $password,
            'zc_email' => $email,
            'zc_mobile' => $mobile,
        );

        //验证手机
        if (empty($data['zc_mobile'])) unset($data['zc_mobile']);

        /* 添加用户 */
        if ($data = $this->create($data)) {
            $uid = $this->add($data);
            return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
        } else {
            return $this->getError(); //错误详情见自动验证注释
        }
    }

    /**
     * uid-auth 登录认证
     * @param  int $uid 用户ID
     * @param  string $auth 用户auth
     * @return bool    登录成功  true or false
     */
    public function uidAuth($uid, $auth)
    {
        if (!$uid | !$auth) {
            return false;
        }
        $user = $this->where(" zl_visible=1 and id=" . intval($uid))->field('id,zc_account,zn_last_login_time')->find();
        if ($user) {
            $auth_array = array(
                'id' => $user['id'],
                'zc_account' => $user['zc_account'],
                'zn_last_login_time' => $user['zn_last_login_time'],
            );
            if (lq_data_auth_sign($auth_array) == $auth) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * 用户登录认证
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @param  integer $sha1 sha1加密开启 （1-开，0-关）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 1, $sha1 = 1)
    {
        if (!isAccount($username) && $type == 1) return -1; //帐号不合法
        if (!isEmail($username) && $type == 2) return -1; //邮箱不合法
        if (!isMobile($username) && $type == 3) return -1; //电话号码不合法

        $map = array();
        switch ($type) {
            case 1:
                $map['zc_account'] = $username;
                break;
            case 2:
                $map['zc_email'] = $username;
                break;
            case 3:
                $map['zc_mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        /* 获取用户数据 */
        $user = $this->where($map)->field("`id`,`zc_account`,`zc_email`,`zc_mobile`,`zc_password`,`zc_salt`,`zn_trylogin_times`,`zn_trylogin_lasttime`,`zl_visible`")->find();
        if (is_array($user) && $user['zl_visible']) {
            $lntime_interval = NOW_TIME - $user["zn_trylogin_lasttime"];
            //return $lntime_interval;
            if (($lntime_interval > C("WEB_SYS_TRYLOGINAFTER")) && ($user["zn_trylogin_times"] >= C("WEB_SYS_TRYLOGINTIMES"))) {
                $this->updateTryLogin($user['id'], 0); //更新用户尝试登录信息
                $user["zn_trylogin_times"] = 0;
            }
            //系统对登录最大次数处理
            if ($user["zn_trylogin_times"] >= C("WEB_SYS_TRYLOGINTIMES")) return -3;
            /* 验证用户密码 */
            if (lq_ucenter_md5($password, $user['zc_salt'], $sha1) === $user['zc_password']) {
                $this->updateLogin($user['id']); //更新用户登录信息
                $this->lqCacheInfo($user["id"]);//缓存当前用户信息
                return $user['id']; //登录成功，返回用户ID
            } else {
//                lq_test(lq_ucenter_md5($password,$user['zc_salt'],$sha1));
//               lq_test($user['zc_password']);
                $this->updateTryLogin($user['id']); //更新用户尝试登录信息
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    //通过ID获取用户信息
    public function lqGetInfoByID($uid)
    {
        $map_sql = "id = " . intval($uid);
        //角色数据
        $admin_role = F('admin_role', '', COMMON_ARRAY);
        $user = $this->where($map_sql)->field('*')->find();
        if (!$user) return 0;
        $user["zn_role_id_label"] = $admin_role[$user["zn_role_id"]]["title"];
        return $user;
    }

    public function lqCacheInfo($uid)
    {
        $user = $this->lqGetInfoByID($uid);
        if ($user) {
            unset($user["zc_password"]); //除去密码缓存
            unset($user["zc_salt"]); //除去密码缓存
            $user["zc_popedom"] = $this->getMenus($uid);
            S('SYSTEM_USER', $user, array('prefix' => $uid . C("S_PREFIX"), 'temp' => SYSTEM_USER_PATH));
            $this->getMenus(session('admin_auth')["id"]);//用户菜单集
            return $user;
        } else {
            return 0;
        }
    }

    /**
     * 获取用户信息
     * @param  string $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function lqGetInfo($uid, $is_username = false)
    {
        if ($is_username) { //通过用户名获取
            $uid = intval($this->where("zc_account='" . $uid . "'")->getField('id'));
        }
        $user = S('SYSTEM_USER', '', array('prefix' => $uid . C("S_PREFIX"), 'temp' => SYSTEM_USER_PATH));
        if (empty($user)) {
            $user = $this->lqCacheInfo($uid);
        }
        if (is_array($user)) {
            return $user;
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 尝试登录,更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateTryLogin($uid, $times = array('exp', 'zn_trylogin_times+1'))
    {
        $data = array(
            'id' => $uid,
            'zn_mdate' => NOW_TIME,
            'zn_trylogin_times' => $times,//尝试登录次数
            'zn_trylogin_lasttime' => NOW_TIME,
        );
        $this->save($data);
    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateLogin($uid)
    {
        $data = array(
            'id' => $uid,
            'zn_login_times' => array('exp', 'zn_login_times+1'),
            'zn_last_login_time' => NOW_TIME,
            'zn_mdate' => NOW_TIME,
            'zn_trylogin_times' => 0,//尝试登陆
            'zn_trylogin_lasttime' => NOW_TIME,
        );
        $this->save($data);
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function lqUpdateInfo($uid, $password, $data)
    {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = '参数错误！';
            return false;
        }
        //更新前检查用户密码
        if (!$this->verifyUser($uid, $password)) {
            $this->error = '验证出错：密码不正确！';
            return false;
        }

        //更新用户信息
        $data = $this->create($data);
        if (data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }

    /**
     * 验证用户密码
     * @param int $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     */
    protected function verifyUser($uid, $password_in)
    {
        $user = $this->where(array('id' => $uid))->field('id,zc_account,zc_password,zc_salt')->find();
        if (lq_ucenter_md5($password_in, $user['zc_salt']) === $user["zc_password"]) {
            return true;
        }
        return false;
    }

    /**
     * 检测访问权限
     * @param int $user 用户信息
     * @return integer         错误编号
     */
    public function lqAccessControl($user)
    {
        $uid = $user["id"];
        $role_id = $user["zn_role_id"];
        if ($uid == 1) return 1;// 原始管理员 授权访问
        $user_popedom_ids = $this->getPopedom($uid);
        $access_array = F('accessControl', '', COMMON_ARRAY);
        if (!$access_array) return -1;//权值表为空或

        $run_table = CONTROLLER_TO_TABLE(CONTROLLER_NAME);

        $control_id = $access_array[$run_table];

        if (empty($control_id)) return -2;//404:无页面

        if (in_array($control_id, explode(',', $user_popedom_ids))) {
            $return_value = 1;//授权访问
        } else {
            if ($access_array["access_check_pop"][$run_table] == 0) {
                $return_value = 1;//免检查权限
            } else {
                return 0;//未授权访问
            }
        }

       /* //操作认证 ACTION_NAME
        if ($return_value == 1) {
            $action_array = F('admin_role', '', COMMON_ARRAY)[$role_id]["action_list"];
            if (in_array(ACTION_NAME, explode(',', $action_array))) {
                $return_value = 1;//授权访问
            } else {
                if (substr(ACTION_NAME, 0, 2) == 'op' | substr(ACTION_NAME, 0, 4) == 'ajax') {
                    $return_value = -4;//未授权访问
                } else {
                    $return_value = 0;//未授权访问
                }
            }
        }*/
        return $return_value;
    }


    //实时获取 权限id集
    protected function getPopedom($uid)
    {
        return $this->where('id=' . intval($uid))->getField('zc_popedom');
    }

    /**
     * 系统模块
     * @return array    菜单数组
     */
    protected function getModuleList($uid)
    {
        //系统模块 ******************************** e
        $sqlwhere_parameter = '';
        if ($uid == 1) {
            $sqlwhere_parameter = '';
        } else {
            $user_popedom_ids = $this->getPopedom($uid);
            if (empty($user_popedom_ids) || $user_popedom_ids == '') return array();
            $user_popedom = explode(',', $user_popedom_ids);
            $sqlwhere_parameter = " and id in($user_popedom_ids)";
        }
        $system_list = M("system_menu")->field("`id`,`zn_fid`,`zc_caption`")->order('zn_sort ASC,id ASC')->where("zn_fid=1 and zl_is_menu=1" . $sqlwhere_parameter)->select();
        return $system_list;
        //系统模块 ******************************** e
    }

    /**
     * 菜单节点
     * @return array    菜单数组
     */
    protected function getNode($fid = 0, $where = '')
    {
        //菜单节点 ******************************** e
        $list = M("system_menu")->field("`id`,`zn_fid`,`zc_caption`,`zc_run_table`,`zc_run`,`zn_type`,`zc_target`")->order('zn_sort ASC,id ASC')->where(" zl_visible=1 and zl_is_menu=1 and zn_fid=" . $fid . $where)->select();
        if ($list)
            return $list;
        else
            return array();
        //菜单节点 ******************************** e
    }

    /**
     * 菜单组合
     * @param  string $uid 用户
     * @return array                菜单数组
     */
    protected function getMenuList($uid, $mid)
    {
        $sqlwhere_parameter = '';
        if ($uid == 1) {
            $sqlwhere_parameter = '';
        } else {
            $user_popedom_ids = $this->getPopedom($uid);
            if (empty($user_popedom_ids) || $user_popedom_ids == '') return array();
            $user_popedom = explode(',', $user_popedom_ids);
            $sqlwhere_parameter = " and id in($user_popedom_ids)";
        }
        $la_menu_data = $this->getNode($mid, $sqlwhere_parameter);
        foreach ($la_menu_data as $lnKey => $laValue) {//一环s
            $child = $this->getNode($laValue["id"], $sqlwhere_parameter);
            foreach ($child as $k => $v) {//一环s
                if ($v["zn_type"] == 6) {
                    $child[$k]["run"] = $v["zc_run"];
                } else {
                    $child[$k]["run"] = U($v["zc_run"]);
                }
            }
            $la_menu_data[$lnKey]["child"] = $child;
        }//一环e
        return $la_menu_data;
    }


    /**
     * 用户菜单
     * @param  string $uid 用户
     * @return array                菜单数组
     */
    public function getMenus($uid)
    {
        $menu = array();
        $la_module_list = $this->getModuleList($uid);
        foreach ($la_module_list as $lnKey => $laValue) {
            $index++;
            if ($index == 1) {
                $menu[$laValue["id"]]["system_style"] = ' style="display:block;"';
            } else {
                $menu[$laValue["id"]]["system_style"] = ' style="display:none;"';
            }
            $menu[$laValue["id"]]["system_title"] = $laValue["zc_caption"];
            $menu[$laValue["id"]]["system_run"] = $laValue["zc_run"];
            $menu[$laValue["id"]]["system_menu"] = $this->getMenuList($uid, $laValue["id"]);
        }
        $array = array(
            'menu' => $menu,
            'system' => $la_module_list,
        );
        return $array;
    }


    /**
     * 条件-用户列表总数
     */
    public function lqListCount($sqlwhere_parameter)
    {
        return $this->where($sqlwhere_parameter)->count();
    }


    /**
     * 用户列表
     * @param  firstRow int               记录开始
     * @param  listRows int               记录条数
     * @param  page_config  array         配置数组
     */
    public function lqList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => ' 1 ', 'order' => '`id` DESC'))
    {
        $la_role_id_data = F('admin_role', '', COMMON_ARRAY);
        $list = $this->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
            $list[$lnKey]['role_label'] = $la_role_id_data[$laValue['zn_role_id']]["title"];
            $list[$lnKey]['visible_label'] = $laValue['zl_visible'] == 1 ? C("USE_STATUS")[1] : C("USE_STATUS")[0];
            $list[$lnKey]['visible_button'] = $laValue['zl_visible'] == 1 ? C("ICONS_ARRAY")['unapprove'] : C("ICONS_ARRAY")['approve'];
            $list[$lnKey]['no'] = $firstRow + $lnKey + 1;
        }
        return $list;
    }

    //获取数据表 字段注释 theone 2015-01-10 add
    public function lqGetCacheComment()
    {
        $db = $this->dbName ?: C('DB_NAME');
        $cache_comment = F('_theone/' . strtolower($db . '.' . $this->tablePrefix . $this->name), '');
        return $cache_comment["_comment"];
    }

    /**
     * 新增用户信息
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function lqInsertAdmin()
    {
        $data = $this->getPostData();
        if (empty($data)) {
            return '参数错误！';
        }

        //验证新增
        $data = $this->create($data);
        if (!$data) {
            return $this->getError();
        } else {
            $uid = $this->add();
            return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
        }
        return '不明错误！';
    }

    /**
     * 更新用户信息
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function lqUpdateAdmin($uid)
    {
        $data = $this->getPostData();
        if ($uid) $data["id"] = $uid;
        if (empty($data)) {
            return '参数错误！';
        }
        if ($this->original_admin($data["id"]) == 0) return '未受权访问！';
        $data = $this->create($data);//验证更新
        if (!$data) {
            return $this->getError();
        } else {
            unset($data["__hash__"]);
            $this->save($data);
            $this->lqCacheInfo($data["id"]);
            return $data["id"];
        }
        return '不明错误！';
    }

    public function lqSaveAdmin($data)
    {
        if (empty($data)) {
            return '参数错误！';
        }
        C('TOKEN_ON', false);//暂时关闭
        $data = $this->create($data);//验证更新
        if (!$data) {
            return $this->getError();
        } else {
            $this->save($data);
            $this->lqCacheInfo($data["id"]);
            return $data["id"];
        }
        return '不明错误！';
    }

    /**
     * 更新用户密码
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function lqEditPass($uid)
    {
        $data = $this->getPostData();
        if ($uid) $data["id"] = $uid;
        if (empty($data)) {
            return '参数错误！';
        }
        if ($this->original_admin($data["id"]) == 0) return '未受权访问！';
        //验证更新
        $data = $this->create($data);
        if (!$data) {
            return $this->getError();
        } else {
            unset($data["__hash__"]);
            $data["zc_salt"] = SALT;
            $data["zc_password"] = lq_ucenter_md5($data["zc_password"], SALT);
            $this->save($data);
            $this->lqCacheInfo($data["id"]);
            return $data["id"];
        }
        return '不明错误！';
    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options)
    {
        $this->lqCacheInfo($data["id"]);
    }

    //除原始管理员可以操作自己，其他人不得处理
    public function original_admin($uid)
    {
        $returnok = 1;
        if ($uid == 1) {
            if (session('admin_auth')["id"] == $uid) {
                $returnok = 1;
            } else {
                $returnok = 0;
            }
        } else {
            $returnok = 1;
        }
        return $returnok;
    }

    //单记录删除
    public function lqDelete($uid)
    {
        $data["id"] = $uid;
        $la_check_data = $this->field("id")->where(" zl_visible=1 and id=" . (int)$data["id"])->find();
        if ($la_check_data) {//记录使用状态提示
            return -1;
        }
        return $this->where($data)->delete();
    }

    //多记录删除
    public function lqDeleteCheckbox($uids)
    {
        $data["id"] = array('in', $uids);
        $data["zl_visible"] = array('eq', 1);
        $la_check_data = $this->field("id")->where($data)->select();
        if ($la_check_data) {//记录使用状态提示
            return -1;
        }
        unset($data["zl_visible"]);
        return $this->where($data)->delete();
    }

    // 删除成功后的回调方法
    protected function _after_delete($data, $options)
    {
        $where = array();
        $where["zn_operator"] = $data["id"];
        $this->model_log->where($where)->delete();//日志
    }

    //用户日志
    public function lqAdminLog($log)
    {
        $this->model_log->add($log);
    }

    //条件-日志列表总数
    public function lqLogCount($sqlwhere_parameter)
    {
        return $this->model_log->where($sqlwhere_parameter)->count();
    }

    //条件-日志列表
    public function lqLogList($firstRow = 0, $listRows = 20, $page_config = array('field' => '*', 'where' => '', 'order' => '`id` DESC'))
    {
        $list = $this->model_log->field($page_config["field"])->where($page_config["where"])->order($page_config["order"])->limit("$firstRow , $listRows")->select();
        foreach ($list as $lnKey => $laValue) {
            $list[$lnKey]['ip'] = long2ip($laValue["zn_ip"]);
            $list[$lnKey]['description'] = lq_cutstr($laValue["zc_description"], 60);
            $list[$lnKey]['no'] = $firstRow + $lnKey + 1;
        }
        return $list;
    }

    //日志多记录删除
    public function lqDeleteLog($mids)
    {
        $data["id"] = array('in', $mids);
        return $this->model_log->where($data)->delete();
    }


}
