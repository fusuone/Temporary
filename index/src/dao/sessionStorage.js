/**
 * sessionStorage
 */

function enabled() {
  return !!window.sessionStorage;
}

function get(key) {
  const data = sessionStorage.getItem(key);
  return data !== null ? JSON.parse(data) : {};
}

function set(key, data) {
  sessionStorage.setItem(key, JSON.stringify(data));
}

function remove(key) {
  return sessionStorage.removeItem(key);
}

function clearAll() {
  return sessionStorage.clear();
}

export default { enabled, get, set, remove, clearAll };
