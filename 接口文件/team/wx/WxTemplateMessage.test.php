<?php

require_once __DIR__.'/WxTemplateMessage.php';

function test_t1() {
  $wxTemplateMessage = new WxTemplateMessage();
  $messageResult = $wxTemplateMessage->t1(array(
    'openid' => 'om3Lw0OpjCGy4J37vUfwVr5Si7ZU',
    'form_id' => '1542877729838',
    'shopName' => '壹软网络(测试)',
    'orderNumber' => '81123142056622',
    'amount' => '0.01',
    'tableNumber' => '301房',
    'orderDate' => '2018-11-23 14:20:56'
  ));
  echo json_encode($messageResult);
}

function test_mp1() {
  $wxTemplateMessage = new WxTemplateMessage();
  $messageResult = $wxTemplateMessage->mp1(array(
    'openid' => 'om3Lw0OpjCGy4J37vUfwVr5Si7ZU',
    'shopname' => '壹软网络(体验店)',
    'amount' => '88.00',
    'username' => '庄龙',
    'date' => '2019.01.07 09:52:08',
    'remark' => ''
  ));
  echo json_encode($messageResult);
}

// test_t1();
// test_mp1();