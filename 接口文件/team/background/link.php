<?php
// 供应链接口功能单元

// 子分类 名片
if ($a == 'get_supply') {
  $cid = checkInput($_GET['cid']);  
	$supptype = checkInput($_GET['supptype']);	

	$output = array('list'=>array(), 'total'=>0);

	if (!$supptype) {
		exit(JSON(array('data'=>$output, 'msg'=>'没有数据', 'status'=>'1')));
	}

    $query = "SELECT sql_calc_found_rows billno,sbillno,(SELECT image from team_salesman where billno=sbillno) AS image,usename AS name,
    phone AS userno,tel,phone,job,mail,compname AS company,address AS companyaddress,qq,fax from `link_dict`
    WHERE enable=1 AND typeno='$cid'";

  if ($supptype != '全部') {
    $query .= " AND comtype LIKE '%$supptype%'";
  }
  $query .= " ORDER BY id DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 子分类类型
if ($a == 'get_supply_type') {
	$cid = checkInput($_GET['cid']);	
	$output = array('list'=>array(), 'total'=>0);
	$query = "SELECT sql_calc_found_rows typename,billno from `link_supply` WHERE `typeno`='$cid'".$paging;
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
  $output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 上游，下游 返回类型,选中
if ($a == 'get_up_type') {
	$sbillno = checkInput($_GET['sbillno']);	
	$statu = checkInput($_GET['statu']);	
	$output = array('list'=>array(), 'arry'=>array());

	$query = "SELECT typebno, sname, typename from `link_upreach` WHERE sbillno='$sbillno' AND `statu`='$statu'";
	$result = $mysqli->query($query) or die($mysqli->error);
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
	$query = "SELECT scid, sname, count(*) AS num from `link_upreach` WHERE sbillno='$sbillno' AND `statu`='$statu' group by sname";
	$result = $mysqli->query($query) or die($mysqli->error);
	 while ($row = $result->fetch_assoc()) {
		array_push($output['arry'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 上游，下游 配置
if ($a == 'set_up_sype') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $admincode = checkInput($obj['admincode']);
  $sbillno = checkInput($obj['sbillno']);
  $typebno = checkInput($obj['typebno']);
  $typename = checkInput($obj['typename']);
  $statu = checkInput($obj['statu']); // 0上游，1下，2同行, 3 自已公司类型
  $flag = checkInput($obj['flag']); // 0 点击选中， 1不选中
  $selected = checkInput($obj['selected']);
  $sname = checkInput($obj['sname']);
  $msg = $flag == 0 ? '添加' : '取消';
  
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == 0) {
	  $query = "SELECT id FROM link_upreach WHERE sbillno='$sbillno' AND typebno='$typebno' AND statu='$statu' LIMIT 1";
	  $result = $mysqli->query($query);
	  $userInfo = $result->fetch_assoc();
	  if (mysqli_num_rows($result) > 0) {
	    exit(JSON(array('data'=>'', 'msg'=>'该类型已存在', 'status'=>'1')));
	  }
	  if ($statu == '3') {
		 // 检查数量是否超过 5 条
			  $query = "SELECT count(*) AS num FROM link_upreach WHERE sbillno='$sbillno' AND statu='$statu'";
			  $result = $mysqli->query($query);
			  $userInfo = $result->fetch_assoc();
			  if ($userInfo['num'] >= 5) {
			    exit(JSON(array('data'=>'', 'msg'=>'公司类型设置达到上限', 'status'=>'1')));
			  }
	  }

	   $sql = "INSERT INTO `link_upreach` SET billno='$_billno',sbillno='$sbillno',`admincode`='$admincode',`admin`='$admin',`scid`='$selected',`sname`='$sname',`typebno`='$typebno',`typename`='$typename',`statu`='$statu'";
	   if (!$mysqli->query($sql)) {
	     exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
	   } 
	   exit(JSON(array('data'=>'', 'msg'=>'添加成功', 'status'=>'0')));
  } else {
     $sql = "DELETE FROM link_upreach WHERE sbillno='$sbillno' AND typebno='$typebno' AND statu='$statu'";
      if (!$mysqli->query($sql)) {
	     exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
	   } 
	   exit(JSON(array('data'=>'', 'msg'=>'取消成功', 'status'=>'0')));
  }
}

// 上游，下游 获取类型, 主页
if ($a == 'get_upperreach') {
	$admin = checkInput($_GET['admin']);	
  $sbillno = checkInput($_GET['sbillno']);  
	$statu = checkInput($_GET['statu']);	
	$output = array('list'=>array());

	$query = "SELECT typename from `link_upreach` WHERE sbillno='$sbillno' AND `statu`='$statu' group by typename";
	$result = $mysqli->query($query) or die($mysqli->error);
	 while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 上游，下游 主页 名片数据
if ($a == 'get_upperreach_data') {
	$admin = checkInput($_GET['admin']);
  $sbillno = checkInput($_GET['sbillno']);
	$statu = checkInput($_GET['statu']);
	$supptype = checkInput($_GET['supptype']);	

	$output = array('list'=>array(), 'total'=>0);

	if (!$supptype) {
		exit(JSON(array('data'=>$output, 'msg'=>'没有数据', 'status'=>'1')));
	}

   $query = "SELECT sql_calc_found_rows billno,sbillno,(SELECT image from team_salesman where billno=link_dict.sbillno) AS image,usename AS name,phone AS userno,tel,phone,job,mail,compname AS company,address AS companyaddress,qq,fax,
    (select billno from `link_friend` where sbillno='$sbillno' AND favbno=link_dict.billno AND statu='$statu') AS cbillno from `link_dict`
    WHERE enable=1 AND comtype LIKE '%$supptype%' AND sbillno !='$sbillno'";

  // $query = "SELECT sql_calc_found_rows billno,(SELECT image from team_salesman where billno=sbillno) AS image,usename AS name,
  //   phone AS userno,tel,phone,job,mail,compname AS company,address AS companyaddress,qq,fax from `link_dict`
  //   WHERE enable=1 AND comtype LIKE '%$supptype%' AND sbillno !='$sbillno'";

  $query .= " ORDER BY sbillno DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

 // 获取 链友数据
 if ($a == 'get_cfriend') {
 	$admin = checkInput($_GET['admin']);
  $sbillno = checkInput($_GET['sbillno']);
	$statu = checkInput($_GET['statu']); // 0上游 1下游 2同行
	$output = array('list'=>array(), 'total'=>0);

	if (!$sbillno) {
		exit(JSON(array('data'=>$output, 'msg'=>'没有数据', 'status'=>'1')));
	}

	$query = "SELECT sql_calc_found_rows a.billno,(SELECT image from team_salesman where billno=a.sbillno) AS image,a.usename AS name,a.phone AS userno,a.tel,a.phone,a.job,a.mail,a.compname AS company,a.address AS companyaddress,a.qq,a.fax,b.billno AS cbillno from `link_dict` a
	  LEFT JOIN `link_friend` b ON a.billno=b.favbno WHERE b.sbillno='$sbillno' AND b.statu='$statu'";
  $query .= " ORDER BY a.billno DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 关注 链友
if ($a == 'set_cfriend') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $sbillno = checkInput($obj['sbillno']);
  $admincode = checkInput($obj['admincode']);
  $favbno = checkInput($obj['favbno']);
  $statu = checkInput($obj['statu']);
  $cbillno = checkInput($obj['cbillno']);
 
  if ($cbillno) {
	  $sql = "DELETE FROM `link_friend` where billno='$cbillno' LIMIT 1";
	   if (!$mysqli->query($sql)) {
	     exit(JSON(array('data'=>'', 'msg'=>'取消关注失败', 'status'=>'1')));
	   } 
	   exit(JSON(array('data'=>'', 'msg'=>'取消关注成功', 'status'=>'0')));
	 } else {
		  $sql = "INSERT INTO `link_friend` SET billno='$_billno',`admincode`='$admincode',sbillno='$sbillno',`admin`='$admin',`favbno`='$favbno',`statu`='$statu'";
		   if (!$mysqli->query($sql)) {
		     exit(JSON(array('data'=>'', 'msg'=>'关注失败', 'status'=>'1')));
		   } 
		   exit(JSON(array('data'=>'', 'msg'=>'关注成功', 'status'=>'0')));
	 }
} 

// 设置链圈
if ($a == 'set_chain_data') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $admin = $obj['admin'];
  $sbillno = $obj['sbillno'];
  $userno = $obj['userno'];
  $username = $obj['username'];
  $title = $obj['title'];

  $sql = "INSERT INTO `link_chain` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',sbillno='$sbillno',
     `admin`='$admin',`title`='$title',image1='$image1',image2='$image2',image3='$image3'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'发送失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'发送成功', 'status'=>'0');
  }
  exit(JSON($output));
}
// 获取链圈
if ($a == 'get_chain_data') {
	$output = array('list'=>array(), 'total'=>0);

	$query = "SELECT sql_calc_found_rows a.*,(SELECT image from team_salesman where billno=b.sbillno) AS image,b.usename AS name,b.compname AS company,(select count(*) from `link_apprse` where chainbno=a.billno) AS anum from `link_chain` a LEFT JOIN `link_dict` b ON a.sbillno=b.sbillno";
  $query .= " ORDER BY a.billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 获取链圈 评论
if ($a == 'get_chain_apprse') {
	$chainbno = checkInput($_GET['chainbno']);
	$output = array('list'=>array(), 'total'=>0);

	if (!$chainbno) {
		exit(JSON(array('data'=>$output, 'msg'=>'获取不到billno', 'status'=>'1')));
	}

	$query = "SELECT sql_calc_found_rows a.*,(SELECT image from team_salesman where billno=b.sbillno) AS image,b.usename AS name,b.compname AS company from `link_apprse` a LEFT JOIN `link_dict` b ON a.sbillno=b.sbillno WHERE a.chainbno='$chainbno'";
  $query .= " ORDER BY a.billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 链圈 分享, 显示网页
if ($a == 'get_chain_apprse_web') {
	$chainbno = checkInput($_GET['chainbno']);
	$output = array('head'=>array(), 'list'=>array(), 'total'=>0);

	if (!$chainbno) {
		exit(JSON(array('data'=>$output, 'msg'=>'获取不到billno', 'status'=>'1')));
	}

	$query = "SELECT sql_calc_found_rows a.*,(SELECT image from team_salesman where billno=b.sbillno) AS image,b.usename AS name,b.compname AS company from `link_chain` a LEFT JOIN `link_dict` b ON a.sbillno=b.sbillno WHERE a.billno='$chainbno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['head'], $row);
  }
  // 评论
	$query = "SELECT sql_calc_found_rows a.*,(SELECT image from team_salesman where billno=b.sbillno) AS image,b.usename AS name,b.compname AS company from `link_apprse` a LEFT JOIN `link_dict` b ON a.sbillno=b.sbillno WHERE a.chainbno='$chainbno'";
  $query .= " ORDER BY a.billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 链圈 评论
if ($a == 'set_chain_apprse') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $chainbno = checkInput($obj['chainbno']);
  $sbillno = checkInput($obj['sbillno']);
  $username = checkInput($obj['username']);
  $userno = checkInput($obj['userno']);
  $title = checkInput($obj['title']);

  $sql = "INSERT INTO `link_apprse` SET billno='$_billno',`billdate`=NOW(),`sbillno`='$sbillno',`username`='$username',`chainbno`='$chainbno',`userno`='$userno',`title`='$title'";
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>'评论失败', 'status'=>'1')));
   } 
   exit(JSON(array('data'=>'', 'msg'=>'评论成功', 'status'=>'0')));
}
// 点赞
if ($a == 'set_chain_num') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $chainbno = checkInput($obj['chainbno']);

  $sql = "UPDATE `link_chain` SET `num`=num+1 WHERE billno='$chainbno'";
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>'点赞失败', 'status'=>'1')));
   } 
   exit(JSON(array('data'=>'', 'msg'=>'点赞成功', 'status'=>'0')));
}
// 删除链圈 信息
if ($a == 'del_chain_mess') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $flat = checkInput($obj['flat']); // 0链圈，  1链圈里的评论
  $chainbno = checkInput($obj['chainbno']);

  if ($flat == 0) {
  	$sql = "DELETE FROM `link_apprse` WHERE chainbno='$chainbno'";
    $mysqli->query($sql);
    $sql = "DELETE FROM `link_chain` WHERE billno='$chainbno' LIMIT 1";
  } else {
  	$sql = "DELETE FROM `link_apprse` WHERE billno='$chainbno' LIMIT 1";
  }
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
   } 
   exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 设置供需
