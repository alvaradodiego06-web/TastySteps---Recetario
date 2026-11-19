<template>
  <div class="receta-page">
    <div class="receta-container">
      <header class="page-header">
        <button class="back-link" @click="$router.push('/recetas')">
          ← Volver a recetas
        </button>

        <span class="eyebrow">TASTYSTEPS · RECETARIO</span>
        <h1>Crear nueva receta</h1>
        <p>Comparte una nueva preparación en tu recetario.</p>
      </header>

      <div class="form-card">
        <div class="section-heading">
          <span class="section-number">01</span>
          <div>
            <h2>Información general</h2>
            <p>Completa los datos principales del platillo.</p>
          </div>
        </div>

        <div class="form-group">
          <label>Nombre del platillo</label>
          <input v-model="form.nombre_platillo" maxlength="150" placeholder="Ej. Pasta con salsa de tomate" />
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Categoría</label>
            <select v-model="form.id_categoria">
              <option disabled value="">
                Selecciona una categoría...
              </option>
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">
                {{ c.nombre_categoria }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Tiempo de preparación (min)</label>
            <input v-model.number="form.tiempo_preparacion" type="number" min="1" />
          </div>

          <div class="form-group">
            <label>Dificultad</label>
            <select v-model="form.dificultad">
              <option v-for="d in dificultades" :key="d" :value="d">
                {{ d }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Porciones</label>
            <input v-model.number="form.porciones" type="number" min="1" />
          </div>

          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado">
              <option v-for="e in estados" :key="e" :value="e">
                {{ e }}
              </option>
            </select>
            <small>
              Solo las recetas publicadas aparecen en Ionic.
            </small>
          </div>

          <div class="form-group">
            <label>Cantidad / medida (opcional)</label>
            <input v-model="form.cantidad_medida" maxlength="100" placeholder="Ej. 250 gramos" />
          </div>
        </div>

        <div class="section-divider"></div>

        <div class="section-heading">
          <span class="section-number">02</span>
          <div>
            <h2>Ingredientes</h2>
            <p>Selecciona los ingredientes de tu receta.</p>
          </div>
        </div>

        <div class="form-group">
          <label>Seleccionar ingredientes</label>
          <select v-model="ingredientesSel" multiple size="6" class="multiple-select">
            <option v-for="i in ingredientes" :key="i.id_ingrediente" :value="i.id_ingrediente">
              {{ i.nombre_ingrediente }}
            </option>
          </select>
          <small>
            Mantén Ctrl o Cmd presionado para seleccionar varios.
          </small>
        </div>

        <div class="input-action-row">
          <input v-model="nuevoIngrediente" placeholder="Escribe un ingrediente nuevo..."
            @keyup.enter="agregarIngrediente" />
          <button type="button" class="btn btn-secondary" @click="agregarIngrediente">
            + Agregar
          </button>
        </div>

        <div class="section-divider"></div>

        <div class="section-heading">
          <span class="section-number">03</span>
          <div>
            <h2>Preparación</h2>
            <p>Describe cada paso para preparar el platillo.</p>
          </div>
        </div>

        <div v-for="(paso, idx) in pasos" :key="idx" class="paso-row">
          <span class="paso-numero">{{ idx + 1 }}</span>

          <input v-model="pasos[idx]" :placeholder="'Descripción del paso ' + (idx + 1)" />

          <button v-if="pasos.length > 1" type="button" class="btn-remove" title="Eliminar paso"
            @click="pasos.splice(idx, 1)">
            ✕
          </button>
        </div>

        <button type="button" class="btn btn-outline" @click="pasos.push('')">
          + Agregar paso
        </button>

        <div class="section-divider"></div>

        <div class="section-heading">
          <span class="section-number">04</span>
          <div>
            <h2>Imagen del platillo</h2>
            <p>Agrega una fotografía para tu receta.</p>
          </div>
        </div>

        <div class="form-group">
          <label>Seleccionar imagen (opcional)</label>
          <input type="file" accept="image/*" class="file-input" @change="imagen = $event.target.files[0] || null" />
          <small>Tamaño máximo permitido: 5 MB.</small>
        </div>

        <p v-if="error" ref="alerta" class="alert-error">
          {{ error }}
        </p>

        <div class="form-actions">
          <button class="btn btn-primary" :disabled="guardando" @click="guardar">
            {{ guardando ? 'Guardando...' : 'Guardar receta' }}
          </button>

          <button class="btn btn-cancel" @click="$router.push('/recetas')">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api';

export default {
  name: 'CrearRecetaView',

  data() {
    return {
      dificultades: ['Fácil', 'Media', 'Difícil'],
      estados: ['Publicado', 'Borrador', 'Oculto'],
      categorias: [],
      ingredientes: [],
      ingredientesSel: [],
      nuevoIngrediente: '',
      pasos: [''],
      imagen: null,
      error: '',
      guardando: false,

      form: {
        id_categoria: '',
        nombre_platillo: '',
        tiempo_preparacion: 30,
        dificultad: 'Fácil',
        porciones: 2,
        estado: 'Publicado',
        cantidad_medida: ''
      }
    };
  },

  mounted() {
    this.cargarCatalogos();
  },

  methods: {
    mostrarError(texto) {
      this.error = texto;

      this.$nextTick(() => {
        const el = this.$refs.alerta;
        if (el) {
          el.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }
      });
    },

    async cargarCatalogos() {
      this.error = '';

      try {
        const c = await api.get('/categorias');
        this.categorias = c.data;
      } catch (e) {
        console.error('Categorías:', e);

        this.error =
          'No se pudieron cargar las categorías. ' +
          (e.response
            ? 'Código ' + e.response.status
            : 'Sin respuesta del servidor (red o CORS).');
      }

      try {
        const i = await api.get('/ingredientes');
        this.ingredientes = i.data;
      } catch (e) {
        console.error('Ingredientes:', e);

        this.error +=
          (this.error ? '\n' : '') +
          'No se pudieron cargar los ingredientes.';
      }
    },

    async agregarIngrediente() {
      const nombre = this.nuevoIngrediente.trim();
      if (!nombre) return;

      try {
        const { data } = await api.post('/ingredientes', {
          nombre_ingrediente: nombre
        });

        if (!this.ingredientes.some(
          i => i.id_ingrediente === data.id_ingrediente
        )) {
          this.ingredientes.push(data);
        }

        if (!this.ingredientesSel.includes(data.id_ingrediente)) {
          this.ingredientesSel.push(data.id_ingrediente);
        }

        this.nuevoIngrediente = '';
      } catch (e) {
        this.mostrarError(
          'No se pudo agregar el ingrediente. ' +
          (e.response
            ? 'Código ' + e.response.status
            : 'Sin respuesta del servidor.')
        );
      }
    },

    async guardar() {
      this.error = '';

      const pasosLimpios = this.pasos
        .map(p => p.trim())
        .filter(p => p);

      if (!this.form.nombre_platillo.trim()) {
        return this.mostrarError('Escribe el nombre del platillo.');
      }

      if (!this.form.id_categoria) {
        return this.mostrarError('Selecciona una categoría.');
      }

      if (this.ingredientesSel.length === 0) {
        return this.mostrarError(
          'Selecciona al menos un ingrediente.'
        );
      }

      if (pasosLimpios.length === 0) {
        return this.mostrarError('Escribe al menos un paso.');
      }

      this.guardando = true;

      const fd = new FormData();

      Object.entries(this.form).forEach(([clave, valor]) => {
        if (valor !== '' && valor !== null) {
          fd.append(clave, valor);
        }
      });

      this.ingredientesSel.forEach(id => {
        fd.append('ingredientes[]', id);
      });

      pasosLimpios.forEach(p => {
        fd.append('pasos[]', p);
      });

      if (this.imagen) {
        fd.append('imagen', this.imagen);
      }

      try {
        await api.post('/recetas', fd);
        this.$router.push('/recetas');
      } catch (e) {
        const errores =
          e.response &&
          e.response.data &&
          e.response.data.errors;

        this.mostrarError(
          errores
            ? Object.values(errores).flat().join('\n')
            : (e.response &&
              e.response.data &&
              (e.response.data.mensaje ||
                e.response.data.message)) ||
            'No se pudo guardar la receta.'
        );
      } finally {
        this.guardando = false;
      }
    }
  }
};
</script>

<style scoped>
.receta-page {
  min-height: 100vh;
  background: #fff8f1;
  padding: 40px 20px 70px;
  font-family: 'Segoe UI', Arial, sans-serif;
  color: #332822;
  box-sizing: border-box;
}

.receta-page * {
  box-sizing: border-box;
}

.receta-container {
  max-width: 850px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 28px;
}

.back-link {
  display: block;
  background: none;
  border: none;
  color: #bd4a2c;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 25px;
  padding: 0;
}

.back-link:hover {
  color: #922d17;
}

.eyebrow {
  display: block;
  color: #c84527;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 2px;
  margin-bottom: 10px;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 34px;
  font-weight: 750;
  color: #32241f;
}

.page-header p {
  color: #8b7d75;
  font-size: 15px;
  margin: 0;
}

.form-card {
  background: white;
  padding: 38px;
  border-radius: 18px;
  border: 1px solid #f1e4d9;
  box-shadow: 0 8px 30px rgba(80, 40, 20, 0.07);
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.section-number {
  width: 43px;
  height: 43px;
  background: #fff0e8;
  color: #c84527;
  border-radius: 12px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: 750;
  font-size: 14px;
  flex-shrink: 0;
}

.section-heading h2 {
  font-size: 20px;
  margin: 0 0 5px;
  color: #382923;
}

.section-heading p {
  font-size: 13px;
  color: #95867d;
  margin: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 20px;
}

.form-group {
  margin-bottom: 22px;
  min-width: 0;
}

.form-group label {
  display: block;
  font-weight: 650;
  font-size: 13px;
  margin-bottom: 9px;
  color: #55433a;
}

.receta-page input:not([type="file"]),
.receta-page select {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #e7dcd4;
  border-radius: 9px;
  background: #fffdfa;
  font-size: 14px;
  font-family: inherit;
  color: #39302b;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.receta-page input:focus,
.receta-page select:focus {
  border-color: #c84527;
  box-shadow: 0 0 0 3px rgba(200, 69, 39, 0.1);
}

.receta-page input::placeholder {
  color: #b1a6a0;
}

.form-group small {
  display: block;
  color: #95867e;
  font-size: 12px;
  margin-top: 7px;
  line-height: 1.5;
}

.multiple-select {
  min-height: 155px;
}

.multiple-select option {
  padding: 7px;
}

.section-divider {
  height: 1px;
  background: #f1e8e1;
  margin: 35px 0;
}

.input-action-row {
  display: flex;
  gap: 10px;
  align-items: stretch;
}

.input-action-row input {
  flex: 1;
  min-width: 0;
}

.btn {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 12px 20px;
  border: none;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 650;
  font-family: inherit;
  transition: background-color 0.2s, transform 0.2s;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #c84527;
  color: white;
}

.btn-primary:hover {
  background: #a9341b;
}

.btn-secondary {
  background: #2e7d32;
  color: white;
  white-space: nowrap;
}

.btn-secondary:hover {
  background: #236328;
}

.btn-outline {
  background: #fff;
  border: 1px dashed #ce8069;
  color: #bf4829;
  margin-top: 10px;
}

.btn-outline:hover {
  background: #fff1e9;
}

.btn-cancel {
  background: #f4efeb;
  color: #6e6058;
}

.btn-cancel:hover {
  background: #e9e0da;
}

.paso-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.paso-row input {
  flex: 1;
  min-width: 0;
}

.paso-numero {
  width: 34px;
  height: 34px;
  background: #f8eee6;
  color: #b75b37;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.btn-remove {
  width: 36px;
  height: 36px;
  background: #ffefec;
  color: #bc3928;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
}

.btn-remove:hover {
  background: #ffdcd6;
}

.file-input {
  display: block;
  width: 100%;
  padding: 13px;
  border: 1px dashed #dfb6a4;
  border-radius: 9px;
  background: #fffaf6;
  font-family: inherit;
  font-size: 13px;
}

.alert-error {
  padding: 15px;
  background: #fff0ed;
  color: #ae3524;
  border: 1px solid #f1c0b5;
  border-radius: 9px;
  white-space: pre-line;
  font-size: 13px;
  margin-top: 25px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 12px;
  border-top: 1px solid #f1e8e1;
  padding-top: 25px;
  margin-top: 35px;
}

@media (max-width: 650px) {
  .receta-page {
    padding: 20px 12px 45px;
  }

  .form-card {
    padding: 22px 17px;
  }

  .page-header h1 {
    font-size: 27px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .input-action-row {
    flex-direction: column;
  }

  .form-actions .btn {
    flex: 1;
  }
}
</style>
