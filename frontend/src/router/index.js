import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'

const routes = [
  { path: '/', component: LoginView },

  { path: '/admin', component: { template: '<h1>Admin Panel</h1>' } },
  { path: '/tecnico', component: { template: '<h1>Tecnico Panel</h1>' } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router