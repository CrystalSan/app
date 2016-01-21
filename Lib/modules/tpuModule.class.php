<?php

class tpuModule extends BaseModule
{
	function save(){
		if(!$GLOBALS['user_info']){
			$data['status'] = -1;
			$data['info'] = "需要登录后进行报名。";
			ajax_return($data);
		}
		$info_data=array();
		$info_data['user_id'] = $GLOBALS['user_info']['id'];
		$info_data['name'] = $GLOBALS['user_info']['user_name'];
		$info_data['mobile']= $GLOBALS['user_info']['mobile'];
		$info_data['ip'] = get_client_ip();
		$info_data['type'] = $_GET['f'];
		$info_data['creat_time']= time();
		if(!check_mobile($info_data['mobile'])){
			$data['status'] = -2;
			$data['info'] = "请填写正确的手机号码";
			ajax_return($data);
		}
		$tmp_zhuanti_user_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."zhuanti_user where (mobile='".$info_data['mobile']."' or user_id=".$info_data['user_id'].") and type=2");
		if($tmp_zhuanti_user_info){
			$data['status'] = -3;
			$data['info'] = "您已参加过报名。";
			ajax_return($data);
		}
		$GLOBALS['db']->autoExecute(DB_PREFIX."zhuanti_user",$info_data,"INSERT");
		$msgInfo = array();
		$msgInfo['title'] = '专题报名';
		$msgInfo['content'] = '专题报名';
		send_zhuanti_sms($info_data['mobile'],$msgInfo,3812);
		$data['status'] = 1;
		$data['info'] = "报名成功！";
		ajax_return($data);
	}
}
?>