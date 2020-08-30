<?php
// 团队管理

// 开通团队(3用户试用版本)
if ($a == 'openteam') {
	$usercode = checkInput($_GET['usercode']);
	$userno = checkInput($_GET['userno']);

	if (!$usercode) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
  if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'手机号为空,请先绑定手机号！', 'status'=>'1')));
  }
  
  $query = "SELECT username,company FROM team_salesman WHERE billno='$usercode' LIMIT 1";
  $result = $mysqli->query($query) or die($mysqli->error);
  $userInfo = $result->fetch_assoc();
	if (!$userInfo) {
		exit(JSON(array('data'=>'', 'msg'=>'不存在', 'status'=>'1')));
  }
  $teamname = $userInfo['company'] ? $userInfo['company'] : $userInfo['username'].'的团队';

	$now = date('Y-m-d H:i:s'); // 当前时间
	$yearnum = 1; // 年数
	$teamdate = date('Y-m-d H:i:s', strtotime($now.'+'.$yearnum.' year')); // 到期时间

	$sql = "UPDATE team_salesman SET team=3,teamname='$teamname',teamdate='$teamdate',teamcreatedate=NOW() WHERE billno='$usercode'";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'创建失败', 'status'=>'1')));
	} else {
	  $sql = "INSERT INTO teams SET billno='$_billno',isteam='-1',`admin`='$userno',admincode='$usercode',division='$userno',userno='$userno',astatus='1'";
	  $mysqli->query($sql);
		exit(JSON(array('data'=>'', 'msg'=>'创建成功', 'status'=>'0')));
	}
}

// 更改团队名称
if ($a == 'change_teamname'){
	$teamname = checkInput($_GET['teamname']);
  $admin = checkInput($_GET['admin']);

  if (!$admin) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$teamname) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	$sql = "UPDATE `team_salesman` SET teamname='$teamname' WHERE userno='$admin'";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'更改团队名称失败', 'status'=>'1')));
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'更改团队名称成功', 'status'=>'0')));
	}
}

// 关闭团队(注销)
if ($a == 'offteam') {
	$usercode = checkInput($_GET['usercode']);
  $username = checkInput($_GET['username']);
	$phone = checkInput($_GET['phone']);
  $captcha = checkInput($_GET['captcha']);

	$msg = "管理员".$username."主动解散了团队！";

	if (!$usercode) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	if (!$phone) {
    exit(JSON(array('data'=>'', 'msg'=>'手机号不能为空', 'status'=>'1')));
  }

  if (!$captcha) {
    exit(JSON(array('data'=>'', 'msg'=>'验证码不能为空', 'status'=>'1')));
  }

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

  $sql = "UPDATE team_salesman SET team=0,teamname='',teamdate=NULL,teamcreatedate=NULL WHERE billno='$usercode'";

	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'注销团队失败', 'status'=>'1')));
	} else {  
  // 信息通知所有员工 (暂时只通知第二级的组长，批量插入问题)
		$query = "SELECT userno FROM `teams` WHERE admin='$phone' AND division='$phone'";
		$result = $mysqli->query($query) or die($mysqli->error);
     while ($row = $result->fetch_assoc()) {
     	$muid = $row['userno'];
	    $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='系统发送',creuserno='$phone',`admin`='$phone', title='注销团队提示',`message`='$msg',userno='$muid',`type`=1";
		  $mysqli->query($sql);
	  }

  // TODO：还要取消队员的关联
  	$sql = "DELETE FROM `teams`  WHERE admin='$phone'";
		if (!$mysqli->query($sql)) {
			exit(JSON(array('data'=>'', 'msg'=>'注销团队删除队员异常!', 'status'=>'1')));
		}
		exit(JSON(array('data'=>'', 'msg'=>'注销团队处理成功', 'status'=>'0')));
	}
}