if ($a == 'set_sdemand') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }
  $admin = $obj['admin'];
  $sbillno = $obj['sbillno'];
  $userno = $obj['userno'];
  $username = $obj['username'];
  $image = $obj['image'];
  $title = $obj['title'];

  $sql = "INSERT INTO `link_demand` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',sbillno='$sbillno',
     `admin`='$admin',`image`='$image',`title`='$title'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'发送失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'发送成功', 'status'=>'0');
  }
  exit(JSON($output));
} 
// 获取供需
if ($a == 'get_sdemand') {
	$output = array('list'=>array(), 'total'=>0);

	$query = "SELECT sql_calc_found_rows *,(select COUNT(*) from link_quote where dembno = `link_demand`.billno ) AS cnum from `link_demand`";
  $query .= " ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 供货报价
if ($a == 'get_sdmydata') {
	$admin = checkInput($_GET['admin']);	
  $sbillno = checkInput($_GET['sbillno']);
	$output = array('list'=>array(), 'total'=>0);
	if (!$admin) {
		exit(JSON(array('data'=>$output, 'msg'=>'没有数据', 'status'=>'1')));
	}

	$query = "SELECT sql_calc_found_rows *,(select COUNT(*) from link_quote where dembno = `link_demand`.billno ) AS cnum from `link_demand`
	          WHERE billno IN (SELECT dembno FROM link_quote where `sbillno`='$sbillno')";
  $query .= " ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 获取供需报价
if ($a == 'get_quote') {
	$dembno = checkInput($_GET['dembno']);
	$admin = checkInput($_GET['admin']);
  $sbillno = checkInput($_GET['sbillno']);
	$output = array('list'=>array(), 'total'=>0);
	if (!$dembno) {
		exit(JSON(array('data'=>$output, 'msg'=>'没有数据', 'status'=>'1')));
	}
 
	$query = "SELECT sql_calc_found_rows a.*,b.image from `link_quote` a LEFT JOIN `team_salesman` b ON a.sbillno=b.billno where a.dembno='$dembno'";
	if ($sbillno) {
		$query .= " AND a.sbillno='$sbillno'";
	}
  $query .= " ORDER BY a.rmb ASC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 
// 进行报价
if ($a == 'set_quote') {
	$input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $dembno = $obj['dembno'];
  // $admin = $obj['admin'];
  $sbillno = $obj['sbillno'];
  $userno = $obj['userno'];
  $username = $obj['username'];
  $rmb = $obj['rmb'];

  // 只能进行一次报价
  $query = "SELECT id FROM link_quote where dembno='$dembno' AND `sbillno`='$sbillno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if (mysqli_num_rows($result) > 0){
		exit(JSON(array('data'=>'', 'msg'=>'你已经报价了！', 'status'=>'1')));
	}

  $sql = "INSERT INTO `link_quote` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',sbillno='$sbillno',
     `admin`='$admin',`dembno`='$dembno',`rmb`='$rmb'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'报价失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'报价成功', 'status'=>'0');
  }
  exit(JSON($output));
} 

// 供应链设置

if ($a == 'getsetinfo') {
  $sbillno = checkInput($_GET['sbillno']); // saleman billno
  $admin = checkInput($_GET['admin']);
  $output = array('list'=>array());
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  
  $query2 = "SELECT billno,compname,tel,fax,mail,usename,job,phone,qq,address,enable,comtype,typeno FROM `link_dict` WHERE sbillno='$sbillno'";
  $result2 = $mysqli->query($query2) or die($mysqli->error);
  $data = $result2->fetch_assoc();
  if ($data) {
    array_push($output['list'], $data);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 设置 - 个人公司类型
if ($a == 'getcomtype') {
  $sbillno = checkInput($_GET['sbillno']); // saleman billno
  $output = array('mtype'=>array());
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT comtype,typeno from `link_dict` WHERE `sbillno`='$sbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['mtype'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 提交供应链信息
if ($a == 'setsetinfo') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $billno = $obj['billno'];
  $sbillno = $obj['sbillno'];
  $compname = $obj['compname'];
  $tel = $obj['tel'];
  $fax = $obj['fax'];
  $username = $obj['username'];
  $job = $obj['job'];
  $phone = $obj['phone'];
  $qq = $obj['qq'];
  $address = $obj['address'];
  $enable = $obj['enable'];
  $mail = $obj['mail'];
  $comtype = $obj['comtype'];
  $typeno = $obj['typeno'];
  $typename = $obj['typename'];

  if ($billno) {
    $sql = "UPDATE link_dict SET compname='$compname',tel='$tel',fax='$fax',usename='$username',job='$job',
          phone='$phone',qq='$qq',address='$address',enable='$enable',mail='$mail',comtype='$comtype',typeno='$typeno',typename='$typename' WHERE billno = '$billno'";
  } else {
     // 根据企业名
     $sql = "INSERT INTO link_dict SET billno='$_billno',sbillno='$sbillno',compname='$compname',tel='$tel',fax='$fax',usename='$username',job='$job',
          phone='$phone',qq='$qq',address='$address',enable='$enable',mail='$mail',comtype='$comtype',typeno='$typeno',typename='$typename'";
  }
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'保存失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'保存成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 申请认领
if ($a == 'set_claim') {
  $sbillno = checkInput($_GET['sbillno']);  
  $nbillno = checkInput($_GET['nbillno']);

  $username = '';
 $userno = '';
 $company = '';
 $tel = '';
 $companyaddress = '';

  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if (!$nbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $qty = "SELECT tstatus from `link_claim` WHERE `sbillno`='$sbillno'";
  $rlt = $mysqli->query($qty) or die($mysqli->error);
  $data = $rlt->fetch_assoc();
   if ($data['tstatus'] == '0') {
     exit(JSON(array('data'=>'', 'msg'=>'审核中,请等待', 'status'=>'1')));
  }

  // 被认领人
  $query = "SELECT sbillno from `link_dict` WHERE billno='$nbillno' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
   if ($row['sbillno']) {
     exit(JSON(array('data'=>'', 'msg'=>'申请失败,该企业已被认领了', 'status'=>'1')));
  }
  // 申请人
  $query2 = "SELECT billno from `link_dict` WHERE sbillno='$sbillno' LIMIT 1";
  $result2 = $mysqli->query($query2) or die($mysqli->error);
  $row2 = $result2->fetch_assoc();
   if ($row2) {
     exit(JSON(array('data'=>'', 'msg'=>'申请失败,你已经认领了其它企业', 'status'=>'1')));
  }
  // 申请人信息
  $query3 = "SELECT username,userno,company,tel,companyaddress from `team_salesman` WHERE billno='$sbillno' LIMIT 1";
  $result3 = $mysqli->query($query3) or die($mysqli->error);
  $row3 = $result3->fetch_assoc();
   if ($row3) {
     $username = $row3['username'];
     $userno = $row3['userno'];
     $company = $row3['company'];
     $tel = $row3['tel'];
     $companyaddress = $row3['companyaddress'];
  }

 $sql = "INSERT INTO link_claim SET billdate=NOW(),billno='$_billno',sbillno='$sbillno',username='$username',userno='$userno',compname='$company',
          tel='$tel',address='$companyaddress',nbillno='$nbillno'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'申请失败', 'status'=>'1');
  }  
   
 exit(JSON(array('data'=>'', 'msg'=>'申请成功', 'status'=>'0')));
} 

// 审核状态 (= 未用到 =)
if ($a == 'get_claim') {
  $sbillno = checkInput($_GET['sbillno']); // saleman billno
  $output = array('list'=>array());
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  $query = "SELECT comtype,typeno from `link_claim` WHERE `sbillno`='$sbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}



