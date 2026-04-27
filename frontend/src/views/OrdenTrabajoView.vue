<template>
  <div class="orden-page">
    <header class="hero">
      <NavbarAdmin />
      <div class="hero-overlay">
        <h1>ORDEN DE TRABAJO</h1>
      </div>
    </header>

    <main class="main-content">
      <section class="section-title">
        <h2>CREAR ORDEN DE TRABAJO</h2>
      </section>

      <section class="form-card">
        <div class="card-header">
          <h3>Orden de Trabajo</h3>
        </div>

        <form @submit.prevent="guardarOrden">
          <div class="grid-form">
            <div class="form-group full-width">
              <label>Tipo de Servicio *</label>
              <select v-model="opcionTipoServicio" :disabled="tipoBloqueado" @change="sincronizarTipoServicio">
                <option value="">Seleccionar tipo de servicio</option>
                <option value="servicio_tecnico">Servicio tecnico</option>
                <option value="solo_venta">Servicio solo venta</option>
              </select>
              <small v-if="tipoBloqueado" class="tipo-bloqueado-msg">
                Ya empezaste a llenar esta orden. Para cambiar el tipo, primero presiona Cancelar.
              </small>
            </div>
          </div>

          <div class="grid-form">
            <div class="form-group">
              <label>{{ esSoloVenta ? 'Cliente (opcional)' : 'Cliente *' }}</label>
              <select v-model="form.id_cliente">
                <option value="">Seleccionar cliente</option>
                <option
                  v-for="cliente in clientesDisponibles"
                  :key="cliente.id_cliente"
                  :value="cliente.id_cliente"
                >
                  {{ cliente.nombres || cliente.nombre }} {{ cliente.apellidos || cliente.apellido }}
                </option>
              </select>
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Vehículo *</label>
              <select v-model="form.id_vehiculo">
                <option value="">Seleccionar vehículo</option>
                <option
                  v-for="vehiculo in vehiculosFiltrados"
                  :key="vehiculo.id_vehiculo"
                  :value="vehiculo.id_vehiculo"
                >
                  {{ vehiculo.placa }} - {{ vehiculo.descripcion || `${vehiculo.marca || ''} ${vehiculo.modelo || ''}`.trim() }}
                </option>
              </select>
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Técnico Asignado *</label>
              <select v-model="form.id_tecnico" :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta">
                <option value="">Seleccionar técnico</option>
                <option
                  v-for="tecnico in tecnicos"
                  :key="tecnico.id_usuario"
                  :value="tecnico.id_usuario"
                >
                  {{ tecnico.nombres || tecnico.nombre }} {{ tecnico.apellidos || '' }}
                </option>
              </select>
            </div>

            <div class="form-group full-width" v-if="!esSoloVenta">
              <label>Descripción del Problema</label>
              <textarea
                v-model="form.descripcion_falla"
                :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta"
                rows="4"
                placeholder="Describe el problema o la solicitud..."
              ></textarea>
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Fecha de Ingreso</label>
              <input v-model="form.fecha_ingreso" :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta" type="date" />
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Fecha Estimada de Entrega</label>
              <input v-model="form.fecha_estimada_entrega" :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta" type="date" />
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Diagnóstico</label>
              <input
                v-model="form.diagnostico"
                :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta"
                type="text"
                placeholder="Diagnóstico inicial"
              />
            </div>

            <div class="form-group" v-if="!esSoloVenta">
              <label>Estado *</label>
              <select v-model="form.id_estado" :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta">
                <option value="">Seleccionar estado</option>
                <option
                  v-for="estado in estados"
                  :key="estado.id_estado"
                  :value="estado.id_estado"
                >
                  {{ estado.nombre }}
                </option>
              </select>
            </div>

            <div class="form-group full-width">
              <label>Observaciones</label>
              <textarea
                v-model="form.observaciones"
                :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta"
                rows="3"
                placeholder="Observaciones adicionales..."
              ></textarea>
            </div>

            <div class="form-group total-box">
              <label>Total Estimado</label>
              <div class="total">{{ moneda(totalEstimado) }}</div>
            </div>
          </div>

          <div class="servicios-actions">
            <button
              v-if="!esSoloVenta"
              type="button"
              class="btn btn-add-service"
              @click="abrirPanelServicios"
              :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta"
            >
              + Agregar Servicio
            </button>
            <button
              type="button"
              class="btn btn-add-service"
              @click="abrirPanelRepuestos"
              :disabled="!puedeCompletarFormularioTecnico && !esSoloVenta"
            >
              + Agregar Repuesto
            </button>
          </div>

          <div v-if="mostrarPanelServicios" class="panel-servicios">
            <div class="panel-servicios-header">
              <h4>Agregar servicio manual</h4>
              <button
                type="button"
                class="btn-close"
                @click="cerrarPanelServicios"
              >
                ✕
              </button>
            </div>

            <div class="grid-form">
              <div class="form-group full-width">
                <label>Descripcion del Servicio</label>
                <textarea
                  v-model="nuevoServicioManual.descripcion"
                  rows="3"
                  placeholder="Ej: Cambio de reten y ajuste del sistema"
                ></textarea>
              </div>

              <div class="form-group">
                <label>Monto del Servicio</label>
                <input
                  v-model.number="nuevoServicioManual.precio_unitario"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                />
              </div>

              <div class="form-group" style="justify-content: end;">
                <button
                  type="button"
                  class="btn btn-select-service"
                  @click="agregarServicioAdicional"
                >
                  Agregar Servicio
                </button>
              </div>
            </div>
          </div>

          <div v-if="mostrarPanelRepuestos" class="panel-servicios">
            <div class="panel-servicios-header">
              <h4>Seleccionar repuesto</h4>
              <button
                type="button"
                class="btn-close"
                @click="cerrarPanelRepuestos"
              >
                ✕
              </button>
            </div>

            <div class="lista-servicios">
              <div
                v-for="repuesto in repuestos"
                :key="repuesto.id_repuesto"
                class="servicio-item"
              >
                <div>
                  <strong>{{ repuesto.nombre }}</strong>
                  <p>{{ repuesto.descripcion || 'Sin descripción' }}</p>
                  <span>Stock: {{ repuesto.stock_actual }} | {{ moneda(repuesto.precio_venta) }}</span>
                </div>

                <button
                  type="button"
                  class="btn btn-select-service"
                  @click="agregarRepuesto(repuesto)"
                >
                  Agregar
                </button>
              </div>
            </div>
          </div>

          <div
            class="detalle-servicios-box"
            v-if="!esSoloVenta && detalleServicios && detalleServicios.length > 0"
          >
            <h4>Detalle de Servicios Agregados</h4>

            <table class="tabla-detalle">
              <thead>
                <tr>
                  <th>Servicio</th>
                  <th>Cantidad</th>
                  <th>Precio Unitario</th>
                  <th>Subtotal</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in detalleServicios" :key="index">
                  <td>{{ item.nombre }}</td>
                  <td>{{ item.cantidad }}</td>
                  <td>{{ moneda(item.precio_unitario) }}</td>
                  <td>{{ moneda(item.cantidad * item.precio_unitario) }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn-delete"
                      @click="eliminarServicioDetalle(index)"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            class="detalle-servicios-box"
            v-if="detalleRepuestos && detalleRepuestos.length > 0"
          >
            <h4>Detalle de Repuestos Agregados</h4>

            <table class="tabla-detalle">
              <thead>
                <tr>
                  <th>Repuesto</th>
                  <th>Cantidad</th>
                  <th>Precio Unitario</th>
                  <th>Subtotal</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in detalleRepuestos" :key="`rep-${index}`">
                  <td>{{ item.nombre }}</td>
                  <td>{{ item.cantidad }}</td>
                  <td>{{ moneda(item.precio_unitario) }}</td>
                  <td>{{ moneda(item.cantidad * item.precio_unitario) }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn-delete"
                      @click="eliminarRepuestoDetalle(index)"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="actions">
            <button
              type="button"
              class="btn btn-cancel"
              @click="limpiarFormulario"
            >
              Cancelar
            </button>
            <button type="submit" class="btn btn-save">
              Guardar Orden
            </button>
          </div>
        </form>

        <p v-if="mensaje" class="mensaje exito">{{ mensaje }}</p>
        <p v-if="error" class="mensaje error">{{ error }}</p>
      </section>
    </main>
  </div>
  <Footer :logo="logo" />
</template>

<script setup>
import NavbarAdmin from '../components/NavbarAdmin.vue'
import { useOrdenTrabajo } from '../composables/useOrdenTrabajo'
import Footer from '../components/Footer.vue'
import '../assets/ordenTrabajo.css'

const {
  form,
  opcionTipoServicio,
  esSoloVenta,
  puedeCompletarFormularioTecnico,
  tipoBloqueado,
  tiposServicioDisponibles,
  clientesDisponibles,
  vehiculosFiltrados,
  tecnicos,
  estados,
  tiposServicio,
  repuestos,
  detalleServicios,
  detalleRepuestos,
  nuevoServicioManual,
  mostrarPanelServicios,
  mostrarPanelRepuestos,
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
  sincronizarTipoServicio
} = useOrdenTrabajo()
</script>