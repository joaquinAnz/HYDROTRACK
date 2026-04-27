<template>
<Navbar />
  <div class="login-container">
    <div class="overlay">
      <div class="login-box">
        <h1>Iniciar Sesión</h1>

        <form @submit.prevent="iniciarSesion">
          <input
            v-model="usuario"
            type="text"
            placeholder="Usuario"
            class="input"
          />

          <input
            v-model="password"
            type="password"
            placeholder="Contraseña"
            class="input"
          />

          <div class="checkbox">
            <input type="checkbox" v-model="mantenerSesion" />
            <label> Mantener cuenta abierta </label>
          </div>

          <button type="submit" :disabled="cargando">
            {{ cargando ? 'Ingresando...' : 'Ingresar' }}
          </button>

          <p v-if="error" class="error">{{ error }}</p>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Navbar from '../components/Navbar.vue'

const router = useRouter()

const usuario = ref('')
const password = ref('')
const mantenerSesion = ref(false)
const cargando = ref(false)
const error = ref('')

const normalizeRoleName = (value) =>
  String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

const resolveHomeByRole = (user) => {
  const roleName = normalizeRoleName(user?.rol_nombre || user?.rol || user?.role || user?.tipo)
  const roleId = String(user?.id_rol || user?.rol_id || '')

  if (roleName.includes('tecnico') || roleName.includes('venta') || roleId === '2' || roleId === '3') {
    return '/dashboard/ordenes'
  }

  return '/dashboard'
}

const iniciarSesion = async () => {
  error.value = ''

  if (!usuario.value || !password.value) {
    error.value = 'Completa todos los campos'
    return
  }

  cargando.value = true

  try {
    const res = await api.post('/login', {
      usuario: usuario.value,
      password: password.value
    })

    const user = res.data.usuario

    if (mantenerSesion.value) {
      localStorage.setItem('user', JSON.stringify(user))
    } else {
      sessionStorage.setItem('user', JSON.stringify(user))
    }

    router.push(resolveHomeByRole(user))

  } catch (err) {
    error.value = err.response?.data?.message || 'Error al iniciar sesión'
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.login-container {
  height: 100vh;
  background: url('../assets/Header.png') no-repeat center center;
  background-size: cover;
  /*margin-top: 70px;*/
}

.overlay {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-box {
  width: 350px;
  text-align: center;
}

h1 {
  margin-bottom: 20px;
}

.input {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border-radius: 10px;
  border: none;
  background: #eee;
}

.checkbox {
  display: flex;
  align-items: center;
  justify-content: start;
  margin-bottom: 15px;
}

button {
  width: 100%;
  padding: 12px;
  border-radius: 20px;
  border: none;
  background: gray;
  color: white;
  margin-bottom: 10px;
  cursor: pointer;
}

.registro {
  background: #b8e0e6;
  color: black;
}

.error {
  color: red;
}
</style>