<?php
// 登录注册等

if ($a == 'login') {
  $uid = checkInput($_GET['uid']);
  $upass = checkInput($_GET['upass']);

  // 用户角色
  $admincode = '';
  $admin = ''; // 最上级管理员(固定)(暂时没用到功能)
  $division = ''; // 上级 (管理员的上级是自己,组长上级是管理员,员工上级是对应组长)
  $isteam = ''; // 职位  -3普通队员, -2副经理（秘书助手）、 -1 经理、0代理、1队员（销售类)、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它、9批发商
  $nature = 0; // 企业属性
  $pubkey = '';
  $ower1 = ''; // 代理商
  $ower2 = ''; // 批发商

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'账号不能为空', 'status'=>'1')));
  }
  if (!$upass) {
    exit(JSON(array('data'=>'', 'msg'=>'密码不能为空', 'status'=>'1')));
  }

  $mrepass = setstrmd5(md5($upass)); // md5加密

  // 取得公共传输的公密钥
  // 暂时关掉
	// $query = "SELECT * FROM team_login WHERE userno='$uid'";
  // $result = $mysqli->query($query) or die($mysqli->error);
  // $data = $result->fetch_assoc();
	// if ($data) {
	// 	$pubkey = $data['pubkey'];
	// 	$sql = "UPDATE team_login SET logintime=NOW() WHERE userno='$uid'";
	// 	if (!$mysqli->query($sql)) {
	// 		exit(JSON(array('data'=>'', 'msg'=>'登录处理错误1', 'status'=>'1')));
	// 	}
	// } else {
	// 	$pubkey = getRandChar(64);
	// 	$sql = "INSERT DELAYED team_login SET userno='$uid',pubkey='$pubkey',logintime=NOW(),serialno='$macid',ver='$v'";
	// 	if (!$mysqli->query($sql)) {
	// 		exit(JSON(array('data'=>'', 'msg'=>'登录处理错误2', 'status'=>'1')));
	// 	}
  // }

  $query = "SELECT *, if(TIMESTAMPDIFF(MINUTE,lastdate,NOW())<=30,true,false) AS deadline FROM `team_salesman` WHERE
              userno='$uid' AND (`password`='$mrepass' OR repassword='$mrepass')";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();

  // 判断是否登录成功
  if ($data) {
    if ($data['password'] == $mrepass) { // 使用正常密码

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

  if ($data['team'] > 0 ) { // 管理员
    // 查询对应的 批发商，代理商
    $query = "SELECT admin,isteam FROM `teams` WHERE userno='$uid' AND (isteam='0' OR isteam='9') AND astatus='1'";
    $result = $mysqli->query($query) or die($mysqli->error);
    while ($row = $result->fetch_assoc()) {
      if ($row['isteam'] == '0') { // 代理商
        $ower1 = $row['admin'];
      }
      if ($row['isteam'] == '9') { // 批发商
        $ower2 = $row['admin'];
      }
    }
    $admincode = $data['billno'];
    $admin = $data['userno'];
    $division = $data['userno'];
    $isteam = '-1';              // -1 经理
    $nature = $data['nature'];
  } else {
    // 组长
    $query = "SELECT `admin`,isteam,admincode FROM `teams` WHERE userno='$uid' AND (division=`admin`) AND division!='' AND astatus='1' AND isteam IN (-2,1,2,3,4,5,6,7,8,10,11)";
    $result = $mysqli->query($query) or die($mysqli->error);
    $roleData1 = $result->fetch_assoc();
    if ($roleData1) {
      $admincode = $roleData1['admincode']; // 管理员的billno
      $admin = $roleData1['admin'];
      $division = $roleData1['admin'];
      $isteam = $roleData1['isteam']; // -2副经理（秘书助手）1销售类、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它

       // 查询对应的 批发商，代理商
      $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
        if ($row['isteam'] == '0') { // 代理商
          $ower1 = $row['admin'];
        }
        if ($row['isteam'] == '9') { // 批发商
          $ower2 = $row['admin'];
        }
      }
    } else {
      // 员工
      $query = "SELECT division,isteam,`admin`,admincode FROM `teams` WHERE userno='$uid' AND (division!=`admin`) AND astatus='1' AND isteam='-3'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData2 = $result->fetch_assoc();
      if ($roleData2) {
        $admincode = $roleData2['admincode'];
        $admin = $roleData2['admin'];
        $division = $roleData2['division'];
        $isteam = $roleData2['isteam']; // -3

        // 查询对应的 批发商，代理商
        $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
        $result = $mysqli->query($query) or die($mysqli->error);
        while ($row = $result->fetch_assoc()) {
          if ($row['isteam'] == '0') { // 代理商
            $ower1 = $row['admin'];
          }
          if ($row['isteam'] == '9') { // 批发商
            $ower2 = $row['admin'];
          }
        }
      } else {
        // 这是未加入团队的人
        // exit(JSON(array('data'=>'', 'msg'=>'错误，无法确定用户身份', 'status'=>'1')));
      }
    }

    // 重新从管理员用户中取得企业属性
    if ($admin <> $uid) {
      $query = "SELECT nature FROM team_salesman WHERE userno='$admin'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData3 = $result->fetch_assoc();
      if ($roleData3) {
        $nature = $roleData3['nature'];
      }
    }
  }

  // TODO：已不适用
  // 密码强度
  $pwLength = strlen($data['password']);
  $pwStrength;
  if ($pwLength == 6) {
    $pwStrength = 'medium';
  } else if ($pwLength >= 8) {
    $pwStrength = 'strong';
  } else {
    $pwStrength = 'weak';
  }


  // 限定返回的用户信息
  $outdata=array('billno'=>$data['billno'],'username'=>$data['username'],'userno'=>$data['userno'],'address'=>$data['address'],'phone'=>$data['phone'],'tel'=>$data['tel'],
          'team'=>$data['team'],'teamname'=>$data['teamname'],'teamdate'=>$data['teamdate'],'image'=>$data['image'],
          'image1'=>$data['image1'],'image2'=>$data['image2'],'memo'=>$data['memo'],'admincode'=>$admincode,
          'avatar'=>$data['image'],'pw_strength'=>$pwStrength,'ower1'=>$ower1,'ower2'=>$ower2,
          'nature'=>$nature,'isteam'=>$isteam,'admin'=>$admin,'division'=>$division,'pubkey'=>$pubkey,'grouplist'=>array());


  //返回用户可切换的团队列表
  $grouplist=array();
  // $query = "SELECT userno,billno,teamname,company FROM `team_salesman` WHERE userno IN (SELECT admin FROM `view_groupuserlist`  WHERE userno='$uid') ORDER BY id DESC";
  $query = "SELECT a.userno,a.username,a.billno,a.teamname,a.company,(b.userno = b.admin) AS issame FROM `team_salesman` a left join `view_groupuserlist` b  
           on a.userno = b.admin WHERE b.userno='$uid' and b.astatus='1' ORDER BY issame DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($grouplist, $row);
  }
  array_push($outdata['grouplist'], $grouplist);

  exit(JSON(array('data'=>$outdata, 'msg'=>'登录成功', 'status'=>'0')));
}

 // 微信登录
 if ($a == 'wxlogin') {
  $openid = checkInput($_GET['openid']);
  $wkey = checkInput($_GET['wkey']);
  // 用户角色
  $admincode = '';
  $admin = ''; // 最上级管理员(固定)(暂时没用到功能)
  $division = ''; // 上级 (管理员的上级是自己,组长上级是管理员,员工上级是对应组长)
  $isteam = ''; // -1 管理员，（-2 秘书，0 销售组，1 员工，2 财务，3 仓库，4 送货）
  $nature = 0; // 企业属性
  $pubkey = '';
  $ower1 = ''; // 代理商
  $ower2 = ''; // 批发商

  if (!$openid) {
    exit(JSON(array('data'=>'', 'msg'=>'openid不能为空', 'status'=>'1')));
  }
  // 判断是否有该微信的openid
  if($wkey == 'wx'){
    $query = "SELECT * FROM `team_salesman` WHERE wxthreeload='$openid' LIMIT 1";
  } else {
    $query = "SELECT * FROM `team_salesman` WHERE qqthreeload='$openid' LIMIT 1";
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();

  if(!$data){
	  exit(JSON(array('data'=>'', 'msg'=>'该用户没有注册', 'status'=>'-1')));
  }
  $uid = $data['userno'];
  if ($data['team'] > 0 ) {
     // 查询对应的 批发商，代理商
    $query = "SELECT admin,isteam FROM `teams` WHERE userno='$uid' AND (isteam='0' OR isteam='9') AND astatus='1'";
    $result = $mysqli->query($query) or die($mysqli->error);
    while ($row = $result->fetch_assoc()) {
      if ($row['isteam'] == '0') { // 代理商
        $ower1 = $row['admin'];
      }
      if ($row['isteam'] == '9') { // 批发商
        $ower2 = $row['admin'];
      }
    }
    // 管理员
    $admincode = $data['admincode'];
    $admin = $data['userno'];
    $division = $data['userno'];
    $isteam = '-1';
    $nature = $data['nature'];
  } else {
    // 组长
    $query = "SELECT `admin`,isteam,admincode FROM `teams` WHERE userno='$uid' AND (division=`admin`) AND division!='' AND astatus='1' AND isteam IN (-2,1,2,3,4,5,6,7,8,10,11)";
    $result = $mysqli->query($query) or die($mysqli->error);
    $roleData1 = $result->fetch_assoc();
    if ($roleData1) {
      $admincode = $roleData1['admincode'];
      $admin = $roleData1['admin'];
      $division = $roleData1['admin'];
      $isteam = $roleData1['isteam']; // 0,2,3,4

      // 查询对应的 批发商，代理商
      $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
        if ($row['isteam'] == '0') { // 代理商
          $ower1 = $row['admin'];
        }
        if ($row['isteam'] == '9') { // 批发商
          $ower2 = $row['admin'];
        }
      }
    } else {
      // 员工
      $query = "SELECT division,isteam,`admin`,admincode FROM `teams` WHERE userno='$uid' AND (division!=`admin`) AND astatus='1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData2 = $result->fetch_assoc();
      if ($roleData2) {
        $admincode = $roleData2['admincode'];
        $admin = $roleData2['admin'];
        $division = $roleData2['division'];
        $isteam = '1';

         // 查询对应的 批发商，代理商
        $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
        $result = $mysqli->query($query) or die($mysqli->error);
        while ($row = $result->fetch_assoc()) {
          if ($row['isteam'] == '0') { // 代理商
            $ower1 = $row['admin'];
          }
          if ($row['isteam'] == '9') { // 批发商
            $ower2 = $row['admin'];
          }
        }
      } 
    }

    // 重新从管理员用户中取得企业属性
    if ($admin <> $uid) {
      $query = "SELECT nature FROM team_salesman WHERE userno='$admin'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData3 = $result->fetch_assoc();
      if ($roleData3) {
        $nature = $roleData3['nature'];
      }
    }
  }

  // TODO：已不适用
  // 密码强度
  $pwLength = strlen($data['password']);
  $pwStrength;
  if ($pwLength == 6) {
    $pwStrength = 'medium';
  } else if ($pwLength >= 8) {
    $pwStrength = 'strong';
  } else {
    $pwStrength = 'weak';
  }

  // 限定返回的用户信息
  $outdata=array('billno'=>$data['billno'],'username'=>$data['username'],'userno'=>$data['userno'],'address'=>$data['address'],'phone'=>$data['phone'],'tel'=>$data['tel'],
          'team'=>$data['team'],'teamname'=>$data['teamname'],'teamdate'=>$data['teamdate'],'image'=>$data['image'],
          'image1'=>$data['image1'],'image2'=>$data['image2'],'memo'=>$data['memo'],'admincode'=>$admincode,
          'avatar'=>$data['image'],'pw_strength'=>$pwStrength,'ower1'=>$ower1,'ower2'=>$ower2,
          'nature'=>$nature,'isteam'=>$isteam,'admin'=>$admin,'division'=>$division,'pubkey'=>$pubkey,'grouplist'=>array());



  //返回用户可切换的团队列表
  $grouplist=array();
  // $query = "SELECT userno,billno,teamname,company FROM `team_salesman` WHERE userno IN (SELECT admin FROM `view_groupuserlist`  WHERE userno='$uid' and astatus='1') ORDER BY id DESC";
  $query = "SELECT a.userno,a.username,a.billno,a.teamname,a.company,(b.userno = b.admin) AS issame FROM `team_salesman` a left join `view_groupuserlist` b  
           on a.userno = b.admin WHERE b.userno='$uid' and b.astatus='1' ORDER BY issame DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($grouplist, $row);
  }
  array_push($outdata['grouplist'], $grouplist);

  exit(JSON(array('data'=>$outdata, 'msg'=>'登录成功', 'status'=>'0')));
}

