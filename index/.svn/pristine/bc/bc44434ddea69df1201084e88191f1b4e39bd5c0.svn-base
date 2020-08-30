import { routerRedux } from 'dva/router';
import { message } from 'antd';
import http from '@/utils/http';

async function __getCaptcha(params) {
  return http({ api: 'getsmscode', params });
}

async function __register(params) {
  return http({ api: 'register', params });
}

export default {
  namespace: 'register',

  state: {
  },

  effects: {
    *getCaptcha({ payload }, { call }) {
      try {
        const { status, msg } = yield call(__getCaptcha, payload);
        if (status === '0') {
          message.success(msg);
          return 'ok';
        }
        message.warn(msg);
        return 'error';
      } catch (error) {
        return 'error';
      }
    },

    *submit({ payload }, { call, put }) {
      try {
        const { status, msg } = yield call(__register, payload);
        if (status === '0') {
          yield put(routerRedux.replace({
            pathname: '/user/register-result',
            state: {
              account: payload.phone
            }
          }));
        } else {
          message.warn(msg);
        }
      } catch (error) {
        //
      }
    }
  },

  reducers: {
  }
};
