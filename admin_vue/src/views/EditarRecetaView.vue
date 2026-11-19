<template>
    <div class="receta-page">
        <div class="receta-container">
            <header class="page-header">
                <button class="back-link" @click="$router.push('/recetas')">
                    ← Volver a recetas
                </button>

                <span class="eyebrow">TASTYSTEPS · RECETARIO</span>
                <h1>Editar receta</h1>
                <p>Actualiza la información de tu preparación.</p>
            </header>

            <div class="form-card">
                <div v-if="cargando" class="loading-state">
                    Cargando información de la receta...
                </div>

                <div v-else>
                    <div class="section-heading">
                        <span class="section-number">01</span>
                        <div>
                            <h2>Información general</h2>
                            <p>Modifica los datos principales del platillo.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre del platillo</label>
                        <input v-model="form.nombre_platillo" maxlength="150" placeholder="Nombre de la receta" />
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
                            <p>Actualiza los ingredientes de la receta.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ingredientes seleccionados</label>
                        <select v-model="ingredientesSel" multiple size="6" class="multiple-select">
                            <option v-for="i in ingredientes" :key="i.id_ingrediente" :value="i.id_ingrediente">
                                {{ i.nombre_ingrediente }}
                            </option>
                        </select>
                        <small>
                            Utiliza Ctrl o Cmd para seleccionar varios ingredientes.
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
                            <p>Modifica los pasos de elaboración.</p>
                        </div>
                    </div>

                    <div v-for="(paso, idx) in pasos" :key="idx" class="paso-row">
                        <span class="paso-numero">
                            {{ idx + 1 }}
                        </span>

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
                            <p>Conserva o reemplaza la fotografía actual.</p>
                        </div>
                    </div>

                    <div v-if="imagenActual" class="imagen-container">
                        <label>Imagen actual</label>
                        <img :src="imagenActual" alt="Imagen actual de la receta" class="imagen-actual" />
                    </div>

                    <div class="form-group">
                        <label>Seleccionar nueva imagen (opcional)</label>
                        <input type="file" accept="image/*" class="file-input"
                            @change="imagen = $event.target.files[0] || null" />
                        <small>
                            Si no seleccionas otra imagen, se conservará la actual.
                            Tamaño máximo: 5 MB.
                        </small>
                    </div>

                    <p v-if="error" class="alert-error">
                        {{ error }}
                    </p>

                    <div class="form-actions">
                        <button class="btn btn-primary" :disabled="guardando" @click="guardar">
                            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                        </button>

                        <button class="btn btn-cancel" @click="$router.push('/recetas')">
                            Cancelar
                        </button>
                    </div>
                </div>

                <p v-if="cargando && error" class="alert-error">
                    {{ error }}
                </p>
            </div>
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

            this.ingredientesSel = (receta.ingredientes || [])
                .map(x => x.id_ingrediente);

            this.pasos = (receta.pasos || [])
                .map(p => p.descripcion_paso);

            if (this.pasos.length === 0) {
                this.pasos = [''];
            }

            if (receta.imagen_url) {
                const base = api.defaults.baseURL.replace(/\/api\/?$/, '');
                this.imagenActual =
                    `${base}/storage/${receta.imagen_url}`;
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
                this.error = 'No se pudo agregar el ingrediente.';
            }
        },

        async guardar() {
            this.error = '';

            const pasosLimpios = this.pasos
                .map(p => p.trim())
                .filter(p => p);

            if (
                this.ingredientesSel.length === 0 ||
                pasosLimpios.length === 0
            ) {
                this.error =
                    'Selecciona al menos un ingrediente y escribe al menos un paso.';
                return;
            }

            this.guardando = true;

            const fd = new FormData();

            // Laravel recibe POST con _method=PUT para actualizar.
            fd.append('_method', 'PUT');

            Object.entries(this.form).forEach(([clave, valor]) => {
                fd.append(clave, valor === null ? '' : valor);
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
                await api.post(`/recetas/${this.$route.params.id}`, fd);
                this.$router.push('/recetas');
            } catch (e) {
                const errores =
                    e.response &&
                    e.response.data &&
                    e.response.data.errors;

                this.error = errores
                    ? Object.values(errores).flat().join('\n')
                    : (e.response &&
                        e.response.data &&
                        (e.response.data.mensaje ||
                            e.response.data.message)) ||
                    'No se pudieron guardar los cambios.';
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
    background: #fff;
    padding: 38px;
    border-radius: 18px;
    border: 1px solid #f1e4d9;
    box-shadow: 0 8px 30px rgba(80, 40, 20, 0.07);
}

.loading-state {
    padding: 45px 10px;
    text-align: center;
    color: #947e72;
    font-size: 15px;
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

.form-group label,
.imagen-container label {
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
    align-items: stretch;
    gap: 10px;
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
    background: white;
    color: #bf4829;
    border: 1px dashed #ce8069;
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
    border-radius: 9px;
    background: #f8eee6;
    color: #b75b37;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}

.btn-remove {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: #ffefec;
    color: #bc3928;
    cursor: pointer;
    font-size: 15px;
}

.btn-remove:hover {
    background: #ffdcd6;
}

.imagen-container {
    margin-bottom: 24px;
}

.imagen-actual {
    display: block;
    max-width: 240px;
    max-height: 200px;
    width: 100%;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #eee1d8;
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
