import { createRouter, createWebHistory } from 'vue-router'

// Vistas
import LoginView from '../views/LoginView.vue'
import OrdenTrabajo from '../views/OrdenTrabajoView.vue'
import DashboardView from '../views/DashboardView.vue'
import GestionView from '../views/GestionView.vue'
//import InventarioView from '../views/InventarioView.vue'

const routes = [
  // Login
  { path: '/', component: LoginView },

  // aneles por rol
  { path: '/admin', component: { template: '<h1>Admin Panel</h1>' } },
  { path: '/tecnico', component: { template: '<h1>Tecnico Panel</h1>' } },

  // Ordenes
  {
    path: '/ordenes',
    name: 'Ordenes',
    component: OrdenTrabajo,
    meta: { requiresAuth: true }
  },

  // Dashboard
  {
    path: '/dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },

  // Gestión de usuarios
  {
    path: '/usuarios',
    component: GestionView,
    meta: { requiresAuth: true }
  },

  // Inventario
  /*{
    path: '/inventario',
    component: InventarioView,
    meta: { requiresAuth: true }
  }*/
]

const router = createRouter({
  history: createWebHistory(),
  routes
})


// 🔒 Middleware de autenticación
router.beforeEach((to, _from, next) => {
  if (!to.meta.requiresAuth) {
    next()
    return
  }

  const storedUser = localStorage.getItem('user') || sessionStorage.getItem('user')

  if (!storedUser) {
    next('/')
    return
  }

  try {
    JSON.parse(storedUser)
  } catch {
    next('/')
    return
  }

  next()
})

export default router