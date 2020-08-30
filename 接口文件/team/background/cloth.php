<?php
// 纺织生产

// 查询生产原料表结构数据
if ($a == 'getcrudelist') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $custname = checkInput($_GET['custname']); // 客户名称
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_crude` WHERE `admin`='$admin'";
  if ($begindate) {
    $query .=" AND billdate between '$begindate' AND '$enddate'";
  }
  if ($custname) {
    $query .=" AND custname LIKE '%$custname%'";
  }
  $query .= "ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 工艺规格 查询
if ($a == 'getartlist') {
  $admin = checkInput($_GET['admin']);
  $artname = checkInput($_GET['artname']);
  $custname = checkInput($_GET['custname']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_art` WHERE `admin`='$admin'";
  if ($artname) {
    $query .= " AND artname LIKE '%$artname%'";
  }
  if ($custname) {
    $query .= " AND custname LIKE '%$custname%'";
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

// 工艺规格 删除
if ($a == 'delart') {
  $bno = checkInput($_GET['bno']);

  if (!$bno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_art` WHERE billno IN ($bno)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 工艺规格 新增、编辑
if ($a == 'setart') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno = checkInput($obj['bno']);
  $uid = checkInput($obj['uid']);
  $admin = checkInput($obj['admin']);
  $custno = checkInput($obj['custno']); // 客户编号
  $custname = checkInput($obj['custname']); // 客户名称
  $suffixno = checkInput($obj['suffixno']); // 缸号
  $artname = checkInput($obj['artname']);	// 工艺名称
  $color = checkInput($obj['color']);	// 颜色
  $cpsl = checkInput($obj['cpsl']);	// 成本缩率
  $jggy = checkInput($obj['jggy']);	// 加工工序
  $stretch = checkInput($obj['stretch']);	// 拉抻
  $veins = checkInput($obj['veins']);	// 斜纹
  $vertical = checkInput($obj['vertical']);	// 经向
  $weft = checkInput($obj['weft']);	// 纬向
  $processtype = checkInput($obj['processtype']);	// 工艺种类
  $processtxt = checkInput($obj['processtxt']);	// 加艺信息
  $processremark = checkInput($obj['processremark']);	// 工艺备注
  $increq = checkInput($obj['increq']);	// 加减码
  $attach = checkInput($obj['attach']);	// 并匹要求 0不并 1并匹
  $remark = checkInput($obj['remark']);	// 备注

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  if (!$artname) {
    exit(JSON(array('data'=>'', 'msg'=>'artname不能为空', 'status'=>'1')));
  }

  if ($bno) {
    $sql = "UPDATE cloth_art SET custno='$custno',custname='$custname',suffixno='$suffixno',artname='$artname',color='$color',cpsl='$cpsl',jggy='$jggy',stretch='$stretch',veins='$veins',vertical='$vertical',weft='$weft',processtype='$processtype',processtxt='$processtxt',processremark='$processremark',increq='$increq',`attach`='$attach',remark='$remark' WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO cloth_art SET billno='$_billno',`admin`='$admin',custno='$custno',custname='$custname',suffixno='$suffixno',artname='$artname',color='$color',cpsl='$cpsl',jggy='$jggy',stretch='$stretch',veins='$veins',vertical='$vertical',weft='$weft',processtype='$processtype',processtxt='$processtxt',processremark='$processremark',increq='$increq',`attach`='$attach',remark='$remark'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 工作人员 查询
if ($a == 'geworker') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $name = checkInput($_GET['name']); // 姓名
  $flag = checkInput($_GET['flag']); // 0 收胚员 1 司机 2 打卷员 3 仓管员 4 送货员 100 取得全部用户
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $role = array('0'=>'收胚员','1'=>'司机','2'=>'打卷员','3'=>'仓管员','4'=>'送货员');
  $targetRole = $role[$flag];

  $query = "SELECT sql_calc_found_rows * FROM `cloth_worker` WHERE `admin`='$admin'";
  if ($targetRole) {
    $query .= " AND wtype='$targetRole'";
  }
  if ($name) {
    $query .= " AND worker LIKE '%$name%'";
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

// 工作人员 新增、编辑
if ($a == 'setworker') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno = checkInput($obj['bno']);
  $admin = checkInput($obj['admin']);
  $worker = checkInput($obj['worker']);	// 名字
  $wtype = checkInput($obj['wtype']);	// 类型
  $phone = checkInput($obj['phone']);	// 电话

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  if (!$worker) {
    exit(JSON(array('data'=>'', 'msg'=>'worker不能为空', 'status'=>'1')));
  }

  if ($bno) {
    $sql = "UPDATE cloth_worker SET worker='$worker',wtype='$wtype',phone='$phone' WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO cloth_worker SET billno='$_billno',`admin`='$admin',worker='$worker',wtype='$wtype',phone='$phone'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 工作人员 删除
if ($a == 'delworker') {
  $bno = checkInput($_GET['bno']);

  if (!$bno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_worker` WHERE billno IN ($bno)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 仓库设置 查询
if ($a == 'getdepot') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $depotcode = checkInput($_GET['depotcode']); // 仓库编号
  $depotname = checkInput($_GET['depotname']); // 仓库名称
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_depot` WHERE `admin`='$admin'";
  if ($depotcode) {
    $query .= " AND depotcode LIKE '%$depotcode%'";
  }
  if ($depotname) {
    $query .= " AND depotname LIKE '%$depotname%'";
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

// 仓库设置 新增、编辑
if ($a == 'setdepot') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno = checkInput($obj['bno']);
  $admin = checkInput($obj['admin']);
  $usercode = checkInput($obj['usercode']);
  $depotcode = checkInput($obj['depotcode']);	// 仓库编号
  $depotname = checkInput($obj['depotname']);	// 仓库名称
  $linkman = checkInput($obj['linkman']);	// 联系人
  $linkphone = checkInput($obj['linkphone']);	// 联系电话
  $address = checkInput($obj['address']);	// 地址

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$depotcode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$depotname) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  if ($bno) {
    $sql = "UPDATE cloth_depot SET usercode='$usercode',depotcode='$depotcode',depotname='$depotname',linkman='$linkman',linkphone='$linkphone',`address`='$address' WHERE id='$bno'";
  } else {
    $sql = "INSERT INTO cloth_depot SET billdate=NOW(),`admin`='$admin',usercode='$usercode',depotcode='$depotcode',depotname='$depotname',linkman='$linkman',linkphone='$linkphone',`address`='$address'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 仓库设置 删除
if ($a == 'deldepot') {
  $bno = checkInput($_GET['bno']);

  if (!$bno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_depot` WHERE id IN ($bno)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}



// 车型车牌 查询
if ($a == 'getcarplate') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_plate` WHERE `admin`='$admin' AND reveplate LIKE '%$keyword%' ORDER BY id DESC";
  $result = $mysqli->query($query) or die($mysqli->error);

  $output['total'] = $result->num_rows;
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 车型车牌 新增、编辑
if ($a == 'setcarplate') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno = checkInput($obj['id']);
  $uid = checkInput($obj['uid']);
  $admin = checkInput($obj['admin']);
  $revecar = checkInput($obj['revecar']); // 车型
  $reveplate = checkInput($obj['reveplate']);	// 车牌号
  $cartype = checkInput($obj['cartype']);	// 类型
  $phone = checkInput($obj['phone']);	// 随车电话

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'uid不能为空', 'status'=>'1')));
  }
  if (!$reveplate) {
    exit(JSON(array('data'=>'', 'msg'=>'车牌号不能为空', 'status'=>'1')));
  }

  if ($bno) {
    $sql = "UPDATE cloth_plate SET revecar='$revecar',reveplate='$reveplate',cartype='$cartype',phone='$phone' WHERE id='$bno'";
  } else {
    $sql = "INSERT INTO cloth_plate SET `admin`='$admin',revecar='$revecar',reveplate='$reveplate',cartype='$cartype',phone='$phone'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 车型车牌 删除
if ($a == 'delcarplate') {
  $id = checkInput($_GET['id']);

  if (!$id) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_plate` WHERE id='$id'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 新增、编辑生产原料记录
if ($a == 'setcrude') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $billno = checkInput($obj['billno']); // 修改用到
  $admin = checkInput($obj['admin']);
  $custno = checkInput($obj['custno']); // 客户编号
  $custname = checkInput($obj['custname']); // 客户名称
  $billdate = checkInput($obj['billdate']); // 制单日期
  $crudeno = checkInput($obj['crudeno']); // 原料单号
  $crudename = checkInput($obj['crudename']); // 原料名称
  $suffixno = checkInput($obj['suffixno']);	// 缸号
  $qty = checkInput($obj['qty']);	// 来胚数量
  $extent = checkInput($obj['extent']);	// 来胚总长度
  $unit = checkInput($obj['unit']);	// 单位
  $class = checkInput($obj['class']);	// 胚布类别
  $factory = checkInput($obj['factory']);	// 加工工厂
  $factoryno = checkInput($obj['factoryno']);	// 工厂编号
  $reveuser = checkInput($obj['reveuser']);	// 收胚人名称
  $revedate = checkInput($obj['revedate']); // 收胚日期
  $revecar = checkInput($obj['revecar']);	// 收布车型
  $reveplate = checkInput($obj['reveplate']);	// 收布车牌
  $driver = checkInput($obj['driver']);	// 收胚司机
  $usercode = checkInput($obj['usercode']);	// 录入人编号(制单人)
  $username = checkInput($obj['username']);	// 录入人名称
  $model = checkInput($obj['artname']); // 规格(对应规格工艺的品名)
  $remark = checkInput($obj['remark']); // 备注
  // 选择的工艺信息
  $cpsl = checkInput($obj['cpsl']);	// 成品缩率
  $cpmf = checkInput($obj['cpmf']);	// 成品门幅
  $stretch = checkInput($obj['stretch']);	// 拉伸
  $veins = checkInput($obj['veins']); // 斜纹
  $processtxt = checkInput($obj['processtxt']);	// 加工要求
  $increq = checkInput($obj['increq']); // 加减码
  $vision = checkInput($obj['vision']);	// 分色
  $attach = checkInput($obj['attach']);	// 允行并匹	0,1
  $flat = $obj['flat'];	// 添加 0，编辑 1

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin is not none', 'status'=>'1')));
  }
  if (!$custno) {
    exit(JSON(array('data'=>'', 'msg'=>'custno is not none', 'status'=>'1')));
  }
  if (!$billdate) {
    exit(JSON(array('data'=>'', 'msg'=>'billdate is not none', 'status'=>'1')));
  }

  if ($flat == '0') {
    $sql = "INSERT INTO cloth_crude SET billno='$_billno',`admin`='$admin',custno='$custno',custname='$custname',billdate='$billdate',crudeno='$crudeno',crudename='$crudename',suffixno='$suffixno',qty='$qty',extent='$extent',unit='$unit',class='$class',factory='$factory',factoryno='$factoryno',reveuser='$reveuser',revedate='$revedate',revecar='$revecar',reveplate='$reveplate',driver='$driver',usercode='$usercode',username='$username',model='$model',remark='$remark',cpsl='$cpsl',cpmf='$cpmf',stretch='$stretch',veins='$veins',processtxt='$processtxt',increq='$increq',vision='$vision',`attach`='$attach'";
  } else if ($flat == '1') {
    $sql = "UPDATE cloth_crude SET custno='$custno',custname='$custname',billdate='$billdate',crudeno='$crudeno',crudename='$crudename',suffixno='$suffixno',qty='$qty',extent='$extent',unit='$unit',class='$class',factory='$factory',factoryno='$factoryno',reveuser='$reveuser',revedate='$revedate',revecar='$revecar',reveplate='$reveplate',driver='$driver',usercode='$usercode',username='$username',model='$model',remark='$remark',cpsl='$cpsl',cpmf='$cpmf',stretch='$stretch',veins='$veins',processtxt='$processtxt',increq='$increq',vision='$vision',`attach`='$attach' WHERE billno='$billno'";
  }
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'保存失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'保存成功', 'status'=>'0')));
}