// 我的团队
if ($a == 'getgroup') {
	$admin = checkInput($_GET['admin']);
	$uid = checkInput($_GET['uid']);
	$key = checkInput($_GET['key']);  // -1副管理员 0管理员 1组长 2队员 3代理，批发
	$division = checkInput($_GET['division']); // 上级
	$sub = checkInput($_GET['sub']);

	if (!$uid) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	if ($key == '-1') { // 副管理员 musername管理员名
			$query = "SELECT A.num AS htolnum,C.username AS hname,C.image,C.billno,C.userno,B.username AS musername,B.team AS htnum,B.teamdate AS htdate,B.teamname AS htname,B.teamname AS maintname,B.teamcreatedate AS tcreatedate,D.num AS hnum FROM (SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin') A,
							(SELECT username,team,teamdate,teamname,teamcreatedate FROM `team_salesman` WHERE userno='$admin') B,
							(SELECT username,image,billno,userno FROM `team_salesman` WHERE userno='$uid') C,
							(SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin' AND division='$admin') D";

	} else if ($key == '0') { // (管理员角度)得到已有所有队员num, 管理员名username, 许可数量team,到期日teamdate
			$query = "SELECT A.num AS htolnum,B.image,B.billno,B.userno,B.username AS hname,B.team AS htnum,B.teamdate AS htdate,B.teamname AS htname,B.teamname AS maintname,B.teamcreatedate AS tcreatedate,C.num AS hnum FROM (SELECT COUNT(*) AS num FROM `teams` WHERE admin='$uid') A,
							(SELECT image,billno,userno,username,team,teamdate,teamname,teamcreatedate FROM `team_salesman` WHERE userno='$uid') B,
							(SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin' AND division='$admin') C";

	} else if ($key == '1') { // (组长角度) 已有队员num, 组长名username, 工作部门 teamname
			$query = "SELECT A.num AS hnum,B.image,B.billno,B.userno,B.username AS hname,B.teamname AS htname,C.teamname AS maintname,C.team AS htnum,B.teamdate AS htdate,B.teamcreatedate AS tcreatedate,D.num AS htolnum FROM (SELECT COUNT(*) AS num FROM `teams` WHERE division='$uid') A,
							(SELECT image,billno,userno,username,teamname,teamdate,teamcreatedate FROM `team_salesman` WHERE userno='$uid') B,
							(SELECT (team - (SELECT COUNT(*) FROM `teams` WHERE admin='$admin')) AS team,teamname FROM `team_salesman` WHERE userno='$admin') C,
							(SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin') D";

	} else if ($key == '2') { // (队员角度)  已有队员num, 组长名username, 工作部门 teamname
			$query = "SELECT A.num AS hnum,B.image,B.billno,B.userno,B.username AS hname,B.teamname AS htname,C.teamname AS maintname,B.team AS htnum,B.teamdate AS htdate,B.teamcreatedate AS tcreatedate,D.num AS htolnum FROM (SELECT COUNT(*) AS num FROM `teams` WHERE division ='$division') A,
							(SELECT image,billno,userno,username,teamname,team,teamdate,teamcreatedate FROM `team_salesman` WHERE userno='$uid') B,
							(SELECT teamname FROM `team_salesman` WHERE userno='$admin') C,
							(SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin') D";
	} else if ($key == '3') { // ----  代理，批发 -------
      $query = "SELECT A.num AS htolnum,B.image,B.billno,B.userno,B.username AS hname,B.team AS htnum,B.teamdate AS htdate,B.teamname AS htname,B.teamname AS maintname,B.teamcreatedate AS tcreatedate,C.num AS hnum FROM (SELECT COUNT(*) AS num FROM `teams` WHERE admin='$uid') A,
							(SELECT image,billno,userno,username,team,teamdate,teamname,teamcreatedate FROM `team_salesman` WHERE userno='$uid') B,
							(SELECT COUNT(*) AS num FROM `teams` WHERE admin='$admin' AND division='$admin') C";
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'key不匹配', 'status'=>'1')));
	}

	$result = $mysqli->query($query) or die($mysqli->error);
	$head = array();
	if ($result->num_rows > 0) {
		  // htolnum 全部员工
			// hnum 已有多少组长或 多少员工
			// hname 管理员名，组长名
			// htnum 许可数量
			// htdate 到期日
      // htname 团队名，小组名
		  // maintname 团队名
      // tcreatedate 团队创建时间
			$row = $result->fetch_assoc();
			array_push($head, $row);
	}
	if ($key == '-1') { // 副管理员查询有多少个组长(副管理员不显示包括自己，但显示的其它副管理员)
		
			$query = "SELECT t_billno,u_billno,isteam,astatus,username,userno,image,teamname,job  FROM view_groupuserlist WHERE admin='$admin' AND admin=division AND userno !='$uid' ORDER BY isteam DESC";    //使用视图方式简化查询语句
			//$query = "SELECT a.billno AS t_billno,b.billno AS u_billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.teamname,b.job FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` WHERE a.admin='$admin' AND a.admin=division AND a.userno !='$uid' ORDER BY a.isteam DESC,a.id DESC";

	} else if ($key == '0') { // 管理员查询有多少个组长
			$query = "SELECT t_billno,u_billno,isteam,astatus,username,userno,image,teamname,job  FROM view_groupuserlist WHERE admin='$uid' AND admin=division and isteam != '-1' ORDER BY isteam DESC";    //使用视图方式简化查询语句
			//$query = "SELECT a.billno AS t_billno,b.billno AS u_billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.teamname,b.job FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` WHERE a.admin='$uid' AND a.admin=division and a.isteam != '-1' ORDER BY a.isteam DESC,a.id DESC";

	} else if ($key == '1') { // 组长查询自己有多少个下级员工	,上级，同辈
			// $query = "SELECT a.billno AS t_billno,b.billno AS u_billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.job,b.teamname FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno`
			// 				WHERE division='$uid' AND `admin`!=division ORDER BY a.isteam DESC,a.id DESC";
		//$query = "SELECT a.billno AS t_billno,b.billno AS u_billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.job,b.teamname FROM `view_groupuserlist` AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` WHERE (a.admin = a.division  and (a.isteam != '-3') and a.admin = '$admin' and a.userno !='$uid') or (a.division='$uid' AND a.admin != a.division and a.isteam = '-3') ORDER BY a.isteam DESC,a.billno DESC";	
			if ($sub == '0'){
				$query = "SELECT t_billno,u_billno,isteam,astatus,username,userno,image,teamname,job  FROM view_groupuserlist WHERE (admin = division  and (isteam != '-3') and admin = '$admin' and userno !='$uid') or (division='$uid' AND admin != division and isteam = '-3') ORDER BY isteam DESC";   //使用视图方式简化查询语句
			}else{
				$query = "SELECT t_billno,u_billno,isteam,astatus,username,userno,image,teamname,job  FROM view_groupuserlist WHERE (division='$uid' AND admin != division and isteam = '-3') ORDER BY isteam DESC";   //使用视图方式简化查询语句
			}		
				
	} else { // 队员查询自己所在的团队有多少个员工
			$query = "SELECT t_billno,u_billno,isteam,astatus,username,userno,image,teamname,job  FROM view_groupuserlist WHERE (division='$division' OR userno='$division') AND userno!='$uid' ORDER BY isteam DESC";   //使用视图方式简化查询语句
	
			//$query = "SELECT a.billno AS t_billno,b.billno AS u_billno,a.isteam,a.astatus,b.username,b.userno,b.image,b.job,b.teamname FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno`  WHERE (a.division='$division' OR a.userno='$division') AND a.userno!='$uid' ORDER BY a.isteam DESC,a.id DESC";
	}

	$result = $mysqli->query($query) or die($mysqli->error);
	$body = array();
	while ($row = $result->fetch_assoc()) {
		array_push($body, $row);
	}
	$data = array('head'=>$head, 'body'=>$body);
	exit(JSON(array('data'=>$data, 'msg'=>"获取成功", 'status'=>'0')));
}

// 申请加入团队
if ($a == 'applyjoingroup') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']); // 申请人手机
	$username = checkInput($_GET['username']);
	$aduserno = checkInput($_GET['aduserno']); // 管理员号码，组长号码

	$msg = $username."申请加入你的团队！";

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$aduserno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	// 查询aduserno号码是否存在
	$query = "SELECT billno FROM team_salesman WHERE userno='$aduserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows <= 0) {
		exit(JSON(array('data'=>'', 'msg'=>'没有找到该帐号', 'status'=>'1')));
	}

	// 查询aduserno是否开通升级了团队
	$query = "SELECT billno FROM `team_salesman` WHERE team>0 AND teamdate!='' AND userno='$aduserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows > 0) {
		// 是否是管理员
	} else {
		// 是否是各部门组长
		$query = "SELECT `admin` FROM `teams` WHERE userno='$aduserno' AND (division=admin) AND division!='' AND astatus='1'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if ($result->num_rows <= 0) {
			exit(JSON(array('data'=>'', 'msg'=>'该用户没有权限添加队员', 'status'=>'1')));
		}
	}

	$sql = "INSERT INTO `team_message` SET billno='$_billno', creuser='$username',creuserno='$userno', title='申请加入团队',`message`='$msg',username='管理员',userno='$aduserno',`type`=3";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'申请失败', 'status'=>'1')));
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'申请信息已发送，请等待通知！', 'status'=>'0')));
	}
}

// 扫码申请加入团队
if ($a == 'qraddgroup') {
	$admincode = checkInput($_GET['admincode']);
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']); // 申请人手机
	$username = checkInput($_GET['username']); // 申请人名
	$aduserno = checkInput($_GET['aduserno']); // 管理员号码，组长号码
	$isteam = checkInput($_GET['isteam']); // 二维码人的角色
	$checkId = checkInput($_GET['checkId']); // 申请人的职能

	$msg = $username."申请加入你的团队！";

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$aduserno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$isteam) {
		exit(JSON(array('data'=>'', 'msg'=>'该用户还没有开通团队', 'status'=>'1')));
	}

  // 查询自已是否已加了团队
  	$query = "SELECT `admin` FROM `teams` WHERE userno='$userno' AND admin='$admin' AND astatus IN (0,-1,1,2)";
		$result = $mysqli->query($query) or die($mysqli->error);
		if ($result->num_rows > 0) {
			exit(JSON(array('data'=>'', 'msg'=>'你已存在于团队中', 'status'=>'1')));
		}

	// 查询aduserno号码是否存在
	$query = "SELECT billno FROM team_salesman WHERE userno='$aduserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows <= 0) {
		exit(JSON(array('data'=>'', 'msg'=>'没有找到该帐号', 'status'=>'1')));
	}

	// 查询aduserno是否开通升级了团队
	$query = "SELECT billno FROM `team_salesman` WHERE team>0 AND teamdate!='' AND userno='$aduserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows > 0) {
		// 是否是管理员
	} else {
		// 是否是各部门组长
		$query = "SELECT `admin` FROM `teams` WHERE userno='$aduserno' AND (division=admin) AND division!='' AND astatus='1' AND isteam !='-3'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if ($result->num_rows <= 0) {
			exit(JSON(array('data'=>'', 'msg'=>'该用户没有权限添加队员', 'status'=>'1')));
		}
	}

  // 待审核的 团员 -----
  if ($isteam == '-2' || $isteam == '-1') { 
  	$sql = "INSERT INTO teams SET billno='$_billno',admincode='$admincode',isteam='$checkId',`admin`='$admin',division='$admin',userno='$userno'";
	    if (!$mysqli->query($sql)) {
		  	exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
	    }
	} else {
		  $sql = "INSERT INTO teams SET billno='$_billno',admincode='$admincode',isteam='-3',`admin`='$admin',division='$aduserno',userno='$userno'";
		  if (!$mysqli->query($sql)) {
				exit(JSON(array('data'=>'', 'msg'=>'添加失败', 'status'=>'1')));
			} 
  }

	$sql = "INSERT INTO `team_message` SET billno='$_billno', creuser='$username',creuserno='$userno', title='申请加入团队',`message`='$msg',username='管理员',userno='$aduserno',`type`=3";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'申请失败', 'status'=>'1')));
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'申请信息已发送，请等待通知！', 'status'=>'0')));
	}
}

