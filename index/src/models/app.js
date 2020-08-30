import * as storageLogin from '@/dao/storageLogin';
import * as storageAccount from '@/dao/storageAccount';

export default {
  namespace: 'app',

  state: {
  },

  effects: {
    // *xx(action, { put }) {
    //   yield put({ type: 'xx/xxx', payload: {} });
    // },
  },

  reducers: {
  },

  subscriptions: {
    setup({ dispatch }) {
      const loginStorage = storageLogin.get();
      if (loginStorage.status === 'ok') {
        const accountStorage = storageAccount.get();
        const { admin, phoneno } = accountStorage;
        window.adm = admin || phoneno; // 设置adm参数
        dispatch({
          type: 'login/changeLoginStatus',
          payload: { status: loginStorage.status }
        });
        dispatch({
          type: 'user/saveCurrentUser',
          payload: accountStorage
        });
      }
    }
  }
};
