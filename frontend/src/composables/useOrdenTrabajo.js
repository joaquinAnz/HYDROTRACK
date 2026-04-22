import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

export function useOrdenTrabajo() {
  const API_BASE = 'http://127.0.0.1:8000/api'

  const clientes = ref([])
  const vehiculos = ref([])
  const tecnicos = ref([])
  const estados = ref([])
  const tiposServicio = ref([])
  const serviciosAdicionales = ref([])

  const mensaje = ref('')
  const error = ref('')

  const mostrarPanelServicios = ref(false)
  const detalleServicios = ref([])

  const form = ref({
    id_cliente: '',
    id_vehiculo: '',
    id_tipo_servicio: '',
    id_tecnico: '',
    descripcion_falla: '',
    fecha_ingreso: '',
    fecha_estimada_entrega: '',
    diagnostico: '',
    id_estado: '',
    observaciones: '',
    costo_mano_obra: 0
  })

  const vehiculosFiltrados = computed(() => {
    if (!form.value.id_cliente) return []
    return vehiculos.value.filter(
      (v) => String(v.id_cliente) === String(form.value.id_cliente)
    )
  })

  const totalServicios = computed(() => {
    return detalleServicios.value.reduce((acc, item) => {
      return acc + Number(item.cantidad) * Number(item.precio_unitario)
    }, 0)
  })

  const totalEstimado = computed(() => {
    return Number(form.value.costo_mano_obra || 0) + totalServicios.value
  })

  const moneda = (valor) => {
    const numero = Number(valor || 0)
    return `Bs ${numero.toFixed(2)}`
  }

  const limpiarFormulario = () => {
    form.value = {
      id_cliente: '',
      id_vehiculo: '',
      id_tipo_servicio: '',
      id_tecnico: '',
      descripcion_falla: '',
      fecha_ingreso: '',
      fecha_estimada_entrega: '',
      diagnostico: '',
      id_estado: '',
      observaciones: '',
      costo_mano_obra: 0
    }

    detalleServicios.value = []
    mensaje.value = ''
    error.value = ''
    mostrarPanelServicios.value = false
  }

  const normalizarRespuesta = (response) => {
    return response.data?.data ?? response.data ?? []
  }

  const cargarClientes = async () => {
    try {
      const response = await axios.get(`${API_BASE}/clientes`)
      clientes.value = normalizarRespuesta(response)
    } catch (e) {
      console.error('Error cargando clientes', e)
    }
  }

  const cargarVehiculos = async () => {
    try {
      const response = await axios.get(`${API_BASE}/vehiculos`)
      vehiculos.value = normalizarRespuesta(response)
    } catch (e) {
      console.error('Error cargando vehículos', e)
    }
  }

  const cargarEstados = async () => {
    try {
      const response = await axios.get(`${API_BASE}/estados-orden`)
      estados.value = normalizarRespuesta(response)
    } catch (e) {
      console.error('Error cargando estados', e)
    }
  }

  const cargarTiposServicio = async () => {
    try {
      const response = await axios.get(`${API_BASE}/tipos-servicio`)
      tiposServicio.value = normalizarRespuesta(response)
    } catch (e) {
      console.error('Error cargando tipos de servicio', e)
    }
  }

  const cargarServiciosAdicionales = async () => {
    try {
      const response = await axios.get(`${API_BASE}/servicios-adicionales`)
      serviciosAdicionales.value = normalizarRespuesta(response).filter(
        (s) => Number(s.estado) === 1
      )
    } catch (e) {
      console.error('Error cargando servicios adicionales', e)
    }
  }

  const cargarTecnicos = async () => {
    try {
      const response = await axios.get(`${API_BASE}/usuarios`)
      const usuarios = normalizarRespuesta(response)

      tecnicos.value = usuarios.filter(
        (u) =>
          String(u.id_rol) === '2' ||
          String(u.rol_nombre || '').toLowerCase() === 'tecnico'
      )
    } catch (e) {
      console.error('Error cargando técnicos', e)
    }
  }

  const abrirPanelServicios = () => {
    mostrarPanelServicios.value = true
  }

  const cerrarPanelServicios = () => {
    mostrarPanelServicios.value = false
  }

  const agregarServicioAdicional = (servicio) => {
    const existente = detalleServicios.value.find(
      (item) => String(item.id_servicio) === String(servicio.id_servicio)
    )

    if (existente) {
      existente.cantidad += 1
      return
    }

    detalleServicios.value.push({
      id_servicio: servicio.id_servicio,
      nombre: servicio.nombre,
      descripcion: servicio.descripcion,
      cantidad: 1,
      precio_unitario: Number(servicio.costo_base || 0)
    })

    mostrarPanelServicios.value = false
  }

  const eliminarServicioDetalle = (index) => {
    detalleServicios.value.splice(index, 1)
  }

  const cargarDatos = async () => {
    await Promise.all([
      cargarClientes(),
      cargarVehiculos(),
      cargarEstados(),
      cargarTecnicos(),
      cargarTiposServicio(),
      cargarServiciosAdicionales()
    ])
  }

  const guardarOrden = async () => {
    mensaje.value = ''
    error.value = ''

    try {
      const payload = {
        id_vehiculo: form.value.id_vehiculo,
        id_tipo_servicio: form.value.id_tipo_servicio,
        id_tecnico: form.value.id_tecnico || null,
        id_usuario_registro: 1,
        descripcion_falla: form.value.descripcion_falla,
        diagnostico: form.value.diagnostico,
        observaciones: form.value.observaciones,
        costo_mano_obra: form.value.costo_mano_obra || 0,
        id_estado: form.value.id_estado
      }

      const response = await axios.post(`${API_BASE}/ordenes-trabajo`, payload)
      const ordenCreada = response.data?.data

      if (ordenCreada?.id_orden && detalleServicios.value.length > 0) {
        for (const item of detalleServicios.value) {
          await axios.post(`${API_BASE}/detalle-servicio-orden`, {
            id_orden: ordenCreada.id_orden,
            id_servicio: item.id_servicio,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario
          })
        }
      }

      mensaje.value = response.data?.message || 'Orden creada correctamente'
      limpiarFormulario()
      mensaje.value = response.data?.message || 'Orden creada correctamente'
    } catch (e) {
      console.error('Error al guardar orden', e)
      error.value =
        e.response?.data?.message ||
        'Ocurrió un error al guardar la orden'
    }
  }

  onMounted(cargarDatos)

  return {
    form,
    clientes,
    vehiculosFiltrados,
    tecnicos,
    estados,
    tiposServicio,
    serviciosAdicionales,
    detalleServicios,
    mostrarPanelServicios,
    totalServicios,
    totalEstimado,
    guardarOrden,
    mensaje,
    error,
    limpiarFormulario,
    moneda,
    abrirPanelServicios,
    cerrarPanelServicios,
    agregarServicioAdicional,
    eliminarServicioDetalle
  }
}