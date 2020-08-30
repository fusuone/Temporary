<?php



// -- 商城 -- 
// 货真真商城首页数据
if ($a == 'get_index') {
  // $usercode = checkInput($_GET['usercode']); // 用户billno
  // $userno = checkInput($_GET['userno']); // 用户userno
  // $admin = checkInput($_GET['admin']); // 商城管理员 admin
  $page = checkInput($_GET['page']);
  $output = array(
    'banner'=>array(),    // 大图广告
    'fourimg'=>array(),  // 四张大图
    'bigarry'=>array(),   // 一张大图
	'scroll'=>array(),  // 滚动广告  
    'merchants'=>array(),  // 新品上市
    'section1_data1'=>array(),  // 版块1
    'section1_data2'=>array(),  // 版块1
    'section2_data1'=>array(),   // 版块2
    'section2_data2'=>array(),   // 版块2
	'section3'=>array(),   // 版块3
     'section_img'=>array(),   // 板块 大图 集合
    'product'=>array()
  );
  // $page = ($page-1) * 20;

  // banner 大图广告
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=0";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['banner'], $row);
  }

  // 四张大图
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $serverAssetsUrl = $serverConfig->getAssetsUrl();
  while ($row = $result->fetch_assoc()) {
    array_push($output['fourimg'], $row);
  }

  // 一张大图
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=2";
 
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['bigarry'], $row);
  }

  // 新品上市
  $query = "SELECT a.billno,a.billdate,a.admin,a.admincode,a.wareno,a.waresname,a.waretype,a.model,a.price,a.retailprice,a.unit,a.`makedate`,a.place,a.warranty,a.buylimit,a.`envelope`,a.ticket,a.net,a.productno,a.description,a.image1,a.image2,a.image3,a.image4,a.image5,a.image6,a.qty,a.qty1,a.qty2,a.qty3,a.points,a.ispoint,a.waresname2,a.series,a.brand,a.price2,a.mallprice,a.destp2,a.img1,a.img2,a.img3,a.img4,a.img5,a.img6,a.category1,b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname,b.litrmb AS litrmb FROM `view_hzzwares` a LEFT JOIN `team_salesman` b ON a.admincode=b.billno where a.billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH) AND a.`status`=0 AND a.honsale='1' ORDER BY RAND() LIMIT 0,8";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['merchants'], $row);
  }

  // 滚动广告 
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=3";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['scroll'], $row);
  }
  
  // 查询所有版块的 一张大图
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where id IN (8,10,25)";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section_img'], $row);
  }

  // 版块1 (品牌1-8)
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=4 AND id BETWEEN 11 AND 18";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section1_data1'], $row);
  }

  // 版块1 (专场1 1-6)
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=4 AND id BETWEEN 19 AND 24";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section1_data2'], $row);
  }
  
  // 版块2 (品牌1-8)
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where id BETWEEN 26 AND 33";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section2_data1'], $row);
  }
  // 版块2 (专场2 1-6)
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=5 AND id BETWEEN 34 AND 39";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section2_data2'], $row);
  }

  // 版块3
  $query = "SELECT billno,title,keyword,class,image,pic,descption,begindate,enddate from mall_index where location=6";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['section3'], $row);
  }  
  
  // 列表商品 推荐
  $query = "SELECT billno,billdate,admin,admincode,wareno,waresname,waretype,model,price,retailprice,unit,`makedate`,place,warranty,buylimit,`envelope`,ticket,net,productno,description,image1,image2,image3,image4,image5,image6,qty,qty1,qty2,qty3,points,ispoint,waresname2,series,brand,price2,mallprice,destp2,img1,img2,img3,img4,img5,img6,category1 FROM `view_hzzwares` WHERE waretype = 2 AND `status`=0 AND honsale='1' ORDER BY RAND() LIMIT 0,50";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['product'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 列表商品 分页查询 一页 20条数据   (暂时不用)
if ($a == 'get_index_page') {
  $admin = checkInput($_GET['admin']);
  $page = checkInput($_GET['page']);
  $output = array('list'=>array(), 'total'=>0);
  
  $page = ($page-1) * 20;

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  // $query = "SELECT sql_calc_found_rows * FROM `team_wares` WHERE `admin`='$admin' AND `status`!=-1 ORDER BY billdate DESC LIMIT $page,20";
  $query = "SELECT sql_calc_found_rows * FROM `team_wares` WHERE  `status`!=-1 ORDER BY billdate DESC LIMIT $page,20";

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 商品搜索
// 商品搜索 -- 热门 推荐
if ($a == 'search_hot_wares') {
  $output = array('list'=>array(), 'total'=>0);

  // 热门 推荐
  $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE waretype = 2 AND `status`=0 AND honsale='1' AND honsale='1'".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 热门搜索
if ($a == 'search_hot_list') {
  $output = array('list'=>array());

  // 热门 搜索 历史
  $query1 = "SELECT title FROM `mall_hotsearch` ORDER BY knum DESC LIMIT 8";
  $result1 = $mysqli->query($query1) or die($mysqli->error);
  while ($row1 = $result1->fetch_assoc()) {
    array_push($output['list'], $row1);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 商品搜索 -- 商品列表
if ($a == 'search_list_wares') {
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);
  
  $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE  `status`=0 AND honsale='1' AND waresname LIKE '%$keyword%' ".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 主页 - 百货，酒水
if ($a == 'get_index_baihuo') {
  $flat = checkInput($_GET['flat']);   // 0 特价促销，1 新品上市，2 推荐优选
  $key = checkInput($_GET['key']);     // 0百货，1酒水
  $page = checkInput($_GET['page']);   // 页数
  $output = array(
    'product'=>array(),
    'total'=>0
  );
  $page = ($page-1) * 15;

  if ($flat == '1'){ // 新品上市
     $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE  `status`=0 AND honsale='1' AND category1='$key' AND billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
          $query .= " ORDER BY billdate DESC LIMIT $page,15";
  } else {
    // waretype，0 普通商品，1 优惠商品(特价促销)，2 推荐,
    switch ($flat) {
      case '0':
        $waretype=1;
      break;
      case '2':
        $waretype=2;
      break;
      
      default:
        break;
    }
    $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1'  AND waretype='$waretype' AND category1='$key' ORDER BY billdate DESC LIMIT $page,15";
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['product'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 百货 酒水 - item(查询第三级 所有商品)
if ($a == 'get_index_baihuo_item') {
  $flat = checkInput($_GET['flat']);   // 0 综合排序，1 销量优先，2 价格高到低， 3价格低到高
  $keyword = checkInput($_GET['keyword']);   // 种类(2级) cid
  $keyword2 = checkInput($_GET['keyword2']);   // 种类(3级)
  $brand = checkInput($_GET['brand']);   // 品牌
  $output = array(
    'list'=>array(),
    'total'=>0
  );
   if($flat == '1') { // 1 销量优先

    $query = "SELECT sql_calc_found_rows *,(select sum(qty) from mall_orderbody where wareno=a.wareno) AS tolnum FROM `view_hzzwares` as a WHERE `status`=0 AND honsale='1' AND  category2='$keyword'";

     if($keyword2){
       $query .= " AND category3='$keyword2'";
    }
    if($brand){
       $query .= " AND brand='$brand'";
    }
    $query .= " ORDER BY tolnum DESC".$paging;
  } else {
    $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND category2='$keyword'";
    if($keyword2){
       $query .= " AND category3='$keyword2'";
    }
    if($brand){
       $query .= " AND brand='$brand'";
    }
    switch ($flat) {
      case '0': // 综合排序
         $query .= " ORDER BY billdate DESC".$paging;
      break;
      case '2': // 2 价格高到低
         $query .= " ORDER BY price DESC".$paging;
      break;
      case '3': // 价格低到高
         $query .= " ORDER BY price ASC".$paging;
      break;
      
      default:
        break;
    }
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));

}
// 百货 酒水 - 品牌
if ($a == 'get_index_baihuo_brand') {
  $flat = checkInput($_GET['flat']);   // 0 综合排序，1 销量优先，2 价格高到低， 3价格低到高
  $keyword = checkInput($_GET['keyword']);
  $brand = checkInput($_GET['brand']);
  $output = array(
    'list'=>array(),
    'total'=>0
  );

  if($flat == '1') { // 1 销量优先
    $query = "SELECT sql_calc_found_rows *,(select sum(qty) from mall_orderbody where warename= waresname) AS tolnum FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND brand='$brand'";
    if($keyword){
        $query .= " AND waresname LIKE '%$keyword%'";
      }
    $query .= " ORDER BY tolnum DESC".$paging;
  } else {
    $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND brand='$brand'";
    if($keyword){
       $query .= " AND waresname LIKE '%$keyword%'";
    }
    switch ($flat) {
      case '0': // 综合排序
         $query .= " ORDER BY billdate DESC".$paging;
      break;
      case '2': // 2 价格高到低
         $query .= " ORDER BY price DESC".$paging;
      break;
      case '3': // 价格低到高
         $query .= " ORDER BY price ASC".$paging;
      break;
      
      default:
        break;
    }
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 主页 - 会员
if ($a == 'get_mall_menber_head') { // 头信息
    $userno = checkInput($_GET['userno']);
    $output = array('list'=>array(), 'head'=>array());
    $query = "SELECT image,username,mallpoint FROM `team_salesman` WHERE  userno='$userno'";
    $result = $mysqli->query($query) or die($mysqli->error);
    if ($row = $result->fetch_assoc()) {
      array_push($output['head'], array('image'=>$row['image'],'username'=>$row['username'],'mallpoint'=>$row['mallpoint']));
    }
    $query = "SELECT * FROM `mall_member` WHERE ishide='1'";
    $result = $mysqli->query($query) or die($mysqli->error);
    while ($row = $result->fetch_assoc()) {
      array_push($output['list'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

if ($a == 'get_mall_menber_body') { // 商品
    $output = array('list'=>array(), 'total'=>0);
   $query = "SELECT sql_calc_found_rows a.*,b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname FROM `view_hzzwares` a LEFT JOIN `team_salesman` b ON a.admincode=b.billno  where a.ispoint = 1 AND a.`status`=0 AND a.honsale='1' ORDER BY a.points ASC".$paging;

    $result = $mysqli->query($query) or die($mysqli->error);
    $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

    $output['total'] = $totalResult->fetch_assoc()['total'];
    while ($row = $result->fetch_assoc()) {
      array_push($output['list'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
  }

// 会员积分 提交商品
if ($a == 'set_menber_mallorder') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, 1);

  // 表头内容
  $mallno = checkInput($obj['head']['salerno']);    //卖方即是商城号
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
  $topoints = checkInput($obj['head']['topoints']); // 总积分

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
  // 查询用户积分
  $query = "SELECT mallpoint FROM `team_salesman` WHERE  billno='$buyerno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($row = $result->fetch_assoc()) {
    $mpoint = $topoints - $row['mallpoint'];
    if ($mpoint > 0) {
      exit(JSON(array('data'=>'', 'msg'=>'积分不足,还差'.$mpoint.'积分!', 'status'=>'1')));
    }
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'找不到用户', 'status'=>'1')));
  }

  $mysqli->query('BEGIN');
  $sql1 = "INSERT INTO mall_orderhead SET billno='$orderhead_billno',billdate=NOW(),salerno='$mallno',salername='$salername',salerlogo='$salerlogo',buyerno='$buyerno',buyername='$buyername',buyeravatar='$buyeravatar',remark='$remark',c_address='$c_address',c_linkman='$c_linkman',c_tel='$c_tel',qty='$num',amount='$amount',payway='$payway',points='$topoints',billstate='2'";
  $sql2 = "INSERT INTO mall_orderbody(billno,billdate,orderno,mallno,wareno,warename,wareimage,price,qty) VALUES".implode(',', $sqlarr);
  $t1 = $mysqli->query($sql1);
  $t2 = $mysqli->query($sql2);

  if ($t1 && $t2) {
      // 扣徐积分
      $sql3 = "UPDATE `team_salesman` SET mallpoint=mallpoint - $topoints  WHERE billno='$buyerno'";
      $mysqli->query($sql3);

      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>'兑换成功', 'status'=>'0')));
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
  }
}

// 主页 - 获取 签到
if ($a == 'get_mall_attendance') {
  $mbillno = checkInput($_GET['mbillno']); 
  $userno = checkInput($_GET['userno']);
  $output = array(
    'list'=>array(),
    'total'=>0, // 已签到天
    'days'=>0   // 连续签到
  );

  $query1="SELECT  continuty_days('$userno') AS condays";
  $result1 = $mysqli->query($query1) or die($mysqli->error);
  if ($row1 = $result1->fetch_assoc()) {
     $output['days'] = $row1['condays'];
  }

  $query = "SELECT DATE_FORMAT(billdate,'%Y-%m-%d') AS billdate FROM `mall_attendance` WHERE mbillno='$mbillno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}
// 进行签到
if ($a == 'set_mall_attendance') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $mbillno = checkInput($obj['mbillno']);
  $username = checkInput($obj['username']);
  $userno = checkInput($obj['userno']);
  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT id from `mall_attendance` where mbillno='$mbillno' AND TO_DAYS(billdate) = TO_DAYS(now())";
  $result = $mysqli->query($query) or die($mysqli->error);
  if (mysqli_num_rows($result) > 0){
      exit(JSON(array('data'=>'', 'msg'=>'今天你已签到！', 'status'=>'1')));
  }

  $conpoints=0;  //初始增加积分的值
  $query="SELECT  continuty_days('$userno') AS condays";
  $result = $mysqli->query($query) or die($mysqli->error);
	if ($row = $result->fetch_assoc()) {
		$conpoints= $row['condays'];
	}
  //限制连续5天的最大积分值
  if ($conpoints<=5){
     $conpoints = $conpoints + 1;
  } else { 
	 $conpoints = 5;
  }
  $sql = "INSERT INTO `mall_attendance` SET billdate=NOW(),billno='$_billno',mbillno='$mbillno',username='$username',userno='$userno',points='$conpoints'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'签到失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'签到成功,积分加'.$conpoints, 'status'=>'0');
  }
  exit(JSON($output));
}

// 主页 - 建议
if ($a == 'set_mall_advice') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $mbillno = checkInput($obj['mbillno']);
  $username = checkInput($obj['username']);
  $userno = checkInput($obj['userno']);
  $title = checkInput($obj['title']); // 内容

  if (!$mbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "INSERT INTO `mall_advice` SET billdate=NOW(),billno='$_billno',mbillno='$mbillno',username='$username',userno='$userno',title='$title'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'提交失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'提交成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 主页 - 4推广大图 打折促销
if ($a == 'get_mall_discount') {
  $keyword = checkInput($_GET['keyword']);
  $wtype = checkInput($_GET['wtype']);
  $output = array(
    'list'=>array(),
    'total'=>0
  );
  $waretype = $wtype == '0' ? '7' : ($wtype == '1' ? '4' : '');

  if (!$waretype) {
    exit(JSON(array('data'=>'', 'msg'=>'没有该类型数据', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1'  AND waresname LIKE '%$keyword%' AND waretype='$waretype' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 新品上市
if ($a == 'get_index_news_ware') {
  $flat = checkInput($_GET['flat']); // 0价格低，1价格高；2销量低，3销量高
  $output = array(
    'list'=>array(),
    'total'=>0
  );
  switch ($flat) {
    case '0': // 价格底
      $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
      $query .= " ORDER BY price ASC".$paging;
      break;
    case '1': // 价格高
      $query = "SELECT sql_calc_found_rows * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
      $query .= " ORDER BY price DESC".$paging;
      break;
    case '2': // 销量低
      $query = "SELECT sql_calc_found_rows *,(select sum(qty) from mall_orderbody where warename= waresname) AS tolnum FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND  billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
      $query .= " ORDER BY tolnum ASC".$paging;
      break;
    case '3': // 销量高
     $query = "SELECT sql_calc_found_rows *,(select sum(qty) from mall_orderbody where warename= waresname) AS tolnum FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND  billdate > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
     $query .= " ORDER BY tolnum DESC".$paging;
      break;
    default:
      break;
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }

  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 店铺主页 hzz
if ($a == 'get_hzz_merchantinfo') {
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
  $query = "SELECT * FROM `view_hzzwares` WHERE `status`=0 AND honsale='1' AND admincode='$shopcode' ORDER BY RAND() LIMIT 0,100";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['product'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// -- 分类 -- 
// 货真真分类 查询(第三级分类，一二级放本地)
// 小程序
if ($a == 'get_mall_category') {
  $flag = checkInput($_GET['flag']); // 分类标签
  $output = array('list'=>array());
  $waredata = array();
  $query = "SELECT  id,billno,cag1,cag2,typename,typeno,typeno2,indexno,image,adbillno,ishidden FROM mall_category where cag1= '$flag' GROUP BY typeno  ORDER BY id ASC";

  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    $ware = array('section'=>'', 'data'=>array()); // 商品
    $mtypeno = $row['typeno'];
    $ware['section'] = $row['cag2'];

    $query2 = "SELECT  id,billno,cag1,cag2,typename,typeno,typeno2,indexno,image,adbillno,ishidden FROM mall_category where typeno='$mtypeno'";
    $result2 = $mysqli->query($query2) or die($mysqli->error);
    while ($row2 = $result2->fetch_assoc()) {
      // array_push($waredata, $row2);
      array_push($ware['data'], $row2);
    }
    array_push($output['list'], $ware);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// app 全部种类
if ($a == 'get_mall_category_app') {
  $flag = checkInput($_GET['flag']); // 分类标签
  $output = array('list'=>array(), 'total'=>0);
  if (!$flag) {
    exit(JSON(array('data'=>'', 'msg'=>'flag不能为空', 'status'=>'1')));
  }
  $query = "SELECT sql_calc_found_rows * FROM mall_category WHERE typeno='$flag' ORDER BY id".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));

}

if ($a == 'get_mall_category_img') {
  $flag = checkInput($_GET['flag']); // 分类标签
  $output = array('list'=>array(), 'total'=>0);
  $query = "SELECT sql_calc_found_rows image1,image2,image3 FROM mall_indcag WHERE cag2='$flag' ORDER BY id".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));

}

// 分类 查询第四级 商品品牌
if ($a == 'get_mall_brand') {
  $typeno = checkInput($_GET['typeno']);
  $output = array('list'=>array(), 'total'=>0);
  if (!$typeno) {
    exit(JSON(array('data'=>'', 'msg'=>'typeno不能为空', 'status'=>'1')));
  }
  $query = "SELECT brandname,image,indexno FROM mall_brand WHERE typeno='$typeno' ORDER BY indexno DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 购物车的数量
if ($a == 'get_car_num') {
  $billno = checkInput($_GET['billno']);
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
  }
  $query = "SELECT IFNULL(sum(qty), 0) AS carnum FROM mall_buycar WHERE userno ='$billno' AND `status`=1 AND mbillno !=''";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row) {
    exit(JSON(array('data'=>$row, 'msg'=>'ok', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'没数据', 'status'=>'1')));
  }
}

//hzz 商品详情
if ($a == 'mall_ware_detail') {
  $goods_no = checkInput($_GET['goods_no']);
  if (!$goods_no) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT a.*,b.billno AS shopcode,b.company_logo AS shoplogo,b.company AS shopname,b.litrmb AS litrmb FROM `view_hzzwares` a LEFT JOIN `team_salesman` b ON a.admincode=b.billno WHERE a.`status`=0 AND a.honsale='1' AND a.wareno='$goods_no' LIMIT 1";
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

// -- 购物车 --
// 提交商品 订单
if ($a == 'set_hzz_mallorder') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, 1);

  // 表头内容
  $mallno = checkInput($obj['head']['salerno']);    //卖方即是商城号
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
  $mfullcut = checkInput($obj['head']['mfullcut']); 
  $mredtip = checkInput($obj['head']['mredtip']); // 使用红包
  $mcoupon = checkInput($obj['head']['mcoupon']); // 优惠金额
  $coubno = checkInput($obj['head']['coubno']); // 优惠billno
  $appver = checkInput($obj['head']['appver']); // app 版本号

  $num = 0;
  $amount = 0;
  $cartnoArr = array();
  $orderhead_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单头编号

  $output = array('list'=>array(), 'orderno'=>'');

  // 检查红包，优惠券的合法
  $query = "SELECT redp FROM team_salesman WHERE billno='$buyerno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($row = $result->fetch_assoc()) {
     if ($row['redp'] < $mredtip) {
      exit(JSON(array('data'=>'', 'msg'=>'请重新选择红包金额', 'status'=>'1')));
     }
  }

  // 表体内容
  $sqlarr = array();
  $mkv = array('cartno'=>'','qty'=>'');
  $mkv2 = array();
  foreach ($obj['body'] as $k => $v) {
    $cartno = checkInput($v['cartno']);
    $exteno = checkInput($v['exteno']); // mall_wares billno
    $wareno = checkInput($v['wareno']);
    $warename = checkInput($v['warename']);
    $wareimage = checkInput($v['wareimage']);
    $price = checkInput($v['price']);
    $qty = checkInput($v['qty']);

    $num += intval($qty);
    $amount += intval($qty) * floatval($price);
    if ($cartno) {
      array_push($cartnoArr, $cartno);
      $mkv['cartno'] = $cartno;
      $mkv['qty'] = $qty;
      array_push($mkv2, $mkv);
    }

    $orderbody_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单体编号
    array_push($sqlarr, "('$orderbody_billno',NOW(),'$orderhead_billno','$mallno','$exteno','$wareno','$warename','$wareimage','$price','$qty')");
  }
  $amount = number_format($amount, 2, '.', '');

  if (count($sqlarr) == 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单体不能为空', 'status'=>'1')));
  }

  $mysqli->query('BEGIN');
  $sql1 = "INSERT INTO mall_orderhead SET billno='$orderhead_billno',billdate=NOW(),salerno='$mallno',salername='$salername',salerlogo='$salerlogo',buyerno='$buyerno',buyername='$buyername',buyeravatar='$buyeravatar',remark='$remark',c_address='$c_address',c_linkman='$c_linkman',c_tel='$c_tel',qty='$num',amount='$amount',payway='$payway',fullcut='$mfullcut',redtip='$mredtip',coupon='$mcoupon',appver='$appver'";
  $sql2 = "INSERT INTO mall_orderbody(billno,billdate,orderno,mallno,exteno,wareno,warename,wareimage,price,qty) VALUES".implode(',', $sqlarr);
  $t1 = $mysqli->query($sql1);
  $t2 = $mysqli->query($sql2);
   
  $output['orderno'] = $orderhead_billno;

  if ($t1 && $t2) {
    if ($payway == '2') { // 货到付款
      // 删除购物车的记录
      if (count($cartnoArr) > 0) {
        $cartnoJoin = implode(',', $cartnoArr);
        $sql3 = "UPDATE mall_buycar SET `status`=0 WHERE billno IN ($cartnoJoin)";
        $mysqli->query($sql3);
      }
      // --
      // foreach ($mkv2 as $k => $v) {
      //   $cartno = checkInput($v['cartno']);
      //   $qty = checkInput($v['qty']);
      //   $sql3 = "UPDATE mall_buycar SET qty=qty-'$qty' WHERE billno = '$cartno'";
      //   $mysqli->query($sql3);
      // }

      // 消掉优惠券的使用数
      $sql4 = "UPDATE mall_mycoup SET `ststus`=1,orderno='$orderhead_billno' WHERE billno = '$coubno'";
      $mysqli->query($sql4);
      // 消掉红包的金额
      $sql5 = "UPDATE team_salesman SET redp= redp - '$mredtip' WHERE billno = '$buyerno'";
      $mysqli->query($sql5);

      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>$output, 'msg'=>'提交成功', 'status'=>'0')));
    } else {
      $orderInfo = array();
      $orderInfo['platform'] = $_p;
      $orderInfo['openid'] = ''; // app 支付不需要
      $orderInfo['body'] = $salername ? $salername : '暂无名称';
      $orderInfo['out_trade_no'] = $orderhead_billno;
      $orderInfo['total_fee'] = $amount;
      $r = _unifiedOrder($orderInfo);
      if ($r['status'] == '0') {

        array_push($output['list'], $r['data']);

        // 删除购物车的记录
        if (count($cartnoArr) > 0) {
          $cartnoJoin = implode(',', $cartnoArr);
          $sql3 = "UPDATE mall_buycar SET `status`=0 WHERE billno IN ($cartnoJoin)";
          $mysqli->query($sql3);
        }
        $mysqli->query('COMMIT');
        $mysqli->query('END');
        exit(JSON(array('data'=>$output, 'msg'=>$r['msg'], 'status'=>'0')));
      } else {
        $mysqli->query('ROLLBACK');
        $mysqli->query('END');
        exit(JSON(array('data'=>'', 'msg'=>$r['msg'], 'status'=>'1')));
      }
    }
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
  }
}

// 暂时文件  小程序
if ($a == 'set_hzz_mallorder2') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, 1);

  // 表头内容
  $mallno = checkInput($obj['head']['salerno']);    //卖方即是商城号
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
  $mfullcut = checkInput($obj['head']['mfullcut']); 
  $mredtip = checkInput($obj['head']['mredtip']); // 使用红包
  $mcoupon = checkInput($obj['head']['mcoupon']); // 优惠金额
  $coubno = checkInput($obj['head']['coubno']); // 优惠billno
  $appver = checkInput($obj['head']['appver']); // app 版本号

  $num = 0;
  $amount = 0;
  $cartnoArr = array();
  $orderhead_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单头编号

  $output = array('list'=>array(), 'orderno'=>'');

  // 检查红包，优惠券的合法
  $query = "SELECT redp FROM team_salesman WHERE billno='$buyerno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($row = $result->fetch_assoc()) {
     if ($row['redp'] < $mredtip) {
      exit(JSON(array('data'=>'', 'msg'=>'请重新选择红包金额', 'status'=>'1')));
     }
  }

  // 表体内容
  $sqlarr = array();
  $mkv = array('cartno'=>'','qty'=>'');
  $mkv2 = array();
  foreach ($obj['body'] as $k => $v) {
    $cartno = checkInput($v['cartno']);
    $exteno = checkInput($v['exteno']); // mall_wares billno
    $wareno = checkInput($v['wareno']);
    $warename = checkInput($v['warename']);
    $wareimage = checkInput($v['wareimage']);
    $price = checkInput($v['price']);
    $qty = checkInput($v['qty']);

    $num += intval($qty);
    $amount += intval($qty) * floatval($price);
    if ($cartno) {
      array_push($cartnoArr, $cartno);
      $mkv['cartno'] = $cartno;
      $mkv['qty'] = $qty;
      array_push($mkv2, $mkv);
    }

    $orderbody_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单体编号
    array_push($sqlarr, "('$orderbody_billno',NOW(),'$orderhead_billno','$mallno','$exteno','$wareno','$warename','$wareimage','$price','$qty')");
  }
  $amount = number_format($amount, 2, '.', '');

  if (count($sqlarr) == 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单体不能为空', 'status'=>'1')));
  }

  $mysqli->query('BEGIN');
  $sql1 = "INSERT INTO mall_orderhead SET billno='$orderhead_billno',billdate=NOW(),salerno='$mallno',salername='$salername',salerlogo='$salerlogo',buyerno='$buyerno',buyername='$buyername',buyeravatar='$buyeravatar',remark='$remark',c_address='$c_address',c_linkman='$c_linkman',c_tel='$c_tel',qty='$num',amount='$amount',payway='$payway',fullcut='$mfullcut',redtip='$mredtip',coupon='$mcoupon',appver='$appver'";
  $sql2 = "INSERT INTO mall_orderbody(billno,billdate,orderno,mallno,exteno,wareno,warename,wareimage,price,qty) VALUES".implode(',', $sqlarr);
  $t1 = $mysqli->query($sql1);
  $t2 = $mysqli->query($sql2);
   
  $output['orderno'] = $orderhead_billno;

  if ($t1 && $t2) {
    if ($payway == '2') { // 货到付款
      // 删除购物车的记录
      // if (count($cartnoArr) > 0) {
      //   $cartnoJoin = implode(',', $cartnoArr);
      //   $sql3 = "UPDATE mall_buycar SET `status`=0 WHERE billno IN ($cartnoJoin)";
      //   $mysqli->query($sql3);
      // }
      // --
      foreach ($mkv2 as $k => $v) {
        $cartno = checkInput($v['cartno']);
        $qty = checkInput($v['qty']);
        $sql3 = "UPDATE mall_buycar SET qty=qty-'$qty' WHERE billno = '$cartno'";
        $mysqli->query($sql3);
      }

      // 消掉优惠券的使用数
      $sql4 = "UPDATE mall_mycoup SET `ststus`=1,orderno='$orderhead_billno' WHERE billno = '$coubno'";
      $mysqli->query($sql4);
      // 消掉红包的金额
      $sql5 = "UPDATE team_salesman SET redp= redp - '$mredtip' WHERE billno = '$buyerno'";
      $mysqli->query($sql5);

      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>$output, 'msg'=>'提交成功', 'status'=>'0')));
    } else {
      $orderInfo = array();
      $orderInfo['platform'] = $_p;
      $orderInfo['openid'] = ''; // app 支付不需要
      $orderInfo['body'] = $salername ? $salername : '暂无名称';
      $orderInfo['out_trade_no'] = $orderhead_billno;
      $orderInfo['total_fee'] = $amount;
      $r = _unifiedOrder($orderInfo);
      if ($r['status'] == '0') {

        array_push($output['list'], $r['data']);

        // 删除购物车的记录
        if (count($cartnoArr) > 0) {
          $cartnoJoin = implode(',', $cartnoArr);
          $sql3 = "UPDATE mall_buycar SET `status`=0 WHERE billno IN ($cartnoJoin)";
          $mysqli->query($sql3);
        }
        $mysqli->query('COMMIT');
        $mysqli->query('END');
        exit(JSON(array('data'=>$output, 'msg'=>$r['msg'], 'status'=>'0')));
      } else {
        $mysqli->query('ROLLBACK');
        $mysqli->query('END');
        exit(JSON(array('data'=>'', 'msg'=>$r['msg'], 'status'=>'1')));
      }
    }
  } else {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
  }
}

