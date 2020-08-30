<?php  
  
function JSON($array) {
  return json_encode($array, JSON_UNESCAPED_UNICODE);
}

function gettostr($input) {
  return $input;
}

function sendphonesms($intext, $phoneno) {
  $timeap = time();
  $timea = 'yy123456_' . $timeap . '_topsky';

  $timea = md5($timea);
  $tel = $phoneno;
    
  $msg = iconv("utf-8", "GBK//ignore", $intext);  	
  $msg = urlencode($msg);  
  
  $ch = curl_init();
  $str = 'http://admin.sms9.net/houtai/sms.php?cpid=566&password=' . $timea .
          '&channelid=16251&tele=' . $tel . '&msg=' . $msg . '&timestamp=' . $timeap;
  curl_setopt($ch, CURLOPT_URL, $str);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec($ch);
  if (strpos($output, 'success') !== false) {
      return 1;
  }    
	return 0;
}

/************************************************************** 
 * 
 * 随机产生一个长度内的乱字符串 
 * @param length  乱串长充
 * @return string  返回随机生产的乱字符串
 * @access public 
 * 
 *************************************************************/  
function getRandChar($length){
  $str = null;
  $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz#!+-=";
  $max = strlen($strPol)-1;

  for($i=0;$i<$length;$i++){
    $str.=$strPol[rand(0,$max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
  }
  return $str;
}

/************************************************************** 
 * 
 * 设置图片的路径
 * @param qty 数量
 * @param serverurl 服务器地址
 * @return array 
 * 例：array(
 * 'urlPath'=>'https://json.kassor.cn/team/images/Img201710090910311.jpg'),
 * 'filePath'=>'./images/Img201710090910311.jpg')
 * )
 *************************************************************/  
function setImagePath($qty=3, $serverurl) {
  $items = array();
  for ($i=0; $i < $qty; $i++) { 
    $fileName = date('YmdHis',time()).'_'.mt_rand(1000, 9999).'.jpg';
    array_push($items, array('urlPath'=>$serverurl.'/signature/'.$fileName, 'filePath'=>'../assets/signature/'.$fileName));
  }
  return $items; 
}

/************************************************************** 
 * 
 * sql图片字段
 * @param field  字段名
 * @param value  图片值，通过该值判断是否保存图片
 * @param path   图片的本地路径和链接路径
 * @param isThumb 是否裁剪图片
 * @return string
 *************************************************************/  
function toSqlImageField($field, $value, $path, $isThumb=false) {
  $result;
  if ($value == '') { // 空字符
    $result = "$field=''"; 
  } else if (substr($value, 0, 4) == 'http') { // 链接
    $result = "$field='$value'"; 
  } else if (substr($value, 0, 5) == 'data:') { // base64
    if ($isThumb) { // 裁剪
      if (mkThumbnail($path['sourcePath'], 124, 124, $path['filePath']))
        $result = "$field='{$path['urlPath']}'";
    } else {
      $base64_image = explode(',', $value)[1];
      if (file_put_contents($path['filePath'], base64_decode($base64_image)))
        $result = "$field='{$path['urlPath']}'";
    }
  }
  return $result;
}

// 图片验证码
class VerifyImage {
  private $code;
  private $codeLength = 4;

  // 设置session
  private function setSession($value) {
    session_start();
    $_SESSION['imageCaptcha'] = $value;
  }

  // 生成随机字串
  private function createCode() {
    $str = '';
    for($i=0; $i < $this->codeLength; $i++) {
      $str .= dechex(mt_rand(0, 15));
    }
    $this->code = $str;
    $this->setSession($str);
  }

  // 进行检查
  public function check($value) {
    session_start();
    if (isset($_SESSION['imageCaptcha']) && strcasecmp($_SESSION['imageCaptcha'], $value) == 0) {
      // $this->setSession(null);
      $_SESSION['imageCaptcha'] = '';
      return true;
    } else {
      $this->setSession(null);
      return false;
    }
  }

  // 生成图片
  public function createImage($_wid=80, $_hig=30, $flag=false) {
    $this->createCode();

    $_img = imagecreatetruecolor($_wid, $_hig);
    // 白色
    $_white = imagecolorallocate($_img, 255, 255, 255);

    // 填充
    imagefill($_img, 0, 0, $_white);

    if ($flag) {
      // 黑色边框
      $_black = imagecolorallocate($_img, 0, 0, 0);
      imagerectangle($_img, 0, 0, $_wid-1, $_hig-1, $_black);
    }
    // 随机画出6个线条
    for ($i=0; $i < 6; $i++){
      $_rnd_color = imagecolorallocate($_img, mt_rand(0, 255),mt_rand(0, 255),mt_rand(0, 255));
      imageline($_img ,mt_rand(0, $_wid),mt_rand(0, $_hig), mt_rand(0, $_wid),mt_rand(0, $_hig), $_rnd_color);
    }

    // 随机打雪花
    for ($i=0; $i< 100; $i++) {
      $_rnd_color = imagecolorallocate($_img, mt_rand(200, 255),mt_rand(200, 255),mt_rand(200, 255));
      imagestring($_img, 1, mt_rand(1, $_wid), mt_rand(1, $_hig), '*', $_rnd_color);
    }

    // 输入验证码
    for ($i=0; $i<strlen($this->code); $i++){
      $_rnd_color = imagecolorallocate($_img, mt_rand(0, 100),mt_rand(0, 150),mt_rand(0, 200));
      imagestring($_img, 5,$i*$_wid/$this->codeLength+mt_rand(1, 10), mt_rand(1, $_hig/2), $this->code[$i],$_rnd_color);
    }

    // 输出图像
    header('Content-Type:image/png');
    imagepng($_img);

    // 销毁
    imagedestroy($_img);
  }
}

// 输入过滤
function checkInput($value) {
  global $mysqli;
  if ($mysqli) {
    $value = $mysqli->real_escape_string($value);
  } else {
    $value = mysql_real_escape_string($value);
  }
  return $value;
}

// 对md5密码位置进行调换
function setstrmd5($str){
  $mk01 = substr($str,0,4);
  $mk02 = substr($str,4,6);
  $mk03 = substr($str,10,19);
  $mk04 = substr($str,29,32);
  return $mk01.$mk03.$mk02.$mk04;
}

// 还原正确的md5密码
function getstrmd5($str){
  $mk01 = substr($str,0,4);
  $mk02 = substr($str,4,19);
  $mk03 = substr($str,23,6);
  $mk04 = substr($str,29,32);
  return $mk01.$mk03.$mk02.$mk04;
}

function noiseUserName($str = '') {
  $str = trim($str, ' ');
  $len = mb_strlen($str);
  if ($len == 1) {
    return $str.'**';
  } else if ($len == 2) {
    return mb_substr($str, 0, 1).'**';
  } else {
    return mb_substr($str, 0, 1).'**'.mb_substr($str, -1, 1);
  }
}

function clearStoredResults(){
    global $mysqli;
    do {
         if ($res = $mysqli->store_result()) {
           $res->free();
         }
        } while ($mysqli->more_results() && $mysqli->next_result());        
}

/**************************************************************
 *
 * 统一下单处理
 * @param array
 * @return array
 *
 *************************************************************/
function _unifiedOrder($orderInfo) {
  include_once __DIR__.'/../wx/WxPayService.php';
  $wxPayService = new WxPayService();

  $platform = $orderInfo['platform'];
  if ($platform === 'mini') {
    $result = $wxPayService->pay_mini($orderInfo);
  } else if ($platform === 'ios' || $platform === 'android') {
    $result = $wxPayService->pay_app($orderInfo);
  } else {
    return array('status'=>'1', 'msg'=>'platform不匹配');
  }

  if ($result['status'] == '0') {
    return array('status'=>'0', 'msg'=>'ok', 'data'=>$result['data']);
  } else {
    return array('status'=>'1', 'msg'=>$result['msg']);
  }
}

// 云打印 一些方法
function getStringLen($str) {
  $key = 0;
  $arr1 = preg_split('/(?<!^)(?!$)/u', $str);

  $num = count($arr1);
  for ($i=0; $i<$num; $i++){
    if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $arr1[$i]) > 0) {
      $key += 2;
    } else {
      $key += 1;
    }
  }
  return $key;
}

