import { readStore, writeStore, clearStore } from './storage.js';
import { seedData } from './seed-data.js';

const state = { data: null, currentUserId: null, effectiveRole: null };

export const initState = () => {
  state.data = readStore() || seedData();
  writeStore(state.data);
  return state;
};
export const getState = () => state;
export const persist = () => writeStore(state.data);
export const resetDemo = () => { clearStore(); state.data = seedData(); state.currentUserId = null; state.effectiveRole = null; persist(); };
export const login = (email, password) => {
  const user = state.data.users.find((u) => u.email === email && u.password === password);
  if (!user) return null;
  state.currentUserId = user.id;
  state.effectiveRole = user.role;
  return user;
};
export const logout = () => { state.currentUserId = null; state.effectiveRole = null; };
export const currentUser = () => state.data.users.find((u) => u.id === state.currentUserId);
export const setEffectiveRole = (role) => { state.effectiveRole = role; };