// 加入团队，来自认证通知, 删除员工审核
// status 1同意 -1拒绝, flag 0加团 1 删除员工, certuid 被删除员工手机号
if ($a == 'jointeam'){
	$mbillno = checkInput($_GET['mbillno']);
	$status = checkInput($_GET['status']); // 0 等待通过 -1 退出(没通过) 1 已通过 2停用,-2(没通过)

  $flag = checkInput($_GET['flag']); // flag 0加团  1删除员工
  $certuid = checkInput($_GET['certuid']);
  $admin = checkInput($_GET['admin']);

  if($flag == '0' || $flag == '2'){ // 2 新人扫码申请 加入团队
    if (!$mbillno) {
			exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
		}
		if ($status != '0' && $status != '-1' && $status != '1' && $status != '2') {
			exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
		}
    // 要先判断 team 是否有这数据 ...

    if($status == '1'){
      $sql = "UPDATE `teams` SET astatus='$status' WHERE billno='$mbillno'";
    }else {
    	$sql = "UPDATE `teams` SET astatus='-2' WHERE billno='$mbillno'";
    }

		if (!$mysqli->query($sql)) {
			exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
		} else {
      // 系统消息
			// $messg = $status == '1' ? '你成功加入了'
			// if($flag == '2'){ // 2 新人扫码申请 加入团队
			//  $msql="INSERT INTO `team_message` SET billno='$_billno',creuser='系统发送',creuserno='$admin',
			// 							title='退出团队提示',message='$mess',userno='$certuid',`type`='1'";
			// 			$mysqli->query($msql);
			// }

			exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
		}

  } else { // 删除员工审核
     if (!$certuid) {
			exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
		 }
     // 先查询 被删除员工 是否还处于 astatus='-1'
     $query = "SELECT billno FROM `teams` WHERE userno='$certuid' AND admin='$admin' AND astatus='-1'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if ($result->num_rows <= 0) {
				exit(JSON(array('data'=>'', 'msg'=>'处理失败,该组长已经撤回删除员工申请！', 'status'=>'-1')));
			}

     // 同意删除员工
     if($status == '1'){
     	// astatus -1 待删除员工
     	$sql = "DELETE FROM `teams`  WHERE userno='$certuid' AND admin='$admin' AND astatus='-1'";
		  if (!$mysqli->query($sql)) {
				exit(JSON(array('data'=>'', 'msg'=>'删除失败！', 'status'=>'1')));
			}
      // 通知被删除的员工
      $mess = "你被管理员正式踢出了团队！";
			$msql="INSERT INTO `team_message` SET billno='$_billno',creuser='系统发送',creuserno='$admin',
							title='退出团队提示',message='$mess',userno='$certuid',`type`='1'";
			$mysqli->query($msql);

			exit(JSON(array('data'=>'', 'msg'=>'删除成功', 'status'=>'0')));
     } else { // 管理员-审核拒绝删除
       	$sql = "UPDATE `teams` SET astatus='1' WHERE userno='$certuid' AND admin='$admin' AND astatus='-1'";
			  if (!$mysqli->query($sql)) {
					exit(JSON(array('data'=>'', 'msg'=>'处理失败！', 'status'=>'1')));
				}
				exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
     }
  }	
}

// 退出团队
if ($a == 'outgroup') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']);  // 自已号码
	$password = checkInput($_GET['password']);
  $division = checkInput($_GET['mdivision']);

  $password = setstrmd5(md5($password)); // md5加密

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
	}
  // 检查 password
	$query = "SELECT username FROM team_salesman  WHERE (userno='$userno') AND ((password='$password') OR (repassword='$password' AND (lastdate BETWEEN DATE_ADD( NOW(), INTERVAL -1 HOUR)  AND  NOW() )))";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows > 0) {
		
	$row = $result->fetch_assoc();
	$username = $row['username'];
	$msg = $username."主动退出了团队！";

// 1.检查  下级是否有员工
   $query = "SELECT billno FROM `teams`  WHERE admin='$admin' AND division='$userno' AND astatus>='-1'";
	 $mresult = $mysqli->query($query) or die($mysqli->error);
	 if ($mresult->num_rows > 0) {
      exit(JSON(array('data'=>'', 'msg'=>'退团失败,请先转移下级员工！', 'status'=>'1')));
	 }

   $query = "SELECT division FROM `teams`  WHERE admin='$admin' AND userno='$userno' AND astatus>='-1'";
	  $mresult = $mysqli->query($query) or die($mysqli->error);
		$row = $mresult->fetch_assoc();
	  if ($row) {
      $division = $row['division'];
	  } else {
	  	exit(JSON(array('data'=>'', 'msg'=>'无法查询上级手机号', 'status'=>'1')));
	  }

// 2.检查  是否拥有客户(有，则把客户传给上级)
	 $query = "SELECT billno FROM `team_customer`  WHERE admin='$admin' AND saler='$userno' AND status>'-1'";
	 $mresult = $mysqli->query($query) or die($mysqli->error);
	 while ($row = $mresult->fetch_assoc()) {
	 	   $bno = $row['billno'];
       $sql = "UPDATE `team_customer` SET saler='$division'  WHERE billno='$bno'";
			 $mysqli->query($sql);
   }

		$sql = "DELETE FROM `teams`  WHERE userno='$userno' AND admin='$admin'";
		if (!$mysqli->query($sql)) {
			exit(JSON(array('data'=>'', 'msg'=>'退出失败', 'status'=>'1')));
		}

		$sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='系统发送',creuserno='$userno',`admin`='$admin', title='退出团队提示',`message`='$msg',userno='$division',`type`=1";
		$mysqli->query($sql);

		exit(JSON(array('data'=>'', 'msg'=>'退出成功', 'status'=>'0')));
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'密码错误', 'status'=>'1')));
	}
}

// 删除队员
if ($a == 'delgroupuser') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']); // 管理员或组长 手机号
	$adminame = checkInput($_GET['adminame']); // 管理员名或组长名
	$typename = checkInput($_GET['typename']); 
	$uid = checkInput($_GET['uid']); // 队员id
	$username = checkInput($_GET['username']); // 队员名称
	$isdell = checkInput($_GET['isdell']); // 0取消删除， 1删除
	$flag = checkInput($_GET['flag']);  // 0、1主副管理员 2组长

	if (!$uid) {
		exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
	}
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
	}

  // 防止管理员 误删 下级员工（管理员删操作，先和组长沟通）
   $query = "SELECT billno FROM `teams`  WHERE admin='$admin' AND division='$uid' AND astatus>='-1'";
	 $mresult = $mysqli->query($query) or die($mysqli->error);
	 if ($mresult->num_rows > 0) {
      exit(JSON(array('data'=>'', 'msg'=>'删除失败,该组长关联了多位员工！', 'status'=>'1')));
	 }

	// 先直接删除行记录
	// 管理员，副管理员可直接删除员工
	if ($flag=='0' || $flag=='1') {
	   $sql = "DELETE FROM teams WHERE userno='$uid' AND division='$admin' AND admin='$admin'";
		 if (!$mysqli->query($sql)) {
			$output = array('data'=>'', 'msg'=>'删除失败', 'status'=>'1');
		} else {
			// 添加信息通知
			if ($flag=='0') {
				$mess = "你被管理员:".$adminame."踢出了团队！";
			} else if ($flag=='1'){
				$mess = "你被副管理员:".$adminame."踢出了团队！";
			} else {
				$mess = "你被组长:".$adminame."踢出了团队！";
			}

			$sql="INSERT INTO `team_message` SET billno='$_billno',creuser='系统发送',
							title='退出团队提示',message='$mess',username='$username',userno='$uid',`type`='1'";
			$mysqli->query($sql);
			$output = array('data'=>'', 'msg'=>'删除成功', 'status'=>'0');
		}
		exit(JSON($output));
	} else { // 组长 不能直接删除员工，只能提出 删除员工 的申请
		 if ($isdell == '0') { // 取消删除
        $mstus = "1";
        $kmsg = "已取消提交删除申请";
		 } else {
		   	$mstus = "-1";
		   	$kmsg = "已提交删除申请";
		 }
     $sql = "UPDATE teams  SET astatus='$mstus' WHERE userno='$uid' AND division='$userno' AND admin='$admin'";
		 if (!$mysqli->query($sql)) {
			$output = array('data'=>'', 'msg'=>'提交失败', 'status'=>'1');
		} else {
				if ($isdell != '0') {
		      // 信息通知审核 是否删除 (要保证这信息的唯一性)
			    $r_title = $adminame."(".$typename.")";
					$r_msg = $adminame."申请删除小组成员[".$username."]，是否同意？";
					// certuid 被删除者的 手机号
          $query = "SELECT userno FROM `teams` WHERE admin='$admin' AND division='$admin' AND (isteam = '-1' OR isteam = '-2') AND astatus = '1'";
					$result = $mysqli->query($query) or die($mysqli->error);
			     while ($row = $result->fetch_assoc()) {
			     	$muid = $row['userno'];
				    $sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='$r_title',creuserno='$userno',`admin`='$admin',title='删除员工审核',`message`='$r_msg',userno='$muid',`type`='3',certuid='$uid'";
				    $mysqli->query($sql);
				  }
	      }
    $output = array('data'=>'', 'msg'=>$kmsg, 'status'=>'0');
		}
     exit(JSON($output));
	}
}

