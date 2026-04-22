<template>
  <div class="gestion-shell">
    <header class="topbar">
      <div class="brand">
        <img :src="logo" alt="Hydrotrack" />
        <span>HYDROTRACK</span>
      </div>
      <nav>
        <router-link to="/dashboard">DASHBOARD</router-link>
        <router-link to="/usuarios">USUARIOS</router-link>
      </nav>
    </header>

    <main class="panel">
      <div class="panel-head">
        <h1>GESTION DE EMPLEADOS Y CLIENTES</h1>
        <button type="button" class="add-btn" @click="openAddModal">+ Agregar {{ currentTabLabel }}</button>
      </div>

      <div class="table-wrap" v-if="activeTab === 'usuarios'">
        <table>
          <thead>
            <tr>
              <th>Nombres</th>
              <th>Apellidos</th>
              <th>Telefono</th>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Fecha Registro</th>
              <th>Ultima Modificacion</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in usuarios" :key="item.id_usuario">
              <td>{{ item.nombres }}</td>
              <td>{{ item.apellidos }}</td>
              <td>{{ item.telefono }}</td>
              <td>{{ item.usuario }}</td>
              <td>{{ item.rol }}</td>
              <td>{{ item.estado ? 'Activo' : 'Inactivo' }}</td>
              <td>{{ formatDate(item.fecha_creacion) }}</td>
              <td>{{ formatDate(item.ultima_modificacion) }}</td>
              <td class="actions-cell">
                <button class="action-btn view" @click="viewUsuario(item)" title="Ver">👁️</button>
                <button class="action-btn edit" @click="editUsuario(item)" title="Editar">✏️</button>
                <button class="action-btn delete" @click="deleteUsuario(item.id_usuario)" title="Eliminar">🗑️</button>
              </td>
            </tr>
            <tr v-if="!usuarios.length">
              <td colspan="9" class="empty">No hay empleados registrados</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-wrap" v-else>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombres</th>
              <th>Apellidos</th>
              <th>Carnet de Identidad</th>
              <th>Fecha Registro</th>
              <th>Ultima Modificacion</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in clientes" :key="item.id_cliente">
              <td>{{ item.id_cliente }}</td>
              <td>{{ item.nombres }}</td>
              <td>{{ item.apellidos }}</td>
              <td>{{ item.carnet_identidad }}</td>
              <td>{{ formatDate(item.fecha_registro) }}</td>
              <td>{{ formatDate(item.ultima_modificacion) }}</td>
              <td>{{ item.estado ? 'Activo' : 'Inactivo' }}</td>
              <td class="actions-cell">
                <button class="action-btn view" @click="viewCliente(item)" title="Ver">👁️</button>
                <button class="action-btn edit" @click="editCliente(item)" title="Editar">✏️</button>
                <button class="action-btn delete" @click="deleteCliente(item.id_cliente)" title="Eliminar">🗑️</button>
              </td>
            </tr>
            <tr v-if="!clientes.length">
              <td colspan="8" class="empty">No hay clientes registrados</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="tabs">
        <button :class="{ active: activeTab === 'clientes' }" @click="activeTab = 'clientes'">Clientes</button>
        <button :class="{ active: activeTab === 'usuarios' }" @click="activeTab = 'usuarios'">Empleados</button>
      </div>
    </main>

    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <h2>
          <span v-if="modalMode === 'create'">Agregar {{ currentTabLabel }}</span>
          <span v-else-if="modalMode === 'view'">Detalles de {{ currentTabLabel }}</span>
          <span v-else-if="modalMode === 'edit'">Editar {{ currentTabLabel }}</span>
        </h2>

        <form v-if="activeTab === 'usuarios'" @submit.prevent="handleUsuarioSubmit" :class="{ 'is-view': modalMode === 'view' }">
          <label>Nombres</label>
          <input v-model="usuarioForm.nombres" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Apellidos</label>
          <input v-model="usuarioForm.apellidos" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Telefono</label>
          <input v-model="usuarioForm.telefono" :disabled="modalMode === 'view'" required maxlength="20" />

          <label>Usuario</label>
          <input v-model="usuarioForm.usuario" :disabled="modalMode === 'view'" required maxlength="100" />
          <p class="field-error" v-if="usuarioFieldError">{{ usuarioFieldError }}</p>

          <template v-if="modalMode === 'create'">
            <label>Contrasena</label>
            <input v-model="usuarioForm.password" :required="modalMode === 'create'" type="password" minlength="6" maxlength="100" placeholder="Dejar en blanco para mantener la actual" />
          </template>

          <label>Rol</label>
          <select v-model.number="usuarioForm.id_rol" :disabled="modalMode === 'view'" required>
            <option disabled :value="null">Selecciona un rol</option>
            <option v-for="rol in employeeRoles" :key="rol.id_rol" :value="rol.id_rol">{{ rol.nombre }}</option>
          </select>

          <label class="inline-check">
            <input type="checkbox" v-model="usuarioForm.estado" :disabled="modalMode === 'view'" />
            Activo
          </label>

          <p class="error" v-if="errorMsg">{{ errorMsg }}</p>

          <div class="actions">
            <button
              v-if="modalMode === 'edit'"
              type="button"
              class="warn"
              :disabled="loading"
              @click="openResetPasswordModal"
            >
              Restablecer contrasena
            </button>
            <button type="button" class="ghost" @click="closeModal">{{ modalMode === 'view' ? 'Cerrar' : 'Cancelar' }}</button>
            <button v-if="modalMode !== 'view'" type="submit" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</button>
          </div>
        </form>

        <form v-else @submit.prevent="handleClienteSubmit" :class="{ 'is-view': modalMode === 'view' }">
          <label>Nombres</label>
          <input v-model="clienteForm.nombres" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Apellidos</label>
          <input v-model="clienteForm.apellidos" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Carnet de Identidad</label>
          <input v-model="clienteForm.carnet_identidad" :disabled="modalMode === 'view'" required maxlength="30" />

          <label class="inline-check">
            <input type="checkbox" v-model="clienteForm.estado" :disabled="modalMode === 'view'" />
            Activo
          </label>

          <p class="error" v-if="errorMsg">{{ errorMsg }}</p>

          <div class="actions">
            <button type="button" class="ghost" @click="closeModal">{{ modalMode === 'view' ? 'Cerrar' : 'Cancelar' }}</button>
            <button v-if="modalMode !== 'view'" type="submit" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showResetPasswordModal" class="mini-modal-backdrop" @click.self="closeResetPasswordModal">
      <div class="mini-modal">
        <h3>Restablecer contrasena</h3>
        <p>Ingresa la nueva contrasena (minimo 6 caracteres)</p>
        <input
          v-model="resetPasswordValue"
          type="password"
          minlength="6"
          maxlength="100"
          placeholder="Nueva contrasena"
        />
        <p class="field-error" v-if="resetPasswordError">{{ resetPasswordError }}</p>

        <div class="actions">
          <button type="button" class="ghost" @click="closeResetPasswordModal">Cancelar</button>
          <button type="button" :disabled="loading" @click="submitResetPassword">
            {{ loading ? 'Guardando...' : 'Guardar nueva contrasena' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../services/api'
import logo from '../assets/logo.png'

const activeTab = ref('usuarios')
const showModal = ref(false)
const modalMode = ref('create') // 'create', 'view', 'edit'
const loading = ref(false)
const errorMsg = ref('')
const usuarioFieldError = ref('')
const showResetPasswordModal = ref(false)
const resetPasswordValue = ref('')
const resetPasswordError = ref('')

const usuarios = ref([])
const clientes = ref([])
const roles = ref([])

const usuarioForm = ref({
  nombres: '',
  apellidos: '',
  telefono: '',
  usuario: '',
  password: '',
  id_rol: null,
  estado: true
})

const clienteForm = ref({
  nombres: '',
  apellidos: '',
  carnet_identidad: '',
  estado: true
})

const currentEditingId = ref(null) // ID del registro siendo editado/visto

const currentTabLabel = computed(() => (activeTab.value === 'usuarios' ? 'Empleado' : 'Cliente'))

const employeeRoles = computed(() => {
  const allowed = ['admin', 'administrador', 'tecnico', 'ventas']
  return roles.value.filter((rol) => allowed.includes(String(rol.nombre || '').toLowerCase()))
})

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

const resetForms = () => {
  usuarioForm.value = { nombres: '', apellidos: '', telefono: '', usuario: '', password: '', id_rol: null, estado: true }
  clienteForm.value = { nombres: '', apellidos: '', carnet_identidad: '', estado: true }
  errorMsg.value = ''
  usuarioFieldError.value = ''
  currentEditingId.value = null
}

const closeModal = () => {
  showModal.value = false
  showResetPasswordModal.value = false
  resetForms()
  modalMode.value = 'create'
}

const openAddModal = () => {
  resetForms()
  modalMode.value = 'create'
  showModal.value = true
}

const loadUsuarios = async () => {
  const { data } = await api.get('/usuarios')
  usuarios.value = data
}

const loadClientes = async () => {
  const { data } = await api.get('/clientes')
  clientes.value = data
}

const loadRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data
}

const createUsuario = async () => {
  loading.value = true
  errorMsg.value = ''
  usuarioFieldError.value = ''
  try {
    await api.post('/usuarios', usuarioForm.value)
    await loadUsuarios()
    closeModal()
  } catch (error) {
    const apiErrors = error.response?.data?.errors
    if (apiErrors?.usuario?.length) {
      usuarioFieldError.value = apiErrors.usuario[0]
    }
    errorMsg.value = error.response?.data?.message || (usuarioFieldError.value ? '' : 'No se pudo crear el usuario')
  } finally {
    loading.value = false
  }
}

const updateUsuario = async () => {
  loading.value = true
  errorMsg.value = ''
  usuarioFieldError.value = ''
  try {
    const payload = { ...usuarioForm.value }
    if (!payload.password) delete payload.password
    await api.put(`/usuarios/${currentEditingId.value}`, payload)
    await loadUsuarios()
    closeModal()
  } catch (error) {
    const apiErrors = error.response?.data?.errors
    if (apiErrors?.usuario?.length) {
      usuarioFieldError.value = apiErrors.usuario[0]
    }
    errorMsg.value = error.response?.data?.message || (usuarioFieldError.value ? '' : 'No se pudo actualizar el usuario')
  } finally {
    loading.value = false
  }
}

const handleUsuarioSubmit = () => {
  if (modalMode.value === 'create') {
    createUsuario()
  } else if (modalMode.value === 'edit') {
    updateUsuario()
  }
}

const createCliente = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    await api.post('/clientes', clienteForm.value)
    await loadClientes()
    closeModal()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo crear el cliente'
  } finally {
    loading.value = false
  }
}

