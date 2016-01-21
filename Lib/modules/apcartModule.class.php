<?php
// +----------------------------------------------------------------------
// | Fanwe 方维众筹商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/shop_lip.php';
class apcartModule extends BaseModule
{
	//购物车
	public function index()
	{		
                //links
                $g_links =get_link_by_id(14);
                
                $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		//(普通众筹)支持之前需要用户绑定手机号
		if(!$GLOBALS['user_info']['mobile'])
		{
			showErr("您未绑定手机,无法进行众筹",0,url("settings#security",array('method'=>'setting-mobile-box')));
			//app_redirect(url("user#user_bind_mobile",array("cid"=>intval($_REQUEST['id']))));
			//app_redirect(url("settings#security",array('method'=>'setting-mobile-box')));
		}
		$id = intval($_REQUEST['id']);
		$deal_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_item where id = ".$id);
		if(!$deal_item)
		{
			app_redirect(url("index"));
		}
		elseif($deal_item['support_count']>=$deal_item['limit_user']&&$deal_item['limit_user']!=0)
		{
			app_redirect(url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and id = ".$deal_item['deal_id']);
		if(!$deal_info)
		{
			app_redirect(url("index"));
		}
		elseif($deal_info['begin_time']>NOW_TIME||($deal_info['end_time']<NOW_TIME&&$deal_info['end_time']!=0))
		{
			app_redirect(url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		$deal_info1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_item where deal_id = ".$deal_item['deal_id']);
		foreach ($deal_info1 as $k=>$v){
			// 统计所有真实+虚拟（钱）
			$deal_info1['total_virtual_price']+=intval($v['price'] * $v['virtual_person']+$v['support_amount']);
		}			
		$deal_info['support_amount']=$deal_info1['total_virtual_price'];
		$deal_item['price_format'] = number_price_format($deal_item['price']);
		$deal_item['delivery_fee_format'] = number_price_format($deal_item['delivery_fee']);
		$deal_info['percent'] = round($deal_info['support_amount']/$deal_info['limit_price']*100);
		$deal_info['remain_days'] = ceil(($deal_info['end_time'] - NOW_TIME)/(24*3600));
		
		$deal_item['ap_price'] = $deal_item['price']*$deal_item['ap_ratio'];
		$deal_item['delivery_ap'] = $deal_item['delivery_fee']*$deal_item['ap_ratio'];
		
		$deal_info['limit_ap'] = $deal_info['limit_price']*$deal_info['ap_ratio'];
		$deal_info['ap_amount'] = $deal_info['support_amount']*$deal_info['ap_ratio'];
		
		$GLOBALS['tmpl']->assign("deal_item",$deal_item);
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		
		if($deal_info['seo_title']!="")
		$GLOBALS['tmpl']->assign("seo_title",$deal_info['seo_title']);
		if($deal_info['seo_keyword']!="")
		$GLOBALS['tmpl']->assign("seo_keyword",$deal_info['seo_keyword']);
		if($deal_info['seo_description']!="")
		$GLOBALS['tmpl']->assign("seo_description",$deal_info['seo_description']);
		$GLOBALS['tmpl']->assign("page_title",$deal_info['name']);
		
		if($deal_item['is_delivery'])
		{
			$consignee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_consignee where user_id = ".intval($GLOBALS['user_info']['id']));
			if($consignee_list)
			$GLOBALS['tmpl']->assign("consignee_list",$consignee_list);
			else
			{
				$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2 order by py asc");  //二级地址
				$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
			}
		}
		
		$GLOBALS['tmpl']->display("apcart_index.html");		
		
	}
	
	public function check()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$deal_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_item where id = ".$id);
		if(!$deal_item)
		{
			showErr("",$ajax,url("index"));
		}
		elseif($deal_item['support_count']>=$deal_item['limit_user']&&$deal_item['limit_user']!=0)
		{
			showErr("",$ajax,url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and id = ".$deal_item['deal_id']);
		if(!$deal_info)
		{
			showErr("",$ajax,url("index"));
		}
		elseif($deal_info['begin_time']>NOW_TIME||($deal_info['end_time']<NOW_TIME&&$deal_info['end_time']!=0))
		{
			showErr("",$ajax,url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		
		if($deal_item['is_delivery']==1)
		{
			$consignee_id = intval($_REQUEST['consignee_id']);
			if($consignee_id==0)
			{
				$consignee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_consignee where user_id = ".intval($GLOBALS['user_info']['id']));
				if($consignee_list)
				{
					showErr("请选择配送方式",$ajax);
				}
				else
				{
					$consignee = strim($_REQUEST['consignee']);
					$province = strim($_REQUEST['province']);
					$city = strim($_REQUEST['city']);
					$address = strim($_REQUEST['address']);
					$zip = strim($_REQUEST['zip']);
					$mobile = strim($_REQUEST['mobile']);
					if($consignee=="")
					{
						showErr("请填写收货人姓名",$ajax,"");	
					}
					if($province=="")
					{
						showErr("请选择省份",$ajax,"");	
					}
					if($city=="")
					{
						showErr("请选择城市",$ajax,"");	
					}
					if($address=="")
					{
						showErr("请填写详细地址",$ajax,"");	
					}
					if(!check_postcode($zip))
					{
						showErr("请填写正确的邮编",$ajax,"");	
					}
					if($mobile=="")
					{
						showErr("请填写收货人手机号码",$ajax,"");	
					}
					if(!check_mobile($mobile))
					{
						showErr("请填写正确的手机号码",$ajax,"");	
					}
					
					$data = array();
					$data['consignee'] = $consignee;
					$data['province'] = $province;
					$data['city'] = $city;
					$data['address'] = $address;
					$data['zip'] = $zip;
					$data['mobile'] = $mobile;
					$data['user_id'] = intval($GLOBALS['user_info']['id']);
					$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$data);
					$consignee_id = $GLOBALS['db']->insert_id();
					
				}
			}			
		}
		
		if(intval($consignee_id)==0&&$deal_item['is_delivery']==1)
		{
			showErr("请选择配送方式",$ajax,"");	
		}
		else
		{
			$memo = strim($_REQUEST['memo']);
			if($memo!=""&&$memo!="在此填写关于回报内容的具体选择或者任何你想告诉项目发起人的话")
			es_session::set("cart_memo_".intval($id),$memo);

			if($deal_item['is_delivery']==0)
			showSuccess("",$ajax,url("apcart#pay",array("id"=>$id)));
			else
			showSuccess("",$ajax,url("apcart#pay",array("id"=>$id,"did"=>$consignee_id)));
		}
		
		
	}
	
	public function pay()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$deal_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_item where id = ".$id);
		if(!$deal_item)
		{
			app_redirect(url("index"));
		}
		elseif($deal_item['support_count']>=$deal_item['limit_user']&&$deal_item['limit_user']!=0)
		{
			app_redirect(url("deal#show",array("id"=>$deal_item['deal_id'])));
		}
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and id = ".$deal_item['deal_id']);
		if(!$deal_info)
		{
			app_redirect(url("index"));
		}
		elseif($deal_info['begin_time']>NOW_TIME||($deal_info['end_time']<NOW_TIME&&$deal_info['end_time']!=0))
		{
			app_redirect(url("deal#show",array("id"=>$deal_item['deal_id'])));
		}
		
		$deal_item['price_format'] = number_price_format($deal_item['price']);
		$deal_item['delivery_fee_format'] = number_price_format($deal_item['delivery_fee']);
		$deal_item['total_price_format'] = number_price_format($deal_item['total_price']);
		$deal_info['percent'] = round($deal_info['support_amount']/$deal_info['limit_price']*100);
		$deal_info['remain_days'] = ceil(($deal_info['end_time'] - NOW_TIME)/(24*3600));
		
		
		$deal_item['ap_price'] = $deal_item['price']*$deal_item['ap_ratio'];
		$deal_item['delivery_ap'] = $deal_item['delivery_fee']*$deal_item['ap_ratio'];
		
		$deal_info['limit_ap'] = $deal_info['limit_price']*$deal_info['ap_ratio'];
		$deal_info['ap_amount'] = $deal_info['support_amount']*$deal_info['ap_ratio'];
		$deal_item['total_ap'] = $deal_item['ap_price']+$deal_item['delivery_ap'];
		
		$GLOBALS['tmpl']->assign("deal_item",$deal_item);
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		
		if($deal_info['seo_title']!="")
		$GLOBALS['tmpl']->assign("seo_title",$deal_info['seo_title']);
		if($deal_info['seo_keyword']!="")
		$GLOBALS['tmpl']->assign("seo_keyword",$deal_info['seo_keyword']);
		if($deal_info['seo_description']!="")
		$GLOBALS['tmpl']->assign("seo_description",$deal_info['seo_description']);
		$GLOBALS['tmpl']->assign("page_title",$deal_info['name']);
		
		$memo = es_session::get("cart_memo_".$id);
		$consignee_id = intval($_REQUEST['did']);
		$GLOBALS['tmpl']->assign("memo",$memo);
		$GLOBALS['tmpl']->assign("consignee_id",$consignee_id);
		
		require_once APP_ROOT_PATH."system/payment/Appay_payment.php";
		$o = new Appay_payment();
		//获取用户积分
		$user_ap = $o->get_user_ap($GLOBALS['user_info']['id']);
 		$GLOBALS['tmpl']->assign('user_ap', $user_ap);
		
		$GLOBALS['tmpl']->display("apcart_pay.html");
	}

	public function go_pay()
	{

		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$consignee_id = intval($_REQUEST['consignee_id']);
		
		
		$memo = strim($_REQUEST['memo']);
		$payment_id = intval($_REQUEST['payment_id']);
		$deal_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_item where id = ".$id);
		if(!$deal_item)
		{
			app_redirect(url("index"));
		}
		elseif($deal_item['support_count']>=$deal_item['limit_user']&&$deal_item['limit_user']!=0)
		{
			app_redirect(url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and id = ".$deal_item['deal_id']);
		if(!$deal_info)
		{
			app_redirect(url("index"));
		}
		elseif($deal_info['begin_time']>NOW_TIME||($deal_info['end_time']<NOW_TIME&&$deal_info['end_time']!=0))
		{
			app_redirect(url("ap#show",array("id"=>$deal_item['deal_id'])));
		}
		
		if(intval($consignee_id)==0&&$deal_item['is_delivery']==1)
		{
			showErr("请选择配送方式",0,get_gopreview());	
		}
		
		$order_info['deal_id'] = $deal_info['id'];
		$order_info['deal_item_id'] = $deal_item['id'];
		$order_info['user_id'] = intval($GLOBALS['user_info']['id']);
		$order_info['user_name'] = $GLOBALS['user_info']['user_name'];
		$order_info['total_price'] = $deal_item['price']+$deal_item['delivery_fee'];
		$order_info['delivery_fee'] = $deal_item['delivery_fee'];
		$order_info['deal_price'] = $deal_item['price'];
		$order_info['support_memo'] = $memo;
		$order_info['payment_id'] = $payment_id;
		$order_info['ap_partner_id'] = intval($_REQUEST['ap_partner_id']);
		$order_info['ap_ratio'] = $deal_item['ap_ratio'];
		$order_info['bank_id'] = strim($_REQUEST['bank_id']);
		
		$max_pay= intval($_REQUEST['max_pay']);
		
		$order_info['credit_pay'] = 0;
		$order_info['online_pay'] = 0;
		$order_info['ap_pay'] = 0;
		$order_info['deal_name'] = $deal_info['name'];
		$order_info['order_status'] = 0;
		$order_info['create_time']	= NOW_TIME;
		$order_info['is_ap']	= 1;
		if($consignee_id>0)
		{
			$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where id = ".$consignee_id." and user_id = ".intval($GLOBALS['user_info']['id']));
			if(!$consignee_info&&$deal_item['is_delivery']==1)
			{
				showErr("请选择配送方式",0,get_gopreview());	
			}
			$order_info['consignee'] = $consignee_info['consignee'];
			$order_info['zip'] = $consignee_info['zip'];
			$order_info['address'] = $consignee_info['address'];
			$order_info['province'] = $consignee_info['province'];
			$order_info['city'] = $consignee_info['city'];
			$order_info['mobile'] = $consignee_info['mobile'];
		}
		$order_info['is_success'] = $deal_info['is_success'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order_info);
		
		$order_id = $GLOBALS['db']->insert_id();
		if($order_id>0)
		{
			$result = pay_order($order_id);
			if($result['status']==0)
			{
				$money = $result['money'];
				$payment_notice['create_time'] = NOW_TIME;
				$payment_notice['user_id'] = intval($GLOBALS['user_info']['id']);
				$payment_notice['payment_id'] = $payment_id;
				$payment_notice['money'] = $money;
				$payment_notice['bank_id'] = strim($_REQUEST['bank_id']);
				$payment_notice['order_id'] = $order_id;
				$payment_notice['memo'] = $memo;
				$payment_notice['deal_id'] = $deal_info['id'];
				$payment_notice['deal_item_id'] = $deal_item['id'];
				$payment_notice['deal_name'] = $deal_info['name'];
				$payment_notice['partner_id'] = $order_info['ap_partner_id'];
				$payment_notice['ap_ratio'] = $order_info['ap_ratio'];
				
				do{
					$payment_notice['notice_sn'] = to_date(NOW_TIME,"Ymd").rand(100,999);
					$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$payment_notice,"INSERT","","SILENT");
					$notice_id = $GLOBALS['db']->insert_id();
				}while($notice_id==0);
				
				require_once APP_ROOT_PATH."system/payment/Appay_payment.php";
				$o = new Appay_payment();
				//获取用户积分
				$res = $o->user_pay($notice_id);
				if($res){
					app_redirect(url("account"));
				}else{
					app_redirect(url("account#credit"));
				}
			}
			elseif($result['status']==1||$result['status']==2)
			{
				app_redirect(url("account#credit"));  
			}
			else
			{
				app_redirect(url("account"));
			}
		}
		else
		{
			showErr("下单失败",0,get_gopreview());	
		}		
		
	}
	
	public function jump()
	{
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
		$notice_id = intval($_REQUEST['id']);
		$notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id." and is_paid = 0 and user_id = ".intval($GLOBALS['user_info']['id']));
		if(!$notice_info)
		{
			app_redirect(url("index"));
		}
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$notice_info['payment_id']);
		$class_name = $payment_info['class_name']."_payment";
		require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
		$o = new $class_name();
		
		header("Content-Type:text/html; charset=utf-8");
		echo $o->get_payment_code($notice_id);
	}
	
	public function jump_wxzf()
	{
 		$notice_id = intval($_REQUEST['id']);
		$notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id."   and user_id = ".intval($GLOBALS['user_info']['id']));
  		if($notice_info['is_paid']==1)
		{
			$data['pay_status'] = 1;
			$data['pay_info'] = '已支付.';
			$data['show_pay_btn'] = 0;
			$GLOBALS['tmpl']->assign('data',$data);
			$GLOBALS['tmpl']->display('pay_order_index.html');
		}else{
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$notice_info['payment_id']);
	 		$class_name = $payment_info['class_name']."_payment";
  			require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
 			$o = new $class_name();
 	  		$picUrl = $o->get_payment_codepic($notice_id);
 	  		//app_redirect(url("account#credit"));
			$GLOBALS['tmpl']->assign('notice_id',$notice_id);
			$GLOBALS['tmpl']->assign('picurl',urlencode($picUrl));
			$GLOBALS['tmpl']->display('cart_wxzf.html');
 		}
	}
	
	public function wx_jspay(){

		$notice_id = intval($_REQUEST['id']);
       // $GLOBALS['user_info']['id']=333;
		$notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id."   and user_id = ".intval($GLOBALS['user_info']['id']));
		
 		if($notice_info['is_paid']==1)
		{
			$data['pay_status'] = 1;
			$data['pay_info'] = '已支付.';
			$data['show_pay_btn'] = 0;
			$data['deal_id'] = $notice_info['deal_id'];
			$GLOBALS['tmpl']->assign('data',$data);
			$GLOBALS['tmpl']->display('pay_order_index.html');
		}else{
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$notice_info['payment_id']);
	 		$class_name = $payment_info['class_name']."_payment";

 			require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
 			$o = new $class_name();
	  		$pay= $o->get_payment_code($notice_id);
           //echo $pay['parameters'];
  	  		$GLOBALS['tmpl']->assign('jsApiParameters',$pay['parameters']);
	  		$notice_info['pay_status'] = 0;
			$notice_info['pay_info'] = '未支付.';
			$notice_info['show_pay_btn'] = 1;
			$notice_info['deal_id'] = $notice_info['deal_id'];
 	  		$GLOBALS['tmpl']->assign('data',$notice_info);

	  		$GLOBALS['tmpl']->display('pay_wx_jspay.html');

  		}
	}

	public function pay_result(){
		$notice_id = intval($_REQUEST['id']);
		if(!$notice_id){
				$data['pay_status'] = 1;
				$data['pay_info'] = '支付失败.';
				$data['show_pay_btn'] = 0;
		}else{
			$notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id."   and user_id = ".intval($GLOBALS['user_info']['id']));
			if($notice_info['is_paid']==1){
				$data['pay_status'] = 1;
				$data['pay_info'] = '支付成功.';
				$data['show_pay_btn'] = 0;
 			}else{
				$data['pay_status'] = 1;
				$data['pay_info'] = '支付失败.';
				$data['show_pay_btn'] = 0;
 			}
		}
		$GLOBALS['tmpl']->assign('data',$data);
		$GLOBALS['tmpl']->display('pay_order_index.html');

	}
	
}
?>