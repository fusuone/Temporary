<?php
// 商城
// 商城首页数据
if ($a == 'getmall') {
  $usercode = checkInput($_GET['usercode']); // 用户billno
  $userno = checkInput($_GET['userno']); // 用户userno
  $output = array(
    'banner'=>array(),
    'industry'=>array(),
    'merchants'=>array(),
	  'myfollow'=>array(),
    'product'=>array(),
  );

  // banner
  $query = "SELECT ad_name, ad_link, ad_code,ad_bno,img_type FROM `team_mall_ad` WHERE `enabled`=1 AND position_id=0 AND media_type=0 AND ad_type=1 ORDER BY billdate DESC LIMIT 4";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['banner'], $row);
  }

  // 行业
  $query = "SELECT `key`,name,icon FROM `mall_industry`";
  $result = $mysqli->query($query) or die($mysqli->error);
  $serverAssetsUrl = $serverConfig->getAssetsUrl();
  while ($row = $result->fetch_assoc()) {
    $row['icon'] = $serverAssetsUrl.'/icon/industry/'.$row['icon'];
    array_push($output['industry'], $row);
  }

  // 推荐企业
  $query = "SELECT billno,company,company_logo FROM `team_salesman` WHERE team>0 AND teamdate!='' AND company_show=1 ORDER BY RAND() LIMIT 8";
 
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['merchants'], $row);
  }

  // 关注推荐
  $query = "SELECT a.billno,a.image1,a.waresname,a.mallprice,b.company FROM `team_wares` a LEFT JOIN `team_salesman` b ON a.admin=b.userno WHERE a.`status`=0 
          AND a.admincode IN(
         SELECT  favno FROM `mall_fav` WHERE userno='$usercode' AND used=1) ORDER BY RAND() LIMIT 6";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['myfollow'], $row);
  }
  
  // 推荐产品
  $query = "SELECT a.billno,a.image1,a.waresname,a.mallprice,b.company FROM `team_wares` a LEFT JOIN `team_salesman` b ON a.admin=b.userno WHERE a.`status`!=-1 AND company_show=1 ORDER BY RAND() LIMIT 10";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['product'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 获取商城中的推荐企业
if ($a == 'get_tj_shop') {
  $query = "SELECT billno,company,image,company_logo FROM `team_salesman` WHERE team>0 AND teamdate!='' AND company_show=1  ORDER BY RAND() LIMIT 8";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = array();
  while ($row = $result->fetch_assoc()) {
    array_push($data, $row);
  }

  exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
}

// 获取商城中的关注推荐
if ($a == 'get_flow_prodect') {
  $usercode = checkInput($_GET['usercode']); // 用户billno
  $query = "SELECT * FROM `team_wares` WHERE `status`=0 AND admin IN(
   SELECT userno FROM `team_salesman` WHERE billno IN (SELECT  favno FROM `mall_fav` WHERE userno='$usercode' AND used=1)) ORDER BY RAND() LIMIT 6";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = array();
  while ($row = $result->fetch_assoc()) {
    array_push($data, $row);
  }

  exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
}

