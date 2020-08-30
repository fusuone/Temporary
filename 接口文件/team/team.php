<?php
header("Access-Control-Allow-Origin: *"); // 解决跨域问题
header("Content-type:text/html;charset=utf-8"); // header("Content-Type:text/html;charset=gbk");
date_default_timezone_set('PRC'); // 设置正确的时区，以防错误

require("DB_config.php");
require("config.php");
require("function.php");
require("thumb.php");

$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die("error connecting") ; // 连接数据库

mysql_query("SET NAMES utf8");
mysql_query("set character_set_client=utf8"); 
mysql_query("set character_set_results=utf8");
/* mysql_query("SET NAMES gbk");
mysql_query("set character_set_client=gbk"); 
mysql_query("set character_set_results=gbk"); */ 
 
mysql_select_db($mysql_database); // 打开数据库

$output = array();
$outdata = array();
$a = @$_GET['a'] ? $_GET['a'] : '';
$v = @$_GET['v'] ? $_GET['v'] : '100';			// 版本号
$macid = @$_GET['macid'] ? $_GET['macid'] : '';	// 机器码

$admin = @$_GET['adm'] ? $_GET['adm'] : '';	

$billno = substr(date("ymdHis"), 1, 11).mt_rand(100, 999);	// 动态生成一个全局单号

if (empty($a)) {
  $output = array('data'=>'', 'msg'=>'头参数不能为空', 'success'=>'0');
  exit(JSON($output));
}


// 分页
define('PAGE', empty($_GET['page']) ? 1 : $_GET['page']); // 第几页(默认第1页)
define('PAGE_SIZE', empty($_GET['pagesize']) ? 15 : $_GET['pagesize']); // 每页显示的条数(默认15条)
define('PAGING', (PAGE - 1) * PAGE_SIZE.', '.PAGE_SIZE);


 //走接口
 if ($a == 'login') {
       $uid= $_GET['uid'];
	   $upass =  $_GET['upass'] ;
	   $admin="";  // 最上级管理员(固定)(暂时没用到功能)
	   $division = "";   // 上级 (管理员的上级是自己,组长上级是管理员,员工上级是对应组长)
	   $isteam= "";   // -1 管理员，（-2 秘书，0 销售组，1 员工，2 财务，3 仓库，4 送货）
	   
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'uid参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	 if (($upass == '') ) {
        $output = array('data'=>'', 'msg'=>'upass参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
	/*
	// 查询新用户是否升级vip了
	$query = "SELECT billno FROM `team_salesman` WHERE team>0 and teamdate!='' and userno='$uid'";	
	//echo "1=".$query;
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {  //是否是管理员
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);	
		$admin = $uid;
		$division = $uid;
		$isteam="-1";   		
		}		
	  }else{   //是否是各部门组长
		 $query2 = "SELECT admin,isteam FROM `teams` WHERE userno='$uid' AND (division=admin) AND division!='' and astatus='1'";	
		 $result = mysql_query($query2, $conn) or die(mysql_error($conn));
		 if (mysql_numrows($result) > 0) { 
			$items = array();
			while ($row = mysql_fetch_assoc($result)) {
			array_push($items, $row);
			$admin = $row['admin'];
            $division = $row['admin'];			
			$isteam = $row['isteam'];   //0,2,3,4    
			}		
		  }else{  //是否是一些员工 
			    $query3 = "SELECT division,isteam,admin FROM `teams` WHERE userno='$uid' AND (division!=admin) and astatus='1'";	
				//echo "3=".$query3;
			    $result = mysql_query($query3, $conn) or die(mysql_error($conn));
			    if (mysql_numrows($result) > 0) { 
				$items = array();
				while ($row = mysql_fetch_assoc($result)) {
				array_push($items, $row);
                $admin = $row['admin'];				
				$division=$row['division'];
				$isteam="1";   				
			}			   
		  }		    
	    }		
    }
	*/
	
   //取得公共传输的公密钥
	$query4 = "select * from team_login where userno='$uid'";	 
	$result2= mysql_query($query4, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result2) > 0) {
		$row2 = mysql_fetch_array($result2);
		$pubkey = $row2['pubkey'];
		
		$sql = "update team_login set logintime=now() where userno='$uid'";
		if (!mysql_query($sql, $conn)) {
			$output = array('data' => '', 'msg' => '登录处理错误' . mysql_error($conn), 'success' => '0');
			exit(JSON($output));
		}	
	} else {
		$pubkey = getRandChar(64);
		$sql = "insert DELAYED team_login set userno='$uid',pubkey='$pubkey',logintime=now(),serialno='$macid',ver='$v'";
		if (!mysql_query($sql, $conn)) {
			$output = array('data' => '', 'msg' => '登录处理错误' . mysql_error($conn), 'success' => '0');
			exit(JSON($output));
		}			
	}		
	
	 $mupass = setstrmd5(md5($upass));  // md5加密
	
	//登录验证
	$query5 = "SELECT * FROM team_salesman  where (userno='$uid') and ((password='$mupass') OR (repassword='$mupass' AND (lastdate BETWEEN DATE_ADD( NOW(), INTERVAL -1 HOUR)  AND  NOW() )))";	
    $result = mysql_query($query5, $conn) or die(mysql_error($conn));
    if (mysql_numrows($result) > 0){
 
		$row = mysql_fetch_array($result);	
    	if ($row['team']>0){		//管理员身份，直接登录
			$admin = $uid;
			$division = $uid;
			$isteam="-1"; 
			$nature=row['nature']; 
			$outdata=array('username'=>$row['username'],'userno'=>$row['userno'],'address'=>$row['address'],'phone'=>$row['phone'],
					  'team'=>$row['team'],'teamname'=>$row['teamname'],'teamdate'=>$row['teamdate'],'image'=>$row['image'],
					  'image1'=>$row['image1'],'image2'=>$row['image2'],'memo'=>$row['memo'],'nature'=>$nature,'isteam'=>$isteam,
					  'admin'=>$admin,'division'=>$division,'pubkey'=>$pubkey);
		} else {
			 $query2 = "SELECT admin,isteam FROM `teams` WHERE userno='$uid' AND (division=admin) AND division!='' and astatus='1'";	
			 $result2 = mysql_query($query2, $conn) or die(mysql_error($conn));
			 if (mysql_numrows($result2) > 0) { 
				$row2 = mysql_fetch_assoc($result2)
				array_push($items, $row);
				$admin = $row2['admin'];
				$division = $row2['admin'];			
				$isteam = $row2['isteam'];   //0,2,3,4    
			  }else{  //是否是一些员工 
					$query3 = "SELECT division,isteam,admin FROM `teams` WHERE userno='$uid' AND (division!=admin) and astatus='1'";	
					$result3 = mysql_query($query3, $conn) or die(mysql_error($conn));
					if (mysql_numrows($result3) > 0){
						$row3 = mysql_fetch_assoc($result3);
						$admin = $row3['admin'];				
						$division=$row3['division'];
						$isteam="1";
					} else {
						 $output = array('data'=>'', 'msg'=>'无法确定用户身份','success'=>'0');
						 exit(JSON($output));	
					} 
			  }
			  if ($admin<>$uid){		//重新从管理员用户中取得企业属性
				  $query4 = "select nature from team_salesman  where userno='$admin'";
				  $result4 = mysql_query($query4, $conn) or die(mysql_error($conn));
					if (mysql_numrows($result4) > 0){
						$row4 = mysql_fetch_assoc($result4);
						$nature=$row4['nature'];
					} else {
						$nature = 0;
					}
			  }	
			  
			  $outdata=array('username'=>$row['username'],'userno'=>$row['userno'],'address'=>$row['address'],'phone'=>$row['phone'],
					  'team'=>$row['team'],'teamname'=>$row['teamname'],'teamdate'=>$row['teamdate'],'image'=>$row['image'],
					  'image1'=>$row['image1'],'image2'=>$row['image2'],'memo'=>$row['memo'],'nature'=>$nature,'isteam'=>$isteam,
					  'admin'=>$admin,'division'=>$division,'pubkey'=>$pubkey);
		}
		
		
		
    	$output = array('data'=>$outdata, 'msg'=>'登录成功','success'=>'1');
        exit(JSON($output));	
    }else{
    	 $output = array('data'=>'', 'msg'=>'用户名或密码错误','success'=>'0');
         exit(JSON($output));	
    }  
   
 }
 
 
 //注册帐号
 if ($a == 'registerUser') {
	   $userno = $_GET['userno'];	 
	   $smstext = "";     
		if ($userno == '') {
			$output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
			exit(JSON($output));
		}	 
	    $repass = substr($userno,-4);
		$mrepass = setstrmd5(md5($repass));  // md5加密
	   
	 	$query = "select * from `team_salesman` where userno='$userno'";
		$result = mysql_query($query, $conn) or die(mysql_error($conn));
		 if (mysql_numrows($result)>0){		 //存在userno		    
	   	    $sql="update `team_salesman` SET repassword='$mrepass',lastdate = now() where userno='$userno'";	
			$smstext = "好业绩：你的临时登录密码为：".$repass."，有效期30分钟，请登录后修改密码！";
			$output = array('data'=>'', 'msg'=>'注册成功','success'=>'2');
		} else{
			$sql="insert into team_salesman SET billno='$billno',`password`='$mrepass',userno='$userno', phone='$userno',regdate=NOW(), lastdate = now()";
			//echo $sql;
			$smstext = "好业绩：用户注册成功，手机号：".$userno."，登录密码：".$repass." 首次使用后，请及时更改登录密码！";
			$output = array('data'=>'', 'msg'=>'注册成功','success'=>'1');
		} 

    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '注册失败', 'success' => '0');
        exit(JSON($output));
    }
    
	sendphonesms($smstext,$userno);    
	//$output = array('data'=>'', 'msg'=>'注册成功','success'=>'1');			
	exit(JSON($output));
 
   }
   
 
   if ($a == 'edituser') {
  	$input = file_get_contents("php://input"); //接收POST数据
  	//$new_file ="./images/Img". date("YmdHms",time()).".jpg"; 
    $obj = json_decode($input,true);
    $username =gettostr($obj['username']);
    $address =gettostr($obj['address']);
    $phone =gettostr($obj['phone']);
    $memo = gettostr($obj['memo']);
    $image = gettostr($obj['image']);
    $sid = gettostr($obj['userno']);
	
      $sql="update team_salesman set username='$username',address='$address',image='$image',phone=" .
     		"'$phone',memo='$memo' where userno='$sid' ";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }
 
 
    //更改密码
 	if ($a == 'setpass') {
		   $uid = $_GET['uid'];
		   $pass = $_GET['pass'];
		   $newpass =  $_GET['newpass'];
		   
		   $mpass = setstrmd5(md5($pass));  // md5加密
		   $mnewpass = setstrmd5(md5($newpass));
	   
		if (($uid == '') ) {
			$output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
			exit(JSON($output));
		}
		
		$query = "select * from team_salesman where (userno='$uid') and ((password='$mpass') or (repassword = '$mpass')) ";
		$result = mysql_query($query, $conn) or die(mysql_error($conn));
		if (mysql_numrows($result) <= 0){		
	   	   $output = array('data'=>'', 'msg'=>'原密码错误','success'=>'-1');
			exit(JSON($output));
		}
		
		$sql = "update team_salesman set password='$mnewpass' where userno='$uid'";		
	    if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'修改失败','success'=>'0');
			exit(JSON($output));
		}
		$output = array('data'=>'', 'msg'=>'修改成功','success'=>'1');
		exit(JSON($output));	
		mysql_close($conn);
	 }
 
 

 if ($a == 'gettype') {
 	   $response=array();
 	   $uid = $_GET['uid'];	   
   
    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "select * from team_type where userno='$uid'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //合作伙伴
 if ($a == 'getpana') {
 	   $response=array();
 	   $saler = $_GET['saler'];	   
 	   $page = $_GET['page'];	
       $flat = "0";//$_GET['flat'];  //是否搜索状态下
	   $search = $_GET['search'];  //是否搜索状态下
	   $keyword = $_GET['keyword'];	 
       $mlatitude = $_GET['mlatitude'];
	   $mlongitude = $_GET['mlongitude'];
	   $mkey = $_GET['mkey'];  //排序方式,0 名称,1 客访, 2 距离近到远	   
   
    if (($saler == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($mkey=='-1'){   //按billdate 排序
		if($search=='0'){  //非搜索状态   
			 $query = "select *,null AS mkey  from `team_customer` where (admin='$admin' and (saler='$saler')) and ispana=1 and (`status`>-1) order by billdate  desc LIMIT $page,15";
		}else if($search=='1'){
		     $query = "select *,null AS mkey  from `team_customer` where (admin='$admin' and (saler='$saler' or `status`='1')) and ispana=1 and (`status`>-1) and  title like '%$keyword%' order by billdate  desc LIMIT $page,15";
	  }	
	}else if($mkey=='0'){           //按名称排序
	    if($search=='0'){  //非搜索状态  
		    $query = "select *,null AS mkey  from `team_customer` where (admin='$admin' and (saler='$saler')) and ispana=1 and (`status`>-1) order by title ASC LIMIT $page,15";
		}else if($search=='1'){
			$query = "select *,null AS mkey from `team_customer` where (admin='$admin' and (saler='$saler' or `status`='1')) and ispana=1 and (`status`>-1) and title like '%$keyword%' order by title ASC LIMIT $page,15";
		}	
	}else if($mkey=='1'){     //客访时间，最新开头 
	     if($search=='0'){   //非搜索状态下
			$query="SELECT a.*,null AS mkey FROM `team_customer` a LEFT JOIN `team_visit` b ON (a.title=b.customername)  
			     WHERE ((a.admin='$admin') and (a.saler='$saler')) and (a.ispana=1) and (a.`status`>-1) GROUP BY b.customername  ORDER BY b.billdate DESC LIMIT $page,15";		
			}else if($search=='1'){
	          $query="SELECT a.*,null AS mkey FROM `team_customer` a LEFT JOIN `team_visit` b ON (a.title=b.customername)  
			     WHERE ((a.admin='$admin') and (a.saler='$saler' OR a.`status`='1')) and (a.ispana=1) and (a.`status`>-1) and a.title like '%$keyword%' GROUP BY b.customername  ORDER BY b.billdate DESC LIMIT $page,15";		   
		  }    
	}else if($mkey=='2'){     //距离近到远
		    if($search=='0'){   //非搜索状态下
			   $query = "select *,ACOS(SIN($mlatitude * PI() / 180) * SIN(latitude * PI() / 180) + COS($mlatitude * PI() / 180) * COS(latitude * PI() / 180) * COS(
                        $mlongitude * PI() / 180 - longitude * PI() / 180)) * 6378.14 AS mkey from team_customer 
						where (admin='$admin' and (saler='$saler')) and (ispana=1) and (`status`>-1) ORDER BY mkey ASC LIMIT $page,15";
			}else if($search=='1'){
			    $query = "select *,ACOS(SIN($mlatitude * PI() / 180) * SIN(latitude * PI() / 180) + COS($mlatitude * PI() / 180) * COS(latitude * PI() / 180) * COS(
                        $mlongitude * PI() / 180 - longitude * PI() / 180)) * 6378.14 AS mkey  from team_customer 
						where (admin='$admin' and (saler='$saler' or `status`='1')) and (ispana=1) and (`status`>-1) and title like '%$keyword%' ORDER BY mkey ASC LIMIT $page,15";
	        }
	 }
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 
 //取得我发起的合同
 if ($a == 'getcontract') {
 	   $response=array();
 	   $userno = $_GET['userno'];	   
	   $page = $_GET['page'];	   
   
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT a.*,b.`username`,b.`image`,b.`image1` FROM `team_contract` AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno`  where a.admin='$admin' and a.userno='$userno'  order by astatus ASC, billdate desc LIMIT $page, 15";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
	 
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 } 
 
 //取得我待审的合同
 if ($a == 'getcheckcontract') {
 	   $response=array();
 	   $userno = $_GET['userno'];	   
	   $page = $_GET['page'];	   
   
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT a.*,b.`username`,b.`image`,b.`image1` FROM `team_contract` AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno`  where a.userno='$userno'  order by billdate desc LIMIT $page, 15";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 } 
 
 
 
    if ($a == 'addcontract') {		//新建合同订单
		$input = file_get_contents("php://input"); //接收POST数据
		 $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
       			
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	  
	 $customerno = gettostr($obj['customerno']);
	 $customername = gettostr($obj['customername']);
	 //表头内容 
	 $title = gettostr($obj['title']);
	 $userno = gettostr($obj['userno']);
	 $username = gettostr($obj['username']);
	 $contrano = gettostr($obj['contrano']);
	 $contradate = gettostr($obj['contradate']);
	
	 $amount = gettostr($obj['amount']);
	 $paid = gettostr($obj['paid']);
	 $introduction = gettostr($obj['introduction']);
	 $captainsign = gettostr($obj['captainsign']);
	 $bosssign = gettostr($obj['bosssign']);	
	 $checker = gettostr($obj['checker']);			
		
			
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
			}
		}			
	
		$sql="INSERT INTO `team_contract` SET title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',username='$username',billno='$billno',
		amount='$amount',paid='$paid',introduction='$introduction',image1='$image_url1',image2='$image_url2',image3='$image_url3',astatus='0',customername='$customername',
		customerno='$customerno',captainsign='$captainsign',bosssign='$bosssign',checker='$checker',modifydate=now(),admin='$admin'";

		if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'合同保存失败'.mysql_error($conn),'success'=>'0');
			exit(JSON($output));
		}	

		$output = array('data'=>'', 'msg'=>'合同保存成功','success'=>'1');
		exit(JSON($output));	
		mysql_close($conn); 
	}
 
 
