<template>
  <div class="dashboard-orders-page">
    <NavbarAdmin />
    <div class="navbar-spacer"></div>

    <main class="orders-wrapper">
      <section class="orders-card">
        <header class="orders-header">
          <div>
            <h1>Lista de Ordenes</h1>
            <p>Listado por defecto de mas reciente a mas antigua</p>
          </div>
          <button type="button" class="btn-back" @click="goDashboard">Volver al Dashboard</button>
        </header>

        <div class="filters-row">
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por orden, cliente, carnet, placa o tipo de servicio"
          />
        </div>

        <div class="tabs-row">
          <button
            type="button"
            class="tab-btn"
            :class="{ active: tabActual === 'pendientes' }"
            @click="tabActual = 'pendientes'"
          >
            Ordenes Pendientes ({{ totalPendientes }})
          </button>
          <button
            type="button"
            class="tab-btn"
            :class="{ active: tabActual === 'completadas' }"
            @click="tabActual = 'completadas'"
          >
            Ordenes Completadas ({{ totalCompletadas }})
          </button>
        </div>

        <div class="table-wrap">
          <table class="orders-table">
            <thead>
              <tr>
                <th>Orden</th>
                <th>Cliente</th>
                <th>Carnet</th>
                <th>Placa</th>
                <th>Tipo Servicio</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cargando">
                <td colspan="9" class="center">Cargando ordenes...</td>
              </tr>
              <tr v-else-if="!ordenesFiltradas.length">
                <td colspan="9" class="center">
                  {{ tabActual === 'pendientes' ? 'No hay ordenes pendientes' : 'No hay ordenes completadas' }}
                </td>
              </tr>
              <tr v-for="orden in ordenesFiltradas" :key="orden.id_orden">
                <td>{{ orden.codigo_seguimiento || orden.id_orden }}</td>
                <td>{{ clienteNombre(orden) }}</td>
                <td>{{ clienteCarnet(orden) }}</td>
                <td>{{ placaVisible(orden) }}</td>
                <td>{{ tipoServicioNombre(orden) }}</td>
                <td>{{ orden?.estado?.nombre || '-' }}</td>
                <td>{{ moneda(orden?.total_orden) }}</td>
                <td>
                  <span class="pago-badge" :class="claseEstadoPago(orden)">
                    {{ textoEstadoPago(orden) }}
                  </span>
                </td>
                <td>
                  <div class="acciones-orden">
                    <button type="button" class="btn-mini btn-primary" @click="abrirModalPago(orden)">Pago</button>
                    <button v-if="!esOrdenVenta(orden)" type="button" class="btn-mini" @click="abrirModalDetalleOrden(orden)">Agregar</button>
                    <button v-if="!esOrdenVenta(orden)" type="button" class="btn-mini" @click="abrirModalActualizacion(orden)">Actualizar</button>
                    <button type="button" class="btn-mini" @click="abrirModalVerOrden(orden)">Ver</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-if="error" class="error-msg">{{ error }}</p>
      </section>
    </main>

    <div v-if="mostrarModalPago" class="modal-backdrop" @click.self="cerrarModalPago">
      <div class="modal">
        <h2>Registrar Pago</h2>

        <label>ID Orden</label>
        <input :value="pagoForm.codigo_seguimiento" disabled />

        <div class="grid-3">
          <div>
            <label>Total Orden</label>
            <input :value="moneda(pagoForm.total_orden)" disabled />
          </div>
          <div>
            <label>Total Pagado</label>
            <input :value="moneda(pagoForm.total_pagado)" disabled />
          </div>
          <div>
            <label>Saldo Pendiente</label>
            <input :value="moneda(pagoForm.saldo_pendiente)" disabled />
          </div>
        </div>

        <div class="metodos-box">
          <div class="metodos-head">
            <label>Metodos de Pago</label>
            <button type="button" class="btn-mini" @click="agregarMetodoPago">+ Metodo</button>
          </div>

          <div class="metodo-row" v-for="(metodo, index) in pagoForm.metodos" :key="`met-${index}`">
            <select v-model="metodo.metodo">
              <option value="qr">QR</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="efectivo">Efectivo</option>
            </select>
            <input v-model.number="metodo.monto" type="number" min="0" step="0.01" />
            <button type="button" class="btn-mini" @click="eliminarMetodoPago(index)">Quitar</button>
          </div>
        </div>

        <label>Observacion</label>
        <textarea v-model="pagoForm.observacion" rows="2"></textarea>

        <div class="actions-row">
          <span class="total-info">Total: {{ moneda(totalMetodosPago) }}</span>
          <button type="button" class="btn-mini" @click="cerrarModalPago">Cancelar</button>
          <button type="button" class="btn-mini btn-primary" @click="registrarPago">Guardar Pago</button>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalActualizacion" class="modal-backdrop" @click.self="cerrarModalActualizacion">
      <div class="modal">
        <h2>Actualizar Proceso</h2>
        <input :value="actualizacionForm.codigo_seguimiento" disabled />
        <label>Estado de la orden</label>
        <select v-model="actualizacionForm.estado_objetivo">
          <option value="en_proceso">En proceso</option>
          <option value="completado">Completado</option>
        </select>
        <label>Detalle de actualizacion</label>
        <textarea v-model="actualizacionForm.descripcion" rows="3" placeholder="Describe el avance..."></textarea>
        <div class="actions-row">
          <button type="button" class="btn-mini" @click="cerrarModalActualizacion">Cerrar</button>
          <button type="button" class="btn-mini btn-primary" @click="registrarActualizacion">Guardar Actualizacion</button>
        </div>

        <div class="box" v-if="actualizacionesOrden.length">
          <h4>Historial</h4>
          <table class="orders-table small">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripcion</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in actualizacionesOrden" :key="item.id_actualizacion">
                <td>{{ item.fecha || '-' }}</td>
                <td>{{ item.tipo || '-' }}</td>
                <td>{{ item.descripcion }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalDetalleOrden" class="modal-backdrop" @click.self="cerrarModalDetalleOrden">
      <div class="modal">
        <h2>Agregar Servicio/Repuesto</h2>
        <input :value="detalleOrdenForm.codigo_seguimiento" disabled />

        <div class="box">
          <h4>Servicio</h4>
          <textarea v-model="detalleOrdenForm.descripcion_servicio" rows="2" placeholder="Descripcion del servicio"></textarea>
          <input v-model.number="detalleOrdenForm.monto_servicio" type="number" min="0" step="0.01" placeholder="Monto" />
          <div class="actions-row">
            <button type="button" class="btn-mini btn-primary" @click="agregarServicioAOrdenExistente">Agregar Servicio</button>
          </div>
        </div>

        <div class="box">
          <h4>Repuesto</h4>
          <select v-model="detalleOrdenForm.id_repuesto" @change="alCambiarRepuestoDetalle">
            <option value="">Seleccionar repuesto</option>
            <option v-for="repuesto in repuestos" :key="repuesto.id_repuesto" :value="repuesto.id_repuesto">
              {{ repuesto.nombre }} (Stock {{ repuesto.stock_actual }})
            </option>
          </select>
          <input v-model.number="detalleOrdenForm.cantidad_repuesto" type="number" min="1" step="1" placeholder="Cantidad" />
          <input v-model.number="detalleOrdenForm.precio_repuesto" type="number" min="0" step="0.01" placeholder="Precio unitario" />
          <div class="actions-row">
            <button type="button" class="btn-mini btn-primary" @click="agregarRepuestoAOrdenExistente">Agregar Repuesto</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalVerOrden" class="modal-backdrop" @click.self="cerrarModalVerOrden">
      <div class="modal">
        <h2>Detalle de Orden</h2>

        <div v-if="cargandoDetalleOrden" class="box">
          <p>Cargando detalle...</p>
        </div>

        <template v-else-if="ordenDetalleVista">
          <div class="box">
            <h4>Informacion General</h4>
            <div class="grid-3">
              <div>
                <label>Orden</label>
                <input :value="ordenDetalleVista.codigo_seguimiento || ordenDetalleVista.id_orden" disabled />
              </div>
              <div>
                <label>Estado</label>
                <input :value="ordenDetalleVista?.estado?.nombre || '-'" disabled />
              </div>
              <div>
                <label>Tipo Servicio</label>
                <input :value="tipoServicioNombre(ordenDetalleVista)" disabled />
              </div>
              <div>
                <label>Cliente</label>
                <input :value="clienteNombre(ordenDetalleVista)" disabled />
              </div>
              <div>
                <label>Carnet</label>
                <input :value="clienteCarnet(ordenDetalleVista)" disabled />
              </div>
              <div>
                <label>Placa</label>
                <input :value="placaVisible(ordenDetalleVista)" disabled />
              </div>
            </div>

            <label>Descripcion Falla</label>
            <textarea :value="ordenDetalleVista.descripcion_falla || '-'" rows="2" disabled></textarea>

            <label>Diagnostico</label>
            <textarea :value="ordenDetalleVista.diagnostico || '-'" rows="2" disabled></textarea>

            <label>Observaciones</label>
            <textarea :value="ordenDetalleVista.observaciones || '-'" rows="2" disabled></textarea>

            <div class="grid-3">
              <div>
                <label>Total Orden</label>
                <input :value="moneda(ordenDetalleVista.total_orden)" disabled />
              </div>
              <div>
                <label>Total Pagado</label>
                <input :value="moneda(ordenDetalleVista.total_pagado)" disabled />
              </div>
              <div>
                <label>Estado Pago</label>
                <input :value="textoEstadoPago(ordenDetalleVista)" disabled />
              </div>
            </div>
          </div>

          <div class="box" v-if="(ordenDetalleVista.detallesServicio || []).length">
            <h4>Servicios</h4>
            <table class="orders-table small">
              <thead>
                <tr>
                  <th>Servicio</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in ordenDetalleVista.detallesServicio" :key="item.id_detalle_servicio || `${item.id_orden}-${item.id_servicio}`">
                  <td>{{ item?.servicio?.nombre || item?.servicio?.descripcion || '-' }}</td>
                  <td>{{ item.cantidad || 0 }}</td>
                  <td>{{ moneda(item.precio_unitario || item.precio || 0) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="box" v-if="(ordenDetalleVista.detallesRepuestos || []).length">
            <h4>Repuestos</h4>
            <table class="orders-table small">
              <thead>
                <tr>
                  <th>Repuesto</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in ordenDetalleVista.detallesRepuestos" :key="item.id_detalle_repuesto || `${item.id_orden}-${item.id_repuesto}`">
                  <td>{{ item?.repuesto?.nombre || '-' }}</td>
                  <td>{{ item.cantidad || 0 }}</td>
                  <td>{{ moneda(item.precio_unitario || item.precio || 0) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="box">
            <h4>Pagos Realizados</h4>
            <table class="orders-table small" v-if="(ordenDetalleVista.pagos || []).length">
              <thead>
                <tr>
                  <th>ID Pago</th>
                  <th>Monto</th>
                  <th>Metodos</th>
                  <th>Observacion</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pago in ordenDetalleVista.pagos" :key="pago.id_pago_orden">
                  <td>{{ pago.id_pago_orden }}</td>
                  <td>{{ moneda(pago.monto_total) }}</td>
                  <td>{{ metodosPagoTexto(pago) }}</td>
                  <td>{{ pago.observacion || '-' }}</td>
                </tr>
              </tbody>
            </table>
            <p v-else>No hay pagos registrados.</p>
          </div>

          <div class="box">
            <h4>Actualizaciones</h4>
            <table class="orders-table small" v-if="(ordenDetalleVista.actualizaciones || []).length">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Descripcion</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in ordenDetalleVista.actualizaciones" :key="item.id_actualizacion">
                  <td>{{ item.fecha || '-' }}</td>
                  <td>{{ item.tipo || '-' }}</td>
                  <td>{{ item.descripcion || '-' }}</td>
                </tr>
              </tbody>
            </table>
            <p v-else>No hay actualizaciones registradas.</p>
          </div>
        </template>

        <div class="actions-row">
          <button type="button" class="btn-mini" @click="cerrarModalVerOrden">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import NavbarAdmin from '../components/NavbarAdmin.vue'

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

const esOrdenVenta = (orden) => {
  const tipoServicio = normalizar(tipoServicioNombre(orden))
  return esVehiculoVirtualVenta(orden) || tipoServicio.includes('venta')
}

const esVehiculoVirtualVenta = (orden) => {
  const placa = String(orden?.vehiculo?.placa || '').toUpperCase()
  return placa.startsWith('MST-')
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
    const subtotalMetodos = metodos.reduce((sum, metodo) => sum + Number(metodo?.monto || 0), 0)
    return acc + subtotalMetodos
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

const ordenesFiltradas = computed(() => {
  const term = normalizar(busqueda.value)

  const listaBase = [...ordenes.value]
    .filter((orden) => {
      if (tabActual.value === 'pendientes') return !esOrdenCompleta(orden)
      return esOrdenCompleta(orden)
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
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Manrope:wght@400;600;700&display=swap');

.dashboard-orders-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #eef2fb 0%, #f8fafc 100%);
  font-family: 'Manrope', sans-serif;
}

.navbar-spacer {
  height: 70px;
}

.orders-wrapper {
  max-width: 100%;
  margin: 24px auto;
  padding: 0 8px 34px;
}

.orders-card {
  background: #fff;
  border-radius: 18px;
  border: 1px solid #d9e1ef;
  box-shadow: 0 14px 32px rgba(30, 53, 108, 0.1);
  padding: 14px;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.orders-header h1 {
  margin: 0;
  font-family: 'Cinzel', serif;
  color: #102c6d;
  font-size: 30px;
}

.orders-header p {
  margin: 5px 0 0;
  color: #4f6288;
  font-size: 14px;
}

.btn-back {
  border: 1px solid #cfd9eb;
  border-radius: 12px;
  background: #f6f9ff;
  color: #17357c;
  padding: 10px 14px;
  font-weight: 700;
  cursor: pointer;
}

.filters-row {
  margin-bottom: 14px;
}

.filters-row input {
  width: 100%;
  border: 1px solid #cfdae9;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
}

.tabs-row {
  display: flex;
  gap: 10px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.tab-btn {
  border: 1px solid #c9d7ee;
  border-radius: 12px;
  background: #f4f8ff;
  color: #1b3a79;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.tab-btn.active {
  background: #1a3b7f;
  border-color: #1a3b7f;
  color: #fff;
}

.table-wrap {
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.orders-table th,
.orders-table td {
  border: 1px solid #d9e1ef;
  padding: 7px 6px;
  font-size: 11px;
  vertical-align: middle;
  overflow-wrap: anywhere;
}

.orders-table th {
  background: #ecf2ff;
  color: #193873;
  font-weight: 700;
}

.orders-table th:nth-child(1),
.orders-table td:nth-child(1) {
  width: 16%;
}

.orders-table th:nth-child(2),
.orders-table td:nth-child(2) {
  width: 15%;
}

.orders-table th:nth-child(3),
.orders-table td:nth-child(3) {
  width: 8%;
}

.orders-table th:nth-child(4),
.orders-table td:nth-child(4) {
  width: 7%;
}

.orders-table th:nth-child(5),
.orders-table td:nth-child(5) {
  width: 14%;
}

.orders-table th:nth-child(6),
.orders-table td:nth-child(6) {
  width: 9%;
}

.orders-table th:nth-child(7),
.orders-table td:nth-child(7) {
  width: 8%;
}

.orders-table th:nth-child(8),
.orders-table td:nth-child(8) {
  width: 8%;
}

.orders-table th:nth-child(9),
.orders-table td:nth-child(9) {
  width: 15%;
}

.orders-table tbody tr:nth-child(odd) {
  background: #fbfdff;
}

.acciones-orden {
  display: flex;
  flex-wrap: nowrap;
  gap: 4px;
  align-items: center;
  justify-content: center;
}

.btn-mini {
  border: 1px solid #c9d7ee;
  border-radius: 8px;
  background: #f4f8ff;
  color: #1b3a79;
  padding: 4px 6px;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}

.btn-mini.btn-primary {
  background: #1ea56f;
  border-color: #1ea56f;
  color: #fff;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: grid;
  place-items: center;
  padding: 12px;
  z-index: 50;
}

.modal {
  width: min(900px, 100%);
  max-height: 92vh;
  overflow-y: auto;
  background: #fff;
  border-radius: 14px;
  padding: 18px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22);
}

.modal h2 {
  margin: 0 0 10px;
  color: #102c6d;
}

.modal label {
  display: block;
  margin: 10px 0 6px;
  font-size: 13px;
  font-weight: 700;
  color: #193873;
}

.modal input,
.modal select,
.modal textarea {
  width: 100%;
  border: 1px solid #cfdae9;
  border-radius: 10px;
  padding: 9px 10px;
  font-size: 13px;
  box-sizing: border-box;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.metodos-box,
.box {
  margin-top: 12px;
  border: 1px solid #d9e1ef;
  border-radius: 12px;
  padding: 10px;
  background: #fbfdff;
}

.box h4 {
  margin: 0 0 8px;
  color: #193873;
}

.metodos-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.metodo-row {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 8px;
  margin-bottom: 8px;
}

.actions-row {
  margin-top: 10px;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  align-items: center;
}

.total-info {
  margin-right: auto;
  font-weight: 700;
  color: #17357c;
}

.orders-table.small {
  min-width: 100%;
}

.pago-badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.badge-pendiente {
  background: #ffe7e7;
  color: #b3261e;
}

.badge-incompleto {
  background: #fff0dd;
  color: #b05f06;
}

.badge-completo {
  background: #e3f7eb;
  color: #1d8b49;
}

.center {
  text-align: center;
}

.error-msg {
  margin-top: 12px;
  color: #ba3a3a;
  font-weight: 600;
}

@media (max-width: 900px) {
  .orders-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .orders-header h1 {
    font-size: 24px;
  }

  .acciones-orden {
    flex-wrap: wrap;
  }

  .grid-3 {
    grid-template-columns: 1fr;
  }

  .metodo-row {
    grid-template-columns: 1fr;
  }
}
</style>
