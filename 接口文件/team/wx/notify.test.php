<?php
/**
 * 本地测试支付异步回调通知
 * 1.支付时设置异步回调地址为测试的，如：https://json.kassor.cn/wxpay/test/index.php
 * 2.通过日志记录下回调结果
 * 3.然后把结果填到这里测试
 */

require_once __DIR__.'/../utils/utils.php';
require_once __DIR__.'/../utils/request.php';

// header('Content-type:text/xml; charset=utf-8'); // 如果要输出xml，启动此项
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$host = $http_type.$_SERVER['HTTP_HOST'];
define('URL', $host.'/team/wx/notify.php');

// 退款通知回调
function test() {
  $utils = new Utils();
  $request = new Request();

  $str = '{"appid":"wx97211d9cd38ec6eb","attach":"{\"an\":\"team\",\"pt\":\"app\"}","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"1529401371","nonce_str":"lfm91y20s034h85d3nx6symr3ckblqh3","openid":"oXD3TwtuAlKjlc1xQH-siYLEP0Ro","out_trade_no":"90322103529434","result_code":"SUCCESS","return_code":"SUCCESS","sign":"674616002F4D66FD2FF844932B44682B","time_end":"20190322103535","total_fee":"1","trade_type":"APP","transaction_id":"4200000301201903223217832027"}';
  $arr = json_decode($str, true);
  $xml = $utils->arrayToXml($arr);
  try {
    $result = $request->post(URL, $xml);
    echo $result;
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

// test();