// 切换 团队  更改登录接口
if ($a=='changelogin'){
  $admin = checkInput($_GET['admin']);
  $uid = checkInput($_GET['uid']);
  
  $admincode = '';
  $division = ''; // 上级 (管理员的上级是自己,组长上级是管理员,员工上级是对应组长)
  $isteam = ''; // 职位  -3普通队员, -2副经理（秘书助手）、 -1 经理、0代理、1队员（销售类)、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它、9批发商
  $nature = 0; // 企业属性
  $pubkey = '';
  $ower1 = ''; // 代理商
  $ower2 = ''; // 批发商

  if (!$uid) {
    exit(JSON(array('data'=>'', 'msg'=>'账号不能为空', 'status'=>'1')));
  }
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'账号不能为空', 'status'=>'1')));
  }  
  
  $query = "SELECT * from team_salesman where userno='$uid'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  
  
  
  if ($admin == $uid ) {
     // 查询对应的 批发商，代理商
      $query = "SELECT admin,isteam FROM `teams` WHERE userno='$uid' AND (isteam='0' OR isteam='9') AND astatus='1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
        if ($row['isteam'] == '0') { // 代理商
          $ower1 = $row['admin'];
        }
        if ($row['isteam'] == '9') { // 批发商
          $ower2 = $row['admin'];
        }
      }
    // 管理员
    $admincode = $data['admincode'];
    $admin = $data['userno'];
    $division = $data['userno'];

    $isteam = '-1';
    $nature = $data['nature'];
  } else { // 组长
    $query = "SELECT `admin`,isteam,admincode FROM `teams` WHERE userno='$uid' AND (division=`admin`) AND division!='' AND astatus='1' AND isteam IN (-2,1,2,3,4,5,6,7,8,10,11)";
    $result = $mysqli->query($query) or die($mysqli->error);
    $roleData1 = $result->fetch_assoc();
    if ($roleData1) {
      $admincode = $roleData1['admincode'];
      $admin = $roleData1['admin'];
      $division = $roleData1['admin'];
      $isteam = $roleData1['isteam']; // -2副经理（秘书助手）1销售类、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它

       // 查询对应的 批发商，代理商
      $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
      $result = $mysqli->query($query) or die($mysqli->error);
      while ($row = $result->fetch_assoc()) {
        if ($row['isteam'] == '0') { // 代理商
          $ower1 = $row['admin'];
        }
        if ($row['isteam'] == '9') { // 批发商
          $ower2 = $row['admin'];
        }
      }
    } else {
      // 员工
      $query = "SELECT division,isteam,`admin`,admincode FROM `teams` WHERE userno='$uid' AND (division!=`admin`) AND astatus='1' AND isteam='-3'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData2 = $result->fetch_assoc();
      if ($roleData2) {
        $admincode = $roleData2['admincode'];
        $admin = $roleData2['admin'];
        $division = $roleData2['division'];
        $isteam = $roleData2['isteam']; // -3

        // 查询对应的 批发商，代理商
        $query = "SELECT admin,isteam FROM `teams` WHERE userno='$admin' AND (isteam='0' OR isteam='9') AND astatus='1'";
        $result = $mysqli->query($query) or die($mysqli->error);
        while ($row = $result->fetch_assoc()) {
          if ($row['isteam'] == '0') { // 代理商
            $ower1 = $row['admin'];
          }
          if ($row['isteam'] == '9') { // 批发商
            $ower2 = $row['admin'];
          }
        }
      } else {
        // 这是未加入团队的人
        // exit(JSON(array('data'=>'', 'msg'=>'错误，无法确定用户身份', 'status'=>'1')));
      }
    }

    // 重新从管理员用户中取得企业属性
    if ($admin <> $uid) {
      $query = "SELECT nature FROM team_salesman WHERE userno='$admin'";
      $result = $mysqli->query($query) or die($mysqli->error);
      $roleData3 = $result->fetch_assoc();
      if ($roleData3) {
        $nature = $roleData3['nature'];
      }
    }
  }

  // 限定返回的用户信息
  $outdata=array('billno'=>$data['billno'],'username'=>$data['username'],'userno'=>$data['userno'],'address'=>$data['address'],'phone'=>$data['phone'],'tel'=>$data['tel'],
          'team'=>$data['team'],'teamname'=>$data['teamname'],'teamdate'=>$data['teamdate'],'image'=>$data['image'],
          'image1'=>$data['image1'],'image2'=>$data['image2'],'memo'=>$data['memo'],'admincode'=>$admincode,
          'avatar'=>$data['image'],'pw_strength'=>$pwStrength,'ower1'=>$ower1,'ower2'=>$ower2,
          'nature'=>$nature,'isteam'=>$isteam,'admin'=>$admin,'division'=>$division,'pubkey'=>$pubkey,'grouplist'=>array());

  //返回用户可切换的团队列表
  $grouplist=array();
  // $query = "SELECT userno,billno,teamname,company FROM `team_salesman` WHERE userno IN (SELECT admin FROM `view_groupuserlist`  WHERE userno='$uid' and astatus='1')";
  $query = "SELECT a.userno,a.username,a.billno,a.teamname,a.company,(b.userno = b.admin) AS issame FROM `team_salesman` a left join `view_groupuserlist` b  
           on a.userno = b.admin WHERE b.userno='$uid' and b.astatus='1' ORDER BY issame DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($grouplist, $row);
  }
  array_push($outdata['grouplist'], $grouplist);

  exit(JSON(array('data'=>$outdata, 'msg'=>'登录成功', 'status'=>'0')));

}




