const KEY = 'restinnov-demo-v1';
export const readStore = () => JSON.parse(localStorage.getItem(KEY) || 'null');
export const writeStore = (data) => localStorage.setItem(KEY, JSON.stringify(data));
export const clearStore = () => localStorage.removeItem(KEY);
export const LANG_KEY = 'restinnov-lang';
