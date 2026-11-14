<template>
  <div style="padding: 30px; font-family: Arial; max-width: 640px;">
    <h2>Nueva receta</h2>

    <label>Nombre del platillo</label>
    <input v-model="form.nombre_platillo" maxlength="150" style="width: 100%; padding: 8px; margin-bottom: 12px;" />

    <label>Categoría</label>
    <select v-model="form.id_categoria" style="width: 100%; padding: 8px; margin-bottom: 12px;">
      <option disabled value="">Selecciona una categoría...</option>
      <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">
        {{ c.nombre_categoria }}
      </option>
    </select>

    <label>Tiempo de preparación (minutos)</label>
    <input v-model.number="form.tiempo_preparacion" type="number" min="1"
      style="width: 100%; padding: 8px; margin-bottom: 12px;" />

    <label>Dificultad</label>
    <select v-model="form.dificultad" style="width: 100%; padding: 8px; margin-bottom: 12px;">
      <option v-for="d in dificultades" :key="d" :value="d">{{ d }}</option>
    </select>

    <label>Porciones</label>
    <input v-model.number="form.porciones" type="number" min="1"
      style="width: 100%; padding: 8px; margin-bottom: 12px;" />

    <label>Estado</label>
    <select v-model="form.estado" style="width: 100%; padding: 8px; margin-bottom: 12px;">
      <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
    </select>
    <small style="display: block; margin: -8px 0 12px; color: #777;">
      Solo las recetas en estado "Publicado" se ven en la app Ionic.
    </small>

    <label>Cantidad / medida (opcional)</label>
    <input v-model="form.cantidad_medida" maxlength="100" style="width: 100%; padding: 8px; margin-bottom: 12px;" />

    <label>Ingredientes (Ctrl/Cmd + clic para elegir varios)</label>
    <select v-model="ingredientesSel" multiple size="6" style="width: 100%; padding: 8px; margin-bottom: 6px;">
      <option v-for="i in ingredientes" :key="i.id_ingrediente" :value="i.id_ingrediente">
        {{ i.nombre_ingrediente }}
      </option>
    </select>
    <div style="display: flex; gap: 6px; margin-bottom: 12px;">
      <input v-model="nuevoIngrediente" placeholder="¿No está? Escribe un ingrediente nuevo"
        style="flex: 1; padding: 8px;" @keyup.enter="agregarIngrediente" />
      <button type="button" @click="agregarIngrediente" style="padding: 8px 12px; cursor: pointer;">Agregar</button>
    </div>

    <label>Pasos</label>
    <div v-for="(paso, idx) in pasos" :key="idx" style="display: flex; gap: 6px; margin-bottom: 6px;">
      <input v-model="pasos[idx]" :placeholder="'Paso ' + (idx + 1)" style="flex: 1; padding: 8px;" />
      <button v-if="pasos.length > 1" type="button" @click="pasos.splice(idx, 1)" style="cursor: pointer;">✕</button>
    </div>
    <button type="button" @click="pasos.push('')" style="margin-bottom: 12px; cursor: pointer;">+ Agregar paso</button>
    <br>

    <label>Imagen (opcional, máx. 5 MB)</label><br>
    <input type="file" accept="image/*" @change="imagen = $event.target.files[0] || null"
      style="margin-bottom: 15px;" />
    <br>

    <p v-if="error" style="color: red; white-space: pre-line;">{{ error }}</p>

    <button @click="guardar" :disabled="guardando"
      style="background: #2e7d32; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
      {{ guardando ? 'Guardando...' : 'Guardar receta' }}
    </button>
    <button @click="$router.push('/recetas')" style="margin-left: 10px; padding: 8px 15px; cursor: pointer;">
      Cancelar
    </button>
  </div>
</template>

<script>
import api from '@/services/api';

export default {
  name: 'CrearRecetaView',
  data() {
    return {
      // Mismos valores que valida la API (RecetaController) y que usa la app Ionic
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
  async mounted() {
    try {
      const [c, i] = await Promise.all([api.get('/categorias'), api.get('/ingredientes')]);
      this.categorias = c.data;
      this.ingredientes = i.data;
    } catch (e) {
      console.error(e);
      this.error = 'No se pudieron cargar las categorías e ingredientes.';
    }
  },
  methods: {
    async agregarIngrediente() {
      const nombre = this.nuevoIngrediente.trim();
      if (!nombre) return;
      try {
        const { data } = await api.post('/ingredientes', { nombre_ingrediente: nombre });
        if (!this.ingredientes.some(i => i.id_ingrediente === data.id_ingrediente)) {
          this.ingredientes.push(data);
        }
        if (!this.ingredientesSel.includes(data.id_ingrediente)) {
          this.ingredientesSel.push(data.id_ingrediente);
        }
        this.nuevoIngrediente = '';
      } catch (e) {
        this.error = 'No se pudo agregar el ingrediente.';
      }
    },
    async guardar() {
      this.error = '';
      this.guardando = true;

      // La API recibe multipart/form-data (por la imagen)
      const fd = new FormData();
      Object.entries(this.form).forEach(([clave, valor]) => {
        if (valor !== '' && valor !== null) fd.append(clave, valor);
      });
      this.ingredientesSel.forEach(id => fd.append('ingredientes[]', id));
      this.pasos.filter(p => p.trim()).forEach(p => fd.append('pasos[]', p.trim()));
      if (this.imagen) fd.append('imagen', this.imagen);

      try {
        await api.post('/recetas', fd);
        this.$router.push('/recetas');
      } catch (e) {
        const errores = e.response && e.response.data && e.response.data.errors;
        this.error = errores
          ? Object.values(errores).flat().join('\n')
          : (e.response && e.response.data && e.response.data.message) || 'No se pudo guardar la receta.';
      } finally {
        this.guardando = false;
      }
    }
  }
};
</script>
