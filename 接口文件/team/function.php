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
function setImagePath($qty=3, $serverurl, $dir='images') {
  $items = array();
  for ($i=0; $i < $qty; $i++) { 
    $file = 'Img'.date('YmdHis',time()).'_'.mt_rand(1000, 9999).'.jpg';
    array_push($items, array('urlPath'=>$serverurl.'/'.$dir.'/'.$file, 'filePath'=>'./'.$dir.'/'.$file));
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
      $this->setSession(null);
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

// 请求
function request($url, $type='GET', $data='') {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	if (isset($data)) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	if (curl_errno($ch)) {
    curl_close($ch);
		return 'Errno'.curl_error($curl);
	} else {
    curl_close($ch);
		return $tmpInfo;
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
        $mk01 =  substr($str,0,4);
        $mk02=substr($str,4,6);
        $mk03=substr($str,10,19);
        $mk04=substr($str,29,32);
        return $mk01.$mk03.$mk02.$mk04;
    }

    // 还原正确的md5密码
 function getstrmd5($str){
        $mk01 =  substr($str,0,4);
        $mk02=substr($str,4,19);
        $mk03=substr($str,23,6);
        $mk04=substr($str,29,32);
        return $mk01.$mk03.$mk02.$mk04;
    }



