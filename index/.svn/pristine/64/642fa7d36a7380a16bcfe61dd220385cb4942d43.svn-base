import 'moment/locale/zh-cn';
import { message } from 'antd';
import { createLogger } from 'redux-logger';

// 打印日志
// eslint-disable-next-line
const logger = createLogger({
  predicate: () => process.env.NODE_ENV === 'development' && !!window.navigator.userAgent,
  collapsed: true,
  duration: false,
  timestamp: false
});

/* eslint-disable import/prefer-default-export */
export function config() {
  return {
    onError(err) {
      err.preventDefault();
      message.error(err.message);
    },
    onAction: logger
  };
}
