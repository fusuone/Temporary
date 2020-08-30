/**
 * 登录账户
 */

import storage from './sessionStorage';

const KEY = '@Login';

export const get = () => {
  try {
    const data = storage.get(KEY);
    return typeof data === 'object' ? data : {};
  } catch (error) {
    return {};
  }
};

export const set = (data) => {
  try {
    storage.set(KEY, data);
  } catch (error) {
    //
  }
};

export const del = () => {
  try {
    storage.remove(KEY);
  } catch (error) {
    //
  }
};
