<?php
// 消息

// 发送消息
if ($a == 'sendmessage') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $image = checkInput($obj['image']);
  $admin = checkInput($obj['admin']);
  $title = checkInput($obj['title']);
  $message = checkInput($obj['message']);
  $creuser = checkInput($obj['creuser']);
  $creuserno = checkInput($obj['creuserno']); // 发送人id
  $username = checkInput($obj['username']);
  $userno = checkInput($obj['userno']); // 接收人id
  $type = checkInput($obj['type']); // 消息类型

  if (!$message) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$creuserno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  // if ($type != '1') { // 目前限定只能发送 用户通知
  //   exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  // }

  $sql = "INSERT INTO`team_message` SET billno='$_billno', billdate=NOW(),creuser='$creuser',creuserno='$creuserno',title='$title'
      ,`message`='$message',username='$username',userno='$userno',`admin`='$admin',`type`='$type',`image`='$image'";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'发送失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'发送成功', 'status'=>'0')));
  }
}

// 获取消息
if ($a == 'getmessage') {
  $uid = checkInput($_GET['uid']);
  $type = checkInput($_GET['type']); // 消息类型 0聊天 1用户通知 2系统消息 3认证信息 4广告消息，传递格式为(1 或 1,2 或 1,3,4)这样
                                     // 收发消息

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if ($type == '') { // 空则取全部
    $type = '(0,1,2,3,4,5)';
  } else {
    $type = '('.$type.')';
  }

  $query = "SELECT * FROM team_message WHERE `type` IN $type AND isread='0' AND userno='$uid' ORDER BY billdate DESC";
  $result = $mysqli->query($query);

  if (!$result) {
    exit(JSON(array('data'=>'', 'msg'=>'获取消息异常', 'status'=>'1')));
  } else {
    $items = array();
    $billnos = array();
    while ($row = $result->fetch_assoc()) {
      array_push($billnos, $row['billno']);
      array_push($items, $row);
    }

    // 把消息更新为已读
    $billnosToString = join(',', $billnos);
    if ($billnosToString) {
      $sql = "UPDATE team_message SET isread='1' WHERE billno IN($billnosToString)";
      $mysqli->query($sql);
    }

    exit(JSON(array('data'=>$items, 'msg'=>'获取成功', 'status'=>'0')));
  }  
}

