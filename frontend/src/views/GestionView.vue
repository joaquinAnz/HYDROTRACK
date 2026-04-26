<template>
  <div class="gestion-shell">
    <NavbarAdmin />
    <div class="navbar-spacer"></div>

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

      <div class="table-wrap" v-else-if="activeTab === 'clientes'">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombres</th>
              <th>Apellidos</th>
              <th>Carnet de Identidad</th>
              <th>Telefono</th>
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
              <td>{{ item.telefono || '-' }}</td>
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
              <td colspan="9" class="empty">No hay clientes registrados</td>
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

        <form v-else-if="activeTab === 'clientes'" @submit.prevent="handleClienteSubmit" :class="{ 'is-view': modalMode === 'view' }">
          <label>Nombres</label>
          <input v-model="clienteForm.nombres" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Apellidos</label>
          <input v-model="clienteForm.apellidos" :disabled="modalMode === 'view'" required maxlength="100" />

          <label>Carnet de Identidad</label>
          <input v-model="clienteForm.carnet_identidad" :disabled="modalMode === 'view'" required maxlength="30" />

          <label>Telefono</label>
          <input v-model="clienteForm.telefono" :disabled="modalMode === 'view'" required maxlength="20" />

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
import { onMounted } from 'vue'
import NavbarAdmin from '../components/NavbarAdmin.vue'

import '../assets/gestion.css'
import logo from '../assets/logo.png'
import {
  activeTab,
  showModal,
  modalMode,
  loading,
  errorMsg,
  usuarioFieldError,
  showResetPasswordModal,
  resetPasswordValue,
  resetPasswordError,
  usuarios,
  clientes,
  usuarioForm,
  clienteForm,
  currentTabLabel,
  employeeRoles,
  formatDate,
  openAddModal,
  closeModal,
  handleUsuarioSubmit,
  handleClienteSubmit,
  viewUsuario,
  editUsuario,
  openResetPasswordModal,
  closeResetPasswordModal,
  submitResetPassword,
  deleteUsuario,
  viewCliente,
  editCliente,
  deleteCliente,
  initGestionData
} from '../services/gestionService'

onMounted(() => {
  initGestionData()
})
</script>

<style scoped>
.navbar-spacer {
  height: 70px;
}
</style>