// 退出登录
if ($a == 'logout') {
  $usercode = checkInput($_GET['usercode']);
  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  exit(JSON(array('data'=>'', 'msg'=>'退出成功', 'status'=>'0')));
}

// 获取图片验证码
if ($a == 'get_image_captcha') {
  $verifyImage = new VerifyImage();;
  $verifyImage->createImage();
}

// 检查图片验证码(只用于测试)
if ($a == 'check_image_captcha') {
  $code = checkInput($_GET['code']);
  $verifyImage = new VerifyImage();;
  $bool = $verifyImage->check($code);
  if ($bool) {
    exit(JSON(array('data'=>'', 'msg'=>'正确', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'错误', 'status'=>'0')));
  }
}

// 获取短信验证码
if ($a == 'getsmscode') {
  $phone = checkInput($_GET['phone']);
  $code = checkInput($_GET['code']); // 图片验证码
  $isCheckAccount = @$_GET['ica'] ? checkInput($_GET['ica']) : 2; // 检查账号是否存在 1开启 2关闭
  $flag = checkInput($_GET['flag']);

  $rondnum = mt_rand(1000, 9999);
  if ($flag == '0') {
    $msg = '好业绩：你的验证码是：'.$rondnum.'，有效期10分钟，请及时输入！非本人操作，请忽略该短信！';
  } else {
    $msg = '货真真商城：你的验证码是：'.$rondnum.'，有效期10分钟，请及时输入！非本人操作，请忽略该短信！';
  }

  if (!$phone) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
  }
  if (!preg_match('/^1[34578]\d{9}$/', $phone)) {
    exit(JSON(array('data'=>'', 'msg'=>'请填写正确的手机号码', 'status'=>'1')));
  }
  if (!$code) {
    exit(JSON(array('data'=>'', 'msg'=>'图片验证码不能为空', 'status'=>'1')));
  }
  $verifyImage = new VerifyImage();
  if (!$verifyImage->check($code)) {
    exit(JSON(array('data'=>'', 'msg'=>'图片验证码错误', 'status'=>'1')));
  }

  // 检查账号
  if ($isCheckAccount == '1') {
    $query = "SELECT id FROM team_salesman WHERE userno='$phone' LIMIT 1";
    $result = $mysqli->query($query) or die($mysqli->error);
    $data = $result->fetch_assoc();
    if (!$data) {
      exit(JSON(array('data'=>'', 'msg'=>'账号不存在', 'status'=>'1')));
    }
  }

  // 是否频繁获取短信
  $query = "SELECT billdate FROM team_smscache WHERE tel='$phone' AND billdate>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) and access=0 ORDER BY billdate DESC LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);

  $data = $result->fetch_assoc();
  if ($data) {
    exit(JSON(array(
      'data'=>array('billdate'=>$data['billdate']),
      'msg'=>'频繁获取短信',
      'status'=>'2'
    )));
  }

  // 把验证码缓存到数据库
  $function = in_array($_p, $platformArray) ? $_p : '未知';
  $mysqli->query('BEGIN');
  $sql = "INSERT INTO team_smscache SET tel='$phone', functions='$function', randno='$rondnum', msg='{$msg}'";
  if (!$mysqli->query($sql)) {
    $mysqli->query('ROLLBACK');
    $mysqli->query('END');
    exit(JSON(array('data'=>'', 'msg'=>'验证码写入错误', 'status'=>'1')));
  }

  // 发送验证码
  require_once __DIR__.'/../utils/request.php';
  $url ='https://json.kassor.cn/sms/sendmsg.php?a=sendmsg&tel='.$phone.'&msg='.$msg;
  try {
    $request = new Request();
    $result = json_decode($request->get($url), true);
    if ($result['success'] == '1') {
      $mysqli->query('COMMIT');
      $mysqli->query('END');
      exit(JSON(array('data'=>'', 'msg'=>'请注意查收短信，有效期10分钟', 'status'=>'0')));
    } else {
      $msg = $result['msg'];
    }
  } catch (Exception $e) {
    $msg = $e->getMessage();
  }

  $mysqli->query('ROLLBACK');
  $mysqli->query('END');
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
}

