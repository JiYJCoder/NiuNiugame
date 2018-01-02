<?php  
/*
项目：狸想家【建誉集团】
开发日期始于：20161108
作者:国雾院theone(438675036@qq.com)、狸想家精英团队
说明:基于 ThinkPHP3.2.3 框架开发, 应用入口文件
家装:hd(home-decoration)
管家:hk(house-keeper)
*/

define("GZIP_ENABLE",function_exists ( 'ob_gzhandler'));
ob_start(GZIP_ENABLE?'ob_gzhandler':null);

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('in_lqweb', true);//系统安全锁
define('APP_DEBUG', true);//ThinkPHP调试模式 默认为关闭状态
define('CODE_TEST', 0);//框架原始 - 整体执行时间测试
define('MYSQL_TEST', 0);//1 输出mysql
define('ACTION_OPEN', 0);//1 action 收集开启
define('ALLOW_IP_OPEN',0);//后台启用IP登录受权

//网站当前路径
define('SITE_PATH', getcwd());
define("WEB_ROOT_BY_FILE", dirname(__FILE__));
define("WEB_ROOT", dirname(__FILE__) . "/");
define("REL_ROOT", "/"); 
define("API_DOMAIN", "http://www.1jianfa.com/"); 

// 定义应用目录
define('APP_PATH', './LQapps/'); 
//应用公共目录
define("APP_COMMON_PATH", SITE_PATH . "/LQapps/Common/");
//应用运行缓存目录
define("RUNTIME_PATH", SITE_PATH . "/Runtime/");
//后台用户数据缓存目录
define("SYSTEM_USER_PATH", RUNTIME_PATH . "SysUser/");
//后台树装路径缓存目录
define("SYSTEM_CURRENT_PATH", RUNTIME_PATH . "SysCurrent/");
//临时文件存放目录
define('STATIC_TEMP',WEB_ROOT."Public/Static/temp/");
//公共数据集
define("COMMON_ARRAY", RUNTIME_PATH . "Array/");
//前端模板目录
define('HOME_VIEW_PATH', './Tpl-website/'); 
define('HOME_VIEW_PATH_TMPL', 'Tpl-website');
//微信证书路径,注意应该填写绝对路径
define("WECHAT_SSLCERT_PATH", STATIC_TEMP.'cacert/apiclient_cert.pem');
define("WECHAT_SSLKEY_PATH", STATIC_TEMP.'cacert/apiclient_key.pem');

//app 微信证书路径,注意应该填写绝对路径
define("APP_WECHAT_SSLCERT_PATH", STATIC_TEMP.'appcert/apiclient_cert.pem');
define("APP_WECHAT_SSLKEY_PATH", STATIC_TEMP.'appcert/apiclient_key.pem');

//空白HTML、HTACCESS,暂无图片
define("TPL_DEFAULT_HTML", WEB_ROOT . "Public/index.html"); 
define("TPL_HTACCESS", WEB_ROOT . "Public/ThinkPHP/.htaccess"); 
define("NO_PICTURE", API_DOMAIN .REL_ROOT. "Public/Static/images/no-pic.png"); 
define("NO_PICTURE_ADMIN", API_DOMAIN .REL_ROOT. "Public/Static/images/no-pic-admin.png"); 
define("NO_HEADIMG", API_DOMAIN .REL_ROOT. "Public/Static/images/noavatar.png"); 
define("NO_BANNER", API_DOMAIN .REL_ROOT. "Public/Static/images/page_banner.png"); 

//启用隐藏 index.php 1开启 0关闭
define("HIDDEN_INDEX_FILE", 1); 
define("INDEX_FILE_NAME",'do');
//加载框架入文件
define('THINK_PATH', './Public/ThinkPHP/');//ThinkPHP架构主体
define('HTML_PATH','./Runtime/Html');
require THINK_PATH.'ThinkPHP.php';