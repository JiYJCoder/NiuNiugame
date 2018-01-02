<?php
return array(
    'TOKEN_ON' => false, // 是否开启令牌验证
    'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET' => true, //令牌验证出错后是否重置令牌 默认为t
	'PAGESIZE'=>10,//每页显示数目
	'TMPL_TEMPLATE_SUFFIX' => '.htm', //模板文件后缀
	
	//url重定义
	'VAR_MODULE' => 'g', // 默认模块获取变量
	'URL_MODEL'  =>  0,  //URL访问模式
	'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小
	
	'SHOW_RUN_TIME'    => false, // 运行时间显示
	'SHOW_ADV_TIME'    => false, // 显示详细的运行时间
	'SHOW_DB_TIMES'    => false, // 显示数据库查询和写入次数
	'SHOW_CACHE_TIMES' => false, // 显示缓存操作次数
	'SHOW_USE_MEM'     => false, // 显示内存开销
	'SHOW_LOAD_FILE'   => false, // 显示加载文件数
	'SHOW_FUN_TIMES'   => false, // 显示函数调用次数
	'SHOW_ADV_TIME'=> false, // 关闭详细的运行时间
	
	
	
		
);