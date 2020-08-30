<?php
header("Content-type: text/html; charset=utf-8");
include 'HttpClient.class.php';

define('USER', '158866366@qq.com');	//*用户填写*：飞鹅云后台注册账号
define('UKEY', 'KVcgmmzbYZmPJNjt');	//*用户填写*: 飞鹅云注册账号后生成的UKEY

//API URL
define('IP','api.feieyun.cn');		//接口IP或域名
define('PORT',80);					//接口IP端口
define('HOSTNAME','/Api/Open/');	//接口路径
define('STIME', time());			//公共参数，请求时间
define('SIG', sha1(USER.UKEY.STIME)); //公共参数，请求公钥


/*
 $conn=mysql_connect($mysql_server_name,$mysql_username,$mysql_password) or die("error connecting") ; //连接数据库
 mysql_query("SET NAMES utf8");
 mysql_query("set character_set_client=utf8"); 
 mysql_query("set character_set_results=utf8");
 date_default_timezone_set('PRC');		//设置正确的时区，以防错误
 mysql_select_db($mysql_database, $conn) or die(mysql_error());

 */


//==================方法1.打印订单==================
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":"316500004_20160823165104_1853029628","serverExecutedTime":6}
		//错误：{"msg":"错误信息.","ret":非零错误码,"data":null,"serverExecutedTime":5}
				
		
		//标签说明：
		//"<BR>"为换行符
		//"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
		//"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片)
		//"<PLUGIN>"为钱箱或者外置音响指令
		//"<CB></CB>"为居中放大
		//"<B></B>"为放大一倍
		//"<C></C>"为居中
		//"<L></L>"为字体变高一倍
	    //"<W></W>"为字体变宽一倍
	    //"<QR></QR>"为二维码
		//"<RIGHT></RIGHT>"为右对齐
	    //拼凑订单内容时可参考如下格式
		//根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式

		// $orderInfo = '<CB>测试打印</CB><BR>';
		// $orderInfo .= '名称　　　　　 单价  数量 金额<BR>';
		// $orderInfo .= '--------------------------------<BR>';
		// $orderInfo .= '饭　　　　　 　10.0   10  10.0<BR>';
		// $orderInfo .= '炒饭　　　　　 10.0   10  10.0<BR>';
		// $orderInfo .= '蛋炒饭　　　　 10.0   100 100.0<BR>';
		// $orderInfo .= '鸡蛋炒饭　　　 100.0  100 100.0<BR>';
		// $orderInfo .= '西红柿炒饭　　 1000.0 1   100.0<BR>';
		// $orderInfo .= '西红柿蛋炒饭　 100.0  100 100.0<BR>';
		// $orderInfo .= '西红柿鸡蛋炒饭 15.0   1   15.0<BR>';
		// $orderInfo .= '备注：加辣<BR>';
		// $orderInfo .= '--------------------------------<BR>';
		// $orderInfo .= '合计：xx.0元<BR>';
		// $orderInfo .= '送货地点：广州市南沙区xx路xx号<BR>';
		// $orderInfo .= '联系电话：13888888888888<BR>';
		// $orderInfo .= '订餐时间：2014-08-08 08:08:08<BR>';
		// $orderInfo .= '<QR>http://www.dzist.com</QR>';//把二维码字符串用标签套上即可自动生成二维码
		
		
		//打开注释可测试
		//wp_print("打印机编号",$orderInfo,1);
		
	//wp_print("918500654",$orderInfo,1);
		
//===========方法2.查询某订单是否打印成功=============
		//***接口返回值说明***
		//正确例子：
		//已打印：{"msg":"ok","ret":0,"data":true,"serverExecutedTime":6}
		//未打印：{"msg":"ok","ret":0,"data":false,"serverExecutedTime":6}
		
		//打开注释可测试
		//$orderindex = "xxxxxxxxxxxxxx";//订单索引，从方法1返回值中获取
		//queryOrderState($orderindex);
		

		
	
//===========方法3.查询指定打印机某天的订单详情============
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":{"print":6,"waiting":1},"serverExecutedTime":9}
		
		//打开注释可测试
		//$sn = "xxxxxxxxx";//打印机编号
		//$date = "2016-08-27";//注意时间格式为"yyyy-MM-dd",如2016-08-27
		//queryOrderInfoByDate($sn,$date);
		



