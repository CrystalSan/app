<?php
class timeModule extends BaseModule {
	public function show(){
		$id = $_GET['id'];
		$wx_info = es_session::get('wx_info');
		$user_info = $GLOBALS['db']->getRow("select * from xlc_calendar where id=".$id);
		if(!$user_info){
			app_redirect("/index.php?ctl=time&act=save");
		}
		if($user_info['wid'] == $wx_info['openid']){
			$GLOBALS['tmpl']->assign("edit","1");
		}
		$startdate=strtotime($user_info['year']."-".$user_info['month']."-".$user_info['day']);
		$nowdate = strtotime("now");
		$enddate=strtotime($user_info['year']."-01-01");
		$tmpday = round(($startdate-$enddate)/3600/24);
		$lifeday = round(($nowdate-$startdate)/3600/24);
		$allday = $user_info['death']*365 - $tmpday;
		$addday = round($lifeday*365/$allday);
		$user_info['life_month'] = date('m',strtotime('+'.$addday.' day',strtotime('2015-01-01')));
		$user_info['life_day'] = date('d',strtotime('+'.$addday.' day',strtotime('2015-01-01')));
		$user_info['age'] = date("Y")-$user_info['year'];
		$user_info['death_day'] = ($user_info['death']-$user_info['age'])*365;
		$user_info['death_eat'] = $user_info['death_day']*3;
		if($user_info['death']>50){
			$checkage = 50;
		}else{
			$checkage = $user_info['death'];
		}
		
		if($checkage < $user_info['age']){
			$user_info['death_fuck'] = 0;
		}else{
			$user_info['death_fuck'] = ($checkage-$user_info['age'])*52*2;
		}
		$user_info['death_year'] = ($user_info['death']-$user_info['age']);
		require_once APP_ROOT_PATH."system/utils/jssdk.php";
		$jssdk = new JSSDK('wxd0efb676fbe5a8ed','cbe473ad7b19f99432a4a162ab772b5a');
		$signPackage = $jssdk->GetSignPackage();
		$GLOBALS['tmpl']->assign("signPackage",$signPackage);
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->display("/calendar_result.html");
	}
	
	public function save(){
		if($_POST){
			if($_POST['id']){
				$id = $_POST['id'];
				$data = array();
				$data['name'] = trim($_POST['name']);
				$data['wid'] = $_POST['wid'];
				$data['year'] = $_POST['year'];
				$data['month'] = $_POST['month'];
				$data['day'] = $_POST['day'];
				$data['death'] = $_POST['death'];
				$data['edit_time'] = time();
				//入库
				$GLOBALS['db']->query("update xlc_calendar set name='".$data['name']."' , year='".$data['year']."' , month='".$data['month']."' , day='".$data['day']."' , death='".$data['death']."' , edit_time='".$data['edit_time']."'  where id=".$id);
				app_redirect("/index.php?ctl=time&act=show&id=".$id);
			}else{
				$data = array();
				$data['name'] = trim($_POST['name']);
				$data['wid'] = $_POST['wid'];
				$data['year'] = $_POST['year'];
				$data['month'] = $_POST['month'];
				$data['day'] = $_POST['day'];
				$data['death'] = $_POST['death'];
				$data['create_time'] = time();
				//入库
				$GLOBALS['db']->autoExecute("xlc_calendar",$data,"INSERT","","SILENT");
				$data_id = intval($GLOBALS['db']->insert_id());
				app_redirect("/index.php?ctl=time&act=show&id=".$data_id);
			}
		}else{
			require APP_ROOT_PATH.'system/utils/weixin.php';
			$is_weixin=isWeixin();
			if(!$is_weixin){
				echo '必须使用微信打开此页！';exit;
			}
			if($_REQUEST['code']){
				$weixin=new weixin('wxd0efb676fbe5a8ed','cbe473ad7b19f99432a4a162ab772b5a',get_domain().$_SERVER["REQUEST_URI"]);
				$wx_info=$weixin->scope_get_userinfo($_REQUEST['code']);
				es_session::set('wx_info',$wx_info);
			}else{
				$weixin_2=new weixin('wxd0efb676fbe5a8ed','cbe473ad7b19f99432a4a162ab772b5a',get_domain().$_SERVER["REQUEST_URI"]);
				$wx_url=$weixin_2->scope_get_code();
				app_redirect($wx_url);
			}
			$edit = $_GET['edit'];
			if(!empty($wx_info['openid'])){
				$data_info = $GLOBALS['db']->getRow("select * from xlc_calendar where wid='".$wx_info['openid']."'");
				if($data_info && !$edit){
					app_redirect("/index.php?ctl=time&act=show&id=".$data_info['id']);
				}
				$GLOBALS['tmpl']->assign("data_info",$data_info);
			}
			$fid = $_GET['fid'];
			$from_info = $GLOBALS['db']->getRow("select * from xlc_calendar where id=".$id);
			if($from_info){
				$GLOBALS['tmpl']->assign("fid",$from_info['wid']);
			}
			$startyear = 1950;
			$yearArr = array();
			$monthArr = array();
			$dayArr = array();
			$deathArr = array();
			for($i=0;$i<60;$i++){
				$yearArr[] = $startyear+$i;
			}
			for($m=1;$m<13;$m++){
				$monthArr[] = $m;
			}
			for($d=1;$d<32;$d++){
				$dayArr[] = $d;
			}
			for($de=10;$de<120;$de++){
				$deathArr[] = $de;
			}
			$GLOBALS['tmpl']->assign("yearArr",$yearArr);
			$GLOBALS['tmpl']->assign("monthArr",$monthArr);
			$GLOBALS['tmpl']->assign("dayArr",$dayArr);
			$GLOBALS['tmpl']->assign("deathArr",$deathArr);
			$GLOBALS['tmpl']->assign("wx_info",$wx_info);
			$GLOBALS['tmpl']->display("/calendar_index.html");
		}
		
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