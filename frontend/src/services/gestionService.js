import { computed, ref, watch } from 'vue'
import api from '../services/api'

export const activeTab = ref('usuarios')
export const showModal = ref(false)
export const modalMode = ref('create') // 'create', 'view', 'edit'
export const loading = ref(false)
export const errorMsg = ref('')
export const usuarioFieldError = ref('')
export const showResetPasswordModal = ref(false)
export const resetPasswordValue = ref('')
export const resetPasswordError = ref('')

export const usuarios = ref([])
export const clientes = ref([])
export const vehiculos = ref([])
export const roles = ref([])

export const usuarioForm = ref({
  nombres: '',
  apellidos: '',
  telefono: '',
  usuario: '',
  password: '',
  id_rol: null,
  estado: true
})

export const clienteForm = ref({
  nombres: '',
  apellidos: '',
  carnet_identidad: '',
  telefono: '',
  estado: true
})

export const vehiculoForm = ref({
  placa: '',
  id_cliente: null,
  descripcion: '',
  estado: 'activo'
})

export const currentEditingId = ref(null) // ID del registro siendo editado/visto

export const currentTabLabel = computed(() => {
  if (activeTab.value === 'usuarios') return 'Empleado'
  if (activeTab.value === 'vehiculos') return 'Vehiculo'
  return 'Cliente'
})

export const employeeRoles = computed(() => {
  const allowed = ['admin', 'administrador', 'tecnico', 'ventas']
  return roles.value.filter((rol) => allowed.includes(String(rol.nombre || '').toLowerCase()))
})

export const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

export const resetForms = () => {
  usuarioForm.value = { nombres: '', apellidos: '', telefono: '', usuario: '', password: '', id_rol: null, estado: true }
  clienteForm.value = { nombres: '', apellidos: '', carnet_identidad: '', telefono: '', estado: true }
  vehiculoForm.value = { placa: '', id_cliente: null, descripcion: '', estado: 'activo' }
  errorMsg.value = ''
  usuarioFieldError.value = ''
  currentEditingId.value = null
}

export const closeModal = () => {
  showModal.value = false
  showResetPasswordModal.value = false
  resetForms()
  modalMode.value = 'create'
}

export const openAddModal = () => {
  resetForms()
  modalMode.value = 'create'
  showModal.value = true
}

export const loadUsuarios = async () => {
  const { data } = await api.get('/usuarios')
  usuarios.value = data
}

export const loadClientes = async () => {
  const { data } = await api.get('/clientes')
  clientes.value = data
}

export const loadRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data
}

export const loadVehiculos = async () => {
  const { data } = await api.get('/vehiculos')
  vehiculos.value = data?.data ?? data ?? []
}

export const createUsuario = async () => {
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

export const updateUsuario = async () => {
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

export const handleUsuarioSubmit = () => {
  if (modalMode.value === 'create') {
    createUsuario()
  } else if (modalMode.value === 'edit') {
    updateUsuario()
  }
}

export const createCliente = async () => {
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

export const updateCliente = async () => {
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

export const handleClienteSubmit = () => {
  if (modalMode.value === 'create') {
    createCliente()
  } else if (modalMode.value === 'edit') {
    updateCliente()
  }
}

export const createVehiculo = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    await api.post('/vehiculos', vehiculoForm.value)
    await loadVehiculos()
    closeModal()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo crear el vehiculo'
  } finally {
    loading.value = false
  }
}

export const updateVehiculo = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    await api.put(`/vehiculos/${currentEditingId.value}`, vehiculoForm.value)
    await loadVehiculos()
    closeModal()
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'No se pudo actualizar el vehiculo'
  } finally {
    loading.value = false
  }
}

export const handleVehiculoSubmit = () => {
  if (modalMode.value === 'create') {
    createVehiculo()
  } else if (modalMode.value === 'edit') {
    updateVehiculo()
  }
}

export const viewUsuario = (usuario) => {
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

export const editUsuario = (usuario) => {
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

export const openResetPasswordModal = () => {
  if (!currentEditingId.value) return
  resetPasswordValue.value = ''
  resetPasswordError.value = ''
  showResetPasswordModal.value = true
}

export const closeResetPasswordModal = () => {
  showResetPasswordModal.value = false
  resetPasswordValue.value = ''
  resetPasswordError.value = ''
}

export const submitResetPassword = async () => {
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

export const deleteUsuario = async (id) => {
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

export const viewCliente = (cliente) => {
  currentEditingId.value = cliente.id_cliente
  clienteForm.value = {
    nombres: cliente.nombres,
    apellidos: cliente.apellidos,
    carnet_identidad: cliente.carnet_identidad,
    telefono: cliente.telefono || '',
    estado: cliente.estado
  }
  modalMode.value = 'view'
  showModal.value = true
}

export const editCliente = (cliente) => {
  currentEditingId.value = cliente.id_cliente
  clienteForm.value = {
    nombres: cliente.nombres,
    apellidos: cliente.apellidos,
    carnet_identidad: cliente.carnet_identidad,
    telefono: cliente.telefono || '',
    estado: cliente.estado
  }
  modalMode.value = 'edit'
  showModal.value = true
}

export const deleteCliente = async (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
    try {
      // Implementar DELETE endpoint cuando sea necesario
      console.log('Eliminar cliente:', id)
    } catch (error) {
      console.error('Error al eliminar:', error)
    }
  }
}

export const viewVehiculo = (vehiculo) => {
  currentEditingId.value = vehiculo.id_vehiculo
  vehiculoForm.value = {
    placa: vehiculo.placa || '',
    id_cliente: vehiculo.id_cliente ?? null,
    descripcion: vehiculo.descripcion || '',
    estado: vehiculo.estado || 'activo'
  }
  modalMode.value = 'view'
  showModal.value = true
}

export const editVehiculo = (vehiculo) => {
  currentEditingId.value = vehiculo.id_vehiculo
  vehiculoForm.value = {
    placa: vehiculo.placa || '',
    id_cliente: vehiculo.id_cliente ?? null,
    descripcion: vehiculo.descripcion || '',
    estado: vehiculo.estado || 'activo'
  }
  modalMode.value = 'edit'
  showModal.value = true
}

export const deleteVehiculo = async (id) => {
  if (confirm('¿Estas seguro de que deseas eliminar este vehiculo?')) {
    try {
      loading.value = true
      errorMsg.value = ''
      await api.delete(`/vehiculos/${id}`)
      await loadVehiculos()
    } catch (error) {
      errorMsg.value = error.response?.data?.message || 'No se pudo eliminar el vehiculo'
    } finally {
      loading.value = false
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

export const initGestionData = async () => {
  await Promise.all([loadUsuarios(), loadClientes(), loadRoles(), loadVehiculos()])
}