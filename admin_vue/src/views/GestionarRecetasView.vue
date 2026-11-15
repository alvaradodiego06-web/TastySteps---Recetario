<template>
  <div style="padding: 30px; font-family: Arial;">
    <h2>Panel de Administración - Gestión de Recetas</h2>
    <button @click="logout"
      style="background: #c84527; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; float: right;">
      Cerrar Sesión
    </button>
    <button @click="$router.push('/recetas/nueva')"
      style="background: #2e7d32; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 15px;">
      + Nueva receta
    </button>
    <button @click="verEnIonic"
      style="background: #333; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; margin-bottom: 15px;">
      Ver recetas
    </button>
    <br><br>

    <p v-if="mensaje" style="color: #c84527;">{{ mensaje }}</p>

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background: #f4f4f4;">
          <th>ID</th>
          <th>Nombre del Platillo</th>
          <th>Categoría</th>
          <th>Estado</th>
          <th>Autor</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="receta in recetas" :key="receta.id_receta">
          <td>{{ receta.id_receta }}</td>
          <td>{{ receta.nombre_platillo }}</td>
          <td>{{ receta.categoria ? receta.categoria.nombre_categoria : '—' }}</td>
          <td>{{ receta.estado }}</td>
          <td>{{ receta.usuario ? receta.usuario.Nombre : '—' }}</td>
          <td>
            <template v-if="puedeEliminar(receta)">
              <button @click="$router.push('/recetas/' + receta.id_receta + '/editar')"
                style="background: #1565c0; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 6px;">
                Editar
              </button>
              <button @click="eliminarReceta(receta.id_receta)"
                style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                Eliminar
              </button>
            </template>
            <span v-else style="color: #999;">Solo lectura</span>
          </td>
        </tr>
        <tr v-if="!recetas || recetas.length === 0">
          <td colspan="6" style="text-align: center; color: #777;">No hay recetas registradas todavía.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import api from '@/services/api';
import { authService } from '@/services/auth';

export default {
  name: 'GestionarRecetasView',
  data() {
    return {
      recetas: [],
      mensaje: '',
      // Si no hay VUE_APP_IONIC_URL, usa el mismo equipo desde el que se abrió esta página (puerto 8100)
      ionicUrl: (process.env.VUE_APP_IONIC_URL
        || `${window.location.protocol}//${window.location.hostname}:8100`) + '/catalog'
    };
  },
  mounted() {
    this.cargarRecetas();
  },
  methods: {
    async cargarRecetas() {
      try {
        const response = await api.get('/recetas');
        this.recetas = response.data.recetas || [];
      } catch (error) {
        console.error('Error al cargar recetas:', error);
        this.recetas = [];
        this.mensaje = 'No se pudieron cargar las recetas.';
      }
    },
    // Mismo criterio que la API: el dueño de la receta o un administrador
    puedeEliminar(receta) {
      const usuario = authService.getUser();
      return authService.esAdmin() || (usuario && receta.id_usuario === usuario.id_usuario);
    },
    async eliminarReceta(id) {
      if (!confirm('¿Estás segura de eliminar esta receta?')) return;
      this.mensaje = '';
      try {
        await api.delete(`/recetas/${id}`);
        this.cargarRecetas();
      } catch (error) {
        console.error('Error al eliminar:', error);
        this.mensaje = (error.response && error.response.data && error.response.data.mensaje)
          || 'No se pudo eliminar la receta.';
      }
    },
    verEnIonic() {
      // Misma pestaña; ?desde=admin le avisa a Ionic que viene del panel
      window.location.href = this.ionicUrl + '?desde=admin';
    },
    async logout() {
      try {
        await api.post('/logout');
      } catch (error) {
        // Si falla (token vencido, sin red) igual se cierra la sesión local
      }
      authService.clearToken();
      this.$router.push('/login');
    }
  }
};
</script>