// 商家  满减规则
if ($a == 'get_mbuy_disc') {
  $money = checkInput($_GET['money']);
  $billno = checkInput($_GET['billno']); // 商家用户 billno
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT * FROM  `mall_discount` WHERE admincode='$billno' AND `status`='0'";
  if ($money) {
    $query .= " AND litrmb<= $money ORDER BY litrmb DESC LIMIT 1";
    $result = $mysqli->query($query) or die($mysqli->error);
    $goodsInfo = $result->fetch_assoc();
    if (!$goodsInfo) {
      exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
    }
    exit(JSON(array('data'=>$goodsInfo, 'msg'=>'ok', 'status'=>'0')));
  } else {
     $query .= " ORDER BY litrmb ASC";
     $output = array('list'=>array());
     $result = $mysqli->query($query) or die($mysqli->error);
     while ($row = $result->fetch_assoc()) {
        array_push($output['list'], $row);
     }
     exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
  }
 }

// -- 我的 -- 
// 注册 hzz
if ($a == 'hzz_register') {
  $phone = checkInput($_GET['phone']);
  $password = checkInput($_GET['password']);
  $captcha = checkInput($_GET['captcha']);
  $app = checkInput($_GET['app']);    //  0 好业绩用户， 1 货真真零售商
  $username = checkInput($_GET['username']);
  $sex = checkInput($_GET['sex']);
  $company = checkInput($_GET['company']);
  $province = checkInput($_GET['province']);
  $city = checkInput($_GET['city']);
  $town = checkInput($_GET['town']);
  $street = checkInput($_GET['street']);
  $companyaddress = checkInput($_GET['companyaddress']);

  if (!$phone) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
  }
  if (!$password) {
    exit(JSON(array('data'=>'', 'msg'=>'密码不能为空', 'status'=>'1')));
  }
  if (!$captcha) {
    exit(JSON(array('data'=>'', 'msg'=>'验证码不能为空', 'status'=>'1')));
  }

  $password = setstrmd5(md5($password)); // md5加密

  // 检查验证码
  $query = "SELECT id, randno FROM team_smscache WHERE tel='$phone' AND billdate>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) and access=0 ORDER BY billdate DESC LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    if ($data['randno'] != $captcha) {
      exit(JSON(array('data'=>'', 'msg'=>'验证码错误', 'status'=>'1')));
    } else {
      // 验证码已使用过，需要改为失效
      $sql = "UPDATE team_smscache SET access=1 WHERE id='{$data['id']}'";
      $mysqli->query($sql);
    }
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'验证码无效，请重新获取', 'status'=>'1')));
  }

  // 检查账号
  $query = "SELECT id,billno,allow FROM team_salesman WHERE userno='$phone' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    if ($data['allow'] == '1') {   // -1审核拒绝，0临时状态， 1 通过,   2正在审核
      exit(JSON(array('data'=>'', 'msg'=>'该账号已拥有登录权限', 'status'=>'1')));
    } else if ($data['allow'] == '2') {
      exit(JSON(array('data'=>'', 'msg'=>'该账号正在审批中，请等待！', 'status'=>'1')));
    } else if ($data['allow'] == '-1') {
      exit(JSON(array('data'=>'', 'msg'=>'该账号审批不通过，请联系管理员重审！', 'status'=>'1')));
    } else {
      $billno = $data['billno'];
      $sql = "UPDATE team_salesman SET username='$username', nickname='$username', userno='$phone',
         sex='$sex', company='$company', province='$province', city='$city', town='$town', street='$street',companyaddress='$companyaddress', app='$app',allow=2,regdate2=NOW() WHERE billno='$billno'";
      if (!$mysqli->query($sql)) {
        exit(JSON(array('data'=>'', 'msg'=>'提交失败', 'status'=>'1')));
      } 
      // 重新发送审核信息 -2副经理（秘书助手）、 -1 经理
      $msg= "用户名：".$username."\n店铺名：".$company."\n地址：".$province.$city.$town.$street;
      $query = "SELECT userno from `teams` where admin='13696886206' AND (isteam='-2' OR isteam='-1') AND astatus= '1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
         $cphone = $row['userno'];
         $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='货真真用户注册',creuserno='$phone',`admin`='13696886206', title='货真真用户注册审核',
            `message`='$msg',userno='$cphone',`type`=5,certuid='$phone'";
         $mysqli->query($sql);
      }
      exit(JSON(array('data'=>'', 'msg'=>'该账号成功提交审核', 'status'=>'1')));
    }
  }

  // 注册
  $sql = "INSERT INTO team_salesman SET username='$username', nickname='$username', billno='$_billno', `password`='$password', userno='$phone',
     sex='$sex', company='$company', province='$province', city='$city', town='$town', street='$street', companyaddress='$companyaddress', app='$app',allow=2,regdate2=NOW()";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'注册失败', 'status'=>'1')));
  } else {
     // 重新发送审核信息 -2副经理（秘书助手）、 -1 经理
      $msg= "用户名：".$username."\n店铺名：".$company."\n地址：".$province.$city.$town.$street;
      $query = "SELECT userno from `teams` where admin='13696886206' AND (isteam='-2' OR isteam='-1') AND astatus= '1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
         $cphone = $row['userno'];
         $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='货真真用户注册',creuserno='$phone',`admin`='13696886206', title='货真真用户注册审核',
            `message`='$msg',userno='$cphone',`type`=5,certuid='$phone'";
         $mysqli->query($sql);
      }
    exit(JSON(array('data'=>'', 'msg'=>'注册成功', 'status'=>'0')));
  }
}

