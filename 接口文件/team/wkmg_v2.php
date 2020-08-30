<?php
header("Access-Control-Allow-Origin: *"); // 解决跨域问题
header("Content-type:text/html;charset=utf-8"); // header("Content-Type:text/html;charset=gbk");
date_default_timezone_set('PRC'); // 设置正确的时区，以防错误

require("DB_config.php");
require("config.php");
require("function.php");
require("thumb.php");
  
$conn = mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die("error connecting") ; // 连接数据库

mysql_query("SET NAMES utf8");
mysql_query("set character_set_client=utf8"); 
mysql_query("set character_set_results=utf8");

 
mysql_select_db($mysql_database); // 打开数据库

$output = array();
$outdata = array();
$a = @$_GET['a'] ? $_GET['a'] : '';
$v = @$_GET['v'] ? $_GET['v'] : '100';			// 版本号
$macid = @$_GET['macid'] ? $_GET['macid'] : '';	// 机器码

$admin = @$_GET['adm'] ? $_GET['adm'] : '';	

// 动态生成一个全局单号
$billno = substr(date("ymdHis"), 1, 11).mt_rand(100, 999);	

// 分页
define('PAGE', empty($_GET['page']) ? 1 : $_GET['page']); // 第几页(默认第1页)
define('PAGE_SIZE', empty($_GET['pagesize']) ? 15 : $_GET['pagesize']); // 每页显示的条数(默认15条)
define('PAGING', (PAGE - 1) * PAGE_SIZE.', '.PAGE_SIZE);


if (empty($a)) {
  $output = array('data'=>'', 'msg'=>'头参数不能为空', 'success'=>'0');
  exit(JSON($output));
}




