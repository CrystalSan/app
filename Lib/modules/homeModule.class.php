<?php

require APP_ROOT_PATH.'app/Lib/shop_lip.php';
require APP_ROOT_PATH.'app/Lib/page.php';
class homeModule extends BaseModule
{
	public function __construct(){		
		require_once APP_ROOT_PATH."system/payment/Appay_payment.php";
		$o = new Appay_payment();
		//获取用户积分
		$user_ap = $o->get_user_ap($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_ap",$user_ap);
	}
	public function index()
	{		
                //links
                $g_links =get_link_by_id(14);
                
                $GLOBALS['tmpl']->assign("g_links",$g_links);
		$id = intval($_REQUEST['id']);
		$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id." and is_effect = 1");
		$home_zhichi_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order as z left join ".DB_PREFIX."deal as d on z.deal_id=d.id where z.user_id = ".$id." and d.is_effect = 1");
		if(!$home_user_info)
		{
			app_redirect(url("index"));	
		}
		
		$home_user_info['weibo_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_weibo where user_id = ".$home_user_info['id']);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$page_size = DEAL_PAGE_SIZE;
		$step_size = DEAL_STEP_SIZE;
		
		$step = intval($_REQUEST['step']);
		if($step==0)$step = 1;
		$page = intval($_REQUEST['p']);
		if($page==0)$page = 1;		
		$limit = (($page-1)*$page_size+($step-1)*$step_size).",".$step_size	;
		
		$GLOBALS['tmpl']->assign("current_page",$page);
		
		$condition = " is_delete = 0 and is_effect = 1 and user_id = ".intval($home_user_info['id'])." "; 
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition.=" and 1=1 ";
		}
		elseif (app_conf("INVEST_STATUS")==1)
		{
			$condition.=" and type=0 ";
		}
		elseif(app_conf("INVEST_STATUS")==2)
		{
			$condition.=" and type=1 ";
		}
		
		$GLOBALS['tmpl']->assign('deal_type','home');
		$deal_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where ".$condition);
		/*（home模块）准备虚拟数据 start*/
			$deal_list = array();
			if($deal_count > 0){
				$now_time = get_gmtime();
				$deal_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where ".$condition." order by sort asc limit ".$limit);
				$deal_ids = array();
				foreach($deal_list as $k=>$v)
				{
					$deal_list[$k]['remain_days'] = floor(($v['end_time'] - NOW_TIME)/(24*3600));
					if($v['begin_time'] > $now_time){
						$deal_list[$k]['left_days'] = intval(($now_time - $v['create_time']) / 24 / 3600);
					}
					$deal_ids[] =  $v['id'];
				}
				//获取当前项目列表下的所有子项目
				$temp_virtual_person_list = $GLOBALS['db']->getAll("select deal_id,virtual_person,price from ".DB_PREFIX."deal_item where deal_id in(".implode(",",$deal_ids).") ");
				$virtual_person_list  = array();
				//重新组装一个以项目ID为KEY的 统计所有的虚拟人数和虚拟价格
				foreach($temp_virtual_person_list as $k=>$v){
					$virtual_person_list[$v['deal_id']]['total_virtual_person'] += $v['virtual_person'];
					$virtual_person_list[$v['deal_id']]['total_virtual_price'] += $v['price'] * $v['virtual_person'];
				}
				unset($temp_virtual_person_list);
				//将获取到的虚拟人数和虚拟价格拿到项目列表里面进行统计
				foreach($deal_list as $k=>$v)
				{
					if($v['type']==1)
					{
						$deal_list[$k]['virtual_person']=$deal_list[$k]['invote_num'];
						$deal_list[$k]['support_count'] =$deal_list[$k]['invote_num'];
						$deal_list[$k]['support_amount'] =$deal_list[$k]['invote_money'];
						$deal_list[$k]['percent'] = round(($deal_list[$k]['support_amount'])/$v['limit_price']*100);
						$deal_list[$k]['limit_price_w']=round(($deal_list[$k]['limit_price'])/10000);
						$deal_list[$k]['invote_mini_money_w']=round(($deal_list[$k]['invote_mini_money'])/10000);
					}else
					{
						$deal_list[$k]['virtual_person']=$virtual_person_list[$v['id']]['total_virtual_person'];
						$deal_list[$k]['percent'] = round(($v['support_amount']+$virtual_person_list[$v['id']]['total_virtual_price'])/$v['limit_price']*100);
						$deal_list[$k]['support_count'] += $deal_list[$k]['virtual_person'];
						$deal_list[$k]['support_amount'] += $virtual_person_list[$v['id']]['total_virtual_price'];
					}
					
				}
			}
		/*（home模块）准备虚拟数据 end*/
		//var_dump($deal_list);
// 		$deal_invest_result = get_deal_list($limit,'type=1');
// 		$deal_list['list']=$deal_invest_result['list'];
		$GLOBALS['tmpl']->assign("deal_list",$deal_list);
		$GLOBALS['tmpl']->assign("deal_count",$deal_count);
		$page = new Page($deal_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$g_links =get_link_by_id(14);
                
                $GLOBALS['tmpl']->assign("g_links",$g_links);
		$GLOBALS['tmpl']->assign("page_title","关注的项目");
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));		
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$s = intval($_REQUEST['s']);
		if($s==3)
		$sort_field = " d.support_amount desc ";
		if($s==1)
		$sort_field = " d.support_count desc ";
		if($s==2)
		$sort_field = " d.support_amount - d.limit_price desc ";
		if($s==0)
		$sort_field = " d.end_time asc ";
		
