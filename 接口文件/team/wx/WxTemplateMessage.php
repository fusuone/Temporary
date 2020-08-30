<?php
/**
 * 模板消息 -> 服务消息&统一服务消息
 * 文档：
 *  https://developers.weixin.qq.com/miniprogram/dev/api/addTemplate.html
 *  https://developers.weixin.qq.com/miniprogram/dev/api/sendUniformMessage.html
 */

require_once __DIR__.'/WxBaseService.php';
require_once __DIR__.'/../utils/request.php';

class WxTemplateMessage {

  /**
   * 服务消息
   * 
   * 下单成功通知
   */
  public function t1($data) {
    if (!$data['openid']) {
      return array('status'=>'1', 'msg'=>'openid null');
    }
    if (!$data['form_id']) {
      return array('status'=>'1', 'msg'=>'form_id null');
    }

    $tmplData = array(
      'touser' => $data['openid'],
      'template_id' => 'eBsiHxgoTr0uAlq9lajcDjXu32OH9GzZs9m6yROu2Vk',
      'page' => 'pages/index/index',
      'form_id' => $data['form_id'],
      'data'=> array(
        'keyword1' => array('value' => $data['shopName']),
        'keyword2' => array('value' => $data['orderNumber']),
        'keyword3' => array('value' => $data['amount'].'元'),
        'keyword4' => array('value' => $data['tableNumber']),
        'keyword5' => array('value' => $data['orderDate'])
      )
    );
    return $this->send(json_encode($tmplData));
  }

  /**
   * 服务消息
   * 
   * 商家确认通知
   */
  public function t2($data) {
    if (!$data['openid']) {
      return array('status'=>'1', 'msg'=>'openid null');
    }
    if (!$data['form_id']) {
      return array('status'=>'1', 'msg'=>'form_id null');
    }

    $tmplData = array(
      'touser' => $data['openid'],
      'template_id' => '0cbqZgZux85JWoNVhyucPxlmdHZ7b8tRJzapsWDqo1k',
      'page' => 'pages/index/index',
      'form_id' => $data['form_id'],
      'data'=> array(
        'keyword1' => array('value' => $data['shopName']),
        'keyword2' => array('value' => $data['orderNumber']),
        'keyword3' => array('value' => $data['amount'].'元('.$data['tableNumber'].')'),
        'keyword4' => array('value' => $data['status'])
      )
    );
    return $this->send(json_encode($tmplData));
  }

  /**
   * 服务消息
   * 
   * 提现申请通知
   */
  public function t3($data) {
    if (!$data['openid']) {
      return array('status'=>'1', 'msg'=>'openid null');
    }
    if (!$data['form_id']) {
      return array('status'=>'1', 'msg'=>'form_id null');
    }

    $tmplData = array(
      'touser' => $data['openid'],
      'template_id' => '7rXDB28axSmD9aZSnFNmGHujdOyfDfVKoqMmWVVEPIY',
      'page' => 'pages/index/index',
      'form_id' => $data['form_id'],
      'data'=> array(
        'keyword1' => array('value' => $data['amount'].'元'), // 提现金额
        'keyword2' => array('value' => $data['money'].'元'), // 实际到账
        'keyword3' => array('value' => $data['cost'].'元'), // 手续费
        'keyword4' => array('value' => $data['type']), // 提现方式
        'keyword5' => array('value' => $data['date']), // 提现时间
        'keyword6' => array('value' => $data['status']), // 提现状态
        'keyword7' => array('value' => $data['remark']) // 备注
      )
    );

    return $this->send(json_encode($tmplData));
  }

  /**
   * 统一服务消息
   * 
   * 提现审核通知，审核员接收
   * 注：url、miniprogram 二选一即可，miniprogram 优先级高
   */
  public function mp1($data) {
    // if (!$data['openid']) {
    //   return array('status'=>'1', 'msg'=>'openid null');
    // }

    $tmplData = array(
      'touser' => $data['openid'],
      'mp_template_msg'=> array(
        'appid' => 'wx582728b1cc432635',
        'template_id' => 'thwLl-s1qkjrGKtSJVx1nTozv8Wp1xDSMZRt4ToKJ7I',
        'url' => 'http://weixin.qq.com/download',
        'miniprogram' => array(
          'appid' => 'wxe2aba0311c27fe12',
          'pagepath' => 'pages/index/index',
        ),
        'data'=> array(
          'first' => array(
            'value' => '您好！您有一笔提现单需要审核！'
          ),
          'keyword1' => array(
            'value' => $data['shopname']
          ),
          'keyword2' => array(
            'value' => $data['amount']
          ),
          'keyword3' => array(
            'value' => $data['username']
          ),
          'keyword4' => array(
            'value' => $data['date']
          ),
          'remark' => array(
            'value' => $data['remark']
          ),
        )
      ),
    );
    return $this->send(json_encode($tmplData), 2);
  }

  // 发送
  private function send($data, $type = 1) {
    $wxBaseService = new WxBaseService();
    $atResult = $wxBaseService->getMpAT();
    if ($atResult['status'] != '0') {
      return array('status'=>'1', 'msg'=>$atResult['msg']);
    }

    $token = $atResult['access_token'];

    if ($type == 1) {
      $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$token;
    } else {
      $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token='.$token;
    }
    
    try {
      $request = new Request();
      $result = json_decode($request->post($url, $data), true);
// echo "pkkpp==".$result['errmsg'];
      if ($result['errcode'] == 0) {
        return array('status'=>'0', 'msg'=>'发送成功');
      } else {
        return array('status'=>'1', 'msg'=>$result['errcode'].':'.$result['errmsg']);
      }
    } catch (Exception $e) {
      return array('status'=>'1', 'msg'=>$e->getMessage());
    }
  }
}