// 云打印，拼接 -- 进出 商品列表
// 序号，名称
function printInOneLine($foodname, $foodprice)
{

    $mstr = $foodname;
    $lenprice = mb_strlen($foodprice, 'utf8');

     $mstr .= '　';

    $mstr .= $foodprice . '<BR>';

    return $mstr;
}

// 价格，数量
function printInOneLine2($foodnum, $foodamount)
{

    $mstr = '';
    $lennum = mb_strlen($foodnum, 'utf8');
    $lenamount = mb_strlen($foodamount, 'utf8');

    // 单价前面的空格
    $len01 = 25 - $lennum;
    for ($i = 0; $i < $len01; $i++) {

        $mstr .= ' ';
    }

    $mstr .= $foodnum;
    // 单价与数量之间的空格长度

    $mstr .= '　'.' ';             // 全

    $mstr .= $foodamount . '<BR>';

    return $mstr;
}

/**
 * 云打印机状态
 * $output['data'] 为原始返回的信息
 */
function printerStatus($sn) {
  include_once __DIR__.'/../utils/feie.php';
  $result = queryPrinterStatus($sn);
  $retjson = json_decode($result, true);
  $output = array();
  if ($retjson['ret'] == 0) {
    $statusMsg = $retjson['data'];
    if (strpos($statusMsg, '正常') !== false) {
      $output = array('status'=>'0', 'msg'=>$statusMsg, 'data'=>$retjson);
    } else {
      $output = array('status'=>'1', 'msg'=>$statusMsg, 'data'=>$retjson);
    }
  } else {
    $output = array('status'=>'1', 'msg'=>$retjson['msg'], 'data'=>$retjson);
  }
  return $output;
}

