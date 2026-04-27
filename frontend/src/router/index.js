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

const normalizeRoleName = (value) =>
  String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

const resolveRoleKey = (user) => {
  const roleName = normalizeRoleName(user?.rol_nombre || user?.rol || user?.role || user?.tipo)
  if (roleName.includes('admin')) return 'admin'
  if (roleName.includes('tecnico')) return 'tecnico'
  if (roleName.includes('venta')) return 'ventas'

  const roleId = String(user?.id_rol || user?.rol_id || '')
  if (roleId === '1') return 'admin'
  if (roleId === '2') return 'tecnico'
  if (roleId === '3') return 'ventas'

  return 'admin'
}

router.beforeEach((to, _from) => {
  if (!to.meta.requiresAuth) {
    return true
  }

  const storedUser = localStorage.getItem('user') || sessionStorage.getItem('user')

  if (!storedUser) {
    return '/'
  }

  let user
  try {
    user = JSON.parse(storedUser)
  } catch {
    return '/'
  }

  const roleKey = resolveRoleKey(user)
  if (to.path === '/dashboard' && roleKey !== 'admin') {
    return '/dashboard/ordenes'
  }

  return true
})

export default router