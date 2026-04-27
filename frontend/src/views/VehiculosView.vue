<template>
  <div class="gestion-shell">
    <NavbarAdmin />
    <div class="navbar-spacer"></div>

    <main class="panel">
      <div class="panel-head">
        <h1>GESTION DE VEHICULOS</h1>
        <button type="button" class="add-btn" @click="openCreateModal">+ Agregar Vehiculo</button>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Placa</th>
              <th>Cliente</th>
              <th>Descripcion</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in vehiculos" :key="item.id_vehiculo">
              <td>{{ item.id_vehiculo }}</td>
              <td>{{ item.placa }}</td>
              <td>{{ item.cliente ? `${item.cliente.nombres} ${item.cliente.apellidos}` : '-' }}</td>
              <td>{{ item.descripcion || '-' }}</td>
              <td>
                <span class="vehicle-state" :class="item.estado === 'en_taller' ? 'state-en-taller' : 'state-activo'">
                  {{ item.estado === 'en_taller' ? 'En taller' : 'Activo' }}
                </span>
              </td>
              <td class="actions-cell">
                <button class="action-btn view" @click="openEditModal(item)" title="Editar">✏️</button>
                <button class="action-btn delete" @click="deleteVehiculo(item.id_vehiculo)" title="Eliminar">🗑️</button>
              </td>
            </tr>
            <tr v-if="!vehiculos.length">
              <td colspan="6" class="empty">No hay vehiculos registrados</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <h2>{{ modalMode === 'create' ? 'Agregar Vehiculo' : 'Editar Vehiculo' }}</h2>

        <form @submit.prevent="saveVehiculo">
          <label>Placa</label>
          <input v-model="form.placa" required maxlength="20" />

          <label>Cliente</label>
          <select v-model.number="form.id_cliente" required>
            <option disabled :value="null">Selecciona un cliente</option>
            <option v-for="cliente in clientes" :key="cliente.id_cliente" :value="cliente.id_cliente">
              {{ cliente.nombres }} {{ cliente.apellidos }}
            </option>
          </select>

          <label>Descripcion breve</label>
          <textarea v-model="form.descripcion" required maxlength="500" rows="3"></textarea>

          <p class="error" v-if="errorMsg">{{ errorMsg }}</p>

          <div class="actions">
            <button type="button" class="ghost" @click="closeModal">Cancelar</button>
            <button type="submit" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <Footer :logo="logo" />
</template>

<script setup>
import { onMounted, ref } from 'vue'
import NavbarAdmin from '../components/NavbarAdmin.vue'
import Footer from '../components/Footer.vue'
import api from '../services/api'
import '../assets/gestion.css'

const vehiculos = ref([])
const clientes = ref([])
const loading = ref(false)
const errorMsg = ref('')
const showModal = ref(false)
const modalMode = ref('create')
const currentId = ref(null)

const form = ref({
  placa: '',
  id_cliente: null,
  descripcion: ''
})

const resetForm = () => {
  form.value = {
    placa: '',
    id_cliente: null,
    descripcion: ''
  }
  currentId.value = null
  errorMsg.value = ''
}

const loadVehiculos = async () => {
  const { data } = await api.get('/vehiculos')
  vehiculos.value = data?.data ?? []
}

const loadClientes = async () => {
  const { data } = await api.get('/clientes')
  clientes.value = data
}

const openCreateModal = () => {
  modalMode.value = 'create'
  resetForm()
  showModal.value = true
}

const openEditModal = (vehiculo) => {
  modalMode.value = 'edit'
  currentId.value = vehiculo.id_vehiculo
  form.value = {
    placa: vehiculo.placa || '',
    id_cliente: vehiculo.id_cliente ?? null,
    descripcion: vehiculo.descripcion || ''
  }
  errorMsg.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const saveVehiculo = async () => {
  loading.value = true
  errorMsg.value = ''

  try {
    if (modalMode.value === 'create') {
      await api.post('/vehiculos', form.value)
    } else {
      await api.put(`/vehiculos/${currentId.value}`, form.value)
    }

    await loadVehiculos()
    closeModal()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo guardar el vehiculo'
  } finally {
    loading.value = false
  }
}

const deleteVehiculo = async (id) => {
  if (!window.confirm('¿Estas seguro de eliminar este vehiculo?')) return

  loading.value = true
  try {
    await api.delete(`/vehiculos/${id}`)
    await loadVehiculos()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo eliminar el vehiculo'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadVehiculos(), loadClientes()])
})
</script>

<style scoped>
.navbar-spacer {
  height: 70px;
}
</style>