if ($a == 'updatecontract') {		//更改合同
		$input = file_get_contents("php://input"); //接收POST数据
		 $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		 $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		 $new_file2 ="Img". date("YmdHms",time())."2.jpg";
		 $new_file3 ="Img". date("YmdHms",time())."3.jpg";
		$new_file4 ="Img". date("YmdHms",time())."4.jpg";
		$new_file5 ="Img". date("YmdHms",time())."5.jpg";
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$image_file4 ="./images/".$new_file4;
		$image_file5 ="./images/".$new_file5;
		
       	$image_url1 =  "";
        $image_url2 =  "";		
		$image_url3 =  "";		
		$image_url4 =  "";
		$image_url5 =  "";
	
      $obj = json_decode($input, true);
	  
	  $customerno = gettostr($obj['customerno']); 
	  $customername = gettostr($obj['customername']);
	  
	  $image1 = gettostr($obj['image1']);
	  $image2 = gettostr($obj['image2']);
	  $image3 = gettostr($obj['image3']);
		//表头内容  
		$title = gettostr($obj['title']);
		$userno = gettostr($obj['userno']);
		$contrano = gettostr($obj['contrano']);
		$contradate = gettostr($obj['contradate']);
		$amount = gettostr($obj['amount']);
		$paid = gettostr($obj['paid']);
		$introduction = gettostr($obj['introduction']);
		$captainsign = gettostr($obj['captainsign']);	
		$bosssign = gettostr($obj['bosssign']);		
		$billno = gettostr($obj['billno']);	
        $orderflag = gettostr($obj['orderflag']);	
        $reason = gettostr($obj['reason']);			
		$update="";		
		$updatetwo="";

        if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				$update=$update."image1='$image_url1'";
			}
		}	
        if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				if($update!=""){
					$update=$update.",image2='$image_url2'";
				}else{
					$update=$update."image2='$image_url2'";
				}
			}
		}	
		   if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				if($update!=""){
					$update=$update.",image3='$image_url3'";
				}else{
					$update=$update."image3='$image_url3'";
				}
			}
		}	
		
	   if ($captainsign != ''){
			if (file_put_contents($image_file4, base64_decode($captainsign))){
				$image_url4 =  $serverurl."/images/".$new_file4;
				$updatetwo=$updatetwo."captainsign='$image_url4'";
			}
		}	
	    if ($bosssign != ''){
			if (file_put_contents($image_file5, base64_decode($bosssign))){
				$image_url5 =  $serverurl."/images/".$new_file5;
				if($updatetwo!=""){
					$updatetwo=$updatetwo.",bosssign='$image_url5'";
				}else{
					$updatetwo=$updatetwo."bosssign='$image_url5'";
				}
			}
		}
		
		
 
	  $sql="";	
      if($orderflag=='0'){  		//保存数据	及  提交审核
	    if($update!=""){
			$sql="UPDATE `team_contract` SET modifydate=now(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',amount='$amount',paid='$paid',introduction='$introduction',customerno='$customerno',customername='$customername',$update where billno='$billno'";
		}else{
		    $sql="UPDATE `team_contract` SET modifydate=now(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',amount='$amount',paid='$paid',introduction='$introduction',customerno='$customerno',customername='$customername' where billno='$billno'";
	    } 
	  }	else if($orderflag=='1'){  //提交审核
	    if($update!=""){
			$sql="UPDATE `team_contract` SET modifydate=now(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',amount='$amount',paid='$paid',introduction='$introduction',customerno='$customerno',customername='$customername',$update,astatus='1' where billno='$billno'";
		}else{
		    $sql="UPDATE `team_contract` SET modifydate=now(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',amount='$amount',paid='$paid',introduction='$introduction',customerno='$customerno',customername='$customername',astatus='1' where billno='$billno'";
	    } 
	}else if($orderflag=='2' ){  //正常通过审核合同   队长
	    if($updatetwo!=""){
			$sql="UPDATE `team_contract` SET $updatetwo,astatus='$orderflag',reason='$reason',captainno='$userno', modifydate=now() where billno='$billno'";	
		}else{
			$sql="UPDATE `team_contract` SET astatus='$orderflag',reason='$reason',captainno='$userno',modifydate=now() where billno='$billno'";		
		}		
		}else if ($orderflag=='3'){   //正常通过审核合同   老板
			if($updatetwo!=""){
				$sql="UPDATE `team_contract` SET $updatetwo,astatus='$orderflag',reason='$reason',bossno='$userno', modifydate=now() where billno='$billno'";
			}else{
				$sql="UPDATE `team_contract` SET astatus='$orderflag',reason='$reason',bossno='$userno',modifydate=now() where billno='$billno'";
			}
		}else if ($orderflag=='4'){	//审核不通过
			$sql="UPDATE `team_contract` SET astatus='4',reason='".$userno." 审核不通过', modifydate=now()  where billno='$billno'";
		}
		
		if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'合同添加失败'.mysql_error($conn),'success'=>'0');
			exit(JSON($output));
		}	
        $a=array('image1'=>$image_url1,'image2'=>$image_url2,'image3'=>$image_url3);
		$output = array('data'=>$a, 'msg'=>'合同保存成功','success'=>'1');
		exit(JSON($output));	
		mysql_close($conn); 
}
 
