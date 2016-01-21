<?php
// +----------------------------------------------------------------------
// | Fanwe 方维众筹商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once 'common.php';

class App2{		
	private $module_obj;
	//网站项目构造
	public function __construct(){		
		$module_name = "timelogModule";
		require APP_ROOT_PATH.'app/Lib/BaseModule.class.php';
		require_once APP_ROOT_PATH."app/Lib/modules/timelogModule.class.php";
		$this->module_obj = new $module_name;
		$this->module_obj->index();
	}
	
	public function __destruct()
	{
		unset($this);
	}
}
?>