const updateCliente = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    await api.put(`/clientes/${currentEditingId.value}`, clienteForm.value)
    await loadClientes()
    closeModal()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo actualizar el cliente'
  } finally {
    loading.value = false
  }
}

const handleClienteSubmit = () => {
  if (modalMode.value === 'create') {
    createCliente()
  } else if (modalMode.value === 'edit') {
    updateCliente()
  }
}

const viewUsuario = (usuario) => {
  currentEditingId.value = usuario.id_usuario
  usuarioForm.value = {
    nombres: usuario.nombres,
    apellidos: usuario.apellidos,
    telefono: usuario.telefono,
    usuario: usuario.usuario,
    password: '',
    id_rol: usuario.id_rol,
    estado: usuario.estado
  }
  modalMode.value = 'view'
  showModal.value = true
}

const editUsuario = (usuario) => {
  currentEditingId.value = usuario.id_usuario
  usuarioForm.value = {
    nombres: usuario.nombres,
    apellidos: usuario.apellidos,
    telefono: usuario.telefono,
    usuario: usuario.usuario,
    password: '',
    id_rol: usuario.id_rol,
    estado: usuario.estado
  }
  modalMode.value = 'edit'
  showModal.value = true
}

const openResetPasswordModal = () => {
  if (!currentEditingId.value) return
  resetPasswordValue.value = ''
  resetPasswordError.value = ''
  showResetPasswordModal.value = true
}

