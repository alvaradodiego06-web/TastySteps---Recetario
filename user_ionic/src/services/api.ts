// src/services/api.ts
import axios from 'axios';

// URL base de la API de Laravel (se puede cambiar en el archivo .env de esta app)
const API_URL: string = import.meta.env.VITE_API_URL || 'https://tastysteps.ethiodeploy.app';

const api = axios.create({
  baseURL: `${API_URL}/api`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Forma de receta que usan todas las pantallas de Ionic
export interface Receta {
  id: number;
  title: string;
  category: string;
  time: number;
  difficulty: string;
  servings: number;
  description: string;
  image?: string;
  ingredients: string[];
  steps: string[];
}

// Convierte la receta tal como la devuelve Laravel (nombre_platillo, categoria, ...)
// a la forma que esperan las pantallas (title, category, ...)
const mapReceta = (r: any): Receta => ({
  id: r.id_receta,
  title: r.nombre_platillo,
  category: r.categoria?.nombre_categoria ?? '',
  time: Number(r.tiempo_preparacion),
  difficulty: r.dificultad,
  servings: Number(r.porciones),
  description: '', // la API todavía no tiene un campo de descripción
  image: r.imagen_url ? `${API_URL}/storage/${r.imagen_url}` : undefined,
  ingredients: (r.ingredientes ?? []).map((i: any) => i.nombre_ingrediente),
  steps: (r.pasos ?? []).map((p: any) => p.descripcion_paso)
});

export default {
  // Recetas publicadas (para el catálogo y la búsqueda)
  async getRecetas(): Promise<Receta[]> {
    const { data } = await api.get('/recetas');
    return (data.recetas ?? []).map(mapReceta);
  },
  // Una receta por su ID (para la vista detalle)
  async getRecetaById(id: number | string): Promise<Receta> {
    const { data } = await api.get(`/recetas/${id}`);
    return mapReceta(data.receta);
  },
  // Nombres de categorías (para los filtros)
  async getCategorias(): Promise<string[]> {
    const { data } = await api.get('/categorias');
    return data.map((c: any) => c.nombre_categoria);
  }
};
