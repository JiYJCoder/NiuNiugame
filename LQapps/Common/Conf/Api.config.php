<?php
/*
API 配置文件
*/

/**
 * ADMIN端配置文件
 * 注意：该配置文件请使用常量方式定义
 */
define('ADMIN_APP_ID', 2); //应用ID
define('ADMIN_API_TYPE', 'Model'); //可选值 Model / Service
define('ADMIN_DB_DSN','mysql://root:root@127.0.0.1:3306/video_hd#utf8');

/**
 * MEMBER端配置文件
 * 注意：该配置文件请使用常量方式定义
 */
define('MEMBER_APP_ID', 3); //应用ID
define('MEMBER_API_TYPE', 'Model'); //可选值 Model / Service
define('MEMBER_DB_DSN','mysql://root:root@127.0.0.1:3306/video_hd#utf8');

//加密固定值
define('AUTH_KEY', 'fuck`_eht;R:3ij5y}xnoQ#"JsgK>Zm(,T./<4[X%'); //加密KEY
define('SALT', rand(100000,999999));

//附件配置
define('ATTACHMENT_APP_ID', 4); //应用ID
define('ATTACHMENT_API_TYPE', 'Model'); //可选值 Model / Service


?>