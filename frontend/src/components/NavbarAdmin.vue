<template>
  <nav class="navbar">
    <div class="left">
      <img :src="logo" class="logo-img" alt="Logo" />
      <div class="logo-text">HYDROTRACK</div>

      <ul class="menu">
        <li v-for="item in menuItems" :key="item.to">
          <router-link :to="item.to">{{ item.label }}</router-link>
        </li>
      </ul>
    </div>

    <div class="icons">
      <span class="role-chip">{{ roleLabel }}</span>
      <span class="icon">🔔</span>
      <span class="icon">👤</span>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import logo from '../assets/logo.png'

const parseStoredUser = () => {
  const raw = localStorage.getItem('user') || sessionStorage.getItem('user')
  if (!raw) return {}

  try {
    return JSON.parse(raw)
  } catch {
    return {}
  }
}

const normalizeRoleName = (value) =>
  String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

const roleKey = computed(() => {
  const user = parseStoredUser()
  const roleName = normalizeRoleName(user.rol_nombre || user.rol || user.role || user.tipo)

  if (roleName.includes('admin')) return 'admin'
  if (roleName.includes('tecnico')) return 'tecnico'
  if (roleName.includes('venta')) return 'ventas'

  const roleId = String(user.id_rol || user.rol_id || '')
  if (roleId === '1') return 'admin'
  if (roleId === '2') return 'tecnico'
  if (roleId === '3') return 'ventas'

  return 'admin'
})

const menus = {
  admin: [
    { to: '/dashboard', label: 'DASHBOARD' },
    { to: '/dashboard/ordenes', label: 'LISTA ORDENES' },
    { to: '/inventario', label: 'INVENTARIO' },
    { to: '/ordenes', label: 'ORDENES' },
    { to: '/usuarios', label: 'USUARIOS' },
    { to: '/vehiculos', label: 'VEHICULOS' },
  ],
  tecnico: [
    { to: '/dashboard/ordenes', label: 'LISTA ORDENES' },
    { to: '/vehiculos', label: 'VEHICULOS' },
  ],
  ventas: [
    { to: '/dashboard/ordenes', label: 'LISTA ORDENES' },
    { to: '/ordenes', label: 'ORDENES' },
    { to: '/inventario', label: 'INVENTARIO' },
    { to: '/vehiculos', label: 'VEHICULOS' },
  ],
}

const menuItems = computed(() => menus[roleKey.value] || menus.admin)

const roleLabel = computed(() => {
  if (roleKey.value === 'tecnico') return 'TECNICO'
  if (roleKey.value === 'ventas') return 'VENTAS'
  return 'ADMIN'
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Manrope:wght@400;600&display=swap');

.navbar {
  width: 100%;
  height: 70px;
  background-color: #0d2c6c;
  padding: 0 20px;
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-sizing: border-box;
  color: white;
  font-family: 'Manrope', sans-serif;
  z-index: 1000;
}

.left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.logo-img {
  width: 32px;
  height: 32px;
  object-fit: contain;
  flex-shrink: 0;
}

.logo-text {
  font-family: 'Cinzel', serif;
  font-size: 22px;
  letter-spacing: 3px;
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.menu {
  display: flex;
  align-items: center;
  gap: 26px;
  list-style: none;
  margin: 0 0 0 28px;
  padding: 0;
  flex-wrap: nowrap;
}

.menu li {
  white-space: nowrap;
}

.menu a {
  font-size: 13px;
  letter-spacing: 1px;
  opacity: 0.85;
  transition: all 0.2s ease;
  text-decoration: none;
  color: #fff;
  padding-bottom: 4px;
  border-bottom: 2px solid transparent;
}

.menu a:hover,
.menu a.router-link-active {
  opacity: 1;
  border-bottom-color: #fff;
}

.icons {
  display: flex;
  gap: 18px;
  align-items: center;
  flex-shrink: 0;
  margin-left: 20px;
}

.role-chip {
  font-size: 10px;
  letter-spacing: 0.08em;
  font-weight: 700;
  border: 1px solid rgba(255, 255, 255, 0.45);
  border-radius: 999px;
  padding: 4px 8px;
  color: #e8efff;
}

.icon {
  font-size: 18px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.icon:hover {
  transform: scale(1.2);
}
</style>