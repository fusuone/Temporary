<?php
require_once './log.php';

function testLog() {
  // 初始化日志
  $logHandler = new CLogFileHandler(__DIR__.'/'.date('Y-m-d').'.test.log');
  Log::Init($logHandler, 15);

  $array = array(
    'an'=>'boss',
    'pt'=>'mini',
    'name'=>'庄龙',
    'n_url'=>'https://tieba.baidu.com'
  );

  Log::DEBUG("begin");
  Log::DEBUG("content: ".json_encode($array, 320)); // https://blog.csdn.net/wuxing164/article/details/73321691
  Log::DEBUG("end \n");
}

// testLog();