// wx 小程序 
if ($a == 'get_openid') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $code = checkInput($obj['code']);
  $appId = checkInput($obj['appId']);
  $secret = $serverConfig->getHzzWxSecret();

  $AccessTokenUrl = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appId."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
  $result = json_decode(file_get_contents($AccessTokenUrl));
  exit(JSON(array('data'=>$result, 'msg'=>'', 'status'=>'0')));
  
 }

// 微信qq登录--绑定手机号
if ($a == 'hzz_wxbindphone') {
  $phone = checkInput($_GET['phone']);
  $captcha = checkInput($_GET['captcha']); // 验证码
  $wkey = checkInput($_GET['wkey']);  // 区分微信,qq， 小程序 wxmin
  $openid = checkInput($_GET['openid']);
  $mstr="";
  $app = checkInput($_GET['app']);    //  0 好业绩用户， 1 货真真零售商
  $username = checkInput($_GET['username']);
  $sex = checkInput($_GET['sex']);
  $company = checkInput($_GET['company']);
  $province = checkInput($_GET['province']);
  $city = checkInput($_GET['city']);
  $town = checkInput($_GET['town']);
  $street = checkInput($_GET['street']);
  $companyaddress = checkInput($_GET['companyaddress']);

  if (!$phone) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
  }
  if (!$openid) {
    exit(JSON(array('data'=>'', 'msg'=>'openid不能为空', 'status'=>'1')));
  }
  if (!$captcha) {
    exit(JSON(array('data'=>'', 'msg'=>'验证码不能为空', 'status'=>'1')));
  }

  $password = setstrmd5(md5($password)); // md5加密

  // 检查验证码
  $query = "SELECT id, randno FROM team_smscache WHERE tel='$phone' AND billdate>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) and access=0 ORDER BY billdate DESC LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    if ($data['randno'] != $captcha) {
      exit(JSON(array('data'=>'', 'msg'=>'验证码错误', 'status'=>'1')));
    } else {
      // 验证码已使用过，需要改为失效
      $sql = "UPDATE team_smscache SET access=1 WHERE id='{$data['id']}'";
      $mysqli->query($sql);
    }
  } else {
    //exit(JSON(array('data'=>'', 'msg'=>'验证码无效，请重新获取', 'status'=>'1')));
  }

  // 检查账号
  if($wkey == 'wx'){
    $mstr=" wxhzz= '$openid'";
  }else if ($wkey == 'wxmin'){  // 小程序
    $mstr=" wxminhzz= '$openid'";
  }else{
    $mstr=" qqhzz= '$openid'";
  }
  $query = "SELECT id,allow FROM team_salesman WHERE userno='$phone' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) { // 该账号已存在
    $flat = $data['allow'];  // -1审核拒绝，0临时状态， 1 通过,   2正在审核
    $sql = "UPDATE `team_salesman` SET regdate2=NOW(),".$mstr;
    $sql .= " WHERE userno='$phone' ";
      if (!$mysqli->query($sql)) {
         exit(JSON(array('data'=>'', 'msg'=>'绑定失败', 'status'=>'1')));
      } else {
         if ($flat == '0') {
          // 重新发送审核信息 -2副经理（秘书助手）、 -1 经理
          $msg= "用户名：".$username."\n店铺名：".$company."\n地址：".$province.$city.$town.$street;
          $query = "SELECT userno from `teams` where admin='13696886206' AND (isteam='-2' OR isteam='-1') AND astatus= '1'";
          $result = $mysqli->query($query) or die($mysqli->error);
          while ($row = $result->fetch_assoc()) {
             $cphone = $row['userno'];
             $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='货真真用户注册',creuserno='$phone',`admin`='13696886206', title='货真真用户注册审核',
                `message`='$msg',userno='$cphone',`type`=5,certuid='$phone'";
             $mysqli->query($sql);
          }
         }
         exit(JSON(array('data'=>'', 'msg'=>'绑定成功', 'status'=>'0')));
      }  
    }
  // 初始密码
    $rp = substr($phone, -4);
    $mrepass = setstrmd5(md5($rp)); // md5加密

  // 注册
  $sql = "INSERT INTO team_salesman SET username='$username', nickname='$username', billno='$_billno', repassword='$mrepass',userno='$phone',
     sex='$sex', company='$company', province='$province', city='$city', town='$town', street='$street', app='$app', companyaddress='$companyaddress',allow=2, ".$mstr;
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'注册失败', 'status'=>'1')));
  } else {
     // 重新发送审核信息 -2副经理（秘书助手）、 -1 经理
      $msg= "用户名：".$username."\n店铺名：".$company."\n地址：".$province.$city.$town.$street;
      $query = "SELECT userno from `teams` where admin='13696886206' AND (isteam='-2' OR isteam='-1') AND astatus= '1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
         $cphone = $row['userno'];
         $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='货真真用户注册',creuserno='$phone',`admin`='13696886206', title='货真真用户注册审核',
            `message`='$msg',userno='$cphone',`type`=5,certuid='$phone'";
         $mysqli->query($sql);
      }
    exit(JSON(array('data'=>'', 'msg'=>'注册成功', 'status'=>'0')));
  }
}

