<?php  

// 获取图片验证码
if ($a == 'get_image_captcha') {
  $verifyImage = new VerifyImage();;
  $data = $verifyImage->createImage();
  exit(JSON(array('data'=>$data, 'msg'=>'获取成功', 'status'=>'0')));
}

// 检查图片验证码(只用于测试)
if ($a == 'check_image_captcha') {
  $image_code = $_GET['image_code'];
  $image_token = $_GET['image_token'];
  
  if (!$image_code || !$image_token) {
    exit(JSON(array('data'=>'', 'msg'=>'参数不能为空', 'status'=>'1')));
  }

  $verifyImage = new VerifyImage();;
  $bool = $verifyImage->checkToken($image_code, $image_token);
  if ($bool) {
    exit(JSON(array('data'=>'', 'msg'=>'正确', 'status'=>'0')));
  } else {
    exit(JSON(array('data'=>'', 'msg'=>'错误', 'status'=>'1')));
  }
}

// 图片验证码
class VerifyImage {
  private $code; // 验证码
  private $codeLength = 4; // 验证码长度
  private $keyword = 'qzooe'; // 加密关键字
  private $token; // code + keyword 加密生成的

  // 设置 Token
  private function setToken($value) {
    $this->token = sha1($this->keyword.$value);
  }

  // 生成随机字串
  private function createCode() {
    $str = '';
    for($i=0; $i < $this->codeLength; $i++) {
      $str .= dechex(mt_rand(0, 15));
    }
    $this->code = $str;
    $this->setToken($str);
  }

  // 进行检查
  public function checkToken($image_code, $image_token) {
    $this->setToken($image_code);
    return $this->token === $image_token;
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
    
    // 直接输出图像
    // header('Content-Type:image/png');
    // imagepng($_img);
    // imagedestroy($_img);

    ob_start();
    imagepng($_img);
    $imageData = ob_get_contents();
    ob_end_clean();

    $imageDataBase64 = 'data:image/png;base64,'.base64_encode($imageData);
    return array('base64'=>$imageDataBase64, 'token'=>$this->token);
  }
}