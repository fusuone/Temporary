$(function() {
  var FADE_TIME = 150; // ms

  // 初始化变量
  var $window = $(window);
  var $usernameInput = $('.usernameInput'); // 账号输入框
  var $messages = $('.messages'); // 显示的消息
  var $inputMessage = $('.inputMessage'); // 消息输入框

  var $loginPage = $('.login.page'); // 登录页
  var $chatPage = $('.chat.page'); // 聊天页

  var uid;
  var username;
  var isLogin = false;
  var $currentInput = $usernameInput.focus();

  // 连接
  var socket = io('http://'+document.domain+':2020');

  // 参与人数
  function addParticipantsMessage (data) {
    var message = '有' + data.numUsers + '人参与';
    log(message);
  }

  // 用户上线
  function onLogin() {
    username = cleanInput($usernameInput.val().trim());
    if (username) {
      uid = '131**' + Math.random().toFixed(2);
      isLogin = true;
      $loginPage.fadeOut();
      $chatPage.show();
      $loginPage.off('click');
      $currentInput = $inputMessage.focus();
      socket.emit('message', {
        type: 'online',
        uid: uid,
        name: username,
        avatar: 'https://avatar.csdn.net/0/4/4/3_sinat_27612147.jpg',
      });
    }
  }

  // 发送消息
  function onMessage() {
    var message = $inputMessage.val();
    message = cleanInput(message);
    if (message) {
      $inputMessage.val('');
      var payload = {
        type: 'privateChat',
        _id: Math.random(),
        text: message,
        createdAt: new Date(),
        sendStatus: 'sending',
        user: {
          _id: uid,
          name: username,
          avatar: 'https://avatar.csdn.net/0/4/4/3_sinat_27612147.jpg',
        },
        toUser: {
          _id: '123456789',
        },
      };
      addChatMessage(payload);
      socket.emit('message', payload);
    }
  }

  // 日志信息
  function log(message, options) {
    var $el = $('<li>').addClass('log').text(message);
    addMessageElement($el, options);
  }

  // 增加消息列表
  function addChatMessage(data, options) {
    options = options || {};

    var $usernameDiv = $('<span class="username"/>').text(data.user.name +'('+ data.user._id +')');
    var $messageBodyDiv = $('<span class="messageBody">').text(data.text);


    var $messageDiv = $('<li class="message"/>')
      .data('username', data.user._id)
      .append($usernameDiv, $messageBodyDiv);

    addMessageElement($messageDiv, options);
  }

  function addMessageElement(el, options) {
    var $el = $(el);

    if (!options) {
      options = {};
    }
    if (typeof options.fade === 'undefined') {
      options.fade = true;
    }
    if (typeof options.prepend === 'undefined') {
      options.prepend = false;
    }

    if (options.fade) {
      $el.hide().fadeIn(FADE_TIME);
    }
    if (options.prepend) {
      $messages.prepend($el);
    } else {
      $messages.append($el);
    }
    $messages[0].scrollTop = $messages[0].scrollHeight;
  }

  // 键盘事件
  $window.keydown(function (event) {
    // 当键入一个键时，自动对焦
    if (!(event.ctrlKey || event.metaKey || event.altKey)) {
      $currentInput.focus();
    }
    // 如果按了回车键
    if (event.which === 13) {
      if (username) {
        onMessage();
      } else {
        onLogin();
      }
    }
  });

  function cleanInput(input) {
    return $('<div/>').text(input).text();
  }

  $loginPage.click(function() {
    $currentInput.focus();
  });

  $inputMessage.click(function() {
    $inputMessage.focus();
  });

  // 监听服务端的通知 ------------
  // 当前
  socket.on('message[current]', function(payload) {
    if (!isLogin) return;
    console.log('message[current]', payload);
    if (payload.type === 'online') { // 上线
      var message = '欢迎来到聊天室';
      log(message, { prepend: true });
      addParticipantsMessage(payload);
    }
  });

  // 指定
  socket.on('message[target]', function(payload) {
    if (!isLogin) return;
    console.log('message[target]', payload);
    if (payload.type === 'privateChat') { // 私聊
      log('接收者' + payload.toUser._id, { prepend: true });
      addChatMessage(payload);
    }
  });

  // 广播
  socket.on('message[broad]', function(payload) {
    if (!isLogin) return;
    console.log('message[broad]', payload);
    if (payload.type === 'joined') { // 加入
      log(payload.uid + '加入');
      addParticipantsMessage(payload);
    } else if (payload.type === 'groupMsg') { // 群聊天
      addChatMessage(payload);
    } else if (payload.type === 'exit') { // 退出
      log(payload.uid + '退出聊天室');
      addParticipantsMessage(payload);
    }
  });

  // 所有
  socket.on('message[all]', function(payload) {
    if (!isLogin) return;
    console.log('message[all]', payload);
    if (payload.type === 'groupMsg') { // 群聊天
      log('all', { prepend: true });
      addChatMessage(payload);
    }
  });
});
