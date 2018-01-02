<?php
return array(
    'TOKEN_ON' => false, // 是否开启令牌验证
    'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET' => true, //令牌验证出错后是否重置令牌 默认为t

    //视图目录
    'VIEW_PATH' => HOME_VIEW_PATH,
    // 设置默认的模板主题
    'DEFAULT_THEME' => 'pc',//当模块中没有设置主题，则模块主题会设置为此处设置的主题,主题名和模块名不能重复，如不能采用“Home”
    'TMPL_DETECT_THEME' => true, // 自动侦测模板主题
    'URL_MODEL' => 1, // 如果你的环境不支持PATHINFO 请设置为3
    'TPL_WEBSITE_USE' => true,//开启 Tpl-website 模板内容优化 theone
    'PAGESIZE' => 10,//每页显示数目
    'TMPL_TEMPLATE_SUFFIX' => '.html', //模板文件后缀


    /* 模板相关配置 */
    //此处只做模板使用，具体替换在COMMON模块中的set_theme函数,该函数替换MODULE_NAME,DEFAULT_THEME两个值为设置值
    'TMPL_PARSE_STRING' => array(
        '__Theme__' => __ROOT__ . '/' . HOME_VIEW_PATH_TMPL . '/pc',
        '__IMG__' => __ROOT__ . '/' . HOME_VIEW_PATH_TMPL . '/pc/res/images',
        '__CSS__' => __ROOT__ . '/' . HOME_VIEW_PATH_TMPL . '/pc/res/css',
        '__JS__' => __ROOT__ . '/' . HOME_VIEW_PATH_TMPL . '/pc/res/js',
        '__PLUGINS__' => __ROOT__ . '/' . HOME_VIEW_PATH_TMPL . '/pc/res/plugins',
    ),

    //url重定义
    'VAR_MODULE' => 'g', // 默认模块获取变量
    'URL_MODEL' => 1,  //URL访问模式
    'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小

    //默认图片
    'DEFAULT_IMAGE' => array(
        'shop' => REL_ROOT . "Tpl-website/default/res/images/shop_default.jpg",
        'artificer' => REL_ROOT . "Tpl-website/default/res/images/shop_default.jpg",
        'brand' => REL_ROOT . "Tpl-website/default/res/images/shop_default.jpg",
        'article' => REL_ROOT . "Tpl-website/default/res/images/shop_default.jpg",
    ),
    /* 日志设置 */
    'LOG_RECORD' => true,// 默认不记录日志
    'LOG_TYPE' => 'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL' => 'ERR', // 允许记录的日志级别
    'LOG_FILE_SIZE' => 2097152, // 日志文件大小限制
    'LOG_EXCEPTION_RECORD' => true, // 是否记录异常信息日志


    'SHOW_RUN_TIME' => true, // 运行时间显示
    'SHOW_ADV_TIME' => true, // 显示详细的运行时间
    'SHOW_DB_TIMES' => true, // 显示数据库查询和写入次数
    'SHOW_CACHE_TIMES' => true, // 显示缓存操作次数
    'SHOW_USE_MEM' => true, // 显示内存开销
    'SHOW_LOAD_FILE' => true, // 显示加载文件数
    'SHOW_FUN_TIMES' => true, // 显示函数调用次数
    'SHOW_ADV_TIME' => true, // 关闭详细的运行时间
    //'TMPL_EXCEPTION_FILE' => '404.php',
    'SHOW_ERROR_MSG'        =>  true,    // 显示错误信息
    'SHOW_PAGE_TRACE' =>false,
    'TRACE_PAGE_TABS'=>array(    'base'=>'基本',     'file'=>'文件',     'think'=>'流程',     'error'=>'错误',     'sql'=>'SQL',     'debug'=>'调试'),
    //网站菜单
    'WEBSITE_MENU' => array(
        1 => array('key' => 1, 'title' => '首页', 'url' => "__APP__/", "child" => array()),
        2 => array('key' => 2, 'title' => '全部课程', 'url' => "__APP__/Home/Lesson", "child" => array()),
        3 => array('key' => 3, 'title' => '我的课程', 'url' => "__APP__/Home/Student", "child" => array()),
        6 => array('key' => 6, 'title' => '关于我们', 'url' => "__APP__/Home/About", "child" => array()),
    ),

);