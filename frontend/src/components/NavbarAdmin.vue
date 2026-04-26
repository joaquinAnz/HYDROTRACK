<template>
  <nav class="navbar">
    <div class="left">
      <img :src="logo" class="logo-img" alt="Logo" />
      <div class="logo-text">HYDROTRACK</div>

      <ul class="menu">
        <li>
          <router-link to="/dashboard">DASHBOARD</router-link>
        </li>
        <li>
          <router-link to="/dashboard/ordenes">LISTA ORDENES</router-link>
        </li>
        <li>
          <router-link to="/inventario">INVENTARIO</router-link>
        </li>
        <li>
          <router-link to="/ordenes">ORDENES</router-link>
        </li>
        <li>
          <router-link to="/usuarios">USUARIOS</router-link>
        </li>
        <li>
          <router-link to="/vehiculos">VEHICULOS</router-link>
        </li>
      </ul>
    </div>

    <div class="icons">
      <span class="icon">🔔</span>
      <span class="icon">👤</span>
    </div>
  </nav>
</template>

<script setup>
import logo from '../assets/logo.png'
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

.icon {
  font-size: 18px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.icon:hover {
  transform: scale(1.2);
}
</style>