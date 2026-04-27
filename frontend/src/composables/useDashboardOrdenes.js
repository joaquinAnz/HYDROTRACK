import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import logo from '../assets/logo.png'

export function useDashboardOrdenes() {
  const router = useRouter()
  const busqueda = ref('')
  const ordenes = ref([])
  const repuestos = ref([])
  const estadosOrden = ref([])
  const cargando = ref(false)
  const error = ref('')
  const tabActual = ref('pendientes')

  const mostrarModalPago = ref(false)
  const mostrarModalActualizacion = ref(false)
  const mostrarModalDetalleOrden = ref(false)
  const mostrarModalVerOrden = ref(false)
  const cargandoDetalleOrden = ref(false)
  const ordenDetalleVista = ref(null)
  const actualizacionesOrden = ref([])

  const pagoForm = ref({
    id_orden: null,
    codigo_seguimiento: '',
    total_orden: 0,
    total_pagado: 0,
    saldo_pendiente: 0,
    metodos: [{ metodo: 'qr', monto: 0 }],
    observacion: ''
  })

  const actualizacionForm = ref({
    id_orden: null,
    codigo_seguimiento: '',
    estado_objetivo: 'en_proceso',
    descripcion: ''
  })

  const detalleOrdenForm = ref({
    id_orden: null,
    codigo_seguimiento: '',
    descripcion_servicio: '',
    monto_servicio: 0,
    id_repuesto: '',
    cantidad_repuesto: 1,
    precio_repuesto: 0
  })

  const normalizar = (valor = '') => {
    return String(valor)
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim()
  }

  const tipoServicioNombre = (orden) => {
    return orden?.tipo_servicio?.nombre || orden?.tipoServicio?.nombre || '-'
  }

  const esVehiculoVirtualVenta = (orden) => {
    const placa = String(orden?.vehiculo?.placa || '').toUpperCase()
    return placa.startsWith('MST-')
  }

  const esOrdenVenta = (orden) => {
    const tipoServicio = normalizar(tipoServicioNombre(orden))
    return esVehiculoVirtualVenta(orden) || tipoServicio.includes('venta')
  }

  const clienteNombre = (orden) => {
    if (esVehiculoVirtualVenta(orden)) return 'Sin cliente (venta)'

    const cliente = orden?.vehiculo?.cliente
    if (!cliente) return '-'
    return `${cliente?.nombres || ''} ${cliente?.apellidos || ''}`.trim() || '-'
  }

  const clienteCarnet = (orden) => {
    if (esVehiculoVirtualVenta(orden)) return '-'

    const cliente = orden?.vehiculo?.cliente
    return cliente?.carnet_identidad || cliente?.carnet || cliente?.ci || '-'
  }

  const placaVisible = (orden) => {
    if (esVehiculoVirtualVenta(orden)) return '-'
    return orden?.vehiculo?.placa || '-'
  }

  const moneda = (valor) => {
    const numero = Number(valor || 0)
    return `Bs ${numero.toFixed(2)}`
  }

  const totalMetodosPago = computed(() => {
    return pagoForm.value.metodos.reduce((acc, item) => acc + Number(item.monto || 0), 0)
  })

  const metodosPagoTexto = (pago) => {
    const metodos = Array.isArray(pago?.metodos) ? pago.metodos : []
    if (!metodos.length) return '-'

    return metodos
      .map((item) => `${String(item.metodo || '').toUpperCase()} ${moneda(item.monto || 0)}`)
      .join(' | ')
  }

  const totalPagadoOrden = (orden) => {
    const totalPagadoCampo = Number(orden?.total_pagado || 0)
    if (totalPagadoCampo > 0) return totalPagadoCampo

    const pagos = Array.isArray(orden?.pagos) ? orden.pagos : []
    return pagos.reduce((acc, pago) => {
      const porPago = Number(pago?.monto_total || pago?.monto || 0)
      if (porPago > 0) return acc + porPago

      const metodos = Array.isArray(pago?.metodos) ? pago.metodos : []
      return acc + metodos.reduce((sum, metodo) => sum + Number(metodo?.monto || 0), 0)
    }, 0)
  }

  const esOrdenCompleta = (orden) => {
    const marcadaCompleta = Number(orden?.pago_completo) === 1
    if (marcadaCompleta) return true

    const total = Number(orden?.total_orden || 0)
    const pagado = totalPagadoOrden(orden)
    return total > 0 && pagado >= total
  }

  const textoEstadoPago = (orden) => {
    if (esOrdenCompleta(orden)) return 'COMPLETO'
    if (totalPagadoOrden(orden) > 0) return 'INCOMPLETO'
    return 'PENDIENTE'
  }

  const claseEstadoPago = (orden) => {
    if (esOrdenCompleta(orden)) return 'badge-completo'
    if (totalPagadoOrden(orden) > 0) return 'badge-incompleto'
    return 'badge-pendiente'
  }

  const totalPendientes = computed(() => {
    return ordenes.value.filter((orden) => !esOrdenCompleta(orden)).length
  })

  const totalCompletadas = computed(() => {
    return ordenes.value.filter((orden) => esOrdenCompleta(orden)).length
  })

  const totalTodas = computed(() => {
    return ordenes.value.length
  })

  const ordenesFiltradas = computed(() => {
    const term = normalizar(busqueda.value)

    const listaBase = [...ordenes.value]
      .filter((orden) => {
        if (tabActual.value === 'pendientes') return !esOrdenCompleta(orden)
        if (tabActual.value === 'completadas') return esOrdenCompleta(orden)
        return true
      })
      .sort((a, b) => {
        const idA = Number(a?.id_orden || 0)
        const idB = Number(b?.id_orden || 0)

        if (idA !== idB) return idB - idA

        const fechaA = new Date(a?.fecha_ingreso || 0).getTime()
        const fechaB = new Date(b?.fecha_ingreso || 0).getTime()
        return fechaB - fechaA
      })

    if (!term) return listaBase

    return listaBase.filter((orden) => {
      const campos = [
        orden?.codigo_seguimiento,
        orden?.id_orden,
        clienteNombre(orden),
        clienteCarnet(orden),
        orden?.vehiculo?.placa,
        tipoServicioNombre(orden),
        orden?.estado?.nombre,
      ]

      return campos.some((valor) => normalizar(valor).includes(term))
    })
  })

  const cargarOrdenes = async () => {
    cargando.value = true
    error.value = ''

    try {
      const response = await api.get('/ordenes-trabajo')
      ordenes.value = response.data?.data || []
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo cargar la lista de ordenes'
    } finally {
      cargando.value = false
    }
  }

  const cargarRepuestos = async () => {
    try {
      const response = await api.get('/repuestos', { params: { activos: 1 } })
      const data = response.data?.data ?? response.data ?? []
      repuestos.value = data.filter((r) => Number(r.stock_actual || 0) > 0)
    } catch (_) {
      repuestos.value = []
    }
  }

  const cargarEstadosOrden = async () => {
    try {
      const response = await api.get('/estados-orden')
      estadosOrden.value = response.data?.data || []
    } catch (_) {
      estadosOrden.value = []
    }
  }

  const obtenerIdEstadoObjetivo = (estadoObjetivo) => {
    const clave = estadoObjetivo === 'completado' ? 'complet' : 'proceso'
    const estado = estadosOrden.value.find((item) => normalizar(item?.nombre).includes(clave))
    return estado?.id_estado || null
  }

  const agregarMetodoPago = () => {
    pagoForm.value.metodos.push({ metodo: 'efectivo', monto: 0 })
  }

  const eliminarMetodoPago = (index) => {
    if (pagoForm.value.metodos.length === 1) return
    pagoForm.value.metodos.splice(index, 1)
  }

  const abrirModalPago = (orden) => {
    const totalOrden = Number(orden?.total_orden || 0)
    const totalPagado = Number(orden?.total_pagado || totalPagadoOrden(orden))
    const saldo = Math.max(0, totalOrden - totalPagado)

    if (totalOrden <= 0 || saldo <= 0) {
      error.value = 'No hay saldo pendiente para registrar pago en esta orden'
      return
    }

    pagoForm.value = {
      id_orden: orden?.id_orden,
      codigo_seguimiento: orden?.codigo_seguimiento || String(orden?.id_orden || ''),
      total_orden: totalOrden,
      total_pagado: totalPagado,
      saldo_pendiente: saldo,
      metodos: [{ metodo: 'qr', monto: 0 }],
      observacion: ''
    }

    error.value = ''
    mostrarModalPago.value = true
  }

  const cerrarModalPago = () => {
    mostrarModalPago.value = false
  }

  const registrarPago = async () => {
    if (!pagoForm.value.id_orden) {
      error.value = 'No se encontro la orden seleccionada'
      return
    }

    const metodos = pagoForm.value.metodos.map((item) => ({
      metodo: String(item.metodo || '').toLowerCase(),
      monto: Number(item.monto || 0)
    }))

    if (metodos.some((item) => !['efectivo', 'qr', 'tarjeta'].includes(item.metodo) || item.monto <= 0)) {
      error.value = 'Verifica metodos y montos del pago'
      return
    }

    if (totalMetodosPago.value > Number(pagoForm.value.saldo_pendiente || 0)) {
      error.value = 'El pago supera el saldo pendiente'
      return
    }

    try {
      await api.post('/pagos-orden', {
        id_orden: pagoForm.value.id_orden,
        metodos,
        observacion: pagoForm.value.observacion
      })

      await api.post(`/ordenes-trabajo/${pagoForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se registro pago por Bs ${totalMetodosPago.value.toFixed(2)}`,
        tipo: 'pago',
        id_usuario: 1
      })

      await cargarOrdenes()
      cerrarModalPago()
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo registrar el pago'
    }
  }

  const cargarActualizacionesOrden = async (idOrden) => {
    try {
      const response = await api.get(`/ordenes-trabajo/${idOrden}/actualizaciones`)
      actualizacionesOrden.value = response.data?.data || []
    } catch (e) {
      actualizacionesOrden.value = []
      error.value = e.response?.data?.message || 'No se pudo cargar historial'
    }
  }

  const abrirModalActualizacion = async (orden) => {
    const estadoNombre = normalizar(orden?.estado?.nombre || '')
    const estadoObjetivoInicial = estadoNombre.includes('complet') ? 'completado' : 'en_proceso'

    actualizacionForm.value = {
      id_orden: orden?.id_orden,
      codigo_seguimiento: orden?.codigo_seguimiento || String(orden?.id_orden || ''),
      estado_objetivo: estadoObjetivoInicial,
      descripcion: ''
    }

    error.value = ''
    await cargarActualizacionesOrden(orden?.id_orden)
    mostrarModalActualizacion.value = true
  }

  const cerrarModalActualizacion = () => {
    mostrarModalActualizacion.value = false
  }

  const registrarActualizacion = async () => {
    if (!actualizacionForm.value.id_orden) {
      error.value = 'Debes seleccionar una orden'
      return
    }

    const descripcion = String(actualizacionForm.value.descripcion || '').trim()
    const idEstadoObjetivo = obtenerIdEstadoObjetivo(actualizacionForm.value.estado_objetivo)

    if (!descripcion) {
      error.value = 'Debes escribir una actualizacion'
      return
    }

    if (!idEstadoObjetivo) {
      error.value = 'No se encontro el estado seleccionado en la base de datos'
      return
    }

    try {
      await api.post(`/ordenes-trabajo/${actualizacionForm.value.id_orden}/actualizaciones`, {
        descripcion,
        tipo: 'actualizacion',
        id_usuario: 1
      })

      await api.put(`/ordenes-trabajo/${actualizacionForm.value.id_orden}`, {
        id_estado: idEstadoObjetivo
      })

      actualizacionForm.value.descripcion = ''
      await cargarActualizacionesOrden(actualizacionForm.value.id_orden)
      await cargarOrdenes()
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo guardar la actualizacion'
    }
  }

  const abrirModalDetalleOrden = (orden) => {
    const primerRepuesto = repuestos.value[0]
    detalleOrdenForm.value = {
      id_orden: orden?.id_orden,
      codigo_seguimiento: orden?.codigo_seguimiento || String(orden?.id_orden || ''),
      descripcion_servicio: '',
      monto_servicio: 0,
      id_repuesto: primerRepuesto?.id_repuesto || '',
      cantidad_repuesto: 1,
      precio_repuesto: Number(primerRepuesto?.precio_venta || 0)
    }
    mostrarModalDetalleOrden.value = true
  }

  const cerrarModalDetalleOrden = () => {
    mostrarModalDetalleOrden.value = false
  }

  const abrirModalVerOrden = async (orden) => {
    if (!orden?.id_orden) return

    mostrarModalVerOrden.value = true
    cargandoDetalleOrden.value = true
    ordenDetalleVista.value = null
    error.value = ''

    try {
      const response = await api.get(`/ordenes-trabajo/${orden.id_orden}`)
      ordenDetalleVista.value = response.data?.data || null
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo cargar el detalle de la orden'
    } finally {
      cargandoDetalleOrden.value = false
    }
  }

  const cerrarModalVerOrden = () => {
    mostrarModalVerOrden.value = false
    ordenDetalleVista.value = null
  }

  const alCambiarRepuestoDetalle = () => {
    const repuesto = repuestos.value.find((item) => String(item.id_repuesto) === String(detalleOrdenForm.value.id_repuesto))
    if (!repuesto) return
    detalleOrdenForm.value.precio_repuesto = Number(repuesto.precio_venta || 0)
  }

  const agregarServicioAOrdenExistente = async () => {
    const descripcion = String(detalleOrdenForm.value.descripcion_servicio || '').trim()
    const monto = Number(detalleOrdenForm.value.monto_servicio || 0)
    if (!detalleOrdenForm.value.id_orden || !descripcion || monto <= 0) {
      error.value = 'Completa descripcion y monto del servicio'
      return
    }

    try {
      const servicio = await api.post('/servicios-adicionales', {
        nombre: `MANUAL-${detalleOrdenForm.value.id_orden}-${Date.now()}`.slice(0, 100),
        descripcion,
        costo_base: monto,
        precio: monto,
        estado: 0
      })

      const idServicio = servicio.data?.data?.id_servicio

      await api.post('/detalle-servicio-orden', {
        id_orden: detalleOrdenForm.value.id_orden,
        id_servicio: idServicio,
        cantidad: 1,
        precio_unitario: monto
      })

      await api.post(`/ordenes-trabajo/${detalleOrdenForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se agrego servicio: ${descripcion} (Bs ${monto.toFixed(2)})`,
        tipo: 'servicio',
        id_usuario: 1
      })

      detalleOrdenForm.value.descripcion_servicio = ''
      detalleOrdenForm.value.monto_servicio = 0
      await cargarOrdenes()
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo agregar servicio'
    }
  }

  const agregarRepuestoAOrdenExistente = async () => {
    const idRepuesto = detalleOrdenForm.value.id_repuesto
    const cantidad = Number(detalleOrdenForm.value.cantidad_repuesto || 0)
    const precio = Number(detalleOrdenForm.value.precio_repuesto || 0)
    if (!detalleOrdenForm.value.id_orden || !idRepuesto || cantidad <= 0 || precio <= 0) {
      error.value = 'Completa datos del repuesto'
      return
    }

    try {
      await api.post('/detalle-repuesto-orden', {
        id_orden: detalleOrdenForm.value.id_orden,
        id_repuesto: idRepuesto,
        cantidad,
        precio_unitario: precio
      })

      const repuesto = repuestos.value.find((item) => String(item.id_repuesto) === String(idRepuesto))
      await api.post(`/ordenes-trabajo/${detalleOrdenForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se agrego repuesto: ${repuesto?.nombre || 'Repuesto'} x${cantidad} (Bs ${precio.toFixed(2)})`,
        tipo: 'repuesto',
        id_usuario: 1
      })

      await cargarOrdenes()
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo agregar repuesto'
    }
  }

  const goDashboard = () => {
    router.push('/dashboard')
  }

  onMounted(cargarOrdenes)
  onMounted(cargarRepuestos)
  onMounted(cargarEstadosOrden)

  return {
    logo,
    busqueda,
    ordenes,
    repuestos,
    estadosOrden,
    cargando,
    error,
    tabActual,
    mostrarModalPago,
    mostrarModalActualizacion,
    mostrarModalDetalleOrden,
    mostrarModalVerOrden,
    cargandoDetalleOrden,
    ordenDetalleVista,
    actualizacionesOrden,
    pagoForm,
    actualizacionForm,
    detalleOrdenForm,
    totalMetodosPago,
    totalPendientes,
    totalCompletadas,
    totalTodas,
    ordenesFiltradas,
    tipoServicioNombre,
    esOrdenVenta,
    clienteNombre,
    clienteCarnet,
    placaVisible,
    moneda,
    metodosPagoTexto,
    textoEstadoPago,
    claseEstadoPago,
    cargarOrdenes,
    cargarRepuestos,
    cargarEstadosOrden,
    agregarMetodoPago,
    eliminarMetodoPago,
    abrirModalPago,
    cerrarModalPago,
    registrarPago,
    abrirModalActualizacion,
    cerrarModalActualizacion,
    registrarActualizacion,
    abrirModalDetalleOrden,
    cerrarModalDetalleOrden,
    abrirModalVerOrden,
    cerrarModalVerOrden,
    alCambiarRepuestoDetalle,
    agregarServicioAOrdenExistente,
    agregarRepuestoAOrdenExistente,
    goDashboard,
  }
}
