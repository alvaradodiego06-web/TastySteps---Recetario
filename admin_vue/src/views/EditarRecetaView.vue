<template>
    <div style="padding: 30px; font-family: Arial; max-width: 640px;">
        <h2>Editar receta</h2>
        <p v-if="cargando">Cargando receta...</p>

        <div v-else>
            <label>Nombre del platillo</label>
            <input v-model="form.nombre_platillo" maxlength="150"
                style="width: 100%; padding: 8px; margin-bottom: 12px;" />

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

            <label>Cantidad / medida (opcional)</label>
            <input v-model="form.cantidad_medida" maxlength="100"
                style="width: 100%; padding: 8px; margin-bottom: 12px;" />

            <label>Ingredientes (Ctrl/Cmd + clic para elegir varios)</label>
            <select v-model="ingredientesSel" multiple size="6" style="width: 100%; padding: 8px; margin-bottom: 6px;">
                <option v-for="i in ingredientes" :key="i.id_ingrediente" :value="i.id_ingrediente">
                    {{ i.nombre_ingrediente }}
                </option>
            </select>
            <div style="display: flex; gap: 6px; margin-bottom: 12px;">
                <input v-model="nuevoIngrediente" placeholder="¿No está? Escribe un ingrediente nuevo"
                    style="flex: 1; padding: 8px;" @keyup.enter="agregarIngrediente" />
                <button type="button" @click="agregarIngrediente"
                    style="padding: 8px 12px; cursor: pointer;">Agregar</button>
            </div>

            <label>Pasos</label>
            <div v-for="(paso, idx) in pasos" :key="idx" style="display: flex; gap: 6px; margin-bottom: 6px;">
                <input v-model="pasos[idx]" :placeholder="'Paso ' + (idx + 1)" style="flex: 1; padding: 8px;" />
                <button v-if="pasos.length > 1" type="button" @click="pasos.splice(idx, 1)"
                    style="cursor: pointer;">✕</button>
            </div>
            <button type="button" @click="pasos.push('')" style="margin-bottom: 12px; cursor: pointer;">+ Agregar
                paso</button>
            <br>

            <label>Imagen</label><br>
            <img v-if="imagenActual" :src="imagenActual" alt="Imagen actual"
                style="max-width: 200px; display: block; margin: 6px 0; border-radius: 6px;" />
            <small style="display: block; color: #777; margin-bottom: 6px;">
                Si no eliges una imagen nueva, se conserva la actual (máx. 5 MB).
            </small>
            <input type="file" accept="image/*" @change="imagen = $event.target.files[0] || null"
                style="margin-bottom: 15px;" />
            <br>

            <p v-if="error" style="color: red; white-space: pre-line;">{{ error }}</p>

            <button @click="guardar" :disabled="guardando"
                style="background: #1565c0; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
                {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
            </button>
            <button @click="$router.push('/recetas')" style="margin-left: 10px; padding: 8px 15px; cursor: pointer;">
                Cancelar
            </button>
        </div>
    </div>
</template>

<script>
import api from '@/services/api';

export default {
    name: 'EditarRecetaView',
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
            imagenActual: '',
            cargando: true,
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
            const id = this.$route.params.id;
            const [c, i, r] = await Promise.all([
                api.get('/categorias'),
                api.get('/ingredientes'),
                api.get(`/recetas/${id}`)
            ]);
            this.categorias = c.data;
            this.ingredientes = i.data;

            const receta = r.data.receta;
            this.form = {
                id_categoria: receta.id_categoria,
                nombre_platillo: receta.nombre_platillo,
                tiempo_preparacion: receta.tiempo_preparacion,
                dificultad: receta.dificultad,
                porciones: receta.porciones,
                estado: receta.estado,
                cantidad_medida: receta.cantidad_medida || ''
            };
            this.ingredientesSel = (receta.ingredientes || []).map(x => x.id_ingrediente);
            this.pasos = (receta.pasos || []).map(p => p.descripcion_paso);
            if (this.pasos.length === 0) this.pasos = [''];

            if (receta.imagen_url) {
                const base = api.defaults.baseURL.replace(/\/api\/?$/, '');
                this.imagenActual = `${base}/storage/${receta.imagen_url}`;
            }
        } catch (e) {
            console.error(e);
            this.error = 'No se pudo cargar la receta.';
        } finally {
            this.cargando = false;
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

            const pasosLimpios = this.pasos.map(p => p.trim()).filter(p => p);
            if (this.ingredientesSel.length === 0 || pasosLimpios.length === 0) {
                this.error = 'Selecciona al menos un ingrediente y escribe al menos un paso.';
                return;
            }

            this.guardando = true;

            const fd = new FormData();
            // PHP no lee archivos en peticiones PUT, por eso se envía POST con _method=PUT
            fd.append('_method', 'PUT');
            Object.entries(this.form).forEach(([clave, valor]) => {
                fd.append(clave, valor === null ? '' : valor);
            });
            this.ingredientesSel.forEach(id => fd.append('ingredientes[]', id));
            pasosLimpios.forEach(p => fd.append('pasos[]', p));
            if (this.imagen) fd.append('imagen', this.imagen);

            try {
                await api.post(`/recetas/${this.$route.params.id}`, fd);
                this.$router.push('/recetas');
            } catch (e) {
                const errores = e.response && e.response.data && e.response.data.errors;
                this.error = errores
                    ? Object.values(errores).flat().join('\n')
                    : (e.response && e.response.data && (e.response.data.mensaje || e.response.data.message))
                    || 'No se pudieron guardar los cambios.';
            } finally {
                this.guardando = false;
            }
        }
    }
};
</script>