// 获取商城中的推荐产品
if ($a == 'get_tj_product') {
  $output = array('list'=>array(), 'total'=>0);
  if ($page > 5) {
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
  }

  $query = "SELECT sql_calc_found_rows a.*,b.company FROM `team_wares` a LEFT JOIN `team_salesman` b ON a.admin=b.userno WHERE a.`status`!=-1 AND b.company_show=1 ORDER BY RAND() LIMIT 50";
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 获取商家信息
if ($a == 'getmerchantinfo') {
  $usercode = checkInput($_GET['usercode']);
  $shopcode = checkInput($_GET['shopcode']);
  $output = array(
    'info'=>array(),
    'product'=>array(),
  );

  if (!$shopcode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT a.*, b.name AS industry FROM `team_salesman` a LEFT JOIN `mall_industry` b ON a.industry=b.key WHERE a.billno='$shopcode' AND a.company_show=1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $info = $result->fetch_assoc();
  if (!$info) {
    exit(JSON(array('data'=>'', 'msg'=>'商城未启用', 'status'=>'1')));
  }
  $output['info'] = $info;

  // 是否收藏
  $output['info']['isfav'] = '0';
  if ($usercode) {
    $query = "SELECT * FROM `mall_fav` WHERE userno='$usercode' AND favno='$shopcode' AND used=1 LIMIT 1";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
      $output['info']['isfav'] = '1'; 
    }
  }
  // 是否已认证
    $query = "SELECT styles FROM `team_license` WHERE ubillno='$shopcode' LIMIT 1";
    $result = $mysqli->query($query);
    $mrow = $result->fetch_assoc();
    if (!$mrow) {
      $output['info']['isrenz'] = '0';
    } else {
      $output['info']['isrenz'] = $mrow['styles']; 
    }

  // 产品
  $query = "SELECT * FROM `team_wares` WHERE `status`!=-1 AND `admin`='{$info['userno']}' limit 50";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['product'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// 获得订单信息
if ($a == 'getmallorder') {
  $admin = checkInput($_GET['admin']);
   $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_order` WHERE admin='$admin'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));	
}

//获取订单状态信息
if ($a == 'getbillstate') {
    $admin = checkInput($_GET['admin']);
    $billstate = checkInput($_GET['billstate']);
    $output = array('list'=>array(), 'total'=>0);

    if (!$admin) {
        exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }

    $query = "SELECT * FROM `mall_order` WHERE admin='$admin'and billstate='$billstate'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
    $output['total'] = $totalResult->fetch_assoc()['total'];
    while ($row = $result->fetch_assoc()) {
        array_push($output['list'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 设置商城订单，接收商城订单记录
if ($a == 'set_mallorder') {
	$input = file_get_contents('php://input');
  $obj = json_decode($input, 1);

  // 表头内容
  $mallno = checkInput($obj['head']['salerno']);		//卖方即是商城号
  $salername = checkInput($obj['head']['salername']);
  $salerlogo = checkInput($obj['head']['salerlogo']);
  $buyerno = checkInput($obj['head']['buyerno']);
  $buyername = checkInput($obj['head']['buyername']);
  $buyeravatar = checkInput($obj['head']['buyeravatar']);
  $remark = checkInput($obj['head']['remark']);
  $c_address = checkInput($obj['head']['c_address']);
  $c_linkman = checkInput($obj['head']['c_linkman']);
  $c_tel = checkInput($obj['head']['c_tel']);
  $payway = checkInput($obj['head']['payway']);

  $num = 0;
  $amount = 0;
  $cartnoArr = array();
  $orderhead_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单头编号

  // 表体内容
  $sqlarr = array();
  foreach ($obj['body'] as $k => $v) {
    $cartno = checkInput($v['cartno']);
    $wareno = checkInput($v['wareno']);
    $warename = checkInput($v['warename']);
    $wareimage = checkInput($v['wareimage']);
    $price = checkInput($v['price']);
    $qty = checkInput($v['qty']);

    $num += intval($qty);
    $amount += intval($qty) * floatval($price);
    if ($cartno) {
      array_push($cartnoArr, $cartno);
    }
    $orderbody_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单体编号
    array_push($sqlarr, "('$orderbody_billno',NOW(),'$orderhead_billno','$mallno','$wareno','$warename','$wareimage','$price','$qty')");
  }
  $amount = number_format($amount, 2, '.', '');

  if (count($sqlarr) == 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单体不能为空', 'status'=>'1')));
  }

  $mysqli->query('BEGIN');
  $sql1 = "INSERT INTO mall_orderhead SET billno='$orderhead_billno',billdate=NOW(),salerno='$mallno',salername='$salername',salerlogo='$salerlogo',buyerno='$buyerno',buyername='$buyername',buyeravatar='$buyeravatar',remark='$remark',c_address='$c_address',c_linkman='$c_linkman',c_tel='$c_tel',qty='$num',amount='$amount',payway='$payway'";
  $sql2 = "INSERT INTO mall_orderbody(billno,billdate,orderno,mallno,wareno,warename,wareimage,price,qty) VALUES".implode(',', $sqlarr);
  $t1 = $mysqli->query($sql1);
  $t2 = $mysqli->query($sql2);

  if ($t1 && $t2) {
    $orderInfo = array();
    $orderInfo['platform'] = $_p;
    $orderInfo['openid'] = ''; // app 支付不需要
    $orderInfo['body'] = $salername ? $salername : '暂无名称';
    $orderInfo['out_trade_no'] = $orderhead_billno;
    $orderInfo['total_fee'] = $amount;
    $r = _unifiedOrder($orderInfo);
    if ($r['status'] == '0') {
      // 删除购物车的记录
      if (count($cartnoArr) > 0) {
        $cartnoJoin = implode(',', $cartnoArr);
        $sql3 = "UPDATE mall_buycar SET `status`=0 WHERE billno IN ($cartnoJoin)";
        $mysqli->query($sql3);
      }
      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>$r['data'], 'msg'=>$r['msg'], 'status'=>'0')));
    } else {
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>$r['msg'], 'status'=>'1')));
    } 
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
  }
}

// team 从订单管理页发起的支付(等待支付的订单)
if ($a == 'payorder') {
  $orderno = checkInput($_GET['orderno']);

  if (!$orderno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 查询出该订单
  $query = "SELECT * FROM mall_orderhead WHERE billstate=0 AND billno='$orderno'";
  $result = $mysqli->query($query);
  $data = $result->fetch_assoc();
  if (!$data) {
    exit(JSON(array('data'=>'', 'msg'=>'订单不存在或已失效', 'status'=>'1')) );
  }
  if ($data['amount'] < 0.01) {
    exit(JSON(array('data'=>'', 'msg'=>'金额至少0.01元', 'status'=>'1')));
  }

  // 统一下单处理
  $orderInfo = array();
  $orderInfo['platform'] = $_p;
  $orderInfo['openid'] = ''; // app 支付不需要
  $orderInfo['body'] = $data['salername'] ? $data['salername'] : '暂无名称';
  $orderInfo['out_trade_no'] = $data['billno'];
  $orderInfo['total_fee'] = $data['amount'];
  $r = _unifiedOrder($orderInfo);
  if ($r['status'] == '0') {
    exit(JSON(array('data'=>$r['data'], 'msg'=>$r['msg'], 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$r['msg'], 'status'=>'1')));
  }
}

// hzz 从订单管理页发起的支付(等待支付的订单)
if ($a == 'hzzpayorder') {
  $orderno = checkInput($_GET['orderno']);

  if (!$orderno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // 查询出该订单
  $query = "SELECT *,(amount-fullcut-redtip-coupon) AS payrmb FROM mall_orderhead WHERE billstate=0 AND billno='$orderno'";
  $result = $mysqli->query($query);
  $data = $result->fetch_assoc();
  if (!$data) {
    exit(JSON(array('data'=>'', 'msg'=>'订单不存在或已失效', 'status'=>'1')) );
  }
  if ($data['payrmb'] < 0.01) {
    exit(JSON(array('data'=>'', 'msg'=>'金额至少0.01元', 'status'=>'1')));
  }

  // 统一下单处理
  $orderInfo = array();
  $orderInfo['platform'] = $_p;
  $orderInfo['openid'] = ''; // app 支付不需要
  $orderInfo['body'] = $data['salername'] ? $data['salername'] : '暂无名称';
  $orderInfo['out_trade_no'] = $data['billno'];
  $orderInfo['total_fee'] = $data['payrmb'];
  $r = _unifiedOrder($orderInfo);
  if ($r['status'] == '0') {
    exit(JSON(array('data'=>$r['data'], 'msg'=>$r['msg'], 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$r['msg'], 'status'=>'1')));
  }
}

// 列取我的收件/发件地址
if ($a == 'get_contact_list') {
  $userno = checkInput($_GET['userno']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "select * from mall_contact where userno='$userno' order by def desc";
  $result = $mysqli->query($query) or die($mysqli->error);

  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// 设置收件/发件地址
if ($a == 'set_contact') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $billno=	@$obj['billno'] ? checkInput($obj['billno']) : '';
  $userno = checkInput($obj['userno']);
  $companyname = checkInput($obj['companyname']);
  $linkman = checkInput($obj['linkman']);
  $address = checkInput($obj['address']);
  $tel = checkInput($obj['tel']);
  $flag = checkInput($obj['flag']);    //0 添加  1 修改 2 删除 3 设置默认

  if ($flag != '0' && $flag != '1' && $flag != '2' && $flag != '3') {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') {
	  $sql ="insert into mall_contact set billno='$_billno',userno='$userno',companyname='$companyname',linkman='$linkman',address='$address',tel='$tel'";
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
    }
    exit(JSON(array('data'=>'', 'msg'=>'添加成功', 'status'=>'0')));
  } else if ($flag == '1') {
	  $sql ="update mall_contact set companyname='$companyname',linkman='$linkman',address='$address',tel='$tel' where userno='$userno' and billno='$billno'";
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'修改失败', 'status'=>'1')));
    }
    exit(JSON(array('data'=>'', 'msg'=>'修改成功', 'status'=>'0')));
  } else if ($flag == '2') {
    if (!$billno) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
    $sql = "DELETE FROM mall_contact WHERE billno='$billno'";
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
    }
    exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
  } else if ($flag == '3') {
    if (!$billno) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
    $mysqli->query('BEGIN');
    $sql1 = "UPDATE mall_contact SET def=1 WHERE billno='$billno'";
    $sql2 = "UPDATE mall_contact SET def=0 WHERE billno!='$billno' AND userno='$userno'";
    $t1 = $mysqli->query($sql1);
    $t2 = $mysqli->query($sql2);
    if ($t1 && $t2) {
      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
    } else {
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'0')));
    }
  }
}



// 我的关注
if ($a == 'get_fav') {
  $usercode = checkInput($_GET['usercode']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname,b.companyaddress AS shopaddress,a.used AS isfav FROM `mall_fav` a LEFT JOIN `team_salesman` b ON a.favno=b.billno
    WHERE a.userno='$usercode' AND a.used=1 ORDER BY a.billdate DESC";
  $result = $mysqli->query($query) or die($mysqli->error);

  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 关注/取消关注
if ($a == 'set_fav') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $usercode = checkInput($obj['usercode']);
  $shopcode = checkInput($obj['shopcode']);

  if (!$usercode || !$shopcode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_fav` WHERE userno='$usercode' AND favno='$shopcode' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if ($r) {
    $used;
    $msg;
    if ($r['used'] == '1') {
      $used = '0';
      $msg = '已取消关注';
    } else {
      $used = '1';
      $msg = '已关注';
    }
    // 关注/取消关注
    $sql = "UPDATE `mall_fav` SET used='$used' WHERE id='{$r['id']}'";
    if ($mysqli->query($sql)) {
      exit(JSON(array('data'=>array('isfav'=>$used), 'msg'=>$msg, 'status'=>'0')));
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
    }
  } else {
    // 新增关注
    $sql = "INSERT INTO `mall_fav` SET userno='$usercode',favno='$shopcode',used=1,billdate=NOW()";
    if ($mysqli->query($sql)) {
      exit(JSON(array('data'=>array('isfav'=>'1'), 'msg'=>'已关注', 'status'=>'0')));
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
    }
  }
}

// 获取评价的信息
if ($a == 'get_appraise') {
  $userno = checkInput($_GET['userno']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows t1.*,t2.wareno,t2.warename,t2.wareimage,t2.price AS wareprice FROM `team_appraise` t1 LEFT JOIN `mall_orderbody` t2 ON t1.b_orderno=t2.billno WHERE t1.userno='$userno' AND t1.statu='0' ORDER BY t1.billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 获取待评价订单
if ($a == 'get_unappraise') {
  $orderno = checkInput($_GET['orderno']);

  if (!$orderno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_orderhead` WHERE billno='$orderno' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if (!$r) {
    exit(JSON(array('data'=>'', 'msg'=>'不存在', 'status'=>'1')));
  }
  if ($r['billstate'] != '6') {
    exit(JSON(array('data'=>'', 'msg'=>'不能评价', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_orderbody` WHERE orderno='$orderno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $orderBodyList = array();
  while ($row = $result->fetch_assoc()) {
    array_push($orderBodyList, $row);
  }
  if (count($orderBodyList) <=0) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无评价商品', 'status'=>'1')));
  }
  exit(JSON(array('data'=>$orderBodyList, 'msg'=>'ok', 'status'=>'0')));
}

// 填写评论
if ($a == 'set_appraise') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $userno = checkInput($obj['userno']);
  $username = checkInput($obj['username']);
  $useravatar = checkInput($obj['useravatar']);
  $shopno = checkInput($obj['shopno']);
  $shopname = checkInput($obj['shopname']);
  $shoplogo = checkInput($obj['shoplogo']);
  $h_orderno = checkInput($obj['h_orderno']);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$shopno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM mall_orderhead WHERE billno='$h_orderno' AND billstate=6 LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if (!$r) {
    exit(JSON(array('data'=>'', 'msg'=>'已经评价了', 'status'=>'1')));
  }

  $sqlarr = array();
  foreach ($obj['list'] as $item) {
    $b_orderno = checkInput($item['b_orderno']);
    $wareno = checkInput($item['wareno']);
    $content = checkInput($item['content']);
    $pic1 = checkInput($item['pic1']);
    $pic2 = checkInput($item['pic2']);
    $pic3 = checkInput($item['pic3']);
    $bno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999);
    array_push($sqlarr, "('$bno',NOW(),'$userno','$username','$useravatar','$shopno','$shopname','$shoplogo','$wareno','$h_orderno','$b_orderno','$content','$pic1','$pic2','$pic3')");
  }

  if (count($sqlarr) == 0) {
    exit(JSON(array('data'=>'', 'msg'=>'错误', 'status'=>'1')));
  }

  $mysqli->query('BEGIN');
  $sql1 = "UPDATE mall_orderhead SET billstate=7 WHERE billno='$h_orderno'";
  $sql2 = "INSERT INTO team_appraise(billno,billdate,userno,username,useravatar,shopno,shopname,shoplogo,wareno,h_orderno,b_orderno,content,pic1,pic2,pic3) VALUES".implode(',', $sqlarr);
  $t1 = $mysqli->query($sql1);
  $t2 = $mysqli->query($sql2);

  if ($t1 && $t2) {
    $mysqli->query('COMMIT');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'评论成功', 'status'=>'0')));
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'评论失败', 'status'=>'1')));
  }
}

