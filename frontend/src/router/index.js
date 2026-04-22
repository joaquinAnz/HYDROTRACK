import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import GestionView from '../views/GestionView.vue'
import InventarioView from '../views/InventarioView.vue'

const routes = [
  { path: '/', component: LoginView },
  { path: '/dashboard', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/usuarios', component: GestionView, meta: { requiresAuth: true } },
  { path: '/inventario', component: InventarioView, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

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