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

    // 🔥 REDIRECCIÓN POR ROL
    if (user.rol === 'Administrador') {
      router.push('/admin')
    } else if (user.rol === 'Tecnico') {
      router.push('/tecnico')
    } else {
      router.push('/')
    }

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
  background: url('https://images.unsplash.com/photo-1581091012184-5c6d1e9c4e3c') no-repeat center center;
  background-size: cover;
  margin-top: 70px;
}

.overlay {
  height: 100%;
  background: rgba(255,255,255,0.85);
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