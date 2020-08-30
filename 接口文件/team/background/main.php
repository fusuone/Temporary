<?php
// 主应用

$serverAssetsUrl = $serverConfig->getAssetsUrl();

// 地理区域
if ($a == 'geographic') {
  $province = file_get_contents('../utils/geographic/province.json');
  $city = file_get_contents('../utils/geographic/city.json');
  $_province = json_decode($province, true);
  $_city = json_decode($city, true);
  exit(JSON(array('data'=>array('province'=>$_province,'city'=>$_city), 'msg'=>'获取成功', 'status'=>'0')));
}

// 客户 查询
if ($a == 'getcustomer') {
  $uid = checkInput($_GET['uid']); // userno
  $sid = checkInput($_GET['sid']); // 类别
  $admin = checkInput($_GET['admin']);
  $ispana = checkInput($_GET['ispana']) || '0'; // 客户类型 0客户 1合作伙伴
  $custname = checkInput($_GET['custname']); // 客户名称
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $mkey = checkInput($_GET['mkey']);

  $nowdate = checkInput($_GET['nowdate']); // 进货列表，筛选 用
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'uid不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM team_customer WHERE (`admin`='$admin' AND (saler='$uid' OR `status`='1')) AND `status`>-1 AND ispana='$ispana'";

  if ($begindate) {
    $query .=" AND billdate between '$begindate' AND '$enddate'";
  }
  if ($custname) {
    $query .=" AND title LIKE '%$custname%'";
  }
  if ($sid != '') {
    $query .=" AND `typeno`='$sid'";
  }
  if ($mkey == '1') { // 客访
    $query .= " ORDER BY refreshdate DESC".$paging;
  } else if ($mkey == '0'){ // 名称
    $query .= " ORDER BY title DESC".$paging;
  } else {  // 最新创建
    $query .= " ORDER BY billdate DESC".$paging;
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 客户 新增
if ($a == 'setcustomer') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image = checkInput($obj['image']);
  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);
  $bno = checkInput($obj['billno']);
  $uid = checkInput($obj['uid']);
  $username = checkInput($obj['username']);
  $admin = checkInput($obj['admin']);
  $typename = checkInput($obj['typename']); // 分类名
  $typeno = checkInput($obj['typeno']);	// 分类编号
  $ispana = checkInput($obj['ispana']);	// 0普通客户 1合作伙伴
  $title = checkInput($obj['title']);	// 客户名称
  $linkman = checkInput($obj['linkman']);	// 联系人
  $tel = checkInput($obj['tel']);	// 电话
  $phone = checkInput($obj['phone']);	// 手机
  $address = checkInput($obj['address']); // 地址
  $scale = checkInput($obj['scale']); // 规模
  $capacity = checkInput($obj['capacity']); // 面积
  $status = checkInput($obj['status']); // 0个人可见 1团队公开 -1 删除
  $latitude = checkInput($obj['latitude']); // 纬度
  $longitude = checkInput($obj['longitude']); // 经度
  $fax = checkInput($obj['fax']); // 传真
  $zipcode = checkInput($obj['zipcode']); // 邮编
  $email = checkInput($obj['email']); // email
  $taxno = checkInput($obj['taxno']); // 营业执照
  $legal_representative = checkInput($obj['legal_representative']); // 法定代表人
  $cardno = checkInput($obj['cardno']); // 银行账号
  $bank = checkInput($obj['bank']); // 开户行
  $invoice_address = checkInput($obj['invoice_address']); // 发票地址
  $remark = checkInput($obj['remark']); // 备注

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$title) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 还要判断客户是否同名

  if ($bno) {
    $sql = "UPDATE team_customer SET typename='$typename',typeno='$typeno',ispana='$ispana',title='$title',linkman='$linkman',tel='$tel',phone='$phone',`address`='$address',scale='$scale',capacity='$capacity',`status`='$status',latitude='$latitude',longitude='$longitude',fax='$fax',zipcode='$zipcode',email='$email',taxno='$taxno',bank='$bank',invoice_address='$invoice_address',remark='$remark',`image`='$image',image1='$image1',image2='$image2',image3='$image3' WHERE billno='$bno'";
  } else {
    $query = "SELECT billno,saler,TIMESTAMPDIFF(MONTH,refreshdate,NOW()) AS mtime FROM team_customer WHERE phone='$phone' AND admin='$admin' AND status >'-1' LIMIT 1";
  
    $result = $mysqli->query($query) or die($mysqli->error);
    $data = $result->fetch_assoc();
    if ($data) {
      // 加入团队的人
      if ($admin) {
        $mtime = $data['mtime'];
        $mb = $data['billno'];
        $saler = $data['saler'];
        if ($saler == $uid) {
          exit(JSON(array('data'=>'', 'msg'=>'你已经添加了该客户!', 'status'=>'1')));
        }
        if ($mtime > 1) { // 大于一个月
          $sql = "UPDATE team_customer SET saler='$uid',refreshdate=NOW() WHERE billno='$mb'";
          if (!$mysqli->query($sql)) {
            exit(JSON(array('data'=>'', 'msg'=>'新增失败', 'status'=>'1')));
          }
            exit(JSON(array('data'=>'', 'msg'=>'新增成功', 'status'=>'0')));
        } else {
            exit(JSON(array('data'=>'', 'msg'=>'该客户已存在团队中', 'status'=>'1')));
        }
      } else { // 未加入团队的人
          $query = "SELECT billno FROM team_customer WHERE phone='$phone' AND saler='$uid' AND status >'-1' LIMIT 1";
          $result = $mysqli->query($query) or die($mysqli->error);
          $data = $result->fetch_assoc();
          if ($data) {
            exit(JSON(array('data'=>'', 'msg'=>'你已经添加过该客户', 'status'=>'1')));
          }
        }
    }

    $sql = "INSERT INTO team_customer SET billno='$_billno',saler='$uid',`admin`='$admin',billdate=NOW(),typename='$typename',typeno='$typeno',ispana='$ispana',title='$title',
           linkman='$linkman',refreshdate=NOW(),tel='$tel',phone='$phone',`address`='$address',scale='$scale',capacity='$capacity',`status`='$status',latitude='$latitude',
           longitude='$longitude',fax='$fax',zipcode='$zipcode',email='$email',taxno='$taxno',bank='$bank',invoice_address='$invoice_address',remark='$remark',`image`='$image',
           image1='$image1',image2='$image2',image3='$image3',sname='$username'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 客户 新增（扫码添加）
if ($a == 'addcustqr') {
  $billno = checkInput($_GET['billno']);
  $admin = checkInput($_GET['admin']);
  $userno = checkInput($_GET['userno']);
  $custbno = checkInput($_GET['custbno']);

  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
   if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'userno为空', 'status'=>'1')));
  }
  if (!$custbno) {
    exit(JSON(array('data'=>'', 'msg'=>'客户billno为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM team_salesman WHERE billno='$custbno' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $custInfo = $result->fetch_assoc();
  if (!$custInfo) {
    exit(JSON(array('data'=>'', 'msg'=>'用户不存在', 'status'=>'1')));
  }
  
  $cusername=$custInfo['username'];
  $cuserno=$custInfo['userno'];
  $ccompany=$custInfo['company'];
  $caddress=$custInfo['companyaddress'];
  $cscale=$custInfo['company_scale'];
  $ctel=$custInfo['tel'];
  $cmail=$custInfo['mail'];
  $cimage=$custInfo['image'];
  $cimage1=$custInfo['image1'];
  $cimage2=$custInfo['image2'];
  $cimage3=$custInfo['image3'];

  $query = "SELECT id FROM team_customer WHERE phone='$cuserno' AND saler='$userno' AND admin='$admin' AND status>'-1' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $userInfo = $result->fetch_assoc();
  if (!$userInfo) {

    $sql = "INSERT INTO team_customer SET billno='$_billno',saler='$userno',`admin`='$admin',billdate=NOW(),typename='新客户',typeno='-1',
    ispana='0',title='$ccompany',linkman='$cusername',tel='$ctel',phone='$cuserno',`address`='$caddress',scale='$cscale',
    capacity='',`status`='0',latitude='',longitude='',fax='',zipcode='',email='$cmail',
    taxno='',bank='',invoice_address='',remark='',`image`='$cimage',image1='$cimage1',image2='$cimage2',image3='$cimage3'";
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
    }
      exit(JSON(array('data'=>'', 'msg'=>'添加成功', 'status'=>'0')));
    }
    exit(JSON(array('data'=>$output, 'msg'=>'该用户已是你的客户,不能重复添加!', 'status'=>'0')));
}

// 客户 删除
if ($a == 'delcustomer') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "UPDATE team_customer SET `status`='-1' WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 客户类型 获取
if ($a == 'getcustomertype') {
  $uid = checkInput($_GET['uid']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM team_type WHERE userno='$uid'";
  $result = $mysqli->query($query) or die($mysqli->error);
  
  $output['total'] = $result->num_rows;
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 客户类别 删除
if ($a == 'delcustomertype') {
  $bno = checkInput($_GET['bno']);

  if ($bno == '') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM team_type WHERE billno=$bno";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'没有找到该类别', 'status'=>'1')));
  }
  $data = $result->fetch_assoc();
  $typename = $data['typename'];

  if ($typename=="新客户" or $typename=="已收款" or $typename=="已完成" or $typename=="未分类") {
    exit(JSON(array('data'=>'', 'msg'=>'系统分类，不能删除', 'status'=>'1')));
  }

  $sql = "DELETE FROM team_type WHERE billno='$bno'";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'删除失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'删除成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 客户类别 新增、编辑
if ($a == 'setcustomertype') {
  $bno = checkInput($_GET['bno']);
  $userno = checkInput($_GET['userno']);
  $typename = checkInput($_GET['typename']);
  $flag = checkInput($_GET['flag']); // 0新增 1编辑

  if ($typename == '' || $userno == '') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if ($typename=="新客户" or $typename=="已收款" or $typename=="已完成" or $typename=="未分类") {
    exit(JSON(array('data'=>'', 'msg'=>'该名称已存在', 'status'=>'1')));
  }
  if ($flag == '1') {
    if (!$bno) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
  }

  $query = "SELECT * FROM team_type WHERE typename='$typename' AND userno='$userno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    exit(JSON(array('data'=>'', 'msg'=>'该名称已存在', 'status'=>'1')));
  }

  if ($flag == '0') {
    $sql = "INSERT INTO team_type (billno,typename,userno) VALUES ('$_billno','$typename','$userno')";
    if (!$mysqli->query($sql)) {
      $output = array('data'=>'', 'msg'=>'添加失败', 'status'=>'1');
    } else {
      $output = array('data'=>'', 'msg'=>'添加成功', 'status'=>'0');
    }
    exit(JSON($output));
  } else {
    $sql = "UPDATE team_type SET typename='$typename' WHERE billno=$bno";
    if (!$mysqli->query($sql)) {
      $output = array('data'=>'', 'msg'=>'修改失败', 'status'=>'1');
    } else {
      $output = array('data'=>'', 'msg'=>'修改成功', 'status'=>'0');
    }
    exit(JSON($output));
  }
}

// 客访记录 查询
if ($a == 'getvisitrecord') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']);
  $page = checkInput($_GET['page']);
  $output = array('list'=>array(), 'total'=>0);
  
  $page = ($page-1) * 10;

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `team_visit` WHERE `admin`='$admin' AND customerno='$customerno' ORDER BY billdate DESC LIMIT $page,10";

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 客访记录 新增
if ($a == 'setvisitrecord') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image = checkInput($obj['image']);
  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);
  $admin = checkInput($obj['admin']);
  $customerno = checkInput($obj['customerno']);
  $customername = checkInput($obj['customername']);
  $salename = checkInput($obj['salename']);
  $saleno = checkInput($obj['saleno']);
  $type = checkInput($obj['type']);
  $title = checkInput($obj['title']);
  $address = checkInput($obj['address']);
  $latitude = checkInput($obj['latitude']);
  $longitude = checkInput($obj['longitude']);

  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$saleno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "INSERT INTO `team_visit` SET billno='$_billno',billdate=NOW(),customername='$customername',customerno='$customerno',salename='$salename',saleno='$saleno',`type`='$type',title='$title',`address`='$address',latitude='$latitude',longitude='$longitude',`admin`='$admin',image1='$image1',image2='$image2',image3='$image3',image='$image'";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 客户证书 查询
if ($a == 'getcertificate') {
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT id,`image`,`describe` FROM `team_certificate` WHERE customerno='$customerno' AND `admin`='$admin' ORDER BY billdate DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  
  $output['total'] = $result->num_rows;
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 客户证书 删除
if ($a == 'delcertificate') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "DELETE FROM team_certificate WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'删除失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'删除成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 客户证书 新增、编辑
if ($a == 'setcertificate') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image = checkInput($obj['image']);
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);
  $customerno = checkInput($obj['customerno']);
  $customername = checkInput($obj['customername']);
  $describe = checkInput($obj['describe']); // 描述内容
  $id = checkInput($obj['id']); // 有值则修改

  if ($id == '') {
    if (!$customerno) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
    if (!$userno) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
  }

  if ($id) {
    $sql = "UPDATE `team_certificate` SET `describe`='$describe',`image`='$image' WHERE id='$id' AND userno='$userno'";
  } else {
    $sql = "INSERT INTO `team_certificate` SET customername='$customername',customerno='$customerno',userno='$userno',`admin`='$admin',`describe`='$describe',`image`='$image'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 考勤 查询某一天的
if ($a == 'get_attendance_oneday') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $date = $_GET['date'] ? checkInput($_GET['date']) : date('Y-m-d');
  $starttime = $date.' 00:00:00';
  $endtime = $date.' 23:59:59';

  $output = array('list'=>array(), 'mtime'=>array());

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query1 = "SELECT startwork,endwork,company_lng,company_lat,distance FROM `team_salesman` WHERE userno='$admin'";
  $result1 = $mysqli->query($query1) or die($mysqli->error);
  $row1 = $result1->fetch_assoc();
  array_push($output['mtime'], $row1);

  $query = "SELECT * FROM `team_attendance` WHERE userno='$uid' AND billdate BETWEEN '$starttime' AND '$endtime'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  array_push($output['list'], $row);
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 考勤 签到、签退
if ($a == 'setattendance') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $username = checkInput($_GET['username']);
  $address = checkInput($_GET['address']);
  $lat = checkInput($_GET['lat']);
  $lng = checkInput($_GET['lng']);
  $flag = checkInput($_GET['flag']); // 0上班 1下班

  $weekarray=array("日","一","二","三","四","五","六");
  $week = "星期".$weekarray[date("w")];
  $starttime = date('Y-m-d').' 00:00:00';
  $endtime = date('Y-m-d').' 23:59:59';

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if ($flag !== '0' && $flag !== '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 先查询是否今天创建了数据
  $query = "SELECT * FROM `team_attendance` WHERE userno='$uid' AND `admin`='$admin' AND billdate BETWEEN '$starttime' AND '$endtime'";
  $rs = $mysqli->query($query) or die($mysqli->error);
  $row = $rs->fetch_assoc();
  $id = $row['id'];

  if ($row) {
    // 更新
    if ($flag == '0') {
      $sql = "UPDATE `team_attendance` SET startcheckwork='1',startworktime=NOW(),startworkaddress='$address',startworklatitude='$lat',startworklongitude='$lng' WHERE userno='$uid' AND id='$id'";
    } else {
      $sql = "UPDATE `team_attendance` SET endcheckwork='1',endworktime=NOW(),endworkaddress='$address',endworklatitude='$lat',endworklongitude='$lng' WHERE userno='$uid' AND id='$id'";
    }
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'服务器错误', 'status'=>'1')));
    }
    exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
  } else {
    // 写入
    if ($flag == '0') {
      $sql = "INSERT INTO `team_attendance` SET billdate=NOW(),username='$username',userno='$uid',admin='$admin',startcheckwork='1',week='$week',startworktime=NOW(),startworkaddress='$address',startworklatitude='$lat',startworklongitude='$lng'";
    } else {
      $sql = "INSERT INTO `team_attendance` SET billdate=NOW(),username='$username',userno='$uid',admin='$admin',endcheckwork='1',week='$week',endworktime=NOW(),endworkaddress='$address',endworklatitude='$lat',endworklongitude='$lng'";
    }

    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'服务器错误', 'status'=>'1')));
    }
    exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
  }
}