// 普通登录
if ($a == 'login_hzz') {
  $uid = checkInput($_GET['uid']);
  $upass = checkInput($_GET['upass']);

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'账号不能为空', 'status'=>'1')));
  }
  if (!$upass) {
    exit(JSON(array('data'=>'', 'msg'=>'密码不能为空', 'status'=>'1')));
  }

  $mrepass = setstrmd5(md5($upass)); // md5加密


  $query = "SELECT *, if(TIMESTAMPDIFF(MINUTE,lastdate,NOW())<=30,true,false) AS deadline FROM `team_salesman` WHERE
              userno='$uid' AND (`password`='$mrepass' OR repassword='$mrepass')";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();

  // 判断是否登录成功
  if ($data) {
    if ($data['password'] == $mrepass) { // 使用正常密码
      if ($data['allow'] == '2') { // -1审核拒绝，0临时状态， 1 通过,   2正在审核
         exit(JSON(array('data'=>'', 'msg'=>'该账号正在审批中，请等待！', 'status'=>'1')));
      } else if ($data['allow'] == '-1') {
        exit(JSON(array('data'=>'', 'msg'=>'该账号审批不通过，请联系管理员重审！', 'status'=>'1')));
      } else if ($data['allow'] == '0') {
        exit(JSON(array('data'=>'', 'msg'=>'该账号不是货真真账户，请去注册！', 'status'=>'1')));
      }
    } else { // 使用临时密码
      if ($data['deadline']) {
        // 有效
      } else {
        // 无效
        exit(JSON(array('data'=>'', 'msg'=>'临时密码已失效，请找回密码', 'status'=>'1')));
      }
    }
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'账号或密码错误', 'status'=>'1')));
  }

  // 限定返回的用户信息
  $outdata=array('billno'=>$data['billno'],'username'=>$data['username'],'userno'=>$data['userno'],'address'=>$data['address'],'phone'=>$data['phone'],'tel'=>$data['tel'],
          'team'=>$data['team'],'teamname'=>$data['teamname'],'teamdate'=>$data['teamdate'],'image'=>$data['image'],
          'image1'=>$data['image1'],'image2'=>$data['image2'],'memo'=>$data['memo'],
          'avatar'=>$data['image'],'pw_strength'=>'weak','admin'=>$uid,'pubkey'=>'');

  exit(JSON(array('data'=>$outdata, 'msg'=>'登录成功', 'status'=>'0')));
}


