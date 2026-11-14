import axios from 'axios';
import { authService } from './auth';

const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'https://tastysteps.ethiodeploy.app/api',
  headers: { Accept: 'application/json' }, // Laravel responde siempre en JSON
});

// Interceptor para enviar el token automáticamente en cada petición
api.interceptors.request.use(config => {
  const token = authService.getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Si el token ya no sirve (401), se limpia la sesión y se vuelve al login
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401 && authService.getToken()) {
      authService.clearToken();
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
