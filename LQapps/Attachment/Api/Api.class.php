<?php
/*
附件API
*/
namespace Attachment\Api;

//载入API配置文件
require_cache(APP_COMMON_PATH . 'Conf/Api.config.php');

/**
 * ATTACHMENT 插件 API调用控制器层
 */
abstract class Api{

	/**
	 * API调用模型实例
	 * @access  protected
	 * @var object
	 */
	protected $model;

	/**
	 * 构造方法，检测相关配置
	 */
	public function __construct(){
		//相关配置检测
		defined('ATTACHMENT_APP_ID') || throw_exception('ATTACHMENT配置错误：缺少ATTACHMENT_APP_ID');
		defined('ATTACHMENT_API_TYPE') || throw_exception('ATTACHMENT配置错误：缺少ATTACHMENT_APP_API_TYPE');
		if(ATTACHMENT_API_TYPE != 'Model' && ATTACHMENT_API_TYPE != 'Service'){
			throw_exception('ATTACHMENT配置错误：ATTACHMENT_API_TYPE只能为 Model 或 Service');
		}
		if(ATTACHMENT_API_TYPE == 'Service' && ATTACHMENT_AUTH_KEY == ''){
			throw_exception('ATTACHMENT配置错误：Service方式调用Api时ATTACHMENT_AUTH_KEY不能为空');
		}
		$this->_init();
	}

	/**
	 * 抽象方法，用于设置模型实例
	 */
	abstract protected function _init();

}