// 微信，QQ 登录 小程序
 if ($a == 'wxlogin_hzz') {
  $openid = checkInput($_GET['openid']);
  $wkey = checkInput($_GET['wkey']); // 小程序wxmin

  if (!$openid) {
    exit(JSON(array('data'=>'', 'msg'=>'openid不能为空', 'status'=>'1')));
  }
  // 判断是否有该微信的openid
  if($wkey == 'wx'){
    $query = "SELECT * FROM `team_salesman` WHERE wxhzz='$openid' LIMIT 1";
  } else if($wkey == 'wxmin'){
    $query = "SELECT * FROM `team_salesman` WHERE wxminhzz='$openid' LIMIT 1";
  } else {
    $query = "SELECT * FROM `team_salesman` WHERE qqhzz='$openid' LIMIT 1";
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();

  if(!$data){
    exit(JSON(array('data'=>'', 'msg'=>'请重新绑定手机号', 'status'=>'-1')));
  } else {
    if ($data['allow'] == '2') {
        exit(JSON(array('data'=>'', 'msg'=>'该账号正在审批中，请等待！', 'status'=>'1')));
    } else if ($data['allow'] == '-1') {
        exit(JSON(array('data'=>'', 'msg'=>'该账号审批不通过，请联系管理员重审！', 'status'=>'1')));
    } else if ($data['allow'] == '0') {
        exit(JSON(array('data'=>'', 'msg'=>'该账号不是货真真账户，请去注册！', 'status'=>'1')));
    }
  }

  // 限定返回的用户信息
  $outdata=array('billno'=>$data['billno'],'username'=>$data['username'],'userno'=>$data['userno'],'address'=>$data['address'],'phone'=>$data['phone'],'tel'=>$data['tel'],
          'team'=>$data['team'],'teamname'=>$data['teamname'],'teamdate'=>$data['teamdate'],'image'=>$data['image'],
          'image1'=>$data['image1'],'image2'=>$data['image2'],'memo'=>$data['memo'],'pw_strength'=>'weak','admin'=>$uid,'pubkey'=>'',
          'avatar'=>$data['image']);

  exit(JSON(array('data'=>$outdata, 'msg'=>'登录成功', 'status'=>'0')));
}

//小程序用户选择位置
if($a == 'get_position'){
 $level = checkInput($_GET['level']);//位置级别(0:省，1:市，2:区，3:街道);
 $code_name='PROVINCE_CODE';
 switch($level){
  case 1:
   $code_name='PROVINCE_CODE';
   break;
  case 2:
   $code_name='CITY_CODE';
   break;
  case 3:
   $code_name='AREA_CODE';
   break;
 }
 switch($level){
  case 0:
   $level='bs_province';
   break;
  case 1:
   $level='bs_city';
   break;
  case 2:
   $level='bs_area';
   break;
  case 3:
   $level='bs_street';
   break;
 }
 
 $code = checkInput($_GET['code']);//位置的标识码，用于查询下一级的位置信息
 $output = array('list'=>array(),'total'=>0);
 
 if(!$level){
  exit(JSON(array('data'=>'','msg'=>'需要传入 位置级别level 参数','status'=>'1')));
 }
 
 if(!$code){
  $query = "select * from $level";
 }else{
  $query = "select * from $level where $code_name=$code";
 }
 $result=$mysqli->query($query) or die($mysqli->error);
    
 while ($row = $result->fetch_assoc()){
  array_push($output['list'],$row);
 }
 exit(JSON(array('data'=>$output,'msg'=>'ok','status'=>'0')));
}

// -- 用户 --
// 全部 优惠券 - 领券中心
if ($a == 'get_coupon') {
  $sbillno = checkInput($_GET['sbillno']); // 用户 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *,(select cbillno from mall_mycoup WHERE sbillno='$sbillno' AND cbillno=`mall_coupon`.billno ) AS cbillno FROM mall_coupon 
           WHERE DATE_FORMAT(enddate,'%Y-%m-%d %H:%i:%S') >= DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%S') ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 领取优惠券 到 个人中心
if ($a == 'set_coupon') { // 要单独 创建用户领取优惠券的表
  $sbillno = checkInput($_GET['sbillno']);
  $username = checkInput($_GET['username']);
  $phone = checkInput($_GET['phone']);
  $cbillno = checkInput($_GET['cbillno']); // 优惠券的 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }
  if (!$cbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'找不到优惠券', 'status'=>'1')));
  }
  // 是否用户已领
  $query = "SELECT id FROM mall_mycoup WHERE sbillno='$sbillno' AND cbillno='$cbillno' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row) {
    exit(JSON(array('data'=>'', 'msg'=>'优惠券不能重复领取', 'status'=>'1')));
  }

  // 是否商家还有券
  $query3 = "SELECT status,num,limitnum FROM mall_coupon WHERE billno='$cbillno' LIMIT 1";
  $result3 = $mysqli->query($query3) or die($mysqli->error);
  $row3 = $result3->fetch_assoc();
  if ($row3) {
    if ($row3['status'] == -1) {
       exit(JSON(array('data'=>'', 'msg'=>'该优惠券已经下架', 'status'=>'1')));
    } else if ($row3['status'] == 1) {
       exit(JSON(array('data'=>'', 'msg'=>'该优惠券暂时停止领取', 'status'=>'1')));
    } else {
      if ($row3['limitnum'] != '') { // 有限
        if ($row3['num'] >= $row3['limitnum']) {
          exit(JSON(array('data'=>'', 'msg'=>'该优惠券已经领完了', 'status'=>'1')));
        }
      }
    }
  }

  $query2 = "INSERT INTO mall_mycoup set billdate=NOW(),billno='$_billno',sbillno='$sbillno',username='$username',phone='$phone',
           cbillno='$cbillno'";
 if (!$mysqli->query($query2)) {
    $output = array('data'=>'', 'msg'=>'领取失败', 'status'=>'1');
 }
  $sql = "UPDATE mall_coupon set num=num+1 WHERE billno='$cbillno'";
  $mysqli->query($sql);
  exit(JSON(array('data'=>'', 'msg'=>'领取成功', 'status'=>'0')));
}

