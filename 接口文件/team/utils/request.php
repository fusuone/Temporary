<?php
/**
 * 请求类
 */

class Request {
  protected $sslcert_path;
  protected $sslkey_path;

  public function __construct($params = array()) {
    $this->sslcert_path = @$params['sslcert_path'];
    $this->sslkey_path = @$params['sslkey_path'];
  }

  /**
   * @param array $params 内部会转换为 name=zl&age=24 的形式，然后拼接 $url，
   * 如果使用 $params，要注意在 url 后面添加 ? 或 &
   */
  public function get($url = '', $params = array(), $options = array()) {
    $ch = curl_init($url.$this->toUrlParams($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!empty($options)) {
      curl_setopt_array($ch, $options);
    }
    // https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    // 状态码
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 200 && $code < 300) {
      if ($error) {
        throw new Exception('请求错误：异常');
      }
      return $result;
    } else {
      $codeMsg = $this->getStatusCodeMessage($code);
      if ($codeMsg) {
        throw new Exception($codeMsg);
      } else {
        throw new Exception('请求错误：'.$code);
      }
    }
  }

  /**
   * @param bool $useCert 是否需要证书，如果为 true，需要在实例化时传入 sslcert_path sslkey_path
   */
  public function post($url = '', $params = '', $useCert = false, $options = array()) {
    if (is_array($params)) {
      $params = http_build_query($params);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置cURL允许执行的最长秒数
    if (!empty($options)) {
      curl_setopt_array($ch, $options);
    }
    // https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($useCert == true) {
      // 第一种方法，cert 与 key 分别属于两个.pem文件
      curl_setopt($ch,CURLOPT_SSLCERTTYPE, 'PEM');
      curl_setopt($ch,CURLOPT_SSLCERT, $this->sslcert_path);
      curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
      curl_setopt($ch,CURLOPT_SSLKEY, $this->sslkey_path);
      // 第二种方式，两个文件合成一个.pem文件
      // curl_setopt($ch,CURLOPT_SSLCERT, getcwd().'/all.pem');
    }

    $result = curl_exec($ch);
    $error = curl_error($ch);
    // 状态码
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 200 && $code < 300) {
      if ($error) {
        throw new Exception('请求错误：异常');
      }
      return $result;
    } else {
      $codeMsg = $this->getStatusCodeMessage($code);
      if ($codeMsg) {
        throw new Exception($codeMsg);
      } else {
        throw new Exception('请求错误：'.$code);
      }
    }
  }

  // 转换为 url 请求参数
  private function toUrlParams($params = array()) {
    $buff = '';
    foreach ($params as $k => $v) {
      $buff .= $k . '=' . $v . '&';
    }
    $buff = trim($buff, '&');
    return $buff;
  }

  // 状态码信息
  private function getStatusCodeMessage($code) {
    $codeMaps = array(
      403=>'YR 服务器禁止访问',
      404=>'YR 服务器没有此服务',
      500=>'YR 服务器出错',
      503=>'YR 服务器超时',
      504=>'YR 服务器没有响应'
    );

    if (array_key_exists($code, $codeMaps)) {
      return $codeMaps[$code];
    } else {
      return false;
    }
  }
}