// 添加队员
if ($a == 'addgroupuser') {
	$admincode = checkInput($_GET['admincode']);
	$admin = checkInput($_GET['admin']);
	$tuserno = checkInput($_GET['tuserno']); // 队员手机号
	$teamname = checkInput($_GET['teamname']);  // 部门名称
	$job = checkInput($_GET['job']);  // 部门职位
	$operatorno = checkInput($_GET['operatorno']); // 管理员或组长userno
	$operatorname = checkInput($_GET['operatorname']); // 管理员或组长username
	$type = checkInput($_GET['type']); // 职位  -3普通队员, -2副经理（秘书助手）、 -1 经理、0代理、1队员（销售类)、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它、9批发商',
	$flag = checkInput($_GET['flag']); // 1 管理员操作 2组长操作

	// 收信人
	$r_billno = "";
	$r_username = "";
	$r_title = "";
	$r_msg = "";

	if (!$tuserno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$teamname) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	// 查询需要添加的用户
	$query = "SELECT billno,username,team FROM team_salesman WHERE userno='$tuserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$r_billno = $row['billno'];
		$r_username = $row['username'];
		$team = $row['team'];
    if ($team > 0 ) { // 查询是否是 管理员
      exit(JSON(array('data'=>'', 'msg'=>'该用户已经是其它团队的管理员!', 'status'=>'1')));
    }
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'该用户还未注册!', 'status'=>'1')));
	}

	// 是否已被添加(查询是否是 员工)
	$query = "SELECT * FROM teams where (userno='$tuserno' OR division='$tuserno') AND astatus>='-1'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if ($result->num_rows > 0) {
		exit(JSON(array('data'=>'', 'msg'=>'该队员已服务其它工作组', 'status'=>'1')));
	}

	if ($flag == '1') {
		$sql = "INSERT INTO teams SET billno='$_billno',admincode='$admincode',isteam=$type,`admin`='$admin',division='$admin',userno='$tuserno'";
		if ($type == '-2') {
			// 设置副管理员
			$r_title = $operatorname."(管理员)";
			$r_msg = $operatorname."诚邀您来当[".$teamname."]副管理员！";
		} else if ($type == '0') {
      // 添加代理
      $r_title = $operatorname."(管理员)";
			$r_msg = $operatorname."诚邀您来当[".$teamname."]的代理！";
		} else if ($type == '9') {
      // 添加批发
      $r_title = $operatorname."(管理员)";
			$r_msg = $operatorname."诚邀您来当[".$teamname."]的批发商！";
		} else if ($type == '1' || $type == '2' || $type == '3' || $type == '4' || $type == '5' || $type == '6' || $type == '7' || $type == '8') {
			// 添加组长
			$r_title = $operatorname."(管理员)";
			$r_msg = $operatorname."诚邀您来当[".$teamname."]小组的组长！";
		} else {
			exit(JSON(array('data'=>'', 'msg'=>'type参数不匹配', 'status'=>'1')));
		}
	} else if ($flag == '2') {
		$r_title = $operatorname."(组长)";
		$r_msg = $operatorname."诚邀您加入[".$teamname."]小组，成为我们的一员！";
		$sql = "INSERT INTO teams SET billno='$_billno',admincode='$admincode',isteam='-3',`admin`='$admin',division='$operatorno',userno='$tuserno'";
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'flag参数不匹配', 'status'=>'1')));
	}

	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
	}

	// 更新被添加用户的团队名
	$tajob ="";
	if ($job) {
    $sql="UPDATE `team_salesman` SET teamname='$teamname',job='$job' WHERE billno='$r_billno'";
	}else{
    $sql="UPDATE `team_salesman` SET teamname='$teamname' WHERE billno='$r_billno'";
	}
	$mysqli->query($sql);

	// 发送通知
	if ($flag == '1') {
		$sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='$r_title',creuserno='$operatorno',`admin`='$admin',title='加入到团队',`message`='$r_msg',username='$r_username',userno='$tuserno',`type`='3'";
	} else if ($flag == '2') {
		$sql = "INSERT INTO `team_message` SET billno='$_billno',creuser='$r_title',creuserno='$operatorno',`admin`='$admin',title='加入到团队',`message`='$r_msg',username='$r_username',userno='$tuserno',`type`='3'";
	}
	$mysqli->query($sql);

	exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
}

// 管理队员
if ($a == 'editgroupuser') {
	$admin = checkInput($_GET['admin']);
	$teamname = checkInput($_GET['teamname']); // 部门名称
	$job = checkInput($_GET['job']); // 职位名称
	$username = checkInput($_GET['username']); // 队员名字
	$type = checkInput($_GET['type']); // 部门职能 -2副管理员 0代理，1销售 2财务 3仓库 4送货 组长操作才需要该参数
	$u_billno = checkInput($_GET['u_billno']); // 队员billno
	$t_billno = checkInput($_GET['t_billno']); // 队员团队billno
	$flag = checkInput($_GET['flag']); // 1 管理员操作 2组长操作

	if (!$u_billno) {
		exit(JSON(array('data'=>'', 'msg'=>'u_billno参数不能为空', 'status'=>'1')));
	}
	if (!$t_billno) {
		exit(JSON(array('data'=>'', 'msg'=>'t_billno参数不能为空', 'status'=>'1')));
	}

  

	if ($flag == '1') {
		$sql = "UPDATE `teams` SET isteam='$type' WHERE billno='$t_billno'";
		$mysqli->query($sql);
	}

	if ($job) {
		$sql = "UPDATE `team_salesman` SET teamname='$teamname',username='$username',job='$job' WHERE billno='$u_billno'";
	} else {
	  $sql = "UPDATE `team_salesman` SET teamname='$teamname',username='$username' WHERE billno='$u_billno'";
  }
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'处理失败', 'status'=>'1')));
	} else {
		exit(JSON(array('data'=>'', 'msg'=>'处理成功', 'status'=>'0')));
	}
}

// 团队 更换从属关系
if ($a == 'changesubordinate') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']); // 该队员的号码
	$username = checkInput($_GET['username']); // 该队员的名称
	$aduserno = checkInput($_GET['aduserno']); // 选择的从属人员号码
	$adusername = checkInput($_GET['adusername']); // 选择的从属人员名称

	$msg = $username."成功并入到".$adusername."的团队中";

  
	if (!$admin) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$aduserno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
  // 转移组长的队员
	$sql = "UPDATE `teams` SET division='$aduserno' WHERE division='$userno' And admin ='$admin'";
	$mysqli->query($sql);

	$sql = "UPDATE `teams` SET division='$aduserno',isteam='-3' WHERE userno='$userno' And admin ='$admin'";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'更换从属失败', 'status'=>'1')));
	}

	$sql = "INSERT INTO `team_message` SET billno='$_billno', creuser='系统发送',creuserno='$admin', title='更换从属提示',`message`='$msg',username='管理员',userno='$aduserno',`type`=1";
	$mysqli->query($sql);

	exit(JSON(array('data'=>'', 'msg'=>'更换从属成功', 'status'=>'0')));
}

