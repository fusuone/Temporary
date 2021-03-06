/**
 * 请求封装
 */

import axios from 'axios';
import { notification } from 'antd';

import Api from '../common/api';

// axios 默认配置
axios.defaults.timeout = 15000;

// 状态码
const statusCodeMaps = {
  403: '服务器禁止访问',
  404: '服务器没有此服务',
  500: '服务器出错',
  503: '服务器超时',
  504: '服务器没有响应'
};

const notifyTip = (bool, message) => {
  bool && notification.error({ message, duration: 2 });
};

export default (reqConfig = {}) => {
  return new Promise((resolve, reject) => {
    const { api: apiKey, url, extraOpts = {} } = reqConfig;
    const { isNotify = true } = extraOpts;
    delete reqConfig.extraOpts; // eslint-disable-line

    // 默认带上的参数
    const baseParams = { adm: window.adm || '' };

    // 请求
    axios({
      ...reqConfig,
      url: Api[apiKey] || url,
      params: { ...reqConfig.params, ...baseParams },
      data: reqConfig.data && reqConfig.data.toString() === '[object FormData]' ?
        reqConfig.data : JSON.stringify(reqConfig.data), // [object FormData] 为上传文件
      // 跨域设置
      withCredentials: true
      // 当 withCredentials=true 时
      // 必须指定 Access-Control-Allow-Credentials: true
      // Access-Control-Allow-Origin 不能为*，必须指定域名(如http://localhost:8000)
      // 当 withCredentials=false 时
      // Access-Control-Allow-Origin 可为*，也可以设置其它域名
    }).then((result) => {
      const { data, status } = result;
      if (status >= 200 && status < 300) {
        if (typeof data === 'object') {
          resolve(data);
        } else {
          const msg = '返回json格式错误';
          notifyTip(isNotify, msg);
          reject(data);
        }
      } else {
        const msg = statusCodeMaps[status] || 'Server Error';
        notifyTip(isNotify, msg);
        reject({ msg });
      }
    }).catch((error) => {
      console.log('request error', error); // eslint-disable-line
      let msg;
      if (error.code === 'ECONNABORTED' || error.message.indexOf('timeout') !== -1) {
        msg = '请求超时';
      } else if (error.code === undefined && error.message.indexOf('Network Error') !== -1) {
        msg = '网络异常';
      } else {
        msg = '未知错误';
      }
      notifyTip(isNotify, msg);
      reject({ msg });
    });
  });
};
