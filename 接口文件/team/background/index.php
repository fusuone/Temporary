<?php
// 入口
error_reporting(E_ERROR);
ini_set('date.timezone', 'Asia/Shanghai');
header('Content-type:text/html; charset=utf-8');

// 跨域访问处理
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allow_origin = array( // 白名单列表
  'http://localhost:8001',
  'http://localhost:8002',
  'http://192.168.3.71:8001',
  'http://192.168.3.36:8001',
  'http://192.168.3.51:8001',
  'http://192.168.3.84:8001',
  'http://192.168.43.194:8001',
  'http://192.168.3.84:8001',
  'http://webteamadmin',
  'https://svr.kassor.cn',
  'http://teams.kassor.cn',
  'https://wolf.kassor.cn',
  'http://user.wolfteams.cn',
  'http://127.0.0.1:41972'

);
// if ($origin) { // 存在跨域
//   if (in_array($origin, $allow_origin)) {
//     header('Access-Control-Allow-Credentials: true');
//     header('Access-Control-Allow-Origin:'.$origin);
//     header('Access-Control-Allow-Methods:POST');
//     header('Access-Control-Allow-Headers:x-requested-with,content-type');
//   } else {
//     exit(json_encode(array('msg'=>'cross domain', 'status'=>'1')));
//   }
// }

// 测试
  header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Origin:'.$origin);
    header('Access-Control-Allow-Methods:POST');
    header('Access-Control-Allow-Headers:x-requested-with,content-type');

require_once __DIR__.'/../utils/config.php';
require_once __DIR__.'/../utils/function.php';
require_once __DIR__.'/../utils/thumb.php';
require_once __DIR__.'/../utils/feie.php';

// 连接数据库
$serverConfig = new Config;
$mysqli = $serverConfig->db();
if (!$mysqli) {
  exit(JSON(array('data'=>'', 'msg'=>'error connecting', 'status'=>'1')));
}

$a = @$_GET['a'] ? checkInput($_GET['a']) : '';
$m = @$_GET['m'] ? checkInput($_GET['m']) : ''; // 模块
$_p = @$_GET['p'] ? checkInput($_GET['p']) : ''; // 平台 ios、android等
$_v = @$_GET['v'] ? checkInput($_GET['v']) : ''; // 版本号
$_macid = @$_GET['macid'] ? checkInput($_GET['macid']) : '';	// 机器码
$_billno = substr(date('ymdHis'), 1, 11).mt_rand(100, 999); // 全局单号

// 分页
$ispaging = @$_GET['ispaging'] ? checkInput($_GET['ispaging']) : '1'; // 1分页(默认) 2不分页
$paging = '';
if ($ispaging == '1') {
	$page = empty($_GET['page']) ? 1 : checkInput($_GET['page']); // 第几页(默认第1页)
	$pagesize = empty($_GET['pagesize']) ? 15 : checkInput($_GET['pagesize']); // 每页显示的条数(默认15条)
	$paging = ' LIMIT '.($page - 1) * $pagesize.', '.$pagesize.' ';
}

// 安卓、ios、网页后台、网页app
$platformArray = array('ios','android','mini','web_bg','web_app');

if (empty($a)) {
  exit(JSON(array('data'=>'', 'msg'=>'a参数不能为空', 'status'=>'1')));
}

if ($m == 'main') {
  require_once 'main.php';
} else if ($m == 'user') {
  require_once 'user.php';
} else if ($m == 'cloth') {
  require_once 'cloth.php';
} else if ($m == 'group'){
	require_once 'group.php';
} else if ($m == 'group2'){
	require_once 'group2.php';
} else if ($m == 'upload'){
	require_once 'upload.php';
} else if ($m == 'mall'){
	require_once 'mall.php';
} else if ($m=='link'){
	require_once 'link.php';
} else if ($m == 'hzz'){		//货真真商城业务单元
	require_once 'hzz.php';
} else if ($m == 'message'){
	require_once 'message.php';
} else if ($m == 'print'){
	require_once 'print.php';
} else if ($m == 'ai'){
	// require_once 'ai.php';
  require_once 'ai.php';
} else if ($m== 'manage'){			//壹软网络内部管理专页
  require_once 'manage.php';
}	
else {
  exit(JSON(array('data'=>'', 'msg'=>'m参数不匹配', 'status'=>'1')));
}