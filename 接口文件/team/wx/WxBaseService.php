<?php
/**
 * 基础服务封装
 */

require_once __DIR__.'/../utils/request.php';
require_once __DIR__.'/../utils/config.php';

class WxBaseService {
  protected $wxServiceBaseUrl;

  public function __construct() {
    $serverConfig = new Config;
    $this->wxServiceBaseUrl = $serverConfig->getWxServiceBaseUrl();
  }

  // 小程序 开放数据校验与解密
  public function getAes($iv, $sessionKey, $encryptedData) {
    $url = $this->wxServiceBaseUrl.'/index.php?a=get_aes&an=team';
    $params = array('iv'=>$iv, 'session_key'=>$sessionKey, 'encrypted_data'=>$encryptedData);

    try {
      $request = new Request();
      $result = json_decode($request->post($url, $params), true);
      return $result;
    } catch (Exception $e) {
      return array('status'=>'1', 'msg'=>$e->getMessage());
    }
  }
  
  // app 获取用户信息
  public function getWxUserInfo($code = '') {
    $url = $this->wxServiceBaseUrl.'/index.php?a=userinfo&an=team&';
  
    try {
      $request = new Request();
      $result = json_decode($request->get($url, array('code'=>$code)), true);
      if ($result['status'] != '0') {
        return array('status'=>'1', 'msg'=>$result['msg']);
      } else {
        return array('status'=>'0', 'data'=>$result['data']);
      }
    } catch (Exception $e) {
      return array('status'=>'1', 'msg'=>$e->getMessage());
    }
  }

  public function code2Session($code = '') {
    $url = $this->wxServiceBaseUrl.'/index.php?a=code2session&an=team&';
  
    try {
      $request = new Request();
      $result = json_decode($request->get($url, array('js_code'=>$code)), true);
      if ($result['status'] != '0') {
        return array('status'=>'1', 'msg'=>$result['msg']);
      } else {
        return array('status'=>'0', 'data'=>$result['data']);
      }
    } catch (Exception $e) {
      return array('status'=>'1', 'msg'=>$e->getMessage());
    }
  }

  // 开放平台 accessToken
  public function getOpenAT($code = '') {
    $url = $this->wxServiceBaseUrl.'/index.php?a=get_open_at&an=team&';

    try {
      $request = new Request();
      $result = json_decode($request->get($url, array('code'=>$code)), true);
      if ($result['status'] != '0') {
        return array('status'=>'1', 'msg'=>$result['msg']);
      } else {
        return array('status'=>'0', 'data'=>$result['data']);
      }
    } catch (Exception $e) {
      return array('status'=>'1', 'msg'=>$e->getMessage());
    }
  }

  // 公众号平台 accessToken
  // 注：该接口使用正式库，如果使用测试库会导致存在正式库中的 accessToken 失效
  public function getMpAT() {
   // $url = $this->wxServiceBaseUrl.'/index.php?a=get_mp_at&an=team';
    $url = $this->wxServiceBaseUrl.'/index.php?a=get_mp_at&an=boss';

    // 连接数据库
    $serverConfig = new Config;
    $serverConfig->setEnv('production');
    $mysqli = $serverConfig->db();  
    if (!$mysqli) {
      return array('status'=>'1', 'msg'=>'error connecting');
    }

    $query = "SELECT *, IF(TIMESTAMPDIFF(SECOND,billdate,NOW())<=7000, 0, 1) AS is_late FROM `access_token` ORDER BY billdate DESC LIMIT 1";
    $result = $mysqli->query($query);
    if (!$result) {
      return array('status'=>'1', 'msg'=>'error query');
    }
    $data = $result->fetch_assoc();

    $request = new Request();
    if ($data) {
      if ($data['is_late'] == 0) {
        // 在有效期内(7200s)
        return array('status'=>'0', 'access_token'=>$data['value']);
      } else {
        try {
          // 已过有效期则重新获取
          $result = json_decode($request->get($url), true);
          if ($result['status'] != '0') {
            return array('status'=>'1', 'msg'=>$result['msg']);
          }

          $token = $result['data']['access_token'];
          $sql = "UPDATE `access_token` SET billdate=NOW(),`value`='$token' WHERE id='{$data['id']}'";
          if (!$mysqli->query($sql)) {
            return array('status'=>'1', 'msg'=>'error update');
          } else {
            return array('status'=>'0', 'access_token'=>$token);
          }
        } catch (Exception $e) {
          return array('status'=>'1', 'msg'=>$e->getMessage());
        }
      }
    } else {
      // 不存在则获取写入
      try {
        $result = json_decode($request->get($url), true);
        if ($result['status'] != '0') {
          return array('status'=>'1', 'msg'=>$result['msg']);
        }

        $token = $result['data']['access_token'];
       
        $sql = "INSERT INTO `access_token` SET billdate=NOW(),`value`='$token'";
        if (!$mysqli->query($sql)) {
          return array('status'=>'1', 'msg'=>'error insert');
        } else {
          return array('status'=>'0', 'access_token'=>$token);
        }
      } catch (Exception $e) {
        $mg = $e->getMessage();
        return array('status'=>'1', 'msg'=>$mg);
      }
    }
  }
}