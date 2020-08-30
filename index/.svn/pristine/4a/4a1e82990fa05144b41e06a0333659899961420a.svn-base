import { routerRedux } from 'dva/router';
import { stringify } from 'qs';
import { setAuthority } from '@/utils/authority';
import { getPageQuery } from '@/utils/utils';
import http from '@/utils/http';
import { reloadAuthorized } from '@/utils/Authorized';

import * as storageLogin from '@/dao/storageLogin';
import * as storageAccount from '@/dao/storageAccount';

async function __login(params) {
  return http({
    api: 'login',
    params
  }).then((result) => {
    const { status, msg, data } = result;
    if (status === '0') {
      const user = {
        ...data
        // 可以添加额外数据以便测试
        // notifyCount: 9, // 通知数量
      };
      window.adm = user.admin || user.phoneno;
      return { status: 'ok', data: user };
    }
    return { status: 'error', errmsg: msg };
  }).catch((error) => {
    return { status: undefined, errmsg: error.msg };
  });
}

function updateStorage(userInfo, loginStatus) {
  if (loginStatus === 'ok') {
    storageAccount.set(userInfo);
  } else {
    storageAccount.set({});
  }
  storageLogin.set({ status: loginStatus });
}

export default {
  namespace: 'login',

  state: {
    status: undefined, // undefined：默认 ok：登录成功 error：登录失败
    errmsg: ''
  },

  effects: {
    *login({ payload }, { call, put }) {
      const { status, errmsg, data } = yield call(__login, payload);
      yield put({
        type: 'changeLoginStatus',
        payload: { status, errmsg }
      });
      // 登录成功
      if (status === 'ok') {
        updateStorage(data, 'ok');
        //
        if(data.userno == "13690852319" || data.userno == "13538750770" || data.userno == "13726553043"){
          setAuthority('admin');
        }else{
          setAuthority('user');
        }
        reloadAuthorized();
        const urlParams = new URL(window.location.href);
        const params = getPageQuery();
        let { redirect } = params;
        if (redirect) {
          const redirectUrlParams = new URL(redirect);
          if (redirectUrlParams.origin === urlParams.origin) {
            redirect = redirect.substr(urlParams.origin.length);
            if (redirect.startsWith('/#')) {
              redirect = redirect.substr(2);
            }
          } else {
            window.location.href = redirect;
            return;
          }
        }
        yield put({
          type: 'user/saveCurrentUser',
          payload: data
        });
        yield put(routerRedux.replace(redirect || '/'));
      }
    },

    *logout(_, { put }) {
      updateStorage({}, undefined);
      yield put({
        type: 'changeLoginStatus',
        payload: {
          status: undefined,
          errmsg: ''
        }
      });
      reloadAuthorized();
      yield put(
        routerRedux.push({
          pathname: '/user/login',
          search: stringify({
            redirect: window.location.href
          })
        }),
      );
    }
  },

  reducers: {
    changeLoginStatus(state, { payload }) {
      // setAuthority('user');

      // if (loginUserno == "13690852319" || loginUserno == "13538750770" || loginUserno == "13726553043"){
      //   setAuthority("admin");
      // } else {
      //     setAuthority("user");
      // }

      return {
        ...state,
        status: payload.status,
        errmsg: payload.errmsg || ''
      };
    }
  }
};