// 个人优惠券
if ($a == 'get_my_coupon') {
  $sbillno = checkInput($_GET['sbillno']); // 用户 billno
  $salerno = checkInput($_GET['salerno']);
  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *,b.`ststus` FROM `mall_coupon` a Left JOIN `mall_mycoup` b ON a.billno = b.cbillno
           WHERE DATE_FORMAT(a.enddate,'%Y-%m-%d %H:%i:%S') >= DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%S') AND b.sbillno='$sbillno'";
  if ($salerno) {
    $query .= " AND (a.admincode ='$salerno' OR a.`stype` ='0')";
  }
  $query .= " ORDER BY a.billdate DESC".$paging;       

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// 红包记录 日志
if ($a == 'get_my_mall_tip') {
  $sbillno = checkInput($_GET['sbillno']); // 用户 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `mall_tip` where userno='$sbillno' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// 领取红包
if ($a == 'got_mall_tip') {
  $sbillno = checkInput($_GET['sbillno']); // 用户 billno
  $tipno = checkInput($_GET['tipno']); // 红包表 的 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }
  if (!$tipno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "update mall_tip set geted=1 where userno='$sbillno' and billno='$tipno' limit 1";
 if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'领取失败', 'status'=>'1');
 }
  exit(JSON(array('data'=>'', 'msg'=>'领取成功', 'status'=>'0')));
}

// 获取红包余额
if ($a == 'get_red_packet_balance') {
  $admin = checkInput($_GET['admin']); // 用户 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT redp FROM team_salesman WHERE billno='$admin'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
   exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}


// 售后服务 获取  
if ($a == 'get_aftersale') {
  $sbillno = checkInput($_GET['sbillno']); // 购买人billno
  $output = array('list'=>array(), 'total'=>0);

  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  // -1 删除 0 待付款 2 已付款/待发货 3 待收货 4 用户取消 5 待付款超时 6 待评价/确认收货  .   15天 有效期
  $query = "SELECT sql_calc_found_rows *,(amount-fullcut-redtip-coupon) AS payrmb,(TIMESTAMPDIFF(DAY,billdate,DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%S')) > 15) AS ismon,
           (SELECT astype FROM mall_aftersale WHERE sbillno='$sbillno' AND cbillno=`mall_orderhead`.billno LIMIT 1) AS mflat1,
           (SELECT stype FROM mall_aftersale WHERE sbillno='$sbillno' AND cbillno=`mall_orderhead`.billno LIMIT 1) AS mflat2
           FROM mall_orderhead WHERE buyerno='$sbillno' AND billstate='6' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 申请售后 上传
if ($a == 'set_aftersale') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $sbillno = checkInput($obj['sbillno']);
  $username = checkInput($obj['username']);
  $phone = checkInput($obj['phone']);
  $cbillno = checkInput($obj['cbillno']);
  $astype = checkInput($obj['astype']);
  $reason = checkInput($obj['reason']);
  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);

  $output = array('list'=>array(), 'total'=>0);
  if (!$sbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }
  if (!$cbillno) {
    exit(JSON(array('data'=>'', 'msg'=>'商品标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT id FROM mall_aftersale WHERE sbillno='$sbillno' AND cbillno='$cbillno' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row) {
    exit(JSON(array('data'=>'', 'msg'=>'你已经申请了该商品售后服务', 'status'=>'1')));
  }

  $query2 = "INSERT INTO mall_aftersale set billdate=NOW(),billno='$_billno',sbillno='$sbillno',username='$username',phone='$phone',
           cbillno='$cbillno',reason='$reason',astype='$astype',image1='$image1',image2='$image2',image3='$image3'";
 if (!$mysqli->query($query2)) {
    $output = array('data'=>'', 'msg'=>'申请失败', 'status'=>'1');
 }
  exit(JSON(array('data'=>'', 'msg'=>'申请成功', 'status'=>'0')));
}

/////////////////////////////////////WEB后台货真真首页内容设置///////////////////////////////////////////////

//  首页滚动广告
if ($a == 'get_index_ad_0') { 
  $query = "SELECT sql_calc_found_rows * FROM mall_index where location = 0 ORDER BY billdate DESC".$paging;
  $output = array('list'=>array(), 'total'=>0);
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

//  取得首页的5大图
if ($a == 'get_index_ad_1') { 
  $query = "SELECT sql_calc_found_rows * FROM mall_index where location in (1,2) ORDER BY billdate DESC".$paging;
  $output = array('list'=>array(), 'total'=>0);
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

//  取得首页模式滚动广告专场
if ($a == 'get_index_ad_2') { 
  $query = "SELECT sql_calc_found_rows * FROM mall_index where location = 3 ORDER BY billdate DESC".$paging;
  $output = array('list'=>array(), 'total'=>0);
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}
//百货品牌广告
if ($a == 'get_index_ad_3') { 
  $query = "SELECT sql_calc_found_rows * FROM mall_index where location = 4 ORDER BY billdate DESC".$paging;
  $output = array('list'=>array(), 'total'=>0);
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}
//酒水品牌广告
if ($a == 'get_index_ad_4') { 
  $query = "SELECT sql_calc_found_rows * FROM mall_index where location = 5 ORDER BY billdate DESC".$paging;
  $output = array('list'=>array(), 'total'=>0);
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

//设置首页广告内容（通用）
if ($a == 'set_index_ad') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $billno = checkInput($obj['billno']);
  $keyword = checkInput($obj['keyword']);
  $image = checkInput($obj['image']);
  $pic = checkInput($obj['pic']);  
  $title = checkInput($obj['title']);  
  $begindate = checkInput($obj['begindate']); 
  $enddate = checkInput($obj['enddate']); 
	
  $output = array('list'=>array(), 'total'=>0);
  
  $sql = "UPDATE mall_index SET keyword='$keyword',image='$image',pic='$pic',begindate='$begindate',title='$title',enddate='$enddate' where billno='$billno'";
	
  if (!$mysqli->query($sql)) {    
		exit(JSON(array('data'=>'', 'msg'=>'操作失败'.$mysqli->error, 'status'=>'1')));
	}
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

 // -- 平板  商城出货 --
if ($a == 'set_hd_wares') {
   $productno = checkInput($_GET['productno']); // 编码
   $username = checkInput($_GET['username']);
   $userno = checkInput($_GET['userno']); // 手机号
   if (!$productno) {
    exit(JSON(array('data'=>'', 'msg'=>'编码不能为空', 'status'=>'1')));
   }
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
   }
  $output = array('list'=>array(), 'total'=>0);

  $query = "SELECT wareno,waresname,price,unit,image1 FROM `view_hzzwares` WHERE productno = '$productno' AND `status`=0 AND honsale='1' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if($row){
    $wareno = $row['wareno'];
    $waresname = $row['waresname'];
    $price = $row['price'];
    $unit = $row['unit'];
    $image1 = $row['image1'];

    $query2 = "SELECT id FROM `mall_sellwares` WHERE userno = '$userno' AND wareno='$wareno' AND isprint=0 LIMIT 1";
    $result2 = $mysqli->query($query2) or die($mysqli->error);
    if ($row2 = $result2->fetch_assoc()) { // 存在
      $sql = "UPDATE mall_sellwares SET qnum=qnum+1 WHERE userno = '$userno' AND wareno='$wareno' AND isprint=0";
        if (!$mysqli->query($sql)) {
          exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
        } 
    } else { // 不存在
       $sql = "INSERT INTO mall_sellwares SET billno='$_billno', username='$username',userno='$userno',wareno='$wareno',waresname='$waresname',
              price='$price',unit='$unit',qnum=1,image1='$image1'";
        if (!$mysqli->query($sql)) {
          exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
        } 
    }
    exit(JSON(array('data'=>$image1, 'msg'=>'ok', 'status'=>'0')));

  } else {
     exit(JSON(array('data'=>'', 'msg'=>'无法找到该商品', 'status'=>'1')));
  }
}

if ($a == 'get_hd_wares') {
   $username = checkInput($_GET['username']);
   $userno = checkInput($_GET['userno']); // 手机号

   if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
   }
  $output = array('list'=>array(), 'total'=>0, 'toprice'=>'0.00');

  $sql = "SELECT sum(qnum) AS total,sum(qnum*price) AS toprice FROM `mall_sellwares` WHERE userno = '$userno' AND isprint=0";
  $result = $mysqli->query($sql) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if($row){
     $output['total'] = $row['total'];
     $output['toprice'] = $row['toprice'];
  }

   // 数据源
   $sql2 = "SELECT * FROM `mall_sellwares` WHERE userno = '$userno' AND isprint=0 ORDER BY billdate DESC";
    $result2 = $mysqli->query($sql2) or die($mysqli->error);
    while ($row2 = $result2->fetch_assoc()) {
      array_push($output['list'], $row2);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

//删除
if ($a == 'del_hd_wares') {
  $billno= checkInput($_GET['billno']);
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
   }
  $query = "SELECT qnum FROM `mall_sellwares` WHERE billno='$billno'";
  $dresult = $mysqli->query($query) or die($mysqli->error);
  $data = $dresult->fetch_assoc();
  if($data['qnum'] > 1){
    $sql = "UPDATE mall_sellwares SET qnum=qnum-1 WHERE billno='$billno'";    
    if (!$mysqli->query($sql)) {
      exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
    }
     exit(JSON(array('data'=>'', 'msg'=>'已减少1件商品', 'status'=>'0')));
  } else {
    $sql = "DELETE FROM mall_sellwares  WHERE billno='$billno' LIMIT 1";
    if (!$mysqli->query($sql)) {
       exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
    } 
    exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
  }
}

// 更新
if ($a == 'update_hd_wares') {
  $userno= checkInput($_GET['userno']);
  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
   }
     $sql = "UPDATE mall_sellwares SET isprint=1 WHERE userno = '$userno' AND isprint=0";    
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>'更新失败', 'status'=>'1')));
  } 
  exit(JSON(array('data'=>'', 'msg'=>'更新成功', 'status'=>'0')));
}

