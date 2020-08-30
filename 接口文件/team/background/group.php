<?php

//团队管理
//更改说明:
//  旧版本            新版本    
//getteamlist  ==>  getgroupuserlist
//getmygroupinfo ==>getgroupuserinfo
//addteamuser  ==>  addgroupuser
//editteamuser ==>  editgroupuser


 //取得团队的人员列表
 if ($a == 'getgroupuserlist') {
	 $uid= $_GET['uid'];
	 $output = array('list'=>array(), 'total'=>0);
     if (($uid == '') ) {
         exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
    }
	
 	  $query = "SELECT a.*,b.username,b.image,b.tel,b.phone,b.sex,b.teamname,b.job,b.jobnumber,b.mail,b.age FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` where admin='$uid' OR division='$uid'  OR admin in (select admin from teams where userno='$uid' and isteam=-2) order by a.isteam desc,a.id desc";
	  
	  
	  $result = $mysqli->query($query) or die($mysqli->error);
	  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

	  $output['total'] = $totalResult->fetch_assoc()['total'];
	  while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	  }
	  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
 }
 
 
 
 //取得团队用户信息列表（主要用于手机用户界面选择用户信息用）
 if ($a == 'getteamselectlist') {
	 $admin= $_GET['admin'];
	 $username= $_GET['username'];
	 $billno=$_GET['billno'];
	 $output = array('list'=>array(), 'total'=>0);
	 if (!$admin) {
	    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
	 }
	  $query = "SELECT a.`admin`,a.`division`,b.id,b.billno,b.userno,b.image,b.username,b.image FROM teams AS a LEFT JOIN `team_salesman` AS b ON a.`userno`=b.`userno` where (admin='$admin') OR (division='$admin')";
	if ($username) {
		$query .=" AND b.username LIKE '%$username%'";
	}
	$query .= " ORDER BY a.isteam DESC,a.id DESC";
  $result = $mysqli->query($query) or die($mysqli->error);
  $totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);

  $output['total'] = $totalResult->fetch_assoc()['total'];
  while ($row = $result->fetch_assoc()) {
    array_push($output['list'], $row);
  }
  exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));	  
 }


 //取得小分组的信息(我的团队)
 if ($a == 'getgroupuserinfo') {
	 $uid= $_GET['uid'];
	 $isadmin=$_GET['isadmin'];
	 $output = array('list'=>array(), 'total'=>0);
	 
     if (($uid == '') ) {
        exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
    }
	
	if($isadmin==0){		//队长 队员的情况	
		$query = "SELECT * from teams where userno='$uid'";
		 $result = $mysqli->query($query) or die($mysqli->error);
		if (mysql_numrows($result) > 0){
			$row = $result->fetch_assoc();
			$viewname=$row['viewname'];
			$isteam=$row['isteam'];
			$astatus=$row['astatus'];
			$admin=$row['admin'];
					
			$query="select * from team_salesman where userno='$admin'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if (mysqli_num_rows($result) > 0){
				$row = $result->fetch_assoc();
				$usercount = 0;	  //队长，队员信息不显示
				$maxcount =  0;   //队长，队员信息不显示
				$expiredate = ""; //队长，队员信息不显示
				$teamname = $row['username'];
				$outdata=array('viewname'=>$viewname,'teamname'=>$teamname,'isteam'=>$isteam,'astatus'=>$astatus,'usercount'=>$usercount,'maxcount'=>$maxcount,'expiredate'=>$expiredate);
				exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
			} else {
				exit(JSON(array('data'=>'', 'msg'=>'获取失败', 'status'=>'1')));				
			}
		} else {
				exit(JSON(array('data'=>'', 'msg'=>'获取失败', 'status'=>'1')));	
		}
	} else {	//管理员的情况
		$query = "SELECT (SELECT COUNT(*) FROM `teams` WHERE admin='$uid' OR admin IN (SELECT userno FROM `teams` WHERE admin='$uid' AND isteam=1)) AS allcount,team,teamname,team AS maxcount,teamdate AS expiredate FROM team_salesman WHERE userno='$uid'";
		$result = $mysqli->query($query) or die($mysqli->error);
		//echo $query;
		if (mysqli_num_rows($result) > 0){
			$row = $result->fetch_assoc();
			$usercount = $row['allcount'];
			$maxcount =  $row['maxcount'];
			$expiredate = $row['expiredate'];
			$teamname = $row['teamname'];
			$outdata=array('viewname'=>'','teamname'=>$teamname,'isteam'=>'-1','astatus'=>'1','usercount'=>$usercount,'maxcount'=>$maxcount,'expiredate'=>$expiredate);
		
			exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
		} else {
			exit(JSON(array('data'=>'', 'msg'=>'获取失败', 'status'=>'1')));		
		}		
	}
 } 
 
 

 //添加队员
 if ($a == 'addgroupuser') {
	 $admin = $_GET['admin'];
	 $isteam = $_GET['isteam'];
	 $username = $_GET['username'];
	 $userno =$_GET['phone'];
	 $msg = $_GET['msg'];
	 
    if (($userno == '') ) {
         exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
	}
    if (($admin == '') ) {
         exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
    }	
	
	$query = "select * from team_salesman where userno='$userno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if (mysqli_num_rows($result) <= 0){
         exit(JSON(array('data'=>'', 'msg'=>'该用户还没有注册！', 'status'=>'1')));
	}
	
 	$query = "SELECT * FROM teams where userno='$userno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if (mysqli_num_rows($result) > 0){
		exit(JSON(array('data'=>'', 'msg'=>'该队员已服务其它工作组！', 'status'=>'1')));
	}
	
	$sql = "insert into teams set billno='$billno',isteam=$isteam, admin='$admin',userno='$userno',viewname='$username'";
	  if (!$mysqli->query($sql)) {
		exit(JSON(array('data'=>'', 'msg'=>'添加队员错误', 'status'=>'1')));
	  }

	$sql = "INSERT INTO `team_message` SET billno='$billno', creuser='用户认证',creuserno='$admin',admin='$admin', title='加入到团队',message='$msg 请求你加入到他的团队！',username='$username',userno='$userno',`type`=3";
	//添加到消息队列
	$mysqli->query($sql);
	
	exit(JSON(array('data'=>'', 'msg'=>'队员添加成功', 'status'=>'0')));
 } 
 
 

 //编辑队员
 if ($a == 'editgroupuser') {
	 $oldUserno = $_GET['oldUserno'];
	 $newUserno = $_GET['newUserno'];  //组长号码，队员号码
	 $madmin = $_GET['admin'];        //管理员号码，组长号码
	 $mflat = $_GET['mflat'];         //mflat=0 管理员编辑队长，1 管理员编辑队员，2 队长编辑队员
	 $teamname = $_GET['teamname'];   //小组名称
	 $adminname = $_GET['adminname'];  //发送方(管理员名称，组长名称)
	 $bino = $_GET['billno'];
	 $iskey = $_GET['iskey']; 
	 $username = ""; //收信人名称
	 $msg="";
	 $cname="";
	 $mess="";
	 
    if (($oldUserno == '') ) {
        $output = array('data'=>'', 'msg'=>'oldUserno参数不能为空','success'=>'0');
        exit(JSON($output));
    }
    if (($newUserno == '') ) {
        $output = array('data'=>'', 'msg'=>'newUserno参数不能为空','success'=>'0');
        exit(JSON($output));
	}		
	
	$query = "select username from team_salesman where userno='$newUserno'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if (mysqli_num_rows($result) > 0){
		while ($row = $row = $result->fetch_assoc()) {	
		$username = $row['username'];	
		}		
	  }else{
		exit(JSON(array('data'=>'', 'msg'=>'该用户还没有注册！', 'status'=>'1')));
	  }			
	
 	$query = "SELECT * FROM teams where userno='$newUserno' AND (astatus='1' OR astatus='0')";
	$result = $mysqli->query($query) or die($mysqli->error);
	if (mysqli_num_rows($result) > 0){
      
		exit(JSON(array('data'=>'', 'msg'=>'该队员已服务其它工作组！', 'status'=>'1')));
	}
			
   if($mflat=='0'){  
        $mess="你被管理员:".$adminname."踢出了团队！";	
	    $cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您来当".$teamname."小组的组长";
	}else if($mflat=='1'){
		$mess="你被管理员:".$adminname."踢出了团队！";	
	    $cname=$adminname."(管理员)";
	    $msg=$adminname."诚邀您加入".$teamname."小组,成为我们的一员!";
	}else if($mflat=='2'){   
	    $mess="你被组长:".$adminname."踢出了团队！";	
	    $cname=$adminname."(组长)";
	    $msg=$adminname."诚邀您加入".$teamname."小组,成为我们的一员!";
	}
	
	$sql="UPDATE `team_salesman` SET teamname='$teamname' WHERE userno='$newUserno'";
	$mysqli->query($sql);	
	
	$sql = "UPDATE `teams` SET division='$newUserno' WHERE division='$oldUserno'";
	$mysqli->query($sql);	
		
	$sql = "UPDATE `teams` SET userno='$newUserno',isteam='$iskey',astatus='0' WHERE userno='$oldUserno'";
	if (!$mysqli->query($sql))
	{       
		exit(JSON(array('data'=>'', 'msg'=>'失败', 'status'=>'1')));
	}
	
	$sql = "INSERT INTO `team_message` SET billno='$bino', creuser='$cname',creuserno='$madmin',admin='$madmin', title='加入到团队',message='$msg',username='$username',userno='$newUserno',`type`=3";	
	//添加到消息队列
    $mysqli->query($sql);
	
	//通知公告，oldUserno被踢出团	
	$sql="INSERT INTO `team_message` SET billno='$billno',creuser='系统发送',creuserno='$madmin',
		  title='退出团队提示',message='$mess',username='$username',userno='$oldUserno',`type`='1'";
	$mysqli->query($sql);
	exit(JSON(array('data'=>'', 'msg'=>'更改成功', 'status'=>'0')));
 }   
 
 
 
 
 //取得仓库部门列表
