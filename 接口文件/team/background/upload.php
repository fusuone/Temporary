<?php
// 文件上传

// 图片上传    ###批注： 这里的图片URL应该改为相对路径。 即APP取得默认的基础URL连接 图片名称作为图片的URL
if ($a == 'uploadimage') {
  exit(JSON(array('data'=>'', 'msg'=>'已失效', 'status'=>'1')));

  $tmp = $_FILES['file']['tmp_name']; // 图片资源
  $uuid = date('YmdHis', time()).'_'.mt_rand(1000, 9999);
  $dir = '/team/images/';

  if (!$tmp) {
    exit(JSON(array('data'=>'', 'msg'=>'没有资源', 'status'=>'1')));
  }
  
  $imageName = $uuid.'.jpg'; // 文件名
  $imagePath = '../..'.$dir.$imageName; // 图片路径
  $imageUrl = $serverAssetsUrl.$dir.$imageName; // 图片url地址

  if (move_uploaded_file($tmp, $imagePath)) {
    $data = array('url'=>$imageUrl, 'thumbUrl'=>'');
    $output = array('data'=>$data, 'msg'=>'上传成功', 'status'=>'0');
  } else {
    $output = array('data'=>'', 'msg'=>'上传失败', 'status'=>'1');
  }
  exit(JSON($output));
}

// 上传图片处理
if ($a == 'uploadimg') {
  $tmp = $_FILES['file']['tmp_name']; // 图片资源
  $is_thumb = $_POST['is_thumb']; // 1|0 是否需要裁剪
  $type = isset($_POST['type']) ? checkInput($_POST['type']) : 'image'; // avatar|image 保存到不同的目录

  $suffix = '.jpg'; // 图片后缀
  $tWidth = 200; // 缩略图宽度
  $tHeight = 200; // 缩略图高度
  $name = date('YmdHis', time()).'_'.mt_rand(1000, 9999); // 图片名称
  $serverAssetsUrl = $serverConfig->getAssetsUrl();

  $imageName = $name.$suffix; // 图片名
  $imagePath = '../assets/images/'.$imageName; // 图片路径
  $imageSource = $serverAssetsUrl.'/images/'.$imageName; // 图片http地址

  $thumbnailName = $name.'_'.$tWidth.$suffix; // 缩略图名
  $thumbnailPath = '../assets/images/'.$thumbnailName; // 缩略图路径
  $thumbnailSource = $serverAssetsUrl.'/images/'.$thumbnailName; // 缩略图http地址

  // 如果是头像则保存在 thumb 目录，并且强制裁剪
  if ($type == 'avatar') {
    $is_thumb = '1';
    $thumbnailPath = '../assets/thumb/'.$thumbnailName;
    $thumbnailSource = $serverAssetsUrl.'/thumb/'.$thumbnailName;
  }

  if (move_uploaded_file($tmp, $imagePath)) {
    $data = array('source'=>'', 'thumbnail'=>'');

    // 使用缩略图
    if ($is_thumb == '1') {
      mkThumbnail($imagePath, $tWidth, $tHeight, $thumbnailPath);
      unlink($imagePath); // 裁剪之后删除原图
      $data['source'] = $thumbnailSource;
    } else {
      $data['source'] = $imageSource;
    }
    $output = array('data'=>$data, 'msg'=>'ok', 'status'=>'0');
  } else {
    $output = array('data'=>'', 'msg'=>'error', 'status'=>'1');
  }
  exit(JSON($output));
}