//===========方法4.查询打印机的状态==========================
		//***接口返回值说明***
		//正确例子：
		//{"msg":"ok","ret":0,"data":"离线","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态正常","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态不正常","serverExecutedTime":9}
		
		//打开注释可测试
		//queryPrinterStatus("打印机编号");
		




/*
 *  方法1
	拼凑订单内容时可参考如下格式
	根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
*/



	 
	 //$aa='{"head":{"tablenum":"306房","print":"订单打印机","callup":"叫上","userno":"13538750770","username":"壹软网络","admin":"13538750770","qty":"1","amount":"0","discount":"100"},"body":[{"billno":"B0321044626323","orderno":"1","foodno":"44483","foodname":"房间菊花茶A12","price":12.34,"costprice":0.443,"qty":"1","unit":"例","image":"http:\/\/json.kassor.cn:8000\/xier\/thumbs\/Img201611221411151.jpg","discount":"100","hobby":"","userno":"13538750770","admin":"13538750770","print":"厨房打印机01","cooker":""}]}';
	 
	 
	 //$arr = json_decode($aa,true);
	 
	 //var_dump($arr);
	 
	 //echo $arr['head']['tablenum'];
	 
	// $admin= '13538750770';
	 
	 //$query = "SELECT  billno,username,nickname,tel,phone,address,memo,title FROM `foodmenu_user` where phoneno='$admin'";
	 //$result = mysql_query($query, $conn) or die(mysql_error($conn));
     //if (mysql_numrows($result) > 0){
	//	 $row = mysql_fetch_array($result);
	//	 print_orderbill($arr, $row);	 
	//}
 
 
 //print_cookbill($arr, '13538750770');











function wp_print($printer_sn,$orderInfo,$times){
	
		$content = array(			
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,
			'apiname'=>'Open_printMsg',

			'sn'=>$printer_sn,
			'content'=>$orderInfo,
		    'times'=>$times//打印次数
		);
		
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$content)){
		return 'error';
	}
	else{
		return $client->getContent();
	}
	
}





/*
 *  方法2
	根据订单索引,去查询订单是否打印成功,订单索引由方法1返回
*/
function queryOrderState($index){
		$msgInfo = array(
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,	 
			'apiname'=>'Open_queryOrderState',
			
			'orderid'=>$index
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		echo 'error';
	}
	else{
		$result = $client->getContent();
		echo $result;
	}
	
}




/*
 *  方法3
	查询指定打印机某天的订单详情
*/
function queryOrderInfoByDate($printer_sn,$date){
		$msgInfo = array(
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,			
			'apiname'=>'Open_queryOrderInfoByDate',
			
	        'sn'=>$printer_sn,
			'date'=>$date
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){ 
		echo 'error';
	}
	else{
		$result = $client->getContent();
		echo $result;
	}
	
}



/*
 *  方法4
	查询打印机的状态
*/
function queryPrinterStatus($printer_sn){
		
	    $msgInfo = array(
	    	'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,		
			'apiname'=>'Open_queryPrinterStatus',
			
	        'sn'=>$printer_sn
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		return 'error';
	}
	else{
		$result = $client->getContent();
		return $result;
	}
}


/*
 *  方法5
	添加打印机
*/
function feie_addcloudPrinter($newprinterinfo){
		
	    $msgInfo = array(
	    	'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,				
			'apiname'=>'Open_printerAddlist',
	        'printerContent'=>$newprinterinfo
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		return 'error';
	}
	else{
		$result = $client->getContent();
		return $result;
	}
}


/*
 *  方法6
	删除打印机
*/
function feie_delcloudPrinter($printerinfo){
		
	    $msgInfo = array(
	    	'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,			
			'apiname'=>'Open_printerDelList',
	        'snlist'=>$printerinfo
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		return 'error';
	}
	else{
		$result = $client->getContent();
		return $result;
	}
}


//填充字符串
function strpad($sourcestr, $count, $addside){
	
	$str = $sourcestr;
	$long = strlen($sourcestr);
	//echo $str.'  '.$long.'<br />';
	if($long<10){
		if ($addside=='LEFT'){
			for ($i=0; $i<$count-$long; $i++){
				$str = '　'.$str;
				$i++;
				}
		} else {
			for ($i=0; $i<$count-$long; $i++){
				$str.= '　';
				$i++;
				}
		}
	}
	return $str;	
}


?>
