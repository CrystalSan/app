<?php

class appdataModule extends BaseModule
{
	function carousel(){
		$tmp_carousel_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."zhuanti where state=1 order by sort desc limit 0,3");
		if($tmp_carousel_info){
			foreach($tmp_carousel_info as $key=>$val){
				$tmp_pic = ltrim($val['pic'] , ".");
				$tmp_carousel_info[$key]['pic'] = "http://www.xinlechou.com".$tmp_pic;
				$tmp_carousel_info[$key]['url'] = "http://www.xinlechou.com".$val['url'];
				$tmp_carousel_info[$key]['wap_url'] = "http://www.xinlechou.com".$val['wap_url'];
			}
			$returnData = array();
			$returnData['error'] = 0;
			$returnData['data'] = $tmp_carousel_info;
			$returnStr = $this->APIJSON($returnData);
			echo $returnStr;
		}
		exit;
	}
	
	function deal_cate(){
		$tmp_deal_cate_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where 1 order by sort desc limit 0,7");
		if($tmp_deal_cate_info){
			foreach($tmp_deal_cate_info as $key=>$val){
				$tmp_pic = ltrim($val['pic'] , ".");
				$tmp_deal_cate_info[$key]['pic'] = "http://www.xinlechou.com".$tmp_pic;
			}
			$returnData = array();
			$returnData['error'] = 0;
			$returnData['data'] = $tmp_deal_cate_info;
			$returnStr = $this->APIJSON($returnData);
			echo $returnStr;
		}
		exit;
	}
	
	function zhuanti(){
		$page=$_POST['p'];
		if(!$page){
			$page = 1;
		}
		$startNum = ($page-1) * 3;
		$tmp_zhuanti_num = $GLOBALS['db']->getRow("select count(*) as num from ".DB_PREFIX."zhuanti where state=1");
		$tmp_zhuanti_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."zhuanti where state=1 ORDER BY sort DESC limit ".$startNum.",3");
		if($tmp_zhuanti_info){
			foreach($tmp_zhuanti_info as $key=>$val){
				$tmp_pic = ltrim($val['pic'] , ".");
				$tmp_zhuanti_info[$key]['pic'] = "http://www.xinlechou.com".$tmp_pic;
				$tmp_zhuanti_info[$key]['url'] = "http://www.xinlechou.com".$val['url'];
				$tmp_zhuanti_info[$key]['wap_url'] = "http://www.xinlechou.com".$val['wap_url'];
			}
			$retrunData = array();
			$returnData['error'] = 0;
			$retrunData['num'] = $tmp_zhuanti_num['num'];
			$retrunData['data'] = $tmp_zhuanti_info;
			$returnStr = $this->APIJSON($retrunData);
			echo $returnStr;
		}
		exit;
	}


	function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }
  
        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
	}
  
	/**************************************************************
	 *
	 *  将数组转换为JSON字符串（兼容中文）
	 *  @param  array   $array      要转换的数组
	 *  @return string      转换得到的json字符串
	 *  @access public
	 *
	 *************************************************************/
	function APIJSON($array) {
	    $this->arrayRecursive($array, 'urlencode', true);
	    $json = json_encode($array);
	    return urldecode($json);
	}

}
?>