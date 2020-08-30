<?php
/**
 * 支付服务封装
 */

require_once __DIR__.'/../utils/config.php';
require_once __DIR__.'/../utils/request.php';

class WxPayService {
  protected $wxNotifyBaseUrl;
  protected $wxServiceBaseUrl;

  public function __construct() {
    $serverConfig = new Config;
    $this->wxNotifyBaseUrl = $serverConfig->getWxNotifyBaseUrl();
    $this->wxServiceBaseUrl = $serverConfig->getWxServiceBaseUrl();
  }

  // 小程序支付
  public function pay_mini($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=pay_mini&an=team';
    $orderData['notify_url'] = $this->wxNotifyBaseUrl.'/notify.php'; // 异步回调地址
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }
  
  // app支付
  public function pay_app($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=pay_app&an=team';
    $orderData['notify_url'] = $this->wxNotifyBaseUrl.'/notify.php'; // 异步回调地址
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }

  // 企业付款到零钱
  public function pay_to_pocket($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=pay_to_pocket&an=team';
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }

  // 企业付款到银行卡
  public function pay_to_bank($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=pay_to_bank&an=team';
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }

  // 查询付款到零钱
  public function query_pocket($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=query_pocket&an=team';
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }

  // 查询付款到银行卡
  public function query_bank($orderData = array()) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=query_bank&an=team';
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $orderData), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'2', 'msg'=>$e->getMessage());
    }
  }
}