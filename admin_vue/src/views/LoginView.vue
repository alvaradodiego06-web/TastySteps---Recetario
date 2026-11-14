<template>
  <div class="login-wrapper">
    <div class="main-card">
      <header class="navbar">
        <h1 class="brand-logo">TastySteps</h1>
      </header>

      <main class="login-container">
        <h2 class="form-title">Iniciar Sesión</h2>
        <p class="form-subtitle">Ingresa tus credenciales para administrar tus recetas</p>

        <!-- Mensaje de error dinámico -->
        <div v-if="errorMessage" class="error-message" role="alert">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="login" class="login-form">
          <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input id="email" type="email" v-model.trim="email" placeholder="ejemplo@correo.com" required />
          </div>

          <div class="form-group">
            <label for="password">Contraseña</label>
            <input id="password" type="password" v-model="password" placeholder="••••••••" required />
          </div>

          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Cargando...' : 'Entrar' }}
          </button>

          <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #737373;">
            ¿No tienes una cuenta? <router-link to="/register"
              style="color: #c84527; text-decoration: none; font-weight: 600;">Regístrate aquí</router-link>
          </p>
        </form>
      </main>
    </div>
  </div>
</template>

<script>
import api from '@/services/api';
import { authService } from '@/services/auth';

export default {
  name: 'LoginView',

  data() {
    return {
      email: '',
      password: '',
      loading: false,
      errorMessage: ''
    };
  },

  methods: {
    async login() {
      this.loading = true;
      this.errorMessage = '';

      try {
        const response = await api.post('/login', {
          correo: this.email,
          password: this.password
        });

        if (response.data.token) {
          authService.setToken(response.data.token);
          authService.setUser(response.data.usuario);
        }

        console.log('Login exitoso:', response.data);

        this.$router.replace('/recetas').catch(err => {
          if (err.name !== 'NavigationDuplicated') {
            throw err;
          }
        });

      } catch (error) {
        console.error('Error en login:', error.response?.data || error);

        this.errorMessage =
          error.response?.data?.mensaje ||
          'Correo o contraseña incorrectos.';

      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  background-color: #262626;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  box-sizing: border-box;
}

.main-card {
  width: 100%;
  max-width: 900px;
  background-color: #fbfbfa;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  min-height: 550px;
}

.navbar {
  padding: 25px 40px;
  display: flex;
  align-items: center;
}

.brand-logo {
  color: #c84527;
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0;
}

.login-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px 40px 60px 40px;
}

.form-title {
  font-size: 2rem;
  color: #2d2d2d;
  margin-bottom: 8px;
  font-weight: 700;
}

.form-subtitle {
  color: #737373;
  font-size: 0.95rem;
  margin-bottom: 20px;
}

.error-message {
  background-color: #ffe6e6;
  color: #d9534f;
  padding: 10px 15px;
  border-radius: 8px;
  font-size: 0.9rem;
  margin-bottom: 15px;
  width: 100%;
  max-width: 360px;
  text-align: center;
  box-sizing: border-box;
}

.login-form {
  width: 100%;
  max-width: 360px;
}

.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #404040;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #e5e5e5;
  border-radius: 25px;
  background-color: #ffffff;
  font-size: 0.95rem;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-group input:focus {
  border-color: #c84527;
}

.btn-primary {
  width: 100%;
  padding: 13px;
  background-color: #c84527;
  color: #ffffff;
  border: none;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #b03a1f;
}

.btn-primary:disabled {
  background-color: #e0a899;
  cursor: not-allowed;
}
</style>