// 查询工作计划
if ($a == 'selectworkPlan') {
  $uid = $_GET['uid'];
  $flat = $_GET['flat']; // 1我的本周 2我的本月 3我的所有 -1队员的本周 -2队员的本月 -3队员的所有 

  if ($uid == '') {
    $output = array('data'=>'', 'msg'=>'uid不能为空', 'success'=>'0');
    exit(JSON($output));
  } 

  $sql = "SELECT a.*,b.startworkaddress AS faddress,b.endworkaddress AS saddress FROM `team_workplan` AS a 
          LEFT JOIN `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  
          WHERE ";
  $sql2 = "SELECT COUNT(*) as total FROM `team_workplan` AS a 
          LEFT JOIN `team_attendance` AS b ON a.userno=b.userno AND TO_DAYS(a.workdate)=TO_DAYS(b.billdate)  
          WHERE ";

  if ($flat == '1') { 
    $query = $sql."a.userno='$uid' AND YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) AND `status`>'-1' 
              ORDER BY a.workdate ASC LIMIT ".PAGING;
    $count = $sql2."a.userno='$uid' AND YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) AND `status`>'-1'";

  } else if ($flat == '2') { 
    $query = $sql."a.userno='$uid' AND DATE_FORMAT(a.workdate,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') AND `status`>'-1' 
              ORDER BY a.workdate ASC LIMIT ".PAGING;
    $count = $sql2."a.userno='$uid' AND DATE_FORMAT(a.workdate,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') AND `status`>'-1'";

  } else if ($flat == '3') {  
    $query = $sql."a.userno='$uid' AND `status`>'-1' ORDER BY a.billdate DESC LIMIT ".PAGING;
    $count = $sql2."a.userno='$uid' AND `status`>'-1'";
    
  } else if ($flat == '-1') {  
    $query = $sql."(a.admin='$admin' OR a.admin='$uid') AND YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) AND `status`>'-1' 
              ORDER BY a.workdate ASC LIMIT ".PAGING;
    $count = $sql2."(a.admin='$admin' OR a.admin='$uid') AND YEARWEEK(DATE_FORMAT(a.workdate,'%Y-%m-%d')) = YEARWEEK(NOW()) AND `status`>'-1'";

  } else if ($flat == '-2') {  
    $query = $sql."(a.admin='$admin' OR a.admin='$uid') AND DATE_FORMAT(a.workdate,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') AND `status`>'-1' 
              ORDER BY a.workdate ASC LIMIT ".PAGING;
    $count = $sql2."(a.admin='$admin' OR a.admin='$uid') AND DATE_FORMAT(a.workdate,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') AND `status`>'-1'";

  } else if ($flat == '-3') {  
    $query = $sql."(a.admin='$admin' OR a.admin='$uid') AND `status`>'-1' ORDER BY a.billdate ASC LIMIT ".PAGING;
    $count = $sql2."(a.admin='$admin' OR a.admin='$uid') AND `status`>'-1'";

  } else {
    $output = array('data'=>'', 'msg'=>'flat错误', 'success'=>'0');
    exit(JSON($output));
  }
  
  $result = mysql_query($query, $conn) or die(mysql_error($conn));
  $result2 = mysql_query($count, $conn) or die(mysql_error($conn));

  $items = array();
  while ($row = mysql_fetch_assoc($result)) {
    array_push($items, $row);
  }

  // 总记录数
  $row2 = mysql_fetch_assoc($result2);
  if (!$row2) {
    $total = 0;
  } else {
    $total = $row2['total'];
  }

  if (empty($items)) {
    $output = array('data'=>'', 'total'=>$total, 'msg'=>'数据为空', 'success'=>'0');
  } else {
    $output = array('data'=>$items, 'total'=>$total, 'msg'=>'获取成功', 'success'=>'1');
  }
  exit(JSON($output));
}

// 工作任务处理
if ($a == 'puttask') {
  $input = file_get_contents("php://input"); 
  $obj = json_decode($input, true);
  // 接收的参数
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
  $captainsign = gettostr($obj['captainsign']);
  $bosssign = gettostr($obj['bosssign']);
  $flat = gettostr($obj['flat']);  // 0新任务插入，1进行中审核任务，2撤销，3暂存任务的更改

  $images = array();
  $signImages = array();
  $imagesPath = setImagePath(5, $serverurl);
  // 签名图片
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[3]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[4]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 其它图片
  $image1_field = toSqlImageField('image1', $image1, $imagesPath[0]);
  $image2_field = toSqlImageField('image2', $image2, $imagesPath[1]);
  $image3_field = toSqlImageField('image3', $image3, $imagesPath[2]);
  !!$image1_field && array_push($images, $image1_field);
  !!$image2_field && array_push($images, $image2_field);
  !!$image3_field && array_push($images, $image3_field);
  // 转换
  $imagesToSql = implode(',', $images);
  $signImagesToSql = implode(',', $signImages);

  if ($flat == '0') {    
    $sql = "INSERT INTO `team_task` SET billno='$billno',billdate=NOW(),memo='$memo',title='$title',responname='$responname',
            `status`='$mstatus',responphone='$responphone',admin='$admin',userno='$userno',username='$username',lastdate='$lastdate',$imagesToSql";
  } else if ($flat == '1') {
    $sql = "UPDATE `team_task` SET `status`='$mstatus',$signImagesToSql WHERE billno='$bino'";
  } else if ($flat == '2') {
     $sql = "UPDATE `team_task` SET `status`='$mstatus' WHERE billno='$bino'";
  } else if ($flat == '3') {  
    $sql = "UPDATE `team_task` SET title='$title',memo='$memo',responname='$responname',
            `status`='$mstatus',responphone='$responphone',username='$username',lastdate='$lastdate',$imagesToSql WHERE billno='$bino'";
  }

  if (!mysql_query($sql, $conn))
    $output = array('data'=>'', 'msg'=>'上传失败', 'success'=>'0');
  else
    $output = array('data'=>'', 'msg'=>'上传成功', 'success'=>'1');    
  exit(JSON($output));
}

// 处理报销
if ($a == 'putreimbur') {
  $input = file_get_contents("php://input"); 
  $obj = json_decode($input, true);
  // 接收的参数
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
  $flat = gettostr($obj['flat']);

  $images = array();
  $signImages = array();
  $imagesPath = setImagePath(5, $serverurl);
  // 签名图片
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[3]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[4]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 其它图片
  $image1_field = toSqlImageField('image1', $image1, $imagesPath[0]);
  $image2_field = toSqlImageField('image2', $image2, $imagesPath[1]);
  $image3_field = toSqlImageField('image3', $image3, $imagesPath[2]);
  !!$image1_field && array_push($images, $image1_field);
  !!$image2_field && array_push($images, $image2_field);
  !!$image3_field && array_push($images, $image3_field);
  // 转换
  $imagesToSql = implode(',', $images);
  $signImagesToSql = implode(',', $signImages);

  if ($flat == '0') {// 新建
    $sql = "INSERT INTO `team_reimbursement` SET billdate=NOW(),billno='$billno',username='$username',userno='$userno',admin='$admin',projectname='$projectname',`money`='$money',`type`='$type',reimburdate='$time',`describe`='$describe',`status`='0',$imagesToSql"; 
  } else if ($flat == '1') {// 修改
    $sql = "update `team_reimbursement` SET billdate=NOW(),projectname='$projectname',money='$money',`type`='$type',reimburdate='$time',`describe`='$describe',$imagesToSql where billno='$mbillno'"; 
  } else if ($flat == '2') {// 审核通过
    $sql = "update `team_reimbursement` SET `status`='2',reason='$reason',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '3') {// 审核不通过
    $sql = "update `team_reimbursement` SET `status`='3',reason='$reason',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '4') {// 撤销
    $sql = "update `team_reimbursement` SET `status`='0',reason='$reason',$signImagesToSql where billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flat不匹配', 'success'=>'0')));
  }

  if (!mysql_query($sql, $conn))
    $output = array('data'=>'', 'msg'=>'上传失败', 'success'=>'0');
  else
    $output = array('data'=>'', 'msg'=>'上传成功', 'success'=>'1');    
  exit(JSON($output));
}

// 处理合同
if ($a == 'putcontract') {
  $input = file_get_contents("php://input"); 
  $obj = json_decode($input, true);
  // 接收的参数
  $image1 = gettostr($obj['image1']);
  $image2 = gettostr($obj['image2']);
  $image3 = gettostr($obj['image3']);
  $userno = gettostr($obj['userno']);
  $username = gettostr($obj['username']);
  $customerno = gettostr($obj['customerno']);       // 客户编号
  $customername = gettostr($obj['customername']);   // 客户名称
  $title = gettostr($obj['title']);                 // 合同名称
  $contrano = gettostr($obj['contrano']);           // 合同编号
  $mbillno = gettostr($obj['billno']);              // 合同唯一编号  
  $introduction = gettostr($obj['introduction']);   // 合同描述
  $amount = gettostr($obj['amount']);               // 合同金额
  $paid = gettostr($obj['paid']);                   // 已付金额
  $contradate = gettostr($obj['contradate']);       // 合同日期  
  $checker = gettostr($obj['checker']);             // 审核人 
  $reason = gettostr($obj['reason']);               // 审核理由
  $bosssign = gettostr($obj['bosssign']);           // 老板审核签名  
  $captainsign = gettostr($obj['captainsign']);     // 队长审核签名
  $flat = gettostr($obj['flat']);

  $images = array();
  $signImages = array();
  $imagesPath = setImagePath(5, $serverurl);
  // 签名图片
  $captainsign_field = toSqlImageField('captainsign', $captainsign, $imagesPath[3]);
  $bosssign_field = toSqlImageField('bosssign', $bosssign, $imagesPath[4]);
  !!$captainsign_field && array_push($signImages, $captainsign_field);
  !!$bosssign_field && array_push($signImages, $bosssign_field);
  // 其它图片
  $image1_field = toSqlImageField('image1', $image1, $imagesPath[0]);
  $image2_field = toSqlImageField('image2', $image2, $imagesPath[1]);
  $image3_field = toSqlImageField('image3', $image3, $imagesPath[2]);
  !!$image1_field && array_push($images, $image1_field);
  !!$image2_field && array_push($images, $image2_field);
  !!$image3_field && array_push($images, $image3_field);
  // 转换
  $imagesToSql = implode(',', $images);
  $signImagesToSql = implode(',', $signImages);

  if ($flat == '0') {// 新建
    $sql = "INSERT INTO `team_contract` SET modifydate=now(),title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',username='$username',billno='$billno',
          amount='$amount',paid='$paid',introduction='$introduction',astatus='0',customername='$customername',customerno='$customerno',admin='$admin',$imagesToSql";
  } else if ($flat == '1') {// 修改
    $sql = "update `team_contract` SET title='$title',contrano='$contrano',contradate='$contradate',userno='$userno',username='$username',
          amount='$amount',paid='$paid',introduction='$introduction',customername='$customername',customerno='$customerno',admin='$admin',$imagesToSql where billno='$mbillno'"; 
  } else if ($flat == '2') {// 队长审核通过
    $sql = "UPDATE `team_contract` SET astatus='2',modifydate=now(),reason='$reason',captainno='$userno',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '3') {// 老板审核通过
    $sql = "UPDATE `team_contract` SET astatus='3',modifydate=now(),reason='$reason',bossno='$userno',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '4') {// 队长审核不通过
    $sql = "UPDATE `team_contract` SET astatus='4',modifydate=now(),reason='$reason',captainno='$userno',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '5') {// 老板审核不通过
    $sql = "UPDATE `team_contract` SET astatus='4',modifydate=now(),reason='$reason',bossno='$userno',$signImagesToSql where billno='$mbillno'";
  } else if ($flat == '6') {// 撤销
    $sql = "UPDATE `team_contract` SET astatus='0',reason='$reason',$signImagesToSql where billno='$mbillno'";
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'flat不匹配', 'success'=>'0')));
  }

  if (!mysql_query($sql, $conn))
    $output = array('data'=>'', 'msg'=>'上传失败', 'success'=>'0');
  else
    $output = array('data'=>'', 'msg'=>'上传成功', 'success'=>'1');    
  exit(JSON($output));
}

// 添加和编辑商品
if ($a == 'putwares') {
  $input = file_get_contents("php://input"); 
  $obj = json_decode($input, true);
  // 接收的参数
  $image1 = gettostr($obj['image1']);
  $image2 = gettostr($obj['image2']);
  $image3 = gettostr($obj['image3']);
  $mbillno = gettostr($obj['mbillno']);   // 修改用到
  $name = gettostr($obj['name']);         // 名称
  $price = gettostr($obj['price']);       // 单价     
  $type = gettostr($obj['type']);         // 型号
  $unit= gettostr($obj['unit']);          // 单位
  $number = gettostr($obj['number']);     // 编码
  $describe = gettostr($obj['describe']); // 描述
  $flat = gettostr($obj['flat']);         // 0添加 1修改       

  $images = array();
  $imagesPath = setImagePath(3, $serverurl);
  // 图片
  $image1_field = toSqlImageField('image1', $image1, $imagesPath[0]);
  $image2_field = toSqlImageField('image2', $image2, $imagesPath[1]);
  $image3_field = toSqlImageField('image3', $image3, $imagesPath[2]);
  !!$image1_field && array_push($images, $image1_field);
  !!$image2_field && array_push($images, $image2_field);
  !!$image3_field && array_push($images, $image3_field);
  // 转换
  $imagesToSql = implode(',', $images);

  if ($flat == '0') {// 新建
    $sql = "INSERT INTO `team_wares` SET billdate=NOW(),billno='$billno',admin='$admin',waresname='$name',unit='$unit',model='$type',
            productno='$number',price='$price',`description`='$describe',$imagesToSql";
  } else {
    $sql = "UPDATE `team_wares` SET waresname='$name',unit='$unit',model='$type',productno='$number',
            price='$price',`description`='$describe',$imagesToSql where billno='$mbillno'";  
  }

  if (!mysql_query($sql, $conn)) {
    $msg = $flat == '0' ? '新建失败' : '修改失败';
    $output = array('data'=>'', 'msg'=>$msg, 'success'=>'0');
  } else {
    $msg = $flat == '0' ? '新建成功' : '修改成功';
    $output = array('data'=>'', 'msg'=>$msg, 'success'=>'1');    
  }
  exit(JSON($output));
}

// 查询对应的消息的数量
if ($a == 'selectmesage') {
  $uid = $_GET['uid'];
          
  if ($uid == '') {
    $output = array('data'=>'', 'msg'=>'参数不能为空','success'=>'0');
    exit(JSON($output));
  }

  $section_1 = "`type`='3' AND isread='0' AND userno='$uid'";
  $section_2 = "`type`='1' AND isread='0' AND (userno='$uid' OR ispublic='1')";
  $section_3 = "`type`='0' AND isread='0' AND (userno='$uid' OR ispublic='1')";
  $section_4 = "`type`='4' AND isread='0' AND (userno='$uid' OR ispublic='1')";

  $query = "
    (SELECT *, (SELECT COUNT(*) FROM team_message WHERE $section_1) AS count, 
      '认证信息' AS informtype FROM team_message WHERE $section_1 ORDER BY billdate DESC LIMIT 1) 
    UNION ALL
    (SELECT *, (SELECT COUNT(*) FROM team_message WHERE $section_2) AS count, 
      '通知公告' AS informtype FROM team_message WHERE $section_2 ORDER BY billdate DESC LIMIT 1) 
    UNION ALL
    (SELECT *, (SELECT COUNT(*) FROM team_message WHERE $section_3) AS count, 
      '系统消息' AS informtype FROM team_message WHERE $section_3 ORDER BY billdate DESC LIMIT 1) 
    UNION ALL
    (SELECT *, (SELECT COUNT(*) FROM team_message WHERE $section_4) AS count, 
      '广告' AS informtype FROM team_message WHERE $section_4 ORDER BY billdate DESC LIMIT 1)";
 
  $result = mysql_query($query, $conn) or die(mysql_error($conn));
  $items = array();
  while ($row = mysql_fetch_assoc($result)) {
    array_push($items, $row);
  }

  $output = array('data'=>$items, 'msg'=>'获取成功', 'success'=>'1');
  exit(JSON($output));
}