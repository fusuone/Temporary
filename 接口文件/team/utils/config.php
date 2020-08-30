<?php
/**
 * 配置
 * 统一在这里设置
 */
error_reporting(E_ERROR);

$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
define('HOST', $http_type.$_SERVER['HTTP_HOST']);

class Config {
  protected $env = 'production'; // 部署环境 测试-dev|  正式-production
  public $enableTimingTask = true; // 定时任务开关

  public function getEnv() {
    return $this->env;
  }

  public function setEnv($env) {
    $this->env = $env;
  }

  public function getHost() {
    return HOST;
  }

  // 图片资源
  public function getAssetsUrl() {
    if ($this->env == 'dev') {
      return HOST.'/team160/assets';
    } else {
      // return 'https://wolf.kassor.cn//team/assets';
      return HOST.'/team/assets';
    }
  }

  // 当前服务的基础url
  public function getCurrentBaseUrl() {
    if ($this->env == 'dev') {
      return HOST.'/team160/background';
    } else {
      // return 'https://wolf.kassor.cn/team/background';
      return HOST.'/team/background';
    }
  }

  // 支付回调基础url
  public function getWxNotifyBaseUrl() {
     if ($this->env == 'dev') {
      return 'https://wolf.kassor.cn/team160/wx';
    } else {
      return 'https://wolf.kassor.cn/team/wx';
    }
  }

  // 微信服务的基础url
  public function getWxServiceBaseUrl() {
    if ($this->env == 'dev') {
      return HOST.'/wxpay';
    } else {
      return 'https://pay.kassor.cn/wxpay'; // 在线上要使用这个地址
      // return HOST.'/mapi/wxpay'; //  --- 测试后删除 ---
    }
  }

  // 微信的配置 Secret(appid在本地) 好业绩团队
  public function getSecret(){
	  return '6a255b97cfeae727d437661b842c8a63'; // 1.好业绩团队
  }

  // 微信的配置 Secret(appid在本地) 货真真
  public function getHzzSecret(){
    return 'cfa4eb5038c7432f6ec2291ff1bcac04'; // 货真真
  }

  // 小程序 - 微信的配置 Secret(appid在本地) 货真真
   public function getHzzWxSecret(){
    return '144e9a6198a20a7335d8a2f1844355f0'; // 货真真 小程序
  }
  
  // 连接数据库
  public function db() {
    if ($this->env == 'dev') {
      // 测试 服务端
      // $mysql_server_name = '39.105.7.130';
      // $mysql_username = 'team';
      // $mysql_password = 'team123456)(*&^%';
      // $mysql_database = 'team';

      $mysql_server_name = '14.152.92.37';
      $mysql_username = 'teamuser';
      $mysql_password = 'zxcvbnm123456!@#';
      $mysql_database = 'team';
      
    } else { // 正式 服务端
      $mysql_server_name = '14.152.92.37';
      $mysql_username = 'teamuser';
      $mysql_password = 'zxcvbnm123456!@#';
      $mysql_database = 'team';
    }

	  $mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);
    if (!$mysqli->connect_error) {
			$mysqli->set_charset('utf8');
		}
	  return $mysqli;
  }
}