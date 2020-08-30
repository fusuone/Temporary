<?php
// 人工智能业务，名片获取，内容审核， 人脸识别等

spl_autoload_register(function ($class) {
    include("./AISDK/{$class}.php");
});

//请在此填入AppID与AppKey
$app_id = '2116732601';
$app_key = 'qKEHi8JrrbBzjxN5';

//设置AppID与AppKey
Configer::setAppInfo($app_id, $app_key);


// 名片识别
if ($a == 'ocrnamecard') {
	$tmp = $_FILES['file']['tmp_name']; // 图片资源
	$type = $_POST['type']; // ai类型
  $suffix = '.jpg'; // 图片后缀
  $tWidth = 200; // 缩略图宽度
  $tHeight = 200; // 缩略图高度
  $name = date('YmdHis', time()).'_'.mt_rand(1000, 9999); // 图片名称
  $serverAssetsUrl = $serverConfig->getAssetsUrl();

  $imageName = $name.$suffix; // 图片名
  $imagePath = './AIcache/'.$imageName; // 图片路径
  $imageSource = $serverAssetsUrl.'/background/AIcache/'.$imageName; // 图片http地址

  if (move_uploaded_file($tmp, $imagePath)) {
    $data = array('source'=>'', 'thumbnail'=>'');

    $data['source'] = $imageSource;

	// 通用OCR识别
	$image_data = file_get_contents('./AIcache/'.$imageName);
	$params = array(
    'image' => base64_encode($image_data),
    'time_stamp' => strval(time()),
    'nonce_str'  => strval(rand()),
    'sign'       => '',	
	);

	$response = API::ocrnamecard($params,$type);

 exit(JSON(array('data'=>$response, 'msg'=>'ok', 'status'=>'0')));

  } else {
    exit(JSON(array('data'=>'', 'msg'=>'失败', 'status'=>'1')));
  }
}



// 敏感词检测
if ($a == 'aievilaudio') {
  exit(JSON(array('data'=>'', 'msg'=>'已失效', 'status'=>'1')));


  exit(JSON($output));
}



