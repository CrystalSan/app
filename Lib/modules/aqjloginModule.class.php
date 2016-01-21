<?php
// +----------------------------------------------------------------------
// | Fanwe 方维众筹商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/shop_lip.php';
class aqjloginModule extends BaseModule
{
	//购物车
	public function index()
	{
    $aqj_id = trim($_REQUEST['aqjid']);
    $aqj_mobile = trim($_REQUEST['m']);
    if(empty($aqj_id) || empty($aqj_mobile)){
    	app_redirect(url("aps"));
    }
    //查询aqj_id是否已绑定过
		$aqj_user=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ap_partner_user where partner_user_id='".$aqj_id."'");
		//如果已经绑定过自动登录并进入积分商城页面
		if($aqj_user){
			$xlc_user=$GLOBALS['db']->getRow("select mobile,user_pwd from ".DB_PREFIX."user where id=".$aqj_user['user_id']);
			require_once APP_ROOT_PATH."system/libs/user.php";
			auto_do_login_user($xlc_user['mobile'],$xlc_user['user_pwd']);
			app_redirect(url("aps"));
		}
    $GLOBALS['tmpl']->assign("aqj_id",$aqj_id);
    $GLOBALS['tmpl']->assign("aqj_m",$aqj_mobile);
		$GLOBALS['tmpl']->display("aqjlogin_index.html");
	}
	
	public function do_register(){
		//查询用户是否存在
		$aqj_id = trim($_POST['reg_aqjid']);
		$aqj_mobile = trim($_POST['reg_m']);
		$xlc_user=$GLOBALS['db']->getRow("select id,mobile,user_pwd from ".DB_PREFIX."user where mobile='".$aqj_mobile."'");
		if($xlc_user){
			$aqj_user=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ap_partner_user where user_id=".$xlc_user['id']);
			if(!$aqj_user){
				$user_ap_partner = array();
				$user_ap_partner['user_id'] = $xlc_user['id'];
				$user_ap_partner['partner_id'] = 2;
				$user_ap_partner['partner_user_id'] = $aqj_id;
				$user_ap_partner['create_time'] = time();
				$GLOBALS['db']->autoExecute(DB_PREFIX."ap_partner_user",$user_ap_partner);
			}else{
				$GLOBALS['db']->query("update ".DB_PREFIX."ap_partner_user set partner_user_id = '".$aqj_id."' where user_id = ".$xlc_user['id']);	
			}
			
			require_once APP_ROOT_PATH."system/libs/user.php";
			auto_do_login_user($xlc_user['mobile'],$xlc_user['user_pwd']);
			app_redirect(url("aps"));
		}else{
			require_once APP_ROOT_PATH."system/libs/user.php";
			$user_data = array();
			$user_data['user_name'] = $aqj_mobile;
			$user_data['mobile'] = $aqj_mobile;
			$user_data['user_pwd'] = rand(100000,999999);
	    //开启邮箱验证
	    if(app_conf("USER_VERIFY")==0||app_conf("USER_VERIFY")==2){
	         $user_data['is_effect'] = 1;
	    }else{
	    	$user_data['is_effect'] = 0;
	    }
			$res = save_user($user_data);
			statistics('register');
			$user_ap_partner = array();
			$user_ap_partner['user_id'] = $res['data'];
			$user_ap_partner['partner_id'] = 2;
			$user_ap_partner['partner_user_id'] = $aqj_id;
			$user_ap_partner['create_time'] = time();
			$GLOBALS['db']->autoExecute(DB_PREFIX."ap_partner_user",$user_ap_partner);
			$result = do_login_user($user_data['mobile'],$user_data['user_pwd']);
			send_auto_register_pwd($aqj_mobile,$user_data['user_pwd']);
			app_redirect(url("aps"));
		}
	}

	public function do_login(){
		$aqj_id = trim($_POST['log_aqjid']);
		$user_mobile = trim($_POST['log_mobile']);
		$user_pwd = trim($_POST['log_pwd']);
		$result = do_login_user($user_mobile,$user_pwd);
		$user_ap_partner = array();
		$user_ap_partner['user_id'] = $result['user']['id'];
		$user_ap_partner['partner_id'] = 2;
		$user_ap_partner['partner_user_id'] = $aqj_id;
		$user_ap_partner['create_time'] = time();
		$GLOBALS['db']->autoExecute(DB_PREFIX."ap_partner_user",$user_ap_partner);
		app_redirect(url("aps"));
	}
	
}
?>