<?php //系统后台 -公共配置
//项目配置文件
return array(
	//'配置项'=>'配置值'
    'TOKEN_ON' => true, // 是否开启令牌验证
    'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET' => true, //令牌验证出错后是否重置令牌 默认为true
	
	
	// 设置默认的模板主题
	'DEFAULT_THEME'  => 'default',
	'TMPL_DETECT_THEME' => true, // 自动侦测模板主题
    'TPL_WEBSITE_USE' =>false,//开启 Tpl-website 模板内容优化 theone

	//页面公共数据集s
	'COM_SORT_NUM'=>'100',//后台默认排序
	'COM_PRICE'=>'0.00',//后台默认金额
	'PAGESIZE'=>10,//每页显示数目
	
	'SYSMUEN_MODEL'=>array(1=>'项目（GROUP_NAME）','系统(SYSTEM)','归类(CLASSIFY)','控制器名(CONTROLLER_NAME)','操作(ACTION_NAME)','链接(LINK)'),//系统架构-节点类型
	'USE_STATUS'=>array('禁用','启用'),//使用情况
	'FINISH_STATUS'=>array('未直播','已直播'),//使用情况
	'INSTALL_STATUS'=>array('卸载','安装'),//安装情况
	'DISPLAY_STATUS'=>array('隐藏','显示'),//可视状态
	'POPLABEL'=>array('绿色','加权'),//权限通道
	'ICONS_ARRAY'=>array('edit'=>'<i class="fa fa-edit"></i>','approve'=>'<i class="fa fa-check-square ac_grey"></i>','unapprove'=>'<i class="fa fa-minus-square"></i>','del'=>'<i class="fa fa-times-circle"></i>','yesuser'=>'<i class="fa fa-unlock"></i>','nouser'=>'<i class="fa fa-lock"></i>'),//图标集
	//页面公共数据集e

	'SHOW_RUN_TIME'    => true, // 运行时间显示
	'SHOW_ADV_TIME'    => true, // 显示详细的运行时间
	'SHOW_DB_TIMES'    => true, // 显示数据库查询和写入次数
	'SHOW_CACHE_TIMES' => true, // 显示缓存操作次数
	'SHOW_USE_MEM'     => true, // 显示内存开销
	'SHOW_LOAD_FILE'   => true, // 显示加载文件数
	'SHOW_FUN_TIMES'   => true, // 显示函数调用次数
	'SHOW_ADV_TIME'=> true, // 关闭详细的运行时间
	
	'INDEX_LOCK'=>array('编辑锁','删除锁','审核锁','缓存锁','查找锁','排序锁'),//可视状态
	'FORM_CONTROLS' => array("text"=>"text","select"=>"select","checkbox"=>"checkbox","radio"=>"radio","file"=>"file","textarea"=>"textarea","editor"=>"editor"),//基本设置-控件类型
    
	//用户SESSION过期时间
	'ADMIN_SESSION_EXPIRE'=>3600*8,
 	'SESSION_OPTIONS' => array('expire'=>3600),

);
