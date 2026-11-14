<template>
  <div class="home-container">
    <div class="welcome-card">
      <h1 class="title">Bienvenida a TastySteps</h1>
      <p class="subtitle">Tu plataforma inteligente para la gestión y exploración de recetas.</p>
      
      <div class="buttons">
        <router-link to="/login" class="btn btn-login">Iniciar Sesión</router-link>
        <router-link to="/register" class="btn btn-register">Registrarse</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeView'
}
</script>

<style scoped>
.home-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #262626;
}

.welcome-card {
  background: #fbfbfa;
  padding: 50px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  max-width: 600px;
  width: 100%;
}

.title {
  color: #c84527;
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 15px;
}

.subtitle {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 30px;
}

.buttons {
  display: flex;
  justify-content: center;
  gap: 20px;
}

.btn {
  padding: 12px 30px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-login {
  background-color: #c84527;
  color: white;
}

.btn-login:hover {
  background-color: #a7371f;
}

.btn-register {
  background-color: #333;
  color: white;
}

.btn-register:hover {
  background-color: #1a1a1a;
}
</style>