import { message } from 'antd';
import { createLogger } from 'redux-logger';

import * as storageLogin from '@/dao/storageLogin';
import * as storageAccount from '@/dao/storageAccount';

import userModel from '@/models/user';
import loginModel from '@/pages/User/models/login';

// 打印日志
// eslint-disable-next-line
const logger = createLogger({
  predicate: () => process.env.NODE_ENV === 'development' && !!window.navigator.userAgent,
  collapsed: true,
  duration: false,
  timestamp: false,
});

/* eslint-disable import/prefer-default-export */
export function config() {
  // 加载storage到initialState
  const loginStorage = storageLogin.get();
  const accountStorage = storageAccount.get();

  const loginState = {
    ...loginModel.state,
    status: loginStorage.status,
  };
  const userState = {
    ...userModel.state,
  };
  if (loginStorage.status === 'ok') {
    const { admin, userno } = accountStorage;
    window.adm = admin || userno; // 设置adm参数
    userState.currentUser = accountStorage;
  }

  return {
    onError(err) {
      err.preventDefault();
      message.error(err.message);
    },
    onAction: logger,
    initialState: {
      user: userState,
      login: loginState,
    },
  };
}
