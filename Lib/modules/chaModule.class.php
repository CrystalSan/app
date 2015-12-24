<?php
require APP_ROOT_PATH.'app/Lib/shop_lip.php';
class chaModule extends BaseModule
{
	public function index(){
		$result=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."xiao");
		$GLOBALS['tmpl']->assign('result',$result);	
		$GLOBALS['tmpl']->display("cha_index.html");
		}
	}
?>