export const authService = {
  setToken(token) {
    sessionStorage.setItem('auth_token', token);
  },
  getToken() {
    return sessionStorage.getItem('auth_token');
  },
  // Datos del usuario que inició sesión (id_usuario, Nombre, Rol...)
  setUser(usuario) {
    sessionStorage.setItem('auth_user', JSON.stringify(usuario || null));
  },
  getUser() {
    try {
      return JSON.parse(sessionStorage.getItem('auth_user'));
    } catch (e) {
      return null;
    }
  },
  esAdmin() {
    const u = this.getUser();
    return !!u && ['admin', 'administrador'].includes(String(u.Rol).toLowerCase());
  },
  clearToken() {
    sessionStorage.removeItem('auth_token');
    sessionStorage.removeItem('auth_user');
  },
  isAuthenticated() {
    return !!this.getToken();
  }
};