// 注册 好业绩
if ($a == 'register') {
  $phone = checkInput($_GET['phone']);
  $password = checkInput($_GET['password']);
  $captcha = checkInput($_GET['captcha']);
  $username = '新用户_'.substr($phone, -4);

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
  $query = "SELECT id FROM team_salesman WHERE userno='$phone' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    exit(JSON(array('data'=>'', 'msg'=>'该账号已存在', 'status'=>'1')));
  }

  // 注册
  $sql = "INSERT INTO team_salesman SET username='$username', nickname='$username', billno='$_billno', `password`='$password', userno='$phone'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'注册失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'注册成功', 'status'=>'0')));
  }
}


// 微信qq登录--绑定手机号
if ($a == 'wxbindphone') {
  $phone = checkInput($_GET['phone']);
  $captcha = checkInput($_GET['captcha']);
  $username = '新用户_'.substr($phone, -4);
  $wkey = checkInput($_GET['wkey']);  // 区分微信,qq
  $openid = checkInput($_GET['openid']);
  $mstr="";

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
	  $mstr=" wxthreeload= '$openid'";
  }else{
	  $mstr=" qqthreeload= '$openid'";
  }
  $query = "SELECT id FROM team_salesman WHERE userno='$phone' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) { // 该账号已存在
	  $sql = "UPDATE `team_salesman` SET ".$mstr;
	  $sql .= " WHERE userno='$phone' ";
      if (!$mysqli->query($sql)) {
         exit(JSON(array('data'=>'', 'msg'=>'绑定失败', 'status'=>'1')));
      } else {
         exit(JSON(array('data'=>'', 'msg'=>'绑定成功', 'status'=>'0')));
      }  
    }
  // 初始密码
    $rp = substr($phone, -4);
    $mrepass = setstrmd5(md5($rp)); // md5加密

  // 注册
  $sql = "INSERT INTO team_salesman SET username='$username', nickname='$username', billno='$_billno', repassword='$mrepass',userno='$phone', ".$mstr;
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'注册失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'注册成功', 'status'=>'0')));
  }
}

 // 微信登录返回acc_token  好业绩
 if ($a == 'getacc_token') {
    $code = checkInput($_GET['code']);
	  $appId = checkInput($_GET['appId']);
    $secret = $serverConfig->getSecret();

	$AccessTokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$secret."&code=".$code."&grant_type=authorization_code";

    $result = json_decode(file_get_contents($AccessTokenUrl));
	exit(JSON(array('data'=>$result, 'msg'=>'', 'status'=>'0')));
  
 }

 // 微信登录返回acc_token  货真真
 if ($a == 'getacc_token_hzz') {
    $code = checkInput($_GET['code']);
    $appId = checkInput($_GET['appId']);
    $secret = $serverConfig->getHzzSecret();

  $AccessTokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$secret."&code=".$code."&grant_type=authorization_code";

    $result = json_decode(file_get_contents($AccessTokenUrl));
  exit(JSON(array('data'=>$result, 'msg'=>'', 'status'=>'0')));
  
 }