// 出货记录
if ($a == 'get_hd_records') {
   $userno = checkInput($_GET['userno']); // 手机号

   if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
   }
  $output = array('list'=>array());

   // 数据源
   $sql2 = "SELECT * FROM `mall_sellwares` WHERE userno = '$userno' AND isprint=1 ORDER BY billdate DESC";
    $result2 = $mysqli->query($sql2) or die($mysqli->error);
    while ($row2 = $result2->fetch_assoc()) {
      array_push($output['list'], $row2);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

//////////// 优惠券 //////////
// - 商家查询 - (后台网页用)
if ($a == 'get_mall_coupon') {
  $admin = checkInput($_GET['admin']);
  $output = array('list'=>array(), 'total'=>0);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'标识不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *,(DATE_FORMAT(enddate,'%Y-%m-%d %H:%i:%S') >= DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%S')) AS isout FROM mall_coupon WHERE admin='$admin' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// - 商家发布 - (后台网页用)
if ($a == 'set_mall_coupon') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $billno = checkInput($obj['billno']); // 更改用
  $admin = checkInput($obj['admin']);
  $keyword = checkInput($obj['keyword']); // 关联商品的 关键字
  $image = checkInput($obj['image']);
  $title = checkInput($obj['title']);  // 优惠标题说明
  $begindate = checkInput($obj['begindate']);
  $enddate = checkInput($obj['enddate']); // 结束时间
  $rmb = checkInput($obj['rmb']);      // 金额
  $krmb = checkInput($obj['krmb']);    // 满足的金额条件
  $kwnum = checkInput($obj['kwnum']);
  $stype = checkInput($obj['stype']);  // 优惠券类型: 0 全品类，1 限品类
  $status = checkInput($obj['status']);// -1下架   0上架中  1暂时停用. 默认是 0
  $limitnum = checkInput($obj['limitnum']); // 限定数量, 如果不填则没限定
  $mstr = "发布";

  $output = array('list'=>array(), 'total'=>0);
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }

  if ($billno) {
    $mstr = "更改";
    $query2 = "UPDATE mall_coupon set billdate=NOW(),admin='$admin',keyword='$keyword',image='$image',
         title='$title',begindate='$begindate',enddate='$enddate',rmb='$rmb',krmb='$krmb',stype='$stype',status='$status',limitnum='$limitnum',kwnum='$kwnum' WHERE billno='$billno'";
  } else {
    $query2 = "INSERT INTO mall_coupon set billdate=NOW(),billno='$_billno',admin='$admin',keyword='$keyword',image='$image',
           title='$title',begindate='$begindate',enddate='$enddate',rmb='$rmb',krmb='$krmb',stype='$stype',status='$status',limitnum='$limitnum',kwnum='$kwnum'";
  }
 if (!$mysqli->query($query2)) {
    $output = array('data'=>'', 'msg'=>$mstr.'失败', 'status'=>'1');
 }
  exit(JSON(array('data'=>'', 'msg'=>$mstr.'成功', 'status'=>'0')));
}