const closeResetPasswordModal = () => {
  showResetPasswordModal.value = false
  resetPasswordValue.value = ''
  resetPasswordError.value = ''
}

const submitResetPassword = async () => {
  if (!currentEditingId.value) return

  if (!resetPasswordValue.value || resetPasswordValue.value.length < 6) {
    resetPasswordError.value = 'La nueva contrasena debe tener al menos 6 caracteres'
    return
  }

  loading.value = true
  errorMsg.value = ''
  resetPasswordError.value = ''

  try {
    await api.put(`/usuarios/${currentEditingId.value}`, { password: resetPasswordValue.value })
    await loadUsuarios()
    closeResetPasswordModal()
    errorMsg.value = 'Contrasena restablecida correctamente'
  } catch (error) {
    resetPasswordError.value = error.response?.data?.message || 'No se pudo restablecer la contrasena'
  } finally {
    loading.value = false
  }
}

const deleteUsuario = async (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar este empleado?')) {
    try {
      loading.value = true
      errorMsg.value = ''
      await api.delete(`/usuarios/${id}`)
      await loadUsuarios()
    } catch (error) {
      errorMsg.value = error.response?.data?.message || 'No se pudo eliminar el empleado'
    } finally {
      loading.value = false
    }
  }
}

const viewCliente = (cliente) => {
  currentEditingId.value = cliente.id_cliente
  clienteForm.value = {
    nombres: cliente.nombres,
    apellidos: cliente.apellidos,
    carnet_identidad: cliente.carnet_identidad,
    estado: cliente.estado
  }
  modalMode.value = 'view'
  showModal.value = true
}

