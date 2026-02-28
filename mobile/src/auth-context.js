import React, { createContext, useContext, useState, useEffect } from 'react';
import { getToken, setToken, removeToken } from './api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getToken().then((token) => {
      setUser(token ? { token } : null);
      setLoading(false);
    });
  }, []);

  const login = async (email, password) => {
    const base = typeof process !== 'undefined' && process.env?.EXPO_PUBLIC_API_URL
      ? process.env.EXPO_PUBLIC_API_URL.replace(/\/$/, '')
      : 'http://localhost:8000';
    const res = await fetch(`${base}/api/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    const data = await res.json();
    if (!res.ok) throw data;
    const token = data.token;
    await setToken(token);
    setUser({ token });
    return data;
  };

  const logout = async () => {
    await removeToken();
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
};
