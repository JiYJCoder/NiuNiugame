<?php
namespace LQLibs\Weixin\WxPayPubHelper;
class  SDKRuntimeException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>