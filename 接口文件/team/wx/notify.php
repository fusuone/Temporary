<?php
/**
 * 支付异步回调通知
 * 文档：https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_7&index=8
 */

ini_set('date.timezone', 'Asia/Shanghai');
require_once __DIR__.'/../utils/config.php';
require_once __DIR__.'/../utils/function.php';
require_once __DIR__.'/../utils/log.php';
require_once __DIR__.'/../utils/utils.php';

// 初始化日志
$logHandler = new CLogFileHandler(__DIR__.'/logs/pay/'.date('Y-m-d').'.log');
Log::Init($logHandler, 15);

class HandleNotify {
  protected $mysqli;
  public $utils;

  public function __construct() {
    $this->utils = new Utils();
  }

  public function check() {
    $xml = file_get_contents('php://input');
    if (!$xml) {
      Log::INFO('xml null');
      return array('status'=>'1');
    }

    // TODO：
    // 这里应该还要校验数据

    $data = $this->utils->xmlToArray($xml);
    if (!is_array($data)) {
      Log::INFO('notify data：error');
      return array('status'=>'1');
    }
    Log::INFO('notify data：'.json_encode($data, 320));

    if (isset($data['return_code']) && $data['return_code'] != 'SUCCESS') {
      Log::INFO("return_code fail：".$data['return_msg']);
      return array('status'=>'1');
    }
    if (isset($data['result_code']) && $data['result_code'] != 'SUCCESS') {
      Log::INFO("result_code fail：".$data['err_code']." ".$data['err_code_des']);
      return array('status'=>'1');
    }

    return array('status'=>'0', 'data'=>$data);
  }

  // 处理具体业务逻辑
  public function business($data = array()) {
    if ($data['status'] != '0') {
      return array('status'=>'1');
    }
    $data = $data['data'];

    $appid = isset($data['appid']) ? $data['appid'] : '';
    $mch_id = isset($data['mch_id']) ? $data['mch_id'] : ''; // 商户号
    $bank_type = isset($data['bank_type']) ? $data['bank_type'] : ''; // 付款银行
    $cash_fee = isset($data['cash_fee']) ? $data['cash_fee'] : ''; // 实收金额，单位：分
    $total_fee = isset($data['total_fee']) ? $data['total_fee'] : ''; // 商户订单金额，单位：分
    $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : ''; // 商户订单号
    $transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : ''; // 微信交易号
    $time_end = isset($data['time_end']) ? $data['time_end'] : ''; // 交易结束时间
    $openid = isset($data['openid']) ? $data['openid'] : ''; // 买家微信账户id

    $cash_fee = $cash_fee / 100; // 转为元
    $total_fee = $total_fee / 100; // 转为元
    $time_end = date('Y-m-d H:i:s', strtotime($time_end)); // 格式化时间

    if (!$out_trade_no || !$transaction_id) {
      Log::INFO('params check error');
      return array('status'=>'1');
    }

    $this->db();
    $mysqli = $this->mysqli;
    if (!$mysqli) {
      Log::INFO('error connecting');
      return array('status'=>'1');
    }

    // 处理订单逻辑 start ===
    $sql = "SELECT * FROM mall_orderhead WHERE billno='$out_trade_no'";
    $result=$mysqli->query($sql);
    $orderInfo =$result->fetch_assoc();
    if (!$orderInfo) {
      Log::INFO('---订单查询错误');
      return array('status'=>'1');
    }
    if ($orderInfo['billstate'] == '2') {
      Log::INFO('---订单已更新');
      return array('status'=>'0');
    }

    $sql = "SELECT * FROM team_salesman WHERE billno='{$orderInfo['salerno']}' LIMIT 1";
    $result=$mysqli->query($sql);
    $shopInfo =$result->fetch_assoc();
    if (!$shopInfo) {
      Log::INFO('---商家查询错误');
      return array('status'=>'1');
    }
    $mysqli->query('BEGIN');

    // 更新订单状态
    $sql = "UPDATE mall_orderhead SET paydate='$time_end',billstate=2 WHERE billno='$out_trade_no'";
    if (!$mysqli->query($sql)) {
      Log::INFO('---订单更新失败');
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      return array('status'=>'1');
    } else {
      Log::INFO('---订单更新成功');
    }

    // 更新商家营业额
    $sql = "UPDATE team_salesman SET rmb=rmb+'$total_fee' WHERE billno='{$orderInfo['salerno']}'";
    if (!$mysqli->query($sql)) {
      Log::INFO('---营业额更新失败');
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      return array('status'=>'1');
    } else {
      Log::INFO('---营业额更新成功');
    }

    // 写入日志表
    $sql = "INSERT INTO team_paidlog SET
      billdate=NOW(),
      orderno='$out_trade_no',
      userno='{$orderInfo['buyerno']}',
      shopno='{$orderInfo['salerno']}',
      payway='3',
      paytype='wx',
      appid='$appid',
      mch_id='$mch_id',
      openid='$openid',
      payval='$total_fee',
      cash_fee='$cash_fee',
      bank_type='$bank_type',
      time_end='$time_end',
      transaction_id='$transaction_id'";
    if (!$mysqli->query($sql)) {
      Log::INFO('---写入日志表失败');
      $mysqli->query('ROLLBACK');
      $mysqli->query('END');
      return array('status'=>'1');
    } else {
      Log::INFO('---写入日志表成功');
    }

    $mysqli->query('COMMIT');
    $mysqli->query('END');

    // 处理订单逻辑 end ===
    return array('status'=>'0');
  }

  // 连接数据库
  public function db() {
    if ($this->mysqli) return;
    $serverConfig = new Config;
    $mysqli = $serverConfig->db();
    if ($mysqli) {
      $this->mysqli = $mysqli;
    }
  }
}

Log::DEBUG("begin");
$handleNotify = new HandleNotify();
$result = $handleNotify->check();
$result = $handleNotify->business($result);

$backXml;
if ($result['status'] == '0') {
  $backXml = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
} else {
  $backXml = '<xml><return_code><![CDATA[FAIL]]></return_code></xml>';
}
Log::DEBUG("end \n");

echo $backXml; // 返回给微信
// echo json_encode($result); // 打印调试，用于 refund_notify.test.php