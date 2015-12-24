<?php
require APP_ROOT_PATH.'app/Lib/shop_lip.php';
require APP_ROOT_PATH.'app/Lib/page.php';
class accountModule extends BaseModule
{
	public function __construct(){		
		require_once APP_ROOT_PATH."system/payment/Appay_payment.php";
		$o = new Appay_payment();
		//获取用户积分
		$user_ap = $o->get_user_ap($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("user_ap",$user_ap);
	}
	public function wodexiaoxi(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$guanzhu_all_num = 10;
		$guanzhu_page = intval($_REQUEST['p1']);
		if($guanzhu_page==0)$guanzhu_page = 1;		
		$limit_guanzhu = (($guanzhu_page-1)*$guanzhu_all_num).",".$guanzhu_all_num;
		$GLOBALS['tmpl']->assign("guanzhu_page",$guanzhu_page);
		$zhihi_all_num = 10;
		$zhichi_page = intval($_REQUEST['p2']);
		if($zhichi_page==0)$zhichi_page = 1;		
		$limit_zhichi = (($zhichi_page-1)*$zhihi_all_num).",".$zhihi_all_num;
		$GLOBALS['tmpl']->assign("zhichi_page",$zhichi_page);
		$guanzhu_num=$GLOBALS['db']->getOne("select count(a.id) as num from ".DB_PREFIX."deal_focus_log as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.user_id=".$GLOBALS['user_info']['id']);
		$guanzhu=$GLOBALS['db']->getAll("select a.create_time , b.name from ".DB_PREFIX."deal_focus_log as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.user_id=".$GLOBALS['user_info']['id']." limit ".$limit_guanzhu);
		$zhichi_num=$GLOBALS['db']->getOne("select count(a.id) as num from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.pay_time <> 0 and a.user_id=".$GLOBALS['user_info']['id']);
		$zhichi=$GLOBALS['db']->getAll("select a.*,b.name as deal_name,b.image from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where b.is_effect=1 and a.pay_time <> 0 and a.user_id=".$GLOBALS['user_info']['id']." limit ".$limit_zhichi);
		$page1 = new Page($guanzhu_num,$guanzhu_all_num);   //初始化分页对象 		
		$p1  =  $page1->show2('p1');
		$GLOBALS['tmpl']->assign('pages1',$p1);
		$page2 = new Page($zhichi_num,$zhihi_all_num);   //初始化分页对象 		
		$p2  =  $page2->show2('p2');
		$GLOBALS['tmpl']->assign('pages2',$p2);
		$GLOBALS['tmpl']->assign("guanzhu",$guanzhu);
		$GLOBALS['tmpl']->assign("zhichi",$zhichi);
		$GLOBALS['tmpl']->display("account_wodexiaoxi.html");
		}	
	public function zhichi(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$zhichi=$GLOBALS['db']->getAll("select a.*,b.image from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where a.user_id=".$GLOBALS['user_info']['id']." and a.is_success=1");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		$GLOBALS['tmpl']->display("account_zhichi.html");
		}
	public function zhichiwei(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$zhichi=$GLOBALS['db']->getAll("select a.*,b.image from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where a.user_id=".$GLOBALS['user_info']['id']." and a.is_success=0");
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		$GLOBALS['tmpl']->display("account_zhichi.html");
		}
	public function chouji(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$chouji=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where user_id=".$GLOBALS['user_info']['id']." and is_success=1");
		 $GLOBALS['tmpl']->assign("chouji",$chouji);
		$GLOBALS['tmpl']->display("account_chouji.html");
		}
	public function choujiwei(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$chouji=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where user_id=".$GLOBALS['user_info']['id']." and is_success=0");
		 $GLOBALS['tmpl']->assign("chouji",$chouji);
		$GLOBALS['tmpl']->display("account_chouji.html");
		}
	public function tree($plList){
			global $tree;
			$tree = $plList;
			foreach($tree as $key=>$value){
				if($value['tree']==0){
					//把当前数组当前tree值改为1
					$tree[$key]['tree'] = 1;
					//查询数据库找到所有子评论
					$sonPlList = array();
					$sonPlList = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_comment where pid =".$value['id']);
					
					if(!empty($sonPlList)){
						//把当前评论下的所有数组插入到临时数组
						$tmpArr = array();
						for($k=0 ; $k<count($tree) ; $k++){
							if($k>$key){
								$tmpArr[] = $tree[$k];	
							}
						}
						//把自评论插入到此条评论下
						$tmpKey = $key;
						foreach($sonPlList as $a=>$v){
							$tmpKey++;
							$tree[$tmpKey] = $v;
							$tree[$tmpKey]['tree'] = 0;
							$tree[$tmpKey]['lvl'] = $value['lvl']+1;
							$tmpS = '';
							for($s=0;$s<$tree[$tmpKey]['lvl'];$s++){
								$tmpS .= '&nbsp;&nbsp;&nbsp;&nbsp;';
							}
							$tree[$tmpKey]['lvls'] =  $tmpS;
						}
						//把当前位置之后的数组内容插入到数组最后	
						foreach($tmpArr as $b=>$s){
							$tmpKey++;
							$tree[$tmpKey] = $s;
						}
						//递归继续查找
						$this->tree($tree);
					}
				}
			}
			return $tree;
		}
	public function pinglun(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		//我发布的评论
		$pinglun['faping']=$GLOBALS['db']->getAll("select a.*,b.image,b.name,b.limit_price,b.is_effect from ".DB_PREFIX."deal_comment as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where user_id=".$GLOBALS['user_info']['id']." and pid = 0");
		foreach($pinglun['faping'] as $key=>$value){
			$pinglun['faping'][$key]['tree'] = 0;
			$pinglun['faping'][$key]['lvl'] = 0;
		}
		$faping = array();
		$faping = $this->tree($pinglun['faping']);
		//我发布的评论end
		//我发布的项目的评论
		$pinglun['xiangmupl']=$GLOBALS['db']->getAll("select a.*,b.image,b.name,b.limit_price,b.is_effect from ".DB_PREFIX."deal_comment as a left join ".DB_PREFIX."deal as b on a.deal_id=b.id where deal_user_id=".$GLOBALS['user_info']['id']." and pid=0");
		foreach($pinglun['xiangmupl'] as $key=>$value){
			$pinglun['xiangmupl'][$key]['tree'] = 0;
			$pinglun['xiangmupl'][$key]['lvl'] = 0;
		}
		$xiangmupl = array();
		$xiangmupl = $this->tree($pinglun['xiangmupl']);
		//我发布的项目的评论
		$pinglun['huifu']=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_comment where pid=id");
		foreach($pinglun as $a=>$b){
			$xiangmu=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where id=".$b['deal_id']);
			}
		$fabuxiang=array();
		$fabuxiang = $this->tree($pinglun['huifu']);
		$GLOBALS['tmpl']->assign("xiangmupl",$xiangmupl);
		 $GLOBALS['tmpl']->assign("fabuxiang",$fabuxiang);
		$GLOBALS['tmpl']->display("account_pinglun.html");
		}
	public function touzi(){
		$g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$zhihi_all_num = 10;
		$zhichi_page = intval($_REQUEST['p1']);
		if($zhichi_page==0)$zhichi_page = 1;		
		$limit_zhichi = (($zhichi_page-1)*$zhihi_all_num).",".$zhihi_all_num;
		$GLOBALS['tmpl']->assign("zhichi_page",$zhichi_page);
		$fabu_all_num = 10;
		$fabu_page = intval($_REQUEST['p2']);
		if($fabu_page==0)$fabu_page = 1;		
		$limit_fabu = (($fabu_page-1)*$fabu_all_num).",".$fabu_all_num;
		$GLOBALS['tmpl']->assign("fabu_page",$fabu_page);
		$zhichi_num= $GLOBALS['db']->getOne("select count(*) as num from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on b.id=a.deal_id where a.user_id = ".$GLOBALS['user_info']['id']);
		$zhichi= $GLOBALS['db']->getAll("select a.*,b.* from ".DB_PREFIX."deal_order as a left join ".DB_PREFIX."deal as b on b.id=a.deal_id where a.user_id = ".$GLOBALS['user_info']['id']." limit ".$limit_zhichi);
		$page1 = new Page($zhichi_num,$zhihi_all_num);   //初始化分页对象 		
		$p1  =  $page1->show2('p1');
		$GLOBALS['tmpl']->assign('pages1',$p1);
		$fabu_num=$GLOBALS['db']->getOne("select count(*) as num from ".DB_PREFIX."deal where user_id=".$GLOBALS['user_info']['id']);
		$fabu=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where user_id=".$GLOBALS['user_info']['id']." limit ".$limit_fabu);
		$page2 = new Page($fabu_num,$fabu_all_num);   //初始化分页对象 		
		$p2  =  $page2->show2('p2');
		$GLOBALS['tmpl']->assign('pages2',$p2);
/*		$touzi['zhichi']=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id=".$GLOBALS['user_info']['id']);
		$touzi['fabu']=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where user_id=".$GLOBALS['user_info']['id']);*/
		 $GLOBALS['tmpl']->assign("zhichi",$zhichi);
		 $GLOBALS['tmpl']->assign("fabu",$fabu);
		$GLOBALS['tmpl']->display("account_touzi.html");
		}
	public function xitong(){
		$g_links =get_link_by_id(14);
    $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$xixiao=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."xiao");
		$GLOBALS['tmpl']->assign("xixiao",$xixiao);
		$GLOBALS['tmpl']->display("account_xixiao.html");
		}
	//支持的项目
	public function index()
	{	
                  //links
                $g_links =get_link_by_id(14);
                
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));		
		$GLOBALS['tmpl']->assign("page_title","支持的项目");
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition = " 1=1 ";
		}
		elseif (app_conf("INVEST_STATUS")==1)
		{
			$condition = " de.type=0 ";
		}
		elseif (app_conf("INVEST_STATUS")==2)
		{
			$condition = " de.type=1 ";
		}
		
		$order_list = $GLOBALS['db']->getAll("select do.* from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal as de on de.id=do.deal_id where $condition and do.user_id = ".intval($GLOBALS['user_info']['id'])." and do.type=0 order by do.create_time desc limit ".$limit);
		foreach($order_list as $k=>$v){
			if($v['repay_make_time']==0&&$v['repay_time']>0){
				$left_date=intval(app_conf("REPAY_MAKE"))?7:intval(app_conf("REPAY_MAKE"));
				$repay_make_date=$v['repay_time']+$left_date*24*3600;
				if($repay_make_date<=get_gmtime()){
 					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set repay_make_time =  ".get_gmtime()." where id = ".$v['id'] );
					$order_list[$k]['repay_make_time']=get_gmtime();
				}
			}
			if($v['is_ap']==1){
				$order_list[$k]['ap_price'] = $v['deal_price'] * $v['ap_ratio'];
				$order_list[$k]['delivery_ap'] = $v['delivery_fee'] * $v['ap_ratio'];
			}
		}
		$order_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal as de on de.id=do.deal_id where $condition and do.user_id = ".intval($GLOBALS['user_info']['id'])." and do.type=0");
		
		$page = new Page($order_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$deal_ids=array();
		foreach($order_list as $k=>$v){
			$deal_ids[] =  $v['deal_id'];
		}
		if($deal_ids!=null){
			$deal_list_array=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where  is_effect = 1 and type=0 and is_delete = 0 and id in (".implode(',',$deal_ids).")");
			$deal_list=array();
			foreach($deal_list_array as $k=>$v){
				if($v['id']){
					$deal_list[$v['id']]=$v;
				}
			}
	 		//unset($deal_list_array);
			foreach($order_list as $k=>$v)
			{
	//			$order_list[$k]['deal_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$v['deal_id']." and is_effect = 1 and is_delete = 0");
	 			$order_list[$k]['deal_info'] =$deal_list[$v['deal_id']];
			}
			 
			$GLOBALS['tmpl']->assign('order_list',$order_list);
		}
		
		$GLOBALS['tmpl']->display("account_index.html");
	}
	//领投列表
	public function lead(){
		$g_links =get_link_by_id(14);
		$GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));		
		$GLOBALS['tmpl']->assign("page_title","跟投的项目");
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$GLOBALS['tmpl']->display("account_lead.html");
	}
	//跟投列表
	public function vote(){
		$g_links =get_link_by_id(14);
		$GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));		
		$GLOBALS['tmpl']->assign("page_title","跟投的项目");
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$GLOBALS['tmpl']->display("account_vote.html");
	}
	
	public function focus()
	{
                    //links
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
			$deal_list[$k]['ap_amount'] = $v['support_amount'] * $v['ap_ratio'];
		}
		
		$page = new Page($deal_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$GLOBALS['tmpl']->display("account_focus.html");
	}
	
	public function del_focus()
	{
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
		$id = intval($_REQUEST['id']);
		$deal_id = $GLOBALS['db']->getOne("select deal_id from ".DB_PREFIX."deal_focus_log where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_focus_log where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
		$GLOBALS['db']->query("update ".DB_PREFIX."deal set focus_count = focus_count - 1 where id = ".intval($deal_id));
		$GLOBALS['db']->query("delete from ".DB_PREFIX."user_deal_notify where user_id = ".intval($GLOBALS['user_info']['id'])." and deal_id = ".$deal_id);
							
		app_redirect_preview();
	}
	
	public function incharge()
	{		
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));		
		$GLOBALS['tmpl']->assign("page_title","充值");
		$GLOBALS['tmpl']->display("account_incharge.html");
	}
	
	public function do_incharge()
	{		
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		$money = doubleval($_REQUEST['money']);
		if($money<=0)
		{
			showErr("充值的金额不正确",$ajax,"");
		}
		
		showSuccess("",$ajax,url("account#pay",array("money"=>round($money*100))));
	}
	
	public function pay()
	{		
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
		$GLOBALS['tmpl']->assign("page_title","支付");
		$money = doubleval(intval($_REQUEST['money'])/100);
		if($money<=0)
		{
			app_redirect(url("account#incharge"));
		}
		
		$GLOBALS['tmpl']->assign("money",$money);
		$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and online_pay=1 order by sort asc ");
		$payment_html = "";
		foreach($payment_list as $k=>$v)
		{
			$class_name = $v['class_name']."_payment";
			require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
			$o = new $class_name;
			$payment_html .= "<div>".$o->get_display_code()."<div class='blank'></div></div>";
		}
		$GLOBALS['tmpl']->assign("payment_html",$payment_html);
		$GLOBALS['tmpl']->display("account_pay.html");
	}
	
	public function go_pay()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$payment_id = intval($_REQUEST['payment']);
		if($payment_id==0)
		{
			app_redirect(url("account#pay"));
		}
		
		$money = doubleval($_REQUEST['money']);
		if($money<=0)
		{
			app_redirect(url("account#pay"));
		}
		
		$payment_notice['create_time'] = NOW_TIME;
		$payment_notice['user_id'] = intval($GLOBALS['user_info']['id']);
		$payment_notice['payment_id'] = $payment_id;
		$payment_notice['money'] = $money;
		$payment_notice['bank_id'] = strim($_REQUEST['bank_id']);
		$payment_notice['is_mortgate']=intval($_REQUEST['is_mortgate']);
		if(!empty($_REQUEST['is_mortgate'])){
			$payment_notice['is_mortgate']=intval($_REQUEST['is_mortgate']);
		}
		
		do{
			$payment_notice['notice_sn'] = to_date(NOW_TIME,"Ymd").rand(100,999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$payment_notice,"INSERT","","SILENT");
			$notice_id = $GLOBALS['db']->insert_id();
		}while($notice_id==0);
		
		if($payment_id==35){
			app_redirect(url("account#jump_wxzf",array("id"=>$notice_id)));
		}else{
			app_redirect(url("account#jump",array("id"=>$notice_id)));
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
		$o = new $class_name;
		
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
			$GLOBALS['tmpl']->display('account_cart_wxzf.html');
 		}
	}
	
	//我的项目列表
	public function project()
	{
                    //links
        $g_links =get_link_by_id(14);
        
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		$GLOBALS['tmpl']->assign("page_title","我的项目列表");
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));	

		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$search_type=isset($_POST['search_type'])?intval($_POST['search_type']):-1;
		$GLOBALS['tmpl']->assign("search_type",$search_type);
		$search_name=strim($_POST['search_name']);
		$GLOBALS['tmpl']->assign("search_name",$search_name);
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition = " 1=1 ";

		}
		elseif (app_conf("INVEST_STATUS")==1)
		{	
			$condition = " type=0 ";	
		}
		elseif (app_conf("INVEST_STATUS")==2)
		{
			$condition = " type=1 ";
		}
		
		if($search_type!=-1||$search_name!=null)
		{
			if($search_type==-1)
			{
				$condition.=" and name LIKE '%".$search_name."%' ";
			}else{
				$condition.=" and type=$search_type and name LIKE '%".$search_name."%' ";
			}
		}
		
		$deal_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where $condition and user_id = ".intval($GLOBALS['user_info']['id'])." and is_delete = 0 order by id desc,create_time desc limit ".$limit);
		$deal_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where $condition and user_id = ".intval($GLOBALS['user_info']['id'])." and is_delete = 0");
		$page = new Page($deal_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$GLOBALS['tmpl']->display("account_project.html");
	}
	
	
	public function credit()
	{
                    //links
                $g_links =get_link_by_id(14);
                
                $GLOBALS['tmpl']->assign("g_links",$g_links);
		$GLOBALS['tmpl']->assign("page_title","收支明细");
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$log_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where user_id = ".intval($GLOBALS['user_info']['id'])." order by log_time desc,id desc limit ".$limit);
		$log_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where user_id = ".intval($GLOBALS['user_info']['id']));
	
		$page = new Page($log_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('log_list',$log_list);
		
		$GLOBALS['tmpl']->display("account_credit.html");
	}
	
	public function del_order()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where order_status = 0 and user_id = ".intval($GLOBALS['user_info']['id'])." and id = ".$order_id);
		if(!$order_info)
		{
			showErr("无效的订单",$ajax,"");
		}
		else
		{
			$money = $order_info['credit_pay'];
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order where id = ".$order_id." and user_id = ".intval($GLOBALS['user_info']['id'])." and order_status = 0");
			if($GLOBALS['db']->affected_rows()>0)
			{
				if($money>0)
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					modify_account(array("money"=>$money),intval($GLOBALS['user_info']['id']),"删除".$order_info['deal_name']."项目支付，退回支付款。");						
				}
			}
			showSuccess("",$ajax,get_gopreview());
		}
	}
	//支持的项目详情
	public function view_order()
	{
		   //links
		$g_links =get_link_by_id(14);
		
		$GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$GLOBALS['tmpl']->assign("page_title","支持的项目详情");
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
		if(!$order_info)
		{
			showErr("无效的项目支持",0,get_gopreview());
		}
		//========如果超过系统设置的时间，则自动设置收到回报 start
		if($order_info['repay_make_time']==0){
			$left_date=intval(app_conf("REPAY_MAKE"))?7:intval(app_conf("REPAY_MAKE"));
			$repay_make_date=$order_info['repay_time']+$left_date*24*3600;
			if($repay_make_date>get_gmtime()&&$order_info['repay_time']>0){
				$order_info['repay_make_date']=date('Y-m-d H:i:s',$repay_make_date);
			}else{
 				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set repay_make_time =  ".get_gmtime()." where id = ".$id);
				$order_info['repay_make_time']=get_gmtime();
			}
		}
		//=============如果超过系统设置的时间，则自动设置收到回报 end
		$GLOBALS['tmpl']->assign("order_info",$order_info);
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$order_info['deal_id']." and is_delete = 0 and is_effect = 1");
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		if($order_info['order_status'] == 0)
		{
			$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and online_pay=1 order by sort asc ");
			$payment_html = "";
			foreach($payment_list as $k=>$v)
			{
				$class_name = $v['class_name']."_payment";
				require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
				$o = new $class_name;
				$payment_html .= "<div>".$o->get_display_code()."<div class='blank'></div></div>";
			}
			$GLOBALS['tmpl']->assign("payment_html",$payment_html);
			
			$max_pay = $order_info['total_price'] - $order_info['credit_pay'];
			$GLOBALS['tmpl']->assign("max_pay",$max_pay);
		}
		
		
		$GLOBALS['tmpl']->display("account_view_order.html");
	}
	
	//支持的项目详情
	public function apview_order()
	{
		   //links
		$g_links =get_link_by_id(14);
		
		$GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$GLOBALS['tmpl']->assign("page_title","支持的项目详情");
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
		if(!$order_info)
		{
			showErr("无效的项目支持",0,get_gopreview());
		}
		//========如果超过系统设置的时间，则自动设置收到回报 start
		if($order_info['repay_make_time']==0){
			$left_date=intval(app_conf("REPAY_MAKE"))?7:intval(app_conf("REPAY_MAKE"));
			$repay_make_date=$order_info['repay_time']+$left_date*24*3600;
			if($repay_make_date>get_gmtime()&&$order_info['repay_time']>0){
				$order_info['repay_make_date']=date('Y-m-d H:i:s',$repay_make_date);
			}else{
 				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set repay_make_time =  ".get_gmtime()." where id = ".$id);
				$order_info['repay_make_time']=get_gmtime();
			}
		}
		//=============如果超过系统设置的时间，则自动设置收到回报 end
		$order_info['ap_price'] = $order_info['deal_price']*$order_info['ap_ratio'];
		$order_info['delivery_ap'] = $order_info['delivery_fee']*$order_info['ap_ratio'];
		$order_info['total_ap'] = $order_info['delivery_ap']+$order_info['ap_price'];
		$GLOBALS['tmpl']->assign("order_info",$order_info);
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$order_info['deal_id']." and is_delete = 0 and is_effect = 1");
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		if($order_info['order_status'] == 0)
		{
			$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and online_pay=1 order by sort asc ");
			$payment_html = "";
			foreach($payment_list as $k=>$v)
			{
				$class_name = $v['class_name']."_payment";
				require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
				$o = new $class_name;
				$payment_html .= "<div>".$o->get_display_code()."<div class='blank'></div></div>";
			}
			$GLOBALS['tmpl']->assign("payment_html",$payment_html);
			
			$max_pay = $order_info['total_price'] - $order_info['credit_pay'];
			$GLOBALS['tmpl']->assign("max_pay",$max_pay);
		}
		
		
		$GLOBALS['tmpl']->display("account_apview_order.html");
	}
	
	public function go_order_pay()
	{

		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$id = intval($_REQUEST['order_id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id'])." and order_status = 0");
		
		if(!$order_info)
		{
			showErr("项目支持已支付",0,get_gopreview());
		}
		else
		{
			$credit = doubleval($_REQUEST['credit']);
			$payment_id = intval($_REQUEST['payment']);
			
			if($credit>0)
			{				
				$max_pay = $order_info['total_price'] - $order_info['credit_pay'];
				$max_credit= $max_pay<$GLOBALS['user_info']['money']?$max_pay:$GLOBALS['user_info']['money'];
				if($max_credit<0){
					$max_credit=0;
				}
				$credit = $credit>$max_credit?$max_credit:$credit;		
			
				if($credit>0)
				{	
	 				require_once APP_ROOT_PATH."system/libs/user.php";
					$re=modify_account(array("money"=>"-".$credit),intval($GLOBALS['user_info']['id']),"支持".$order_info['deal_name']."项目支付");		
 					if($re){
						
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set credit_pay = credit_pay + ".$credit." where id = ".$order_info['id']);//追加使用余额支付
	 				}
				}		
			}	
			$result = pay_order($order_info['id']);
 			if($result['status']==0)
			{
				$money = $result['money'];
				$payment_notice['create_time'] = NOW_TIME;
				$payment_notice['user_id'] = intval($GLOBALS['user_info']['id']);
				$payment_notice['payment_id'] = $payment_id;
				$payment_notice['money'] = $money;
				$payment_notice['bank_id'] = strim($_REQUEST['bank_id']);
				$payment_notice['order_id'] = $order_info['id'];
				$payment_notice['memo'] = $order_info['support_memo'];
				$payment_notice['deal_id'] = $order_info['deal_id'];
				$payment_notice['deal_item_id'] = $order_info['deal_item_id'];
				$payment_notice['deal_name'] = $order_info['deal_name'];
				
				do{
					$payment_notice['notice_sn'] = to_date(NOW_TIME,"Ymd").rand(100,999);
					$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$payment_notice,"INSERT","","SILENT");
					$notice_id = $GLOBALS['db']->insert_id();
				}while($notice_id==0);
				if($payment_id==35){
					app_redirect(url("account#jump_wxzf",array("id"=>$notice_id)));
				}else{
					app_redirect(url("account#jump",array("id"=>$notice_id)));
				}
			}
			else
			{
				app_redirect(url("account#view_order",array("id"=>$order_info['id'])));  
			}
		}	
		
	}
	
	public function go_aporder_pay()
	{

		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$id = intval($_REQUEST['order_id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id'])." and order_status = 0");
		
		if(!$order_info)
		{
			showErr("项目支持已支付",0,get_gopreview());
		}
		else
		{
			$credit = doubleval($_REQUEST['credit']);
			$payment_id = intval($_REQUEST['payment']);
			
			if($credit>0)
			{				
				$max_pay = $order_info['total_price'] - $order_info['credit_pay'];
				$max_credit= $max_pay<$GLOBALS['user_info']['money']?$max_pay:$GLOBALS['user_info']['money'];
				if($max_credit<0){
					$max_credit=0;
				}
				$credit = $credit>$max_credit?$max_credit:$credit;		
			
				if($credit>0)
				{	
	 				require_once APP_ROOT_PATH."system/libs/user.php";
					$re=modify_account(array("money"=>"-".$credit),intval($GLOBALS['user_info']['id']),"支持".$order_info['deal_name']."项目支付");		
 					if($re){
						
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set credit_pay = credit_pay + ".$credit." where id = ".$order_info['id']);//追加使用余额支付
	 				}
				}		
			}	
			$result = pay_order($order_info['id']);
 			if($result['status']==0)
			{
				$money = $result['money'];
				$payment_notice['create_time'] = NOW_TIME;
				$payment_notice['user_id'] = intval($GLOBALS['user_info']['id']);
				$payment_notice['payment_id'] = $payment_id;
				$payment_notice['money'] = $money;
				$payment_notice['bank_id'] = strim($_REQUEST['bank_id']);
				$payment_notice['order_id'] = $order_info['id'];
				$payment_notice['memo'] = $order_info['support_memo'];
				$payment_notice['deal_id'] = $order_info['deal_id'];
				$payment_notice['deal_item_id'] = $order_info['deal_item_id'];
				$payment_notice['deal_name'] = $order_info['deal_name'];
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
			else
			{
				app_redirect(url("account#apview_order",array("id"=>$order_info['id'])));  
			}
		}	
		
	}
	//我的项目-支持列表
	public function support()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$GLOBALS['tmpl']->assign("page_title","我的项目-支持列表");
		$deal_id = intval($_REQUEST['id']);
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id." and is_delete = 0 and is_effect = 1 and is_success = 1 and user_id = ".intval($GLOBALS['user_info']['id']));
		
		if(!$deal_info)
		{
			app_redirect_preview();
		}
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)$page = 1;		
		$limit = (($page-1)*$page_size).",".$page_size	;

		$support_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where deal_id = ".$deal_id." and order_status = 3 and is_refund = 0 order by create_time desc limit ".$limit);
		$support_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where deal_id = ".$deal_id." and order_status = 3 and is_refund = 0");
		foreach($support_list as $k=>$v){
			if($deal_info['type']==1){
				//项目金额
				$support_list[$k]['stock_value'] =number_format($deal_info['limit_price'],2);
				$support_list[$k]['money'] =$v['total_price'];
				//用户所占股份
 				$support_list[$k]['user_stock']= ($support_list[$k]['money']/$deal_info['limit_price'])*$deal_info['transfer_share'];			
			}
		}
		$GLOBALS['tmpl']->assign("support_list",$support_list);
		$page = new Page($support_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);	
		
		$GLOBALS['tmpl']->display("account_support.html");
	}
	
	public function save_repay()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id." and order_status = 3 and is_refund = 0");
		if(!$order_info)
		{
			showErr("无权为该订单设置回报",$ajax);
		}
		
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$order_info['deal_id']." and is_delete = 0 and is_effect = 1 and is_success = 1 and user_id = ".intval($GLOBALS['user_info']['id']));
		if(!$deal_info)
		{
			showErr("无权为该订单设置回报",$ajax);
		}
		
		$order_info['repay_time'] = NOW_TIME;
		$order_info['repay_memo'] = strim($_REQUEST['repay_memo']);
		
		if($order_info['repay_memo']=="")
		{
			showErr("请输入回报内容",$ajax);
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order_info,"UPDATE","id=".$order_info['id']);
		
		send_notify($order_info['user_id'],"您支持的项目".$order_info['deal_name']."回报已发放","account#view_order","id=".$order_info['id']);
		showSuccess("回报设置成功",$ajax);		
		
	}
	//我的冻结资金列表
	public function dongjie()
	{
                    //links
        $g_links =get_link_by_id(14);
        
        $GLOBALS['tmpl']->assign("g_links",$g_links);
		$GLOBALS['tmpl']->assign("page_title","我的冻结资金列表");
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));	

		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$search_type=isset($_POST['search_type'])?intval($_POST['search_type']):-1;
		$GLOBALS['tmpl']->assign("search_type",$search_type);
		$search_name=strim($_POST['search_name']);
		$GLOBALS['tmpl']->assign("search_name",$search_name);
		
		if(app_conf("INVEST_STATUS")==0)
		{
			$condition = " 1=1 ";

		}
		elseif (app_conf("INVEST_STATUS")==1)
		{	
			$condition = " type=0 ";	
		}
		elseif (app_conf("INVEST_STATUS")==2)
		{
			$condition = " type=1 ";
		}
		
		if($search_type!=-1||$search_name!=null)
		{
			if($search_type==-1)
			{
				$condition.=" and name LIKE '%".$search_name."%' ";
			}else{
				$condition.=" and type=$search_type and name LIKE '%".$search_name."%' ";
			}
		}
		
		$deal_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where $condition and user_id = ".intval($GLOBALS['user_info']['id'])." and is_success = 1 order by id desc,create_time desc limit ".$limit);
		$deal_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where $condition and user_id = ".intval($GLOBALS['user_info']['id'])." and is_success = 1");
		//检查已放款数额
		if($deal_list){
			foreach($deal_list as $k=>$v){
				$tmp_pay_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."deal_pay_log where deal_id = ".$v['id']);
				$tmp_apply_pay_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_apply_pay_log where deal_id = ".$v['id']);
				$deal_list[$k]['paid_money'] = intval($tmp_pay_money);
				$deal_list[$k]['apply_pay_num'] = intval($tmp_apply_pay_num);
				if($deal_list[$k]['pay_amount'] > $deal_list[$k]['paid_money'] && $deal_list[$k]['apply_pay_num']==0){
					$deal_list[$k]['can_apply'] = 1;
				}else{
					$deal_list[$k]['can_apply'] = 0;
				}
			}
		}
		$page = new Page($deal_count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$GLOBALS['tmpl']->display("account_dongjie.html");
	}
	
	//申请放款页面
	public function to_pay()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$deal_id = $_REQUEST['id'];
		$region_pid = 0;
		$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2 order by py asc");  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['name'] == $GLOBALS['user_info']['province'])
			{
				$region_lv2[$k]['selected'] = 1;
				$region_pid = $region_lv2[$k]['id'];
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		
		if($region_pid>0)
		{
			$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".$region_pid." order by py asc");  //三级地址
			foreach($region_lv3 as $k=>$v)
			{
				if($v['name'] == $GLOBALS['user_info']['city'])
				{
					$region_lv3[$k]['selected'] = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		}
		$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		$GLOBALS['tmpl']->assign("page_title","申请放款");
		$GLOBALS['tmpl']->display("account_to_pay.html");
	}
	
	//保存申请放款
	public function save_to_pay()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$to_pay_log_data['do_time'] = time($_REQUEST['do_time']);
		$to_pay_log_data['log_time'] = get_gmtime();
		$to_pay_log_data['deal_id'] = $_REQUEST['deal_id'];
		$to_pay_log_data['province']	= $_REQUEST['province'];
		$to_pay_log_data['city'] = $_REQUEST['city'];
		$to_pay_log_data['address']	= $_REQUEST['address'];
		$to_pay_log_data['images1'] = $_REQUEST['images_1_image'];
		$to_pay_log_data['images2'] = $_REQUEST['images_2_image'];
		$to_pay_log_data['images3'] = $_REQUEST['images_3_image'];
		$to_pay_log_data['images4'] = $_REQUEST['images_4_image'];
		$to_pay_log_data['images5'] = $_REQUEST['images_5_image'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_apply_pay_log",$to_pay_log_data,"INSERT","","SILENT");
		$log_id = $GLOBALS['db']->insert_id();
		app_redirect(url("account#dongjie"),2,"申请成功，系统管理员将在7个工作日之内为您放款。");
	}
	
	//我的放款记录
	public function paid()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		
		$deal_id = intval($_REQUEST['id']);
		$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id." and is_delete = 0 and is_effect = 1 and is_success = 1 and user_id = ".intval($GLOBALS['user_info']['id']));
		
		if(!$deal_info)
		{
			app_redirect_preview();
		}
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)$page = 1;		
		$limit = (($page-1)*$page_size).",".$page_size	;

		$paid_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_pay_log where deal_id = ".$deal_id." order by create_time desc limit ".$limit);
		$paid_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_pay_log where deal_id = ".$deal_id);
		
		
		$GLOBALS['tmpl']->assign("paid_list",$paid_list);

		$page = new Page($paid_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("account_paid.html");
	}
	//提现记录列表
	public function refund()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$GLOBALS['tmpl']->assign("page_title","提现记录列表");
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)$page = 1;		
		$limit = (($page-1)*$page_size).",".$page_size	;

		$refund_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_refund where user_id = ".intval($GLOBALS['user_info']['id'])." order by create_time desc limit ".$limit);
		$refund_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_refund where user_id = ".intval($GLOBALS['user_info']['id']));
	
		
		$GLOBALS['tmpl']->assign("refund_list",$refund_list);

		$page = new Page($refund_count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("account_money_carry_log.html");
	}
	
	//提现页面
	public function refund_list()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$GLOBALS['tmpl']->assign("page_title","提现");
		 
		
		$GLOBALS['tmpl']->display("account_refund_list.html");
	}
	
	public function submitrefund()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		$user_bank_id=intval($_REQUEST['user_bank_id']);
		$money = doubleval($_REQUEST['money']);
		$memo = strim($_REQUEST['memo']);
		
		if($money<=0)
		{
			showErr("提现金额出错",$ajax);
		}
		$paypassword=strim($_REQUEST['paypassword']);
		if($GLOBALS['user_info']['paypassword']!=md5($paypassword)){
			showErr("支付密码错误",$ajax);
		}
		
		$ready_refund_money =doubleval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_refund where user_id = ".intval($GLOBALS['user_info']['id'])." and is_pay = 0"));
		if($ready_refund_money + $money > $GLOBALS['user_info']['money'])
		{
			showErr("提现超出限制",$ajax);
		}
		$refund_data['user_bank_id'] = $user_bank_id;
		$refund_data['money'] = $money;
		$refund_data['user_id'] = $GLOBALS['user_info']['id'];
		$refund_data['create_time'] = NOW_TIME;
		$refund_data['memo'] = $memo;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_refund",$refund_data);
		
		showSuccess("提交成功",$ajax,url("account#refund"));
	}
	
	public function delrefund()
	{
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr("",$ajax,url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."user_refund where id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows()>0)
		{
			showSuccess("删除成功",$ajax);
		}
		else
		{
			showErr("删除失败",$ajax);
		}
		
	}
	//充值记录
	public function record(){
 		$g_links =get_link_by_id(14);
 		$GLOBALS['tmpl']->assign("g_links",$g_links);
		if(!$GLOBALS['user_info'])
			app_redirect(url("user#login"));
		$GLOBALS['tmpl']->assign("page_title","充值记录");
		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		//SELECT * FROM fanwe_payment_notice n WHERE n.order_id='0' AND n.deal_id='0' AND n.deal_item_id='0' AND deal_name='';
		
		$record_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where user_id = ".intval($GLOBALS['user_info']['id'])." and order_id=0 AND deal_id=0 AND deal_item_id=0 AND deal_name='' order by create_time desc limit ".$limit);
		$record_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment_notice where user_id = ".intval($GLOBALS['user_info']['id'])." and order_id=0 AND deal_id=0 AND deal_item_id=0 AND deal_name=''");
		foreach($record_list as $k=>$v){
			if(!$v['is_paid']){
				$record_list[$k]['url']=url("account#record_pay",array("notice_id"=>$v['id']));
			}
		}
		$page = new Page($record_count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		/*
		foreach($record_list as $k=>$v)
		{
			$record_list[$k]['deal_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$v['deal_id']." and is_effect = 1 and is_delete = 0");
		}
		*/
		$GLOBALS['tmpl']->assign('record_list',$record_list);
		$GLOBALS['tmpl']->display("account_record.html");
	}
	public function record_pay(){
 		$id=intval($_REQUEST['notice_id']);
 		$payment_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id=".$id);
 		$payment_list=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where online_pay=1 and is_effect=1 ");
  		$payment_html = "";
		foreach($payment_list as $k=>$v)
		{
			$class_name = $v['class_name']."_payment";
			require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
			$o = new $class_name;
			$payment_html .= "<div>".$o->get_display_code()."<div class='blank'></div></div>";
		}
  		$GLOBALS['tmpl']->assign("page_title","充值");
  		$GLOBALS['tmpl']->assign("payment_html",$payment_html);
  		$GLOBALS['tmpl']->assign('payment_info',$payment_info);
  		$GLOBALS['tmpl']->assign('payment_list',$payment_list);
  		$GLOBALS['tmpl']->display("account_record_pay.html");
  	}
  	public function record_go_pay(){
  		$return=array('status'=>1,'info'=>'','jump'=>'');
 		$id=intval($_REQUEST['notice_id']);
 		$payment_id=intval($_REQUEST['payment']);
 		
 		$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set payment_id=$payment_id where id=$id ");
 		$return['jump']=url("account#jump",array('id'=>$id));
 		
 		ajax_return($return);
  	}
  	
  	public function mortgate_pay()
  	{
  		if(!$GLOBALS['user_info'])
  			app_redirect(url("user#login"));
  		$GLOBALS['tmpl']->assign("page_title","充值诚意金");
  	 
  		$new_money=user_need_mortgate();
  		$has_money=$GLOBALS['db']->getOne("select mortgage_money from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
   		$money = $new_money-$has_money;
   		if($money<=0)
  		{
  			//app_redirect(url("account#mortgate_incharge"));
  			showSuccess("您的诚意金已支付，无需再支付！");
  		}
  		 
  		$GLOBALS['tmpl']->assign("money",$money);
  		$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and online_pay=1 order by sort asc ");
  		$payment_html = "";
  		foreach($payment_list as $k=>$v)
  		{
  			$class_name = $v['class_name']."_payment";
  			require_once APP_ROOT_PATH."system/payment/".$class_name.".php";
  			$o = new $class_name;
  			$payment_html .= "<div>".$o->get_display_code()."<div class='blank'></div></div>";
  		}
  		$GLOBALS['tmpl']->assign("payment_html",$payment_html);
  		$GLOBALS['tmpl']->display("mortgage_incharge.html");
  	}
  	public function get_leader_list(){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
  		$deal_id=$_REQUEST['id'];
  		$deal=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=$deal_id and user_id=".$GLOBALS['user_info']['id']);
   		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
  		$investor_list=$GLOBALS['db']->getAll("select invest.*,u.user_name from  ".DB_PREFIX."investment_list as invest left join ".DB_PREFIX."user as u on u.id=invest.user_id where invest.type=1 and invest.deal_id=$deal_id order by invest.id desc limit $limit ");
   		$investor_list_num=$GLOBALS['db']->getOne("select count(*) as num from  ".DB_PREFIX."investment_list where type=1 and deal_id=$deal_id order by id desc limit $limit ");
  		$now_time=NOW_TIME;
  		foreach($investor_list as $k=>$v){
  			if($v['status']==0&&$now_time>$deal['end_time']){
  				$investor_list[$k]['status']=2;
   					$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=2 where id= ".$v['id']);
  			}
  			$investor_list[$k]['cates']=unserialize($v['cates']);
  		}
  		$page = new Page($investor_list_num,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('deal',$deal);
		$GLOBALS['tmpl']->assign('deal_id',$deal_id);
		$GLOBALS['tmpl']->assign('pages',$p);
   		$GLOBALS['tmpl']->assign('investor_list',$investor_list);
  		$GLOBALS['tmpl']->display("account_leader_list.html");
  	}
  	public function add_leader_info(){
  		if(!$GLOBALS['user_info']){
  			app_redirect(url("user#login"));
  		}
  		$id=intval($_REQUEST['id']);
  		$leader_info=$GLOBALS['db']->getRow("select invest.*,d.name as deal_name from ".DB_PREFIX."investment_list as invest left join ".DB_PREFIX."deal as d on d.id=invest.deal_id where invest.id=".$id);
  		if($GLOBALS['user_info']['id']!=$leader_info['user_id']){
  			showErr("该页面不存在",0,"");
  			return false;
  		}
  		$file=unserialize($leader_info['leader_moban']);
  		$GLOBALS['tmpl']->assign('file',$file);
  		$GLOBALS['tmpl']->assign('leader_info',$leader_info);
  		$GLOBALS['tmpl']->assign('title','上传领投信息');
  		$GLOBALS['tmpl']->display("account_add_leader_info.html");
  	}
  	public function mine_investor_status(){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		if(app_conf("INVEST_STATUS")==1)
		{
			showErr("股权众筹已经关闭");
		}
  		$user_id=$GLOBALS['user_info']['id'];
  		$type=intval($_REQUEST['type']);
    	$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
   		$investor_list=$GLOBALS['db']->getAll("select invest.*,d.end_time,d.pay_end_time,d.begin_time,d.name as deal_name ,d.image as deal_image,d.id as deal_id" .
   				" from  ".DB_PREFIX."investment_list as invest left join ".DB_PREFIX."deal as d on d.id=invest.deal_id where  invest.type=$type and invest.user_id=$user_id order by invest.id desc limit $limit ");
    	$investor_list_num=$GLOBALS['db']->getOne("select count(*) from  ".DB_PREFIX."investment_list where  type=$type and user_id=$user_id  ");
  		$now_time=NOW_TIME;
 		if($type==0||$type==2||$type==1){
   			foreach($investor_list as $k=>$v){
   				if($type==1){ 
   					if($v['status']==0&&$now_time>$v['end_time']){
						$investor_list[$k]['status']=2;
						$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=2 where id= ".$v['id']);
   					}
   				}
				if($v['investor_money_status']==0&&$now_time>$v['end_time']){
					$investor_list[$k]['investor_money_status']=2;
					$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set investor_money_status=2 where id= ".$v['id']);
   				}elseif($v['investor_money_status']==1&&$now_time>$v['pay_end_time']){
   					$investor_list[$k]['investor_money_status']=4;
					deal_invest_break($v['id']);   				
   				}
   			}
		} 
   		
   		$title='';
   		switch($type){
  			case 1:
  			$title='领投列表';
  			break;
  			case 2:
  			$title='跟投列表';
  			break;
  			case 0:
  			$title='询价列表';
  			break;
  		}
  		$page = new Page($investor_list_num,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
   		$GLOBALS['tmpl']->assign('type',$type);
   		$GLOBALS['tmpl']->assign('title',$title);
   		$GLOBALS['tmpl']->assign('investor_list',$investor_list);
  		$GLOBALS['tmpl']->display("account_mine_investor.html");
  	}
  	public function get_investor_status(){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
    		//$user_id=$_REQUEST['user_info']['user_id'];
  		$deal_id=$_REQUEST['id'];
  		$type=intval($_REQUEST['type']);
  		$deal=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=$deal_id and user_id=".$GLOBALS['user_info']['id']);
  		$GLOBALS['tmpl']->assign('deal',$deal);
  	 	
  		$page_size = ACCOUNT_PAGE_SIZE;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		if($type==1){
			$condition=" and  invest.type=1 and invest.deal_id=$deal_id and invest.money>0 and invest.status=1 ";
		}else{
			$condition=" and  invest.type=$type and invest.deal_id=$deal_id ";
		}
		
  		$investor_list=$GLOBALS['db']->getAll("select invest.*,u.user_name from  ".DB_PREFIX."investment_list as invest left join ".DB_PREFIX."user as u on invest.user_id=u.id where 1=1 $condition order by id desc limit $limit ");
   		$investor_list_num=$GLOBALS['db']->getOne("select count(*) from  ".DB_PREFIX."investment_list where 1=1 $condition  ");
   		$now_time=NOW_TIME;
 		if($type==0||$type==2||$type==1){
   			foreach($investor_list as $k=>$v){
   				if($type==1){
   					if($v['status']==0&&$now_time>$deal['end_time']){
 						$investor_list[$k]['status']=2;
						$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=2 where id= ".$v['id']);
   					}
   				}
    				if($v['investor_money_status']==0&&$now_time>$deal['end_time']){
    				$investor_list[$k]['investor_money_status']=2;
   					$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set investor_money_status=2 where id= ".$v['id']);
   				}elseif($v['investor_money_status']==1&&$now_time>$deal['pay_end_time']){
   					$investor_list[$k]['investor_money_status']=4;
					deal_invest_break($v['id']);
   				}
   			}
		} 
   		
   		$title='';
   		switch($type){
  			case 1:
  			$title='领投列表';
  			break;
  			case 2:
  			$title='跟投列表';
  			break;
  			case 0:
  			$title='询价列表';
  			break;
  		}
  		$page = new Page($investor_list_num,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
   		$GLOBALS['tmpl']->assign('type',$type);
   		$GLOBALS['tmpl']->assign('deal_id',$deal_id);
   		$GLOBALS['tmpl']->assign('title',$title);
   		$GLOBALS['tmpl']->assign('investor_list',$investor_list);
  		$GLOBALS['tmpl']->display("account_investor_list.html");
  	}
  	public function lead_examine(){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
  		
  		$ajax=1;
  		$id=intval($_REQUEST['id']);
  		$type=intval($_REQUEST['type']);
  		$status=intval($_REQUEST['status']);
  		$item=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."investment_list where id=$id and type=1 ");
  		if(!$item){
  			showErr("该申请不存在",$ajax,"");
  		}
  		if($status==1){
  			$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=1 where id=$id ");
  			$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=2 where deal_id=".$item['deal_id']." and type=1 and id!=".$item['id']);
  		}else{
   			$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set status=2 where id=$id ");
  		}
  		showSuccess("审核成功",$ajax);
  	}
  	
  	//修改估值
  	public function investor_examine(){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		
  		$ajax=1;
  		//$result=array('status'=>1,'info'=>'');
  		$id=intval($_REQUEST['id']);
  		$type=intval($_REQUEST['type']);
  		$status=intval($_REQUEST['status']);
  		$item=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."investment_list where id=$id ");
  		if(!$item){
  			showErr("该询价不存在",$ajax,"");
  		}
  		$deal_id=intval($item['deal_id']);
  		$deal=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."deal where id=$deal_id and user_id=".$GLOBALS['user_info']['id']);
  		if(!$deal){
  			showErr("项目不存在",$ajax,"");
  		}
  		if($status==1){
  			$now_money=$GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."investment_list where deal_id=$deal_id and (investor_money_status=1 or investor_money_status=3  )");
  			if($type==0){
  				if($item['stock_value']<($now_money+$item['money'])){
	  				showErr("您允许的金额已超过估值的额度".$deal['stock_value'],$ajax);
	  			}
  			}else{
  				if($deal['limit_price']<($now_money+$item['money'])){
	  				showErr("您允许的金额已超过你的融资额度".$deal['limit_price'],$ajax);
	  			}
  			}
  			
  			$this->create_investor_pay($id);
  			$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set investor_money_status=1 where id=$id ");
   			if($type==0){
   				$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."deal set limit_price=".$item['stock_value']." where id=$deal_id ");
   			}
   			
   		}else{
   			$GLOBALS['db']->query("UPDATE  ".DB_PREFIX."investment_list set investor_money_status=2 where id=$id ");
   		}
  		showSuccess("审核成功",$ajax);
  		
  		
  	}
  	
  	public function create_investor_pay($invest_id){
  		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
  		
  		$ajax=1;
  		if(!$invest_id){
  			showErr("该申请错误",$ajax);
  		}
  		$invest=$GLOBALS['db']->getRow("select  invest.*,u.user_name from  ".DB_PREFIX."investment_list as invest left join ".DB_PREFIX."user as u on u.id=invest.user_id where invest.id=".$invest_id."  ");
  		if(!$invest){
  			showErr("没有该申请",$ajax);
  		}
  		$user_id=$GLOBALS['user_info']['id'];
  		$deal_info=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."deal where id=".$invest['deal_id']." and user_id=$user_id");
  		
  		if(!$deal_info){
  			showErr("没有该项目",$ajax);
  		}
  		$order_info['deal_id'] = $deal_info['id'];
 		$order_info['user_id'] = intval($invest['user_id']);
		$order_info['user_name'] = $invest['user_name'];
		$order_info['total_price'] = $invest['money'];
		$order_info['delivery_fee'] = 0;
		$order_info['deal_price'] = $invest['money'];
		$order_info['support_memo'] = "";
		//$order_info['payment_id'] = "";
		//$order_info['bank_id'] = strim($_REQUEST['bank_id']);
		
 		
		$order_info['credit_pay'] = 0;
		$order_info['online_pay'] = 0;
		$order_info['deal_name'] = $deal_info['name'];
		$order_info['order_status'] = 0;
		$order_info['type'] = 1;
		$order_info['invest_id'] = $invest_id;
		$order_info['create_time']	= NOW_TIME;
		$order_info['is_success'] = $deal_info['is_success'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order_info);
		
		$order_id = $GLOBALS['db']->insert_id();
		if(!$order_id){
			showErr("订单生成失败",$ajax);
		}else{
			//生成发送通知
			invest_pay_send($invest['id'],$order_id);
		}
		$GLOBALS['db']->query("UPDATE ".DB_PREFIX."investment_list SET order_id=$order_id where id=".$invest['id']);
  	}
  	/*我的推荐列表*/
  	public function recommend(){
  		if(!$GLOBALS['user_info'])
  		{
  			showErr("",0,url("user#login"));
  		}
  		$page_size = ACCOUNT_PAGE_SIZE;
  		$page = intval($_REQUEST['p']);
  		if($page==0)$page = 1;
  		$limit = (($page-1)*$page_size).",".$page_size;
  		$user_id=intval($GLOBALS['user_info']['id']);
  		$recommend_info=$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."recommend WHERE user_id=".$user_id." ORDER BY create_time DESC limit $limit");
  		$recommend_count=$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."recommend WHERE user_id=".$user_id);
  		$page = new Page($recommend_count,$page_size);   //初始化分页对象
  		$p  =  $page->show();
  		$GLOBALS['tmpl']->assign('pages',$p);
  		$GLOBALS['tmpl']->assign("recommend_info",$recommend_info);
  		$GLOBALS['tmpl']->display("account_recommend.html");
  	}
  	
  	public function money_index()
  	{
		if(!$GLOBALS['user_info'])
  		{
  			showErr("",0,url("user#login"));
  		}
		$user_id=intval($GLOBALS['user_info']['id']);
  		$recommend_info=$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."recommend WHERE user_id=".$user_id." ORDER BY create_time DESC limit $limit");
  		$recommend_count=$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."recommend WHERE user_id=".$user_id);
		//获取活动资金和已筹集资金总数
		$frozen_funds=$GLOBALS['db']->getOne("SELECT sum(pay_amount) FROM ".DB_PREFIX."deal WHERE user_id=".$user_id." and is_success=1");
		$all_support_amount=$GLOBALS['db']->getOne("SELECT sum(support_amount) FROM ".DB_PREFIX."deal WHERE user_id=".$user_id." and is_has_send_success=0 and is_success=0");
		$all_pay_money=$GLOBALS['db']->getOne("SELECT sum(b.money) FROM ".DB_PREFIX."deal as a , ".DB_PREFIX."deal_pay_log as b WHERE a.id = b.deal_id and a.user_id=".$user_id." and is_success=1");
		$frozen_funds = $frozen_funds - $all_pay_money;
  		$GLOBALS['tmpl']->assign("frozen_funds",$frozen_funds);
		$GLOBALS['tmpl']->assign("all_support_amount",$all_support_amount);
  		$GLOBALS['tmpl']->display("account_money_index.html");
  	}
	public function money_inchange()
	{		
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$GLOBALS['tmpl']->display("account_money_inchange.html");
	}
	public function money_inchange_log()
	{	
		
		$GLOBALS['tmpl']->display("account_money_inchange_log.html");
	}
	public function money_carry_bank(){
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$banks=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_bank where user_id=".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('banks',$banks);
		$GLOBALS['tmpl']->display("account_money_carry_bank.html");
	}
	
	public function money_carry_log()
	{
		if(!$GLOBALS['user_info'])
		{
			app_redirect(url("user#login"));
		}
		$GLOBALS['tmpl']->display("account_money_carry_log.html");
	}
	public function money_carry_addbank()
	{
 		$bank_list=get_bank_list();
 		//$bank_list=$bank_list['recommend'];
 		$GLOBALS['tmpl']->assign('bank_list',$bank_list);
		$GLOBALS['tmpl']->display("inc/account_money_carry_addbank.html");
	}
	public function addbank(){
		$GLOBALS['tmpl']->display("account_money_carry_addbank.html");
	}
	public function delbank(){
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));
		$ajax=1;
		$id=intval($_REQUEST['id']);
		$user_id=$GLOBALS['user_info']['id'];
		if($id>0){
			$re=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where user_id=$user_id and id=$id ");
			if($re){
				$result= $GLOBALS['db']->query("delete from  ".DB_PREFIX."user_bank where id=$id");
				 if($result){
				 	showSuccess("删除成功" ,$ajax);
				 }else{
				 	showErr("删除失败",$ajax);
				 }
			}else{
				showErr("您没有权限删除该银行卡",$ajax);
			}
		}else{
			showErr("不存在该银行卡",$ajax);
		}
	}
	public function savebank(){
		if(!$GLOBALS['user_info'])
		app_redirect(url("user#login"));	
 		$ajax=intval($_REQUEST['ajax']);
		if(empty($GLOBALS['user_info']['identify_name'])){
			showErr('请进行完成身份认证，才可以添加银行卡',$ajax);
		}
 		$bank_id = strim($_REQUEST['bank_id']);
 		$otherbank=intval($_REQUEST['otherbank']);
 		$data=array();
 		if(empty($bank_id)){
			showErr("请选择银行".$bank_id,$ajax);
		}else{
			if($bank_id=='other'){
				if($otherbank==0){
					showErr("请选择银行",$ajax);
				}else{
					$data['bank_id']=$otherbank;
				}
			}else{
				$data['bank_id']=$bank_id;
			}
		}
		$real_name=strim($_REQUEST['real_name']);
		if(empty($real_name)){
			showErr("请填写开户名",$ajax);
		}
		$province=strim($_REQUEST['province']);
		if(empty($province)){
			showErr("请选择省份",$ajax);
		}
		$data['region_lv2']=$province;
		$city=strim($_REQUEST['city']);
		if(empty($city)){
			showErr("请选择城市",$ajax);
		}
		$data['region_lv3']=$city;
		$bankzone=strim($_REQUEST['bankzone']);
		if($bankzone==''){
			showErr('请填写开户行网点',$ajax);
		}
		$data['bankzone']=$bankzone;
		$bankcard=strim($_REQUEST['bankcard']);
		if($bankcard==''){
			showErr('请填写银行卡号',$ajax);
		}
		$data['bankcard']=$bankcard;
		$reBankcard=strim($_REQUEST['reBankcard']);
		if($reBankcard!=$bankcard){
			showErr('银行卡号与确认卡号不一致',$ajax);
		}
		$data['reBankcard']=$reBankcard;
		$data['user_id']=$GLOBALS['user_info']['id'];
		
 		$data['real_name']=$real_name;
 		$data['bank_name']=$GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."bank where id=".$data['bank_id']);
 		$re=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$data,"INSERT","","SILENT");
 		if($re){
 			showSuccess("添加成功",$ajax,url("account#money_carry_bank"));
 		}else{
 			showErr("插入失败",$ajax);
 		}
		
	}
	public function money_carry()
	{
		$user_bank_id=intval($_REQUEST['id']);
		$bank_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where id=".$user_bank_id." and user_id=".$GLOBALS['user_info']['id']);
 		if(!$bank_info){
 			showErr("银行信息不存在",0,url("account#money_carry_bank"));
 		}
 		$ready_refund_money =doubleval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_refund where user_id = ".intval($GLOBALS['user_info']['id'])." and is_pay = 0"));
 		$bank_info['ready_refund_money']=$ready_refund_money;
 		$bank_info['bankcard']=substr($bank_info['bankcard'],0,-6)."***".substr($bank_info['bankcard'],-3);
 		$GLOBALS['tmpl']->assign('bank_info',$bank_info);
 		$GLOBALS['tmpl']->display("account_money_carry.html");
	}
	
}
?>