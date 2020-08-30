<?php
// 壹软网络内部管理功能接口集中营
 
// 取得新注册的用户列表  /  新客户挖掘接口
if ($a == 'getintent') {
	$flag = @$_GET['flag']? checkInput($_GET['flag']): 0; // 0 新注册成功的用户 1 发送短信记录用户
	$begindate = @$_GET['begin']? checkInput($_GET['begin']):'';		//开始时间
	$enddate = @$_GET['begin']? checkInput($_GET['end']): '';		//结束时间
	$output = array('list'=>array(), 'total'=>0);
	
   if ($flag == 0 ){
		if ($begindate==''){
			$query="SELECT SQL_CALC_FOUND_ROWS regdate,billno,username,userno,nickname,tel,sex,calltxt, 0 as flag FROM `team_salesman` ";
		} else {
			$query="SELECT SQL_CALC_FOUND_ROWS regdate,billno,username,userno,nickname,tel,sex,calltxt, 0 as flag FROM `team_salesman` WHERE  regdate between '$begindate' and '$enddate'";
		}
   } else {
		if ($begindate==''){
			$query="SELECT SQL_CALC_FOUND_ROWS billdate AS regdate,id AS billno,functions AS username,tel AS userno,'' AS nickname, tel, '' AS sex,calltxt, 1 as flag FROM `team_smscache` ";
		} else {
			$query="SELECT SQL_CALC_FOUND_ROWS billdate AS regdate,id AS billno,functions AS username,tel AS userno,'' AS nickname, tel, '' AS sex,calltxt, 1 as flag FROM `team_smscache` WHERE  regdate between '$begindate' and '$enddate'";
		}

   }
	
	$query .= " ORDER BY id DESC".$paging;
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
	$output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功','total'=>"$total", 'status'=>'0')));
	
}



// 新客户挖掘  跟进状态信息设置
if ($a == 'setcalltxt') {
	$flag = @$_GET['flag']? checkInput($_GET['flag']): 0; 
	$billno= checkInput($_GET['billno']);
	$calltxt = checkInput($_GET['calltxt']);		//添加客户微信，客户无兴趣， 客户挂机， 电话无人接， 电话错误
	$output = array('list'=>array(), 'total'=>0);

	if (!$billno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	
	if ($flag == 0){
		$sql = "update team_salesman set calltxt='$calltxt' where billno='$billno'";
	} else {
		$sql = "update team_smscache set calltxt='$calltxt' where id='$billno'";
	}
	if (!$mysqli->query($sql))
	{       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
	
}


//取得理发票列表
if ($a == 'getinvoicelist') {
	$searchkey = checkInput($_GET['searchkey']);
	$begindate = @$_GET['begin']? checkInput($_GET['begin']):'';		//开始时间
	$enddate = @$_GET['end']? checkInput($_GET['end']): '';		//结束时间
	$output = array('list'=>array(), 'total'=>0);
	
   if ($searchkey==''){
	if ($begindate==''){
		$query="SELECT SQL_CALC_FOUND_ROWS * FROM `t_invoice` where fill=0 ";
	} else {
		$query="SELECT SQL_CALC_FOUND_ROWS * FROM `t_invoice` WHERE fill=0 AND billdate between '$begindate' AND '$enddate'";
	}
   } else {
	   $query="SELECT SQL_CALC_FOUND_ROWS * FROM `t_invoice` where dutyno like '%$searchkey%' or company like '%$searchkey%'";
   }
 
	
	$query .= " ORDER BY id DESC".$paging;
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
	$output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功','total'=>"$total", 'status'=>'0')));
	
}


//发票物流寄出
if ($a == 'postinvoice') {
	$billno = checkInput($_GET['billno']);
	$express_name = checkInput($_GET['express_name']);;		//物流公司
	$express_no =checkInput($_GET['express_no']);;		//物流单号
	$output = array('list'=>array(), 'total'=>0);
	
	if (!$billno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	
 
	$sql = "update t_invoice set express_name='$express_name',express_no='$express_no',fill=1 where billno='$billno'";
	if (!$mysqli->query($sql))
	{       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
	
}



//Content review  内容审核

if ($a == 'getreviewlist') {
	$begindate = @$_GET['begin']? checkInput($_GET['begin']):'';		//开始时间
	$enddate = @$_GET['begin']? checkInput($_GET['end']): '';		//结束时间
	$output = array('list'=>array(), 'total'=>0);
 
	if ($begindate==''){
		$query="SELECT SQL_CALC_FOUND_ROWS * FROM `t_contentreview` where checked=0 ";
	} else {
		$query="SELECT SQL_CALC_FOUND_ROWS * FROM `t_contentreview` WHERE checked=0  and  billdate between '$begindate' and '$enddate'";
	}
	
	$query .= " ORDER BY id DESC".$paging;
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
	$output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功','total'=>"$total", 'status'=>'0')));
	
}


//处理审核
if ($a == 'doreview') {
	$billno = checkInput($_GET['billno']);
	$c_username = checkInput($_GET['c_username']);
	$c_userno = checkInput($_GET['c_userno']);
	$checked =checkInput($_GET['checked']); 		//操作   0.删除 1.下架 2.通过
	$output = array('list'=>array(), 'total'=>0);
	
	if (!$billno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	
 
	$sql = "update t_contentreview set c_username='$c_username',c_userno='$c_userno',checked='$checked' where billno='$billno'";
	if (!$mysqli->query($sql))
	{       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
	
}




//货真真相关

//取得待审新用户列表
if ($a=='getwholesalelist')
{
	$allow= checkInput($_GET['allow']);
	$output = array('list'=>array(), 'total'=>0);
 
	if ($allow=='0'){   //还没有审核的用户
		$query="SELECT SQL_CALC_FOUND_ROWS billno,username,userno,regdate,company,company_linkman,province,city,town,street,allow FROM `team_salesman`  WHERE app=1 and allow=0";
	} else {	//已通过的用户
		$query="SELECT SQL_CALC_FOUND_ROWS billno,username,userno,regdate,company,company_linkman,province,city,town,street,allow FROM `team_salesman`  WHERE app=1 and allow=1";
	}
	
	
	$query .= " ORDER BY id DESC".$paging;
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
	$output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功','total'=>"$total", 'status'=>'0')));
}


//处理货真真相关新用户审核
if ($a == 'dowholesale') {
	$userno= checkInput($_GET['userno']);		//操作人员编号
	$billno = checkInput($_GET['billno']);
	$allow = checkInput($_GET['allow']);		//1 通过， 2 不能过
	$output = array('list'=>array(), 'total'=>0);
	
	if (!$billno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	
 
	$sql = "update team_salesman set allow=$allow,remark='$userno' where billno='$billno'";
	if (!$mysqli->query($sql))
	{       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
	
}