// 考勤 查询 正常，迟到，早退，缺席
if($a == 'getattendalldata'){
	$userno = checkInput($_GET['userno']); 
  $stime = checkInput($_GET['stime']);
  $etime = checkInput($_GET['etime']);
	$output = array(
    'attendchart'=>array()
  );
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	// 查询 考勤
	 $query = "SELECT A.c AS normal,B.c AS late,C.c AS leavelate,D.c AS absent FROM          
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='1' AND endcheckwork='1' 
  AND DATE_FORMAT(startworktime,'%H:%i:%S')<='$stime' AND DATE_FORMAT(endworktime,'%H:%i:%S')>='$etime' AND YEAR(billdate)=YEAR(NOW())) A,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='1' 
  AND DATE_FORMAT(startworktime,'%H:%i:%S')>'$stime' AND YEAR(billdate)=YEAR(NOW())) B,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND endcheckwork='1' 
  AND DATE_FORMAT(endworktime,'%H:%i:%S')<'$etime' AND YEAR(billdate)=YEAR(NOW())) C,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='0' AND endcheckwork='0' AND YEAR(billdate)=YEAR(NOW())) D";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row){
	array_push($output['attendchart'], $row);
 }
 
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 查询;？“的详情
if($a == 'getattenddetail'){
	$userno = checkInput($_GET['userno']); 
	$output = array('list'=>array());
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
  $query = "SELECT billdate,startcheckwork,startworktime,endcheckwork,endworktime,startworkaddress,endworkaddress FROM `team_attendance` WHERE userno='$userno'";
  $query .= " ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 考勤-请假记录
if ($a == 'getleave') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
      exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($flag == '0') { // 查询非无效的数据 大于-1
    $query = "SELECT sql_calc_found_rows id,billdate,billno,username,userno,admin,leavetype,`describe`,TIMESTAMPDIFF(day,starttime,endtime) AS mday,status FROM `team_leave` 
             WHERE (userno='$uid' OR (admin='$uid' AND `status`>0) OR userno IN (SELECT userno FROM `teams` WHERE division ='$uid'))";
    if ($keyword) {
        $query .=" AND (leavetype LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
    $query .=" ORDER BY billdate DESC".$paging;

  } else if ($flag == '1'){ // 查询已经提交的数据 大于0的
    $query = "SELECT sql_calc_found_rows id,billdate,billno,username,userno,admin,leavetype,`describe`,TIMESTAMPDIFF(day,starttime,endtime) AS mday,status FROM `team_leave` WHERE userno='$uid'  AND `status`>'0'";
     if ($keyword) {
        $query .=" AND (leavetype LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
    $query .=" ORDER BY billdate DESC".$paging;

  } else if($flag == '2') { // 查询审核通过，不通过的数据 大于1
    $query = "SELECT sql_calc_found_rows id,billdate,billno,username,userno,admin,leavetype,`describe`,TIMESTAMPDIFF(day,starttime,endtime) AS mday,status FROM `team_leave` WHERE ((userno='$uid' AND `status`>'1') OR (admin='$uid' AND `status`>1))";
     if ($keyword) {
        $query .=" AND (leavetype LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
     $query .=" ORDER BY billdate DESC".$paging;

  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 请假 提交
if ($a == 'setleave') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $admin = $obj['admin'];
  $userno = $obj['userno'];
  $username = $obj['username'];
  $leavetype = $obj['leavetype'];
  $starttime = $obj['starttime'];
  $endtime = $obj['endtime'];
  $describe = $obj['describe'];
  $mbillno = $obj['billno'];
  $flag = $obj['flag'];

  if ($flag =='1') { // 修改
    $sql = "UPDATE `team_leave` SET billdate=NOW(),leavetype='$leavetype',starttime='$starttime',endtime='$endtime',`describe`='$describe',
            image1='$image1',image2='$image2',image3='$image3' WHERE billno='$mbillno'";
  } else if ($flag =='0'){ // 新建
    $sql = "INSERT INTO `team_leave` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',`admin`='$admin',
    leavetype='$leavetype',starttime='$starttime',endtime='$endtime',`describe`='$describe',image1='$image1',image2='$image2',image3='$image3'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 请假详情
if ($a == 'getleavedetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT *,TIMESTAMPDIFF(day,starttime,endtime) AS mday FROM `team_leave` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'查询成功', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 请假-删除，修改，提交
if($a == 'updateleave') {
  $mbillno = checkInput($_GET['billno']);
  $flag = checkInput($_GET['flag']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($flag === '0' ){  // 更新提交
    $sql = "UPDATE `team_leave` SET status='1' WHERE billno='$mbillno'";
  } else if ($flag === '-1') { // 删除即无效
    $sql = "DELETE from `team_leave`  WHERE billno='$mbillno' limit 1";
  } else if($flag === '1') {
    $sql = "UPDATE `team_leave` SET status='2' WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'更新失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'更新成功', 'status'=>'0')));
  }
}

 // 请假 审核
if ($a == 'checkleave') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $mbillno = checkInput($obj['billno']);
  $reason = checkInput($obj['reason']);
  $captainsign = checkInput($obj['captainsign']);
  $bosssign = checkInput($obj['bosssign']);
  $flag = checkInput($obj['flag']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $signImages = array();
  $imagesPath = setImagePath(2, $serverAssetsUrl);
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[0]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[1]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 转换
  $signImagesToSql = implode(',', $signImages);

  if ($flag == '2') { // 审核通过
    $sql = "UPDATE `team_leave` SET `status`='2',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '3') { // 审核不通过
    $sql = "UPDATE `team_leave` SET `status`='3',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '4') { // 撤销
    $sql = "UPDATE `team_leave` SET `status`='0',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 设置上下班时间
if ($a == 'setworktime') {
  $uid = checkInput($_GET['uid']);
  $startwork = checkInput($_GET['startwork']);
  $endwork = checkInput($_GET['endwork']);
  $distance = checkInput($_GET['distance']);
  $company_lng = checkInput($_GET['company_lng']);
  $company_lat = checkInput($_GET['company_lat']);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

   $sql = "UPDATE `team_salesman` SET startwork='$startwork',endwork='$endwork',company_lng='$company_lng',company_lat='$company_lat',distance='$distance' WHERE userno='$uid'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'设置失败', 'status'=>'1');
  } else {
    $output = array('data'=>$sql, 'msg'=>'设置成功', 'status'=>'0');
  }
  exit(JSON($output));
}


// 工作计划 查询
if ($a == 'getworkplan') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

// exit(JSON(array('data'=>'', 'msg'=>'ppp', 'status'=>'0')));
  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') {  // 只查询自己本周数据
    $query = "SELECT sql_calc_found_rows a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE a.userno='$uid'
      AND YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) and `status`>'-1'";
    if ($keyword) {
        $query .=" AND (a.username LIKE '%$keyword%' OR a.workdate LIKE '%$keyword%')";
      }
      $query .=" ORDER BY a.workdate ASC".$paging;
  } else if ($flag == '1') { // 查询自己所有数据
    $query = "SELECT sql_calc_found_rows a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE  a.userno='$uid' and `status`>'-1'";
    if ($keyword) {
        $query .=" AND (a.username LIKE '%$keyword%' OR a.workdate LIKE '%$keyword%')";
      }
    $query .=" ORDER BY a.billdate DESC".$paging;
  } else if ( $flag == '2') { // 队员本周
    $query = "SELECT sql_calc_found_rows a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a LEFT JOIN  `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  WHERE (a.admin='$admin' OR a.admin='$uid') and YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) and `status`>'-1'";
   if ($keyword) {
        $query .=" AND (a.username LIKE '%$keyword%' OR a.workdate LIKE '%$keyword%')";
      }
    $query .=" ORDER BY a.workdate ASC".$paging;
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 工作计划 详情
if ($a == 'getworkplandetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_workplan` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'查询成功', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 工作计划 新增、编辑
if ($a == 'setworkplan') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $username = checkInput($_GET['username']);
  $date = checkInput($_GET['date']);
  $fplan = checkInput($_GET['fplan']);
  $splan = checkInput($_GET['splan']);
  $mbillno = checkInput($_GET['billno']); // 有值则修改

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if (!$mbillno) {
    $query = "SELECT * from team_workplan WHERE `admin`='$admin' AND userno='$uid' AND workdate='$date'";
    $result = $mysqli->query($query) or die($mysqli->error);
    if (mysqli_num_rows($result) > 0){
      exit(JSON(array('data'=>'', 'msg'=>'你已经有当天的计划', 'status'=>'2')));
    }
    $sql = "INSERT INTO `team_workplan` SET billdate=NOW(),billno='$_billno',`admin`='$admin',username='$username',userno='$uid',workdate='$date',fplan='$fplan',splan='$splan'";
  } else {  // 修改操作
    $sql = "UPDATE `team_workplan` SET username='$username',workdate='$date',fplan='$fplan',splan='$splan' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 工作计划 当天总结、删除
if ($a == 'setworkplansummary') {
  $mbillno = checkInput($_GET['billno']);
  $summary = checkInput($_GET['summary']);
  $flag = checkInput($_GET['flag']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '-1') { // 删除
    $sql = "UPDATE `team_workplan` SET `status`='-1' WHERE billno='$mbillno'";
  } else { // 提交当天总结
    $sql = "UPDATE `team_workplan` SET summary='$summary' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 工作任务 查询
if ($a == 'gettask') {
  $uid = checkInput($_GET['uid']);
  $flag = checkInput($_GET['flag']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') { // 看板上的列表	as_status -1删除，0暂存，1正在进行，4未完成，2、3完成
    $query = "SELECT sql_calc_found_rows *, CASE STATUS
            WHEN '0' THEN STATUS
            WHEN '1' THEN IF(NOW() > CONCAT(lastdate, ' 23:59:59'),4,STATUS)
            WHEN '2' THEN STATUS
            WHEN '3' THEN STATUS
            ELSE '' END AS as_status
            FROM `team_task` WHERE
              ((userno='$uid' AND `status` IN (0,1,3) ) OR
              (responphone='$uid' AND `status` IN (1,3)))";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
      $query .=" ORDER BY `status` ASC, billdate DESC".$paging;
  } else if ($flag == '1') { // 正在进行的任务
    $query = "SELECT sql_calc_found_rows *,status as as_status FROM `team_task` WHERE (userno='$uid' or responphone='$uid') AND `status`='1' AND NOW() <= CONCAT(lastdate, ' 23:59:59')";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
      $query .=" ORDER BY `status` asc, billdate DESC".$paging;

  } else if ($flag == '2') { // 完成的任务
    $query = "SELECT sql_calc_found_rows *,status as as_status FROM `team_task` WHERE  (userno='$uid' or responphone='$uid') AND (`status`='2' or `status`='3')";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
      $query .=" ORDER BY `status` asc,billdate DESC".$paging;

  } else if ($flag=='3') { // 未完成
    $query = "SELECT sql_calc_found_rows *,4 as as_status  FROM `team_task` WHERE (userno='$uid' OR responphone='$uid') AND `status`='1' AND NOW() > CONCAT(lastdate, ' 23:59:59')";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
      $query .=" ORDER BY `status` ASC, billdate DESC".$paging;

  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 工作任务 详情
if ($a == 'gettaskdetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT  *, CASE STATUS
          WHEN '0' THEN STATUS
          WHEN '1' THEN IF(NOW() > CONCAT(lastdate, ' 23:59:59'),4,STATUS)
          WHEN '2' THEN STATUS
          WHEN '3' THEN STATUS
          ELSE '' END AS as_status
          FROM `team_task` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'查询成功', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>$output, 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 工作任务 删除&提交
if ($a == 'updatetask') {
  $mbillno = checkInput($_GET['billno']);
  $flag = checkInput($_GET['flag']); // 0提交，-1删除

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if($flag == '0') {  // 更新提交
    $sql = "UPDATE `team_task` SET `status`='1' WHERE billno='$mbillno'";
  } else if ($flag == '1') { // 删除即无效
    $sql = "UPDATE `team_task` SET `status`='-1' WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'更新失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'更新成功', 'status'=>'0')));
  }
}

// 工作任务 审核
if ($a == 'checktask') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $mbillno = checkInput($obj['billno']);
  $captainsign = checkInput($obj['captainsign']);
  $bosssign = checkInput($obj['bosssign']);
  $mstatus = checkInput($obj['flag']); // 0撤销 2组长审核 3老板审核

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if ($mstatus != 0 && $mstatus != 2 && $mstatus != 3) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $signImages = array();
  $imagesPath = setImagePath(2, $serverAssetsUrl);
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[0]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[1]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 转换
  $signImagesToSql = implode(',', $signImages);

  $sql = "UPDATE `team_task` SET `status`='$mstatus',$signImagesToSql WHERE billno='$mbillno'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 工作任务 新增、编辑
if ($a == 'settask') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);
  $title = checkInput($obj['title']);
  $memo = checkInput($obj['memo']);
  $userno = checkInput($obj['userno']);
  $username = checkInput($obj['username']);
  $responname = checkInput($obj['responname']);
  $responphone = checkInput($obj['responphone']);
  $lastdate = checkInput($obj['lastdate']);
  $mbillno = checkInput($obj['billno']); // 存在表示修改

  if (!$mbillno) {
    $sql = "INSERT INTO `team_task` SET billno='$_billno',billdate=NOW(),memo='$memo',title='$title',responname='$responname',
        responphone='$responphone',`admin`='$admin',userno='$userno',username='$username',lastdate='$lastdate',`status`='0',image1='$image1',image2='$image2',image3='$image3'";
  } else {
    $sql = "UPDATE `team_task` SET title='$title',memo='$memo',responname='$responname',
        responphone='$responphone',username='$username',lastdate='$lastdate',image1='$image1',image2='$image2',image3='$image3' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 报销 查询
if ($a == 'getreimbur') {
  $uid = checkInput($_GET['uid']);
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
      exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($flag == '0') { // 查询非无效的数据 大于-1
    $query = "SELECT sql_calc_found_rows * FROM `team_reimbursement` WHERE ((userno='$uid' AND `status`>-1) OR (admin='$uid' AND `status`>0) OR userno IN (SELECT userno FROM `teams` WHERE division ='$uid'))";
    if ($keyword) {
        $query .=" AND (projectname LIKE '%$keyword%' OR type LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
    $query .=" ORDER BY billdate DESC".$paging;

  } else if ($flag == '1'){ // 查询已经提交的数据 大于0的
    $query = "SELECT sql_calc_found_rows * FROM `team_reimbursement` WHERE userno='$uid'  AND `status`>'0'";
     if ($keyword) {
        $query .=" AND (projectname LIKE '%$keyword%' OR type LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
    $query .=" ORDER BY billdate DESC".$paging;

  } else if($flag == '2') { // 查询审核通过，不通过的数据 大于1
    $query = "SELECT sql_calc_found_rows * FROM `team_reimbursement` WHERE ((userno='$uid' AND `status`>'1') OR (admin='$uid' AND `status`>1))";
     if ($keyword) {
        $query .=" AND (projectname LIKE '%$keyword%' OR type LIKE '%$keyword%' OR username LIKE '%$keyword%')";
      }
     $query .=" ORDER BY billdate DESC".$paging;

  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 报销详情
if ($a == 'getreimburdetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_reimbursement` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'查询成功', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 更改报销记录状态
if($a == 'updatereimbur') {
  $mbillno = checkInput($_GET['billno']);
  $flag = checkInput($_GET['flag']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($flag === '0' ){  // 更新提交
    $sql = "UPDATE `team_reimbursement` SET status='1' WHERE billno='$mbillno'";
  } else if ($flag === '-1') { // 删除即无效
    $sql = "UPDATE `team_reimbursement` SET status='-1' WHERE billno='$mbillno'";
  } else if($flag === '1') {
    $sql = "UPDATE `team_reimbursement` SET status='2' WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'更新失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'更新成功', 'status'=>'0')));
  }
}

// 报销 新增、编辑
if ($a == 'setreimbur') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $admin = $obj['admin'];
  $projectname = $obj['projectname'];
  $userno = $obj['userno'];
  $username = $obj['name'];
  $money = $obj['money'];
  $type = $obj['type'];
  $time = $obj['time'];
  $describe = $obj['describe'];
  $mbillno = $obj['billno'];
  $flag = $obj['flag'];

  if ($flag == '0') { // 新建
    $sql = "INSERT INTO `team_reimbursement` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',`admin`='$admin',projectname='$projectname',`money`='$money',`type`='$type',reimburdate='$time',`describe`='$describe',`status`='0',image1='$image1',image2='$image2',image3='$image3'";
  } else if ($flag == '1') { // 修改
    $sql = "UPDATE `team_reimbursement` SET billdate=NOW(),projectname='$projectname',`money`='$money',`type`='$type',reimburdate='$time',`describe`='$describe',image1='$image1',image2='$image2',image3='$image3' WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 报销 审核
if ($a == 'checkreimbur') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $mbillno = checkInput($obj['billno']);
  $reason = checkInput($obj['reason']);
  $captainsign = checkInput($obj['captainsign']);
  $bosssign = checkInput($obj['bosssign']);
  $flag = checkInput($obj['flag']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $signImages = array();
  $imagesPath = setImagePath(2, $serverAssetsUrl);
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[0]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[1]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 转换
  $signImagesToSql = implode(',', $signImages);

  if ($flag == '2') { // 审核通过
    $sql = "UPDATE `team_reimbursement` SET `status`='2',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '3') { // 审核不通过
    $sql = "UPDATE `team_reimbursement` SET `status`='3',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '4') { // 撤销
    $sql = "UPDATE `team_reimbursement` SET `status`='0',reason='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 我的管家 收支记录
if ($a == 'getfinance') {
  $admin = checkInput($_GET['admin']);
  $uid = checkInput($_GET['uid']);
  $flag = checkInput($_GET['flag']); // 0本周 1收入 2支出
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($flag == '0') {
    $query = "SELECT sql_calc_found_rows * FROM `team_finance` WHERE userno='$uid' AND `admin`='$admin' AND YEARWEEK(DATE_FORMAT(findate,'%Y-%m-%d')) = YEARWEEK(NOW()) ORDER BY billdate DESC".$paging;
  } else if($flag == '1') {
    $query = "SELECT sql_calc_found_rows * FROM `team_finance` WHERE userno='$uid' AND `admin`='$admin' AND `type` = '0' ORDER BY billdate DESC".$paging;
  } else if($flag == '2') {
    $query = "SELECT sql_calc_found_rows * FROM `team_finance` WHERE userno='$uid' AND `admin`='$admin' AND `type` = '1' ORDER BY billdate DESC".$paging;
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 管家 收支记录详情
if ($a == 'getfinancedetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_finance` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'查询成功', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 管家 删除
if ($a == 'updatefinance') {
  $mbillno = checkInput($_GET['billno']);
  $flag = checkInput($_GET['flag']);  // -1删除

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '-1') {
    $sql = "DELETE FROM `team_finance` WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 管家 新增&修改
if ($a == 'setfinance') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);
  $username = checkInput($obj['username']);
  $type = checkInput($obj['type']); // 收支类型 0收入 1支出
  $finname = checkInput($obj['finname']); // 收支名称
  $money = $obj['money'] ? checkInput($obj['money']) : 0.00; // 收支金额
  $describe = checkInput($obj['describe']); // 描述
  $findate = checkInput($obj['findate']); // 合同日期
  $mbillno = checkInput($obj['billno']); // 有值则修改

  if ($type != '0' && $type != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'type参数不匹配', 'status'=>'1')));
  }

  if (!$mbillno) {
    $sql = "INSERT INTO `team_finance` SET billdate=NOW(),billno='$_billno',username='$username',userno='$userno',`admin`='$admin',finname='$finname',`money`='$money',`type`='$type',findate='$findate',`describe`='$describe',image1='$image1',image2='$image2',image3='$image3'";
  } else { // 修改
    $sql = "UPDATE `team_finance` SET username='$username',userno='$userno',`admin`='$admin',finname='$finname',`money`='$money',`type`='$type',findate='$findate',`describe`='$describe',image1='$image1',image2='$image2',image3='$image3' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 合同 查询
if ($a == 'getcontract') {
  $admin  = checkInput($_GET['admin']);
  $uid  = checkInput($_GET['uid']);
  $flag = checkInput($_GET['flag']); // 0待处理的合同 1我提交的合同 2我审核的合同 3某个客户的全部合同
  $customerno = checkInput($_GET['customerno']);
  $keyword = checkInput($_GET['keyword']);
  // $role = checkInput($_GET['role']); // 角色
  $output = array('list'=>array(), 'total'=>0);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') {
    $query = "SELECT sql_calc_found_rows * FROM `team_contract` WHERE ((userno='$uid' AND astatus IN (0,1,2)) OR ((admin='$uid' OR admin IN (SELECT admin FROM `teams` WHERE userno ='$uid' AND isteam ='-2')) AND astatus IN (1,2)) OR (userno IN (SELECT userno FROM `teams` WHERE division ='$uid') AND admin='$admin' AND astatus IN (1,2)))";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%' OR contrano LIKE '%$keyword%')";
      }
    $query .= "ORDER BY modifydate DESC".$paging;

  } else if ($flag == '1') {
    $query = "SELECT sql_calc_found_rows * FROM `team_contract` WHERE (userno='$uid' AND astatus IN (1,2) AND admin='$admin')";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%' OR contrano LIKE '%$keyword%')";
      }
    $query .= " ORDER BY modifydate DESC".$paging;

  } else if ($flag == '2') {
    $query = "SELECT sql_calc_found_rows * FROM `team_contract` WHERE  (captainno='$uid' OR bossno='$uid') AND astatus IN (1,2) AND admin='$admin'";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%' OR contrano LIKE '%$keyword%')";
      }
    $query .= " ORDER BY modifydate DESC".$paging;

  } else if ($flag == '3') {
    $query = "SELECT sql_calc_found_rows * FROM `team_contract` WHERE userno='$uid' AND customerno='$customerno' AND admin='$admin'";
    if ($keyword) {
        $query .=" AND (title LIKE '%$keyword%' OR username LIKE '%$keyword%' OR contrano LIKE '%$keyword%')";
      }
    $query .= " ORDER BY billdate DESC".$paging;

  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 合同 详情
if ($a == 'getcontractdetail') {
  $mbillno = checkInput($_GET['billno']);

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_contract` WHERE billno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);

  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    exit(JSON(array('data'=>$row, 'msg'=>'暂无数据', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 合同 提交、删除， 撤销合同 
if ($a == 'updatecontract') {
  $mbillno = checkInput($_GET['billno']);
  $flag = checkInput($_GET['flag']);  // 1提交 -1删除, 0撤销合同

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '-1') {
      $sql = "UPDATE `team_contract` SET astatus='-1' WHERE billno='$mbillno'";
  } else if ($flag == '1') {
    $sql = "UPDATE `team_contract` SET astatus='1' WHERE billno='$mbillno'";
  } else if ($flag == '0') {
    $sql = "UPDATE `team_contract` SET astatus='1',reason3='',cancelsign='' WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 合同 新增&修改
if ($a == 'setcontract') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $admin = $obj['admin'];
  $userno = $obj['userno'];
  $username = $obj['username'];
  $customerno = $obj['customerno']; // 客户编号
  $customername = $obj['customername']; // 客户名称
  $title = $obj['title']; // 合同名称
  $contrano = $obj['contrano']; // 合同编号
  $introduction = $obj['introduction']; // 合同描述
  $amount = $obj['amount'] ? $obj['amount'] : 0.00; // 合同金额
  $paid = $obj['paid'] ? $obj['paid'] : 0.00; // 已付金额
  $contradate = $obj['contradate']; // 合同日期
  $mbillno = $obj['billno']; // 有值则修改
  $contratype = $obj['contratype'] == '采购' ? '0' : '1';

  if (!$mbillno) {
    $sql = "INSERT INTO `team_contract` SET modifydate=NOW(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',username='$username',billno='$_billno',
          amount='$amount',paid='$paid',introduction='$introduction',astatus='0',customername='$customername',contratype='$contratype',customerno='$customerno',`admin`='$admin',image1='$image1',image2='$image2',image3='$image3'";
  } else { // 修改
    $sql = "UPDATE `team_contract` SET modifydate=NOW(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',username='$username',
          amount='$amount',paid='$paid',introduction='$introduction',customername='$customername',contratype='$contratype',customerno='$customerno',`admin`='$admin',image1='$image1',image2='$image2',image3='$image3' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 合同 审核
if ($a == 'checkcontract') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $mbillno = $obj['billno'];
  $userno = $obj['userno'];
  $reason = $obj['reason']; // 理由
  $captainsign = $obj['captainsign'];
  $bosssign = $obj['bosssign'];
  $flag = $obj['flag'];
  $isok = $obj['isok'];

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $signImages = array();
  $imagesPath = setImagePath(2, $serverAssetsUrl);
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[0]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[1]);
  $cancelsign_field = toSqlImageField('cancelsign', $bosssign, $imagesPath[1]);

  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  !!$cancelsign_field && array_push($signImages, $cancelsign_field);
  // 转换
  $signImagesToSql = implode(',', $signImages);

  $astatus = $isok == '1' ? 1 : -1;
  if ($flag == '2') { // 队长审核
    $sql = "UPDATE `team_contract` SET captstatus='$astatus',modifydate=NOW(),reason='$reason',captainno='$userno',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '3') { // 老板审核
    $sql = "UPDATE `team_contract` SET bossstatus='$astatus',modifydate=NOW(),reason2='$reason',bossno='$userno',$signImagesToSql WHERE billno='$mbillno'";
  } else if ($flag == '0') { // 撤销
    $sql = "UPDATE `team_contract` SET astatus='2',reason3='$reason',$signImagesToSql WHERE billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flag不匹配', 'status'=>'1')));
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 收款记录 查询 （工作台-收付款 查询）
if ($a == 'getpaidrecord') {
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']);
  $uid = checkInput($_GET['uid']);
  $key = checkInput($_GET['key']);
  $output = array('list'=>array(), 'total'=>0);

  if ($key == '0') {
     if (!$customerno) {
        exit(JSON(array('data'=>'', 'msg'=>'uid参数错误', 'status'=>'1')));
      }
      $query = "SELECT * FROM `team_receivables` WHERE customerno='$customerno' AND `admin`='$admin' ORDER BY billdate DESC".$paging;
  } else { // 工作台-收付款 查询
     if (!$uid) {
        exit(JSON(array('data'=>'', 'msg'=>'uid参数错误', 'status'=>'1')));
      }
     $query = "SELECT * FROM `team_receivables` WHERE salephone='$uid' AND `admin`='$admin' ORDER BY billdate DESC".$paging;
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 查询收款的金额
if ($a == 'getpaiddetail') {
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']);

  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT SUM(amount) AS amount,(SUM(amount-paid)-IFNULL((SELECT SUM(receivedamount) FROM team_receivables WHERE `admin`='$admin' AND customerno='$customerno'),0)) AS nopaid FROM `team_contract`
              WHERE `admin`='$admin' AND (astatus='2' OR astatus='3') AND customerno='$customerno'";
  $result = $mysqli->query($query) or die($mysqli->error);

  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    if (!$data['nopaid']) {
      exit(JSON(array('data'=>'', 'msg'=>'该客户暂无合同需要收款', 'status'=>'1')));
    } else {
      exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
    }
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 收款处理
if ($a == 'setpaid') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $admin = $obj['admin'];
  $customerno = $obj['customerno']; // 客户编号
  $customername = $obj['customername']; // 客户名称
  $receivedamount = $obj['receivedamount']; // 收款金额
  $saleman = $obj['saleman']; // 操作员名称
  $salepnone = $obj['salepnone']; // 操作员id
  $describe = $obj['describe']; // 描述

  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$salepnone) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "INSERT INTO `team_receivables` SET billdate=NOW(),billno='$_billno',customername='$customername',customerno='$customerno',receivedamount='$receivedamount',saleman='$saleman',salephone='$salepnone',`admin`='$admin',`describe`='$describe',image1='$image1',image2='$image2',image3='$image3'";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 商品进出货 查询进出货统计
if ($a == 'get_stock_statistic') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']); // 0进货 1出货
  $warename = checkInput($_GET['warename']); // 商品名称
  $wareno = checkInput($_GET['wareno']); // 商品名称
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $customerno = checkInput($_GET['customerno']); // 客户编号
  $output = array('list'=>array(), 'total'=>0);

  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $table = $flag == '0' ? 'team_stockin' : 'team_stockout';

  $query = "SELECT sql_calc_found_rows DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate,SUM(qty*price + qty1*price + qty2*price + qty3*price) AS price,SUM(qty+qty1+qty2+qty3) AS qty FROM $table WHERE `admin`='$admin'";
  if ($begindate) {
    $query .=" AND billdate between '$begindate' AND '$enddate'";
  }
  if ($customerno) {
    $query .=" AND customerno='$customerno'";
  }
  $query .= " GROUP BY DATE_FORMAT(billdate,'%Y-%m-%d') ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 商品进出货 查询进出货的详细
if ($a == 'get_stock_body') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']); // 0进货 1出货
  $date1 = checkInput($_GET['date']); // 日期
  $date2 = @$_GET['date2'] ? checkInput($_GET['date2']) : $date1 ; // 结束日期
  $customerno = checkInput($_GET['customerno']); // 客户编号
  $output = array('list'=>array(), 'total'=>0);

  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$date1) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  
  $table = $flag == '0' ? 'team_stockin' : 'team_stockout';

  //$query = "SELECT sql_calc_found_rows * FROM $table WHERE `admin`='$admin' AND (DATE_FORMAT('$date1','%Y-%m-%d')=DATE_FORMAT(billdate,'%Y-%m-%d'))";
  $query = "SELECT sql_calc_found_rows * FROM $table WHERE `admin`='$admin' AND (DATE_FORMAT(billdate,'%Y-%m-%d') between DATE_FORMAT('$date1','%Y-%m-%d') and DATE_FORMAT('$date2','%Y-%m-%d'))";   //增加结束日期  chendognzhou 2019-11-04
  
  if ($customerno) {
    $query .=" AND customerno='$customerno'";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 打印商品列表
if ($a == 'printwareslist') {
  $admin = checkInput($_GET['admin']);
  $userno = checkInput($_GET['userno']);
  $flag = checkInput($_GET['flag']); // 0进货 1出货
  $date = checkInput($_GET['date']); // 日期
  $customerno = checkInput($_GET['customerno']); // 客户编号
  $customername = checkInput($_GET['customername']); // 客户名
  $customeraddress = checkInput($_GET['customeraddress']);
  $contractno = checkInput($_GET['contractno']); // 合同billno
  $username = checkInput($_GET['username']); //
  $number = checkInput($_GET['number']); // 次数

  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$date) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
   if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

   // 用户信息
  $query = "SELECT printersn,companyaddress,company FROM team_salesman WHERE userno='$userno' LIMIT 1";
  $result = $mysqli->query($query);
  $userInfo = $result->fetch_assoc();
  if (mysqli_num_rows($result) <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'用户不存在', 'status'=>'1')));
  }
  $printersn = $userInfo['printersn'];
  $companyaddres = $userInfo['companyaddress'];
  $company = $userInfo['company'];

  // 打印机是否连接
  $result = printerStatus($printersn);
  if ($result['status'] != '0') {
    exit(JSON(array('data'=>'', 'msg'=>'打印机状态异常', 'status'=>'1')));
  }

  // 打印的头部订单信息
  $printInfo = array(
    'customername' => $customername,
    'contractno' => $contractno,
    'customeraddress' => $customeraddress,
    'username' => $username,
    'date' => $date,
    'userno' => $userno,
    'companyaddres' => $companyaddres,
    'company' => $company,
    'list' => array()
  );

  $table = $flag == '0' ? 'team_stockin' : 'team_stockout';

  $query = "SELECT warename,model,qty,qty1,qty2,qty3,price,contactno FROM $table WHERE `admin`='$admin' AND (DATE_FORMAT('$date','%Y-%m-%d')=DATE_FORMAT(billdate,'%Y-%m-%d')) AND customerno='$customerno' ";

  if ($contractno) {
    $query .=" AND contactno='$contractno'";
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $key = 1;
  while ($item = $result->fetch_assoc()) {
     array_push(
      $printInfo['list'],
      array('id' => $key++, 'warename' => $item['warename'].' '.$item['model'], 'qty' => $item['qty']+$item['qty1']+$item['qty2']+$item['qty3'], 'price' => $item['price'],'amount' => sprintf('%01.2f', $item['qty']*$item['price']))
    );
  }

  $result = printOrder($printersn, $printInfo, 1, $flag, $number);
  if ($result['status'] == '0') {
    exit(JSON(array('data'=>'', 'msg'=>'正在打印中', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$result['msg'], 'status'=>'1')));
  }
}

// 商品进出货处理
if ($a == 'setstock') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']); // 操作员id
  $username = checkInput($obj['username']); // 操作员名称
  $customerno = checkInput($obj['customerno']); // 供应商编号
  $customername = checkInput($obj['customername']); // 供应商名称
  $contractno = checkInput($obj['contractno']); // 合同编号 
  $flag = checkInput($obj['flag']); // 0进1出

  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sqlarr = array();
  foreach ($obj['goodsList'] as $k => $v) {
    $wareno = checkInput($v['wareno']);
    $warename = checkInput($v['warename']);
    $image1 = checkInput($v['image1']);
    $image2 = checkInput($v['image2']);
    $image3 = checkInput($v['image3']);
    $price = checkInput($v['price']);
    $unit = checkInput($v['unit']);
    $model = checkInput($v['model']);
    $productno = checkInput($v['productno']);
    $serialno = checkInput($v['serialno']);
    $description = checkInput($v['description']);
    $qty = checkInput($v['qty']);
    $qty1 = checkInput($v['qty1']);
    $qty2 = checkInput($v['qty2']);
    $qty3 = checkInput($v['qty3']);

    $bno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999);
    array_push($sqlarr, "('$bno',NOW(),'$customerno','$customername','$username','$userno','$admin','$wareno','$warename','$model','$description','$qty','$qty1','$qty2','$qty3','$productno','$serialno','$price','$unit','$image1','$image2','$image3','$contractno')");
  }

  $tableName = $flag == '0' ? "team_stockin" : "team_stockout";
  $sql = "INSERT INTO $tableName(billno,billdate,customerno,customername,username,userno,admin,wareno,warename,model,description,qty,qty1,qty2,qty3,productno,serialno,price,unit,image1,image2,image3,contactno) VALUES".implode(',', $sqlarr);

  if (!$mysqli->query($sql)) {
    $output = array('data'=>$sql, 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>$sql, 'msg'=>'处理成功', 'status'=>'0');
    // 2019 - 11 = 16
    $sql = "UPDATE $tableName AS a,team_wares AS b SET a.image1 = b.image1,a.image2 = b.image2,a.image3 = b.image3 
            WHERE a.wareno = b.billno AND a.admin = b.admin AND a.image1 = '';";
    $mysqli->query($sql);
  }
  exit(JSON($output));
}

// 商品进出货 删除记录
if ($a == 'delstock') {
  $items = checkInput($_GET['items']);
  $flag = checkInput($_GET['flag']); // 0进货 1出货

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  } 
  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $table = $flag == '0' ? 'team_stockin' : 'team_stockout';
  $sql = "DELETE FROM $table WHERE billno IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 商品列表
if ($a == 'getwares') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能空', 'status'=>'1')));
  } 
  $output = array('list'=>array(), 'total'=>0);

  $query = "SELECT sql_calc_found_rows *,(SELECT typename from mall_category WHERE billno=`team_wares`.category3) AS catname FROM `team_wares` WHERE `admin`='$admin' AND `status`>'-1'";

  if ($keyword) {
    $query .=" AND (waresname LIKE '%$keyword%' OR productno='$keyword')";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 添加、编辑商品
if ($a == 'setwares') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1']; // 头像
  $image2 = $obj['image2']; // 轮播图
  $image3 = $obj['image3'];
  $image4 = $obj['image4'];
  $image5 = $obj['image5'];
  $image6 = $obj['image6'];
  $pic = $obj['pic']; // 详情图片
  $admincode = $obj['admincode'];			//管理员对应的  billno
  $admin = $obj['admin'];
  $username = $obj['username'];
  $waresname = $obj['waresname']; // 名称
  $model = $obj['model']; // 型号
  $productno = $obj['productno']; // 编码
  $price = $obj['price']; // 单价
  $mallprice = $obj['mallprice']; // 商城单价
  $unit= $obj['unit']; // 单位
  $describe = $obj['describe']; // 描述
  $mbillno = $obj['billno']; // 有值则修改
  $checkedIndex = $obj['checkedIndex'];
  $series = $obj['series']; // 产品的分类
  $onsale = $checkedIndex ? '1' : '0'; // 1商城上架,
  $brand = $obj['brand']; // 品牌
  $category1 = $obj['category1'];
   $category2 = $obj['category2']; // 分类
  $category3 = $obj['category3']; // 分类
  $mincode = $obj['mincode'];
  $minunit = $obj['minunit'];
  $netnum = $obj['netnum'];
  $minprice = $obj['minprice'];
  $minprice2 = $obj['minprice2'];
  
  if (!$mbillno) {
    $sql = "INSERT INTO `team_wares` SET billdate=NOW(),billno='$_billno',`admin`='$admin',`admincode`='$admincode',
    username='$username',waresname='$waresname',unit='$unit',model='$model',productno='$productno',price='$price',
    mallprice='$mallprice',`description`='$describe',brand='$brand',category1='$category1',category3='$category3',
    category2='$category2',image1='$image1',image2='$image2',image3='$image3',image4='$image4',image5='$image5',
    image6='$image6',pic='$pic',series='$series',onsale='$onsale',mincode='$mincode',minunit='$minunit',netnum='$netnum',minprice='$minprice',minprice2='$minprice2'";
  } else {
    $sql = "UPDATE `team_wares` SET username='$username',waresname='$waresname',unit='$unit',model='$model',productno='$productno',
    brand='$brand',category1='$category1',category3='$category3',category2='$category2',price='$price',mallprice='$mallprice',
    `description`='$describe',image1='$image1',image2='$image2',image3='$image3',image4='$image4',image5='$image5',image6='$image6',
    pic='$pic',series='$series',onsale='$onsale',mincode='$mincode',minunit='$minunit',netnum='$netnum',minprice='$minprice',minprice2='$minprice2' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 批发商，代理商 查询商品
if ($a == 'getmallwares') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  // $query = "SELECT sql_calc_found_rows * FROM `mall_wares` WHERE `admin`='$admin' AND `status`>'-1'";
  $query = "SELECT sql_calc_found_rows * FROM `view_wares` WHERE `admin`='$admin' AND `status`=0 AND onsale1='1'"; // 2019-11-09 改

  if ($keyword) {
    $query .=" AND (waresname LIKE '%$keyword%' OR wareno='$keyword' OR productno='$keyword')";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 批发商增加商品
if ($a == 'setmallwares') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }
  $image1 = $obj['image1']; // 头像
  $image2 = $obj['image2']; // 详情图片
  $image3 = $obj['image3'];
  $image4 = $obj['image4'];
  $image5 = $obj['image5'];
  $image6 = $obj['image6'];
  $admin = $obj['admin'];
  $admincode = $obj['admincode'];
  $username = $obj['username'];
  $waresname = $obj['waresname']; // 名称
  $model = $obj['model']; // 型号
  $waretype = $obj['waretype'];
  $productno = $obj['productno']; // 编码
  $price = $obj['price']; // 单价
  // $mallprice = $obj['mallprice']; // 商城单价
  $checkedIndex = $obj['checkedIndex'];
  $onsale = $checkedIndex ? '1' : '0'; // 1商城上架,
  $unit= $obj['unit']; // 单位
  $describe = $obj['describe']; // 描述
  $mbillno = $obj['billno']; // 有值则修改
  $wareno = $obj['wareno'];
  $place = $obj['place'];
  $makedate = $obj['makedate'];
  $warranty = $obj['warranty'];
  $buylimit = $obj['buylimit'];
  $envelope = $obj['envelope'];
  $ticket = $obj['ticket'];
  $net = $obj['net'];
  $mincode = $obj['mincode'];
  $minunit = $obj['minunit'];
  $netnum = $obj['netnum'];
  $minprice = $obj['minprice'];
  $minprice2 = $obj['minprice2'];
  $retailprice = $obj['retailprice'];
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  } 

  if (!$mbillno) {
   // 不能进同一个的商品
    $query = "SELECT  billno FROM `mall_wares` WHERE `admin`='$admin' AND wareno='$wareno' AND `status` !='-1'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $row = $result->fetch_assoc();
    if ($row) {
      exit(JSON(array('data'=>'', 'msg'=>'库存已存在该商品!', 'status'=>'1')));
    }

    $sql = "INSERT INTO `mall_wares` SET billdate=NOW(),billno='$_billno',`admin`='$admin',`admincode`='$admincode',username='$username',
    waresname='$waresname',unit='$unit',model='$model',wareno='$wareno',waretype='$waretype',productno='$productno',price='$price',
    `description`='$describe',image1='$image1',image2='$image2',image3='$image3',image4='$image4',image5='$image5',image6='$image6',
    place='$place',makedate='$makedate',warranty='$warranty',buylimit='$buylimit',envelope='$envelope',ticket='$ticket',net='$net'
    ,mincode='$mincode',minunit='$minunit',netnum='$netnum',minprice='$minprice',minprice2='$minprice2',onsale='$onsale',retailprice='$retailprice'";
  } else {
    $sql ="UPDATE `mall_wares` SET username='$username',waresname='$waresname',unit='$unit',model='$model',wareno='$wareno',waretype='$waretype',
    productno='$productno', price='$price',`description`='$describe',image1='$image1',image2='$image2',image3='$image3',image4='$image4',
    image5='$image5',image6='$image6',place='$place',makedate='$makedate',warranty='$warranty',buylimit='$buylimit',envelope='$envelope',
    ticket='$ticket',net='$net',mincode='$mincode',minunit='$minunit',netnum='$netnum',minprice='$minprice',minprice2='$minprice2',onsale='$onsale',retailprice='$retailprice'  WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 删除商品
if ($a == 'delwares') {
  $items = checkInput($_GET['items']); // 表 billno
  $flag = checkInput($_GET['flag']); // 0 , 1批发商

  if ($items == '') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 库存商品为零时才能删除
  if ($flag == 0) {
    $query = "SELECT SUM(qty+qty1+qty2+qty3) AS mqty FROM `team_wares` WHERE billno='$items'";
  } else {
    $query = "SELECT SUM(qty+qty1+qty2+qty3) AS mqty FROM `mall_wares` WHERE billno='$items'";
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row['mqty'] > 0) {
    exit(JSON(array('data'=>'', 'msg'=>'库存商品为零时才能删除', 'status'=>'1')));
  }
  if ($flag == 0) {
    $sql = "UPDATE `team_wares` SET `status`='-1' WHERE billno IN ($items)";
  } else {
    $sql = "UPDATE `mall_wares` SET `status`='-1' WHERE billno IN ($items)";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 取得退货列表 
if ($a == 'get_saleback_list') {  
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']); // 客户编号
  $output = array('list'=>array(), 'total'=>0);

  $table = 'team_saleback';

  $query = "SELECT sql_calc_found_rows * FROM $table WHERE `admin`='$admin' ";
  if ($customerno) {
    $query .=" AND customerno='$customerno'";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 取得盘点表列表
if ($a == 'get_stockcheck_list') {
  $admin = checkInput($_GET['admin']);
  $customerno = checkInput($_GET['customerno']); // 客户编号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $table = 'team_stockcheck';

  $query = "SELECT sql_calc_found_rows * FROM $table WHERE `admin`='$admin' ";
  if ($customerno) {
    $query .=" AND customerno='$customerno'";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 退货添加
if ($a == 'return_wares') {
 $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $backdate = checkInput($obj['date']);
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);
  $username = checkInput($obj['username']);
  $customername = checkInput($obj['customername']);
  $customerno = checkInput($obj['customerno']);
  $wareno = checkInput($obj['wareno']);
  $warename = checkInput($obj['warename']);
  $warepic = checkInput($obj['warepic']);
  $qty = checkInput($obj['qty']); 
  $amount = checkInput($obj['amount']); 
  $remark = checkInput($obj['remark']); 
  $unit = checkInput($obj['unit']);
  $selectid = checkInput($obj['selectid']); // 退货类型
  $series = checkInput($obj['series']);
  $model = checkInput($obj['model']);

  $sql = "INSERT INTO `team_saleback` SET billno='$_billno',backdate='$backdate',admin='$admin',userno='$userno',username='$username',
     customername='$customername',customerno='$customerno',series='$series',model='$model',
     wareno='$wareno',warename='$warename',unit='$unit',warepic='$warepic',qty='$qty',backtype='$selectid',amount='$amount',remark='$remark'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 盘点添加, 编辑
if ($a == 'check_wares') {
 $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  // $checkdate = checkInput($obj['date']);
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);
  $username = checkInput($obj['username']);
  $wareno = checkInput($obj['wareno']);
  $productno = checkInput($obj['productno']);
  $warename = checkInput($obj['warename']);
  $warepic = checkInput($obj['warepic']);
  $qty = checkInput($obj['qty']); 
  $newqty = checkInput($obj['newqty']); 
  $remark = checkInput($obj['remark']); 
  $unit = checkInput($obj['unit']);
  $qty1 = checkInput($obj['qty1']); 
  $qty2 = checkInput($obj['qty2']); 
  $qty3 = checkInput($obj['qty3']); 
  $minqty = checkInput($obj['minqty']);
  $newqty1 = checkInput($obj['newqty1']); 
  $newqty2 = checkInput($obj['newqty2']); 
  $newqty3 = checkInput($obj['newqty3']); 
  $nminqty = checkInput($obj['nminqty']); 
  $billno = checkInput($obj['billno']); 
  $series = checkInput($obj['series']); 
  $model = checkInput($obj['model']); 
  $mstr = "上传";

  if (!$billno){ // 新增
    $sql = "INSERT INTO `team_stockcheck` SET billno='$_billno',admin='$admin',userno='$userno',username='$username',series='$series',model='$model'
      ,wareno='$wareno',warename='$warename',unit='$unit',productno='$productno',warepic='$warepic',qty='$qty',newqty='$newqty',remark='$remark'
      ,qty1='$qty1',qty2='$qty2',qty3='$qty3',newqty1='$newqty1',newqty2='$newqty2',newqty3='$newqty3',minqty='$minqty',nminqty='$nminqty'";
  } else {   // 编辑
    $mstr = "更新";
    $sql = "UPDATE `team_stockcheck` SET admin='$admin',userno='$userno',username='$username',series='$series',model='$model'
      ,wareno='$wareno',warename='$warename',unit='$unit',productno='$productno',warepic='$warepic',qty='$qty',newqty='$newqty',remark='$remark'
      ,qty1='$qty1',qty2='$qty2',qty3='$qty3',newqty1='$newqty1',newqty2='$newqty2',newqty3='$newqty3',minqty='$minqty',nminqty='$nminqty' WHERE billno='$billno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>$mstr.'失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>$mstr.'成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 盘点删除
if ($a == 'delinventitem') {
  $billno = checkInput($_GET['billno']);
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "DELETE FROM `team_stockcheck` WHERE billno='$billno'";

  if (!$mysqli->query($query)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 根据条码去查询商品
if ($a == 'get_pro_wares') {
  $productno = checkInput($_GET['productno']);
  $items = array();
  $query = "SELECT * FROM `team_wares` WHERE productno='$productno' AND `status`>'-1' LIMIT 1";

  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row){
	$output["data"] = $row;
	$output["msg"] = "获取成功";
	$output["status"] = '0';
	exit(JSON($output));
 }else{
	$output = array('data'=>'', 'msg'=>'数据为空','status'=>'1');
	exit(JSON($output));
 } 
     
}

// 获取合同(仓存)
if ($a == 'get_contract') {
  $customerno = checkInput($_GET['customerno']);
  $output = array('list'=>array(), 'total'=>0);
  if (!$customerno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  $query = "SELECT title,contrano,image1 FROM `team_contract` WHERE customerno ='$customerno' AND bossstatus=1";
  $query .= " ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query);
 
  while($row = $result->fetch_assoc()){
        array_push($output['list'], $row);
     }
 exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 进仓，出仓的明细
if ($a == 'get_outin_details') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']); // 0进仓明细，1出仓明细
  $output = array('list'=>array());

  $table = $flag == '0' ? 'team_stockin' : 'team_stockout';
  
  $query = "SELECT sql_calc_found_rows * FROM $table WHERE `admin`='$admin' ";

  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 调仓信息 查询
if ($a == 'getrollover') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  $query = "SELECT * FROM `team_rollover` WHERE `admin`='$admin'";

  if ($keyword) {
    $query .=" AND (warename LIKE '%$keyword%' OR productno='$keyword')";
  }
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 调仓 信息添加，编辑
if ($a == 'setrollover') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $image1 = $obj['image1'];
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  //$ubillno = $obj['ubillno'];
  $admin = $obj['admin'];
  $username = $obj['username'];
  $warename = $obj['warename']; // 名称
  $wareno = $obj['wareno'];
  $model = $obj['model']; // 型号
  $productno = $obj['productno']; // 编码
  $price = $obj['price']; // 单价
  $qty = $obj['qty'];
  $qty1 = $obj['qty1'];
  $qty2 = $obj['qty2'];
  $qty3 = $obj['qty3'];
  $unit= $obj['unit']; // 单位
  $describe = $obj['describe']; // 描述
  $mbillno = $obj['billno']; // 有值则修改
  $series = $obj['series']; // 产品的分类
  $sorcedepot = $obj['sorcedepot'];
  $destination = $obj['destination'];
  $movenum = $obj['movenum'];
  
  if (!$mbillno) {
    $sql = "INSERT INTO `team_rollover` SET billdate=NOW(),billno='$_billno',`admin`='$admin',username='$username',warename='$warename',wareno='$wareno',unit='$unit',model='$model',
            productno='$productno',price='$price',qty='$qty',qty1='$qty1',qty2='$qty2',qty3='$qty3',`description`='$describe',image1='$image1',image2='$image2',image3='$image3',series='$series',sorcedepot='$sorcedepot',destination='$destination',movenum='$movenum'";
  } else {
    $sql = "UPDATE `team_rollover` SET username='$username',warename='$warename',wareno='$wareno',unit='$unit',model='$model',productno='$productno',
            price='$price',qty='$qty',qty1='$qty1',qty2='$qty2',qty3='$qty3',`description`='$describe',image1='$image1',image2='$image2',image3='$image3',series='$series',sorcedepot='$sorcedepot',destination='$destination',movenum='$movenum' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 调仓 删除
if ($a == 'delrollover') {
  $items = checkInput($_GET['items']);

  if ($items == '') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "DELETE FROM `team_wares`  WHERE billno='$items' LIMIT 1";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'处理失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'处理成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 简报
if ($a == 'getbriefing') {
  $userno = checkInput($_GET['userno']);
  $key = checkInput($_GET['key']);
  $output = array(
    'customer'=>'0', // 本周的新增客户
    'visit'=>'0',
    'contract'=>'0',
    'receivables'=>'0.00',
    'waresin'=>array('price'=>'0.00', 'qty'=>'0'),
    'waresout'=>array('price'=>'0.00', 'qty'=>'0')
  );
  $mstr ="";
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'userno参数不能为空', 'status'=>'1')));
  }
  
  if($key == '0'){ // 周
      $mstr =" AND YEARWEEK(DATE_FORMAT(billdate,'%Y-%m-%d')) = YEARWEEK(NOW())";
  } else if($key == '1'){ // 月
	  $mstr =" AND DATE_FORMAT( billdate, '%Y%m' ) = DATE_FORMAT( CURDATE() ,'%Y%m' )";
  } else{ // 年
	  $mstr =" AND YEAR(billdate) = YEAR(NOW())";
  }
  
  // 本周的新增客户		  
  $query = "SELECT count(*) AS count FROM `team_customer` WHERE saler='$userno' AND status!=-1".$mstr;
 
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $output['customer'] = $data['count'];
  }

  // 本周的拜访客户
  $query = "SELECT count(*) AS count FROM `team_visit` WHERE saleno='$userno'".$mstr;
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $output['visit'] = $data['count'];
  }

  // 新增合同
  $query = "SELECT count(*) AS count FROM `team_contract` WHERE userno='$userno' AND astatus!=-1".$mstr;
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $output['contract'] = $data['count'];
  }

  // 收款金额
  $query = "SELECT IF(SUM(receivedamount),SUM(receivedamount),'0.00') AS amount FROM `team_receivables` WHERE salephone='$userno'".$mstr;
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
      $data = $result->fetch_assoc();
      $output['receivables'] = $data['amount'];
  }

  // 进货
  $query = "SELECT IF(SUM(qty*price),SUM(qty*price),'0.00') AS price,IF(SUM(qty),SUM(qty),'0') AS qty FROM team_stockin WHERE userno='$userno'".$mstr;
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $output['waresin']['price'] = $data['price'];
    $output['waresin']['qty'] = $data['qty'];
  }

  // 出货
  $query = "SELECT IF(SUM(qty*price),SUM(qty*price),'0.00') AS price,IF(SUM(qty),SUM(qty),'0') AS qty FROM team_stockout WHERE userno='$userno'".$mstr;
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $output['waresout']['price'] = $data['price'];
    $output['waresout']['qty'] = $data['qty'];
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 商品库存统计图表数据
if ($a == 'getstockchart') {
  $admin = checkInput($_GET['admin']);
  $datepart = @$_GET['datapart'] ? checkInput($_GET['datapart']) : 'Y';			//日期 D,W,M,Y 
  $output = array(
    'sellbuychart'=>array(),
    'businesspichart'=>array(),
	'workerchart'=>array()
  );

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'userno参数不能为空', 'status'=>'1')));
  }

  // 营销按月图表数据
  $query = "call getMonthChartData('$admin','$datepart');";			//调用存储过程取得数据
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['sellbuychart'], $row);
  }
  //mysql_free_result($result);
  clearStoredResults();
  
  //营业饼图
  $query = "call getBusinessPichart('$admin','$datepart');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['businesspichart'], $row);
  }
  
   clearStoredResults();
  //工作人员进出货统计
  $query = "call getWorkerChartData('$admin','$datepart');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['workerchart'], $row);
  }
  

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 经营数据
if($a == 'getoperatedata'){
	$userno = checkInput($_GET['userno']); 
	$output = array(
		'customerchart'=>array(),
		'contrachart'=>array()
   );
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
 // 客户分析	
  $query = "call getOperateChartData('$userno');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
	array_push($output['customerchart'], $row);
 }
   clearStoredResults();
  
 // 合同分析
  $query = "call getOperContChartData('$userno');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
	array_push($output['contrachart'], $row);
 }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
	
}


/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////积分管理/////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////


// 查询积分规则分类
if ($a == 'getpointitemlist') {
  $admin = checkInput($_GET['admin']);
  $output = array('list'=>array(), 'total'=>0);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `point_items` WHERE `admin`='$admin' ";

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 添加、修改积分规则 (2019-4-1 hqh  暂不用)
if ($a == 'setpointrule009') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno= checkInput($obj['bno']);		//新增时为空  编辑不能为空
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);
  // $staffname =  checkInput($obj['staffname']);
  // $staffno = checkInput($obj['staffno']); // 职员、队员编号
  // $ruletype = checkInput($obj['ruletype']); // 规分分类
  $rulename = checkInput($obj['rulename']); // 规则名称
  $points = checkInput($obj['points']);	// 积分 可为 正+ 负-
  $remark = checkInput($obj['remark']);	// 备注
 
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  // if (!$staffno) {
    // exit(JSON(array('data'=>'', 'msg'=>'staffno不能为空', 'status'=>'1')));
  // }
  
  if ($bno) {
    $sql = "UPDATE point_rule SET userno='$userno',rulename='$rulename',points='$points',remark='$remark' WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO point_rule SET billno='$_billno',`admin`='$admin',userno='$userno',rulename='$rulename',points='$points',remark='$remark'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
 
}

//删除积分规则
if ($a == 'delpointrule') {
  $items = checkInput($_GET['items']);		//可选择多条删除


  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "DELETE FROM `point_rule` WHERE id IN ($items)";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}



// 查询积分规则 
if ($a == 'getpointrulelist') {
  $admin = checkInput($_GET['admin']);
  $staffno = checkInput($_GET['staffno']); 
  $staffname = checkInput($_GET['staffname']);   //人员编号
  $ruletype = checkInput($_GET['ruletype']);
  // 规分分类 
   $billdate = checkInput($_GET['billdate']);//制单日期
  $output = array('list'=>array(), 'total'=>0);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  // exit(JSON(array('data'=>$staffno, 'msg'=>'这样调试', 'status'=>'1')));
  $query = "SELECT sql_calc_found_rows * FROM `point_rule` WHERE `admin`='$admin' ";
  if ($staffname) {
    $query .= " AND staffname LIKE '%$staffname%'";
  }
  if ($ruletype) {
    $query .= " AND ruletype LIKE '%$ruletype%'";
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



////积分统计列表,APP织分第一页/////////////////////////
if ($a == 'getpointtracklist') {
  $admin = checkInput($_GET['admin']);
  $output = array('list'=>array(), 'total'=>0);
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  } 
  
  $query = "SELECT a.*,(SELECT IFNULL(SUM(points),0) FROM `point_track` WHERE userno=a.`userno`) AS pointsum,(SELECT IFNULL(SUM(points),0) FROM `point_track` WHERE userno=a.`userno` AND DATE_FORMAT(billdate,'%Y%m')=DATE_FORMAT(CURDATE(),'%Y%m')) AS monthsum FROM  `view_groupuserlist` AS a  WHERE a.`admin`='$admin' ";
  $query .= $paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
	array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));  
}



///////////////////////////////////个人积分接口////////////////////////////////////////////

// 查询我的积分 (app 积分详细--hqh)
if ($a == 'getmypointtracklist') {
  $admin = checkInput($_GET['admin']);
  $staffno = checkInput($_GET['staffno']); 
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$staffno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `point_track` WHERE `admin`='$admin' AND staffno='$staffno'";
 
  if($keyword) {
	$query .=" AND (billdate LIKE '%$keyword%' OR staffname LIKE '%$keyword%' OR ruletype LIKE '%$keyword%')";
  }
   $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
 
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 添加、修改员工人员积分  (某人的积分增加 2019-4-3  hqh )
if ($a == 'setpointrule') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno= checkInput($obj['bno']);		//新增时为空  编辑不能为空
  $admin = checkInput($obj['admin']);
  $userno = checkInput($obj['userno']);		//录入员
  $staffno = checkInput($obj['staffno']); // 职员、队员编号
  $staffname = checkInput($obj['staffname']); 
  $ruletype = checkInput($obj['ruletype']); // 规分分类
  $rulename = checkInput($obj['rulename']); // 规则名称
  $points = checkInput($obj['points']);	// 积分 可为 正+ 负-
  $authname = checkInput($obj['authname']);	// 加扣分的授权人
  $remark = checkInput($obj['remark']);	// 备注
 
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  if (!$staffno) {
    exit(JSON(array('data'=>'', 'msg'=>'staffno不能为空', 'status'=>'1')));
  }
  
  if ($bno) {
    $sql = "UPDATE point_track SET userno='$userno',staffno='$staffno',staffname='$staffname',ruletype='$ruletype',rulename='$rulename',points='$points',authname='$authname',remark='$remark' WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO point_track SET billno='$_billno',`admin`='$admin',userno='$userno',staffno='$staffno',staffname='$staffname',ruletype='$ruletype',rulename='$rulename',points='$points',authname='$authname',remark='$remark'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
 
}



//删除用户积分记录（只有管理员才能使用的接口）
if ($a == 'delpointtrack') {
  $items = checkInput($_GET['items']);
 

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "DELETE FROM `point_track` WHERE id IN ($items)";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

//////////////////////////////////////////////积分接口结束////////////////////////////////////////////////////////

 //实名认证
 if($a == 'check_realname'){
  $userno = checkInput($_GET['userno']);
  
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $data = array('list'=>array());
  $query = "SELECT * FROM team_license WHERE styles='1' ORDER BY billdate DESC";
  $result = $mysqli->query($query);
  if($result){
    while ($row =  $result->fetch_assoc()) {
      array_push($data['list'], $row);
    }
  }
  exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
}



///////////////




// 查询用户余额
if ($a == 'user_money') {
  $usercode = checkInput($_GET['usercode']);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM team_salesman WHERE billno='$usercode' LIMIT 1";
  $result = $mysqli->query($query);
  $r = $result->fetch_assoc();
  if (!$r) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  } else {
    $data = array('money'=>$r['rmb']);
    exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
  }
}

// 申请提现（商家） hqh-
if ($a == 'set_withdraw') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, true);

  $type = checkInput($obj['type']); // 1:银行 2:钱包
  // $openid = checkInput($obj['openid']); // 提现人openid
  $usercode = checkInput($obj['usercode']); // 提现人id
  $bank_id = checkInput($obj['bank_id']); // 银行编号 https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_4&index=5
  $bank_name = checkInput($obj['bank_name']); // 银行名称
  $bank_card_id = checkInput($obj['bank_card_id']); // 卡号
  $cardholder = checkInput($obj['cardholder']); // 持卡人、真实姓名
  $mobile = checkInput($obj['mobile']); // 联系手机
  $amoney = checkInput($obj['amoney']); // 提现金额
  $wx_form_id = isset($obj['wx_form_id']) ? checkInput($obj['wx_form_id']) : ''; // wx模板消息id
  $remind = isset($obj['remind']) ? checkInput($obj['remind']) : '1'; // 启用检查返回订单信息给客户端确认；1:返回确认信息 2:正式提交

  $amoney = number_format($amoney, 2, '.', ''); // 格式化

  if ($type != '1' && $type != '2') {
    exit(JSON(array('data'=>'', 'msg'=>'type不匹配', 'status'=>'1')));
  }
  if (is_numeric($amoney) == false) {
    exit(JSON(array('data'=>'', 'msg'=>'金额为数字', 'status'=>'1')));
  }
  if ($type == '1' && (!$bank_id || !$bank_name || !$bank_card_id)) {
    exit(JSON(array('data'=>'', 'msg'=>'银行卡资料填写错误', 'status'=>'1')));
  }
  if (!$usercode || !$cardholder || !$mobile) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if ($amoney <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'金额不能小于0元', 'status'=>'1')));
  }

  // 查询进行中的提现任务，待审核&待到账属于进行中的任务
  $query = "SELECT id FROM team_withdraw WHERE `status` IN (0,2) AND `user_id`='$usercode'";
  $result = $mysqli->query($query);
  if ($result->fetch_assoc()) {
    exit(JSON(array('data'=>'', 'msg'=>'已有正在处理的申请', 'status'=>'1')));
  }

  // 查询用户余额
  $query = "SELECT * FROM team_salesman WHERE billno='$usercode'";
  $result = $mysqli->query($query);
  $userInfo = $result->fetch_assoc();
  if (!$userInfo) {
    exit(JSON(array('data'=>'', 'msg'=>'没有数据', 'status'=>'1')));
  }

  // 查询提现规则
  $query = "SELECT * FROM team_finance_config WHERE id=0";
  $result = $mysqli->query($query);
  $financeConfig = $result->fetch_assoc();
  if (!$financeConfig) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无配置记录', 'status'=>'1')));
  }

  // 提现到银行卡或提现到微信钱包的规则
  if ($type == '1') {
    $min_amount = $financeConfig['min_amount']; // 最小提现金额
    $max_amount = $financeConfig['max_amount']; // 最大提现金额
    $service_charge = $financeConfig['service_charge']; // 手续费
    $min_service_charge = $financeConfig['min_service_charge']; // 单笔手续费最少费用
  } else {
    $min_amount = $financeConfig['wx_min_amount']; // 最小提现金额
    $max_amount = $financeConfig['wx_max_amount']; // 最大提现金额
    $service_charge = $financeConfig['wx_service_charge']; // 手续费
    $min_service_charge = $financeConfig['wx_min_service_charge']; // 单笔手续费最少费用
  }
  $openid = $userInfo['wxthreeload'];
  $username = $userInfo['username']; // 提现人用户名
  $money = $userInfo['rmb']; // 现有金额
  $z_money = $money - $amoney; // 提现后剩余金额
  $cost = $amoney * $service_charge;  // 实际手续费
  if ($cost < $min_service_charge) {
    $cost = $min_service_charge; // 手续费至少*元
  }
  $r_money = $amoney - $cost; // 实际提现金额

  // 实际提现金额不能小于**
  if ($r_money <= 0) {
    // 加0.01？比如用户提现1元，手续费也是1元，这样实际转账就为0元了，所以要加0.01
    exit(JSON(array('data'=>'', 'msg'=>'金额至少'.($cost+0.01).'元', 'status'=>'1')));
  }
  // 大于现有金额
  if ($amoney > $money) {
    exit(JSON(array('data'=>'', 'msg'=>'余额不足', 'status'=>'1')));
  }
  // 提现金额是否在规定范围内
  if ($amoney < $min_amount) {
    exit(JSON(array('data'=>'', 'msg'=>'最少提现'.$min_amount.'元', 'status'=>'1')));
  }
  // 提现金额是否在规定范围内
  if ($amoney > $max_amount) {
    exit(JSON(array('data'=>'', 'msg'=>'最多提现'.$max_amount.'元', 'status'=>'1')));
  }

  // 返回订单确认信息
  if ($remind == '1') {
    $msg = '提现'.$amoney.'元，含手续费('.$cost.')元。';
    exit(JSON(array(
      'data'=>array('amoney'=>$amoney, 'cost'=>$cost),
      'msg'=>'ok',
      'status'=>'2'
    )));
  }

  $mysqli->query('BEGIN');

  // 更新用户的金额
  $sql = "UPDATE team_salesman SET `rmb`='$z_money' WHERE billno='$usercode'";
  $t1 = $mysqli->query($sql);

  // 在提现列表添加一条数据
  $sql = "INSERT INTO team_withdraw SET billno='$_billno',wx_id='$openid',name='$username',user_id='$usercode',mobile='$mobile',bank_id='$bank_id',bank_name='$bank_name',bank_card_id='$bank_card_id',cardholder='$cardholder',money='$r_money',z_money='$z_money',s_charge='$cost',status=0,wx_form_id='$wx_form_id',add_date=CURRENT_TIMESTAMP,type='$type',user_type=1";
  $t2 = $mysqli->query($sql);

  if ($t1 && $t2) { 
    $mysqli->query('COMMIT');
    $mysqli->query('END');
    echo JSON(array('data'=>'', 'msg'=>'申请成功', 'status'=>'0'));
    // 通过公众号发送提现通知给审核员
    include_once __DIR__.'/../wx/WxTemplateMessage.php';
    $wxTemplateMessage = new WxTemplateMessage();
 
    // $query = "SELECT wxthreeload AS openid FROM team_salesman WHERE checked=1 AND wxthreeload IS NOT NULL";
    $query = "SELECT wxthreeload2 AS openid FROM team_examine WHERE checked=1 AND wxthreeload2 IS NOT NULL";
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
      $wxTemplateMessage->mp1(array(
        'openid' => $row['openid'], 
        'shopname' => $userInfo['company'],
        'amount' => $amoney,
        'username' => $userInfo['username'],
        'date' => date('Y-m-d H:i:s'),
        'remark' => '来源：好业绩'
      ));
    }
    exit;
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'申请失败', 'status'=>'1')));
  }
}

// 我的交易记录，现在只做提现记录，以后可能会有充值记录等
if ($a == 'transfer_record') {
  $usercode = checkInput($_GET['usercode']);
  $type = checkInput($_GET['type']); // 1:提现记录
  $data = array('list'=>array(), 'total'=>0);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *,(money+s_charge) as amoney FROM team_withdraw WHERE user_id='$usercode' ORDER BY add_date DESC".$paging;
  $result = $mysqli->query($query);
  $totalResult = $mysqli->query("SELECT found_rows() AS total");
  $row2 =$totalResult->fetch_assoc();
  $oudatatput['total'] = $row2['total'];
  while ($row =  $result->fetch_assoc()) {
    array_push($data['list'], $row);
  }
  exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
}

// 壹软运营
// 提现审核列表
if ($a == 'withdraw_list') {
  $status = checkInput($_GET['status']); // '100':全部 0:待审核 1:审核拒绝 2:待到账 3:已到账 4:代付失败 5:银行退票
  $output = array('list'=>array(), 'total'=>0);

  $statusField;
  if ($status != '100') {
    $statusField = "WHERE status='$status'";
  }

  // 只显示该用户最新的一条动态
  // 对于5.7版本的mysql需要在排序的时候加一个limit
  $query = "SELECT sql_calc_found_rows t.*,(t.money+t.s_charge) as amoney FROM
    (SELECT * FROM team_withdraw $statusField ORDER BY add_date DESC LIMIT 10000000000) t
    GROUP BY user_id ORDER BY add_date DESC".$paging;
  $result = $mysqli->query($query);
  $totalResult = $mysqli->query("SELECT found_rows() AS total");

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 壹软运营
// 处理提现
if ($a == 'handle_withdraw') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, true);

  $auditor_id = checkInput($obj['auditor_id']); // 操作员id
  $auditor_phone = checkInput($obj['auditor_phone']); // 操作员id
  $user_id = checkInput($obj['user_id']); // 申请人billno
  $userno = checkInput($obj['userno']); // 申请人 phone
  $remark = isset($obj['remark']) ? checkInput($obj['remark']) : ''; // 审核备注
  $flag = checkInput($obj['flag']); // 1通过 2拒绝

  if ($flag != '1' && $flag != '2') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$auditor_id || !$user_id) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 检查操作员权限
  $query = "SELECT id FROM team_examine WHERE checked=1 AND phone='$auditor_phone'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows <= 0) {
      exit(JSON(array('data'=>'', 'msg'=>'没有权限审核提现', 'status'=>'1')));
  }

  // 查询申请人
  $query = "SELECT `rmb` FROM team_salesman WHERE billno='$user_id'";
  $result = $mysqli->query($query);
  $userInfo = $result->fetch_assoc();
  if (!$userInfo) {
    exit(JSON(array('data'=>'', 'msg'=>'用户不存在', 'status'=>'1')));
  }

  // 查询申请记录
  $query = "SELECT * FROM team_withdraw WHERE `status`=0 AND `user_id`='$user_id' LIMIT 1";
  $result = $mysqli->query($query);
  $r = $result->fetch_assoc();
  if (!$r) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }

  $money = $userInfo['rmb']; // 提现人现有金额
  $tx_type = $r['type']; // 提现类型
  $z_money = $r['money'] + $r['s_charge']; // 实际提现金额 + 实际手续费

  if ($flag == '1') {
    // 发起转账
    include_once __DIR__.'/../wx/WxPayService.php';
    $wxPayService = new WxPayService();

    $output = array();
    if ($tx_type == '1') { // 银行卡
      $orderInfo = array(
        'tradeno'=>$r['billno'],
        'bank_card_id'=>$r['bank_card_id'],
        'bank_id'=>$r['bank_id'],
        'true_name'=>$r['cardholder'],
        'total_fee'=>$z_money,
      );
      $payResult = $wxPayService->pay_to_bank($orderInfo);
    } else { // 微信钱包
      $orderInfo = array(
        'tradeno'=>$r['billno'],
        'openid'=>$r['wx_id'],
        'true_name'=>$r['cardholder'],
        'total_fee'=>$z_money,
      );
      exit(JSON(array('data'=>$orderInfo, 'msg'=>'到这里1', 'status'=>'1')));
      $payResult = $wxPayService->pay_to_pocket($orderInfo);
      
    }

    if ($payResult['status'] == '0') {
      // 微信受理该转账，可以更新提现记录状态
      if ($tx_type == '1') {
        $wx_charge = $payResult['data']['cmms_amt'] / 100; // 微信代付收取的手续费
      } else {
        $wx_charge = 0;
      }
      $sql = "UPDATE team_withdraw SET status=2,auditor_id='$auditor_id',remark='$remark',wx_charge='$wx_charge',check_date=CURRENT_TIMESTAMP WHERE id='{$r['id']}'";
      if (!$mysqli->query($sql)) {
        $output = array('msg'=>'wx已受理，但订单状态更新失败', 'status'=>'1');
      } else {
        if ($tx_type == '2') {
          // 如果是提现到零钱，可直接进行查询，因为零钱到账很快
          foreachTransferProgress();
        }
        $output = array('msg'=>'成功受理', 'status'=>'0');
      }
    } else if ($payResult['status'] == '1') {
      $msg = $payResult['data']['err_code'].' '.$payResult['data']['err_code_des'];
      $output = array('msg'=>$msg, 'status'=>'1');
    } else {
      $output = array('msg'=>$payResult['msg'], 'status'=>'1');
    }
    exit(JSON($output));
  } else {
    $mysqli->query('BEGIN');
    // 恢复用户金额
    $sql = "UPDATE team_salesman SET `rmb`=`rmb`+'$z_money' WHERE billno='$user_id'";
    $t1 = $mysqli->query($sql);

    // 更新提现记录状态
    $sql = "UPDATE team_withdraw SET status=1,auditor_id='$auditor_id',remark='$remark',check_date=CURRENT_TIMESTAMP WHERE id='{$r['id']}'";
    $t2 = $mysqli->query($sql);

    if ($t1 && $t2) {
      $mysqli->query('COMMIT');
      $mysqli->query('END');
      // 发送模板消息
      if ($r['wx_id'] && $r['wx_form_id']) {
        include_once __DIR__.'/../wx/WxTemplateMessage.php';
        $wxTemplateMessage = new WxTemplateMessage();
        $wxTemplateMessage->t3(array(
          'openid'=>$r['wx_id'],
          'form_id'=>$r['wx_form_id'],
          'amount'=>$z_money,
          'money'=>$r['money'],
          'cost'=>$r['s_charge'],
          'type'=>$tx_type == '1' ? '银行卡' : '微信钱包',
          'date'=>$r['add_date'],
          'status'=>'审核拒绝',
          'remark'=>$remark
        ));
      }
      exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
    } else {
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>'处理错误', 'status'=>'1')));
    }
  }
}