// 优惠券 上架，下架 
if ($a == 'set_mcoup_staus') {
  $billno= checkInput($_GET['billno']);
  $flat= checkInput($_GET['flat']); // -1下架   0上架
  $mstr = $flat == '-1' ? '下架' : '上架';
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
   }
  $sql = "UPDATE mall_coupon SET `status`='$flat' WHERE billno = '$billno'";    
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>$mstr.'失败', 'status'=>'1')));
  } 
  exit(JSON(array('data'=>'', 'msg'=>$mstr.'成功', 'status'=>'0')));
}

////////////// 满减优惠 ////////////
// 满减 查询
if ($a == 'get_mall_disc') {
  $billno = checkInput($_GET['billno']); // 商家用户 billno
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT * FROM  `mall_discount` WHERE admincode='$billno'";
  $output = array('list'=>array());
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
      array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
 }
// - 满减 商家发布 - (后台网页用)
if ($a == 'set_mall_disc') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $billno = checkInput($obj['billno']); // 更改用
  $admincode = checkInput($obj['admincode']);
  $litrmb = checkInput($obj['litrmb']);
  $rmb = checkInput($obj['rmb']);
  $title = checkInput($obj['title']);  // 标题说明
  $mstr = "发布";
  if (!$admincode) {
    exit(JSON(array('data'=>'', 'msg'=>'用户标识不能为空', 'status'=>'1')));
  }
  if ($billno) {
    $mstr = "更改";
    $query2 = "UPDATE mall_discount set admincode='$admincode',litrmb='$litrmb',rmb='$rmb',title='$title' WHERE billno='$billno'";
  } else {
    $query2 = "INSERT INTO mall_discount set billno='$_billno',admincode='$admincode',litrmb='$litrmb',rmb='$rmb',title='$title'";
  }
 if (!$mysqli->query($query2)) {
    $output = array('data'=>'', 'msg'=>$mstr.'失败', 'status'=>'1');
 }
  exit(JSON(array('data'=>'', 'msg'=>$mstr.'成功', 'status'=>'0')));
}
// 满减 上架，下架 
if ($a == 'set_mdisc_staus') {
  $billno= checkInput($_GET['billno']);
  $flat= checkInput($_GET['flat']); // -1下架   0上架
  $mstr = $flat == '-1' ? '下架' : '上架';
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
   }
  $sql = "UPDATE mall_discount SET `status`='$flat' WHERE billno = '$billno'";    
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>$mstr.'失败', 'status'=>'1')));
  } 
  exit(JSON(array('data'=>'', 'msg'=>$mstr.'成功', 'status'=>'0')));
}

////////////// 售后服务 (商家)////////////
// 售后 查询
if ($a == 'get_after_sale') {
  $shopcode = checkInput($_GET['shopcode']); // 商家 billno
  $output = array('list'=>array(), 'total'=>0);
  if (!$shopcode) {
    exit(JSON(array('data'=>'', 'msg'=>'标识不能为空', 'status'=>'1')));
  }
  $query = "SELECT sql_calc_found_rows a.* FROM `mall_aftersale` a LEFT JOIN `mall_orderhead` b ON a.cbillno = b.billno 
           WHERE b.salerno='$shopcode' ORDER BY a.billdate ASC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
 }
// 售后 审批
 if ($a == 'set_afsale_stus') {
  $billno= checkInput($_GET['billno']);
  $answer= checkInput($_GET['answer']); // 商家答复
  $flat= checkInput($_GET['flat']); // -1拒绝   1 同意
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
   }
  $sql = "UPDATE mall_aftersale SET `stype`='$flat',`answer`='$answer' WHERE billno = '$billno'";    
  if (!$mysqli->query($sql)) {
     exit(JSON(array('data'=>'', 'msg'=>'审核失败', 'status'=>'1')));
  } 
  exit(JSON(array('data'=>'', 'msg'=>'审核成功', 'status'=>'0')));
}