// 删除生产原料记录
if ($a == 'delcrude') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_crude` WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}




////////////////////////////////////////////////////////////////////////////////////////////////////

//设置进胚记录是否审核
if ($a == 'checkcrude') {
  $admin = checkInput($_GET['admin']);
  $billno = checkInput($_GET['billno']);
  $audit = checkInput($_GET['audit']); 

  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if ($audit != '0' && $audit != '1') {
    exit(JSON(array('data'=>'', 'msg'=>'audit params error', 'status'=>'1')));
  }

  $sql = "update cloth_crude set audit=$audit where admin='$admin' and id IN ($billno)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
}



////////////////////////////////////////////////////////////



//已检布匹列表
if ($a == 'getcrudechecklist') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $custname = checkInput($_GET['custname']); // 客户名称
  
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$begindate){
	exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }	
  $query = "select sql_calc_found_rows * from cloth_crude WHERE `admin`='$admin' and ischeck=1 and billdate between '$begindate' and '$enddate' ";
  if ($custname){
	  $query .= " and custname='$custname'";
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



//未检布匹列表
if ($a == 'getcrudenochecklist') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $custname = checkInput($_GET['custname']); // 客户名称
  
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$begindate){
	exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "select sql_calc_found_rows * from cloth_crude WHERE `admin`='$admin' and ischeck=0 and billdate between '$begindate' and '$enddate' ";
  if ($custname){
	  $query .= " and custname='$custname'";
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

// 码单查询
if ($a == 'gettracklist') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $serialno = checkInput($_GET['serialno']); // 单号
  $custno = checkInput($_GET['custno']); // 客户编号
  $usercode = checkInput($_GET['usercode']); // 操作编号
  $checkno = checkInput($_GET['checkno']); // 打卷员编号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows *, DATE_FORMAT(billdate, '%Y-%m-%d') AS billdate FROM cloth_track  WHERE `admin`='$admin'";
  if ($begindate) {
    $query .= " AND billdate between '$begindate' AND '$enddate'";
  }
  if ($serialno) {
	  $query .= " AND serialno='$serialno'";
  }
  if ($custno) {
	  $query .= " AND custno='$custno'";
  }
  if ($usercode) {
	  $query .= " AND usercode='$usercode'";
  }
  if ($checkno) {
	  $query .= " AND checkno='$checkno'";
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

// 码单明细 新增、编辑
if ($a == 'settrack') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $usercode = checkInput($obj['usercode']); // 录入人编号
  $username = checkInput($obj['username']); // 录入人名称
  $machineno = checkInput($obj['machineno']); // 机台号
  $increq = checkInput($obj['increq']); // 加减码
  $qty = checkInput($obj['qty']); // 标签长度
  $worker = checkInput($obj['worker']); // 验布员
  $workerno = checkInput($obj['workerno']); // 验布编号
  $remark = checkInput($obj['remark']);	// 备注
  // 码单信息
  $crudeItem = $obj['crude_item'];
  $serialno = checkInput($crudeItem['crudeno']); // 单号
  $custno = checkInput($crudeItem['custno']); // 客户编号
  $custname = checkInput($crudeItem['custname']); // 客户名称
  $suffixno = checkInput($crudeItem['suffixno']); // 缸号
  $indexno = checkInput($crudeItem['indexno']); // 新匹号
  $model = checkInput($crudeItem['model']); // 规格
  $color = checkInput($crudeItem['color']); // 颜色
  $unit = checkInput($crudeItem['unit']); // 单位
  $price = checkInput($crudeItem['price']); // 价格
  $extent = checkInput($crudeItem['extent']); // 原长度
  $bigness = checkInput($crudeItem['bigness']); // 实际长度
  $attach = checkInput($crudeItem['attach']); // 并匹
  $attachtxt = checkInput($crudeItem['attachtxt']); // 并匹说明
  $processtxt = checkInput($crudeItem['processtxt']); // 加工工序
  $processtype = checkInput($crudeItem['processtype']); // 加工类型
  $processremark = checkInput($crudeItem['processremark']); // 加工说明
  $jggy = checkInput($crudeItem['jggy']); // 加工工艺
  $factory = checkInput($crudeItem['factory']);
  $factoryno = checkInput($crudeItem['factoryno']);
  $auditno = checkInput($crudeItem['auditno']);
  $auditname = checkInput($crudeItem['auditname']);


  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$serialno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "INSERT INTO cloth_track SET billno='$_billno',billdate=NOW(),`admin`='$admin',usercode='$usercode',username='$username',machineno='$machineno',increq='$increq',qty='$qty',worker='$worker',workerno='$workerno',remark='$remark',serialno='$serialno',custno='$custno',custname='$custname',suffixno='$suffixno',indexno='$indexno',model='$model',color='$color',unit='$unit',price='$price',extent='$extent',bigness='$bigness',`attach`='$attach',attachtxt='$attachtxt',processtxt='$processtxt',processtype='$processtype',processremark='$processremark',jggy='$jggy',factory='$factory',factoryno='$factoryno',auditno='$auditno',auditname='$auditname'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'新增失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'新增成功', 'status'=>'0')));
}

// 删除码单明细
if ($a == 'deltrack') {
  $bno = checkInput($_GET['bno']);

  if (!$bno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_track` WHERE billno IN ($bno)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 查询转仓单（单头）
