import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'sanctum_token';

export const getApiUrl = () => {
  if (typeof process !== 'undefined' && process.env?.EXPO_PUBLIC_API_URL) {
    return process.env.EXPO_PUBLIC_API_URL.replace(/\/$/, '');
  }
  return 'http://localhost:8000';
};

export const getToken = async () => SecureStore.getItemAsync(TOKEN_KEY);
export const setToken = async (token) => SecureStore.setItemAsync(TOKEN_KEY, token);
export const removeToken = async () => SecureStore.deleteItemAsync(TOKEN_KEY);

export const api = async (path, options = {}) => {
  const base = getApiUrl();
  const token = await getToken();
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...options.headers,
  };
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, { ...options, headers });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw { status: res.status, ...data };
  return data;
};

export const apiMultipart = async (path, formData, options = {}) => {
  const base = getApiUrl();
  const token = await getToken();
  const headers = { Accept: 'application/json', ...options.headers };
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, { method: 'POST', body: formData, headers, ...options });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw { status: res.status, ...data };
  return data;
};
