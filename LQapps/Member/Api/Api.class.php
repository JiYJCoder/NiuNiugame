<?php
/*
会员API
*/

namespace Member\Api;
//载入API配置文件
require_cache(APP_COMMON_PATH . 'Conf/Api.config.php');

/**
 * MEMBER API调用控制器层
 * 调用方法 A('Uc/User', 'Api')->login($username, $password, $type);
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
		defined('MEMBER_APP_ID') || throw_exception('MEMBER配置错误：缺少MEMBER_APP_ID');
		defined('MEMBER_API_TYPE') || throw_exception('MEMBER配置错误：缺少MEMBER_APP_API_TYPE');
		defined('AUTH_KEY') || throw_exception('MEMBER配置错误：缺少AUTH_KEY');
		if(MEMBER_API_TYPE != 'Model' && MEMBER_API_TYPE != 'Service'){
			throw_exception('MEMBER配置错误：MEMBER_API_TYPE只能为 Model 或 Service');
		}
		if(MEMBER_API_TYPE == 'Service' && AUTH_KEY == ''){
			throw_exception('MEMBER配置错误：Service方式调用Api时AUTH_KEY不能为空');
		}

		$this->_init();
	}

	/**
	 * 抽象方法，用于设置模型实例
	 */
	abstract protected function _init();

}
