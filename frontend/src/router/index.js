import { createRouter, createWebHistory } from 'vue-router'

// Vistas
import LoginView from '../views/LoginView.vue'

import OrdenTrabajo from '../views/OrdenTrabajoView.vue'
import DashboardView from '../views/DashboardView.vue'
import DashboardOrdenesView from '../views/DashboardOrdenesView.vue'
import GestionView from '../views/GestionView.vue'
import VehiculosView from '../views/VehiculosView.vue'
//import InventarioView from '../views/InventarioView.vue'

import InventarioView from '../views/InventarioView.vue'


const routes = [
  // Login
  { path: '/', name: 'Login', component: LoginView },

  // Accesos antiguos por rol
  { path: '/admin', redirect: '/dashboard' },
  { path: '/tecnico', redirect: '/dashboard' },

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
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },

  {
    path: '/dashboard/ordenes',
    name: 'DashboardOrdenes',
    component: DashboardOrdenesView,
    meta: { requiresAuth: true }
  },

  // Gestión de usuarios
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: GestionView,
    meta: { requiresAuth: true }
  },

  {
    path: '/vehiculos',
    name: 'Vehiculos',
    component: VehiculosView,
    meta: { requiresAuth: true }
  },

  // Inventario
  /*{
    path: '/inventario',
    component: InventarioView,
    meta: { requiresAuth: true }
  }*/

  {
    path: '/inventario',
    name: 'Inventario',
    component: InventarioView,
    meta: { requiresAuth: true }
  },

  // Fallback
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' }

]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, _from) => {
  if (!to.meta.requiresAuth) {
    return true
  }

  const storedUser = localStorage.getItem('user') || sessionStorage.getItem('user')

  if (!storedUser) {
    return '/'
  }

  try {
    JSON.parse(storedUser)
  } catch {
    return '/'
  }

  return true
})

export default router