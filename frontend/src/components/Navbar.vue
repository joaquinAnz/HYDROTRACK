<template>
  <nav class="navbar">
    <div class="navbar-content">
      <img :src="logo" class="logo" />
      <h1 class="title">HYDROTRACK</h1>
    </div>
  </nav>
</template>

<script setup>
import logo from '../assets/logo.png'
</script>

<style scoped>
.navbar {
  width: 100%;
  background-color: #0d2c6c; /* azul */
  padding: 10px 20px;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
}

.navbar-content {
  display: flex;
  align-items: center;
}

.logo {
  width: 40px;
  margin-right: 10px;
}

.title {
  color: white;
  font-size: 18px;
  font-weight: bold;
}
</style>