// 找回密码
if ($a == 'take_password') {
  $phone = checkInput($_GET['phone']);
  $password = checkInput($_GET['password']);
  $captcha = checkInput($_GET['captcha']);

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
  $query = "SELECT id FROM team_salesman WHERE userno='$phone' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if (!$data) {
    exit(JSON(array('data'=>'', 'msg'=>'该账号不存在', 'status'=>'1')));
  }

  // 更新
  $sql = "UPDATE team_salesman SET `password`='$password' WHERE id='{$data['id']}'";
  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'修改失败', 'status'=>'1')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'修改成功', 'status'=>'0')));
  }
}

// 获取用户信息
if ($a == 'getuserinfo') {
  $usercode = checkInput($_GET['billno']);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_salesman` WHERE billno='$usercode'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 修改密码
if ($a == 'setpass') {
  $uid = checkInput($_GET['uid']);
  $pass = checkInput($_GET['pass']);
  $newpass = checkInput($_GET['newpass']);

  if ($uid == '' || $pass == '' || $newpass == '') {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $pass = setstrmd5(md5($pass)); // md5加密
  $newpass = setstrmd5(md5($newpass)); // md5加密

  $query = "SELECT * FROM team_salesman WHERE (userno='$uid') AND ((`password`='$pass') OR (repassword='$pass'))";
  $result = $mysqli->query($query) or die($mysqli->error);
  if ($result->num_rows <= 0) {
    exit(JSON(array('data'=>'', 'msg'=>'原密码错误', 'status'=>'1')));
  }

  $sql = "UPDATE team_salesman SET `password`='$newpass',repassword='' WHERE userno='$uid'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'修改失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'修改成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 修改基本信息 web
if ($a == 'set_baseinfo') {
  $usercode = checkInput($_GET['usercode']);
  $username = checkInput($_GET['username']);
  $phone = checkInput($_GET['phone']);
  $tel = checkInput($_GET['tel']);
  $memo = checkInput($_GET['memo']);
  $province = checkInput($_GET['province']);
  $city = checkInput($_GET['city']);
  $address = checkInput($_GET['address']);
  $avatar = checkInput($_GET['avatar']);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$username) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "UPDATE team_salesman SET
    username='$username',
    phone='$phone',
    tel='$tel',
    memo='$memo',
    -- province='$province',
    -- city='$city',
    `address`='$address'";
  
  if (substr($avatar, 0, 4) == 'http') {
    $sql .= " ,image='$avatar'";
  }
  $sql .= " WHERE billno='$usercode'";

  if (!$mysqli->query($sql)) {
    exit(JSON(array('data'=>'', 'msg'=>'修改失败', 'status'=>'1')));
  } else {
    $data = array(
      'username'=>$username,
      'phone'=>$phone,
      'tel'=>$tel,
      'memo'=>$memo,
      // 'province'=>$province,
      // 'city'=>$city,
      'address'=>$address,
      'avatar'=>$avatar
    );
    exit(JSON(array('data'=>$data, 'msg'=>'修改成功', 'status'=>'0')));
  }
}

// 修改基本信息 app
if ($a == 'set_baseinfo_2') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  foreach ($obj as $key => $value) {
    $obj[$key] = checkInput($value);
  }

  $usercode = $obj['usercode'];
  $image = $obj['image'];
  $username = $obj['username'];
  $sex = $obj['sex'];
  $company = $obj['company'];
  $job = $obj['job'];
  $jobnumber = $obj['jobnumber'];
  $phone = $obj['phone'];
  $mail = $obj['mail'];
  $companyaddress = $obj['companyaddress'];
  $age = $obj['age'];
  $nativeplace = $obj['nativeplace'];
  $nation = $obj['nation'];
  $idcard = $obj['idcard'];
  $address = $obj['address'];
  $fax = $obj['fax'];
  $qq = $obj['qq'];
  $tel = $obj['tel'];

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $sql = "UPDATE team_salesman SET username='$username',sex='$sex',company='$company',job='$job',jobnumber='$jobnumber',phone='$phone',mail='$mail',companyaddress='$companyaddress',age='$age',nativeplace='$nativeplace',nation='$nation',idcard='$idcard',`address`='$address',`image`='$image',fax='$fax',qq='$qq',tel='$tel' WHERE billno='$usercode'";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'修改失败', 'status'=>'1');
  } else {
    $data = array(
      'image'=>$image,
      'username'=>$username,
      'sex'=>$sex,
      'company'=>$company,
      'job'=>$job,
      'jobnumber'=>$jobnumber,
      'phone'=>$phone,
      'mail'=>$mail,
      'companyaddress'=>$companyaddress,
      'age'=>$age,
      'nativeplace'=>$nativeplace,
      'nation'=>$nation,
      'idcard'=>$idcard,
      'address'=>$address,
      'fax'=>$fax,
      'qq'=>$qq,
      'tel'=>$tel
    );
    $output = array('data'=>$data, 'msg'=>'修改成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 获取店铺信息
if ($a == 'get_shopbaseinfo') {
  $admin = checkInput($_GET['admin']);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT * FROM `team_salesman` WHERE userno='$admin'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if ($r) {
    $query = "SELECT * FROM `mall_industry` WHERE `key`='{$r['industry']}'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $r2 = $result->fetch_assoc();
    $r['industry'] = $r2;
    // 认证的状态
    $query = "select styles from `team_license` where ubillno ='{$r['billno']}'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $r3 = $result->fetch_assoc();
    $r['styles'] = $r3['styles'] ? $r3['styles'] : '0';

    exit(JSON(array('data'=>$r, 'msg'=>'ok', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

// 设置店铺信息
if ($a == 'set_shopbaseinfo') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $usercode = checkInput($obj['usercode']);
  $company = checkInput($obj['company']);
  $company_scale = checkInput($obj['company_scale']);
  $company_logo = checkInput($obj['company_logo']);
  $company_phone = checkInput($obj['company_phone']);
  $company_tel = checkInput($obj['company_tel']);
  $company_linkman = checkInput($obj['company_linkman']);
  $company_attach = checkInput($obj['company_attach']);
  $company_show = checkInput($obj['company_show']);
  $company_intro = checkInput($obj['company_intro']);
  $company_img1 = checkInput($obj['company_img1']);
  $company_img2 = checkInput($obj['company_img2']);
  $company_img3 = checkInput($obj['company_img3']);
  $companyaddress = checkInput($obj['companyaddress']);
  $company_housenum = checkInput($obj['company_housenum']);
  $company_lng = checkInput($obj['company_lng']);
  $company_lat = checkInput($obj['company_lat']);
  $litrmb = checkInput($obj['litrmb']);
  $industry = $obj['industry'];

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (is_array($industry)) {
    $industry = $industry['key'];
  }

  $sql = "UPDATE team_salesman SET company='$company',company_scale='$company_scale',company_logo='$company_logo',company_phone='$company_phone',company_tel='$company_tel',company_linkman='$company_linkman',company_attach='$company_attach',company_show='$company_show',company_intro='$company_intro',company_img1='$company_img1',company_img2='$company_img2',company_img3='$company_img3',companyaddress='$companyaddress',company_housenum='$company_housenum',company_lng='$company_lng',company_lat='$company_lat',industry='$industry',litrmb='$litrmb' WHERE billno='$usercode'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'修改失败', 'status'=>'1');
  } else {
    $data = array(
      'company'=>$company,
      'company_scale'=>$company_scale,
      'company_logo'=>$company_logo,
      'company_phone'=>$company_phone,
      'company_tel'=>$company_tel,
      'company_linkman'=>$company_linkman,
      'company_attach'=>$company_attach,
      'company_show'=>$company_show,
      'company_intro'=>$company_intro,
      'company_img1'=>$company_img1,
      'company_img2'=>$company_img2,
      'company_img3'=>$company_img3,
      'companyaddress'=>$companyaddress,
      'company_housenum'=>$company_housenum,
      'company_lng'=>$company_lng,
      'company_lat'=>$company_lat,
      'industry'=>$industry
    );
    $output = array('data'=>$data, 'msg'=>'修改成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 商城是否开放
if ($a == 'eheck_shop') {
  $madmin = checkInput($_GET['madmin']);
  $company_show = checkInput($_GET['company_show']);

  if (!$madmin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  
  // 先判断最新的 company_show（待做）
  
  $query = "update `team_salesman` set company_show = '$company_show' WHERE userno='$madmin'";
  if (!$mysqli->query($query)) {
    exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
  }
    exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
}

// 商城-隐私设置
if ($a == 'eheck_shop_secret') {
  $madmin = checkInput($_GET['madmin']);
  $ischeck = checkInput($_GET['ischeck']);

  if (!$madmin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  
  $query = "update `team_salesman` set company_secret = '$ischeck' WHERE userno='$madmin'";
  if (!$mysqli->query($query)) {
    exit(JSON(array('data'=>'', 'msg'=>'操作失败', 'status'=>'1')));
  }
    exit(JSON(array('data'=>$data, 'msg'=>'ok', 'status'=>'0')));
}

 // 获取 隐私设置 信息
if ($a == 'getmallsecret') {
  $userno = checkInput($_GET['userno']);
  $custopen = checkInput($_GET['custopen']);
  $output = array('list'=>array());

  if (!$userno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM team_customer WHERE admin='$userno' AND custopen='$custopen' AND status>'-1' ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
}

// 设置地区派送
if ($a == 'setshoparea') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $code = checkInput($obj['code']);
  $flag = checkInput($obj['flag']); // 0 点击选中， 1不选中
  $newdata = array();
  $mstr = "[$code]";
  $msg = $flag == 0 ? '添加' : '取消';
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

   $query = "SELECT expareas FROM  `team_salesman` WHERE userno='$admin'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $data = $result->fetch_assoc();
    if ($data['expareas']) {
        $newdata = explode(',', $data['expareas']);
        if ($flag == 0) { // 点击选中时
          if(in_array($mstr,$newdata)){ // 包含
            exit(JSON(array('data'=>'', 'msg'=>'该地区已经存在', 'status'=>'1')));
          } else { // 不包含
            array_push($newdata, $mstr);
          }
        } else { // 点击 不选中
            $key = array_search($mstr ,$newdata);
            array_splice($newdata,$key,1);
        }
    } else {
       array_push($newdata, $mstr);
    }
     $comma_separated = implode(',', $newdata);
   
  $sql = "UPDATE `team_salesman` SET expareas='$comma_separated' WHERE userno='$admin'";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>$msg.'失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>$msg.'成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 获取 地区派送 code
if ($a == 'getshoparea') {
  $admin = checkInput($_GET['admin']);
  // $output = array();

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "SELECT expareas FROM  `team_salesman` WHERE userno='$admin'";
    $result = $mysqli->query($query) or die($mysqli->error);
    $data = $result->fetch_assoc();
    if ($data['expareas']) {
        $newdata = explode(',', $data['expareas']);
        exit(JSON(array('data'=>$newdata, 'msg'=>'ok', 'status'=>'0')));
    } else {
        exit(JSON(array('data'=>'', 'msg'=>'', 'status'=>'1')));
    }
}


// 删除隐私客户权限(多选)
if ($a == 'delmallsecret') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $custopen = checkInput($obj['custopen']);
  $mstr = '';

  foreach ($obj['selectedRowKeys'] as $k => $v) {
   if($k>0){
   $mstr .= ',';
   }  
   $mstr .= "'$v'";
  }
  $sql = "UPDATE  `team_customer` SET custopen='$custopen' WHERE billno IN ($mstr)";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=> $custopen == '0' ? '删除失败' : '添加失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>$custopen == '0' ? '删除成功' : '添加成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 提交身份
if ($a == 'set_idapprovinfo') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $usercode = checkInput($obj['usercode']);
  $uid = checkInput($obj['uid']);
  $card_img1 = checkInput($obj['card_img1']);
  $card_img2 = checkInput($obj['card_img2']);
  $card_img3 = checkInput($obj['card_img3']);
  $license_img = checkInput($obj['license_img']);
  $work_style = checkInput($obj['work_style']);
  $real_name = checkInput($obj['real_name']);
  $company = checkInput($obj['company']);
  $license_no = checkInput($obj['license_no']);
  $sex = checkInput($obj['sex']);
  $card_no = checkInput($obj['card_no']);
  $end_date = checkInput($obj['end_date']);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  $query = "select * from `team_license` where ubillno = '$usercode'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if ($r) {
     $sql = "UPDATE `team_license` SET  card_img1='$card_img1',card_img2='$card_img2',card_img3='$card_img3'
            ,license_img='$license_img',work_style='$work_style',real_name='$real_name',company='$company',license_no='$license_no',sex='$sex'
            ,card_no='$card_no',end_date='$end_date',styles='1' WHERE ubillno='$usercode'";
  } else {
    $sql = "INSERT INTO `team_license` SET billno='$_billno',ubillno='$usercode',card_img1='$card_img1',card_img2='$card_img2',card_img3='$card_img3'
            ,license_img='$license_img',work_style='$work_style',real_name='$real_name',company='$company',license_no='$license_no',sex='$sex'
            ,card_no='$card_no',end_date='$end_date',styles='1'";
  }
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'提交失败', 'status'=>'1');
  } else {
    $msql = "INSERT INTO`team_message` SET billno='$_billno', billdate=NOW(),creuser='系统发送',creuserno='13100000003',title='实名认证'
      ,`message`='你的实名认证信息正在审核中...',username='$real_name',userno='$uid',`admin`='',`type`='2',`image`=''";
    $mysqli->query($msql);
    $output = array('data'=>'', 'msg'=>'提交成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 查询身份
if ($a == 'get_idapprovinfo') {
  $usercode = checkInput($_GET['usercode']);

  if (!$usercode) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  $query = "select * from `team_license` where ubillno = '$usercode'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $r = $result->fetch_assoc();
  if ($r) {
    exit(JSON(array('data'=>$r, 'msg'=>'ok', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无数据', 'status'=>'1')));
  }
}

 // 查询云打印机状态
if ($a == 'get_printerstatus') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $printno = checkInput($obj['printno']);

  if (!$printno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  $result = printerStatus($printno);

  if ($result['status'] == '0') {
    exit(JSON(array('data'=>'', 'msg'=>$result['msg'], 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>$result['msg'], 'status'=>'1')));
  }
}

// 我的打印机
if ($a == 'get_printerinfo') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $userbno = checkInput($obj['userbno']);

  if (!$userbno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
 
  $query = "SELECT printbrand,printersn,printerkey,echostatus FROM team_salesman WHERE billno='$userbno' AND printersn !='' AND printerkey !=''  LIMIT 1";
  $result = $mysqli->query($query);
   $printerInfo = $result->fetch_assoc();
  if($printerInfo){
	  $printbrand = $printerInfo['printbrand'];
	  $printersn = $printerInfo['printersn'];
	  $printerkey = $printerInfo['printerkey'];
	  $echostatus = $printerInfo['echostatus'];

	  // 是否已绑定
	  $is_bind = false;
	  if ($printbrand == '飞鹅' && $printersn && $printerkey) {
		$is_bind = true;
	  }
	  $printerInfo['is_bind'] = $is_bind;
	  exit(JSON(array('data'=>$printerInfo, 'msg'=>'ok', 'status'=>'0')));
	  
  }else {
	  exit(JSON(array('data'=>'', 'msg'=>'未绑定打印机', 'status'=>'1')));
  }
}

// 设置打印机
if ($a == 'set_printerinfo') {
  $input = file_get_contents('php://input');
  $obj = json_decode($input, true);

  $userbno = checkInput($obj['userbno']);
  $model = checkInput($obj['model']);
  $printno = checkInput($obj['printno']);
  $password = checkInput($obj['password']);
  $echostatus = isset($obj['echostatus']) ? checkInput($obj['echostatus']) : '1'; // 0：关闭 1：自动出单
  // $mprint = isset($obj['mprint']) ? checkInput($obj['mprint']) : '0'; // 0：关闭 1：菜品小票单独打印
  $flag = isset($obj['flag']) ? checkInput($obj['flag']) : '0'; // 0：绑定 1：解绑 2：输出 3：单独打印

  // if ($flag != '0' && $flag != '1' && $flag != '2' && $flag != '3') {
    // exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  // }
  if (!$userbno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

 // include_once 'lib/cloudprint/feie.php';
  if ($flag == '0') {
    // 绑定处理
    if ($model == '飞鹅') {
      $printerinfo = $printno.'#'.$password.'#'.'云打印'.$userbno;
      $printresult = feie_addcloudPrinter($printerinfo);
      $retjson = json_decode($printresult, true);
      if ($retjson['ret'] == 0) {
          $query = "UPDATE  team_salesman SET printbrand='$model',printersn='$printno',printerkey='$password' WHERE billno='$userbno'";
          if ($mysqli->query($query)) {
            exit(JSON(array('data'=>'', 'msg'=>'绑定成功', 'status'=>'0')));
          } else {
            exit(JSON(array('data'=>'', 'msg'=>'绑定失败', 'status'=>'1')));
          }       
      } else {
        exit(JSON(array('data'=>'', 'msg'=>$retjson['msg'], 'status'=>'1')));
      }
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'暂时不支持的品牌', 'status'=>'1')));
    }
  } else if ($flag == '1') {
    // 解绑处理
    if ($model == '飞鹅') {
      $query = "UPDATE  team_salesman SET printbrand='',printersn='',printerkey='' WHERE billno='$userbno'";
        if ( $mysqli->query($query)) {
          exit(JSON(array('data'=>'', 'msg'=>'解绑成功', 'status'=>'0')));
        } else {
          exit(JSON(array('data'=>'', 'msg'=>'解绑失败', 'status'=>'1')));
        }
    } else {
      exit(JSON(array('data'=>'', 'msg'=>'暂时不支持的品牌', 'status'=>'1')));
    }
  }
  // 暂时不做
  // else if ($flag == '2') {
    // $query = "UPDATE foodmenu_user SET echostatus='$echostatus' WHERE billno='$usercode'";
    // if ($mysqli->query($query)) {
      // exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
    // } else {
      // exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
    // }
  // } else if ($flag == '3') {
    // $query = "UPDATE foodmenu_user SET mprint='$mprint' WHERE billno='$usercode'";
    // if ($mysqli->query($query)) {
      // exit(JSON(array('data'=>'', 'msg'=>'设置成功', 'status'=>'0')));
    // } else {
      // exit(JSON(array('data'=>'', 'msg'=>'设置失败', 'status'=>'1')));
    // }
  // }
}


