<template>
  <div class="gestion-page">
    <div class="gestion-container">
      <header class="page-header">
        <div>
          <span class="eyebrow">TASTYSTEPS · ADMINISTRACIÓN</span>
          <h1>Gestión de recetas</h1>
          <p>Administra y organiza las recetas de tu recetario.</p>
        </div>

        <button class="btn btn-logout" @click="logout">
          Cerrar sesión
        </button>
      </header>

      <section class="content-card">
        <div class="card-header">
          <div>
            <h2>Recetas registradas</h2>
            <p>
              {{ recetas.length }}
              {{ recetas.length === 1 ? 'receta disponible' : 'recetas disponibles' }}
            </p>
          </div>

          <div class="header-actions">
            <button class="btn btn-primary" @click="$router.push('/recetas/nueva')">
              + Nueva receta
            </button>

            <button class="btn btn-dark" @click="verEnIonic">
              Ver recetas
            </button>
          </div>
        </div>

        <div v-if="mensaje" class="alert-error">
          {{ mensaje }}
        </div>

        <div class="table-wrapper">
          <table class="recetas-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre del platillo</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Autor</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="receta in recetas" :key="receta.id_receta">
                <td class="id-cell">
                  #{{ receta.id_receta }}
                </td>

                <td class="nombre-cell">
                  {{ receta.nombre_platillo }}
                </td>

                <td>
                  {{ receta.categoria
                    ? receta.categoria.nombre_categoria
                    : '—' }}
                </td>

                <td>
                  <span class="estado-badge" :class="{
                    'estado-publicado': receta.estado === 'Publicado',
                    'estado-borrador': receta.estado === 'Borrador',
                    'estado-oculto': receta.estado === 'Oculto'
                  }">
                    {{ receta.estado }}
                  </span>
                </td>

                <td>
                  {{ receta.usuario
                    ? receta.usuario.Nombre
                    : '—' }}
                </td>

                <td>
                  <div v-if="puedeEliminar(receta)" class="table-actions">
                    <button class="btn btn-edit" @click="$router.push('/recetas/' + receta.id_receta + '/editar')">
                      Editar
                    </button>

                    <button class="btn btn-delete" @click="eliminarReceta(receta.id_receta)">
                      Eliminar
                    </button>
                  </div>

                  <span v-else class="solo-lectura">
                    Solo lectura
                  </span>
                </td>
              </tr>

              <tr v-if="!recetas || recetas.length === 0">
                <td colspan="6" class="empty-state">
                  No hay recetas registradas todavía.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
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
      ionicUrl: (
        process.env.VUE_APP_IONIC_URL ||
        `${window.location.protocol}//${window.location.hostname}:8100`
      ) + '/catalog'
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

    puedeEliminar(receta) {
      const usuario = authService.getUser();

      return authService.esAdmin() ||
        (usuario && receta.id_usuario === usuario.id_usuario);
    },

    async eliminarReceta(id) {
      if (!confirm('¿Estás segura de eliminar esta receta?')) return;

      this.mensaje = '';

      try {
        await api.delete(`/recetas/${id}`);
        await this.cargarRecetas();
      } catch (error) {
        console.error('Error al eliminar:', error);

        this.mensaje =
          (error.response &&
            error.response.data &&
            error.response.data.mensaje) ||
          'No se pudo eliminar la receta.';
      }
    },

    verEnIonic() {
      window.location.href = this.ionicUrl + '?desde=admin';
    },

    async logout() {
      try {
        await api.post('/logout');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }

      authService.clearToken();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.gestion-page {
  min-height: 100vh;
  background: #fff8f1;
  padding: 45px 24px;
  font-family: 'Segoe UI', Arial, sans-serif;
  color: #333;
  box-sizing: border-box;
}

.gestion-page * {
  box-sizing: border-box;
}

.gestion-container {
  max-width: 1250px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
}

.eyebrow {
  display: block;
  color: #c84527;
  font-weight: 700;
  font-size: 12px;
  letter-spacing: 2px;
  margin-bottom: 12px;
}

.page-header h1 {
  font-size: 34px;
  color: #30231f;
  margin: 0 0 10px;
  font-weight: 750;
}

.page-header p {
  color: #81756f;
  font-size: 15px;
  margin: 0;
}

.content-card {
  background: #fff;
  border-radius: 18px;
  padding: 30px;
  box-shadow: 0 8px 30px rgba(80, 40, 20, 0.07);
  border: 1px solid #f3e5da;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 25px;
}

.card-header h2 {
  margin: 0 0 6px;
  font-size: 21px;
  color: #33251f;
}

.card-header p {
  margin: 0;
  color: #8a7e76;
  font-size: 14px;
}

.header-actions,
.table-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 11px 18px;
  border: none;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  transition: background-color 0.2s, transform 0.2s;
  white-space: nowrap;
}

.btn:hover {
  transform: translateY(-2px);
}

.btn-primary {
  background: #c84527;
  color: #fff;
}

.btn-primary:hover {
  background: #a9341b;
}

.btn-dark {
  background: #342b28;
  color: white;
}

.btn-dark:hover {
  background: #51423c;
}

.btn-logout {
  background: #fff;
  color: #c84527;
  border: 1px solid #e9b9ab;
}

.btn-logout:hover {
  background: #fff0ea;
}

.btn-edit {
  background: #e8f1ff;
  color: #2163af;
  padding: 8px 13px;
}

.btn-edit:hover {
  background: #d4e6ff;
}

.btn-delete {
  background: #ffebea;
  color: #c43932;
  padding: 8px 13px;
}

.btn-delete:hover {
  background: #ffd8d5;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #f0e5dc;
}

.recetas-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  min-width: 780px;
}

.recetas-table thead {
  background: #fcf2eb;
}

.recetas-table th {
  padding: 17px 16px;
  color: #805342;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.recetas-table td {
  padding: 17px 16px;
  font-size: 14px;
  color: #594c46;
  border-top: 1px solid #f3ece7;
}

.recetas-table tbody tr:hover {
  background: #fffaf5;
}

.id-cell {
  font-weight: 600;
  color: #ad8270 !important;
}

.nombre-cell {
  font-weight: 700;
  color: #322823 !important;
}

.estado-badge {
  display: inline-block;
  padding: 6px 11px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 650;
  background: #f1f1f1;
  color: #555;
}

.estado-publicado {
  background: #e4f5e8;
  color: #267e3d;
}

.estado-borrador {
  background: #fff2d8;
  color: #a16b13;
}

.estado-oculto {
  background: #eeeef2;
  color: #666674;
}

.solo-lectura {
  color: #a6a09d;
  font-size: 13px;
  font-style: italic;
}

.empty-state {
  text-align: center;
  padding: 45px !important;
  color: #8e817b !important;
}

.alert-error {
  padding: 14px 18px;
  margin-bottom: 20px;
  background: #fff0ed;
  border: 1px solid #f3c0b5;
  border-radius: 9px;
  color: #b63720;
}

@media (max-width: 768px) {
  .gestion-page {
    padding: 20px 12px;
  }

  .page-header,
  .card-header {
    flex-direction: column;
    align-items: stretch;
  }

  .page-header h1 {
    font-size: 27px;
  }

  .content-card {
    padding: 18px 12px;
  }

  .header-actions .btn {
    flex: 1;
  }

  .btn-logout {
    align-self: flex-start;
  }
}
</style>
