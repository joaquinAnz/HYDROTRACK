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
          <button
            type="button"
            class="tab-btn"
            :class="{ active: tabActual === 'todas' }"
            @click="tabActual = 'todas'"
          >
            Todas las Ordenes ({{ totalTodas }})
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
                  {{ tabActual === 'pendientes' ? 'No hay ordenes pendientes' : tabActual === 'completadas' ? 'No hay ordenes completadas' : 'No hay ordenes registradas' }}
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
  <Footer :logo="logo" />
</template>

<script setup>
import NavbarAdmin from '../components/NavbarAdmin.vue'
import Footer from '../components/Footer.vue'
import { useDashboardOrdenes } from '../composables/useDashboardOrdenes'

const {
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
} = useDashboardOrdenes()
</script>

<style scoped src="../assets/dashboard-ordenes.css"></style>
