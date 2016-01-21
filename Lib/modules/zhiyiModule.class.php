<?php
require APP_ROOT_PATH.'app/Lib/shop_lip.php';
class zhiyiModule extends BaseModule
{
	function index(){
		echo "ok";
		exit;
		$g_links =get_link_by_id(14);
		print_r($g_links);
		exit;
		$id= intval($_REQUEST['id']);
		$zhichi = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$id." and is_success=1");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		 $GLOBALS['tmpl']->display("zhichi.html");
		}
		function zhiwei(){
		$id= intval($_REQUEST['id']);
		$zhichi = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$id." and is_success=0");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		 $GLOBALS['tmpl']->display("zhichi.html");
		}
		function choucheng(){
		$id= intval($_REQUEST['id']);
		$zhichi = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$id." and is_success=1");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		 $GLOBALS['tmpl']->display("zhichi.html");
		}
		function choucheng(){
		$id= intval($_REQUEST['id']);
		$zhichi = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$id." and is_success=1");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		 $GLOBALS['tmpl']->display("zhichi.html");
		}
	}
?>