<?php //项目中也可能用到语言行为
return array(
    // 添加下面一行定义即可
    'app_begin' => array('Behavior\CheckLangBehavior'),
	'view_filter'=> array('Behavior\ShowRuntimeBehavior','Behavior\TokenBuildBehavior'),
	'LQLibs\Util\Input'=> array(THINK_PATH.'\LQLibs\Util\Input'),
);
?>