/**
 * 云打印机打印订单       mb_substr($item['id'],0,5),
 * $output['data'] 为原始返回的信息
 */

function printOrder($sn, $data = array(), $times = 1, $flag = 0, $number = 1) { // $number 次数
  include_once __DIR__.'/../utils/feie.php';

  $printInfo = '';
  for ($i=0; $i<$number; $i++) {

  $tolnum = 0;
  $tolmoney = 0;
  $printInfo .= $flag == 0 ? '<CB>进货单</CB><BR>' : '<CB>出货单</CB><BR>';
  $printInfo .= $flag == 0 ? '供应商：'.$data['customername'].'<BR>' : '客户名称' .$data['customername'].'<BR>';
  $printInfo .= $data['contractno'] ? '合同编号：'.$data['contractno'].'<BR>' : '';
  $printInfo .= '地  址：'.$data['customeraddress'].'<BR>';
  $printInfo .= '操作员：'.$data['username'].'  日期：'.$data['date'].'<BR>';
  $printInfo .= '--------------------------------<BR>'; 
  $printInfo .= '序号　　名称　　     单价   数量<BR>';
  $printInfo .= '--------------------------------<BR>';
  foreach ($data['list'] as $item) {
    $tolnum = $tolnum + $item['qty'];
    $tolmoney = $tolmoney + $item['amount'];
    $printInfo .= printInOneLine(
      $item['id'],
      $item['warename']
    );
    $printInfo .= printInOneLine2(
      $item['price'],
      $item['qty']
    );
  }
  $printInfo .= '--------------------------------<BR>';
  $printInfo .= '<L>总数量：'.$tolnum.'</L><BR><BR>';
  $printInfo .= '<L>总金额：'.$tolmoney.'元</L><BR>';
  $printInfo .= '--------------------------------<BR>';
  $printInfo .= $flag == 0 ? '收货单位：'.$data['company'].'<BR>' : '供应商名：'.$data['company'].'<BR>';
  $printInfo .= $flag == 0 ? '联系电话：'.$data['userno'].'<BR>' : '联系电话：'.$data['userno'].'<BR>';
  $printInfo .= $flag == 0 ? '联系地址：'.$data['companyaddres'].'<BR><BR>' : '联系地址：'.$data['companyaddres'].'<BR><BR>';
  $printInfo .= $flag == 0 ? '收货人签名：<BR>' : '出货人签名：<BR>';
  $printInfo .= '--------------------------------<BR>';
  if ($flag != 0) { // 出货单
  $printInfo .= '收货单位：'.$data['customername'].'<BR><BR>';
  $printInfo .= '收货时间：<BR>';
  $printInfo .= '收货人签名：<BR>';
  $printInfo .= '--------------------------------<BR><BR>';
  }
  $printInfo .= '<BR><BR><BR>';
  if ($number>1){ 
    $printInfo .= '<CUT>';
  }
  }  
  $result = wp_print($sn, $printInfo, $times);
  $retjson = json_decode($result, true);
  $output = array();
  if ($retjson['ret'] == 0) {
    $output = array('status'=>'0', 'msg'=>$retjson['msg'], 'data'=>$retjson);
  } else {
    $output = array('status'=>'1', 'msg'=>$retjson['msg'], 'data'=>$retjson);
  }
  return $output;
}


// 云打印   hzz 商城 平板 -- 扫描 -- 打印商品
function printHzzOrder($sn, $data = array(), $times = 1, $flag = 0, $number = 1) { // $number 次数
  include_once __DIR__.'/../utils/feie.php';

  $printInfo = '';

  $tolnum = 0;
  $tolmoney = 0;
  $printInfo .= '<CB>货真真商城订单</CB><BR>';
  $printInfo .= '操作员：'.$data['username'].'  日期：'.$data['date'].'<BR>';
  $printInfo .= '--------------------------------<BR>'; 
  $printInfo .= '序号　　名称　　     单价   数量<BR>';
  $printInfo .= '--------------------------------<BR>';
  foreach ($data['list'] as $item) {
    $tolnum = $tolnum + $item['qty'];
    $tolmoney = $tolmoney + $item['amount'];
    $printInfo .= printInOneLine(
      $item['id'],
      $item['warename']
    );
    $printInfo .= printInOneLine2(
      $item['price'],
      $item['qty']
    );
  }
  $printInfo .= '--------------------------------<BR>';
  $printInfo .= '<L>总数量：'.$tolnum.'</L><BR><BR>';
  $printInfo .= '<L>总金额：'.$tolmoney.'元</L><BR>';
  $printInfo .= '<BR><BR><BR>';
  $printInfo .= '<CUT>';

  $result = wp_print($sn, $printInfo, $times);
  $retjson = json_decode($result, true);
  $output = array();
  if ($retjson['ret'] == 0) {
    $output = array('status'=>'0', 'msg'=>$retjson['msg'], 'data'=>$retjson);
  } else {
    $output = array('status'=>'1', 'msg'=>$retjson['msg'], 'data'=>$retjson);
  }
  return $output;
}