if ($a == 'editcontract') {		//添加合同订单
	$input = file_get_contents("php://input"); //接收POST数据
	$obj=json_decode($input,1);
	//表头内容
	$userno = $obj['userno'];
	$billdate = $obj['billdate'];
	$customerno= $obj['customerno'];
	$customername =  $obj['customername'];
	$amount = $obj['amount'];
	$paid = $obj['paid'];
	$introduction = $obj['introduction'];
	$billno=$obj['billno'];

	$sql="update team_contract set modifydate=now(),billno='$billno',userno='$userno',billdate='$billdate',customerno='$customerno',customername='$customername',amount='$amount',paid='$paid',introduction='$introduction' where billno='$billno'";
	
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'合同添加失败'.mysql_error($conn),'success'=>'0');
		exit(JSON($output));
	}	

	$output = array('data'=>'', 'msg'=>'合同保存成功','success'=>'1');
	exit(JSON($output));	
	mysql_close($conn); 
} 
 
 
 
 
 if ($a == 'addtype') {    
     $typename =  $_GET['typename'] ;   
     $uid = $_GET['uid'];  
	 
	  
     //$typename = iconv("utf-8","gb2312//IGNORE",$name);
     // echo $typename;
    if (($typename == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    $query = "select * from team_type where (typename='$typename') and (userno='$uid')";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
    if (mysql_numrows($result) > 0){
    	  $output = array('data'=>'', 'msg'=>'类型'.$typename.'已经存在','success'=>'0');
        exit(JSON($output));	
   }else{
   //  $content = iconv("utf-8","gb2312//IGNORE",$typename);
   	 $sql="INSERT INTO team_type (typename,userno,billno) VALUES ('$typename','$uid','$billno')";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'类型添加失败','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'类型添加成功','success'=>'1');
        exit(JSON($output));	

        mysql_close($conn);
   }
  
 }
 if ($a == 'edittype') {
    
     $sid= $_GET['sid'];
     $typename = $_GET['typename'] ;  
     $userno = $_GET['userno'] ;  	 
     //$typename = iconv("utf-8","gb2312//IGNORE",$name);
     // echo $typename;
     if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'SID不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($typename == '') ) {
        $output = array('data'=>'', 'msg'=>'类别名称不能为空','success'=>'0');
        exit(JSON($output));
    }
	
	if($typename=="新客户" or $typename=="已收款" or $typename=="已完成")	{
       $output = array('data'=>'', 'msg'=>'系统分类名，不能修改','success'=>'0');
        exit(JSON($output));
	}
	
    $query = "select * from team_type where ((typename='$typename') and userno='$userno' and (id<>$sid))";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
    if (mysql_numrows($result) > 0){
    	  $output = array('data'=>'', 'msg'=>'类型'.$typename.'已经存在','success'=>'0');
        exit(JSON($output));	
   }else{
	//$content = iconv("utf-8","gb2312//IGNORE",$typename);
   	 $sql="update team_type set typename='$typename' where id=$sid";
     

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'类型修改失败','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'类型修改成功','success'=>'1');
        exit(JSON($output));	

        mysql_close($conn);
   }
  
 }
 if ($a == 'deltype') {
    
     $sid= $_GET['sid'];
	 $typename = "";
    
     if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'SID不能为空','success'=>'0');
        exit(JSON($output));
    }
	
    $query = "select * from team_type where  id=$sid";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
    if (mysql_numrows($result) > 0){
		$row = mysql_fetch_assoc($result);
    	$typename=$row['typename'];
   }
   
	
	if($typename=="新客户" or $typename=="已收款" or $typename=="已完成" or $typename=="未分类")	{
        $output = array('data'=>'', 'msg'=>'系统分类，不能删除！','success'=>'0');
        exit(JSON($output));
	}
	
    $sql="delete from  team_type  where id=$sid"; 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'类型删除失败','success'=>'0');
        exit(JSON($output));
    }
	//更新用户表的信息
    $sql="update team_customer set typename='未分类',typeno=0  where typeno=$sid"; 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'更新错误','success'=>'0');
        exit(JSON($output));
    }
	
	$output = array('data'=>'', 'msg'=>'类型删除成功','success'=>'1');
	exit(JSON($output));
	mysql_close($conn); 
 }
 
 
 
  //设置团队名称
 if ($a == 'setteamname') {
	 $uid = $_GET['uid'];
	 $tname = $_GET['tname'];
	 
    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($tname == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	
	
	$sql = "update team_salesman set teamname='$tname' where userno='$uid'";
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'改名处理错误！','success'=>'0');
        exit(JSON($output));
    }
	
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'改名处理成功','success'=>'1');
	exit(JSON($output));
 } 
 
 if ($a == 'getteamlist') {
	 $uid= $_GET['uid'];
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT a.*,b.username,b.image FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` where admin='$uid' OR division='$uid' order by a.isteam desc,a.id desc";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     }	
 }
 
 //添加队员
 if ($a == 'addteamuser') {
	 $admin = $_GET['admin'];
	 $isteam = $_GET['isteam'];
	 $username = $_GET['username'];
	 $userno = $_GET['phone'];
	 $msg = $_GET['msg'];
	 
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($admin == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	
	
	$query = "select * from team_salesman where userno='$userno'";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) <= 0){
		mysql_close($conn); 
     	$output = array('data'=>'', 'msg'=>'该用户还没有注册！','success'=>'0');
        exit(JSON($output));
	}
	
 	$query = "SELECT * FROM teams where userno='$userno'";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0){
     	$output = array('data'=>'', 'msg'=>'该队员已服务其它工作组！','success'=>'0');
        exit(JSON($output));
	}
	
	$sql = "insert into teams set billno='$billno',isteam=$isteam, admin='$admin',userno='$userno',viewname='$username'";
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'添加队员错误！'.mysql_error($conn),'success'=>'0');
        exit(JSON($output));
    }
	$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='用户认证',creuserno='$admin',admin='$admin', title='加入到团队',message='$msg 请求你加入到他的团队！',username='$username',userno='$userno',`type`=3";
	
	//添加到消息队列
    mysql_query($sql,$conn);
     
	//echo $sql;
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'队员添加成功','success'=>'1');
	exit(JSON($output));
 } 
 
 
 //编辑队员
 if ($a == 'editteamuser') {
	 $oldUserno = $_GET['oldUserno'];
	 $newUserno = $_GET['newUserno'];  //组长号码，队员号码
	 $madmin = $_GET['admin'];        //管理员号码，组长号码
	 $mflat = $_GET['mflat'];         //mflat=0 管理员编辑队长，1 管理员编辑队员，2 队长编辑队员
	 $teamname = $_GET['teamname'];  //小组名称
	 $adminname = $_GET['adminname']; //发送方(管理员名称，组长名称)
	 $bino = $_GET['billno'];
	 $iskey = $_GET['iskey']; 
	 $username = ""; //收信人名称
	 $msg="";
	 $cname="";
	 $mess="";
	 
    if (($oldUserno == '') ) {
        $output = array('data'=>'', 'msg'=>'oldUserno参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($newUserno == '') ) {
        $output = array('data'=>'', 'msg'=>'newUserno参数不能为空','success'=>'0');
        exit(JSON($output));
	}		
	
	$query = "select username from team_salesman where userno='$newUserno'";
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {  
		while ($row = mysql_fetch_assoc($result)) {	
		$username = $row['username'];	
		}		
	  }else{
		mysql_close($conn); 
     	$output = array('data'=>'', 'msg'=>'该用户还没有注册！','success'=>'0');
        exit(JSON($output)); 
	  }			
	
 	$query = "SELECT * FROM teams where userno='$newUserno' AND (astatus='1' OR astatus='0')";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0){
     	$output = array('data'=>'', 'msg'=>'该队员已服务其它工作组！','success'=>'0');
        exit(JSON($output));
	}
			
   if($mflat=='0'){  
        $mess="你被管理员:".$adminname."踢出了团队！";	
	    $cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您来当".$teamname."小组的组长";
	}else if($mflat=='1'){
		$mess="你被管理员:".$adminname."踢出了团队！";	
	    $cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您加入".$teamname."小组,成为我们的一员!";
	}else if($mflat=='2'){   
	    $mess="你被组长:".$adminname."踢出了团队！";	
	    $cname=$adminname."(组长)";
	    $msg=$adminname."诚邀您加入".$teamname."小组,成为我们的一员!";
	}
	
	$sql2="UPDATE `team_salesman` SET teamname='$teamname' WHERE userno='$newUserno'";
	mysql_query($sql2,$conn);
	
	$query = "UPDATE `teams` SET division='$newUserno' WHERE division='$oldUserno'";
	mysql_query($query,$conn);		
		
	$sql = "UPDATE `teams` SET userno='$newUserno',isteam='$iskey',astatus='0' WHERE userno='$oldUserno'";
		if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'失败！','success'=>'0');
			exit(JSON($output));
		}
	
	$sql = "INSERT INTO `team_message` SET billno='$bino', creuser='$cname',creuserno='$madmin',admin='$madmin', title='加入到团队',message='$msg',username='$username',userno='$newUserno',`type`=3";	
	//添加到消息队列
    mysql_query($sql,$conn);
	
	//通知公告，oldUserno被踢出团	
	$sql="INSERT INTO `team_message` SET billno='$billno',creuser='系统发送',creuserno='$madmin',
		  title='退出团队提示',message='$mess',username='$username',userno='$oldUserno',`type`='1'";
	mysql_query($sql,$conn);
		 
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'更改成功','success'=>'1');
	exit(JSON($output));
 }  
 
 //(管理队员)更换团队名称
 if ($a == 'changeTname') {
	 $username = $_GET['username'];  
	 $teamname = $_GET['teamname'];  
	 $bino = $_GET['bino'];  
	 $iskey = $_GET['iskey'];  
	 $uid = $_GET['uid'];  
	 
    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }

	$sql = "UPDATE `teams` SET isteam='$iskey' WHERE billno='$bino'";
	mysql_query($sql,$conn);
	
	$sql = "UPDATE `team_salesman` SET teamname='$teamname',username='$username' WHERE userno='$uid'";
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'更换失败！','success'=>'0');
		exit(JSON($output));
	}			
	
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'更换成功！','success'=>'1');
	exit(JSON($output));
}  
 
 
 //删除队员
 if ($a == 'delteamuser') {
	 $adminame = $_GET['adminame'];  //管理员名或组长名
	 $username = $_GET['username'];  //删除人名
	 $mflat = $_GET['mflat'];  //0,1管理员     2组长 
	 $uid = $_GET['uid'];
	 
    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	//直接删除行记录		
	//先添加信息通知
    if($mflat=='0'||$mflat=='1'){
		$mess="你被管理员:".$adminame."踢出了团队！";
	}else{
		$mess="你被组长:".$adminame."踢出了团队！";
	}		
	$sql="INSERT INTO `team_message` SET billno='$billno',creuser='系统发送',creuserno='$admin',
		  title='退出团队提示',message='$mess',username='$username',userno='$uid',`type`='1'";
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'系统处理出错！','success'=>'0');
		exit(JSON($output));
	}
	$sql = "delete from  teams where userno='$uid' or division='$uid'";
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'删除队员错误！','success'=>'0');
		exit(JSON($output));
	}			
	
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'处理成功！','success'=>'1');
	exit(JSON($output));
}  
 
 
 //取得小分组的信息(我的团队)
 if ($a == 'getmygroupinfo') {
	 $uid= $_GET['uid'];
	 $isadmin=$_GET['isadmin'];
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
	if($isadmin==0){		//队长 队员的情况	
		$query = "SELECT * from teams where userno='$uid'";
		$result = mysql_query($query, $conn) or die(mysql_error($conn));
		if (mysql_numrows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$viewname=$row['viewname'];
			$isteam=$row['isteam'];
			$astatus=$row['astatus'];
			$admin=$row['admin'];
					
			$query="select * from team_salesman where userno='$admin'";
			$result = mysql_query($query, $conn) or die(mysql_error($conn));
			if (mysql_numrows($result) > 0){
				$row = mysql_fetch_assoc($result);
				$usercount = 0;	  //队长，队员信息不显示
				$maxcount =  0;   //队长，队员信息不显示
				$expiredate = ""; //队长，队员信息不显示
				$teamname = $row['username'];
				$outdata=array('viewname'=>$viewname,'teamname'=>$teamname,'isteam'=>$isteam,'astatus'=>$astatus,'usercount'=>$usercount,'maxcount'=>$maxcount,'expiredate'=>$expiredate);
				$output = array('data'=>$outdata, 'msg'=>'获取成功','success'=>'1');
				exit(JSON($output));
			} else {
				$output = array('data'=>$outdata, 'msg'=>'获取失败','success'=>'0');
				exit(JSON($output));			
			}
		} else {
				$output = array('data'=>$outdata, 'msg'=>'获取失败','success'=>'0');
				exit(JSON($output));
		}
	} else {	//管理员的情况
		$query = "SELECT (SELECT COUNT(*) FROM `teams` WHERE admin='$uid' OR admin IN (SELECT userno FROM `teams` WHERE admin='$uid' AND isteam=1)) AS allcount,team,teamname,team AS maxcount,teamdate AS expiredate FROM team_salesman WHERE userno='$uid'";
		$result = mysql_query($query, $conn) or die(mysql_error($conn));
		//echo $query;
		if (mysql_numrows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$usercount = $row['allcount'];
			$maxcount =  $row['maxcount'];
			$expiredate = $row['expiredate'];
			$teamname = $row['teamname'];
			$outdata=array('viewname'=>'','teamname'=>$teamname,'isteam'=>'-1','astatus'=>'1','usercount'=>$usercount,'maxcount'=>$maxcount,'expiredate'=>$expiredate);
			$output = array('data'=>$outdata, 'msg'=>'获取成功','success'=>'1');
			exit(JSON($output));
		} else {
			$output = array('data'=>$outdata, 'msg'=>'获取失败','success'=>'0');
			exit(JSON($output));			
		}		
	}
 } 
 
 
 
  if ($a == 'addimage') {
		$input = file_get_contents("php://input"); //接收POST数据		  
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$tbumb_file= "./thumbs/".$new_file1;
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$tbumb_url =  $serverurl."/thumbs/".$new_file1;
		$image_url1 =  $serverurl."/images/".$new_file1;
		$image_url2 =  $serverurl."/images/".$new_file2;
		$image_url3 =  $serverurl."/images/".$new_file3;
		$obj = json_decode($input,true);	  
		$image1 = gettostr($obj['image1']);
		$image2 = gettostr($obj['image2']);
		$image3 = gettostr($obj['image3']);
		$sid = gettostr($obj['sid']);		
		$update = "";
		
	    if (($sid == '') ) {
			$output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
			exit(JSON($output));
		}		
		if ($image1 != ''){			
			//echo "image1 not null";
			$update = $update."image='$tbumb_url',image1='$image_url1'";
			if (file_put_contents($image_file1, base64_decode($image1))){
				mkThumbnail($image_file1, 124, 124, $tbumb_file);				
			}
		}
		if ($image2 != ''){
			if ($update==""){
				$update = $update."image2='$image_url2'";
			} else{
				$update = $update.",image2='$image_url2'";
			}
			file_put_contents($image_file2, base64_decode($image2));
		}
		if ($image3 != ''){			 
			if ($update==""){
				$update = $update."image3='$image_url3'";
			} else{
				$update = $update.",image3='$image_url3'";
			}
			file_put_contents($image_file3, base64_decode($image3));			
		}
		 
		if ($update <> ""){	//更新字串有内容，进行更新操作
			$sql = "update team_customer set ".$update." where id='$sid'";
			//echo $sql;
			if (!mysql_query($sql,$conn))
			{       
				$output = array('data'=>'', 'msg'=>'error','success'=>'0');
				exit(JSON($output));
			}		
		}
		//echo $sql;
		$output = array('data'=>'', 'msg'=>'save pic ok','success'=>'1');			
		exit(JSON($output));
  }
  
  if ($a == 'addhandimg') {
		$input = file_get_contents("php://input"); //接收POST数据

		$uid= $_GET['uid'];

		$new_file ="Img". date("YmdHms",time()).".jpg";
		$tbumb_file= "./headers/".$new_file;
		$image_file ="./headers/head".$new_file;

		$image =  $serverurl."/headers/".$new_file;		//取得图片的绝对路径
		$image2 = $serverurl."/headers/head".$new_file;		//取得缩略图片的绝对路径

		$obj = json_decode($input,true);
	  
		$imageBM = gettostr($obj['image']);
			if (file_put_contents($image_file, base64_decode($imageBM))){
				mkThumbnail($image_file, 124, 124, $tbumb_file);
				
			
			$sql = "update team_salesman set image='$image',image2='$image2' where userno='$uid'";	
			//echo $sql;
			if (!mysql_query($sql,$conn))
			{       
				$output = array('data'=>'', 'msg'=>'error update','success'=>'0');
				exit(JSON($output));
			}				
				
				
			$output = array('data'=>$new_file, 'msg'=>'save pic ok','success'=>'1');			
			exit(JSON($output));
	  }else{
		$output = array('data'=>'', 'msg'=>'save pic error'.$new_file,'success'=>'0');
		exit(JSON($output));
	  }
  } 
  //新增客户，编辑客户
  if ($a == 'addcustomer') {
  	$input = file_get_contents("php://input"); //接收POST数据
  	//$new_file ="./image/Img". date("YmdHms",time()).".jpg"; 
    $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$tbumb_file= "./thumbs/".$new_file1;
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$tbumb_url =  $serverurl."/thumbs/".$new_file1;
		$image_url1 =  $serverurl."/images/".$new_file1;
		$image_url2 =  $serverurl."/images/".$new_file2;
		$image_url3 =  $serverurl."/images/".$new_file3;
		$obj = json_decode($input,true);	  
		$image1 = gettostr($obj['image1']);
		$image2 = gettostr($obj['image2']);
		$image3 = gettostr($obj['image3']);
		//$sid = gettostr($obj['sid']);		
		$update = "";
    $typename =gettostr($obj['typename']);
    $typeno = gettostr($obj['typeno']);
    $title = gettostr($obj['title']);
	$tel = gettostr($obj['tel']);
	$phone = gettostr($obj['phone']);
	$linkman = gettostr($obj['linkman']);
	$address = gettostr($obj['address']);
	$scale = gettostr($obj['scale']);
	$capacity= gettostr($obj['capacity']);
	//$sect = gettostr($obj['sect']);	
	$latitude = gettostr($obj['latitude']);
	$longitude = gettostr($obj['longitude']);
	$mbillno = gettostr($obj['mbillno']);
	$flat = gettostr($obj['flat']);
	$status = gettostr($obj['status']);
	$isch = gettostr($obj['isch']);
	$dayNum="";
	$mbo="";
    $uid = $obj['uid'];
	
    if ($uid == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('username is not none'),'success'=>'0');
        exit(JSON($output));
    }
    if ($title == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('title is not none'),'success'=>'0');
        exit(JSON($output));
    }
    if ($typeno == '') {
        $output = array('data'=>'', 'msg'=>'typeno is not none','success'=>'0');
        exit(JSON($output));
    }
	if ($phone == ''){
        $output = array('data'=>'', 'msg'=>'phone is not none','success'=>'0');
        exit(JSON($output));	
	}
	  
	  if ($image1 != ''){			
			//echo "image1 not null";
			$update = $update."image='$tbumb_url',image1='$image_url1'";
			if (file_put_contents($image_file1, base64_decode($image1))){
				mkThumbnail($image_file1, 124, 124, $tbumb_file);				
			}
		}
		if ($image2 != ''){
			if ($update==""){
				$update = $update."image2='$image_url2'";
			} else{
				$update = $update.",image2='$image_url2'";
			}
			file_put_contents($image_file2, base64_decode($image2));
		}
		if ($image3 != ''){			 
			if ($update==""){
				$update = $update."image3='$image_url3'";
			} else{
				$update = $update.",image3='$image_url3'";
			}
			file_put_contents($image_file3, base64_decode($image3));			
		}
	if($flat=='0') {	
	if($isch=='1'){	
	//检查公司名是否存在于数据库中		
	$query = "select billno,refreshdate from team_customer where title  like '%$title%' and admin = '$admin' and ispana = '0'";
	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);	
		$refreshdate=$row['refreshdate']; //最近客访时间
        $nowtime=date("Y-m-d H:i:s");
		$mbo=$row['billno'];
        // 1.得到天数
        $dayNum = getCount_days($refreshdate,$nowtime);		
		}
		// 2.若客户的refreshdate与现在天数相差小于30天
		if ($dayNum <=30) {
		$output = '';
        $output["data"] = $items;
        $output["msg"] = "已存在";
        $output["success"] = 2;
		mysql_close($conn); 
		exit(JSON($output));
		}else{
		// 3.若客户的refreshdate与现在天数相差天于30天，直接转让客户权
		$sql3 = "update team_customer set saler='$uid' where billno='$mbo'";
        if (!mysql_query($sql3)) {
            $output = array('data' => '', 'msg' => '更改失败' . mysql_error($conn), 'success' => '0'); 
            exit(JSON($output)); 
           }
		    $output = array('data'=>'', 'msg'=>'更改成功','success'=>'1');
	        mysql_close($conn); 
	        exit(JSON($output));
	    }
	  }
     }	
	}else{
	if($isch=='1'){	
	//检查公司名是否存在于数据库中		
	$query = "select billno from team_customer where title  like '%$title%' and admin = '$admin' and ispana = '0'";
	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);	
		}
		$output = '';
        $output["data"] = $items;
        $output["msg"] = "已存在";
        $output["success"] = 2;
		mysql_close($conn); 
		exit(JSON($output));
	  }
     }	
	}
		
		
	if($flat=='0') {    //增加
    if($update!=""){
		$sql="INSERT INTO team_customer set billno='$billno',typeno='$typeno',typename='$typename',title='$title',tel='$tel',phone='$phone',linkman='$linkman',".
		 "address='$address',saler='$uid',scale='$scale',capacity='$capacity',latitude='$latitude',longitude='$longitude',$update,`status`='$status',admin='$admin'";
	}else{
		$sql="INSERT INTO team_customer set billno='$billno',typeno='$typeno',typename='$typename',title='$title',tel='$tel',phone='$phone',linkman='$linkman',".
		 "address='$address',saler='$uid',scale='$scale',capacity='$capacity',latitude='$latitude',longitude='$longitude',`status`='$status',admin='$admin'";
	 }
	}else if($flat=='1'){    //更改
		 if($update!=""){
		$sql="UPDATE team_customer set typeno='$typeno',typename='$typename',title='$title',tel='$tel',phone='$phone',linkman='$linkman',".
		 "address='$address',saler='$uid',scale='$scale',capacity='$capacity',latitude='$latitude',longitude='$longitude',$update,`status`='$status' where billno='$mbillno'";
	}else{
		$sql="update team_customer set typeno='$typeno',typename='$typename',title='$title',tel='$tel',phone='$phone',linkman='$linkman',".
		 "address='$address',saler='$uid',scale='$scale',capacity='$capacity',latitude='$latitude',longitude='$longitude',`status`='$status' where billno='$mbillno'";
	 }
	}
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'error','success'=>'0');
		mysql_close($conn); 
		exit(JSON($output));
	}

	$output = array('data'=>'', 'msg'=>'ok','success'=>'1');
	mysql_close($conn); 
	exit(JSON($output));
  }
  
  // 两日期相差的天数
function getCount_days($date1,$date2){
    $date1_stamp=strtotime($date1);
    $date2_stamp=strtotime($date2);
    return round(($date2_stamp-$date1_stamp)/3600/24);
}
  
  //新增客户 查询是否名称重复
   if ($a == 'selectCustTitle') {
 	   $title= $_GET['title'];
	   $ispana=$_GET['ispana']; 
       $dayNum="";	   
	
	$query = "select billno,refreshdate from team_customer where title  like '%$title%' and admin = '$admin' and ispana = '$ispana'";	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {  //存在记录时，又比较客访时间天数差是否小于30天
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);	
		$refreshdate=$row['refreshdate']; //最近客访时间
        $nowtime=date("Y-m-d H:i:s");
		$mbo=$row['billno'];
        // 1.得到天数
        $dayNum = getCount_days($refreshdate,$nowtime);		
		}
		// 2.若客户的refreshdate与现在天数相差小于30天
		if ($dayNum <=30) {
			$output = '';
			$output["data"] = $items;
			$output["msg"] = "重复数据,不可添加！";
			$output["success"] = 3;
			mysql_close($conn); 
			exit(JSON($output));
		}else{
		// 3.若客户的refreshdate与现在天数相差天于30天
            $output = '';
			$output["data"] = $items;
			$output["msg"] = "可添加！";
			$output["success"] = 2;
			mysql_close($conn); 
			exit(JSON($output));
	    }
	  }else{    //不存在记录
		    $output = '';
			$output["data"] = '';
			$output["msg"] = "可添加！";
			$output["success"] = 1;
			mysql_close($conn); 
			exit(JSON($output));
			
	  }		
 }
 // 转换客户信息，合作伙伴信息 
  if($a == 'updateCustTitle'){
		$bno = $_GET['billno'];
		$uid = $_GET['uid'];
		
	   if ($bno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
      }	 
	   $sql="UPDATE `team_customer` SET saler='$uid' WHERE billno='$bno'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'转换失败','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'转换成功','success'=>'1');
          exit(JSON($output));	            
   }	
 
  
  
 if ($a == 'editcustomer') {
  	$input = file_get_contents("php://input"); //接收POST数据
    $obj = json_decode($input,true);
    $typename =gettostr($obj['typename']);
    $typeno = gettostr($obj['typeno']);
    $title = gettostr($obj['title']);
	$tel = gettostr($obj['tel']);
	$phone = gettostr($obj['phone']);
	$linkman = gettostr($obj['linkman']);
	$address = gettostr($obj['address']);   
    $sid = gettostr($obj['sid']);
	$scale = gettostr($obj['scale']);
	$capacity= gettostr($obj['capacity']);
	//$sect = gettostr($obj['sect']);
	$uid = $obj['uid'];
	
    if ($title == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('title not is none'),'success'=>'0');
        exit(JSON($output));
    }
    if ($typeno == '') {
        $output = array('data'=>'', 'msg'=>'type not is none','success'=>'0');
        exit(JSON($output));
    }
	if ($phone == ''){
        $output = array('data'=>'', 'msg'=>'phone is not none','success'=>'0');
        exit(JSON($output));	
	}	
 
   
   
     $sql="update team_customer set typeno='$typeno',typename='$typename',scale='$scale',capacity='$capacity',title=" .
     		"'$title',tel='$tel',phone='$phone',linkman='$linkman',address='$address' ";
		$sql=$sql. " where id=$sid ";
			

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=> $sql,'success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
		mysql_close($conn); 
        exit(JSON($output));	            
  }
  
  
      //客户打理主页
  if ($a == 'getproducts') {
 	 // $response=array();
 	   $sid= $_GET['sid'];
	   $flat="0";
 	   $uid= $_GET['uid'];
	   $page = $_GET['page'];
	   $search = $_GET['search'];    //是否搜索状态下
	   $keyword = $_GET['keyword'];
	   $mlatitude = $_GET['mlatitude'];
	   $mlongitude = $_GET['mlongitude'];
	   $mkey = $_GET['mkey'];  //排序方式,0 名称,1 客访, 2 距离近到远
	  
 	   if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
     }
	 if($mkey=='-1'){   //按billdate 排序
		 if($search=='0'){   //非搜索状态下
			  $query = "select *,null AS mkey 
			            from team_customer where (admin='$admin' and saler='$uid') and (ispana=0) and (`status`>-1) and (typeno='$sid') ORDER BY billdate DESC LIMIT $page,15";
			}else if($search=='1'){         	
	          $query = "select *,null AS mkey 
			            from team_customer where (admin='$admin' and (saler='$uid' OR `status`='1')) and (ispana=0) and (`status`>-1) and (typeno='$sid') and title like '%$keyword%' ORDER BY billdate DESC LIMIT $page,15";
			}
	 
	 }else if($mkey=='0'){           //按名称排序
		    if($search=='0'){   //非搜索状态下
			  $query = "select *,null AS mkey 
			            from team_customer where (admin='$admin' and saler='$uid') and (ispana=0) and (`status`>-1) and (typeno='$sid') ORDER BY title ASC LIMIT $page,15";
			}else if($search=='1'){
			  $query = "select *,null AS mkey 
			            from team_customer where (admin='$admin' and (saler='$uid' OR `status`='1')) and (ispana=0) and (`status`>-1) and (typeno='$sid') and title like '%$keyword%' ORDER BY title ASC LIMIT $page,15";
	        }
	 }else if($mkey=='1'){     //客访时间，最新开头   ?
		    if($search=='0'){   //非搜索状态下	
			$query="SELECT a.*,null AS mkey FROM `team_customer` a LEFT JOIN `team_visit` b ON (a.title=b.customername)  
			     WHERE (a.admin='$admin' and a.saler='$uid') and (a.ispana=0) and (a.`status`>-1) and (a.typeno='$sid') GROUP BY b.customername  ORDER BY b.billdate DESC";
			}else if($search=='1'){			
	       $query="SELECT a.*,null AS mkey FROM `team_customer` a LEFT JOIN `team_visit` b ON (a.title=b.customername)  WHERE (a.admin='$admin' and (a.saler='$uid' OR a.`status`='1')) and (a.ispana=0) and (a.`status`>-1) and (a.typeno='$sid') and title like '%$keyword%' GROUP BY b.customername  ORDER BY b.billdate DESC LIMIT $page,15";		   
		  
		  }
	 }else if($mkey=='2'){     //距离近到远
		    if($search=='0'){   //非搜索状态下
			   $query = "select *,ACOS(SIN($mlatitude * PI() / 180) * SIN(latitude * PI() / 180) + COS($mlatitude * PI() / 180) * COS(latitude * PI() / 180) * COS(
                        $mlongitude * PI() / 180 - longitude * PI() / 180)) * 6378.14 AS mkey from team_customer 
						where (admin='$admin' and saler='$uid') and (ispana=0) and (`status`>-1) and (typeno='$sid') ORDER BY mkey ASC LIMIT $page,15";
			}else if($search=='1'){
			    $query = "select *,ACOS(SIN($mlatitude * PI() / 180) * SIN(latitude * PI() / 180) + COS($mlatitude * PI() / 180) * COS(latitude * PI() / 180) * COS(
                        $mlongitude * PI() / 180 - longitude * PI() / 180)) * 6378.14 AS mkey  from team_customer 
						where (admin='$admin' and (saler='$uid' OR `status`='1')) and (ispana=0) and (`status`>-1) and (typeno='$sid') and title like '%$keyword%' ORDER BY mkey ASC LIMIT $page,15";
	        }
	 }
	

      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        mysql_close($conn); 
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
		
		mysql_close($conn); 
        exit(JSON($output));
     } 
 }
 //删除客户,伙伴
 if ($a == 'delproducts') {
 	   $billno= $_GET['billno'];
 	   if (($billno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
    $sql="update team_customer set `status`='-1' where billno='$billno'"; 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'客户删除失败','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'客户删除成功','success'=>'1');
		mysql_close($conn); 
        exit(JSON($output));	
 }
 

  
 
 
 if ($a == 'getmessage') {
 	 // $response=array();
 	   $isread= $_GET['isread'];
 	    $uid= $_GET['uid'];
 	    if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	   if (($isread == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	  $query = "select * from foodmenu_message where (isread='$isread') and (userno='$uid')";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
//        $output = array(
//        'msg'=>'获取成功','success'=>'1');
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        mysql_close($conn); 
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
     } 
 }
 
 if ($a == 'setmessage') {
 	 // $response=array();
 	   $sid= $_GET['sid'];
 	   $isread= $_GET['isread'];
 	   if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($isread == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
      $sql="update foodmenu_message set isread=$isread  where id=$sid "; 

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'消息处理失败','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'消息处理成功','success'=>'1');
		mysql_close($conn); 
        exit(JSON($output));	
       
 }
 
   if ($a == 'delnotice') {
 	 // $response=array();
 	   $sid= $_GET['sid'];
 	   if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
      
	 $sql="delete from  foodmenu_message  where id=$sid"; 

    if (!mysql_query($sql,$conn))
    {       
		mysql_close($conn); 
        $output = array('data'=>'', 'msg'=>'消息删除失败','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'消息删除成功','success'=>'1');
		mysql_close($conn); 
        exit(JSON($output));	
 }
 
 //修改付款的值
 if ($a == 'setmoney') {
 	 // $response=array();
 	   $sid= $_GET['sid'];
 	   $stype= $_GET['sType'];
	   $sval= $_GET['mval'];
	   $uid= $_GET['uid'];
	   
 	   if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($stype == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($sval == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
	
	if ($stype=="paid"){
      $sql="update team_customer set paid=$sval   where id=$sid "; 
	  //添加修改日志
	  $logsql="insert into team_paylog set userid=$sid,paytype='paid',payval=$sval,billdate=now(),userno='$uid'";
	  mysql_query($logsql,$conn);
	}
	
 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'消息处理失败','success'=>'0');
        mysql_close($conn); 
		exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'消息处理成功','success'=>'1');
		mysql_close($conn);  
        exit(JSON($output));	
          
 }
 
 
   if ($a == 'addnotice') {
 	 // $response=array();
 	   $uid= $_GET['uid'];
 	   $content= $_GET['content'];
 	   if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($content == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
	//取得业务员数据
	 $query= "SELECT userno FROM team_salesman";
	 $items = array();
	 $result = mysql_query($query, $conn) or die(mysql_error($conn));
     while($row=mysql_fetch_assoc($result)){
          array_push($items, $row['userno']);
     }	 
	 foreach ($items as   $v){
		 $sql="insert into foodmenu_message set msgtype=1, memo='$content',creuser='$uid',isread=0,userno='$v' ";		 
		if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'消息处理失败','success'=>'0');
			exit(JSON($output));
		}
	 }
	$sql="insert into foodmenu_message set msgtype=1,memo='$content',creuser='$uid',isread=0,userno='$uid' ";
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'消息处理失败','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'消息处理成功','success'=>'1');
		mysql_close($conn); 
        exit(JSON($output));	 
 }
 
 
  if ($a == 'setpay') {
  	$input = file_get_contents("php://input"); //接收POST数据
  	//$new_file ="./images/Img". date("YmdHms",time()).".jpg"; 
    $obj = json_decode($input,true);
	$sid =gettostr($obj['sid']);
    $license =gettostr($obj['license']);
    $iyear = gettostr($obj['iyear']);
    $amount = gettostr($obj['amount']);	
	$userno = gettostr($obj['userno']);
	
	
 	if (($sid == '') ) {
        $output = array('data'=>'', 'msg'=>'sid参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
 	if (($license == '') ) {
        $output = array('data'=>'', 'msg'=>'license参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($iyear == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
    if (($amount == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
	
	
	  $logsql="insert into team_paylog set userid=$sid,paytype='paid',amount=$amount,billdate=now(),userno='$userno',license=$license,iyear=$iyear";
	  mysql_query($logsql,$conn);	
	
	//这里还需加入失效年份增加
	$sql="update team_customer set paid=$amount+paid  where id=$sid "; 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'消息处理失败','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'消息处理成功','success'=>'1');		
		mysql_close($conn);  
        exit(JSON($output));
	
  }
 
 //上传个人信息
if ($a == 'savedate') {
  	$input = file_get_contents("php://input"); //接收POST数据
  	//$new_file ="./images/Img". date("YmdHms",time()).".jpg"; 
	$new_file ="Img". date("YmdHms",time()).".jpg";
    $tbumb_file= "./headers/".$new_file;
	$image_file ="./headers/head".$new_file;

	$image =  $serverurl."/headers/".$new_file;		//取得图片的绝对路径
	$image2 = $serverurl."/headers/head".$new_file;		//取得缩略图片的绝对路径
	
    $obj = json_decode($input,true);
	$userno = gettostr($obj['userno']);
	$bm =gettostr($obj['bm']);
	
    $name =gettostr($obj['name']);
    $msex = gettostr($obj['msex']);
    $company = gettostr($obj['company']);	
	$job = gettostr($obj['job']);
	$jobnumber = gettostr($obj['jobnumber']);
	$phone = gettostr($obj['phone']);
	$mail = gettostr($obj['mail']);
	$caddress = gettostr($obj['caddress']);
	$age = gettostr($obj['age']);
	$nativeplace = gettostr($obj['nativeplace']);
	$Nation = gettostr($obj['Nation']);
	$IDcard = gettostr($obj['IDcard']);
	$homeaddress = gettostr($obj['homeaddress']);
	
 	if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'userno参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	    
	       if ($bm != ''){
			if (file_put_contents($image_file, base64_decode($bm))){
				mkThumbnail($image_file, 124, 124, $tbumb_file);
			}
		   
	       $sql = "update team_salesman set image='$image',image2='$image2',username='$name',sex='$msex',
   		            company='$company',job='$job',jobnumber='$jobnumber',phone='$phone',mail='$mail',companyaddress='$caddress',
					 age='$age',nativeplace='$nativeplace',nation='$Nation',idcard='$IDcard',address='$homeaddress' where userno='$userno'";	
		   }else{
			   $sql = "update team_salesman set username='$name',sex='$msex',
   		            company='$company',job='$job',jobnumber='$jobnumber',phone='$phone',mail='$mail',companyaddress='$caddress',
					 age='$age',nativeplace='$nativeplace',nation='$Nation',idcard='$IDcard',address='$homeaddress' where userno='$userno'";
		   }
			if (!mysql_query($sql,$conn))
			{       
				$output = array('data'=>'', 'msg'=>'error update','success'=>'0');
				mysql_close($conn); 
				exit(JSON($output));
			}							
			$output = array('data'=>'', 'msg'=>'save pic ok','success'=>'1');		
			mysql_close($conn); 			
			exit(JSON($output));
}
 
 //查询用户信息
 if ($a == 'getuserinfo') {
 	   $uid= $_GET['uid'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	  $query = "SELECT * FROM `team_salesman` WHERE userno='{$uid}'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        mysql_close($conn); 
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
     } 
 }
 
 if ($a == 'getmyuserinfo') {
 	   $uid= $_GET['uid'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	  $query = "SELECT username,image FROM `team_salesman` WHERE userno='{$uid}'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        mysql_close($conn); 
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
     } 
 }
 
 //工作签到 updateworks
 if ($a == 'updateworks') {
		   $response=array();
		   $uid = $_GET['uid'];
		   $flat = $_GET['flat'];  //区分,0上班还是 1下班
		   //$starttime = $_GET['starttime'];
		   //$endtime = $_GET['endtime'];
		   $username =  $_GET['username'];
		   // $week =  $_GET['week'];
		   $address =  $_GET['address'];
		   $lat =  $_GET['lat'];
		   $log =  $_GET['log'];
		   // $nowtime =  $_GET['nowtime'];
			
			$weekarray=array("日","一","二","三","四","五","六");
			$week = "星期".$weekarray[date("w")];
			$starttime = date('Y-m-d').' 00:00:00';
			$endtime = date('Y-m-d').' 23:59:59';

		   	   
		if (($uid == '')){
			$output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
			mysql_close($conn); 
			exit(JSON($output));
		}	
		
		  //先查询是否今天创建了数据
		    $query="SELECT * FROM `team_attendance` WHERE userno='$uid' and admin='$admin' AND billdate BETWEEN '$starttime' AND '$endtime'";
			//echo $query;
		    $rs=mysql_query($query);
			while ($row = mysql_fetch_assoc($rs)){
            $id = $row['id'];
          		if($flat=='0'){
					 $msql="UPDATE `team_attendance` SET startcheckwork='1',startworktime=NOW(),startworkaddress='$address',startworklatitude='$lat',startworklongitude='$log' WHERE userno='$uid' AND id='$id'";
					 
				  }
				  else{
					 $msql="UPDATE `team_attendance` SET endcheckwork='1',endworktime=NOW(),endworkaddress='$address',endworklatitude='$lat',endworklongitude='$log' WHERE userno='$uid' AND id='$id'";	
                    //  echo $sql;				 
				}
				 if(!mysql_query($msql,$conn))
			     {       
					$output = array('data'=>'', 'msg'=>'更新失败','success'=>'0');
					mysql_close($conn); 
					exit(JSON($output));
			    }
				$output = array('data'=>'', 'msg'=>'更新成功','success'=>'1');
				mysql_close($conn); 
				exit(JSON($output));	            
		       }
          
				if($flat==0){
					$sql = "INSERT INTO `team_attendance` SET billdate=NOW(),username='$username',userno='$uid',admin='$admin',startcheckwork='1',week='$week',startworktime=NOW(),startworkaddress='$address',startworklatitude='$lat',startworklongitude='$log'";		
				    // echo $sql;
				}else{
					$sql = "INSERT INTO `team_attendance` SET billdate=NOW(),username='$username',userno='$uid',admin='$admin',endcheckwork='1',week='$week',endworktime=NOW(),endworkaddress='$address',endworklatitude='$lat',endworklongitude='$log'";		
				}
               
				if (!mysql_query($sql,$conn))
			   {       
				$output = array('data'=>'', 'msg'=>'签到失败！','success'=>'0');
				mysql_close($conn); 
				exit(JSON($output));
			   }
				$output = array('data'=>'', 'msg'=>'签到成功！','success'=>'1');
				mysql_close($conn); 
				exit(JSON($output));
	 }
 //查询是否签到了
 if ($a == 'selectworks') {
 	   $uid= $_GET['uid'];
	   $starttime = date('Y-m-d').' 00:00:00';
	   $endtime = date('Y-m-d').' 23:59:59';
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	  $query = "SELECT startcheckwork,startworktime,endcheckwork,endworktime FROM `team_attendance` WHERE userno='$uid' AND billdate BETWEEN '$starttime' AND '$endtime'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        mysql_close($conn); 
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
     } 
 }
 
 //查询所有的是否签到信息
 if ($a == 'selectallworks') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	  // $starttime=$_GET['starttime'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }
 	 // $query = "SELECT * FROM `team_attendance` WHERE userno='$uid' AND DATE_FORMAT(billdate,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND billdate<'$starttime' ORDER BY billdate DESC LIMIT $page,15";
      $query = "SELECT * FROM `team_attendance` WHERE userno='$uid' ORDER BY billdate DESC LIMIT $page,15";
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //插入汇报内容
 if($a == 'insertreport'){
		   $uid= $_GET['uid'];
		   $status=$_GET['status'];
	       $username=$_GET['username'];
		   $title=$_GET['title'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    } 
	 
	 $sql="INSERT INTO `team_workreport` SET reportdate=NOW(),billno='$billno',admin='$admin',username='$username',userno='$uid',title='$title',status='$status'";

	if (!mysql_query($sql,$conn))
	{       
	$output = array('data'=>'', 'msg'=>'error','success'=>'0');
	exit(JSON($output));
	}
	 
	$output = array('data'=>'', 'msg'=>'ok','success'=>'1' );
	  exit(JSON($output));
 }
 
 //查询工作报告
 if ($a == 'selectreport') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	   $flat=$_GET['flat'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	//查询当月的数据
	if($flat=='0'){				//看板上的工作报告
		//$query="SELECT `team_workreport`.*,(SELECT COUNT(*) FROM `team_comment` WHERE `team_comment`.reportid=`team_workreport`.billno) num FROM `team_workreport` WHERE admin='$admin' and userno='$uid' OR (billdate BETWEEN DATE_ADD(NOW(), INTERVAL -7 DAY) AND  NOW()) userno  IN (SELECT userno FROM `teams` WHERE admin='$uid' OR admin IN (SELECT userno FROM `teams` WHERE admin='$uid' AND isteam=1)) ) ORDER BY reportdate DESC LIMIT $page,15";
	     $query="SELECT `team_workreport`.*,(SELECT COUNT(*) FROM `team_comment` WHERE `team_comment`.reportid=`team_workreport`.billno) num FROM `team_workreport` WHERE  userno='$uid'   ORDER BY reportdate DESC LIMIT $page,15";
	}else if($flat=='1'){		//我的工作报告
	     $query="SELECT `team_workreport`.*,(SELECT COUNT(*) FROM `team_comment` WHERE `team_comment`.reportid=`team_workreport`.billno) num FROM `team_workreport` WHERE admin='$admin' and userno='$uid'   ORDER BY reportdate DESC LIMIT $page,15";
	} else if ($flat == '2'){	//我审批的工作报告
	     $query="SELECT `team_workreport`.*,(SELECT COUNT(*) FROM `team_comment` WHERE `team_comment`.reportid=`team_workreport`.billno) num FROM `team_workreport` WHERE  admin='$admin' and billno IN (SELECT reportid FROM team_comment WHERE userno='$uid' and (billdate BETWEEN DATE_ADD(NOW(), INTERVAL -30 DAY) AND  NOW()))  ORDER BY reportdate DESC LIMIT $page,15";
	}
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //上传新建报销记录

if ($a == 'putimage') {
    $input = file_get_contents("php://input"); //接收POST数据
    $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		$new_file4 ="Img". date("YmdHms",time())."4.jpg";
		$new_file5 ="Img". date("YmdHms",time())."5.jpg";
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
        $image_file4 ="./images/".$new_file4;
        $image_file5 ="./images/".$new_file5;		
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		$image_url4 =  "";
		$image_url5 =  "";
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	
	 $reason = gettostr($obj['reason']);
	 $projectname = gettostr($obj['projectname']);
	 $userno = gettostr($obj['userno']);
	 $username = gettostr($obj['name']);
	 $money = gettostr($obj['money']);
	 $type = gettostr($obj['type']);
	 $time = gettostr($obj['time']);
	 $describe = gettostr($obj['describe']);	
	 $mbillno = gettostr($obj['billno']);
	 $captainsign = gettostr($obj['captainsign']);
	 $bosssign = gettostr($obj['bosssign']);
	 $key = gettostr($obj['key']);
	 $mstatus = gettostr($obj['mstatus']);
	 $imagestr="";
	 $bname="";
	 
	 if($captainsign!=''){
		if (file_put_contents($image_file4, base64_decode($captainsign))){
				$image_url4 =  $serverurl."/images/".$new_file4;
				$bname=$bname."captainsign='$image_url4'";
			} 
	 }
	 if($bosssign!=''){
		if (file_put_contents($image_file5, base64_decode($bosssign))){
				$image_url5 =  $serverurl."/images/".$new_file5;
				if($bname==""){
				   $bname=$bname."bosssign='$image_url5'";
				}else{
				   $bname=$bname.",bosssign='$image_url5'";
				}
				
			} 
	 }
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				$imagestr=$imagestr."image1='$image_url1'";
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				if($imagestr==""){
					$imagestr="image2='$image_url2'";
				}else{
					$imagestr=$imagestr.",image2='$image_url2'";
				}
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				if($imagestr==""){
					$imagestr="image3='$image_url3'";
				}else{
					$imagestr=$imagestr.",image3='$image_url3'";
				}
			}
		}	
       if($key=='-1'){   //提交暂存,提交去审核
		   if($imagestr!=""){
			$sql = "INSERT INTO `team_reimbursement` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',$imagestr,`describe`='$describe',`status`='$mstatus'"; 
		 }else{
			$sql = "INSERT INTO `team_reimbursement` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',`describe`='$describe',`status`='$mstatus'"; 
		 }
	    }else if($key=='0'){  //提交去审核
	     if($imagestr!=""){
			$sql = "update `team_reimbursement` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',$imagestr,`describe`='$describe',`status`='1' where billno='$mbillno'"; 
		   // echo "1".$sql;
		 }else{
			$sql = "update `team_reimbursement` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',`describe`='$describe',`status`='1' where billno='$mbillno'"; 
		  //echo "2".$sql;
		 }		   
	   }else if($key=='1'){  //审核是否通过,通过后撤销
		 $sql = "update `team_reimbursement` SET $bname,`status`='$mstatus',reason='$reason' where billno='$mbillno'";
		// echo "11".$sql;
	 }
	 //else if($key=='2'){   //通过后撤销
		//  $sql = "update `team_reimbursement` SET `status`='$mstatus' where billno='$mbillno'";
	// }
    // $sql = "INSERT INTO `team_reimbursement` SET billdate=NOW(),username='$username',userno='$userno',projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',image1='$image_url1',image2='$image_url2',image3='$image_url3',`describe`='$describe',`status`='0'";
	 
	 //echo $sql;
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
 
 //查询报销
 if ($a == 'selectreimbur') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	   $flat=$_GET['flat'];
	   
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){  //查询非无效的数据  大于-1
		$query = "SELECT * FROM `team_reimbursement` WHERE ((userno='$uid' AND `status`>-1) OR (admin='$uid' AND `status` >0) OR userno IN (SELECT userno FROM `teams` WHERE division ='$uid')) ORDER BY billdate DESC LIMIT $page,15";
	}else if($flat=='1'){  //查询已经提交的数据 大于0的
		$query = "SELECT * FROM `team_reimbursement` WHERE userno='$uid'  AND `status` > '0' ORDER BY billdate DESC LIMIT $page,15";
	}else if($flat=='2'){   //查询审核通过，不通过的数据 ，大于1
		$query = "SELECT * FROM `team_reimbursement` WHERE ((userno='$uid' AND `status` > '1') OR (admin='$uid' AND `status` >1)) ORDER BY billdate DESC LIMIT $page,15";
	}
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = array();
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        // var_dump($items); exit;
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //更改报销记录状态 
 if($a == 'updatereimbur') {
      $billno= $_GET['billno'];
	  $flat=$_GET['flat'];
	  
      if (($billno == '') ) {
          $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
      }
	  if($flat=='0'){  //更新提交
		  $msql="UPDATE `team_reimbursement` SET status='1' WHERE billno='$billno'";	
	  }else if($flat=='-1'){   //删除即无效
		  $msql="UPDATE `team_reimbursement` SET status='-1' WHERE billno='$billno'";
	  }else if($flat=='1'){
		  $msql="UPDATE `team_reimbursement` SET status='2' WHERE billno='$billno'";	
	  }                       
		 if(!mysql_query($msql,$conn))
			{       
			   $output = array('data'=>'', 'msg'=>'更新失败','success'=>'0');
			   exit(JSON($output));
			    }
				$output = array('data'=>'', 'msg'=>'更新成功','success'=>'1');
				  exit(JSON($output));	            
	
 }
 
 //改变消息状态
 if($a == 'updatemessg') {
      $id= $_GET['id'];
	  $flat=$_GET['flat'];
	  
      if (($id == '') ) {
          $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
      }
	  if($flat=='1'){  //1已读
		  $msql="UPDATE `team_message` SET isread='$flat' WHERE id='$id'";	
	  }else if($flat=='-1'){   //-1删除即无效
		  $msql="UPDATE `team_message` SET isread='$flat' WHERE id='$id'";
	  }                    
		 if(!mysql_query($msql,$conn))
			{       
			   $output = array('data'=>'', 'msg'=>'更新失败','success'=>'0');
			   exit(JSON($output));
			    }
				$output = array('data'=>'', 'msg'=>'更新成功','success'=>'1');
				  exit(JSON($output));	            
	
 }
 
 //查询对应的消息的数量
 if ($a == 'selectmesage') {
 	   $uid= $_GET['uid'];
    	   	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
     $query="SELECT A.c AS worknum,B.c AS idennum,C.c AS systemnum,D.c AS messnum,E.c AS helpnum FROM (SELECT COUNT(*) AS c FROM `team_task` WHERE `status`='0' AND responphone='$uid') A,
	      (SELECT COUNT(*) AS c FROM `team_message` WHERE `type`='3' AND isread='0' AND userno='$uid') B ,
		  (SELECT COUNT(*) AS c FROM `team_message` WHERE `type`='1' AND isread='0' AND ((userno='$uid') or (ispublic='1'))) C,
		   (SELECT COUNT(*) AS c FROM `team_message` WHERE `type`='0' AND isread='0' AND ((userno='$uid') or (ispublic='1'))) D,
		    (SELECT COUNT(*) AS c FROM `team_message` WHERE `type`='5' AND isread='0' AND ((userno='$uid') or (ispublic='1'))) E";
 	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
  //根据flat来查询未查看的消息
 if ($a == 'selectallmess') {
 	   $uid= $_GET['uid'];
	   $flat=$_GET['flat'];
    	   	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){        //系统消息
	       $query = "SELECT * FROM `team_message` WHERE userno='$uid' AND isread='0' AND type='$flat' ORDER BY billdate DESC";	
	}else if($flat=='1'){        //通知公告
		 $query = "SELECT * FROM `team_message` WHERE userno='$uid'  AND isread='0' AND type='$flat' ORDER BY billdate DESC";
	}else if($flat=='3'){   //认证
		 $query = "SELECT * FROM `team_message` WHERE userno='$uid'  AND isread='0' AND type='$flat' ORDER BY billdate DESC";
	}	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);		

		$billno=$row['billno'];
		  $sql2 = "UPDATE `team_message` SET isread='1' where billno='{$billno}'";
	      mysql_query($sql2);	
		
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 if ($a == 'selectMymess') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	   $flat=$_GET['flat'];
    	   	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){    
	       $query = "SELECT * FROM `team_message` WHERE userno='$uid' AND isread='0' AND type='$flat' ORDER BY billdate DESC LIMIT $page,15";	
	}else if($flat=='1'){       
		 $query = "SELECT * FROM `team_message` WHERE userno='$uid'  AND isread='0' AND type='$flat' ORDER BY billdate DESC LIMIT $page,15";
	}else if($flat=='3'){  
		 $query = "SELECT * FROM `team_message` WHERE userno='$uid'  AND isread='0' AND type='$flat' ORDER BY billdate DESC LIMIT $page,15";
	}   	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);		

		$billno=$row['billno'];
		  $sql2 = "UPDATE `team_message` SET isread='1' where billno='{$billno}'";
	      mysql_query($sql2);	
		
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 
 //查询下属员工手机号（任务成员）
 if ($a == 'selectwp') {
 	 $uid= $_GET['uid'];
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT b.userno,b.username AS viewname,b.image FROM teams AS a 
				LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` 
				WHERE a.admin='$uid' OR a.division='$uid' ";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 //插入消息
  if($a == 'insertmessage'){
	  $input = file_get_contents("php://input"); //接收POST数据
	    $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$image_file1 ="./images/".$new_file1;
		$image_url1 = "";
		
		$obj = json_decode($input,true);
		$image =gettostr($obj['image']);
   
		$creuser =gettostr($obj['creuser']);
		$creuserno = gettostr($obj['creuserno']);
		$title = gettostr($obj['title']);
		$message = gettostr($obj['message']);
		$username = gettostr($obj['username']);
		$userno = gettostr($obj['userno']);
		$type = gettostr($obj['type']);
	
 
     if ($image != ''){						 
			if (file_put_contents($image_file1, base64_decode($image))){
				$image_url1 =  $serverurl."/images/".$new_file1;	
        			
			}
		}
	 
		$sql="INSERT INTO`team_message` SET billno='$billno', billdate=NOW(),creuser='$creuser',creuserno='$creuserno',title='$title'
		      ,message='$message',image='$image_url1',username='$username',userno='$userno',admin='$admin',`type`='$type'";
	 		
	if (!mysql_query($sql,$conn))
	{       
	$output = array('data'=>'', 'msg'=>'error','success'=>'0');
	exit(JSON($output));
	}
	 
	$output = array('data'=>'', 'msg'=>'ok','success'=>'1' );
	  exit(JSON($output));
 }
 
 //查询通知数量
 if ($a == 'selectmesagetwo') {
 	   $uid= $_GET['uid'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT  COUNT(*) AS num  FROM `team_message` WHERE (userno='$uid' OR ispublic='1') and admin='$admin' AND isread='0'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
		//$num = $row['num'];
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 
 //上传新建任务

if ($a == 'puttask') {
    $input = file_get_contents("php://input"); //接收POST数据
    $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		$new_file4 ="Img". date("YmdHms",time())."4.jpg";
		$new_file5 ="Img". date("YmdHms",time())."5.jpg";
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$image_file4 ="./images/".$new_file4;
		$image_file5 ="./images/".$new_file5;		
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		$image_url4 =  "";
		$image_url5 =  "";
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	 
	 $title = gettostr($obj['title']);
	 $memo = gettostr($obj['memo']);
	 $userno = gettostr($obj['userno']); 
	 $username = gettostr($obj['username']); 
	 $responname = gettostr($obj['responname']);
	 $responphone = gettostr($obj['responphone']);
	 $lastdate = gettostr($obj['lastdate']);	 
	 $mstatus = gettostr($obj['mstatus']);
	 $bino = gettostr($obj['billno']);
	 $wkey = gettostr($obj['wkey']);  //0新任务插入，1 进行中审核任务，2 撤销，3 暂存任务的更改
	 $captainsign = gettostr($obj['captainsign']);
	 $bosssign = gettostr($obj['bossSign']);
	 $update="";
	 $updatetwo="";
	 
	 if ($captainsign != ''){
			if (file_put_contents($image_file4, base64_decode($captainsign))){
				$image_url4 =  $serverurl."/images/".$new_file4;
				$updatetwo=$updatetwo."captainsign='$image_url4'";
			}
		}
		if ($bosssign != ''){
			if (file_put_contents($image_file5, base64_decode($bosssign))){
				$image_url5 =  $serverurl."/images/".$new_file5;
					if($updatetwo!=""){
					$updatetwo=$updatetwo.",bosssign='$image_url5'";
					}else{
						$updatetwo=$updatetwo."bosssign='$image_url5'";
					}
			}
		}
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				$update=$update."image1='$image_url1'";
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				if($update!=""){
					$update=$update.",image2='$image_url2'";
				}else{
					$update=$update."image2='$image_url2'";
				}				
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				if($update!=""){
					$update=$update.",image3='$image_url3'";
				}else{
					$update=$update."image3='$image_url3'";
				}
			}
		}	
 if($wkey=='0'){		
   if($update!=""){
	    $sql = "INSERT INTO `team_task` SET billno='$billno',billdate=NOW(),memo='$memo',title='$title',responname='$responname',
		`status`='$mstatus',responphone='$responphone',admin='$admin',userno='$userno',username='$username',lastdate='$lastdate',$update";
   }else{
	    $sql = "INSERT INTO `team_task` SET billno='$billno',billdate=NOW(),memo='$memo',title='$title',responname='$responname',
	        `status`='$mstatus',responphone='$responphone',admin='$admin',userno='$userno',username='$username',lastdate='$lastdate'";
   }
	}else if($wkey=='1'){
		 if($updatetwo!=""){
			 $sql="UPDATE `team_task` SET `status`='$mstatus',$updatetwo WHERE billno='$bino'";
			  // echo $sql;
		 }else{
			 $sql="UPDATE `team_task` SET `status`='$mstatus' WHERE billno='$bino'";
		 }
	} else if($wkey=='2'){
		 $sql="UPDATE `team_task` SET `status`='$mstatus' WHERE billno='$bino'";
	}else if($wkey=='3'){  
		 if($update!=""){
	        $sql = "UPDATE `team_task` SET title='$title',memo='$memo',responname='$responname',
		      `status`='$mstatus',responphone='$responphone',username='$username',lastdate='$lastdate',$update WHERE billno='$bino'";
			}else{
			$sql = "UPDATE `team_task` SET title='$title',memo='$memo',responname='$responname',
	           `status`='$mstatus',responphone='$responphone',username='$username',lastdate='$lastdate' WHERE billno='$bino'";
   }
	}
	 
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
 
 //查询工作任务
 if ($a == 'selecttask') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
       $flat=$_GET['flat'];	  	   
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){ //看板上的列表	-1 删除，0暂存，1正在进行、4未完成，2 3完成	 	        
		  $query="SELECT *, CASE STATUS 
		          WHEN '0' THEN STATUS 
		          WHEN '1' THEN IF(NOW() > CONCAT(lastdate, ' 23:59:59'),4,STATUS) 
				  WHEN '2' THEN STATUS
				  WHEN '3' THEN STATUS
				  ELSE '' END AS as_status
				  FROM `team_task` WHERE 
					((userno='$uid' AND `status` IN (0,1,3) ) OR 
					(responphone='$uid' AND `status` IN (1,3))) ORDER BY 
					`status` ASC, billdate DESC LIMIT $page,15";
	}else if($flat=='1'){  	//正在进行的任务
		  $query = "SELECT *,status as as_status FROM `team_task` WHERE (userno='$uid' or responphone='$uid') AND `status`='1' AND NOW() <= CONCAT(lastdate, ' 23:59:59') ORDER BY `status` asc, billdate DESC LIMIT $page,15";
	
	}else if($flat=='2'){   //完成的任务
		 $query = "SELECT *,status as as_status FROM `team_task` WHERE  (userno='$uid' or responphone='$uid') AND (`status`='2' or `status`='3') ORDER BY   `status` asc,billdate DESC LIMIT $page,15"; 
	}else if($flat=='3'){   //未完成
		 $query ="SELECT *,4 as as_status  FROM `team_task` WHERE (userno='$uid' OR responphone='$uid') AND `status`='1' AND NOW() > CONCAT(lastdate, ' 23:59:59') ORDER BY `status` ASC, billdate DESC LIMIT $page,15;";
	}
 	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //上传客访记录
 if ($a == 'putvisit') {
    $input = file_get_contents("php://input"); //接收POST数据	
	
	    $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;		
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
    $obj = json_decode($input, true);
	
	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	
	 $customerno = gettostr($obj['customerno']);
	 $customername = gettostr($obj['customername']);
	 
	 $salename = gettostr($obj['salename']);
	 $saleno = gettostr($obj['saleno']);
	 $type = gettostr($obj['type']);
	 $title = gettostr($obj['title']);
	 $address = gettostr($obj['address']);
	 $latitude = gettostr($obj['latitude']);
	 $longitude = gettostr($obj['longitude']);	
	 
     if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
			}
		}		
	 
     $sql = "INSERT INTO `team_visit` SET billno='$billno', billdate=NOW(),customername='$customername',customerno='$customerno',salename='$salename',saleno='$saleno',type='$type',title='$title',address='$address',latitude='$latitude',longitude='$longitude',image1='$image_url1',image2='$image_url2',image3='$image_url3',admin='$admin'";
	 
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
   
   //更改店铺分类类型
 if ($a == 'changshoptype') {
	 $id = $_GET['id'];
	 $typename = $_GET['typename'];
	 $typeno = $_GET['typeno'];
	 
    if (($id == '') ) {
        $output = array('data'=>'', 'msg'=>'id参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
	$sql = "UPDATE `team_customer` SET typename='$typename',typeno='$typeno' WHERE id='$id'";
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'更改失败！','success'=>'0');
        exit(JSON($output));
    }
	
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'更改成功','success'=>'1');
	exit(JSON($output));
 } 
 //查询客访记录
 if ($a == 'selectvisitrecord') {
 	$uid= $_GET['uid'];
	$page= $_GET['page'];
	$customerno= $_GET['customerno'];
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT * FROM `team_visit` WHERE admin='$admin' and  saleno='$uid' AND customerno='$customerno' ORDER BY billdate DESC LIMIT $page,15";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
		//$num = $row['num'];
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //证书上传
 if ($a == 'putcertificate') {
    $input = file_get_contents("php://input"); //接收POST数据
    $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";		
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;				
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	 $userno = gettostr($obj['userno']);
	 $customerno = gettostr($obj['customerno']);	
	 $customername = gettostr($obj['customername']);
	 $count = gettostr($obj['count']);
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				 $sql = "INSERT INTO `team_certificate` SET billdate=NOW(),image='$image_url1',customername='$customername',customerno='$customerno',userno='$userno',admin='$admin',`describe`='$count'";
			    // echo $sql;
				 if (!mysql_query($sql, $conn)) {
                   $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
                   exit(JSON($output));
        }
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				$sql = "INSERT INTO `team_certificate` SET billdate=NOW(),image='$image_url2',customername='$customername',customerno='$customerno',userno='$userno',admin='$admin',`describe`='$count'";
			      if (!mysql_query($sql, $conn)) {
                   $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
                   exit(JSON($output));
				  }
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				$sql = "INSERT INTO `team_certificate` SET billdate=NOW(),image='$image_url3',customername='$customername',customerno='$customerno',userno='$userno',admin='$admin',`describe`='$count'";
			      if (!mysql_query($sql, $conn)) {
                   $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
                   exit(JSON($output));
				  }
			}
		}		     
	 
          $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
		
	//新增伙伴pana
	if ($a == 'addpana') {
  	$input = file_get_contents("php://input"); //接收POST数据
  	//$new_file ="./image/Img". date("YmdHms",time()).".jpg"; 
	    $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$tbumb_file= "./thumbs/".$new_file1;
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$tbumb_url =  $serverurl."/thumbs/".$new_file1;
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		
		 $obj = json_decode($input,true);
		$image1 =gettostr($obj['image1']);
		$image2 =gettostr($obj['image2']);
		$image3 =gettostr($obj['image3']);
   
    $title =gettostr($obj['title']);
    $linkman = gettostr($obj['linkman']);
    $tel = gettostr($obj['tel']);
	$phone = gettostr($obj['phone']);
	$address = gettostr($obj['address']);
	$scale = gettostr($obj['scale']);
	$major = gettostr($obj['major']);
	$uid = gettostr($obj['uid']);
	//$sid= gettostr($obj['sid']);
	$latitude = gettostr($obj['latitude']);
	$longitude = gettostr($obj['longitude']);
	$status = gettostr($obj['status']);
	$update="";
	$mbo="";
	$dayNum="";
	
    if ($uid == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('username is not none'),'success'=>'0');
        exit(JSON($output));
    }
    if ($title == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('title is not none'),'success'=>'0');
        exit(JSON($output));
    }
 
	if ($phone == ''){
        $output = array('data'=>'', 'msg'=>'phone is not none','success'=>'0');
        exit(JSON($output));	
	}
 
     if ($image1 != ''){						 
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
                 mkThumbnail($image_file1, 124, 124, $tbumb_file);				
                $update = $update."image='$tbumb_url',image1='$image_url1'";				
			}
		}
		  if ($image2 != ''){						 
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;	
				 if ($update==""){
				     $update = $update."image2='$image_url2'";
			          }else{
			    	 $update = $update.",image2='$image_url2'";
			   }
			}
		}
		
		 if ($image3 != ''){						 
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;	
				 if ($update==""){
				     $update = $update."image3='$image_url3'";
			          }else{
			    	 $update = $update.",image3='$image_url3'";
			   }
			}
		}
		
	//检查公司名是否存在于数据库中		
	$query = "select billno,refreshdate from team_customer where title  like '%$title%' and admin = '$admin' and ispana = '1'";
	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);	
		$refreshdate=$row['refreshdate']; //最近客访时间
        $nowtime=date("Y-m-d H:i:s");
		$mbo=$row['billno'];
        // 1.得到天数
        $dayNum = getCount_days($refreshdate,$nowtime);		
		}
		// 2.若客户的refreshdate与现在天数相差小于30天
		if ($dayNum <=30) {
		$output = '';
        $output["data"] = $items;
        $output["msg"] = "已存在";
        $output["success"] = 2;
		mysql_close($conn); 
		exit(JSON($output));
		}else{
		// 3.refreshdate时间超出一个月的，直接转让客户权
		$sql3 = "update team_customer set saler='$uid' where billno='$mbo'";
        if (!mysql_query($sql3)) {
            $output = array('data' => '', 'msg' => '更改失败' . mysql_error($conn), 'success' => '0'); 
            exit(JSON($output)); 
           }
		    $output = array('data'=>'', 'msg'=>'更改成功','success'=>'1');
	        mysql_close($conn); 
	        exit(JSON($output));
	    }
	}	

	
  if ($update==""){
	  $sql="INSERT INTO `team_customer` SET ispana=1, billno='$billno',title='$title',linkman='$linkman',tel='$tel',phone='$phone',address='$address',scale='$scale',major='$major',latitude='$latitude',longitude='$longitude',saler='$uid',status='$status',admin='$admin'";
			   
  }else{
	  $sql="INSERT INTO `team_customer` SET ispana=1, billno='$billno',title='$title',linkman='$linkman',tel='$tel',phone='$phone',address='$address',
               scale='$scale',major='$major',latitude='$latitude',longitude='$longitude',saler='$uid',$update,status='$status',admin='$admin'";			    
  }
	if (!mysql_query($sql,$conn))
	{       
	$output = array('data'=>'', 'msg'=>'error','success'=>'0');
	exit(JSON($output));
	}
	 
	$output = array('data'=>'', 'msg'=>'ok','success'=>'1');
	  exit(JSON($output));
  }
  
  //编辑伙伴 pana（不能通过编辑来夺取一个月没客访的客户,只能通过新增）
  if ($a == 'editpana') {
  	$input = file_get_contents("php://input"); //接收POST数据
	    $new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$tbumb_file= "./thumbs/".$new_file1;
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		$tbumb_url =  $serverurl."/thumbs/".$new_file1;
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		
		$obj = json_decode($input,true);
		$image1 =gettostr($obj['image1']);
		$image2 =gettostr($obj['image2']);
		$image3 =gettostr($obj['image3']);
   
		$title =gettostr($obj['title']);
		$linkman = gettostr($obj['linkman']);
		$tel = gettostr($obj['tel']);
		$phone = gettostr($obj['phone']);
		$address = gettostr($obj['address']);
		$scale = gettostr($obj['scale']);
		$major = gettostr($obj['major']);
		$uid = gettostr($obj['uid']);
		$latitude = gettostr($obj['latitude']);
		$longitude = gettostr($obj['longitude']);
		$billno = gettostr($obj['billno']);
		$status = gettostr($obj['status']);
		$isch = gettostr($obj['isch']);
		$update="";
	
	 if ($billno == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('billno is not none'),'success'=>'0');
        exit(JSON($output));
    }
	
    if ($uid == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('username is not none'),'success'=>'0');
        exit(JSON($output));
    }
    if ($title == '')  {
        $output = array('data'=>'', 'msg'=>gettostr('title is not none'),'success'=>'0');
        exit(JSON($output));
    }
 
	if ($phone == ''){
        $output = array('data'=>'', 'msg'=>'phone is not none','success'=>'0');
        exit(JSON($output));	
	}
 
     if ($image1 != ''){						 
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;	
				 mkThumbnail($image_file1, 124, 124, $tbumb_file);				
                $update = $update."image='$tbumb_url',image1='$image_url1'";
               			
			}
		}
		  if ($image2 != ''){						 
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;	
				 if ($update==""){
				     $update = $update."image2='$image_url2'";
			          }else{
			    	 $update = $update.",image2='$image_url2'";
			   }
			}
		}
		
		 if ($image3 != ''){						 
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;	
				 if ($update==""){
				     $update = $update."image3='$image_url3'";
			          }else{
			    	 $update = $update.",image3='$image_url3'";
			   }
			}
		}
   if($isch=='1'){	
	//检查公司名是否存在于数据库中		
	$query = "select billno from team_customer where title  like '%$title%' and admin = '$admin' and ispana = '1'";
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {
		$items = array();
		while ($row = mysql_fetch_assoc($result)) {
		array_push($items, $row);			
		}	
		$output = '';
        $output["data"] = $items;
        $output["msg"] = "已存在";
        $output["success"] = 2;
		mysql_close($conn); 
		exit(JSON($output));		
	}
}	
 
  if ($update==""){
	  $sql="UPDATE `team_customer` SET title='$title',linkman='$linkman',tel='$tel',phone='$phone',address='$address',
               scale='$scale',major='$major',latitude='$latitude',longitude='$longitude',saler='$uid',status='$status' where billno='$billno'";
  }else{
	  $sql="UPDATE `team_customer` SET title='$title',linkman='$linkman',tel='$tel',phone='$phone',address='$address',
               scale='$scale',major='$major',latitude='$latitude',longitude='$longitude',saler='$uid',$update,status='$status'  where billno='$billno'";
  }
	
	if (!mysql_query($sql,$conn))
	{       
	$output = array('data'=>'', 'msg'=>'error','success'=>'0');
	exit(JSON($output));
	}
	 
	$output = array('data'=>'', 'msg'=>'ok','success'=>'1');
	  exit(JSON($output));
  }	
  
		
	//查询合同
 if ($a == 'selectcont') {
 	   $uid  = $_GET['uid'];
	   $page = $_GET['page'];
       $flat = $_GET['flat'];	  	
       $customerno=$_GET['customerno'];	 	   
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){ 
		  //$query = "SELECT * FROM `team_contract` WHERE (userno='$uid' AND astatus IN (0,1)) OR (userno='$uid' AND astatus IN (2,4) AND billdate BETWEEN DATE_ADD(NOW(), INTERVAL -15 DAY) AND  NOW()) OR ((userno IN (SELECT admin FROM `teams` WHERE userno='$uid') OR userno IN (SELECT admin FROM `teams` WHERE userno IN (SELECT admin FROM `teams` WHERE userno = '$uid') )) AND astatus IN (1,2) ) ORDER BY modifydate DESC LIMIT $page,15";
		  $query="SELECT * FROM `team_contract` WHERE (userno='$uid' AND astatus IN (0,1,2,3,4)) OR (admin='$uid' AND astatus IN (1,2,3,4)) OR userno IN (SELECT userno FROM `teams` WHERE division ='$uid') ORDER BY modifydate DESC LIMIT $page,15";
	}else if($flat=='1'){  	//我提交的合同
		  $query = "SELECT * FROM `team_contract` WHERE (userno='$uid' AND astatus IN (1,3,4)) ORDER BY modifydate DESC LIMIT $page,15";
		  //echo $query;
	}else if($flat=='2'){   //我审核的合同
		 $query = "SELECT * FROM `team_contract` WHERE  ( userno IN (SELECT admin FROM `teams` WHERE userno='$uid')  OR userno IN (SELECT admin FROM `teams` WHERE userno IN (SELECT admin FROM `teams` WHERE userno = '$uid') )) AND astatus IN (2,3,4) ORDER BY modifydate DESC LIMIT $page,15";
		 //echo $query;
	}else if($flat=='3'){
		$query = "SELECT * FROM `team_contract` WHERE userno='$uid' and customerno='$customerno' ORDER BY billdate DESC LIMIT $page,15"; 
	}
 	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
	//撤销合同/提交审核/删除合同
	if($a == 'revokecontract'){
	    $billno = $_GET['billno'];
		$flat = $_GET['flat'];  //0提交合同，1 删除
	//	$reason = $_GET['reason'];
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
     //$sql="UPDATE `team_contract` SET astatus='$flat',reason='$reason' WHERE billno='$billno'";
	  $sql="UPDATE `team_contract` SET astatus='$flat' WHERE billno='$billno'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }
   
   
	//收款开通
	if ($a == 'getpaiddata') {
 	   $customerno= $_GET['customerno'];
     if (($customerno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
 	  $query = "SELECT SUM(amount) AS amount,(SUM(amount-paid)-IFNULL((SELECT SUM(receivedamount) FROM team_receivables WHERE admin='$admin' AND customerno='$customerno'),0)) AS nopaid FROM `team_contract` 
                WHERE admin='$admin' AND (astatus='2' OR astatus='3') AND customerno='$customerno'";
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
	
	//收款操作
	 if ($a == 'recemoney') {		//添加合同订单
		$input = file_get_contents("php://input"); //接收POST数据
		 $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
       			
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";
		
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
		//表头内容 
		$customername = gettostr($obj['customername']);
		$customerno = gettostr($obj['customerno']);
		$receivedamount = gettostr($obj['receivedamount']);
		$saleman = gettostr($obj['saleman']);
		$salepnone = gettostr($obj['salepnone']);
		$describe = gettostr($obj['describe']);
			
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
			}
		}			
	
		$sql="INSERT INTO `team_receivables` SET billdate=NOW(),billno='$billno',customername='$customername',customerno='$customerno',receivedamount='$receivedamount',saleman='$saleman',salephone='$salepnone',admin='$admin',`describe`='$describe',image1='$image_url1',image2='$image_url2',image3='$image_url3'";
		
		  //echo $sql;
		
		if (!mysql_query($sql,$conn))
		{       
			$output = array('data'=>'', 'msg'=>'保存失败'.mysql_error($conn),'success'=>'0');
			exit(JSON($output));
		}	

		//这里要处理，先签先付的原则处理“已付款”
	   //$quel=" UPDATE `team_contract` SET paid=(paid+$receivedamount) WHERE billno='$billno'";
	   
	   //使用存储过程更新合同付款记录
	  // $quel = "CALL recemoneyupdatecontract('$billno',$receivedamount);";
	  
	   //if (!mysql_query($quel,$conn))
		 // {       
		//	$output = array('data'=>'', 'msg'=>'update保存失败'.mysql_error($conn),'success'=>'0');
		//	exit(JSON($output));
		//}	
		
		$output = array('data'=>'', 'msg'=>'保存成功','success'=>'1');
		exit(JSON($output));	
		mysql_close($conn); 
	}
	
	//收款查询
	 if ($a == 'selectmoneyrecord') {
	   $page=$_GET['page'];  	
       $customerno=$_GET['customerno'];	 	   
	  
     if (($customerno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
		$query = "SELECT * FROM `team_receivables` WHERE customerno='$customerno' and admin='$admin' ORDER BY billdate DESC LIMIT $page,15"; 

 	 
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
	//删除任务
	if($a == 'deletetask'){
	     $billno = $_GET['billno'];
		 $key = $_GET['key'];  //0 提交，1 删除
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	 
	if($key=='0'){  
		$sql="UPDATE `team_task` SET `status`='1' WHERE billno='$billno'";
	}else if($key=='1'){
		$sql="UPDATE `team_task` SET `status`='-1' WHERE billno='$billno'";
	}

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }
	
	//合同中查询客户名称
	 if ($a == 'selectcustomer') {
		  $uid = $_GET['uid'];
		  $page=$_GET['page'];  
          $keyword=$_GET['keyword'];  
          $kflat=$_GET['kflat']; 
		  if (($uid == '') ) {
			$output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
			exit(JSON($output));
		  }
		  
	  if($kflat=='0'){  //非搜索状态
		  $query="SELECT a.billno,a.image,a.title,a.linkman,a.phone,a.tel,a.address,b.username FROM `team_customer` AS a LEFT JOIN `team_salesman` AS b ON b.`userno`=a.`saler` WHERE a.saler = '$uid' OR  a.saler IN (SELECT userno FROM `teams` WHERE admin='$uid' OR admin IN (SELECT userno FROM `teams` WHERE admin='$uid' AND isteam=1)) LIMIT $page,15";
	  }	else if($kflat=='1'){
		 $query="SELECT a.billno,a.image,a.title,a.linkman,a.phone,a.tel,a.address,b.username FROM `team_customer` AS a LEFT JOIN `team_salesman` AS b ON b.`userno`=a.`saler` WHERE (saler = '$uid' OR  saler IN (SELECT userno FROM `teams` WHERE admin='$uid' OR admin IN (SELECT userno FROM `teams` WHERE admin='$uid' AND isteam=1))) AND (title like '%$keyword%' or linkman like '%$keyword%') LIMIT $page,15"; 
	  }	  
	  
       //echo $query;
	  $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
	//查询汇报评论
 if ($a == 'selectcomment') {
       $billno=$_GET['billno'];  
	    if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }		
      $query="SELECT * FROM `team_comment` WHERE reportid='$billno' order by billdate desc ";
       
	  $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }	
	//发表汇报评论
if($a == 'insertcomment'){
	$uid = $_GET['uid'];
	$billno = $_GET['billno'];
	$image = $_GET['image'];
	$name = $_GET['name'];
	$comment = $_GET['comment'];
	
    $sql="insert `team_comment` SET userno = '$uid', billdate=NOW(),reportid='$billno',image='$image',`name`='$name',`comment`='$comment'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
   //更改汇报状态
	if($a == 'updateisread'){
	    $billno = $_GET['billno'];
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	
    $sql="UPDATE `team_workreport` SET isread='1' WHERE billno='$billno'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
	//查询证书
 if ($a == 'selectcertificate') {
        $customerno=$_GET['customerno'];  
	    if ($customerno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }		
      $query="SELECT image,`describe` FROM `team_certificate` WHERE customerno='$customerno' and admin='$admin' order by billdate desc ";
       
	  $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }	
 //是否加入团队team
 if($a == 'jointeams'){
	    $billno = $_GET['billno'];
		$flat= $_GET['flat'];
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	
    $sql="UPDATE `teams` SET astatus='$flat' WHERE billno='$billno'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
 
 //第三登录是否绑定了
 if ($a == 'isbingphone') {
         $type=$_GET['type'];  
		 $key=$_GET['key'];  
	    if ($key == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
      if($type=='0'){   //qq登录
		// $query='SELECT address,userno form `team_salesman` WHERE qqthreeload='$key'';
		 $query = "SELECT a.*,IFNULL(b.isteam,-1) AS isteam,b.image,IFNULL(b.astatus,-1) AS astatus FROM team_salesman AS a LEFT JOIN 
		          teams b ON a.userno = b.userno where (a.qqthreeload='$key')";
		 
	  }else if($type=='1'){   //微信		
		  $query = "SELECT a.*,IFNULL(b.isteam,-1) AS isteam,b.image,IFNULL(b.astatus,-1) AS astatus FROM team_salesman AS a LEFT JOIN 
		            teams b ON a.userno = b.userno where (a.wxthreeload='$key')";
	  } 	
       
	   $result = mysql_query($query, $conn) or die(mysql_error($conn));
   if (mysql_numrows($result) > 0){
   	$row = mysql_fetch_assoc($result);
	
    	$outdata=array('username'=>$row['username'],'userno'=>$row['userno'],'address'=>$row['address'],'phone'=>$row['phone'],
		          'team'=>$row['team'],'teamname'=>$row['teamname'],'teamdate'=>$row['teamdate'],'image'=>$row['image'],
				  'image1'=>$row['image1'],'image2'=>$row['image2'],'memo'=>$row['memo'],'isteam'=>$row['isteam'],'astatus'=>$row['astatus']);
    	$output = array('data'=>$outdata, 'msg'=>'已绑定','success'=>'1');
        exit(JSON($output));	
   }else{
    	 $output = array('data'=>'', 'msg'=>'未绑定','success'=>'0');
         exit(JSON($output));	
   }
   
 }	
 
 //查询是否存在对应的phone
 if ($a == 'ishavephone') { 
		 $userno=$_GET['userno'];  
	    if ($userno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
		 $query = "SELECT a.*,IFNULL(b.isteam,-1) AS isteam,b.image,IFNULL(b.astatus,-1) AS astatus FROM team_salesman AS a LEFT JOIN 
		          teams b ON a.userno = b.userno where (a.userno='$userno')";
				  
	   $result = mysql_query($query, $conn) or die(mysql_error($conn));
   if (mysql_numrows($result) > 0){
   	$row = mysql_fetch_assoc($result);
	
    	$outdata=array('username'=>$row['username'],'userno'=>$row['userno'],'address'=>$row['address'],'phone'=>$row['phone'],
		          'team'=>$row['team'],'teamname'=>$row['teamname'],'teamdate'=>$row['teamdate'],'image'=>$row['image'],
				  'image1'=>$row['image1'],'image2'=>$row['image2'],'memo'=>$row['memo'],'isteam'=>$row['isteam'],'astatus'=>$row['astatus']);
    	$output = array('data'=>$outdata, 'msg'=>'存在phone','success'=>'1');
        exit(JSON($output));	
   }else{
    	 $output = array('data'=>'', 'msg'=>'不存在phone','success'=>'0');
         exit(JSON($output));	
   }
   
 }	
 
 
 //wx操作绑定
 if($a == 'wxbingphone'){
	    $key = $_GET['key'];
		$username= $_GET['username'];
		$password= $_GET['password'];
		$userno= $_GET['userno'];
		$image= $_GET['image'];
		$wxthreeload= $_GET['wxthreeload'];
		
	   if ($userno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	 if($key=='0'){
		$sql="INSERT INTO `team_salesman` SET billno='$billno',username='$username',`password`='$password',userno='$userno',phone='$userno',
		      regdate=NOW(),image='$image',wxthreeload='$wxthreeload'";
	}else if($key=='1'){
		$sql="UPDATE `team_salesman` SET wxthreeload='$wxthreeload' WHERE userno='$userno'";
	}

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
 
 //qq绑定
 if($a == 'qqbingphone'){
	    $key = $_GET['key'];
		$username= $_GET['username'];
		$password= $_GET['password'];
		$userno= $_GET['userno'];
		$image= $_GET['image'];
		$qqthreeload= $_GET['qqthreeload'];
		
	   if ($userno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	 if($key=='0'){
		$sql="INSERT INTO `team_salesman` SET billno='$billno',username='$username',`password`='$password',userno='$userno',phone='$userno',
		      regdate=NOW(),image='$image',qqthreeload='$qqthreeload'";
	}else if($key=='1'){
		$sql="UPDATE `team_salesman` SET qqthreeload='$qqthreeload' WHERE userno='$userno'";
	}

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
 
 
//发送验证信息
if ($a == 'sendmsg') {
    $timeap = time();
    $timea = 'yy123456_' . $timeap . '_topsky';

    $timea = md5($timea);
    $tel = $_GET['tel'];
    $msg = '好业绩：你的验证码是:' . $_GET['msg'] . '，请及时输入！非本人操作，请忽略！';
    $msg = iconv("utf-8", "GBK//ignore", $_GET['msg']); //$_GET['msg']; 

    $msg = urlencode($msg); //. $_GET['msg']
    // echo $msg;
    $ch = curl_init();
    $str = 'http://admin.sms9.net/houtai/sms.php?cpid=566&password=' . $timea .
            '&channelid=16251&tele=' . $tel . '&msg=' . $msg . '&timestamp=' . $timeap;
    curl_setopt($ch, CURLOPT_URL, $str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    //  echo $output;
    if (strpos($output, 'success') !== false) {
        $output = array('data' => '', 'msg' => '短信提交成功', 'success' => '1');
        exit(JSON($output));
    }
    $output = array('data' => '', 'msg' => '短信提交失败' . $output, 'success' => '0');
    exit(JSON($output));
} 
 
 // flat=0 商品增加上传,  1 修改商品
 if ($a == 'putWaresAdd') {
    $input = file_get_contents("php://input"); //接收POST数据
    $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
		
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";				
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	 
	 if ($obj['billno'] != ''){		//修改时才传入billno值
		 $billno = gettostr($obj['billno']);
	 }
	 	 
	 $name = gettostr($obj['name']);
	 $price = gettostr($obj['price']); 
	 $type = gettostr($obj['type']); 
	 $unit= gettostr($obj['unit']); 
	 $number = gettostr($obj['number']);
	 $describe = gettostr($obj['describe']);
	 $flat = gettostr($obj['flat']);
	 $update="";
	 		 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				$update=$update."image1='$image_url1'";
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				if($update!=""){
					$update=$update.",image2='$image_url2'";
				}else{
					$update=$update."image2='$image_url2'";
				}
				
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				if($update!=""){
					$update=$update.",image3='$image_url3'";
				}else{
					$update=$update."image3='$image_url3'";
				}
			}
		}	
	if($flat=='0'){	//增加
	   if($update!=""){
			$sql = "INSERT INTO `team_wares` SET billno='$billno',billdate=NOW(),admin='$admin',waresname='$name',unit='$unit',model='$type',
			 productno='$number',price='$price',`description`='$describe',$update";
			
	   }else{
		   $sql = "INSERT INTO `team_wares` SET billno='$billno',billdate=NOW(),admin='$admin',waresname='$name',unit='$unit',model='$type',
			  productno='$number',price='$price',`description`='$describe'";
	   }
	 }else{  //修改
		 if($update!=""){
			$sql = "UPDATE `team_wares` SET waresname='$name',unit='$unit',model='$type',
			 productno='$number',price='$price',`description`='$describe',$update where billno='$billno'";			
	   }else{
		   $sql = "UPDATE `team_wares` SET waresname='$name',unit='$unit',model='$type',
			  productno='$number',price='$price',`description`='$describe' where billno='$billno'";
	   }
	}	 
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '添加失败！', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'添加成功！','success'=>'1');			
			exit(JSON($output));
 
   }
 
 //获取商品
  if ($a == 'getWaresInfo') {
 	   $response=array();
 	   $page = $_GET['page'];	
	   $search = $_GET['search'];  //是否搜索状态下
	   $keyword = $_GET['keyword'];	   
   
	   //按billdate 排序
		if($search=='0'){  //非搜索状态  
			 $query = "SELECT * FROM `team_wares` WHERE admin='$admin' and `status`>'-1' order by billdate  desc LIMIT $page,15";
		}else if($search=='1'){
		     $query = "SELECT * FROM `team_wares` WHERE admin='$admin' and `status`>'-1' and (waresname like '%$keyword%' or productno='$keyword') order by billdate  desc LIMIT $page,15";
	  }	
	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //删除商品
 if($a == 'deleteWares'){
	    $mbillno = $_GET['billno'];
		
	   if ($mbillno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	
    $sql="UPDATE `team_wares` SET `status`='-1' WHERE billno='$mbillno'";

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	

  //扫码查询商品信息
  
  if ($a == 'getWaresdata') {
 	   $response=array();   
 	   $productno = $_GET['productno'];		  
   
	  $query = "SELECT * FROM `team_wares` WHERE `status`>'-1' and productno='$productno' AND admin='$admin'";		
	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //上传进货商品信息
 if ($a == 'putStockin') {
    $input = file_get_contents("php://input"); //接收POST数据
 
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	 
	 $customerno = gettostr($obj['customerno']);
	 $customername = gettostr($obj['customername']);
	 $username = gettostr($obj['username']);
	 $name = gettostr($obj['name']);
	 $price = gettostr($obj['price']); 
	 $type = gettostr($obj['type']); 
	 $unit= gettostr($obj['unit']); 
	 $number = gettostr($obj['number']);  //编码
	 $describe = gettostr($obj['describe']);
     $knum = gettostr($obj['knum']);	 //数量	 
	 $mbillno = gettostr($obj['billno']);	 //数量	 
	 $serialno = gettostr($obj['serialno']);	 //唯一码
	 $kk = gettostr($obj['kk']);
   
   if($kk=='0'){
	    $sql = "INSERT INTO `team_stockin` SET billno='$billno',billdate=NOW(),customerno='$customerno',customername='$customername',username='$username',admin='$admin',wareno='$mbillno',warename='$name',model='$type',
		  productno='$number',price='$price',`description`='$describe',qty='$knum',unit='$unit',image1='$image1',image2='$image2',image3='$image3',serialno='$serialno'";
   }else if($kk=='1'){
	   $sql = "INSERT INTO `team_stockout` SET billno='$billno',billdate=NOW(),customerno='$customerno',customername='$customername',username='$username',admin='$admin',wareno='$mbillno',warename='$name',model='$type',
		  productno='$number',price='$price',`description`='$describe',qty='$knum',unit='$unit',image1='$image1',image2='$image2',image3='$image3',serialno='$serialno'";
   } 
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '添加失败!', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'添加成功!','success'=>'1');			
			exit(JSON($output));
 
   }

   //查询进货，出货 的信息  (只显示当天数据)
 if ($a == 'getAddWaresinfo') {
 	   $response=array();
	   $customerno = $_GET['customerno'];	
 	   $page = $_GET['page'];	
	   $mkey = $_GET['mkey'];  //0 进货,1 出货   
   
    if (($customerno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($admin == '') ) {
        $output = array('data'=>'', 'msg'=>'admin参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if($mkey=='0'){  //进货
	    $query = "SELECT * FROM `team_stockin` WHERE admin='$admin' and customerno='$customerno' AND TO_DAYS(billdate) = TO_DAYS(NOW()) order by billdate  desc LIMIT $page,15";
	 }else{       //出货
		$query = "SELECT * FROM `team_stockout` WHERE admin='$admin' and customerno='$customerno' AND TO_DAYS(billdate) = TO_DAYS(NOW()) order by billdate  desc LIMIT $page,15";
	 }
	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
  //进出货记录
  if ($a == 'getWareRecords') {
 	   $response=array();
 	   $wareno = $_GET['wareno'];	   
 	   $page = $_GET['page'];	
	   $key = $_GET['key'];  //0 进货,1 出货   
   
    if (($wareno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
     if($key=='0'){  //进货
	     $query = "SELECT * FROM  `team_stockin` WHERE wareno='$wareno' and admin='$admin'  order by billdate  desc LIMIT $page,15";		
	 }else{       //出货
	      $query = "SELECT * FROM `team_stockout` WHERE wareno='$wareno' and admin='$admin' order by billdate  desc LIMIT $page,15";	
	 }
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 //进  出货 （所有商品）记录, key 0 进，1 出
 if ($a == 'getALLWareRecords') {
 	   $response=array();
       $customerno = $_GET['customerno'];	 // 客户店铺billno   	   
	   $key = $_GET['key'];   
   
    if (($customerno == '') ) {
         if($key=='0'){  //进货	 
	     $query="SELECT DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate, SUM(qty*price) AS price,SUM(qty) AS qty FROM `team_stockin` WHERE  admin='$admin'
                GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d') ORDER BY DATE_FORMAT(billdate,'%Y-%m-%d') DESC ";	 
	  }else{       //出货
	      $query="SELECT DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate, SUM(qty*price) AS price,SUM(qty) AS qty FROM `team_stockout` WHERE  admin='$admin'
                GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d') ORDER BY DATE_FORMAT(billdate,'%Y-%m-%d') DESC ";
	  }
    }else{
      if($key=='0'){  //进货	 
        // $query="SELECT SUM(qty) AS qty,SUM(price) AS price,DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate FROM 
		 //       (SELECT *,DATE_FORMAT(billdate,'%Y-%m-%d') FROM`team_stockin`) AS k WHERE customerno='$customerno' AND userno='$userno' GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d')";		
	 
	     $query="SELECT DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate, SUM(qty*price) AS price,SUM(qty) AS qty FROM `team_stockin` WHERE customerno='$customerno' AND admin='$admin'
                GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d') ORDER BY DATE_FORMAT(billdate,'%Y-%m-%d') DESC ";
	 
	  }else{       //出货
	      //$query="SELECT SUM(qty) AS qty,SUM(price) AS price,DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate FROM 
		   //     (SELECT *,DATE_FORMAT(billdate,'%Y-%m-%d') FROM`team_stockout`) AS k WHERE customerno='$customerno' AND userno='$userno' GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d')";		
	 
	  $query="SELECT DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate, SUM(qty*price) AS price,SUM(qty) AS qty FROM `team_stockout` WHERE customerno='$customerno' AND admin='$admin'
                GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d') ORDER BY DATE_FORMAT(billdate,'%Y-%m-%d') DESC ";
	  }
	}
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
  //进货出货详情listview
  if ($a == 'getWprshow') {
 	   $response=array();
	   $customerno = $_GET['customerno'];	
	   $mdate= $_GET['mdate'];	
 	   $page = $_GET['page'];	
	   $key = $_GET['key'];  //0 进货,1 出货   
   
    if (($admin == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($customerno == ''){
		if($key=='0'){  //进货		
			$query = "SELECT * FROM `team_stockin` WHERE admin='$admin'  AND DATE_FORMAT('$mdate','%Y-%m-%d') = DATE_FORMAT(billdate,'%Y-%m-%d') order by billdate  desc LIMIT $page,15";
		}else{       //出货	
			$query = "SELECT * FROM `team_stockout` WHERE admin='$admin' AND DATE_FORMAT('$mdate','%Y-%m-%d') = DATE_FORMAT(billdate,'%Y-%m-%d') order by billdate  desc LIMIT $page,15";
		 }
	}else{
		if($key=='0'){  //进货		
			$query = "SELECT * FROM `team_stockin` WHERE admin='$admin' and customerno='$customerno' AND DATE_FORMAT('$mdate','%Y-%m-%d') = DATE_FORMAT(billdate,'%Y-%m-%d') order by billdate  desc LIMIT $page,15";
		}else{       //出货	
			$query = "SELECT * FROM `team_stockout` WHERE admin='$admin' and customerno='$customerno' AND DATE_FORMAT('$mdate','%Y-%m-%d') = DATE_FORMAT(billdate,'%Y-%m-%d') order by billdate  desc LIMIT $page,15";
		 }
	}
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
	 
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 
 
 //增加收支记录
 if ($a == 'addFinanceRecord') {
    $input = file_get_contents("php://input"); //接收POST数据
    $new_file = "Img" . date("YmdHms", time()) . ".jpg";
		$new_file1 ="Img". date("YmdHms",time())."1.jpg";
		$new_file2 ="Img". date("YmdHms",time())."2.jpg";
		$new_file3 ="Img". date("YmdHms",time())."3.jpg";
		
		$image_file1 ="./images/".$new_file1;
		$image_file2 ="./images/".$new_file2;
		$image_file3 ="./images/".$new_file3;
       	
		$image_url1 =  "";
		$image_url2 =  "";
		$image_url3 =  "";		
		
    $obj = json_decode($input, true);

	 $image1 = gettostr($obj['image1']);
	 $image2 = gettostr($obj['image2']);
	 $image3 = gettostr($obj['image3']);
	
	 $style = gettostr($obj['style']);
	 $name = gettostr($obj['name']);
	 $money = gettostr($obj['money']);
	 $date = gettostr($obj['date']);
	 $desc = gettostr($obj['desc']);
	 $username = gettostr($obj['username']);
	 $userno = gettostr($obj['userno']);	 
	 $imagestr="";	
	 
		if ($image1 != ''){
			if (file_put_contents($image_file1, base64_decode($image1))){
				$image_url1 =  $serverurl."/images/".$new_file1;
				$imagestr=$imagestr."image1='$image_url1'";
			}
		}
		if ($image2 != ''){
			if (file_put_contents($image_file2, base64_decode($image2))){
				$image_url2 =  $serverurl."/images/".$new_file2;
				if($imagestr==""){
					$imagestr="image2='$image_url2'";
				}else{
					$imagestr=$imagestr.",image2='$image_url2'";
				}
			}
		}
		if ($image3 != ''){
			if (file_put_contents($image_file3, base64_decode($image3))){
				$image_url3 =  $serverurl."/images/".$new_file3;
				if($imagestr==""){
					$imagestr="image3='$image_url3'";
				}else{
					$imagestr=$imagestr.",image3='$image_url3'";
				}
			}
		}	
		
		   if($imagestr!=""){
			$sql = "INSERT INTO `team_finance` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',finname='$name',money='$money',`type`='$style',findate='$date',$imagestr,`describe`='$desc'"; 
		 }else{
			$sql = "INSERT INTO `team_finance` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',finname='$name',money='$money',`type`='$style',findate='$date',`describe`='$desc'"; 
		 }
	    //echo $sql;
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
 //获取我的管家收支数据
 if ($a == 'selectFinanceData') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	   $flat=$_GET['flat'];
	   
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){  //查询收入
		$query = "SELECT * FROM `team_finance` WHERE userno='$uid' and admin='$admin' AND `type` = '0' ORDER BY billdate DESC LIMIT $page,15";
	}else if($flat=='1'){  //查询支出
		$query = "SELECT * FROM `team_finance` WHERE userno='$uid' and admin='$admin' AND `type` = '1' ORDER BY billdate DESC LIMIT $page,15";
	}else if($flat=='2'){   //查询所有
		$query = "SELECT * FROM `team_finance` WHERE userno='$uid' and admin='$admin' ORDER BY billdate DESC LIMIT $page,15";
	}
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 //添加工作计划
  if($a == 'addworkplan'){
	    $uid = $_GET['uid'];
		$username = $_GET['username'];
		$date = $_GET['date'];
		$fplan = $_GET['fplan'];
		$splan = $_GET['splan'];
		$key = $_GET['key'];
		$mbillno = $_GET['billno'];
		
	   if ($uid == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	 
	  if($key=='0'){
	    $query = "select * from team_workplan where admin='$admin' and userno='$uid' and workdate='$date'";
		$result = mysql_query($query, $conn) or die(mysql_error($conn));
		if (mysql_numrows($result) > 0){		
	   	   $output = array('data'=>'', 'msg'=>'已存在','success'=>'-1');
			exit(JSON($output));
		}		
		  $sql="insert into `team_workplan` SET billdate=NOW(),billno='$billno',admin='$admin',username='$username',userno='$uid',workdate='$date',fplan='$fplan',splan='$splan'";
	 
	 }else{  //修改操作
		  $sql="update `team_workplan` SET username='$username',workdate='$date',fplan='$fplan',splan='$splan' where billno='$mbillno'"; 
	  }   

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
   //提交当天总结,删除
    if($a == 'putsummary'){
		$billno = $_GET['billno'];
		$summary = $_GET['summary'];
		$key = $_GET['key'];
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
      }	 
	if($key=='-1'){  //删除
	  $sql="update `team_workplan` SET  `status`='-1' where billno='$billno'";
	}else{  //提交当天总结
	  $sql="update `team_workplan` SET  summary='$summary' where billno='$billno'";
	}

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
   
   
   //查询工作计划数据
if ($a == 'selectworkPlan') {
 	   $uid= $_GET['uid'];
	   $page=$_GET['page'];
	   $flat=$_GET['flat'];
	     
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='0'){  //只查询自己本周数据
		$query = "SELECT a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE a.userno='$uid' 
		AND  YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) and `status`>'-1' ORDER BY a.workdate ASC LIMIT $page,15;";
	}else if($flat=='1'){  //查询自己所有数据
		$query = "SELECT a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE  a.userno='$uid' and `status`>'-1' ORDER BY a.billdate DESC LIMIT $page,15";
	}else if($flat=='2'){   //队员本周
		$query = "SELECT a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE (a.admin='$admin' OR a.admin='$uid') and YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) and `status`>'-1' ORDER BY a.workdate ASC LIMIT $page,15;";
	}
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 

   
 //保存出，入仓单  
  if ($a == 'putWarehouse') {
    $input = file_get_contents("php://input"); //接收POST数据
    $obj = json_decode($input, true);

	 $mbillno = gettostr($obj['mbillno']); //个人的billno
	 $bno = gettostr($obj['bno']);   //合同的billno
	 $username = gettostr($obj['username']);
	 $userno = gettostr($obj['userno']);
	 $warename = gettostr($obj['warename']);
	 $model = gettostr($obj['model']);
	 $num = gettostr($obj['num']);
	 $remarks = gettostr($obj['remarks']);
	 $key = gettostr($obj['key']);	
     $kk = gettostr($obj['kk']);
    if($kk=='0'){
		 if($key=='0'){   //入仓单
		 $sql = "INSERT INTO `team_warehousein` SET billdate=NOW(),billno='$billno',bno='$bno',username='$username',userno='$userno',warename='$warename'
		 ,model='$model',`num`='$num',remarks='$remarks',admin='$admin'";  

	 }else{   //出仓单
		  $sql = "INSERT INTO `team_warehouseout` SET billdate=NOW(),billno='$billno',bno='$bno',username='$username',userno='$userno',warename='$warename'
		 ,model='$model',`num`='$num',remarks='$remarks',admin='$admin'";  
	 }	
	}else{ //修改操作
		 if($key=='0'){   //入仓单
		 $sql = "update `team_warehousein` SET billdate=NOW(),warename='$warename'
		 ,model='$model',`num`='$num',remarks='$remarks' where billno='$mbillno'";  
	 }else{   //出仓单
		  $sql = "update `team_warehouseout` SET billdate=NOW(),warename='$warename'
		 ,model='$model',`num`='$num',remarks='$remarks' where billno='$mbillno'";  
	 }	
	}	 		
		 
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
			exit(JSON($output));
 
   }
   //查询出入仓单
   if ($a == 'selectWarehouse') {
 	   $bno= $_GET['bno'];
	   $key=$_GET['key'];  //0查询入仓单,1查询出仓单
	  
     if (($bno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($key=='0'){  //查询入仓单
		$query = "SELECT * FROM `team_warehousein` WHERE admin='$admin' and  bno='$bno' AND `status` > '-1' ORDER BY billdate DESC";
	}else if($key=='1'){  //查询出仓单
		$query = "SELECT * FROM `team_warehouseout` WHERE admin='$admin' and bno='$bno' AND `status` > '-1' ORDER BY billdate DESC";
	}
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }//通过循环读取数据内容12222zdfrrttrt
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;
        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 //提交出入仓单与删除 的更改状态
 if($a == 'updateWarehouse'){
		$billno = $_GET['billno'];
		$key = $_GET['key'];
		$flat = $_GET['flat']; //0提交仓单，1删除仓单
		
	   if ($billno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
      }	 
	if($key=='0'){  //入仓单
	  if($flat=='0'){   //提交仓单
		  $sql="update `team_warehousein` SET  `status`='1' where bno='$billno' and `status`='0'";
	  }else{   //删除仓单
		   $sql="update `team_warehousein` SET  `status`='-1' where billno='$billno'";
	  }	 
	}else{  //出仓单
	  if($flat=='0'){   //提交仓单
		  $sql="update `team_warehouseout` SET  `status`='1' where bno='$billno' and `status`='0'";
	  }else{   //删除仓单
		   $sql="update `team_warehouseout` SET  `status`='-1' where billno='$billno'";
	  }
	}

    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'error','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'ok','success'=>'1');
          exit(JSON($output));	            
   }	
 
 

 
 //工资激励机制  获取机制
if ($a == 'getwages') {
	   $uid=$_GET['uid'];
 	   $page = $_GET['page'];	
	   $search = $_GET['search'];  //是否搜索状态下
	   $keyword = $_GET['keyword'];	   
   
   
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }   
	   //按billdate 排序
		if($search=='0'){  //非搜索状态  
			 $query = "select * FROM `team_wage` WHERE admin='$admin' and userno='$uid' order by id desc LIMIT $page,15";
		}else if($search=='1'){
		     $query = "select * FROM `team_wage` WHERE admin='$admin' and  userno='$uid' and wagename like '%$keyword%' order by billdate  desc LIMIT $page,15";
	  }	
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
}
 
 
 //工资激励机制 上传
 if ($a == 'putwage') {
    $input = file_get_contents("php://input"); //接收POST数据
    
    $obj = json_decode($input, true);

	 $billno = gettostr($obj['billno']);
	 $userno = gettostr($obj['userno']);
	 $wagename = gettostr($obj['wagename']);
	 $basewage = gettostr($obj['basewage']); 
	 $basetask = gettostr($obj['basetask']); 
	 $basebonus = gettostr($obj['basebonus']);
	 $addcommission = gettostr($obj['addcommission']);
	 $deccommission = gettostr($obj['deccommission']);
	 $remark = gettostr($obj['remark']);
	 
	 $update="";
	 		 
 
		
   if($billno!=""){
	    $sql = "UPDATE `team_wage` SET wagename='$wagename',basewage=$basewage,basetask=$basetask,basebonus=$basebonus,addcommission=$addcommission,deccommission=$deccommission,remark='$remark' where billno='$billno'";
		
   }else{
	   $billno=substr(date("ymdHis"),1,11).mt_rand(100,999);	//动态生成一个全局单号
	   $sql = "INSERT INTO `team_wage` SET wagename='$wagename',basewage=$basewage,basetask=$basetask,basebonus=$basebonus,addcommission=$addcommission,deccommission=$deccommission,remark='$remark',billno='$billno',userno='$userno',admin='$admin'";
   }
	
	//echo $sql;
    if (!mysql_query($sql, $conn)) {
        $output = array('data' => '', 'msg' => '上传失败', 'success' => '0');
        exit(JSON($output));
    }
	
	$output = array('data'=>'', 'msg'=>'上传成功','success'=>'1');			
		exit(JSON($output));
   } 
 
  //我的团队测试
   if ($a == 'getmyTeam') {
 	   $uid= $_GET['uid'];
	   $key=$_GET['key'];  //0管理员,1组长，2 队员
	   $division=$_GET['division'];	
	   $image="";
	   $hnum="";  //已有多少组长或 多少员工
	   $hname=""; //管理员名，组长名
	   $htnum=""; //许可数量
	   $htdate="";//到期日
	   $htname="";//团队名，小组名	     
	  
     if (($uid == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	
	
	if($key=='-1'){  //副管理员
		$sql="SELECT A.num,C.hname,C.image,B.team,B.teamdate,B.teamname FROM (SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin') A,
             (SELECT team,teamdate,teamname FROM `team_salesman` WHERE userno='$admin') B,
			 (SELECT image,username AS hname FROM `team_salesman` WHERE userno='$uid') C";
		
	}else if($key=='0'){//(管理员角度)得到已有所有队员num, 管理员名username, 许可数量team,到期日teamdate
		$sql="SELECT A.num,B.image,B.hname,B.team,B.teamdate,B.teamname FROM (SELECT COUNT(*) AS num FROM `teams` WHERE admin='$uid') A,
             (SELECT image,team,teamdate,username AS hname,teamname FROM `team_salesman` WHERE userno='$uid') B";
	}else if($key=='1'){ //(组长角度) 已有队员num, 组长名username, 工作部门 teamname		
		$sql="SELECT A.num,B.image,B.hname,B.teamname,C.team,B.teamdate FROM (SELECT COUNT(*) AS num FROM `teams` WHERE division='$uid') A,
             (SELECT image,teamname,teamdate,username AS hname FROM `team_salesman` WHERE userno='$uid') B,
			 (SELECT (team - (SELECT COUNT(*) FROM `teams` WHERE admin='$admin')) AS team FROM `team_salesman` WHERE userno='$admin') C";	
	}else {  // (队员角度)  已有队员num, 组长名username, 工作部门 teamname
		$sql="SELECT A.num,B.image,B.hname,B.teamname,B.team,B.teamdate FROM (SELECT COUNT(*) AS num FROM `teams` WHERE division ='$division') A,
             (SELECT image,teamname,team,teamdate,username AS hname FROM `team_salesman` WHERE userno='$division') B";	
	}
	$result = mysql_query($sql, $conn) or die(mysql_error($conn));
	$mitems = array();
	if (mysql_numrows($result) > 0) {  
		while ($row = mysql_fetch_assoc($result)) {
        $image = $row['image'];			
		$hnum = $row['num'];
		$hname= $row['hname'];  
        $htnum= $row['team']; 
        $htdate= $row['teamdate'];  
        $htname= $row['teamname']; 	
		$mrow = array('image'=>$image,'hnum'=>$hnum,'hname'=>$hname,'htnum'=>$htnum,'htdate'=>$htdate,'htname'=>$htname);
        array_push($mitems, $mrow);		
		}		
	  }		
	if($key=='-1'){//副管理员查询有多少个组长(副管理员不显示包括自己的其它副管理员)
		$query = "SELECT a.billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.jobnumber,b.teamname,b.job FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` 
                  WHERE admin='$admin' AND admin=division AND isteam !='-2' ORDER BY a.isteam DESC,a.id DESC";
	}else if($key=='0'){ //管理员查询有多少个组长
		$query = "SELECT a.billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.jobnumber,b.teamname,b.job FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` 
                  WHERE admin='$uid' AND admin=division ORDER BY a.isteam DESC,a.id DESC";
	}else if($key=='1'){  //组长查询自己有多少个员工
		$query = "SELECT a.billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.jobnumber,b.job,b.teamname FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` 
                 WHERE division='$uid' AND admin!=division ORDER BY a.isteam DESC,a.id DESC";
	}else{   //队员查询自己所在的团队有多少个员工
		$query = "SELECT a.billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.jobnumber,b.job,b.teamname FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` 
                 WHERE division='$division' AND admin!=division ORDER BY a.isteam DESC,a.id DESC";
	}
	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        $rows = array('billno'=>$row['billno'],'username'=>$row['username'],'userno'=>$row['userno'],'image'=>$row['image'],'jobnumber'=>$row['jobnumber'],'teamname'=>$row['teamname'],
		'job'=>$row['job'],'isteam'=>$row['isteam'],'astatus'=>$row['astatus']);
		array_push($items, $rows);
     }
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
		$output["head"] = $mitems;
        $output["body"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;     
        exit(JSON($output));
     }else{
     	$output = array('head'=>$mitems,'body'=>'','msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
 }
 //我的团队添加组长，添加队员
 if ($a == 'addMeteamuser') {
	 $madmin = $_GET['admin'];        //管理员号码，组长号码
	 $iskey = $_GET['iskey']; 
	 $teamname = $_GET['teamname'];  //小组名称
	 $userno = $_GET['userno'];     //组长号码，队员号码
	 $adminname = $_GET['adminname']; //发送方(管理员名称，组长名称)
	 $mflat = $_GET['mflat'];        //0 管理员帮直属队员添加队员 ，1 直属队员自己添加队员,
	 $username = ""; //收信人名称
	 $msg="";
	 $cname="";
	 
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($madmin == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }	
	
	$query = "select billno,username from team_salesman where userno='$userno'";
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {  
		while ($row = mysql_fetch_assoc($result)) {	
		$username = $row['username'];	
		}		
	  }else{
		mysql_close($conn); 
     	$output = array('data'=>'', 'msg'=>'该用户还没有注册！','success'=>'0');
        exit(JSON($output)); 
	  }				
	
 	$query = "SELECT * FROM teams where (userno='$userno' OR division='$userno') AND (astatus='1' OR astatus='0')";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0){
     	$output = array('data'=>'', 'msg'=>'该队员已服务其它工作组！','success'=>'0');
        exit(JSON($output));
	}
	if($iskey=='-2'){   //设置副管理员
		$cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您来当".$teamname;
		$sql = "insert into teams set billno='$billno',isteam=$iskey,admin='$admin',division='$admin',userno='$userno'";		
	}else if($iskey=='0'||$iskey=='2'||$iskey=='3'||$iskey=='4'){  //添加组长 iskey=0,2,3,4
	    $cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您来当".$teamname."小组的组长";
		$sql = "insert into teams set billno='$billno',isteam=$iskey,admin='$admin',division='$admin',userno='$userno'";
	}else if($iskey=='1'){   //添加队员 iskey=1
	    if($mflat=='0'){
			$cname=$adminname."(管理员)";
		}else{
			$cname=$adminname."(组长)";
		}
	    $msg=$adminname."诚邀您加入".$teamname."小组,成为我们的一员!";
		$sql = "insert into teams set billno='$billno',isteam=$iskey,admin='$admin',division='$madmin',userno='$userno'";
	}
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'添加失败！'.mysql_error($conn),'success'=>'0');
        exit(JSON($output));
    }
	
	$sql2="UPDATE `team_salesman` SET teamname='$teamname' WHERE userno='$userno'";
	mysql_query($sql2,$conn);
	
	if($mflat=='0'){  //管理员帮直属队员添加队员
		$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$cname',creuserno='$admin',admin='$admin', title='加入到团队',message='$msg',username='$username',userno='$userno',`type`=3";
	}else{           //直属队员自己添加队员
		$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$cname',creuserno='$madmin',admin='$admin', title='加入到团队',message='$msg',username='$username',userno='$userno',`type`=3";
	}
	//添加到消息队列
    mysql_query($sql,$conn);
     
	//echo $sql;
	mysql_close($conn); 
	$output = array('data'=>'', 'msg'=>'添加成功','success'=>'1');
	exit(JSON($output));
 } 
 
 //退出团队
 if ($a == 'outTeam') {
    $userno = $_GET['userno'];  
    $password = $_GET['password'];
    $division = $_GET['division'];	
    $msg="";
	
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    $query = "SELECT username FROM team_salesman  where (userno='$userno') and ((password='$password') OR (repassword='$password' AND (lastdate BETWEEN DATE_ADD( NOW(), INTERVAL -1 HOUR)  AND  NOW() )))";	
	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
    if (mysql_numrows($result) > 0){
		$row = mysql_fetch_array($result);	
		$username = $row['username'];	
		$msg=$username."主动退出了团队！";
		$sql="UPDATE `teams` SET astatus='-1' WHERE userno='$userno'";
		if (!mysql_query($sql, $conn)) {
           $output = array('data' => '', 'msg' => '退出团队失败!', 'success' => '0');
           exit(JSON($output));
        }
				
		$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='系统发送',creuserno='$userno',admin='$admin', title='退出团队提示',message='$msg',userno='$division',`type`=1";

        mysql_query($sql,$conn);
	    mysql_close($conn); 
		
		$output = array('data'=>'', 'msg'=>'退出团队成功!','success'=>'1');			
		exit(JSON($output));
		
	}else{
		$output = array('data' => '', 'msg' => '退出失败,密码错误！', 'success' => '-1');
        exit(JSON($output));
	}
  } 
 
 // 普通人申请加入团队
  if ($a == 'applyTeam') {
    $userno = $_GET['userno'];  
	$username = $_GET['username'];  
    $aduserno = $_GET['aduserno'];    // 管理员号码，组长号码
    $msg=$username."申请加入你的团队！";
	
    if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	// 1.查询aduserno号码是否注册
	$query = "select billno from team_salesman where userno='$aduserno'";
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) <= 0) {  
		mysql_close($conn); 
     	$output = array('data'=>'', 'msg'=>'该用户还没有注册！','success'=>'0');
        exit(JSON($output)); 	
	  }	
	  // 2.查询aduserno 是否开通升级了团队
	$query = "SELECT billno FROM `team_salesman` WHERE team>0 and teamdate!='' and userno='$aduserno'";	
	$result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0) {  //是否是管理员
		$items = array();	
	  }else{   //是否是各部门组长
		 $query2 = "SELECT admin FROM `teams` WHERE userno='$aduserno' AND (division=admin) AND division!='' and astatus='1'";	
		 $result = mysql_query($query2, $conn) or die(mysql_error($conn));
		 if (mysql_numrows($result) > 0) { 
			$items = array();	
		  }else{  
			mysql_close($conn); 
     	    $output = array('data'=>'', 'msg'=>'该用户没有权限添加队员！','success'=>'0');
            exit(JSON($output)); 	   
	    }		
    }
	
	$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$username',creuserno='$userno', title='申请加入团队',message='$msg',username='管理员',userno='$aduserno',`type`=3";

        mysql_query($sql,$conn);
	    mysql_close($conn); 		
		$output = array('data'=>'', 'msg'=>'申请信息发送成功!','success'=>'1');			
		exit(JSON($output));	
  } 
  
  //管理员处理申请加入请求
   if ($a == 'addApplyTeam') {
    $applyno = $_GET['applyno'];      //申请人号码
	$applyname = $_GET['applyname'];  //申请人名称  
	$adusername = $_GET['adusername']; 
    $aduserno = $_GET['aduserno'];    // 管理员号码，组长号码,(若是副管理员,aduserno就是上级管理员号码)
	$isteam = $_GET['isteam'];
	$flat = $_GET['flat'];
	
    if (($applyno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	 if (($aduserno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }
	if($flat=='1'){
	   $msg="恭喜你成功加入了".$adusername."的团队!";
	
	// 1.查询申请人是否已经加入了其它团队
	$query = "SELECT * FROM teams where (userno='$applyno' OR division='$applyno') AND (astatus='1' OR astatus='0')";
    $result = mysql_query($query, $conn) or die(mysql_error($conn));
	if (mysql_numrows($result) > 0){
     	$output = array('data'=>'', 'msg'=>'该队员已服务其它工作组！','success'=>'0');
        exit(JSON($output));
	}
	// 2.查询是否有许可人数添加
	
	$sql = "insert into teams set billno='$billno',isteam=$isteam,admin='$admin',division='$aduserno',userno='$applyno',astatus='1'";
	
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'添加失败！'.mysql_error($conn),'success'=>'0');
        exit(JSON($output));
    }	
	$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$adusername',creuserno='$aduserno', title='申请加入团队',message='$msg',username='$applyname',userno='$applyno',`type`=1";

        mysql_query($sql,$conn);
	    mysql_close($conn); 		
		$output = array('data'=>'', 'msg'=>'添加成功','success'=>'1');		
		exit(JSON($output));	
	}else{
		$msg="很遗憾,".$adusername."拒绝了你加入团队!";
		$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$adusername',creuserno='$aduserno', title='申请加入团队',message='$msg',username='$applyname',userno='$applyno',`type`=1";
        mysql_query($sql,$conn);
	    mysql_close($conn);
        $output = array('data'=>'', 'msg'=>'拒绝加入!','success'=>'1');				
		exit(JSON($output));	
	}
  } 
  
  
 //查询从属 名单
 if ($a == 'selectManTeam') {
	   $userno=$_GET['userno'];
   
     if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }   
	  $query = "SELECT A.userno,B.image,B.username FROM `teams`  AS A LEFT JOIN team_salesman B ON A.`userno`=B.`userno` 
	           WHERE admin='$admin' AND admin=division AND isteam!=-2 AND A.userno !='$userno'";
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
}
 // 更换从属 关系
 if ($a == 'changeSubordinate') {
	   $username=$_GET['username'];
	   $userno=$_GET['userno'];
	   $adusername=$_GET['adusername'];
	   $aduserno=$_GET['aduserno'];
       $msg=$username."成功并入到".$adusername."团队中";
     if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }   
	  if (($aduserno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }   

	$sql="UPDATE `teams` SET division='$aduserno' WHERE division='$userno'";
	 mysql_query($sql, $conn);
	 
	$sql="UPDATE `teams` SET division='$aduserno',isteam='1' WHERE userno='$userno'";
	if (!mysql_query($sql, $conn)) {
           $output = array('data' => '', 'msg' => '更换从属失败!', 'success' => '0');
           exit(JSON($output));
        }
	$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='系统发送',creuserno='$admin', title='更换从属提示',message='$msg',username='管理员',userno='$aduserno',`type`=1";
        mysql_query($sql,$conn);
		
	    mysql_close($conn); 		
		$output = array('data'=>'', 'msg'=>'更换成功!','success'=>'1');			
		exit(JSON($output));	
	 
}
 //取得生产原料表的数据列表
  if($a == 'sendTeamMess'){
		$username = $_GET['username'];
		$userno = $_GET['userno'];
		$mess = $_GET['mess']; //信息内容
		$adusername = $_GET['adusername'];
		$aduserno = $_GET['aduserno'];
		
	   if ($userno == '') {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
       }	 
	  
	   $sql = "INSERT INTO `team_message` SET billno='$billno', creuser='$adusername',creuserno='$aduserno', title='我的团队信息',message='$mess',username='$username',userno='$userno',`type`=1";
	  
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'信息发送失败！','success'=>'0');
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'信息发送成功！','success'=>'1');
          exit(JSON($output));	            
   }	
 
 
 
 
 //查询生产原料表结构数据
 if ($a == 'getcrudelist') {
	 $admin=$_GET['admin'];			//管理员编号
	 //$userno=$_GET['userno'];       //用户编号
	 $begindate=$_GET['begindate']; //开始日期
	 $enddate=$_GET['enddate'];		//结束日期
	 $customer=$_GET['customer'];	//客户名称
   
     if (($admin == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }    
     if (($userno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
        exit(JSON($output));
    }   
	
	  $query = "SELECT * FROM `team_crude` WHERE admin='$admin' ";
	  if ($begindate<>''){
		  $query = $query. "  and billdate between '$begindate' and '$enddate'";
	  }
	  if ($customer<>''){
		  $query = $query. "  and custname='$customer'";
	  }
 	
      $result = mysql_query($query, $conn) or die(mysql_error($conn));
      $output = '';
      $items = array();
     while($row=mysql_fetch_assoc($result)){
        array_push($items, $row);
     }
     $res = urldecode(json_encode($items));
     if ($res<>'[]'){
        $output["data"] = $items;
        $output["msg"] = "获取成功";
        $output["success"] = true;        
        exit(JSON($output));
     }else{
     	$output = array('data'=>'', 'msg'=>'数据为空','success'=>'0');
        exit(JSON($output));
     } 
}
 
 
 
  //新增、编辑生产原料记录
  if ($a == 'setcrude') {
	$input = file_get_contents("php://input"); //接收POST数据

	$update = "";
	$obj = json_decode($input,true);	  
	
	$billno= @$_GET['billno'] ? $_GET['billno'] : substr(date("ymdHis"),1,11).mt_rand(100,999);	
    $admin =gettostr($obj['admin']);
    $billdate = gettostr($obj['billdate']);
    $crudename = gettostr($obj['crudename']);		//原料名称
	$qty = gettostr($obj['qty']);					//来胚数量
	$extent = gettostr($obj['extent']);				//来胚总长度
	$unit = gettostr($obj['unit']);					//单位
	$price = gettostr($obj['price']);				//价格
	$remark = gettostr($obj['remark']);
	$machineno= gettostr($obj['machineno']);		//机台号
	$color = gettostr($obj['color']);
	$model = gettostr($obj['model']);
	$jggy = gettostr($obj['jggy']);					//加工工艺
	$jggy_xm = gettostr($obj['jggy_xm']);			//加工工艺细码
	$custno = gettostr($obj['custno']);
	$custname = gettostr($obj['custname']);
	$custshort = gettostr($obj['custshort']);		//客户简称
	$factory = gettostr($obj['factory']);			//加工工厂
	$factoryno = gettostr($obj['factoryno']);		//工厂编号
	$attach = gettostr($obj['attach']);				//允行并匹	0,1
	$attachtex = gettostr($obj['attachtex']);		//允行并匹说明
	$reve = gettostr($obj['reve']);					//是否收取  0,1
	$reveuser = gettostr($obj['reveuser']);			//收布人
	$revedate = gettostr($obj['revedate']);
	$revecar = gettostr($obj['revecar']);			//收布车型
	$reveplate = gettostr($obj['reveplate']);		//收布车牌
	$stretch = gettostr($obj['stretch']);			//拉伸
	$veins = gettostr($obj['veins']);				//斜纹
	$processtxt = gettostr($obj['processtxt']);		//加工要求
	$processtype = gettostr($obj['processtype']);	//加工种类
	$processremark = gettostr($obj['processremark']);	//加工备注
	$vision = gettostr($obj['vision']);				//分色
	$userno = gettostr($obj['userno']);				//录入人编号
	$username = gettostr($obj['username']);			//录入人名称
	$flat = $obj['flat'];							//添加 0， 编辑 1 标记 
	

	
    if ($admin == '')  {
        $output = array('data'=>'', 'msg'=>'admin is not none','success'=>'0');
        exit(JSON($output));
    }
    if ($model == '') {
        $output = array('data'=>'', 'msg'=>'model is not none','success'=>'0');
        exit(JSON($output));
    }
	if ($custno == ''){
        $output = array('data'=>'', 'msg'=>'custno is not none','success'=>'0');
        exit(JSON($output));	
	}
	  

		
		
	if($flat=='0') {    //增加
		$sql="INSERT INTO team_crude set billno='$billno',admin='$admin',billdate='$billdate',crudename='$crudename',qty='$qty',extent='$extent',unit='$unit',price='$price',remark='$remark',machineno='$machineno',color='$color',model='$model',jggy='$jggy',jggy_xm='$jggy_xm',$update,`custno`='$custno',custname='$custname',custshort='$custshort',factory='$factory',attach='$attach',attachtex='$attachtex',reve='$reve',reveuser='$reveuser',revedate='$revedate',stretch='$stretch',veins='$veins',userno='$userno',username='$username'";

	}else if($flat=='1'){    //更改
		$sql="UPDATE team_crude set billdate='$billdate',crudename='$crudename',qty='$qty',extent='$extent',unit='$unit',price='$price',remark='$remark',machineno='$machineno',color='$color',model='$model',jggy='$jggy',jggy_xm='$jggy_xm',$update,`custno`='$custno',custname='$custname',custshort='$custshort',factory='$factory',attach='$attach',attachtex='$attachtex',reve='$reve',reveuser='$reveuser',revedate='$revedate',stretch='$stretch',veins='$veins',userno='$userno',username='$username' where billno='$billno' and admin='$admin'";
	}
	if (!mysql_query($sql,$conn))
	{       
		$output = array('data'=>'', 'msg'=>'error','success'=>'0');
		mysql_close($conn); 
		exit(JSON($output));
	}

	$output = array('data'=>'', 'msg'=>'ok','success'=>'1');
	mysql_close($conn); 
	exit(JSON($output));
  }
 //删除进胚记录
  if ($a == 'delcrudelist') {
 	   $billno= $_GET['billno'];
	   $flat= $_GET['flat'];
	   
	   
 	   if (($billno == '') ) {
        $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
		mysql_close($conn); 
        exit(JSON($output));
    }	
    $sql="update team_crude set `status`='-1' where billno='$billno'"; 
    if (!mysql_query($sql,$conn))
    {       
        $output = array('data'=>'', 'msg'=>'进胚记录删除失败','success'=>'0');
		 
        exit(JSON($output));
    }
        $output = array('data'=>'', 'msg'=>'进胚记录删除成功','success'=>'1');
		
        exit(JSON($output));	
 }
 
 
 
 
?>
