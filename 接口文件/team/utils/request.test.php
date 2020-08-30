<?php
require_once './request.php';

// 请求参数
$paramsData = array('name'=>'zhangsan','age'=>'24'); // 传递的参数
$options = array(CURLOPT_TIMEOUT=>3); // 超时

// GET
function testGet() {
  global $paramsData, $options;
  $request = new Request();
  try {
    $result = $request->get('http://t.yushu.im/v2/movie/in_theaters?', array(), $options);
    echo $result;
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

// POST
function testPost() {
  global $paramsData, $options;
  $request = new Request();
  try {
    $result = $request->post('http://t.yushu.im/v2/movie/in_theaters', $paramsData, false, $options);
    echo $result;
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

// testGet();