if ($a == 'getstockdeportlist') {
	$admin = checkInput($_GET['admin']);	
	$output = array('list'=>array(), 'total'=>0);
	if (!$admin) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	
	
	$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT teamname,isteam from `view_groupuserlist`  WHERE admin='$admin' AND isteam >2";
	$result = $mysqli->query($query) or die($mysqli->error);
	$totalResult = $mysqli->query("SELECT found_rows() as total") or die($mysqli->error);
	$output['total'] = $totalResult->fetch_assoc()['total'];
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功','total'=>"$total", 'status'=>'0')));
	
 } 

// 团队 - 通讯录
if ($a == 'get_team_card') {
	$admin = checkInput($_GET['admin']);	
	$keyword = checkInput($_GET['keyword']);	
	$industry = checkInput($_GET['industry']);
	$output = array('list'=>array());
	if (!$admin) {
		exit(JSON(array('data'=>'', 'msg'=>'参数错误', 'status'=>'1')));
	}	

	// 暂时 写法
	switch ($industry) {
		case '网络/IT':
			$key = 1;
		break;
			case '机械/仪表':
		  $key = 2;
		break;
			case '服装/食品':
		  $key = 3;
		break;
			case '健康/医疗':
		  $key = 4;
		break;
			case '地产/建筑':
		  $key = 5;
		break;
			case '美容/美发':
		  $key = 6;
		break;
			case '质控/安防':
		  $key = 7;
		break;
			case '物流/仓存':
		  $key = 8;
		break;
		
		default:
			break;
	}
	
	$query = "SELECT image,username AS name,userno,tel,phone,job,mail,company,companyaddress,qq,fax from `team_salesman`  WHERE  userno IN (SELECT userno FROM `teams` WHERE admin='$admin')";
	if ($keyword) {
		$query .=" AND (username LIKE '%$keyword%' OR company LIKE '%$keyword%' OR phone LIKE '%$keyword%')";
	}
	if ($industry) {
		$query .=" AND `industry`='$key'";
	}
	$result = $mysqli->query($query) or die($mysqli->error);
	while ($row = $result->fetch_assoc()) {
		array_push($output['list'], $row);
	}
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 

// 团队 - 通讯录 - 行业数据
 if ($a == 'get_team_card_inust') {
	$output = array('list'=>array());
	$query = "SELECT * FROM `mall_industry`";
	 $result = $mysqli->query($query) or die($mysqli->error);
  $serverAssetsUrl = $serverConfig->getAssetsUrl();
  while ($row = $result->fetch_assoc()) {
    $row['icon'] = $serverAssetsUrl.'/icon/industry/'.$row['icon'];
    array_push($output['list'], $row);
  }
    exit(JSON(array('data'=>$output, 'msg'=>'获取成功', 'status'=>'0')));
} 




