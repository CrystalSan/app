<?php
// +----------------------------------------------------------------------
// | Fanwe 方维众筹商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/shop_lip.php';
 class updsModule extends BaseModule
{
	public function index()
	{	
		$add_order_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."add_order where time < ".time()." and state=0");
		if($add_order_list){
			foreach($add_order_list as $key=>$val){
				$tmp_deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$val['deal_id']);
				$tmp_deal_item_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_item where id = ".$val['deal_item_id']);
				for($i=0;$i<$val['num'];$i++){
					$tmp_num = rand(0,50000);
					$tmp_time = rand(100,86000);
					//获取导入用户
					$tmp_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where login_ip = '' limit ".$tmp_num.",1");
					$tmp_order_info = array();
					$tmp_order_info['deal_id'] = $val['deal_id'];
					$tmp_order_info['deal_item_id'] = $val['deal_item_id'];
					$tmp_order_info['user_id'] = $tmp_user_info['id'];
					$tmp_order_info['user_name'] = $tmp_user_info['user_name'];
					$tmp_order_info['pay_time'] = $val['time']+$tmp_time;
					$tmp_order_info['total_price'] = $tmp_deal_item_info['delivery_fee']+$tmp_deal_item_info['price'];
					$tmp_order_info['delivery_fee'] = $tmp_deal_item_info['delivery_fee'];
					$tmp_order_info['deal_price'] = $tmp_deal_item_info['price'];
					$tmp_order_info['support_memo'] = '支持！加油！';
					$tmp_order_info['payment_id'] = 35;
					$tmp_order_info['credit_pay'] = 0;
					$tmp_order_info['online_pay'] = $tmp_deal_item_info['delivery_fee']+$tmp_deal_item_info['price'];
					$tmp_order_info['deal_name'] = $tmp_deal_info['name'];
					$tmp_order_info['order_status'] = 3;
					$tmp_order_info['create_time'] = $val['time']+$tmp_time;
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$tmp_order_info);
					exit;
					echo $tmp_user_info['user_name']."<br>";
				}
			}
		}
		exit;
		$id = intval($_REQUEST['id']);
		$order_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where deal_id = ".$id." and order_status=3");
		$item_array = array();
		foreach($order_list as $k=>$v){
			$log_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_support_log where deal_id = ".$v['deal_id']." and user_id=".$v['user_id']." and create_time='".$v['pay_time']."' and deal_item_id=".$v['deal_item_id']);
			if($log_list){
				
			}else{
				$tmp_log_info = array();
				$tmp_log_info['deal_id'] = $v['deal_id'];
				$tmp_log_info['deal_item_id'] = $v['deal_item_id'];
				$tmp_log_info['user_id'] = $v['user_id'];
				$tmp_log_info['create_time'] = $v['pay_time'];
				$tmp_log_info['price'] = $v['total_price'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_support_log",$tmp_log_info);
				$tmp_log_id = $GLOBALS['db']->insert_id();
				echo 'add log id = '.$tmp_log_id.' 。';
			}
			if(isset($item_array[$v['deal_item_id']])){
				$item_array[$v['deal_item_id']]['num'] +=1;
				$item_array[$v['deal_item_id']]['total'] +=$v['total_price'];
			}else{
				$item_array[$v['deal_item_id']] = array();
				$item_array[$v['deal_item_id']]['num'] = 1;
				$item_array[$v['deal_item_id']]['total'] = $v['total_price'];
			}
		}
		foreach($item_array as $ki=>$vi){
			echo $ki.'    ';
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_item set support_count = ".intval($vi['num']).",support_amount = ".intval($vi['total'])." where id = ".$ki);
			echo 'update item num = '.$vi['num'];
		}
		syn_deal_status($id);
		syn_deal($id);
		echo 'OK';
	}
}
?>