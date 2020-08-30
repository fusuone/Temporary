<?php
use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Autoloader;
use PHPSocketIO\SocketIO;

// composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

class SocketChat {
  private $timeout = 60; // 超时(秒)
  private $port = 2020; // 监听端口
  private $io; // SocketIO 对象
  private $socket; // SocketIO 客户端连接
  private static $chatUser = []; // 在线用户

  public function __construct() {
    $this->start();
  }

  public function start() {
    $io = new SocketIO($this->port);
    $io->on('connection', function($socket) use($io) {
      $this->io = $io;
      $socket->on('message', function($payload) use($socket) {
        $this->socket = $socket;
        $this->parseMessage($payload);
      });
  
      $socket->on('disconnect', function($payload) use($socket)  {
        $this->socket = $socket;
        $this->parseMessage(['type' => 'disconnect']);
      });
    });
  }

  // 关闭链接
  public function close() {
    $this->socket->disconnect();
  }

  // 解析发送的数据
  public function parseMessage($payload) {
    switch ($payload['type']) {
      case 'online': // 上线
        $this->bind($payload);
        $this->freshOnlineUser($payload);
        break;
      case 'offline': // 下线
        $this->offline($payload);
        break;
      case 'privateChat': // 私聊
        $this->sendChatMessgae($payload);
        break;
      case 'disconnect': // 客户端断开连接
        $this->disConnect();
        break;
      default:
        break;
    }
  }

  // 用户--链接 绑定
  public function bind($payload) {
    $this->socket->uid = $payload['uid'];
    self::$chatUser[$payload['uid']] = [
      'uid' => $payload['uid'],
      'name' => $payload['name'],
      'avatar' => $payload['avatar'],
      'socketId' => $this->socket->id,
    ];
  }

  // 用户--链接 解绑
  public function unBind($uid) {
    unset(self::$chatUser[$uid]);
  }

  // 获取在线用户
  public function getOnlineUser() {
    return self::$chatUser;
  }

  // 用户上线通知
  public function freshOnlineUser($payload) {
    $numUsers = count(self::$chatUser);
    $this->sendToCurrent([
      'type' => 'online',
      'uid' => $payload['uid'],
      'name' => $payload['name'],
      'avatar' => $payload['avatar'],
      'numUsers' => $numUsers,
    ]);
    $this->sendToBroad([
      'type' => 'joined',
      'uid' => $payload['uid'],
      'name' => $payload['name'],
      'avatar' => $payload['avatar'],
      'list' => self::$chatUser,
      'numUsers' => $numUsers,
    ]);
  }

  // 聊天消息通知
  public function sendChatMessgae($payload) {
    $toID = $payload['toUser']['_id'];

    // 附加数据
    $payload['date'] = date('Y-m-d H:i:s'); 
    $payload['sendStatus'] = 'success';

    // 对方是否在线
    if (empty(self::$chatUser[$toID])) {
      // 缓存离线消息
    } else {
      $targetUser = self::$chatUser[$toID];
      $this->sendToTarget($targetUser['socketId'], $payload);
    }

    // 发送消息的回执
    $this->sendToCurrent([
      'type' => 'privateChat ack',
      'msgId' => $payload['_id'],
      'sendStatus' => $payload['sendStatus'],
      'date' => $payload['date']
    ]);

    // 广播到其它客户端
    $payload['type'] = 'groupMsg';
    $this->sendToBroad($payload);
  }

  // 用户下线
  function offline($payload) {
    $this->unBind($payload['uid']);
    // 在全局上通知该用户离开
    $this->sendToBroad([
      'type' => 'exit',
      'msg' => 'offline',
      'uid' => $this->socket->uid,
      'chatUser' => self::$chatUser,
      'numUsers' => count(self::$chatUser),
    ]);
  }

  // 客户端断开连接
  function disConnect() {
    if ($this->socket->uid) {
      $this->unBind($this->socket->uid);
      // 在全局上通知该用户离开
      $this->sendToBroad([
        'type' => 'exit',
        'msg' => 'dis',
        'uid' => $this->socket->uid,
        'chatUser' => self::$chatUser,
        'numUsers' => count(self::$chatUser),
      ]);
    }
  }

  // 向当前客户端发送事件
  public function sendToCurrent($payload) {
    $this->socket->emit('message[current]', $payload);
  }

  // 向指定客户端发送事件
  public function sendToTarget($socketId, $payload) {
    $this->socket->to($socketId)->emit('message[target]', $payload);
  }

  // 向所有客户端发送事件
  public function sendToAll($payload) {
    $this->io->emit('message[all]', $payload);
  }

  // 向所有客户端发送事件，但不包括当前连接
  public function sendToBroad($payload) {
    $this->socket->broadcast->emit('message[broad]', $payload);
  }
}

new SocketChat();

if (!defined('GLOBAL_START')) {
  Worker::runAll();
}
