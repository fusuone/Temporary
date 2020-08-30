<?php
// 打印业务

// 出库打印
if ($a == 'printstockout') {
  exit(JSON(array('data'=>'', 'msg'=>'已失效', 'status'=>'1')));


  exit(JSON($output));
}



// 卖单打印
if ($a == 'printmallorder') {
  exit(JSON(array('data'=>'', 'msg'=>'已失效', 'status'=>'1')));


  exit(JSON($output));
}

// hzz 商城 进出货打印
if ($a == 'printmalllist') {
  $billno = checkInput($_GET['billno']); // mall_head billno
  $userno = checkInput($_GET['userno']);
  $flag = checkInput($_GET['flag']); // 0进货 1出货
  $number = checkInput($_GET['number']); // 次数

  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
   if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

   // 用户信息
  $query = "SELECT printersn FROM team_salesman WHERE userno='$userno' LIMIT 1";
  $result = $mysqli->query($query);
  $userInfo = $result->fetch_assoc();
  if (mysqli_num_rows($result) <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'用户不存在', 'status'=>'1')));
  }
  $printersn = $userInfo['printersn'];

  // 打印机是否连接
  $result = printerStatus($printersn);
  if ($result['status'] != '0') {
    exit(JSON(array('data'=>'', 'msg'=>'打印机状态异常', 'status'=>'1')));
  }
  // 查询单头的信息
  $query = "SELECT billdate,salername,buyername,amount,c_address,c_linkman,c_tel FROM `mall_orderhead` WHERE billno='$billno' LIMIT 1";
  $result = $mysqli->query($query);
  $mallhead = $result->fetch_assoc();
  if (mysqli_num_rows($result) <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单不存在', 'status'=>'1')));
  }

  // 打印的头部订单信息
  $printInfo = array(
  	'salername' => $mallhead['salername'],
    'billdate' => $mallhead['billdate'],
    'buyername' => $mallhead['buyername'],
    'amount' => $mallhead['amount'],
    'c_address' => $mallhead['c_address'],
    'c_linkman' => $mallhead['c_linkman'],
    'c_tel' => $mallhead['c_tel'],
    'list' => array()
  );

  $query = "SELECT warename,unit,qty,price FROM `mall_orderbody` WHERE orderno='$billno' ";

  $result = $mysqli->query($query) or die($mysqli->error);
  $key = 1;
  while ($item = $result->fetch_assoc()) {
     array_push(
      $printInfo['list'],
      array('id' => $key++, 'warename' => $item['warename'], 'unit' => $item['unit'],'qty' => $item['qty'], 'price' => $item['price'],'amount' => sprintf('%01.2f', $item['qty']*$item['price']))
    );
  }

  $result = printHzzOrder($printersn, $printInfo, 1, $flag, $number);
  if ($result['status'] == '0') {
    exit(JSON(array('data'=>'', 'msg'=>'正在打印中', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$result['msg'], 'status'=>'1')));
  }
}

// 平板 --  打印 扫描商品
if ($a == 'print_hd_wares') {
  $userno = checkInput($_GET['userno']);
  $username = checkInput($_GET['username']);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

   // 用户信息
  $query = "SELECT printersn FROM team_salesman WHERE userno='$userno' LIMIT 1";
  $result = $mysqli->query($query);
  $userInfo = $result->fetch_assoc();
  if (mysqli_num_rows($result) <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'用户不存在', 'status'=>'1')));
  }
  $printersn = $userInfo['printersn'];

  // 打印机是否连接
  $result = printerStatus($printersn);
  if ($result['status'] != '0') {
    exit(JSON(array('data'=>'', 'msg'=>'打印机状态异常', 'status'=>'1')));
  }

  // 打印的头部订单信息
  $printInfo = array(
    'username' => $username,
    'userno' => $userno,
    'date' => date("Y-m-d H:i:s"),
    'list' => array()
  );

  $query = "SELECT waresname,price,unit,qnum FROM mall_sellwares WHERE userno = '$userno' AND isprint=0 ";
  $result = $mysqli->query($query) or die($mysqli->error);
  $key = 1;
  while ($item = $result->fetch_assoc()) {
     array_push(
      $printInfo['list'],
      array('id' => $key++, 'warename' => $item['waresname'], 'qty' => $item['qnum'], 'price' => $item['price'],'amount' => sprintf('%01.2f', $item['qnum']*$item['price']))
    );
  }

  $result = printHzzOrder($printersn, $printInfo);
  if ($result['status'] == '0') {
    exit(JSON(array('data'=>$result['data'], 'msg'=>'正在打印中', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$result['msg'], 'status'=>'1')));
  }
}