// 获取商品的评价列表
if ($a == 'get_goodsappraise') {
  $goodsno = checkInput($_GET['goodsno']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$goodsno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `team_appraise` WHERE wareno='$goodsno' AND statu='0' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    $row['username'] = noiseUserName($row['username']);
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 我的买客列表
if ($a == 'get_contact_in') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']); // 0|1
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') {
    $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname,b.companyaddress AS shopaddress FROM `team_salesman` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockin WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
  } else {
    $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname,b.companyaddress AS shopaddress FROM `team_salesman` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockout WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}



// 我的买客列表
if ($a == 'get_partner_list') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']); // 0|1
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if ($flag != '0' && $flag != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '0') {
    // $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.image AS shoplogo,b.company AS shopname,b.companyaddress AS shopaddress FROM `team_salesman` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockin WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
     $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.image AS shoplogo,b.title AS shopname,b.address AS shopaddress FROM `team_customer` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockin WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
 } else {
    // $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.image AS shoplogo,b.company AS shopname,b.companyaddress AS shopaddress FROM `team_salesman` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockout WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
      $query = "SELECT sql_calc_found_rows b.billno AS shopcode,b.image AS shoplogo,b.title AS shopname,b.address AS shopaddress FROM `team_customer` b WHERE billno IN (SELECT DISTINCT customerno FROM team_stockout WHERE admin='$admin' AND way =1 AND STATUS = 0 ORDER BY billdate DESC)".$paging;
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}




// 取得买单列表
if ($a == 'get_orderin_list') {
  $userno = checkInput($_GET['userno']);
  $flag = checkInput($_GET['flag']); // -1 删除 0 待付款 2 已付款/待发货 3 待收货 4 用户取消 5 待付款超时 6 待评价/确认收货
  $output = array('list'=>array(), 'total'=>0);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!in_array($flag, array('-1','0','2','3','4','5','6','99'))) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $billstate = "AND billstate='$flag'";
  if ($flag == '99') {
    $billstate = "";
  }
  $query = "SELECT sql_calc_found_rows *,(amount-fullcut-redtip-coupon) AS payrmb FROM mall_orderhead WHERE buyerno='$userno' $billstate ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 取得卖单列表
if ($a == 'get_orderout_list') {
  $userno = checkInput($_GET['userno']);
  $flag = checkInput($_GET['flag']); // -1 删除 0 待付款 2 已付款/待发货 3 待收货 4 用户取消 5 待付款超时 6 待评价/确认收货
  $output = array('list'=>array(), 'total'=>0);

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!in_array($flag, array('-1','0','2','3','4','5','6','99'))) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $billstate = "AND billstate='$flag'";
  if ($flag == '99') {
    $billstate = "";
  }
  $query = "SELECT sql_calc_found_rows * FROM mall_orderhead WHERE salerno='$userno' $billstate ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// team 订单详情, hzz 商品详情
if ($a == 'orderdetail') {
  $orderno = checkInput($_GET['orderno']);

  if (!$orderno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_orderbody` WHERE orderno='$orderno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  
  $data = array();
  while ($row = $result->fetch_assoc()) {
    array_push($data, $row);
  }

  if ($data) {
    exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 更改订单状态
if ($a == 'set_orderstatus') {
  $userno = checkInput($_GET['userno']);
  $orderno = checkInput($_GET['orderno']);
  $flag = checkInput($_GET['flag']); // 0 取消订单 1 确认收货 2 发货(物流), 3 发货（送货上门）

  if (!in_array($flag, array('0', '1', '2', '3'))) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$orderno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `mall_orderhead` WHERE billno='$orderno' AND billstate!=-1 LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if (!$r) {
    exit(JSON(array('data'=>'', 'msg'=>'该订单不存在', 'status'=>'1')));
  }

  if ($flag == '0') {
    if ($r['billstate'] == '0') {
      $sql = "UPDATE `mall_orderhead` SET billstate=4 WHERE billno='$orderno' AND billstate!=-1";
      if ($mysqli->query($sql)) {
        exit(JSON(array('data'=>'', 'msg'=>'取消成功', 'status'=>'0')));
      } else {
        exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
      }
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'商品已发货,不能取消订单', 'status'=>'1')));
    }
  } else if ($flag == '1') {
    $sql = "UPDATE `mall_orderhead` SET billstate=6 WHERE billno='$orderno' AND billstate!=-1";
    if ($mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'收货成功', 'status'=>'0')));
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
    }
  } else if ($flag == '2') { // 发货(物流)
    $express_no = checkInput($_GET['express_no']);
    $express_company = checkInput($_GET['express_company']);
    if (!$express_no || !$express_company) {
      exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
    }
    if ($r['billstate'] == 3) {
      exit(JSON(array('data'=>'', 'msg'=>'请勿重复发货', 'status'=>'1')));
    }
    $sql = "UPDATE `mall_orderhead` SET express_no='$express_no',express_company='$express_company',billstate=3 WHERE billno='$orderno' AND billstate!=-1";
    if ($mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'发货成功', 'status'=>'0')));
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
    }
  } else if ($flag == '3') { // 发货（送货上门）
    $sendname = checkInput($_GET['sendname']);
    $sendno = checkInput($_GET['sendno']);
    if ($r['billstate'] == 3) {
      exit(JSON(array('data'=>'', 'msg'=>'请勿重复发货', 'status'=>'1')));
    }
    $sql = "UPDATE `mall_orderhead` SET sendname='$sendname',sendno='$sendno',billstate=3 WHERE billno='$orderno' AND billstate!=-1";
    if ($mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'发货成功', 'status'=>'0')));
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
    }
  }
}

//team 商品详情
if ($a == 'goodsdetail') {
  $goods_no = checkInput($_GET['goods_no']);

  if (!$goods_no) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT a.*,b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname FROM `team_wares` a LEFT JOIN `team_salesman` b ON a.admin=b.userno WHERE a.billno='$goods_no' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $goodsInfo = $result->fetch_assoc();
  if (!$goodsInfo) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM team_appraise WHERE wareno='$goods_no' AND statu='0' ORDER BY billdate DESC LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $appraiseInfo = $result->fetch_assoc();
  if ($appraiseInfo) {
    $result = $mysqli->query("SELECT found_rows() AS total") or die($mysql->error);
    $appraiseInfo['username'] = noiseUserName($appraiseInfo['username']);
    $appraiseInfo['total'] = $result->fetch_assoc()['total'];
    $goodsInfo['appraise'] = $appraiseInfo;
  }

  exit(JSON(array('data'=>$goodsInfo, 'msg'=>'ok', 'status'=>'0')));
}

// 获取行业
if ($a == 'get_industry_list') {
  $output = array('list'=>array(), 'total'=>0);

  $query = "SELECT * FROM `mall_industry`";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 搜索企业、产品
if ($a == 'searchshop') {
  $keyword = checkInput($_GET['keyword']);
  $industry_key = checkInput($_GET['industry_key']);
  $flag = checkInput($_GET['flag']); // 1：行业搜索 2：企业名搜索
  $output = array('list'=>array(), 'total'=>0);

  if ($flag != '1' && $flag != '2') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  if ($flag == '1') {
    $query = "SELECT sql_calc_found_rows * FROM `team_salesman` WHERE `industry`='$industry_key' AND company_show=1".$paging;
  } else {
    $query = "SELECT sql_calc_found_rows * FROM `team_salesman` WHERE company_show=1 AND company LIKE '%$keyword%'".$paging;
  }

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// team 获取购物车列表
if ($a == 'get_buycar_list') {
  $userno=checkInput($_GET['userno']);
  $output = array('list'=>array(), 'total'=>0);

  $query = "SELECT a.billno,a.warename,a.qty,a.price,b.* FROM `mall_buycar` a JOIN
  (SELECT t1.billno AS shopcode,t1.company AS shopname,t1.company_logo AS shoplogo,t2.billno AS wareno,t2.image1 AS wareimage FROM `team_salesman` t1 JOIN `team_wares` t2 ON t1.userno=t2.admin) b
  ON a.wareno=b.wareno WHERE a.userno='$userno' AND a.`status`=1";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// hzz 获取购物车列表
if ($a == 'get_hzz_buycar') {
  $userno=checkInput($_GET['userno']); // 用户的billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT a.billno,a.warename,a.qty,a.price,b.* FROM `mall_buycar` a JOIN
  (SELECT t1.billno AS shopcode,t1.company AS shopname,t1.company_logo AS shoplogo,t1.litrmb AS litrmb,t2.billno AS mbillno,t2.wareno AS wareno,t2.image1 AS wareimage,t2.`status` AS mstatus,t2.onsale AS honsale FROM `team_salesman` t1 JOIN `mall_wares` t2 ON t1.billno=t2.admincode) b
  ON a.mbillno=b.mbillno WHERE a.userno='$userno' AND a.`status`=1 AND a.qty !=0 AND b.mstatus='0' AND honsale='1'";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// hzz 检查购物车的库存
if ($a == 'select_hzzbcar_qty') {
  $userno=checkInput($_GET['userno']); // 用户的billno
  $shopno=checkInput($_GET['shopno']); // 商家 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT a.warename,a.qty,b.qty AS kqty,b.unit,(a.qty <= b.qty) AS isqty FROM `mall_buycar` a LEFT JOIN `mall_wares` b ON a.wareno=b.wareno WHERE a.userno='$userno' AND b.admincode='$shopno' AND a.`status`=1";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// hzz 查询  立即购买  商品 的库存
if ($a == 'select_hzzbuy_qty') {
  $shopno=checkInput($_GET['shopno']); // 商品mall billno
  $output = array('list'=>array(), 'total'=>0);
  $query = "SELECT a.billno,a.billdate,a.admin,a.admincode,a.wareno,a.waresname,a.waretype,a.model,a.price,a.retailprice,a.unit,a.`makedate`,a.place,a.warranty,a.buylimit,
           a.`envelope`,a.ticket,a.net,a.productno,a.description,a.image1,a.image2,a.image3,a.image4,a.image5,a.image6,a.qty,a.qty1,a.qty2,a.qty3,a.points,a.ispoint,
           a.waresname2,a.series,a.brand,a.price2,a.mallprice,a.destp2,a.img1,a.img2,a.img3,a.img4,a.img5,a.img6,a.category1,b.billno AS shopcode,b.company_logo AS shoplogo,
           b.company AS shopname,b.litrmb AS litrmb FROM `view_hzzwares` a LEFT JOIN `team_salesman` b ON a.admincode=b.billno 
           where a.billno='$shopno' AND a.`status`=0 AND a.honsale='1' ORDER BY RAND() LIMIT 1";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// team 添加购物车
if ($a == 'set_buycar') {
  $userno=	checkInput($_GET['userno']);
  $wareno=checkInput($_GET['wareno']);
  $warename=checkInput($_GET['warename']);
  $qty= checkInput($_GET['qty']);
  $price = checkInput($_GET['price']);
  $flag = 0;

  $query = "SELECT * FROM mall_buycar WHERE userno='$userno' AND wareno='$wareno' AND `status`=1";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows > 0) {
	 $flag=1; 
     $sql = "update mall_buycar set qty=qty+$qty,price=$price where userno='$userno' AND wareno='$wareno' AND `status`=1";
  } else {
	 $flag=0; 
	 $sql = "insert into  mall_buycar set qty=$qty,price=$price, billno='$_billno',warename='$warename',userno='$userno',wareno='$wareno'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $flag ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $flag ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// hzz 添加购物车
if ($a == 'set_hzzbuycar') {
  $userno=  checkInput($_GET['userno']);
  $mbillno=checkInput($_GET['mbillno']);  // mall_wares billno
  $wareno=checkInput($_GET['wareno']);
  $warename=checkInput($_GET['warename']);
  $qty= checkInput($_GET['qty']);
  $price = checkInput($_GET['price']);
  $flag = 0;
 
  $query = "SELECT * FROM mall_buycar WHERE userno='$userno' AND mbillno='$mbillno' AND `status`=1 LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row) {
    $mbino = $row['billno'];
   $flag=1; 
     $sql = "update mall_buycar set qty=qty+$qty,price=$price where billno='$mbino'";
  } else {
   $flag=0; 
   $sql = "insert into  mall_buycar set qty=$qty,price=$price, billno='$_billno',warename='$warename',userno='$userno',wareno='$wareno',mbillno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $flag ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $flag ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));

}


//删除购物车记录
if ($a == 'del_buycar') {
	$userno= checkInput($_GET['userno']);
	$billno= checkInput($_GET['billno']);
	$sql = "UPDATE mall_buycar SET `status`=0 WHERE userno='$userno' AND billno='$billno'";
	if (!$mysqli->query($sql)) {
	   exit(JSON(array('data'=>'', 'msg'=>'error', 'status'=>'1')));
	} 
	exit(JSON(array('data'=>'', 'msg'=>'ok', 'status'=>'0')));
	
}

// 设置商品的数量
if ($a == 'set_buycar_num') {
  $userno= checkInput($_GET['userno']);
  $billno= checkInput($_GET['billno']);
  $flat= checkInput($_GET['flat']);
  if ($flat == '0') {
    $query = "SELECT qty from `mall_buycar` where userno='$userno' AND billno='$billno' LIMIT 1";
    $result = $mysqli->query($query) or die($mysqli->error);
    $row = $result->fetch_assoc();
    if ($row['qty'] == 1){
        exit(JSON(array('data'=>'', 'msg'=>'只剩下最后一个商品！', 'status'=>'1')));
    }
    $sql = "UPDATE mall_buycar SET qty=qty-1 WHERE userno='$userno' AND billno='$billno'";
    if (!$mysqli->query($sql)) {
       exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
    } 
  } else {
    $sql = "UPDATE mall_buycar SET qty=qty+1 WHERE userno='$userno' AND billno='$billno'";
      if (!$mysqli->query($sql)) {
         exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
    } 
  }
  exit(JSON(array('data'=>'', 'msg'=>'ok', 'status'=>'0')));
}

//实名认证
if($a == 'check_realname'){
  $userno = checkInput($_GET['userno']);
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $data = array('list'=>array());
  $sql = "SELECT * FROM team_license WHERE styles='1' ORDER BY billdate DESC";
  $result = $mysqli->query($sql);
  $a = 0;
  while ($row = $result->fetch_assoc()) {
    $a += 1;
    $row["key"] = $a;
    array_push($data['list'], $row);
  }
  exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
}

//设置实名审核状态
if($a == 'set_realname'){
  $userno = checkInput($_GET['userno']);
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $billno = checkInput($_GET['billno']);
  $status = checkInput($_GET['status']);
  $sql = "UPDATE team_license SET styles='$status' WHERE billno='$billno'";
  $result = $mysqli->query($sql);
  if($result){
    exit(JSON(array('data'=>'', 'msg'=>'ok', 'status'=>'0')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'更新失败', 'status'=>'1')));
}


//获取销售金额
if($a == 'getstockmoney'){
  $admin = checkInput($_GET['admin']);
  $model = checkInput($_GET['model']);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($model == '1') {
    //返回指定时间区域销售额
    $data=array(
      "salesData" => array(),
      "salesDataTop" => array(),
    );
    $seriesArray_area = array();
    $startdate = checkInput($_GET['startdate']);
    $enddate = checkInput($_GET['enddate']);
    $sql_t="select qty,qty1,qty2,qty3,price,warename from team_stockout where admin='$admin' and status='0' and billdate >= '$startdate"." 00:00:00' and billdate < '$enddate"." 23:59:00'";
    $result = $mysqli->query($sql_t);
    $sum_money=0.00;
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $sum_money += $money;
        array_push($seriesArray_area,$row["warename"]);
      }
    }
    array_push($data["salesData"], array("x"=>$startdate."~".$enddate, "y"=>$sum_money));
     //排行
    $salesDataTop_area = array();
    $seriesArray_area = array_unique($seriesArray_area); //去重复
    foreach($seriesArray_area as $key => $value){
      $price_sum = 0.00;
      $sql_t="select qty,qty1,qty2,qty3,price from team_stockout where admin='$admin' and warename='$value' and status='0' and billdate >= '$startdate"." 00:00:00' and billdate < '$enddate"." 23:59:00'";
      $result = $mysqli->query($sql_t);
      $sum_money=0.00;
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
          $money = $row["price"]*$sum;
          $sum_money += $money;
        }
        array_push($salesDataTop_area,array("title"=>$value,"total"=>$price_sum));
      }
    }
    //排序
    for ($i=0;$i<count($salesDataTop_area);$i++) {
      for ($j=$i+1;$j<count($salesDataTop_area);$j++) {
        if ($salesDataTop_area[$i]["total"] < $salesDataTop_area[$j]["total"]) {
          $tmp = $salesDataTop_area[$i];
          $salesDataTop_area[$i] = $salesDataTop_area[$j]; //更换位置
          $salesDataTop_area[$j] = $tmp;            // 完成位置互换
        }
      }
    }
    //截取前十名商品销量的集
    $salesDataTop_area = array_slice($salesDataTop_area,0,10);
    $data["salesDataTop"] = $salesDataTop_area;
    // 返回数据
    exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
  }

  $output=array(
    "all" => array(),
    "today" => array(),
    "payBillMonth" => array(
      "payBillData" => array(),
      "all" => array(),
      "today" => array(),
    ),
    "salesMoneyMonth" => array(
      "salesMoneyData" => array(),
      "all" => array(),
      "today" => array(),
    ),
    "salesData" => array(
      "year" => array(),
      "month" => array(),
      "week" => array(),
      "today" => array(),
    ),
    "salesDataTop" => array(
      "year" => array(),
      "month" => array(),
      "week" => array(),
      "today" => array(),
    ),
  );

  // 总销售额
  $sql = "SELECT qty,qty1,qty2,qty3,price FROM team_stockout WHERE admin='$admin' and status='0'";
  $result = $mysqli->query($sql);
  if($result){
    $sum_money=0.00;
    while($row=$result->fetch_assoc()){
      $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
      $money = $row["price"]*$sum;
      $sum_money += $money;
    }
    array_push($output["all"],$sum_money);
  }
  
  //日销售额
  $today = date('Y-m-d');
  $sql = "SELECT qty,qty1,qty2,qty3,price FROM team_stockout WHERE admin='$admin' and status='0' and billdate like '$today %'";
  $result = $mysqli->query($sql);
  if($result){
    $sum_money=0.00;
    while($row=$result->fetch_assoc()){
      $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
      $money = $row["price"]*$sum;
      $sum_money += $money;
    }
    array_push($output["today"],$sum_money);
  }

  //年销售额
  $year = date('m');
  $mon = date("Y-m");//当前月份
  //得到系统的年月
  $tmp_date=date("Ym");
  //切割出当前月的月份
  //切割出年份
  $tmp_year=substr($tmp_date,0,4);
  $tmp_mon =substr($tmp_date,4,2);
  $seriesArray_year = array();
  for($i=1;$i<=$year;$i++){
    //得到月份
    $tmp_backwardmonth=mktime(0,0,0,$tmp_mon-($year-$i),1,$tmp_year);
    $lastmon=date("Y-m",$tmp_backwardmonth);
    $sql="select qty,qty1,qty2,qty3,price,warename from team_stockout where admin='$admin' and status='0' and billdate like '$lastmon%'";
    $result = $mysqli->query($sql);
    $sum_money=0.00;
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $sum_money += $money;
        array_push($seriesArray_year,$row["warename"]);
      }
    }
    array_push($output["salesData"]["year"],array("x"=>$lastmon,"y"=>$sum_money));
  }
  //排行
  $salesDataTop_year = array();
  $seriesArray_year = array_unique($seriesArray_year); //去重复
  foreach($seriesArray_year as $key => $value){
    $price_sum = 0.00;
    for($i=1;$i<=$year;$i++){
      $tmp_backwardmonth=mktime(0,0,0,$tmp_mon-($year-$i),1,$tmp_year);
      $lastmon=date("Y-m",$tmp_backwardmonth);
      $sql="select qty,qty1,qty2,qty3,price from team_stockout where admin='$admin' and warename='$value' and status='0' and billdate like '$lastmon%'";
      $result = $mysqli->query($sql);
      $sum_money=0.00;
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
          $money = $row["price"]*$sum;
          $price_sum += $money;
        }
      }
    }
    array_push($salesDataTop_year,array("title"=>$value,"total"=>$price_sum));
  }
  //排序
  for ($i=0;$i<count($salesDataTop_year);$i++) {
    for ($j=$i+1;$j<count($salesDataTop_year);$j++) {
      if ($salesDataTop_year[$i]["total"] < $salesDataTop_year[$j]["total"]) {
        $tmp = $salesDataTop_year[$i];
        $salesDataTop_year[$i] = $salesDataTop_year[$j]; //更换位置
        $salesDataTop_year[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $salesDataTop_year = array_slice($salesDataTop_year,0,10);
  $output["salesDataTop"]["year"] = $salesDataTop_year;

  
  //本月销售额
  $sales_price_sum =  0.00;
  $payBillMonth_sum = 0;
  $month = date('d');
  $seriesArray = array();
  for($i=$month-1;$i>=0;$i--){
    $tian= date("Y-m-d",strtotime("-$i day"));
    $sql="select qty,qty1,qty2,qty3,price,warename from team_stockout where admin='$admin' and status='0' and billdate like '$tian%'";
    $result = $mysqli->query($sql);
    $sum_money=0.00;
    $sun_pay = 0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $sum_money += $money;
        $payBillMonth_sum += 1;
        $sun_pay += 1;
        $sales_price_sum += $money;
        //排行
        array_push($seriesArray,$row["warename"]);
      }
    }
    array_push($output["salesData"]["month"],array("x"=>substr($tian,-2),"y"=>$sum_money));
    array_push($output["payBillMonth"]["payBillData"],array("x"=>$tian,"y"=>$sun_pay));
    array_push($output["salesMoneyMonth"]["salesMoneyData"],array("x"=>$tian,"y"=>$sum_money));
  }
  $output["payBillMonth"]["all"] = $payBillMonth_sum;
  $output["salesMoneyMonth"]["all"] = $sales_price_sum;

  //排行
  $salesDataTop_month = array();
  $seriesArray = array_unique($seriesArray); //去重复
  foreach($seriesArray as $key => $value){
    $price_sum = 0.00;
    for($i=$month-1;$i>=0;$i--){
      $tian= date("Y-m-d",strtotime("-$i day"));
      $sql="select qty,qty1,qty2,qty3,price from team_stockout where admin='$admin' and status='0' and warename='$value' and billdate like '$tian%'";
      $result = $mysqli->query($sql);
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
          $money = $row["price"]*$sum;
          $price_sum += $money;
        }
      }
    }
    array_push($salesDataTop_month,array("title"=>$value,"total"=>$price_sum));
  }
   //排序
  for ($i=0;$i<count($salesDataTop_month);$i++) {
    for ($j=$i+1;$j<count($salesDataTop_month);$j++) {
      if ($salesDataTop_month[$i]["total"] < $salesDataTop_month[$j]["total"]) {
        $tmp = $salesDataTop_month[$i];
        $salesDataTop_month[$i] = $salesDataTop_month[$j]; //更换位置
        $salesDataTop_month[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $salesDataTop_month = array_slice($salesDataTop_month,0,10);
  $output["salesDataTop"]["month"] = $salesDataTop_month;


  //本周销售额
  $w = date("w");
  if($w == 0){
    $w = 7;
  }
  $seriesArray_week = array();
  for($i=$w-1;$i>=0;$i--){
    $tian= date("Y-m-d",strtotime("-$i day"));
    $sql="select qty,qty1,qty2,qty3,price,warename from team_stockout where admin='$admin' and status='0' and billdate like '$tian%'";
    $result = $mysqli->query($sql);
    $sum_money=0.00;
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $sum_money += $money;
        array_push($seriesArray_week,$row["warename"]);
      }
    }
    array_push($output["salesData"]["week"],array("x"=>$tian,"y"=>$sum_money));
  }
  //排行
  $salesDataTop_week = array();
  $seriesArray_week = array_unique($seriesArray_week); //去重复
  foreach($seriesArray_week as $key => $value){
    $price_sum = 0.00;
    for($i=$w-1;$i>=0;$i--){
      $tian= date("Y-m-d",strtotime("-$i day"));
      $sql="select qty,qty1,qty2,qty3,price from team_stockout where admin='$admin' and warename='$value' and status='0' and billdate like '$tian%'";
      $result = $mysqli->query($sql);
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
          $money = $row["price"]*$sum;
          $price_sum += $money;
        }
      }
    }
    array_push($salesDataTop_week,array("title"=>$value,"total"=>$price_sum));
  }
  //排序
  for ($i=0;$i<count($salesDataTop_week);$i++) {
    for ($j=$i+1;$j<count($salesDataTop_week);$j++) {
      if ($salesDataTop_week[$i]["total"] < $salesDataTop_week[$j]["total"]) {
        $tmp = $salesDataTop_week[$i];
        $salesDataTop_week[$i] = $salesDataTop_week[$j]; //更换位置
        $salesDataTop_week[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $salesDataTop_week = array_slice($salesDataTop_week,0,10);
  $output["salesDataTop"]["week"] = $salesDataTop_week;


  //今日销售额
  $sales_price_sum_today = 0.00;
  $payBillMonth_today_sum = 0;
  $seriesArray_today = array();
  $dateArray1= [['08:00','12:00'],['12:00','16:00'],['16:00','20:00'],['20:00','00:00'],['00:00','08:00']];
  foreach($dateArray1 as $key => $value){
    $sql_t="select qty,qty1,qty2,qty3,price,warename from team_stockout where admin='$admin' and status='0' and billdate >= '$today"." $value[0]' and billdate < '$today"." $value[1]'";
    $result = $mysqli->query($sql_t);
    $sum_money=0.00;
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $sum_money += $money;
        array_push($seriesArray_today,$row["warename"]);
        $payBillMonth_today_sum += 1;
        $sales_price_sum_today += $money;
      }
    }
    array_push($output["salesData"]["today"],array("x"=>$value[0].'~'.$value[1],"y"=>$sum_money));
  }
  $output["payBillMonth"]["today"] = $payBillMonth_today_sum;
  $output["salesMoneyMonth"]["today"] = $sales_price_sum_today;

  //排行
  $salesDataTop_today = array();
  $seriesArray_today = array_unique($seriesArray_today); //去重复
  foreach($seriesArray_today as $key => $value){
    $price_sum = 0.00;
    $sql_t="select qty,qty1,qty2,qty3,price from team_stockout where admin='$admin' and warename='$value' and status='0' and billdate like '$today%'";
    $result = $mysqli->query($sql_t);
    if($result){
      while ($row=$result->fetch_assoc()){
        $sum = $row["qty"]+$row["qty1"]+$row["qty2"]+$row["qty3"];
        $money = $row["price"]*$sum;
        $price_sum += $money;
      }
    }
    array_push($salesDataTop_today,array("title"=>$value,"total"=>$price_sum));
  }
  //排序
  for ($i=0;$i<count($salesDataTop_today);$i++) {
    for ($j=$i+1;$j<count($salesDataTop_today);$j++) {
      if ($salesDataTop_today[$i]["total"] < $salesDataTop_today[$j]["total"]) {
        $tmp = $salesDataTop_today[$i];
        $salesDataTop_today[$i] = $salesDataTop_today[$j]; //更换位置
        $salesDataTop_today[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $salesDataTop_today = array_slice($salesDataTop_today,0,10);
  $output["salesDataTop"]["today"] = $salesDataTop_today;

  //返回数据
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


//获取客户访问量
if($a == 'getcustomervisits'){
  $admin = checkInput($_GET['admin']);
  $model = checkInput($_GET['model']);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($model == '1') {
    //返回指定时间区域客访数据
    $data=array(
      "visitsData" => array(),
      "visitsDataTop" => array(),
    );
    $visitsArray_area = array();
    $startdate = checkInput($_GET['startdate']);
    $enddate = checkInput($_GET['enddate']);
    $sql_t="select type from team_visit where admin='$admin' and billdate >= '$startdate"." 00:00:00' and billdate < '$enddate"." 23:59:00'";
    $result = $mysqli->query($sql_t);
    $visits_sum=0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum += 1;
        array_push($visitsArray_area,$row["type"]);
      }
    }
    array_push($data["visitsData"], array("x"=>$startdate."~".$enddate, "y"=>$visits_sum));
     //排行
    $visitsDataTop_area = array();
    $visitsArray_area = array_unique($visitsArray_area); //去重复
    foreach($visitsArray_area as $key => $value){
      $sum_visits = 0;
      $sql_t="select billdate from team_visit where admin='$admin' and type='$value' and billdate >= '$startdate"." 00:00:00' and billdate < '$enddate"." 23:59:00'";
      $result = $mysqli->query($sql_t);
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum_visits += 1;
        }
        array_push($visitsDataTop_area,array("title"=>$value,"total"=>$sum_visits));
      }
    }
    //排序
    for ($i=0;$i<count($visitsDataTop_area);$i++) {
      for ($j=$i+1;$j<count($visitsDataTop_area);$j++) {
        if ($visitsDataTop_area[$i]["total"] < $visitsDataTop_area[$j]["total"]) {
          $tmp = $visitsDataTop_area[$i];
          $visitsDataTop_area[$i] = $visitsDataTop_area[$j]; //更换位置
          $visitsDataTop_area[$j] = $tmp;            // 完成位置互换
        }
      }
    }
    //截取前十名商品销量的集
    $visitsDataTop_area = array_slice($visitsDataTop_area,0,10);
    $data["visitsDataTop"] = $visitsDataTop_area;
     // 返回数据
     exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
  }

  $output=array(
    "all" => array(),
    "today" => array(),
    "visitsMonth" => array(
      "visitsData" => array(),
      "all" => array(),
      "today" => array(),
    ),
    "visitsData" => array(
      "year" => array(),
      "month" => array(),
      "week" => array(),
      "today" => array(),
    ),
    "visitsDataTop" => array(
      "year" => array(),
      "month" => array(),
      "week" => array(),
      "today" => array(),
    ),
  );

  //年访量
  $year = date('m');
  $mon = date("Y-m");//当前月份
  //得到系统的年月
  $tmp_date=date("Ym");
  //切割出当前月的月份
  //切割出年份
  $tmp_year=substr($tmp_date,0,4);
  $tmp_mon =substr($tmp_date,4,2);
  $visitsArray_year = array();
  for($i=1;$i<=$year;$i++){
    //得到月份
    $tmp_backwardmonth=mktime(0,0,0,$tmp_mon-($year-$i),1,$tmp_year);
    $lastmon=date("Y-m",$tmp_backwardmonth);
    $sql="select type from team_visit where admin='$admin' and billdate like '$lastmon%'";
    $result = $mysqli->query($sql);
    $visits_sum=0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum += 1;
        array_push($visitsArray_year,$row["type"]);
      }
    }
    array_push($output["visitsData"]["year"],array("x"=>$lastmon,"y"=>$visits_sum));
  }
  // 排行
  $visitsDataTop_year = array();
  $visitsArray_year = array_unique($visitsArray_year); //去重复
  foreach($visitsArray_year as $key => $value){
    $sum_visits = 0;
    for($i=1;$i<=$year;$i++){
      $tmp_backwardmonth=mktime(0,0,0,$tmp_mon-($year-$i),1,$tmp_year);
      $lastmon=date("Y-m",$tmp_backwardmonth);
      $sql="select billdate from team_visit where admin='$admin' and type='$value' and billdate like '$lastmon%'";
      $result = $mysqli->query($sql);
      if($result){
        while ($row=$result->fetch_assoc()){
          $sum_visits +=1;
        }
      }
    }
    array_push($visitsDataTop_year,array("title"=>$value,"total"=>$sum_visits));
  }
  //排序
  for ($i=0;$i<count($visitsDataTop_year);$i++) {
    for ($j=$i+1;$j<count($visitsDataTop_year);$j++) {
      if ($visitsDataTop_year[$i]["total"] < $visitsDataTop_year[$j]["total"]) {
        $tmp = $visitsDataTop_year[$i];
        $visitsDataTop_year[$i] = $visitsDataTop_year[$j]; //更换位置
        $visitsDataTop_year[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $visitsDataTop_year = array_slice($visitsDataTop_year,0,10);
  $output["visitsDataTop"]["year"] = $visitsDataTop_year;


  //本月访问量
  $month = date('d');
  $visitsArray_month = array();
  $all_visits_sum = 0;
  for($i=$month-1;$i>=0;$i--){
    $tian= date("Y-m-d",strtotime("-$i day"));
    $sql="select type from team_visit where admin='$admin' and billdate like '$tian%'";
    $result = $mysqli->query($sql);
    $visits_sum=0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum += 1;
        $all_visits_sum += 1;
        array_push($visitsArray_month,$row["type"]);
      }
    }
    array_push($output["visitsData"]["month"],array("x"=>substr($tian,-2),"y"=>$visits_sum));
    array_push($output["visitsMonth"]["visitsData"],array("x"=>$tian,"y"=>$visits_sum));
  }
  $output["visitsMonth"]["all"] = $all_visits_sum;

  //排行
  $visitsDataTop_month = array();
  $visitsArray_month = array_unique($visitsArray_month); //去重复
  foreach($visitsArray_month as $key => $value){
    $visits_sum = 0;
    for($i=$month-1;$i>=0;$i--){
      $tian= date("Y-m-d",strtotime("-$i day"));
      $sql="select billdate from team_visit where admin='$admin' and type='$value' and billdate like '$tian%'";
      $result = $mysqli->query($sql);
      if($result){
        while ($row=$result->fetch_assoc()){
          $visits_sum += 1;
        }
      }
    }
    array_push($visitsDataTop_month,array("title"=>$value,"total"=>$visits_sum));
  }
  //排序
  for ($i=0;$i<count($visitsDataTop_month);$i++) {
    for ($j=$i+1;$j<count($visitsDataTop_month);$j++) {
      if ($visitsDataTop_month[$i]["total"] < $visitsDataTop_month[$j]["total"]) {
        $tmp = $visitsDataTop_month[$i];
        $visitsDataTop_month[$i] = $visitsDataTop_month[$j]; //更换位置
        $visitsDataTop_month[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $visitsDataTop_month = array_slice($visitsDataTop_month,0,10);
  $output["visitsDataTop"]["month"] = $visitsDataTop_month;


  //本周销售额
  $w = date("w");
  if($w == 0){
    $w = 7;
  }
  $typeArray_week = array();
  for($i=$w-1;$i>=0;$i--){
    $tian= date("Y-m-d",strtotime("-$i day"));
    $sql="select type from team_visit where admin='$admin' and billdate like '$tian%'";
    $result = $mysqli->query($sql);
    $visits_sum=0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum +=1;
        array_push($typeArray_week,$row["type"]);
      }
    }
    array_push($output["visitsData"]["week"],array("x"=>$tian,"y"=>$visits_sum));
  }
  //排行
  $visitsDataTop_week = array();
  $typeArray_week = array_unique($typeArray_week); //去重复
  foreach($typeArray_week as $key => $value){
    $visits_sum = 0;
    for($i=$w-1;$i>=0;$i--){
      $tian= date("Y-m-d",strtotime("-$i day"));
      $sql="select billdate from team_visit where admin='$admin' and type='$value' and billdate like '$tian%'";
      $result = $mysqli->query($sql);
      if($result){
        while ($row=$result->fetch_assoc()){
          $visits_sum += 1;
        }
      }
    }
    array_push($visitsDataTop_week,array("title"=>$value,"total"=>$visits_sum));
  }
  //排序
  for ($i=0;$i<count($visitsDataTop_week);$i++) {
    for ($j=$i+1;$j<count($visitsDataTop_week);$j++) {
      if ($visitsDataTop_week[$i]["total"] < $visitsDataTop_week[$j]["total"]) {
        $tmp = $visitsDataTop_week[$i];
        $visitsDataTop_week[$i] = $visitsDataTop_week[$j]; //更换位置
        $visitsDataTop_week[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $visitsDataTop_week = array_slice($visitsDataTop_week,0,10);
  $output["visitsDataTop"]["week"] = $visitsDataTop_week;


  //今日客访量
  $today_visits_sum = 0; 
  $today = date('Y-m-d');
  $visitsArray_today = array();
  $dateArray1= [['08:00','12:00'],['12:00','16:00'],['16:00','20:00'],['20:00','00:00'],['00:00','08:00']];
  foreach($dateArray1 as $key => $value){
    $sql_t="select type from team_visit where admin='$admin' and billdate >= '$today"." $value[0]' and billdate < '$today"." $value[1]'";
    $result = $mysqli->query($sql_t);
    $visits_sum=0;
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum +=1;
        $today_visits_sum += 1;
        array_push($visitsArray_today,$row["type"]);
      }
    }
    array_push($output["visitsData"]["today"],array("x"=>$value[0].'~'.$value[1],"y"=>$visits_sum));
  }
  $output["visitsMonth"]["today"] = $today_visits_sum;
  //排行
  $visitsDataTop_today = array();
  $visitsArray_today = array_unique($visitsArray_today); //去重复
  foreach($visitsArray_today as $key => $value){
    $visits_sum = 0;
    $sql_t="select billdate from team_visit where admin='$admin' and type='$value' and billdate like '$today%'";
    $result = $mysqli->query($sql_t);
    if($result){
      while ($row=$result->fetch_assoc()){
        $visits_sum += 1;
      }
    }
    array_push($visitsDataTop_today,array("title"=>$value,"total"=>$visits_sum));
  }
  //排序
  for ($i=0;$i<count($visitsDataTop_today);$i++) {
    for ($j=$i+1;$j<count($visitsDataTop_today);$j++) {
      if ($visitsDataTop_today[$i]["total"] < $visitsDataTop_today[$j]["total"]) {
        $tmp = $visitsDataTop_today[$i];
        $visitsDataTop_today[$i] = $visitsDataTop_today[$j]; //更换位置
        $visitsDataTop_today[$j] = $tmp;            // 完成位置互换
      }
    }
  }
  //截取前十名商品销量的集
  $visitsDataTop_today = array_slice($visitsDataTop_today,0,10);
  $output["visitsDataTop"]["today"] = $visitsDataTop_today;

  //返回数据
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


//获取上级信息
if($a == 'getadminbillno'){
  $userno = checkInput($_GET['admin']);
  // $userno = "13690852319";
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $output = array();
  $sql = "SELECT billno, userno FROM team_salesman where userno='$userno'" ;
  $result = $mysqli->query($sql) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
		array_push($output, $row);
	}
    //返回数据
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


//获取轮播图片信息
if($a == 'getbannerinfo'){
	$begindate = @$_GET['begin']? checkInput($_GET['begin']):'';		//开始时间
	$enddate = @$_GET['begin']? checkInput($_GET['end']): '';		//结束时间
	$output = array('list'=>array(), 'total'=>0);
	
  if ($begindate==''){
    $query="SELECT SQL_CALC_FOUND_ROWS * FROM `team_mall_ad` where media_type=0 ";
  } else {
    $query="SELECT SQL_CALC_FOUND_ROWS * FROM `team_mall_ad` WHERE media_type=0 and  billdate between '$begindate' and '$enddate'";
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


//添加广告
if($a == "addshopad"){
	$ad_bno = checkInput($_GET['ad_bno']);
  $ad_code = checkInput($_GET['ad_code']); 
  $ad_link = checkInput($_GET['ad_link']); 
	$end_time = checkInput($_GET['end_time']); 
	$start_time =checkInput($_GET['start_time']); 
	$img_type = checkInput($_GET['img_type']); 
	$link_email = checkInput($_GET['link_email']);
	$link_man = checkInput($_GET['link_man']);
	$link_phone =checkInput($_GET['link_phone']); 
  $link_man = checkInput($_GET['link_man']);

	$sql = "INSERT INTO `team_mall_ad` SET billno='$_billno',ad_link='$ad_link',link_phone='$link_phone',img_type='$img_type',link_man='$link_man',link_email='$link_email',billdate=NOW(),position_id='0',media_type='0',ad_bno='$ad_bno',ad_name='好业绩',ad_type='1',ad_code='$ad_code',start_time='$start_time',enabled='1',end_time='$end_time'";
  if (!$mysqli->query($sql)){       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
}


//更改广告显示状态
if($a == "updateshopadstatus"){
  $billno = checkInput($_GET['c_billno']);
  $enabled = checkInput($_GET['enabled']);

  if (!$billno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }	
  
  $sql = "update team_mall_ad set enabled='$enabled' where billno='$billno'";
  if (!$mysqli->query($sql)){       
		exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
	}	
 
   exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
}


//获取店铺优惠商品列表
if ($a== 'getsaleofflist'){
	$admin=checkInput($_GET['admin']);
	
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT sql_calc_found_rows * FROM `mall_wares` WHERE admin='$admin' and waretype=1";

  $query .= " ORDER BY id DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
	
}


//获取商城折扣商品
if($a=="getmalldiscount"){
  $searchkey = checkInput($_GET['searchkey']);
	$begindate = @$_GET['begin']? checkInput($_GET['begin']):'';		//开始时间
  $enddate = @$_GET['end']? checkInput($_GET['end']): '';		//结束时间
  $admin= checkInput($_GET['userno']);
  $output = array('list'=>array(), 'total'=>0);
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($searchkey==''){
    if ($begindate==''){
      $query="SELECT SQL_CALC_FOUND_ROWS * FROM `mall_wares` where waretype=1 and status<>-1";
    } else {
      $query="SELECT SQL_CALC_FOUND_ROWS * FROM `mall_wares` WHERE waretype=1 and status<>-1 AND billdate between '$begindate' AND '$enddate'";
    }
  } else {
    $query="SELECT SQL_CALC_FOUND_ROWS * FROM `mall_wares` where waretype=1 and status<>-1 and waresname like '%$searchkey%' or description like '%$searchkey%'";
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


//添加 修改优惠商品
if($a=="setmallware"){
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }
  $image1 = $obj['image1']; // 头像
  $image2 = $obj['image2'];
  $image3 = $obj['image3'];
  $image4 = $obj['image4'];
  $image5 = $obj['image5'];
  $image6 = $obj['image6'];
  $qty = $obj['qty'];
  $qty1 = $obj['qty1'];
  $qty2 = $obj['qty2'];
  $qty3 = $obj['qty3'];
  $admin = $obj['admin'];
  $username = $obj['username'];
  $waresname = $obj['waresname']; // 名称
  $waresno = $obj['waresno']; //
  $waretype = $obj['waretype']; //类型
  $model = $obj['model']; // 型号
  $productno = $obj['productno']; // 编码
  $price = $obj['price']; // 单价
  $unit= $obj['unit']; // 单位
  $description = $obj['description']; // 描述
  $mbillno = $obj['billno']; // 有值则修改
  $series = $obj['series']; // 产品的分类

  if (!$mbillno) {
    $sql = "INSERT INTO `mall_wares` SET billdate=NOW(),billno='$_billno',admin='$admin',username='$username',wareno='$waresno',waresname='$waresname',waretype='$waretype',unit='$unit',model='$model',productno='$productno',price='$price',description='$description',image1='$image1',image2='$image2',image3='$image3',image4='$image4',image5='$image5',image6='$image6',status=0,qty='$qty',qty1='$qty1',qty2='$qty2',qty3='$qty3'";
  } else {
    $sql = "UPDATE `mall_wares` SET username='$username',wareno='$waresno',waresname='$waresname',waretype='$waretype',unit='$unit',model='$model',productno='$productno',price='$price',description='$description',image1='$image1',image2='$image2',image3='$image3',image4='$image4',image5='$image5',image6='$image6',status=0,qty='$qty',qty1='$qty1',qty2='$qty2',qty3='$qty3' WHERE billno='$mbillno'";
  }

  if (!$mysqli->query($sql)) {
    $output = array('data'=>$sql, 'msg'=>'上传失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'上传成功', 'status'=>'0');
  }
  exit(JSON($output));
}


//删除优惠商品
if($a=="delmallwares"){
  $billno= checkInput($_GET['billno']);
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  
  $sql = "UPDATE `mall_wares` SET status=-1 WHERE billno='$billno'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 查询 发货人
if ($a == 'getmallsend') {
  $admin = checkInput($_GET['admin']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM team_salesman WHERE userno in (select userno from teams where isteam = 4 AND astatus = 1 AND admin = $admin)".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// ------- 购物车提交后，查看订单 --------

//查询订单头
if($a=='getorderhead'){
 $orderno=checkInput($_GET['orderno']);
 $output = array('list'=>array(), 'total'=>0);
 
 if(!$orderno){
  exit(JSON(array('data'=>'', 'msg'=>'未传入订单标识', 'status'=>'1')));
 }
 
 $query = "SELECT *,(amount-fullcut-redtip-coupon) AS payrmb FROM `mall_orderhead` WHERE billno='$orderno'";
 $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

//查询订单商品
if($a=='getorderbody'){
 $orderno=checkInput($_GET['orderno']);
 $output = array('list'=>array(), 'total'=>0);
 
 if(!$orderno){
  exit(JSON(array('data'=>'', 'msg'=>'未传入订单标识', 'status'=>'1')));
 }
 
 $query = "SELECT * FROM `mall_orderbody` WHERE orderno='$orderno'";
 $result = $mysqli->query($query) or die($mysqli->error);
   $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
   $output['total'] = $totalResult->fetch_assoc()['total'];
   while ($row = $result->fetch_assoc()) {
     array_push($output['list'], $row);
   }
   exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// hzz 商品列表
if ($a == 'get_hzzwares') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能空', 'status'=>'1')));
  } 
  $output = array('list'=>array(), 'total'=>0);
  // 查询原主的基础商品
  $sql = "SELECT admin from `teams` WHERE userno='$admin' LIMIT 1";
  $result2 = $mysqli->query($sql) or die($mysqli->error);
  $row2 = $result2->fetch_assoc();
  if ($row2) {
    $muserno = $row2['admin'];
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'没有数据源', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *,(SELECT typename from mall_category WHERE billno=`team_wares`.category3) AS catname FROM `team_wares` WHERE `admin`='$muserno' AND `status`>'-1'";

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




