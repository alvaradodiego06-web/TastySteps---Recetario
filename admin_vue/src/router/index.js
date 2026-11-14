import Vue from 'vue'
import VueRouter from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import GestionarRecetasView from '@/views/GestionarRecetasView.vue'
import CrearRecetaView from '@/views/CrearRecetaView.vue'
import EditarRecetaView from '@/views/EditarRecetaView.vue'
import { authService } from '@/services/auth'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView
  },
  {
    path: '/recetas',
    name: 'recetas',
    component: GestionarRecetasView,
    meta: { requiereSesion: true }
  },
  {
    path: '/recetas/nueva',
    name: 'receta-nueva',
    component: CrearRecetaView,
    meta: { requiereSesion: true }
  },
  {
      path: '/recetas/:id/editar',
      name: 'receta-editar',
      component: EditarRecetaView,
      meta: { requiereSesion: true }
    }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

// Las rutas con requiereSesion necesitan haber iniciado sesión
router.beforeEach((to, from, next) => {
  if (to.matched.some(r => r.meta.requiereSesion) && !authService.isAuthenticated()) {
    next('/login')
  } else {
    next()
  }
})

export default router