const editCliente = (cliente) => {
  currentEditingId.value = cliente.id_cliente
  clienteForm.value = {
    nombres: cliente.nombres,
    apellidos: cliente.apellidos,
    carnet_identidad: cliente.carnet_identidad,
    estado: cliente.estado
  }
  modalMode.value = 'edit'
  showModal.value = true
}

const deleteCliente = async (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
    try {
      // Implementar DELETE endpoint cuando sea necesario
      console.log('Eliminar cliente:', id)
    } catch (error) {
      console.error('Error al eliminar:', error)
    }
  }
}

watch(activeTab, () => {
  errorMsg.value = ''
  usuarioFieldError.value = ''
})

watch(() => usuarioForm.value.usuario, () => {
  usuarioFieldError.value = ''
})

onMounted(async () => {
  await Promise.all([loadUsuarios(), loadClientes(), loadRoles()])
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Manrope:wght@400;600;700&display=swap');

.gestion-shell {
  min-height: 100vh;
  background: #efefef;
  font-family: 'Manrope', sans-serif;
}

.topbar {
  background: #9ea4a4;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 28px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand img {
  width: 22px;
}

.brand span {
  font-family: 'Cinzel', serif;
  letter-spacing: 0.2em;
  font-size: 30px;
}

nav {
  display: flex;
  gap: 28px;
}

nav a {
  color: #fff;
  text-decoration: none;
  font-size: 12px;
  letter-spacing: 0.08em;
}

nav a.router-link-active {
  text-decoration: underline;
}

.panel {
  max-width: 1160px;
  margin: 0 auto;
  padding: 20px 16px 36px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
}

.panel h1 {
  margin: 0;
  font-family: 'Cinzel', serif;
  color: #2e3773;
  font-size: clamp(26px, 3vw, 52px);
  font-weight: 500;
}

.add-btn {
  border: none;
  background: #4f9a63;
  color: #fff;
  font-weight: 700;
  border-radius: 10px;
  padding: 12px 16px;
  cursor: pointer;
}

.table-wrap {
  margin-top: 18px;
  background: #fff;
  border: 1px solid #c8cecc;
  border-radius: 16px;
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  background: #9ea4a4;
  color: #fff;
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  font-weight: 600;
  padding: 8px 6px;
  border-right: 1px solid #c9cfd2;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

thead th:last-child {
  border-right: none;
}

tbody td {
  background: #b6deb0;
  border: 1px solid #2e2e2e;
  text-align: center;
  padding: 6px 5px;
  font-size: 12px;
  color: #1d1d1d;
  max-width: 120px;
  word-break: break-word;
}

.actions-cell {
  padding: 4px !important;
  background: #a8d8a0 !important;
  display: flex;
  gap: 3px;
  justify-content: center;
  flex-wrap: wrap;
}

.action-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 16px;
  padding: 2px 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.action-btn:hover {
  transform: scale(1.15);
}

.action-btn.view:hover {
  background: rgba(65, 105, 225, 0.2);
}

.action-btn.edit:hover {
  background: rgba(255, 165, 0, 0.2);
}

.action-btn.delete {
  filter: drop-shadow(0 0 2px rgba(255, 0, 0, 0.3));
}

.action-btn.delete:hover {
  background: rgba(255, 0, 0, 0.2);
}

.empty {
  padding: 20px;
  background: #fff;
}

.tabs {
  margin-top: 24px;
  display: flex;
  justify-content: center;
  gap: 0;
}

.tabs button {
  border: 1px solid #cfd4d8;
  background: #f0f2f5;
  min-width: 170px;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: 700;
}

.tabs button.active {
  background: #c6e5cc;
  color: #1d6931;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: grid;
  place-items: center;
  padding: 16px;
}

.modal {
  width: min(520px, 94vw);
  background: #fff;
  border-radius: 14px;
  padding: 18px;
}

.mini-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: grid;
  place-items: center;
  padding: 16px;
  z-index: 1200;
}

.mini-modal {
  width: min(430px, 92vw);
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 14px 38px rgba(0, 0, 0, 0.28);
}

.mini-modal h3 {
  margin: 0 0 8px;
  font-size: 20px;
  color: #2e3773;
}

.mini-modal p {
  margin: 0 0 10px;
}

.modal h2 {
  margin: 0 0 14px;
}

label {
  display: block;
  font-weight: 700;
  margin: 12px 0 6px;
}

input,
select {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #cbd2dc;
  border-radius: 8px;
  padding: 10px;
}

input:readonly {
  background: #f0f0f0;
  color: #555;
  cursor: not-allowed;
}

select:disabled,
input:disabled {
  background: #f0f0f0;
  color: #999;
  cursor: not-allowed;
}

.is-view input,
.is-view select {
  user-select: none;
  -webkit-user-select: none;
}

.inline-check {
  display: flex;
  align-items: center;
  gap: 8px;
}

.inline-check input {
  width: auto;
}

.actions {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.actions button {
  border: none;
  border-radius: 8px;
  padding: 10px 14px;
  cursor: pointer;
  background: #356fb6;
  color: #fff;
}

.actions .ghost {
  background: #e4e8ef;
  color: #1f2937;
}

.actions .warn {
  background: #b7791f;
  color: #fff;
}

.error {
  color: #d32f2f;
  margin: 10px 0 0;
}

.field-error {
  color: #d32f2f;
  margin: 6px 0 2px;
  font-size: 12px;
  font-weight: 600;
}

@media (max-width: 900px) {
  .topbar {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .panel-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .brand span {
    font-size: 20px;
  }

  table {
    min-width: 900px;
  }

  .table-wrap {
    overflow: auto;
  }

  thead th {
    font-size: 11px;
  }

  tbody td {
    font-size: 11px;
    padding: 5px 4px;
  }
}
</style>