// 获取 聊天
if ($a == 'get_chat_msg') {
  $uid = checkInput($_GET['uid']);
  $cid = checkInput($_GET['cid']);
  $output = array(
    'head'=>array(),
    'body'=>array(),
  );

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT image FROM team_salesman WHERE userno='$uid' limit 1";
  $result1 = $mysqli->query($query);
  $row1 = $result1->fetch_assoc();
  $output['head']['image1']=$row1['image'];

  $query = "SELECT image FROM team_salesman WHERE userno='$cid' limit 1";
  $result2 = $mysqli->query($query);
  $row2 = $result2->fetch_assoc();
  $output['head']['image2']=$row2['image'];

  $query = "SELECT billdate,message,creuserno,image,userno FROM team_message WHERE `type`='0' AND ((creuserno='$cid'AND userno='$uid') OR (creuserno='$uid' AND userno='$cid')) AND isread !='-1' ORDER BY billdate DESC";
  $result = $mysqli->query($query);

  if (!$result) {
    exit(JSON(array('data'=>'', 'msg'=>'获取消息异常', 'status'=>'1')));
  } else {
    $items = array();
    while ($row = $result->fetch_assoc()) {
      array_push($output['body'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
  }  
}

// 获取个人发送的消息
if ($a == 'get_send_message') {
  $uid = checkInput($_GET['uid']);
  $output = array('list'=>array());

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "select * from `team_message` where creuserno = '$uid ' and isread !='-1' and type = '1'";
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query);

  if (!$result) {
    exit(JSON(array('data'=>'', 'msg'=>'获取消息异常', 'status'=>'1')));
  } else {
    while ($row = $result->fetch_assoc()) {
       array_push($output['list'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
  }  
}

// 删除个人发送的消息(多选)
if ($a == 'delreadmessage') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $userno = checkInput($obj['userno']); // 操作员id
  $mstr = '';
  
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  foreach ($obj['selectedRowKeys'] as $k => $v) {
   if($k>0){
   $mstr .= ',';
   }  
   $mstr .= "'$v'";
  }
  $sql = "DELETE FROM `team_message` WHERE billno IN ($mstr)";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'删除失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'删除成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 查询我的记事列表
if ($a == 'getnotelist') {
  $userno = checkInput($_GET['userno']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `team_note` WHERE `userno`='$userno'";
  if ($keyword) {
    $query .=" AND (title LIKE '%$keyword%' OR note LIKE '%$keyword%')";
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

// 添加/修改我的记事
if ($a == 'setnotemessage') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno= checkInput($obj['bno']);		//新增时为空  编辑不能为空
  $userno = checkInput($obj['userno']);
  $title =  checkInput($obj['title']);			//记录标题 /分类
  $note = checkInput($obj['note']); 			// 记录内容
 
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'userno不能为空', 'status'=>'1')));
  }
  if (!$note) {
    exit(JSON(array('data'=>'', 'msg'=>'note内容不能为空', 'status'=>'1')));
  }
  
  if ($bno) {
    $sql = "UPDATE team_note SET title='$title',note='$note' WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO team_note SET billno='$_billno',userno='$userno',title='$title',note='$note'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 删除我的记事(只能删除一条)
if ($a == 'delnote') {
  $billno = checkInput($_GET['billno']);
  
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'不能为空', 'status'=>'1')));
  }
   $sql = "DELETE FROM `team_note` WHERE billno='$billno'";
   if(!$mysqli->query($sql)){
	   exit(JSON(array('data'=>$items, 'msg'=>'删除失败!', 'status'=>'1')));
   }
    exit(JSON(array('data'=>$items, 'msg'=>'删除成功!', 'status'=>'0')));
  }  

//删除记录
if ($a == 'delnotemessage') {
  $items = checkInput($_GET['items']);		//可选择多条删除

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "DELETE FROM `team_note` WHERE billno IN ($items)";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 审核 hzz 注册 (单选)
if ($a == 'sethzzreg'){
  $status = checkInput($_GET['status']); // 0 等待通过 -1 (没通过) 1 已通过   
  $certuid = checkInput($_GET['certuid']);

  if (!$certuid) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT id FROM `team_salesman` WHERE allow='1' WHERE userno='$certuid' limit 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    exit(JSON(array('data'=>'', 'msg'=>'该账号已是货真真用户，请忽重复审批！', 'status'=>'1')));
  }

  // -1审核拒绝，0临时状态， 1 通过,   2正在审核
  $sql = "UPDATE `team_salesman` SET allow='$status',regdate2=NOW() WHERE userno='$certuid' limit 1";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'审核失败', 'status'=>'1')));
  }
    // 短信通知
  if ($status == '1') {
    $msg = '货真真商城：你通过了审核，请登录货真真商城！非本人操作，请忽略该短信！';
  } else {
    $msg = '货真真商城：你的审核不通过，请联系管理员重新审核！非本人操作，请忽略该短信！';
  }
  // 发送验证码
  require_once __DIR__.'/../utils/request.php';
  $url ='https://json.kassor.cn/sms/sendmsg.php?a=sendmsg&tel='.$certuid.'&msg='.$msg;
  try {
    $request = new Request();
    $result = json_decode($request->get($url), true);
    if ($result['success'] == '1') {
      $mysqli->query('COMMIT');
      $mysqli->query('END');
      // exit(JSON(array('data'=>'', 'msg'=>'请注意查收短信，有效期10分钟', 'status'=>'0')));
    } else {
      $msg = $result['msg'];
    }
  } catch (Exception $e) {
    $msg = $e->getMessage();
  } 
    exit(JSON(array('data'=>'', 'msg'=>'审核成功', 'status'=>'0')));
  } 
// 审核 hzz 注册 (多选)
if ($a == 'sethzzregall') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $status = checkInput($obj['status']);
  $mstr = '';
  
  if (!$status) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  foreach ($obj['selectedRowKeys'] as $k => $v) {
   if($k>0){
   $mstr .= ',';
   }  
   $mstr .= "'$v'";
  }
  // -1审核拒绝，0临时状态， 1 通过,   2正在审核
  $sql = "UPDATE `team_salesman` SET allow='$status',regdate2=NOW() WHERE userno IN ($mstr)";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'审批失败', 'status'=>'1');
  } else {
  // 短信通知
  if ($status == '1') {
    $msg = '货真真商城：你通过了审核，请登录货真真商城！非本人操作，请忽略该短信！';
  } else {
    $msg = '货真真商城：你的审核不通过，请联系管理员重新审核！非本人操作，请忽略该短信！';
  }
  // 发送验证码
  require_once __DIR__.'/../utils/request.php';
  foreach ($obj['selectedRowKeys'] as $k => $v) {
   $url ='https://json.kassor.cn/sms/sendmsg.php?a=sendmsg&tel='.$v.'&msg='.$msg;
    try {
      $request = new Request();
      $result = json_decode($request->get($url), true);
      if ($result['success'] == '1') {
        $mysqli->query('COMMIT');
        $mysqli->query('END');
        // exit(JSON(array('data'=>'', 'msg'=>'请注意查收短信，有效期10分钟', 'status'=>'0')));
      } else {
        $msg = $result['msg'];
      }
    } catch (Exception $e) {
      $msg = $e->getMessage();
    } 
  } 
    $output = array('data'=>'', 'msg'=>'审批成功', 'status'=>'0');
  }
  exit(JSON($output));
}