if ($a == 'getwaretrackhead') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $bno = checkInput($_GET['bno']); // 单号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$begindate) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_waretrackhead` WHERE `admin`='$admin' AND billdate between '$begindate' AND '$enddate'";
  if ($bno) {
    $query .=" AND billno='$bno'";
  }
  $query .= "ORDER BY id DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 查询转仓单（单体）
if ($a == 'getwaretrackbody') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $custname = checkInput($_GET['custname']); // 客户名称
  $orderhead_billno = checkInput($_GET['orderhead_billno']); // 订单头编号
  $serialno = checkInput($_GET['serialno']); // 码单单号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$orderhead_billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "select sql_calc_found_rows * from cloth_waretrack WHERE waretrackno='$orderhead_billno'";
  if ($custname) {
	  $query .= " and custname='$custname'";
  }
  if ($serialno) {
	  $query .= " and serialno='$serialno'";
  }
  $query .= " ORDER BY id DESC";
  
  $result = $mysqli->query($query) or die($mysqli->error);

  $output['total'] = $result->num_rows;
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 设置转仓单
if ($a == 'setwaretrackorder') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  // 订单头
  $orderHead_billno = $_billno; // 订单头编号
  $flat = checkInput($obj['head']['flat']); // 添加 0，编辑 1
  $admin = checkInput($obj['head']['admin']);
  $usercode = checkInput($obj['head']['usercode']); // 录入人编号
  $username = checkInput($obj['head']['username']); // 录入人名称
  $billdate = checkInput($obj['head']['billdate']); // 制单日期
  $qty = checkInput($obj['head']['qty']); // 转仓数量（匹数）   默认 1  系统自动触发更新
  $bigness = checkInput($obj['head']['bigness']); // 转仓长度    默认 0  系统自动触发更新
  $operator = checkInput($obj['head']['operator']); // 仓管员
  $depotcode = checkInput($obj['head']['depotcode']); // 出货仓库编号
  $depotname = checkInput($obj['head']['depotname']); // 出货仓库名称
  $workercode = checkInput($obj['head']['workercode']); // 送货人编号
  $workername = checkInput($obj['head']['workername']); // 送货人名称
  $carplate = checkInput($obj['head']['carplate']); // 车牌
  $movedate = checkInput($obj['head']['movedate']); // 转仓日期
  $remark = checkInput($obj['head']['remark']); // 备注

  if (!$admin)  {
    exit(JSON(array('data'=>'', 'msg'=>'admin is not none', 'status'=>'1')));
  }
  if (!$billdate) {
    exit(JSON(array('data'=>'', 'msg'=>'billdate is not none', 'status'=>'1')));
  }
  if (!$depotname) {
    exit(JSON(array('data'=>'', 'msg'=>'depotname is not none', 'status'=>'1')));
  }

  // 订单体
  $sqlarr = array();
  foreach ($obj['body'] as $k => $v) {
    $trackno = checkInput($v['billno']);
    $serialno = checkInput($v['serialno']);
    $suffixno = checkInput($v['suffixno']);
    $batchno = checkInput($v['batchno']);
    $indexno = checkInput($v['indexno']);
    $body_qty = checkInput($v['qty']);
    $unit = checkInput($v['unit']);
    $model = checkInput($v['model']);
    $color = checkInput($v['color']);
    $jggy = checkInput($v['jggy']);
    $cpmf = checkInput($v['cpmf']);
    $cpsl = checkInput($v['cpsl']);
    $vertical = checkInput($v['vertical']);
    $weft = checkInput($v['weft']);
    $body_bigness = checkInput($v['bigness']);
    $increq = checkInput($v['increq']);
    $body_remark = checkInput($v['remark']);
    $orderBody_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 订单体编号
    array_push($sqlarr, "('$orderBody_billno','$orderHead_billno','$trackno','$serialno','$suffixno','$batchno','$indexno','$body_qty','$unit','$model','$color','$jggy','$cpmf','$cpsl','$vertical','$weft','$body_bigness','$increq','$body_remark')");
  }

  // 是否存在表体内容
  if (count($sqlarr) === 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单体不能为空', 'status'=>'1')));
  }

  // 表头处理
  $sql = "INSERT INTO cloth_waretrackhead SET billno='$orderHead_billno',`admin`='$admin',usercode='$usercode',username='$username',billdate='$billdate',qty='$qty',bigness='$bigness',operator='$operator',depotcode='$depotcode',depotname='$depotname',workercode='$workercode',workername='$workername',carplate='$carplate',movedate='$movedate',remark='$remark'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'head error', 'status'=>'1')));
  }

  // 表体处理
  $sql = "INSERT INTO cloth_waretrack(billno,waretrackno,trackno,serialno,suffixno,batchno,indexno,qty,unit,model,color,jggy,cpmf,cpsl,vertical,weft,bigness,increq,remark) VALUES".implode(',', $sqlarr);
  if (!$mysqli->query($sql)) {
    // 订单体处理失败要删除订单头
    $sql = "DELETE FROM `cloth_waretrackhead` WHERE billno='$orderHead_billno'";
    $mysqli->query($sql);
    exit(JSON(array('data'=>'', 'msg'=>'body error', 'status'=>'1')));
  }

  exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
}

