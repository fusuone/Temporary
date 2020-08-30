import { query as queryUsers, queryCurrent } from '@/services/user';
import * as storageAccount from '@/dao/storageAccount';

export default {
  namespace: 'user',

  state: {
    list: [],
    currentUser: {}
  },

  effects: {
    *fetch(_, { call, put }) {
      const response = yield call(queryUsers);
      yield put({
        type: 'save',
        payload: response
      });
    },
    *fetchCurrent(_, { call, put }) {
      const response = yield call(queryCurrent);
      yield put({
        type: 'saveCurrentUser',
        payload: response
      });
    }
  },

  reducers: {
    save(state, action) {
      return {
        ...state,
        list: action.payload
      };
    },
    saveCurrentUser(state, action) {
      return {
        ...state,
        currentUser: action.payload || {}
      };
    },
    changeCurrentUser(state, action) {
      storageAccount.set({
        ...state.currentUser,
        ...action.payload
      }); // 修改用户信息后需要保存
      return {
        ...state,
        currentUser: {
          ...state.currentUser,
          ...action.payload
        }
      };
    },
    changeNotifyCount(state, action) {
      return {
        ...state,
        currentUser: {
          ...state.currentUser,
          notifyCount: action.payload
        }
      };
    }
  }
};