		$GLOBALS['tmpl']->assign("s",$s);
		$f = intval($_REQUEST['f']);
		if($f==0)
		$cond = " 1=1 ";
		if($f==1)
		$cond = " d.begin_time < ".NOW_TIME." and (d.end_time = 0 or d.end_time > ".NOW_TIME.") ";
		if($f==2)
		$cond = " d.end_time <> 0 and d.end_time < ".NOW_TIME." and d.is_success = 1 "; //过期成功
		if($f==3)
		$cond = " d.end_time <> 0 and d.end_time < ".NOW_TIME." and d.is_success = 0 "; //过期失败
		$GLOBALS['tmpl']->assign("f",$f);
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition = " 1=1 ";
		}
		elseif (app_conf("INVEST_STATUS")==1)
		{
			$condition = " d.type=0 ";
		}
		elseif (app_conf("INVEST_STATUS")==2)
		{
			$condition = " d.type=1 ";
		}
		
		$app_sql = " ".DB_PREFIX."deal_focus_log as dfl left join ".DB_PREFIX."deal as d on d.id = dfl.deal_id where $condition and dfl.user_id = ".intval($GLOBALS['user_info']['id']).
				   " and d.is_effect = 1 and d.is_delete = 0 and ".$cond." ";
		
		$deal_list = $GLOBALS['db']->getAll("select d.*,dfl.id as fid from ".$app_sql." order by ".$sort_field." limit ".$limit);
		$deal_count = $GLOBALS['db']->getOne("select count(*) from ".$app_sql);
		foreach($deal_list as $k=>$v)
		{
			$deal_list[$k]['remain_days'] = ceil(($v['end_time'] - NOW_TIME)/(24*3600));
			$deal_list[$k]['percent'] = round($v['support_amount']/$v['limit_price']*100);
			if($v['type']== 0){
				$deal_list[$k]['support_count']= $deal_list[$k]['support_count']+ $deal_list[$k]['virtual_num'];
			}
		}
		
		$page = new Page($deal_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$guan_num = 10;
		$guan_page = intval($_REQUEST['p1']);
		if($guan_page==0)$guan_page = 1;		
		$guan_limit = (($guan_page-1)*$guan_num).",".$guan_num;
		$GLOBALS['tmpl']->assign("guan_page",$guan_page);
		$zhi_num = 10;
		$zhi_page = intval($_REQUEST['p2']);
		if($zhi_page==0)$zhi_page = 1;		
		$zhi_limit = (($zhi_page-1)*$zhi_num).",".$zhi_num;
		$GLOBALS['tmpl']->assign("zhi_page",$zhi_page);
		$shulian=$GLOBALS['db']->getOne("select count(*) as num from ".DB_PREFIX."deal_focus_log as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where a.user_id=".$id." and b.is_effect=1");
		$guanzhu=$GLOBALS['db']->getAll("select a.*,b.* from ".DB_PREFIX."deal_focus_log as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where a.user_id=".$id." and b.is_effect=1 limit ".$guan_limit);

		$page1 = new Page($shulian,$guan_num);   //初始化分页对象 		
		$p1  =  $page1->show2('p1');
		$GLOBALS['tmpl']->assign('pages1',$p1);
		$zshu=$GLOBALS['db']->getOne("select count(*) as num from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.pay_time <> 0 and a.user_id=".$id);
		$zhichi=$GLOBALS['db']->getAll("select a.*,b.image from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.pay_time <> 0 and a.user_id=".$id." limit ".$zhi_limit);
		$page2 = new Page($zshu,$zhi_num);   //初始化分页对象 		
		$p2  =  $page2->show2('p2');
		$GLOBALS['tmpl']->assign('pages2',$p2);
		$GLOBALS['tmpl']->assign('zshu',$zshu);
		$GLOBALS['tmpl']->assign('guanzhu',$guanzhu);
		$GLOBALS['tmpl']->assign('zhichi',$zhichi);
		$GLOBALS['tmpl']->assign('shulian',$shulian);
		$GLOBALS['tmpl']->display("home_index.html");
	}
	
	
	public function support()
	{	
                    //links
                $g_links =get_link_by_id(14);
                
                $GLOBALS['tmpl']->assign("g_links",$g_links);
		$GLOBALS['tmpl']->assign("page_title","最新动态");

		$id = intval($_REQUEST['id']);
		
		$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id." and is_effect = 1");
		if(!$home_user_info)
		{
			app_redirect(url("index"));	
		}
		
		$home_user_info['weibo_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_weibo where user_id = ".$home_user_info['id']);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$page_size = DEAL_PAGE_SIZE;
		$step_size = DEAL_STEP_SIZE;
		
		$step = intval($_REQUEST['step']);
		if($step==0)$step = 1;
		$page = intval($_REQUEST['p']);
		if($page==0)$page = 1;		
		$limit = (($page-1)*$page_size+($step-1)*$step_size).",".$step_size	;
		
		$GLOBALS['tmpl']->assign("current_page",$page);
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition=" 1=1 ";
		}
		elseif (app_conf("INVEST_STATUS")==1)
		{
			$condition=" d.type=0 ";
		}
		elseif(app_conf("INVEST_STATUS")==2)
		{
			$condition=" d.type=1 ";
		}
		
		$sql = "select distinct(d.id) as id,d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_support_log as dsl on d.id = dsl.deal_id ".
			   " where $condition and dsl.user_id = ".$home_user_info['id']." order by d.sort asc limit ".$limit;
	
		$sql_count = "select count(distinct(d.id)) from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_support_log as dsl on d.id = dsl.deal_id ".
			   " where $condition and dsl.user_id = ".$home_user_info['id'];
		//得到当前页面项目信息
	
		$deal_count = $GLOBALS['db']->getOne($sql_count);
		/*（home模块）准备虚拟数据 start*/
			$deal_list = array();
			if($deal_count > 0){
				$now_time = get_gmtime();
				$deal_list = $GLOBALS['db']->getAll($sql);
				
				$deal_ids = array();
				foreach($deal_list as $k=>$v)
				{
					$deal_list[$k]['remain_days'] = floor(($v['end_time'] - NOW_TIME)/(24*3600));
					if($v['begin_time'] > $now_time){
						$deal_list[$k]['left_days'] = intval(($now_time - $v['create_time']) / 24 / 3600);
					}
					$deal_ids[] =  $v['id'];
				}
				//获取当前项目列表下的所有子项目
				$temp_virtual_person_list = $GLOBALS['db']->getAll("select deal_id,virtual_person,price from ".DB_PREFIX."deal_item where deal_id in(".implode(",",$deal_ids).") ");
				$virtual_person_list  = array();
				//重新组装一个以项目ID为KEY的 统计所有的虚拟人数和虚拟价格
				foreach($temp_virtual_person_list as $k=>$v){
					$virtual_person_list[$v['deal_id']]['total_virtual_person'] += $v['virtual_person'];
					$virtual_person_list[$v['deal_id']]['total_virtual_price'] += $v['price'] * $v['virtual_person'];
				}
				unset($temp_virtual_person_list);
				//将获取到的虚拟人数和虚拟价格拿到项目列表里面进行统计
				foreach($deal_list as $k=>$v)
				{
					if($v['type']==1)
					{
						$deal_list[$k]['virtual_person']=$deal_list[$k]['invote_num'];
						$deal_list[$k]['support_count'] =$deal_list[$k]['invote_num'];
						$deal_list[$k]['support_amount'] =$deal_list[$k]['invote_money'];
						$deal_list[$k]['percent'] = round(($deal_list[$k]['support_amount'])/$v['limit_price']*100);
						$deal_list[$k]['limit_price_w']=round(($deal_list[$k]['limit_price'])/10000);
						$deal_list[$k]['invote_mini_money_w']=round(($deal_list[$k]['invote_mini_money'])/10000);
					}
					else
					{
						$deal_list[$k]['virtual_person']=$virtual_person_list[$v['id']]['total_virtual_person'];
						$deal_list[$k]['percent'] = round(($v['support_amount']+$virtual_person_list[$v['id']]['total_virtual_price'])/$v['limit_price']*100);
						$deal_list[$k]['support_count'] += $deal_list[$k]['virtual_person'];
						$deal_list[$k]['support_amount'] += $virtual_person_list[$v['id']]['total_virtual_price'];
					}
				}
			}
		/*（home模块）准备虚拟数据 end*/
		$GLOBALS['tmpl']->assign("deal_list",$deal_list);
		$GLOBALS['tmpl']->assign("deal_count",$deal_count);
		$page = new Page($deal_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);		
		
		$GLOBALS['tmpl']->display("home_support.html");
	}
	

	
	
}
?>