// 删除转仓记录头
if ($a == 'delwaretrackhead') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_waretrackhead` WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 删除转仓记录体
if ($a == 'delwaretrackbody') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_waretrack` WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 查询送货出库（单头信息）
if ($a == 'getsalorderhead') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $begindate = checkInput($_GET['begindate']); // 开始日期
  $enddate = checkInput($_GET['enddate']); // 结束日期
  $bno = checkInput($_GET['bno']); // 单号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$begindate) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `cloth_salorderhead` WHERE `admin`='$admin' AND billdate between '$begindate' AND '$enddate'";
  if ($bno) {
    $query .=" AND billno='$bno'";
  }
  $query .= "ORDER BY id DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 查询送货出库（单体信息）
if ($a == 'getsalorderbody') {
  $admin = checkInput($_GET['admin']); // 管理员编号
  $custname = checkInput($_GET['custname']); // 客户名称
  $orderhead_billno = checkInput($_GET['orderhead_billno']); // 订单头编号
  $serialno = checkInput($_GET['serialno']); // 码单单号
  $output = array('list'=>array(), 'total'=>0);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  if (!$orderhead_billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "select sql_calc_found_rows * from cloth_salorderbody WHERE billno='$orderhead_billno'";
  if ($custname) {
	  $query .= " and custname='$custname'";
  }
  if ($serialno) {
	  $query .= " and serialno='$serialno'";
  }
  $query .= " ORDER BY id DESC";
  
  $result = $mysqli->query($query) or die($mysqli->error);

  $output['total'] = $result->num_rows;
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 设置送货出库单
if ($a == 'setsalorder') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  // 订单头
  $orderHead_billno = $_billno; // 订单头编号
  $flat = checkInput($obj['head']['flat']); // 添加 0，编辑 1
  $admin = checkInput($obj['head']['admin']);
  $usercode = checkInput($obj['head']['usercode']); // 录入人编号
  $username = checkInput($obj['head']['username']); // 录入人名称
  $custno = checkInput($obj['head']['custno']); // 客户编号
  $custname = checkInput($obj['head']['custname']); // 客户名称
  $billdate = checkInput($obj['head']['billdate']); // 制单日期
  $billstatus = checkInput($obj['head']['billstatus']); // 账单方式
  $pay = checkInput($obj['head']['pay']); // 应收款
  $apay = checkInput($obj['head']['apay']); // 已收款
  $operator = checkInput($obj['head']['operator']); // 出库员
  $destination = checkInput($obj['head']['destination']); // 送货地址
  $depotcode = checkInput($obj['head']['depotcode']); // 出货仓库编号
  $depotname = checkInput($obj['head']['depotname']); // 出货仓库名称
  $driver = checkInput($obj['head']['driver']); // 送货人
  $driverphone = checkInput($obj['head']['driverphone']); // 送货人手机号
  $carplate = checkInput($obj['head']['carplate']); // 送货车牌
  $carphone = checkInput($obj['head']['carphone']); // 随车电话
  $carmodel = checkInput($obj['head']['carmodel']); // 车型
  $cartype = checkInput($obj['head']['cartype']); // 车辆使用类型
  $dealdate = checkInput($obj['head']['dealdate']); // 交货日期
  $period = checkInput($obj['head']['period']); // 账期
  $invtype = checkInput($obj['head']['invtype']); // 发票类型
  $invoice = checkInput($obj['head']['invoice']); // 发票编号
  $remark = checkInput($obj['head']['remark']); // 备注

  if (!$admin)  {
    exit(JSON(array('data'=>'', 'msg'=>'admin is not none', 'status'=>'1')));
  }
  if (!$custname) {
    exit(JSON(array('data'=>'', 'msg'=>'custname is not none', 'status'=>'1')));
  }
  if (!$billdate) {
    exit(JSON(array('data'=>'', 'msg'=>'billdate is not none', 'status'=>'1')));
  }
  if (!$dealdate) {
    exit(JSON(array('data'=>'', 'msg'=>'dealdate is not none', 'status'=>'1')));
  }

  // 订单体
  $sqlarr = array();
  foreach ($obj['body'] as $k => $v) {
    $tradeno = checkInput($v['billno']);
    $serialno = checkInput($v['serialno']);
    $suffixno = checkInput($v['suffixno']);
    $batchno = checkInput($v['batchno']);
    $indexno = checkInput($v['indexno']);
    $qty = checkInput($v['qty']);
    $unit = checkInput($v['unit']);
    $model = checkInput($v['model']);
    $color = checkInput($v['color']);
    $jggy = checkInput($v['jggy']);
    $cpmf = checkInput($v['cpmf']);
    $cpsl = checkInput($v['cpsl']);
    $vertical = checkInput($v['vertical']);
    $weft = checkInput($v['weft']);
    $bigness = checkInput($v['bigness']);
    $increq = checkInput($v['increq']);
    $body_remark = checkInput($v['remark']);
    array_push($sqlarr, "('$orderHead_billno','$tradeno','$serialno','$suffixno','$batchno','$indexno','$qty','$unit','$model','$color','$jggy','$cpmf','$cpsl','$vertical','$weft','$bigness','$increq','$body_remark')");
  }

  // 是否存在表体内容
  if (count($sqlarr) === 0) {
    exit(JSON(array('data'=>'', 'msg'=>'订单体不能为空', 'status'=>'1')));
  }

  // 表头处理
  $sql = "INSERT INTO cloth_salorderhead SET billno='$orderHead_billno',`admin`='$admin',usercode='$usercode',username='$username',custno='$custno',custname='$custname',billdate='$billdate',billstatus='$billstatus',pay='$pay',apay='$apay',operator='$operator',destination='$destination',depotcode='$depotcode',depotname='$depotname',driver='$driver',driverphone='$driverphone',carplate='$carplate',carphone='$carphone',carmodel='$carmodel',cartype='$cartype',dealdate='$dealdate',`period`='$period',invtype='$invtype',invoice='$invoice',remark='$remark'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'head error', 'status'=>'1')));
  }

  // 表体处理
  $sql = "INSERT INTO cloth_salorderbody(billno,tradeno,serialno,suffixno,batchno,indexno,qty,unit,model,color,jggy,cpmf,cpsl,vertical,weft,bigness,increq,remark) VALUES".implode(',', $sqlarr);
  if (!$mysqli->query($sql)) {
    // 订单体处理失败要删除订单头
    $sql = "DELETE FROM `cloth_salorderhead` WHERE billno='$orderHead_billno'";
    $mysqli->query($sql);
    exit(JSON(array('data'=>'', 'msg'=>'body error', 'status'=>'1')));
  }

  exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
}

// 删除送货出库记录详细项
if ($a == 'delsalorderhead') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_salorderhead` WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}

// 删除送货出库记录详细项
if ($a == 'delsalorderbody') {
  $items = checkInput($_GET['items']);

  if (!$items) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $sql = "DELETE FROM `cloth_salorderbody` WHERE id IN ($items)";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'删除失败', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
}
