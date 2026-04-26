import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

export function useOrdenTrabajo() {
  const API_BASE = 'http://127.0.0.1:8000/api'

  const normalizarTexto = (texto = '') => {
    return String(texto)
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
  }

  const clientes = ref([])
  const vehiculos = ref([])
  const tecnicos = ref([])
  const estados = ref([])
  const tiposServicio = ref([])
  const repuestos = ref([])
  const ordenes = ref([])

  const mensaje = ref('')
  const error = ref('')

  const mostrarPanelServicios = ref(false)
  const mostrarPanelRepuestos = ref(false)
  const mostrarModalPago = ref(false)
  const mostrarModalActualizacion = ref(false)
  const mostrarModalDetalleOrden = ref(false)
  const mostrarModalEditarPago = ref(false)
  const detalleServicios = ref([])
  const detalleRepuestos = ref([])
  const actualizacionesOrden = ref([])
  const pagosOrdenSeleccionada = ref([])
  const nuevoServicioManual = ref({
    descripcion: '',
    precio_unitario: 0
  })

  const actualizacionForm = ref({
    id_orden: null,
    codigo_seguimiento: '',
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

  const pagoEditarForm = ref({
    id_pago_orden: null,
    id_orden: null,
    observacion: '',
    metodos: [{ metodo: 'efectivo', monto: 0 }]
  })

  const pagoForm = ref({
    id_orden: null,
    codigo_seguimiento: '',
    tipo_servicio: '',
    cliente: '',
    total_orden: 0,
    total_pagado: 0,
    saldo_pendiente: 0,
    metodos: [{ metodo: 'qr', monto: 0 }],
    observacion: ''
  })

  const form = ref({
    modo_atencion: '',
    id_cliente: '',
    id_vehiculo: '',
    id_tipo_servicio: '',
    id_tecnico: '',
    descripcion_falla: '',
    fecha_ingreso: '',
    fecha_estimada_entrega: '',
    diagnostico: '',
    id_estado: '',
    observaciones: ''
  })

  const opcionTipoServicio = ref('')

  const esSoloVenta = computed(() => opcionTipoServicio.value === 'solo_venta')

  const tiposServicioDisponibles = computed(() => {
    return tiposServicio.value.filter((t) => {
      const nombre = normalizarTexto(t.nombre || '')
      return nombre.includes('tecnico') || nombre.includes('venta')
    })
  })

  const clientesDisponibles = computed(() => {
    return clientes.value.filter((c) => {
      const carnet = String(c.carnet_identidad || c.carnet || '').trim()
      return carnet !== '0000000'
    })
  })

  const vehiculosDisponibles = computed(() => {
    return vehiculos.value.filter((v) => !String(v.placa || '').toUpperCase().startsWith('MST-'))
  })

  const vehiculosFiltrados = computed(() => {
    if (!form.value.id_cliente) return vehiculosDisponibles.value
    return vehiculosDisponibles.value.filter(
      (v) => String(v.id_cliente) === String(form.value.id_cliente)
    )
  })

  const puedeCompletarFormularioTecnico = computed(() => {
    return esSoloVenta.value || Boolean(form.value.id_cliente && form.value.id_vehiculo)
  })

  watch(
    () => form.value.id_vehiculo,
    (idVehiculo) => {
      if (esSoloVenta.value) return
      if (!idVehiculo) return

      const vehiculo = vehiculos.value.find(
        (v) => String(v.id_vehiculo) === String(idVehiculo)
      )

      if (!vehiculo) return

      if (String(form.value.id_cliente) !== String(vehiculo.id_cliente)) {
        form.value.id_cliente = vehiculo.id_cliente
      }
    }
  )

  watch(
    () => form.value.id_cliente,
    (idCliente) => {
      if (esSoloVenta.value) return
      if (!idCliente || !form.value.id_vehiculo) return

      const vehiculoPerteneceCliente = vehiculos.value.some(
        (v) =>
          String(v.id_vehiculo) === String(form.value.id_vehiculo) &&
          String(v.id_cliente) === String(idCliente)
      )

      if (!vehiculoPerteneceCliente) {
        form.value.id_vehiculo = ''
      }
    }
  )

  const totalServicios = computed(() => {
    return detalleServicios.value.reduce((acc, item) => {
      return acc + Number(item.cantidad) * Number(item.precio_unitario)
    }, 0)
  })

  const totalRepuestos = computed(() => {
    return detalleRepuestos.value.reduce((acc, item) => {
      return acc + Number(item.cantidad) * Number(item.precio_unitario)
    }, 0)
  })

  const totalEstimado = computed(() => {
    return totalServicios.value + totalRepuestos.value
  })

  const formularioIniciado = computed(() => {
    return Boolean(
      form.value.id_cliente ||
      form.value.id_vehiculo ||
      form.value.id_tecnico ||
      form.value.descripcion_falla ||
      form.value.diagnostico ||
      form.value.id_estado ||
      form.value.observaciones ||
      detalleServicios.value.length > 0 ||
      detalleRepuestos.value.length > 0
    )
  })

  const tipoBloqueado = computed(() => {
    return Boolean(opcionTipoServicio.value) && formularioIniciado.value
  })

  const sincronizarTipoServicio = () => {
    const candidatos = tiposServicioDisponibles.value
    if (!candidatos.length) return

    const buscado = opcionTipoServicio.value === 'solo_venta' ? 'venta' : 'tecnico'
    const encontrado = candidatos.find((t) => normalizarTexto(t.nombre || '').includes(buscado))

    if (encontrado) {
      form.value.id_tipo_servicio = encontrado.id_tipo_servicio
      form.value.modo_atencion = opcionTipoServicio.value
    }
  }

  const obtenerNombreTipoServicio = (orden) => {
    return (
      orden?.tipo_servicio?.nombre ||
      orden?.tipoServicio?.nombre ||
      tiposServicio.value.find(
        (t) => String(t.id_tipo_servicio) === String(orden?.id_tipo_servicio)
      )?.nombre ||
      '-'
    )
  }

  watch(opcionTipoServicio, () => {
    sincronizarTipoServicio()

    if (esSoloVenta.value) {
      form.value.id_cliente = ''
      form.value.id_vehiculo = ''
      form.value.id_tecnico = ''
      form.value.id_estado = ''
      form.value.descripcion_falla = ''
      form.value.diagnostico = ''
      detalleServicios.value = []
      mostrarPanelServicios.value = false
    }
  })

  const moneda = (valor) => {
    const numero = Number(valor || 0)
    return `Bs ${numero.toFixed(2)}`
  }

  const limpiarFormulario = () => {
    form.value = {
      modo_atencion: '',
      id_cliente: '',
      id_vehiculo: '',
      id_tipo_servicio: '',
      id_tecnico: '',
      descripcion_falla: '',
      fecha_ingreso: '',
      fecha_estimada_entrega: '',
      diagnostico: '',
      id_estado: '',
      observaciones: ''
    }

    detalleServicios.value = []
    detalleRepuestos.value = []
    opcionTipoServicio.value = ''
    mensaje.value = ''
    error.value = ''
    mostrarPanelServicios.value = false
    mostrarPanelRepuestos.value = false
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

  const cargarRepuestos = async () => {
    try {
      const response = await axios.get(`${API_BASE}/repuestos`, {
        params: { activos: 1 }
      })
      const items = normalizarRespuesta(response)
      repuestos.value = items.filter((r) => Number(r.stock_actual || 0) > 0)
    } catch (e) {
      console.error('Error cargando repuestos', e)
    }
  }

  const cargarTecnicos = async () => {
    try {
      const response = await axios.get(`${API_BASE}/usuarios`)
      const usuarios = normalizarRespuesta(response)

      tecnicos.value = usuarios.filter(
        (u) =>
          String(u.id_rol) === '2' ||
          String(u.rol_nombre || u.rol || '').toLowerCase() === 'tecnico'
      )
    } catch (e) {
      console.error('Error cargando técnicos', e)
    }
  }

  const abrirPanelServicios = () => {
    if (!puedeCompletarFormularioTecnico.value) {
      error.value = 'Primero selecciona cliente y vehiculo para continuar.'
      return
    }

    nuevoServicioManual.value = {
      descripcion: '',
      precio_unitario: 0
    }
    mostrarPanelServicios.value = true
  }

  const cerrarPanelServicios = () => {
    nuevoServicioManual.value = {
      descripcion: '',
      precio_unitario: 0
    }
    mostrarPanelServicios.value = false
  }

  const abrirPanelRepuestos = () => {
    if (!puedeCompletarFormularioTecnico.value) {
      error.value = 'Primero selecciona cliente y vehiculo para continuar.'
      return
    }

    mostrarPanelRepuestos.value = true
  }

  const cerrarPanelRepuestos = () => {
    mostrarPanelRepuestos.value = false
  }

  const agregarServicioAdicional = () => {
    const descripcion = String(nuevoServicioManual.value.descripcion || '').trim()
    const precio = Number(nuevoServicioManual.value.precio_unitario || 0)

    if (!descripcion) {
      error.value = 'Debes ingresar la descripcion del servicio'
      return
    }

    if (precio <= 0) {
      error.value = 'Debes ingresar un monto de servicio mayor a 0'
      return
    }

    detalleServicios.value.push({
      id_servicio: null,
      nombre: descripcion,
      descripcion,
      cantidad: 1,
      precio_unitario: precio
    })

    error.value = ''
    cerrarPanelServicios()
  }

  const eliminarServicioDetalle = (index) => {
    detalleServicios.value.splice(index, 1)
  }

  const agregarRepuesto = (repuesto) => {
    const existente = detalleRepuestos.value.find(
      (item) => String(item.id_repuesto) === String(repuesto.id_repuesto)
    )

    if (existente) {
      if (existente.cantidad < Number(repuesto.stock_actual || 0)) {
        existente.cantidad += 1
      }
      return
    }

    detalleRepuestos.value.push({
      id_repuesto: repuesto.id_repuesto,
      nombre: repuesto.nombre,
      descripcion: repuesto.descripcion,
      stock_actual: Number(repuesto.stock_actual || 0),
      cantidad: 1,
      precio_unitario: Number(repuesto.precio_venta || 0)
    })

    mostrarPanelRepuestos.value = false
  }

  const eliminarRepuestoDetalle = (index) => {
    detalleRepuestos.value.splice(index, 1)
  }

  const cargarDatos = async () => {
    await Promise.all([
      cargarClientes(),
      cargarVehiculos(),
      cargarEstados(),
      cargarTecnicos(),
      cargarTiposServicio(),
      cargarRepuestos(),
      cargarOrdenes()
    ])

    sincronizarTipoServicio()
  }

  const guardarOrden = async () => {
    mensaje.value = ''
    error.value = ''

    if (!opcionTipoServicio.value) {
      error.value = 'Selecciona primero el tipo de servicio'
      return
    }

    if (esSoloVenta.value && detalleRepuestos.value.length === 0) {
      error.value = 'En venta directa debes agregar al menos un repuesto'
      return
    }

    if (!esSoloVenta.value && !form.value.id_cliente) {
      error.value = 'En servicio tecnico debes seleccionar un cliente'
      return
    }

    try {
      const payload = esSoloVenta.value
        ? {
            modo_atencion: 'solo_venta',
            id_cliente: form.value.id_cliente || null,
            id_tipo_servicio: form.value.id_tipo_servicio,
            id_usuario_registro: 1,
            observaciones: form.value.observaciones
          }
        : {
            modo_atencion: 'servicio_tecnico',
            id_cliente: form.value.id_cliente,
            id_vehiculo: form.value.id_vehiculo,
            id_tipo_servicio: form.value.id_tipo_servicio,
            id_tecnico: form.value.id_tecnico || null,
            id_usuario_registro: 1,
            descripcion_falla: form.value.descripcion_falla,
            diagnostico: form.value.diagnostico,
            observaciones: form.value.observaciones,
            costo_mano_obra: 0,
            id_estado: form.value.id_estado
          }

      const response = await axios.post(`${API_BASE}/ordenes-trabajo`, payload)
      const ordenCreada = response.data?.data

      if (ordenCreada?.id_orden && detalleServicios.value.length > 0) {
        for (let index = 0; index < detalleServicios.value.length; index += 1) {
          const item = detalleServicios.value[index]

          const servicioManualPayload = {
            nombre: `MANUAL-${ordenCreada.id_orden}-${index + 1}-${Date.now()}`.slice(0, 100),
            descripcion: item.descripcion || item.nombre,
            costo_base: Number(item.precio_unitario || 0),
            precio: Number(item.precio_unitario || 0),
            estado: 0
          }

          const servicioCreadoResponse = await axios.post(
            `${API_BASE}/servicios-adicionales`,
            servicioManualPayload
          )

          const idServicioCreado = servicioCreadoResponse.data?.data?.id_servicio

          if (!idServicioCreado) {
            throw new Error('No se pudo crear el servicio manual')
          }

          await axios.post(`${API_BASE}/detalle-servicio-orden`, {
            id_orden: ordenCreada.id_orden,
            id_servicio: idServicioCreado,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario
          })
        }
      }

      if (ordenCreada?.id_orden && detalleRepuestos.value.length > 0) {
        for (const item of detalleRepuestos.value) {
          await axios.post(`${API_BASE}/detalle-repuesto-orden`, {
            id_orden: ordenCreada.id_orden,
            id_repuesto: item.id_repuesto,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario
          })
        }
      }

      mensaje.value = response.data?.message || 'Orden creada correctamente'
      await cargarOrdenes()
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

  const calcularTotalPagado = (orden) => {
    const pagos = Array.isArray(orden?.pagos) ? orden.pagos : []
    return pagos.reduce((acc, pago) => acc + Number(pago.monto || 0), 0)
  }

  const cargarOrdenes = async () => {
    try {
      const response = await axios.get(`${API_BASE}/ordenes-trabajo`)
      ordenes.value = normalizarRespuesta(response)
    } catch (e) {
      console.error('Error cargando ordenes', e)
    }
  }

  const abrirModalPago = (orden) => {
    const totalOrden = Number(orden?.total_orden || 0)
    const totalPagado = Number(orden?.total_pagado || calcularTotalPagado(orden))
    const saldo = Math.max(0, totalOrden - totalPagado)

    pagoForm.value = {
      id_orden: orden?.id_orden,
      codigo_seguimiento: orden?.codigo_seguimiento || '',
      tipo_servicio: orden?.tipo_servicio?.nombre || orden?.tipoServicio?.nombre || 'N/A',
      cliente: orden?.vehiculo?.cliente
        ? `${orden.vehiculo.cliente.nombres || ''} ${orden.vehiculo.cliente.apellidos || ''}`.trim()
        : 'N/A',
      total_orden: totalOrden,
      total_pagado: totalPagado,
      saldo_pendiente: saldo,
      metodos: [{ metodo: 'qr', monto: 0 }],
      observacion: ''
    }

    if (saldo <= 0) {
      error.value = 'Esta orden ya se encuentra pagada completamente'
      return
    }

    error.value = ''
    mostrarModalPago.value = true
  }

  const cerrarModalPago = () => {
    mostrarModalPago.value = false
  }

  const cargarActualizacionesOrden = async (idOrden) => {
    try {
      const response = await axios.get(`${API_BASE}/ordenes-trabajo/${idOrden}/actualizaciones`)
      actualizacionesOrden.value = normalizarRespuesta(response)
    } catch (e) {
      actualizacionesOrden.value = []
      error.value = e.response?.data?.message || 'No se pudo cargar el historial de la orden'
    }
  }

  const abrirModalActualizacion = async (orden) => {
    actualizacionForm.value = {
      id_orden: orden?.id_orden,
      codigo_seguimiento: orden?.codigo_seguimiento || String(orden?.id_orden || ''),
      descripcion: ''
    }
    error.value = ''
    await cargarActualizacionesOrden(orden?.id_orden)
    mostrarModalActualizacion.value = true
  }

  const cerrarModalActualizacion = () => {
    mostrarModalActualizacion.value = false
    actualizacionesOrden.value = []
  }

  const registrarActualizacion = async () => {
    if (!actualizacionForm.value.id_orden) {
      error.value = 'No se encontro la orden para actualizar'
      return
    }

    if (!String(actualizacionForm.value.descripcion || '').trim()) {
      error.value = 'Debes escribir una descripcion de actualizacion'
      return
    }

    try {
      await axios.post(`${API_BASE}/ordenes-trabajo/${actualizacionForm.value.id_orden}/actualizaciones`, {
        descripcion: actualizacionForm.value.descripcion,
        tipo: 'actualizacion',
        id_usuario: 1
      })

      actualizacionForm.value.descripcion = ''
      await cargarActualizacionesOrden(actualizacionForm.value.id_orden)
      await cargarOrdenes()
      mensaje.value = 'Actualizacion registrada correctamente'
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo registrar la actualizacion'
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
    error.value = ''
    mostrarModalDetalleOrden.value = true
  }

  const cerrarModalDetalleOrden = () => {
    mostrarModalDetalleOrden.value = false
  }

  const alCambiarRepuestoDetalle = () => {
    const repuesto = repuestos.value.find(
      (item) => String(item.id_repuesto) === String(detalleOrdenForm.value.id_repuesto)
    )
    if (!repuesto) return
    detalleOrdenForm.value.precio_repuesto = Number(repuesto.precio_venta || 0)
  }

  const agregarServicioAOrdenExistente = async () => {
    const descripcion = String(detalleOrdenForm.value.descripcion_servicio || '').trim()
    const monto = Number(detalleOrdenForm.value.monto_servicio || 0)

    if (!detalleOrdenForm.value.id_orden) {
      error.value = 'No se encontro la orden seleccionada'
      return
    }

    if (!descripcion || monto <= 0) {
      error.value = 'Completa la descripcion y monto del servicio'
      return
    }

    try {
      const servicioCreadoResponse = await axios.post(`${API_BASE}/servicios-adicionales`, {
        nombre: `MANUAL-${detalleOrdenForm.value.id_orden}-${Date.now()}`.slice(0, 100),
        descripcion,
        costo_base: monto,
        precio: monto,
        estado: 0
      })

      const idServicio = servicioCreadoResponse.data?.data?.id_servicio

      await axios.post(`${API_BASE}/detalle-servicio-orden`, {
        id_orden: detalleOrdenForm.value.id_orden,
        id_servicio: idServicio,
        cantidad: 1,
        precio_unitario: monto
      })

      await axios.post(`${API_BASE}/ordenes-trabajo/${detalleOrdenForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se agrego servicio: ${descripcion} (Bs ${monto.toFixed(2)})`,
        tipo: 'servicio',
        id_usuario: 1
      })

      detalleOrdenForm.value.descripcion_servicio = ''
      detalleOrdenForm.value.monto_servicio = 0
      await cargarOrdenes()
      mensaje.value = 'Servicio agregado a la orden'
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo agregar el servicio a la orden'
    }
  }

  const agregarRepuestoAOrdenExistente = async () => {
    const idRepuesto = detalleOrdenForm.value.id_repuesto
    const cantidad = Number(detalleOrdenForm.value.cantidad_repuesto || 0)
    const precio = Number(detalleOrdenForm.value.precio_repuesto || 0)

    if (!detalleOrdenForm.value.id_orden) {
      error.value = 'No se encontro la orden seleccionada'
      return
    }

    if (!idRepuesto || cantidad <= 0 || precio <= 0) {
      error.value = 'Selecciona repuesto y completa cantidad/precio'
      return
    }

    try {
      await axios.post(`${API_BASE}/detalle-repuesto-orden`, {
        id_orden: detalleOrdenForm.value.id_orden,
        id_repuesto: idRepuesto,
        cantidad,
        precio_unitario: precio
      })

      const repuesto = repuestos.value.find((item) => String(item.id_repuesto) === String(idRepuesto))
      const nombre = repuesto?.nombre || 'Repuesto'

      await axios.post(`${API_BASE}/ordenes-trabajo/${detalleOrdenForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se agrego repuesto: ${nombre} x${cantidad} (Bs ${precio.toFixed(2)})`,
        tipo: 'repuesto',
        id_usuario: 1
      })

      await cargarOrdenes()
      mensaje.value = 'Repuesto agregado a la orden'
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo agregar el repuesto a la orden'
    }
  }

  const cargarPagosOrden = async (idOrden) => {
    try {
      const response = await axios.get(`${API_BASE}/pagos-orden/orden/${idOrden}`)
      pagosOrdenSeleccionada.value = normalizarRespuesta(response)
    } catch (e) {
      pagosOrdenSeleccionada.value = []
      error.value = e.response?.data?.message || 'No se pudieron cargar los pagos de la orden'
    }
  }

  const abrirModalEditarPago = async (orden) => {
    pagoEditarForm.value = {
      id_pago_orden: null,
      id_orden: orden?.id_orden,
      observacion: '',
      metodos: [{ metodo: 'efectivo', monto: 0 }]
    }
    await cargarPagosOrden(orden?.id_orden)
    mostrarModalEditarPago.value = true
  }

  const cerrarModalEditarPago = () => {
    mostrarModalEditarPago.value = false
    pagosOrdenSeleccionada.value = []
  }

  const seleccionarPagoParaEdicion = (pago) => {
    const metodos = Array.isArray(pago?.metodos) && pago.metodos.length
      ? pago.metodos.map((item) => ({
          metodo: String(item.metodo || '').toLowerCase(),
          monto: Number(item.monto || 0)
        }))
      : [{ metodo: 'efectivo', monto: Number(pago?.monto_total || 0) }]

    pagoEditarForm.value = {
      id_pago_orden: pago?.id_pago_orden,
      id_orden: pago?.id_orden,
      observacion: pago?.observacion || '',
      metodos
    }
  }

  const agregarMetodoPagoEdicion = () => {
    pagoEditarForm.value.metodos.push({ metodo: 'efectivo', monto: 0 })
  }

  const eliminarMetodoPagoEdicion = (index) => {
    if (pagoEditarForm.value.metodos.length === 1) return
    pagoEditarForm.value.metodos.splice(index, 1)
  }

  const totalMetodosPagoEdicion = computed(() => {
    return pagoEditarForm.value.metodos.reduce((acc, item) => acc + Number(item.monto || 0), 0)
  })

  const guardarEdicionPago = async () => {
    if (!pagoEditarForm.value.id_pago_orden) {
      error.value = 'Selecciona primero un pago para editar'
      return
    }

    const metodos = pagoEditarForm.value.metodos.map((item) => ({
      metodo: String(item.metodo || '').toLowerCase(),
      monto: Number(item.monto || 0)
    }))

    if (metodos.some((item) => !['efectivo', 'qr', 'tarjeta'].includes(item.metodo) || item.monto <= 0)) {
      error.value = 'Verifica metodo y monto de pago'
      return
    }

    try {
      await axios.put(`${API_BASE}/pagos-orden/${pagoEditarForm.value.id_pago_orden}`, {
        metodos,
        observacion: pagoEditarForm.value.observacion
      })

      await axios.post(`${API_BASE}/ordenes-trabajo/${pagoEditarForm.value.id_orden}/actualizaciones`, {
        descripcion: `Se edito el pago #${pagoEditarForm.value.id_pago_orden} (total Bs ${totalMetodosPagoEdicion.value.toFixed(2)})`,
        tipo: 'pago',
        id_usuario: 1
      })

      await cargarPagosOrden(pagoEditarForm.value.id_orden)
      await cargarOrdenes()
      mensaje.value = 'Pago actualizado correctamente'
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo actualizar el pago'
    }
  }

  const totalMetodosPago = computed(() => {
    const metodos = Array.isArray(pagoForm.value.metodos) ? pagoForm.value.metodos : []
    return metodos.reduce((acc, item) => acc + Number(item.monto || 0), 0)
  })

  const pagoCompletaSaldo = computed(() => {
    return Number(totalMetodosPago.value.toFixed(2)) === Number(Number(pagoForm.value.saldo_pendiente || 0).toFixed(2))
  })

  const resumenPago = computed(() => {
    if (totalMetodosPago.value <= 0) return ''
    if (pagoCompletaSaldo.value) return 'Pago de orden completado listo para registrar.'
    if (totalMetodosPago.value < Number(pagoForm.value.saldo_pendiente || 0)) return 'Adelanto de orden listo para registrar.'
    return 'El total ingresado supera el saldo pendiente.'
  })

  const agregarMetodoPago = () => {
    pagoForm.value.metodos.push({ metodo: 'efectivo', monto: 0 })
  }

  const eliminarMetodoPago = (index) => {
    if (pagoForm.value.metodos.length === 1) return
    pagoForm.value.metodos.splice(index, 1)
  }

  const registrarPago = async () => {
    error.value = ''

    if (!pagoForm.value.id_orden) {
      error.value = 'No se encontro la orden seleccionada'
      return
    }

    if (!Array.isArray(pagoForm.value.metodos) || pagoForm.value.metodos.length === 0) {
      error.value = 'Debes agregar al menos un metodo de pago'
      return
    }

    const metodosNormalizados = pagoForm.value.metodos.map((item) => ({
      metodo: String(item.metodo || '').toLowerCase(),
      monto: Number(item.monto || 0)
    }))

    const hayMetodoInvalido = metodosNormalizados.some(
      (item) => !['efectivo', 'qr', 'tarjeta'].includes(item.metodo)
    )

    if (hayMetodoInvalido) {
      error.value = 'Metodo de pago invalido. Usa efectivo, qr o tarjeta'
      return
    }

    const hayMontoInvalido = metodosNormalizados.some((item) => item.monto <= 0)
    if (hayMontoInvalido) {
      error.value = 'Cada metodo de pago debe tener un monto mayor a 0'
      return
    }

    if (totalMetodosPago.value <= 0) {
      error.value = 'El monto total debe ser mayor a 0'
      return
    }

    if (totalMetodosPago.value > Number(pagoForm.value.saldo_pendiente || 0)) {
      error.value = 'El total de metodos no puede superar el saldo pendiente'
      return
    }

    try {
      const response = await axios.post(`${API_BASE}/pagos-orden`, {
        id_orden: pagoForm.value.id_orden,
        metodos: metodosNormalizados,
        observacion: pagoForm.value.observacion
      })

      await cargarOrdenes()
      mensaje.value = response.data?.message || 'Pago registrado correctamente'
      cerrarModalPago()
    } catch (e) {
      error.value = e.response?.data?.message || 'No se pudo registrar el pago'
    }
  }

  return {
    form,
    opcionTipoServicio,
    esSoloVenta,
    tipoBloqueado,
    tiposServicioDisponibles,
    clientes,
    clientesDisponibles,
    vehiculosFiltrados,
    puedeCompletarFormularioTecnico,
    tecnicos,
    estados,
    tiposServicio,
    repuestos,
    ordenes,
    detalleServicios,
    detalleRepuestos,
    nuevoServicioManual,
    mostrarPanelServicios,
    mostrarPanelRepuestos,
    mostrarModalPago,
    mostrarModalActualizacion,
    mostrarModalDetalleOrden,
    mostrarModalEditarPago,
    pagoForm,
    actualizacionForm,
    detalleOrdenForm,
    pagoEditarForm,
    actualizacionesOrden,
    pagosOrdenSeleccionada,
    totalMetodosPago,
    totalMetodosPagoEdicion,
    pagoCompletaSaldo,
    resumenPago,
    totalServicios,
    totalRepuestos,
    totalEstimado,
    guardarOrden,
    mensaje,
    error,
    limpiarFormulario,
    moneda,
    abrirPanelServicios,
    cerrarPanelServicios,
    abrirPanelRepuestos,
    cerrarPanelRepuestos,
    agregarServicioAdicional,
    eliminarServicioDetalle,
    agregarRepuesto,
    eliminarRepuestoDetalle,
    sincronizarTipoServicio,
    obtenerNombreTipoServicio,
    agregarMetodoPago,
    eliminarMetodoPago,
    agregarMetodoPagoEdicion,
    eliminarMetodoPagoEdicion,
    abrirModalPago,
    cerrarModalPago,
    registrarPago,
    abrirModalActualizacion,
    cerrarModalActualizacion,
    registrarActualizacion,
    abrirModalDetalleOrden,
    cerrarModalDetalleOrden,
    alCambiarRepuestoDetalle,
    agregarServicioAOrdenExistente,
    agregarRepuestoAOrdenExistente,
    abrirModalEditarPago,
    cerrarModalEditarPago,
    seleccionarPagoParaEdicion,
    guardarEdicionPago
  }
}