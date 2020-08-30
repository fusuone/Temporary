/**
 * localStorage
 */

function enabled() {
  return !!window.localStorage;
}

function get(key) {
  const data = localStorage.getItem(key);
  return data !== null ? JSON.parse(data) : {};
}

function set(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

function remove(key) {
  return localStorage.removeItem(key);
}

function clearAll() {
  return localStorage.clear();
}

export default { enabled, get, set, remove, clearAll };