// 更换 客户的从属关系
if ($a == 'changesubkh') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']); // 该队员的号码
	$username = checkInput($_GET['username']); // 该队员的名称
	$aduserno = checkInput($_GET['aduserno']); // 选择的从属人员号码
	$adusername = checkInput($_GET['adusername']); // 选择的从属人员名称

	$msg = $username."的客户成功并入到".$adusername."中";

  
	if (!$admin) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	if (!$aduserno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
  // 转移队员的客户

	$sql = "UPDATE `team_customer` SET saler='$aduserno' WHERE saler='$userno' And admin ='$admin' AND status>'-1'";
	if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'更换从属失败', 'status'=>'1')));
	}

	$sql = "INSERT INTO `team_message` SET billno='$_billno', creuser='系统发送',creuserno='$admin', title='更换客户从属提示',`message`='$msg',username='管理员',userno='$aduserno',`type`=1";
	$mysqli->query($sql);

	exit(JSON(array('data'=>'', 'msg'=>'更换从属成功', 'status'=>'0')));
}

// 查询从属名单
if ($a == 'getgroupsubordinate') {
	$admin = checkInput($_GET['admin']);
	$userno = checkInput($_GET['userno']);
	$output = array('list'=>array(), 'total'=>0);

	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}

	$query = "SELECT A.userno,B.image,B.username FROM `teams`  AS A LEFT JOIN team_salesman B ON A.`userno`=B.`userno`
					WHERE admin='$admin' AND admin=division AND isteam!=-2 AND astatus NOT IN(0,-1) AND A.userno !='$userno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}

	exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 查询下属员工手机号（任务成员）
