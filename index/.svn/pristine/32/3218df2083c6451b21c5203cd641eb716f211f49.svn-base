import { message } from 'antd';
import http from '@/utils/http';

async function __geographic(params) {
  return http({ api: 'geographic', params });
}

export default {
  namespace: 'geographic',

  state: {
    province: [],
    city: []
  },

  effects: {
    *get({ payload }, { call, put, select }) {
      try {
        // 存在则不需要重复获取
        const { province } = yield select(state => state.geographic);
        if (province.length > 0) {
          return 'ok';
        }
        const { status, msg, data } = yield call(__geographic, payload);
        if (status === '0') {
          yield put({
            type: 'saveGeographic',
            payload: data
          });
          return 'ok';
        }
        message.warn(msg);
        return 'error';
      } catch (error) {
        return 'error';
      }
    }
  },

  reducers: {
    saveGeographic(state, { payload }) {
      return {
        ...state,
        province: payload.province || [],
        city: payload.city || []
      };
    }
  }
};
