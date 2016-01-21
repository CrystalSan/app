<?php
require APP_ROOT_PATH.'app/Lib/shop_lip.php';
class timelogModule extends BaseModule {
	function index(){
		$userInfo = es_session::get("timeuser");
		if(empty($userInfo)){
			app_redirect(url("timelog#login"));
		}
		$GLOBALS['tmpl']->assign("user_info",$userInfo);
		$user_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."staff_time_user where state=1 order by id asc");
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		//创建基础表格
		//获取当前时间
		$startTime = time();
		$dataArray = array();
		$dataArray['all'] = array();
		foreach($user_list as $key=>$val){
			$dataArray[$val['id']] = array();
			$dataArray[$val['id']]['id'] = $val['id'];
			$dataArray[$val['id']]['username'] = $val['name'];
			$dataArray[$val['id']]['email'] = $val['email'];
		}
		$showTimeArray = array();
		for($h=0;$h<24;$h++){
			$showTimeArray[] = $h;
		}
		$empdateArray = array();
		$startDayStr = $startTime;
		$endDayStr = $startTime+14*24*60*60;
		for($d=0;$d<15;$d++){
			$tmpDateStr = date("Y-m-d" ,$startTime+$d*24*60*60);
			$tmpDayStr = date("m/d" ,$startTime+$d*24*60*60);
			$empdateArray[$d] = array();
			$empdateArray[$d]['value'] = $tmpDayStr;
			$empdateArray[$d]['day'] = $tmpDateStr;
			$empdateArray[$d]['list'] = array();
			for($h=0;$h<24;$h++){
				$empdateArray[$d]['list'][$h] = array();
				$empdateArray[$d]['list'][$h]['value'] = $h;
				$empdateArray[$d]['list'][$h]['list'] = array();
			}
		}
		foreach($dataArray as $k=>$userv){
			$dataArray[$k]['list'] = $empdateArray;
		}
		foreach($dataArray as $k=>$userv){
			//获取发起的会议邀请
			if($k=='all'){
				$tmpDataList = $GLOBALS['db']->getAll("select a.*,b.name as send_name from ".DB_PREFIX."staff_time_data as a, ".DB_PREFIX."staff_time_user as b where a.send_id=b.id and a.start_date>=".$startDayStr." and a.start_date<=".$endDayStr." order by a.start_date asc");
			}else{
				$tmpDataList = $GLOBALS['db']->getAll("select a.*,b.name as send_name from ".DB_PREFIX."staff_time_data as a, ".DB_PREFIX."staff_time_user as b where a.send_id=b.id and a.start_date>=".$startDayStr." and a.start_date<=".$endDayStr." and a.send_id=".$userv['id']." order by a.start_date asc");
			}
			
			if($tmpDataList){
				foreach($tmpDataList as $kdata=>$datav){
					foreach($userv['list'] as $kd=>$dayv){
						$datav['start_day'] = date("Y-m-d",$datav['start_date']);
						if($datav['start_day']==$dayv['day']){
							foreach($dayv['list'] as $kh=>$hv){
								if($datav['start_time']<=$hv['value'] && $datav['end_time']>=$hv['value']){
									//获取被邀请者确认信息
									$tmpUserCheckList = $GLOBALS['db']->getAll("select a.id,a.state,a.check_time,b.name from ".DB_PREFIX."staff_time_invite as a,".DB_PREFIX."staff_time_user as b where a.to_id=b.id and a.data_id=".$datav['id']);
									$tmpDataInfo = $datav;
									$tmpDataInfo['check_info'] = '';
									if($tmpUserCheckList){
										foreach($tmpUserCheckList as $kc=>$checkv){
											if($checkv['state']==0){
												$tmpDataInfo['check_info'] .= $checkv['name']."未进行确认；";
											}elseif($checkv['state']==1){
												$tmpDataInfo['check_info'] .= $checkv['name']."在".date("Y-m-d",$checkv['check_time'])."确认参加；";
											}else{
												$tmpDataInfo['check_info'] .= $checkv['name']."拒绝参加；";
											}
										}
									}
									//发起
									$dataArray[$k]['list'][$kd]['list'][$kh]['show'] = 1;
									$dataArray[$k]['list'][$kd]['list'][$kh]['list'][] = $tmpDataInfo;
								}
							}
						}
					}
				}
			}
			//获取被邀请的会议邀请
			if($k!='all'){
				$tmpInviteList = $GLOBALS['db']->getAll("select a.state as user_check_state,b.*,c.name as send_name from ".DB_PREFIX."staff_time_invite as a, ".DB_PREFIX."staff_time_data as b, ".DB_PREFIX."staff_time_user as c where b.start_date>=".$startDayStr." and b.start_date<=".$endDayStr." and a.data_id=b.id and b.send_id=c.id and to_id=".$userv['id']." order by to_id asc");
			}
			if($tmpInviteList){
				foreach($tmpInviteList as $kdata=>$checkv){
					foreach($userv['list'] as $kd=>$dayv){
						$checkv['start_day'] = date("Y-m-d",$checkv['start_date']);
						if($checkv['start_day']==$dayv['day']){
							foreach($dayv['list'] as $kh=>$hv){
								if($checkv['start_time']<=$hv['value'] && $checkv['end_time']>=$hv['value']){
									$tmpDataInfo = $checkv;
									$tmpDataInfo['check_info'] = $checkv['send_name']."邀请参加《".$checkv['title']."》会议；";
									if($checkv['user_check_state']==1){
										//确认参加
										$dataArray[$k]['list'][$kd]['list'][$kh]['show'] = 2;
										$tmpDataInfo['check_info'] .= "确认参加；";
									}elseif($checkv['user_check_state']==0){
										//未确认
										$dataArray[$k]['list'][$kd]['list'][$kh]['show'] = 3;
										$tmpDataInfo['check_info'] .= "尚未确认；";
									}else{
										//拒绝参加
										$dataArray[$k]['list'][$kd]['list'][$kh]['show'] = 4;
										$tmpDataInfo['check_info'] .= "拒绝参加；";
									}
									$dataArray[$k]['list'][$kd]['list'][$kh]['list'][] = $tmpDataInfo;
								}
							}
						}
					}
				}
			}
		}
		$GLOBALS['tmpl']->assign("data_list",$dataArray);
		$GLOBALS['tmpl']->assign("show_time_list",$showTimeArray);
		$GLOBALS['tmpl']->display("timelog_index.html");
	}
	
	function save(){
		$userInfo = es_session::get("timeuser");
		if(empty($userInfo)){
			app_redirect(url("timelog#login"));
		}
		if($_POST){
			$data = array();
			$data['send_id'] = $_POST['send_id'];
			$data['title'] = trim($_POST['title']);
			$data['info'] = $_POST['info'];
			$year=((int)substr($_POST['start_date'],0,4));//取得年份
			$month=((int)substr($_POST['start_date'],5,2));//取得月份
			$day=((int)substr($_POST['start_date'],8,2));//取得几号
			$data['start_date'] = mktime(0,0,0,$month,$day,$year);
			$data['start_time'] = $_POST['start_time'];
			$data['end_time'] = $_POST['end_time'];
			$data['call_time'] = $_POST['call_time'];
			$data['ads'] = $_POST['ads'];
			$to_user_array = $_POST['to_user'];
			//入库
			$GLOBALS['db']->autoExecute(DB_PREFIX."staff_time_data",$data,"INSERT","","SILENT");
			$data_id = intval($GLOBALS['db']->insert_id());
			foreach($to_user_array as $v){
				$tmp_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."staff_time_user where id=".$v);
				$tmp_to_user_data = array();
				$tmp_to_user_data['data_id'] = $data_id;
				$tmp_to_user_data['send_id'] = $userInfo['id'];
				$tmp_to_user_data['send_email'] = $userInfo['email'];
				$tmp_to_user_data['to_id'] = $tmp_user_info['id'];
				$tmp_to_user_data['to_email'] = $tmp_user_info['email'];
				$tmp_to_user_data['state'] = 0;
				$tmp_to_user_data['send_time'] = time();
				$GLOBALS['db']->autoExecute(DB_PREFIX."staff_time_invite",$tmp_to_user_data,"INSERT","","SILENT");
			}
			app_redirect(url("timelog#index"));
		}
		$GLOBALS['tmpl']->assign("user_info",$userInfo);
		$user_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."staff_time_user where state=1 order by id asc");
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		$showTimeArray = array();
		for($h=0;$h<24;$h++){
			$showTimeArray[] = $h;
		}
		$GLOBALS['tmpl']->assign("show_time_list",$showTimeArray);
		$GLOBALS['tmpl']->display("timelog_save.html");
	}
	
	function login(){
		if($_POST){
			$email = $_POST['email'];
			$pwd = md5($_POST['pwd']);
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."staff_time_user where email='".$email."' and pwd='".$pwd."'");
			if(empty($user_info)){
				$error = "用户名密码不匹配";
			}elseif($user_info['state']!=1){
				$error = "用户状态不正确";
			}else{
				es_session::set("timeuser",$user_info);
				app_redirect(url("timelog#index"));
			}
			$GLOBALS['tmpl']->assign("error",$error);
		}
		$GLOBALS['tmpl']->display("timelog_log.html");
	}

	function del() {
		
	}
}