if ($a == 'getgroupmember') {
  $admin = checkInput($_GET['admin']); // 管理员
  $division = checkInput($_GET['division']); // 组长
	$uid = checkInput($_GET['uid']);
	$output = array('list'=>array(), 'total'=>0);

	if (!$uid) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'暂无团队成员', 'status'=>'1')));
  }

  // 管理员
  $query = "SELECT userno,username AS viewname,`image` FROM `team_salesman` WHERE userno='$admin' LIMIT 1";
  $result = $mysqli->query($query);
  $r = $result->fetch_assoc();
  if ($r) {
    // 如果是管理员登录则排除自己
    if ($r['userno'] != $uid) {
      array_push($output['list'], $r);
    }
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'暂无团队成员', 'status'=>'1')));
  }

	$query = "SELECT distinct b.userno,b.username AS viewname,b.image FROM teams AS a
						LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno`
						WHERE (a.admin='$uid' OR a.admin='$admin' OR a.division='$uid' OR a.division='$division')
            AND a.userno!='$uid'
            AND a.astatus NOT IN(0,-1)";
	$r2 = $mysqli->query($query) or die($mysqli->error);
	while ($row = $r2->fetch_assoc()) {
    array_push($output['list'], $row);
  }
	exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 查询每个人的考勤，合同，报销等的统计
 if($a == 'getteamstatnum'){
	$userno = checkInput($_GET['userno']); 
	$output = array(
    'attendchart'=>array(),
    'contrachart'=>array(),
	'taskplanchart'=>array()
  );
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
	// 查询 考勤
	 $query = "SELECT A.c AS normal,B.c AS late,C.c AS leavelate,D.c AS absent FROM          
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='1' AND endcheckwork='1' 
  AND DATE_FORMAT(startworktime,'%H:%i:%S')<='09:00:00' AND DATE_FORMAT(endworktime,'%H:%i:%S')>='18:00:00' AND YEAR(billdate)=YEAR(NOW())) A,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='1' 
  AND DATE_FORMAT(startworktime,'%H:%i:%S')>'09:00:00' AND YEAR(billdate)=YEAR(NOW())) B,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND endcheckwork='1' 
  AND DATE_FORMAT(endworktime,'%H:%i:%S')<'18:00:00' AND YEAR(billdate)=YEAR(NOW())) C,
(SELECT COUNT(*) AS c FROM `team_attendance` WHERE userno='$userno' AND startcheckwork='0' AND endcheckwork='0' AND YEAR(billdate)=YEAR(NOW())) D";
  $result = $mysqli->query($query) or die($mysqli->error);
  $row = $result->fetch_assoc();
  if ($row){
	array_push($output['attendchart'], $row);
 }
   clearStoredResults();
 
 // 查询 合同,报销
  $query = "call getContractChartData('$userno');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
	array_push($output['contrachart'], $row);
 }
   clearStoredResults();
  
 // 查询 任务，计划
  $query = "call getTaskPlanChartData('$userno');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
	array_push($output['taskplanchart'], $row);
 }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 新增 ，修改 目标
if ($a == 'setteamtarget') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $bno= checkInput($obj['bno']);		//新增时为空  编辑不能为空
  $admin = checkInput($obj['admin']);
  $userbno = checkInput($obj['userbno']);
  $year =  checkInput($obj['year']);			
  $saletotal = checkInput($obj['saletotal']); // 销售总额
  $salenum1 = checkInput($obj['salenum1']);
  $salenum2 = checkInput($obj['salenum2']);
  $salenum3 = checkInput($obj['salenum3']);
  $salenum4 = checkInput($obj['salenum4']);
  $salenum5 = checkInput($obj['salenum5']);
  $salenum6 = checkInput($obj['salenum6']);
  $salenum7 = checkInput($obj['salenum7']);
  $salenum8 = checkInput($obj['salenum8']);
  $salenum9 = checkInput($obj['salenum9']);
  $salenum10 = checkInput($obj['salenum10']);
  $salenum11 = checkInput($obj['salenum11']);
  $salenum12 = checkInput($obj['salenum12']);
  $custtotal = checkInput($obj['custtotal']); // 总客户数
  $custnum1 = checkInput($obj['custnum1']);
  $custnum2 = checkInput($obj['custnum2']);
  $custnum3 = checkInput($obj['custnum3']);
  $custnum4 = checkInput($obj['custnum4']);
  $custnum5 = checkInput($obj['custnum5']);
  $custnum6 = checkInput($obj['custnum6']);
  $custnum7 = checkInput($obj['custnum7']);
  $custnum8 = checkInput($obj['custnum8']);
  $custnum9 = checkInput($obj['custnum9']);
  $custnum10 = checkInput($obj['custnum10']);
  $custnum11 = checkInput($obj['custnum11']);
  $custnum12 = checkInput($obj['custnum12']);
  
 
  if (!$userbno) {
    exit(JSON(array('data'=>'', 'msg'=>'userbno不能为空', 'status'=>'1')));
  }
  
  $query = "SELECT id FROM `team_target` WHERE myear ='$year' AND billno !='$bno' AND userbno='$userbno'";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    exit(JSON(array('data'=>'', 'msg'=>'该年份已设置目标', 'status'=>'1')));
  }
  
  if ($bno) {
    $sql = "UPDATE team_target SET myear='$year',admin='$admin',saletotal='$saletotal',salenum1='$salenum1',
salenum2='$salenum2',salenum3='$salenum3',salenum4='$salenum4',salenum5='$salenum5',salenum6='$salenum6',
salenum7='$salenum7',salenum8='$salenum8',salenum9='$salenum9',salenum10='$salenum10',salenum11='$salenum11',salenum12='$salenum12',
custtotal='$custtotal',custnum1='$custnum1',custnum2='$custnum2',custnum3='$custnum3',custnum4='$custnum4',custnum5='$custnum5',
custnum6='$custnum6',custnum7='$custnum7',custnum8='$custnum8',custnum9='$custnum9',custnum10='$custnum10',custnum11='$custnum11',custnum12='$custnum12'
WHERE billno='$bno'";
  } else {
    $sql = "INSERT INTO team_target SET billno='$_billno',userbno='$userbno',admin='$admin',myear='$year',saletotal='$saletotal',salenum1='$salenum1',
salenum2='$salenum2',salenum3='$salenum3',salenum4='$salenum4',salenum5='$salenum5',salenum6='$salenum6',
salenum7='$salenum7',salenum8='$salenum8',salenum9='$salenum9',salenum10='$salenum10',salenum11='$salenum11',salenum12='$salenum12',
custtotal='$custtotal',custnum1='$custnum1',custnum2='$custnum2',custnum3='$custnum3',custnum4='$custnum4',custnum5='$custnum5',
custnum6='$custnum6',custnum7='$custnum7',custnum8='$custnum8',custnum9='$custnum9',custnum10='$custnum10',custnum11='$custnum11',custnum12='$custnum12'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $bno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $bno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}


// 查询目标数据 
if ($a == 'getteamtarget') {
  $userbno = checkInput($_GET['userbno']);
  $key = checkInput($_GET['key']);
  $output = array('list'=>array());

  if (!$userbno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }
  $query = "SELECT sql_calc_found_rows * FROM `team_target` WHERE userbno='$userbno'";
   if($key == '1'){
	  $query .= " AND YEAR(billdate)=YEAR(NOW()) ";
  }
  $query .= " ORDER BY id DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 删除目标数据 
if ($a == 'delteamtarget') {
  $billno = checkInput($_GET['billno']);
  
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'不能为空', 'status'=>'1')));
  }
   $sql = "DELETE FROM `team_target` WHERE billno='$billno'";
   if(!$mysqli->query($sql)){
	   exit(JSON(array('data'=>$items, 'msg'=>'删除失败!', 'status'=>'1')));
   }
    exit(JSON(array('data'=>$items, 'msg'=>'删除成功!', 'status'=>'0')));
 }  
 
 
// 查询目标统计数据 （销售额，新增客户数）
if($a == 'getteamsalestatis'){
	$userno = checkInput($_GET['userno']); 
	$output = array(
	    'salehart'=>array()
   );
	if (!$userno) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}
  
 // 销售，客户分析
  $query = "call getTargetSaleChartData('$userno');";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
	array_push($output['salehart'], $row);
 }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
	
}
// 查询档案管理
if ($a == 'getarchives') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `team_archives` WHERE admin='$admin'";
  if($keyword){
	 $query .= " AND username LIKE '%$keyword%'";
  }
  $query .= " ORDER BY id DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 新增档案，更新档案
if ($a == 'setarchives') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $admin = checkInput($obj['admin']);
  $billno = checkInput($obj['billno']);
  $username = checkInput($obj['username']);
  $ubillno = checkInput($obj['ubillno']);
  $telphone = checkInput($obj['telphone']);
  $sex = checkInput($obj['sex']);
  $job = checkInput($obj['job']);
  $jobnumber = checkInput($obj['jobnumber']);
  $workdate = checkInput($obj['workdate']);
  $birthday = checkInput($obj['birthday']);
  $nativeplace = checkInput($obj['nativeplace']);
  $startwork = checkInput($obj['startwork']);
  $workstatus = checkInput($obj['workstatus']);
  $phone = checkInput($obj['phone']);
  $record = checkInput($obj['record']);
  $image = checkInput($obj['image']);
  $image1 = checkInput($obj['image1']);
  $image2 = checkInput($obj['image2']);
  $image3 = checkInput($obj['image3']);
  
  if($billno){
	    $sql = "UPDATE team_archives SET admin='$admin',username='$username',telphone='$telphone',sex='$sex',
			ubillno='$ubillno',job='$job',jobnumber='$jobnumber',
			workdate='$workdate',birthday='$birthday',nativeplace='$nativeplace',
			startwork='$startwork',workstatus='$workstatus',phone='$phone',
			record='$record',image='$image',image1='$image1',
			image2='$image2',image3='$image3' WHERE billno='$billno'";
  } else {	  
	  // 查询是否已经添加到档案中了
	  $query ="SELECT id FROM `team_archives` WHERE ubillno='$ubillno' AND admin='$admin'";
	  $result = $mysqli->query($query) or die($mysqli->error);
	  if ($row = $result->fetch_assoc()) {
		  exit(JSON(array('data'=>'', 'msg'=>'该员工已经添加进档案!', 'status'=>'1')));
	  }	  
	   $sql = "INSERT INTO team_archives SET billno='$_billno',admin='$admin',username='$username',telphone='$telphone',sex='$sex',
			ubillno='$ubillno',job='$job',jobnumber='$jobnumber',
			workdate='$workdate',birthday='$birthday',nativeplace='$nativeplace',
			startwork='$startwork',workstatus='$workstatus',phone='$phone',
			record='$record',image='$image',image1='$image1',
			image2='$image2',image3='$image3'";
  }

  if (!$mysqli->query($sql)) {
    $msg = $billno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $billno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 查询工资表 数据 
if ($a == 'getsalarydata') {
  $admin = checkInput($_GET['admin']);
  $keyword = checkInput($_GET['keyword']);
  $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $query = "SELECT sql_calc_found_rows * FROM `team_payroll` WHERE admin='$admin'";
  if($keyword){
	 $query .= " AND username LIKE '%$keyword%'";
  }
  $query .= " ORDER BY billdate DESC".$paging;
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 人员薪酬资料增加
if ($a == 'setpaydataadd') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);
  $admin = checkInput($obj['admin']);
  $billno = checkInput($obj['billno']); // payroll的billno
  $username = checkInput($obj['username']);
  $ubillno = checkInput($obj['ubillno']);
  $ym = checkInput($obj['ym']);
  $salary = checkInput($obj['salary']);
  $insurance = checkInput($obj['insurance']);
  $fullattend = checkInput($obj['fullattend']);
  $commission = checkInput($obj['commission']);
  $other = checkInput($obj['other']);
  $workstatus = checkInput($obj['workstatus']);
  $remark = checkInput($obj['remark']);
  
  if ($billno) {
		$sql = "UPDATE `team_payroll` SET ym='$ym',ubillno='$ubillno',username='$username',salary='$salary',insurance='$insurance',
             fullattend='$fullattend',commission='$commission',other='$other',remark='$remark' where billno='$billno'";
	}else {
		$query = "SELECT id FROM `team_payroll` WHERE ubillno ='$ubillno' AND ym = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y%m')";
		$result = $mysqli->query($query);
		  $r = $result->fetch_assoc();
		  if ($r) {
			exit(JSON(array('data'=>'', 'msg'=>'当月已存在该数据', 'status'=>'1')));
		  }			
		$sql = "INSERT INTO `team_payroll` SET billno='$_billno',ym='$ym',admin='$admin',ubillno='$ubillno',username='$username',salary='$salary',insurance='$insurance',
             fullattend='$fullattend',commission='$commission',other='$other',workstatus='$workstatus',remark='$remark'";
	}

  if (!$mysqli->query($sql)) {
	$msg = $billno ? '修改失败' : '新增失败';
    exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $billno ? '修改成功' : '新增成功';
  exit(JSON(array('data'=>'', 'msg'=>$msg, 'status'=>'0')));
}

// 工资表信息删除
if ($a == 'delpayrolldata') {
  $billno = checkInput($_GET['billno']);
  
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
  }
   $sql = "DELETE FROM `team_payroll` WHERE billno='$billno'";
   if(!$mysqli->query($sql)){
	   exit(JSON(array('data'=>$items, 'msg'=>'删除失败!', 'status'=>'1')));
   }
    exit(JSON(array('data'=>$items, 'msg'=>'删除成功!', 'status'=>'0')));
  }  

// 删除档案
if ($a == 'delarchives') {
  $billno = checkInput($_GET['billno']);
  
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'billno不能为空', 'status'=>'1')));
  }
   $sql = "DELETE FROM `team_archives` WHERE billno='$billno'";
   if(!$mysqli->query($sql)){
	   exit(JSON(array('data'=>$items, 'msg'=>'删除失败!', 'status'=>'1')));
   }
    exit(JSON(array('data'=>$items, 'msg'=>'删除成功!', 'status'=>'0')));
  }  
  
// 档案添加 -- 选择员工
if ($a == 'selectteamstaff') {
  $admin = checkInput($_GET['admin']);
  $custname = checkInput($_GET['custname']);
  $output = array('list'=>array());
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }  

  $query = "SELECT SQL_CALC_FOUND_ROWS billno,username,userno,sex,image FROM view_groupuserlist where admin='$admin' ";
 
  if ($custname) {
    $query .=" and (username LIKE '%$custname%' OR userno LIKE '%$custname%')";
  }
  
	$query .= $paging;
	
  $result = $mysqli->query($query) or die($mysqli->error);
  
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 人员薪酬资料增加 - 选择员工
if ($a == 'selectarcstaff') {
  $admin = checkInput($_GET['admin']);
  $custname = checkInput($_GET['custname']);
  $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  
  $query = "SELECT SQL_CALC_FOUND_ROWS billno,ubillno,username,workstatus,image FROM team_archives WHERE admin ='$admin' ";
 
  if ($custname) {
    $query .=" username LIKE '%$custname%' OR telphone LIKE '%$custname'";
  }
  
  $query .= " ORDER BY billdate DESC".$paging;

  $result = $mysqli->query($query) or die($mysqli->error);
  
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

//薪酬统计报表
if ($a == 'salaryreport') {
	$admin = checkInput($_GET['admin']);
    $userno = checkInput($_GET['userno']);
	$ym	=checkInput($_GET['ym']);			//统计当年   Y，还是统计当月 M
  $output = array(
    'remtotal'=>'0',		//薪酬合计
    'membertotal'=>'0',		//成员总数
	'remaverage'=>'0',		//平均薪酬
	'remaddition'=>'0',		//加薪总计
	'amercetotal'=>'0',		//罚款总计
	'contribute'=>'0',		//贡献业绩
	'salaryanalysis'=>array()
  );  
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'不能为空', 'status'=>'1')));
  }
  if ($admin<>$userno){            //普通用户情况,同
	 //薪酬合计 
	$query = "SELECT IFNULL(SUM(salary+insurance+fullattend+commission+other),0.00) AS remtotal FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$remtotal = $row['remtotal'];
	$output['remtotal'] = $remtotal;
	//成员总数
	$query = "SELECT count(*) AS membertotal FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$membertotal =  $row['membertotal'];
	$output['membertotal'] = $membertotal;
	//平均薪酬  
	$output['remaverage'] = $membertotal ? $remtotal/$membertotal : '0';	
	//加薪总计    要改
	$query = "SELECT IFNULL(SUM(commission+other),0.00) AS remaddition FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$remaddition = $row['remaddition'];
	$output['remaddition'] = $remaddition;
	//罚款总计     要改
	if ($remaddition<0) {
		$output['amercetotal'] = -$remaddition;
	}
	//贡献业绩    要改
	$query = "SELECT IFNULL(SUM(commission),0.00) AS contribute FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$output['contribute'] = $row['contribute'];
	
	//拼接统计表查询
	$query = "SELECT IFNULL(SUM(salary+insurance+fullattend+commission+other),0) AS remtotal,COUNT(*) AS membertotal, ym FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym LIKE CONCAT(YEAR(NOW()),'%') GROUP BY ym ORDER BY ym";
  } else {   //////////////////////////////////////////////////管理员情况
	 //薪酬合计 
	$query = "SELECT IFNULL(SUM(salary+insurance+fullattend+commission+other),0.00) AS remtotal FROM `team_payroll` WHERE admin='$admin' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$remtotal = $row['remtotal'];
	$output['remtotal'] = $remtotal;
	//成员总数
	$query = "SELECT count(*) AS membertotal FROM `team_payroll` WHERE admin='$admin' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$membertotal =  $row['membertotal'];
	$output['membertotal'] = $membertotal;
	//平均薪酬  
	$output['remaverage'] = $membertotal ? $remtotal/$membertotal : '0';	
	//加薪总计    要改
	$query = "SELECT IFNULL(SUM(commission+other),0.00) AS remaddition FROM `team_payroll` WHERE admin='$admin' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$remaddition = $row['remaddition'];
	$output['remaddition'] = $remaddition;
	//罚款总计     要改
	if ($remaddition<0) {
		$output['amercetotal'] = -$remaddition;
	}
	//贡献业绩    要改
	$query = "SELECT IFNULL(SUM(commission),0.00) AS contribute FROM `team_payroll` WHERE admin='$admin' and ym='$ym'";
	$result = $mysqli->query($query) or die($mysqli->error);
	$row = $result->fetch_assoc();
	$output['contribute'] = $row['contribute'];
	
	//拼接统计表查询
	$query = "SELECT IFNULL(SUM(salary+insurance+fullattend+commission+other),0) AS remtotal,COUNT(*) AS membertotal, ym FROM `team_payroll` WHERE admin='$admin' and ubillno='$userno' and ym LIKE CONCAT(YEAR(NOW()),'%') GROUP BY ym ORDER BY ym";
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['salaryanalysis'], $row);
  }
  
  
	exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
  }  




// 团队排行榜
if ($a == 'toplist') {
  $admin = checkInput($_GET['admin']);
  $datepart = @$_GET['datepart'] ? checkInput($_GET['datepart']) : 'Y';			//排行时段   M 当月,Y 当年  LM 上月  LY 去年
  $key = checkInput($_GET['key']);  // 升降序
 $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  
  if ($datepart == 'M'){
	$query = "SELECT a.username,IFNULL(b.totalamount,0.00) AS totalamount FROM `view_groupuserlist` AS a LEFT JOIN (SELECT SUM(amount) AS totalamount,userno FROM `team_contract` WHERE admin='$admin'  AND DATE_FORMAT(contradate,'%Y%m')=DATE_FORMAT(CURDATE(),'%Y%m') GROUP BY userno ) AS b ON a.userno=b.userno  WHERE a.admin='$admin' OR a.userno='$admin' ORDER BY totalamount  $key LIMIT 10";  
  } else if ($datepart == 'LM'){
	$query = "SELECT a.username,IFNULL(b.totalamount,0.00) AS totalamount FROM `view_groupuserlist` AS a LEFT JOIN (SELECT SUM(amount) AS totalamount,userno FROM `team_contract` WHERE admin='$admin'  AND DATE_FORMAT(DATE_SUB(contradate, INTERVAL 1 MONTH),'%Y%m')=DATE_FORMAT(DATE_SUB(CURDATE(),INTERVAL 1 MONTH),'%Y%m') GROUP BY userno ) AS b ON a.userno=b.userno  WHERE a.admin='$admin' OR a.userno='$admin' ORDER BY totalamount $key LIMIT 10"; 	  
 } else if ($datepart == 'LY'){ // 去年
		$query = "SELECT a.username,IFNULL(b.totalamount,0.00) AS totalamount FROM `view_groupuserlist` AS a LEFT JOIN (SELECT SUM(amount) AS totalamount,userno FROM `team_contract` WHERE admin='$admin'  AND YEAR(contradate)=YEAR(DATE_SUB(NOW(),INTERVAL 1 YEAR)) GROUP BY userno ) AS b ON a.userno=b.userno  WHERE a.admin='$admin' OR a.userno='$admin' ORDER BY totalamount  $key  LIMIT 10"; 
 }  else { //默认年统计
  
	$query = "SELECT a.username,IFNULL(b.totalamount,0.00) AS totalamount FROM `view_groupuserlist` AS a LEFT JOIN (SELECT SUM(amount) AS totalamount,userno FROM `team_contract` WHERE admin='$admin'  AND YEAR(contradate)=YEAR(NOW()) GROUP BY userno ) AS b ON a.userno=b.userno  WHERE a.admin='$admin' OR a.userno='$admin' ORDER BY totalamount  $key  LIMIT 10"; 
  }
	  

  
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 选择职位
if ($a == 'getteamjob') {
  $admin = checkInput($_GET['admin']);
  $flag = checkInput($_GET['flag']);
  $output = array('list'=>array());

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
  if ($flag == '0') {
  	$query = "SELECT job FROM `team_job` WHERE astyle='1' OR (admin='$admin' AND astyle='0')  ORDER BY id".$paging; 
  } else {
    $query = "SELECT * FROM `team_job` WHERE admin='$admin' AND astyle='0'  ORDER BY id DESC".$paging; 
  }
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}

// 设置职位
if ($a == 'setteamjob') {
  $admin = checkInput($_GET['admin']);
  $job = checkInput($_GET['job']);
  $billno = checkInput($_GET['billno']);

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
	if (!$job) {
	    exit(JSON(array('data'=>'', 'msg'=>'job不能为空', 'status'=>'1')));
	  }

  $query = "SELECT id FROM `team_job` WHERE astyle='1' AND job='$job'"; 
	$result = $mysqli->query($query);
  $r = $result->fetch_assoc();
  if ($r) {
	  exit(JSON(array('data'=>'', 'msg'=>'职位名称不能与系统职位重复', 'status'=>'1')));
  }			

  $query = "SELECT id FROM `team_job` WHERE astyle='0' AND job='$job' AND admin='$admin'"; 
	$result = $mysqli->query($query);
  $r = $result->fetch_assoc();
  if ($r) {
	  exit(JSON(array('data'=>'', 'msg'=>'职位名称已存在', 'status'=>'1')));
  }			
  if ($billno) {
    $sql = "UPDATE `team_job` SET job='$job' WHERE admin='$admin' AND billno='$billno'"; 
  } else {
    $sql = "INSERT INTO `team_job` SET billno='$_billno',job='$job',admin='$admin'"; 
  }
  
  if (!$mysqli->query($sql)) {
  	$msg = $billno ? '修改失败' : '添加失败';
    exit(JSON(array('data'=>$output, 'msg'=>$msg, 'status'=>'1')));
  }
  $msg = $billno ? '修改成功' : '添加成功';
  exit(JSON(array('data'=>$output, 'msg'=>$msg, 'status'=>'0')));
}

// 删除职位
if ($a == 'delreteamjob') {
  $input = file_get_contents("php://input");
  $obj = json_decode($input, true);

  $admin = checkInput($obj['admin']);
  $mstr = '';
  
  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }

  foreach ($obj['selectedRowKeys'] as $k => $v) {
   if($k>0){
   $mstr .= ',';
   }  
   $mstr .= "'$v'";
  }
  $sql = "DELETE FROM `team_job` WHERE admin='$admin' AND billno IN ($mstr)";

  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'删除失败', 'status'=>'1');
  } else {
    $output = array('data'=>'', 'msg'=>'删除成功', 'status'=>'0');
  }
  exit(JSON($output));
}

// 组员查询 关于团队
if ($a == 'gettboutinfo') {
  $admin = checkInput($_GET['admin']);
  $userno = checkInput($_GET['userno']);
  $output = array();

  if (!$admin) {
    exit(JSON(array('data'=>'', 'msg'=>'admin不能为空', 'status'=>'1')));
  }
	$query = "SELECT A.username AS teamer,A.teamname,A.teamcreatedate,B.teamname AS divname,B.username AS hname,C.job,D.billdate FROM 
						(SELECT username,teamname,teamcreatedate FROM `team_salesman` WHERE userno='$admin') A,
						(SELECT teamname,username FROM `team_salesman` WHERE userno IN (SELECT division FROM `teams` WHERE userno='$userno' AND isteam='-3')) B,
						(SELECT job FROM `team_salesman` WHERE userno='$userno') C,
						(SELECT billdate FROM `teams` WHERE userno='$userno') D";
  $result = $mysqli->query($query) or die($mysqli->error);
  while ($row = $result->fetch_assoc()) {
    array_push($output, $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}


// 我的 - 货真真统计
if ($a == 'gethzzstats') {
  $output = array(
    'customer'=>array('qty1'=>'0', 'qty2'=>'0', 'qty3'=>'0', 'qty4'=>'0', 'qty5'=>'0', 'qty6'=>'0'),
    'amount'=>array('price1'=>'0.00', 'price2'=>'0.00', 'price3'=>'0.00')
  );
 
  $query = "select COUNT(case WHEN c.allow=2 THEN c.id END) qty1,COUNT(case WHEN c.allow=1 THEN c.id END) qty2,COUNT(case WHEN c.allow=-1 THEN c.id END) qty3,
           COUNT(case WHEN c.allow=1 AND TIMESTAMPDIFF(MONTH,c.regdate2,DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%S')) <=1 THEN c.id END)qty4,
           COUNT(case WHEN c.allow=1 AND flowuser=1 THEN c.id END) qty5,COUNT(case WHEN c.allow=1 AND flowuser=0 THEN c.id END) qty6 FROM team_salesman c";
 
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
    $output['customer']['qty1'] = $data['qty1']; // 待审用户
    $output['customer']['qty2'] = $data['qty2']; // 注册用户
    $output['customer']['qty3'] = $data['qty3']; // 拒绝用户
    $output['customer']['qty4'] = $data['qty4']; // 新增用户 (1个月内)
    $output['customer']['qty5'] = $data['qty5']; // 跟进用户
    $output['customer']['qty6'] = $data['qty6']; // 待跟进用户
  }

  // 跟进，未跟进用户
  // $query = "SELECT count(*) AS count FROM `team_visit` WHERE saleno='$userno'".$mstr;
  // $result = $mysqli->query($query) or die($mysqli->error);
  // if ($result->num_rows > 0) {
  //   $data = $result->fetch_assoc();
  //   $output['visit'] = $data['count'];
  // }

  // 交易的金额
  $query = "SELECT IFNULL(SUM(amount),'0.00') AS amount FROM `mall_orderhead` WHERE (billstate !=-1 OR billstate !=4) AND appver!=''";
  $result = $mysqli->query($query) or die($mysqli->error);
  $data = $result->fetch_assoc();
  if ($data) {
     $output['amount']['price1'] = $data['amount'];
  }

  // 目标
  // $query = "SELECT IF(SUM(qty*price),SUM(qty*price),'0.00') AS price,IF(SUM(qty),SUM(qty),'0') AS qty FROM team_stockin WHERE userno='$userno'".$mstr;
  // $result = $mysqli->query($query) or die($mysqli->error);
  // if ($result->num_rows > 0) {
  //   $data = $result->fetch_assoc();
  //   $output['waresin']['price'] = $data['price'];
  //   $output['waresin']['qty'] = $data['qty'];
  // }


  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
}
// hzz 获取待审用户，，新增用户   
if ($a == 'get_hzz_puser') { 
   $allow = checkInput($_GET['allow']); // -1审核拒绝，0临时状态， 1 通过,   2正在审核',
   $output = array('list'=>array(), 'total'=>0);
   if ($allow == -4) {
     $query = "SELECT sql_calc_found_rows * FROM `team_salesman` where allow='1' AND flowuser='0' ORDER BY regdate2 DESC".$paging;
   } else if ($allow == -3) {
     $query = "SELECT sql_calc_found_rows * FROM `team_salesman` where allow='1' AND flowuser='1' ORDER BY regdate2 DESC".$paging;
   } else if ($allow == -2) {
     $query = "SELECT sql_calc_found_rows * FROM `team_salesman` where allow='1' AND TIMESTAMPDIFF(MONTH,regdate2,DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%S')) <=1 ORDER BY regdate2 DESC".$paging;
   } else {
     $query = "SELECT sql_calc_found_rows * FROM `team_salesman` where allow='$allow' ORDER BY regdate2 DESC".$paging;
   }
    $result = $mysqli->query($query) or die($mysqli->error);
    $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

    $output['total'] = $totalResult->fetch_assoc()['total'];
    while ($row = $result->fetch_assoc()) {
      array_push($output['list'], $row);
    }
    exit(JSON(array('data'=>$output, 'msg'=>'ok', 'status'=>'0')));
  }
  // 设置跟进用户
  if ($a == 'setfllowup') {
  $flowuser = checkInput($_GET['flowuser']);
  $billno = checkInput($_GET['billno']);
  if (!$billno) {
    exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
  }
	 $sql = "UPDATE `team_salesman` SET flowuser='$flowuser' WHERE billno = '$billno' LIMIT 1";
  if (!$mysqli->query($sql)) {
    $output = array('data'=>'', 'msg'=>'设置失败', 'status'=>'1');
  } 
  exit(JSON(array('data'=>$output, 'msg'=>'设置成功', 'status'=>'0')));
}




