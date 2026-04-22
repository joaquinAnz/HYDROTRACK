<template>
  <div class="orden-page">
    <header class="hero">
      <Navbar />
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
            <div class="form-group">
              <label>Cliente *</label>
              <select v-model="form.id_cliente">
                <option value="">Seleccionar cliente</option>
                <option
                  v-for="cliente in clientes"
                  :key="cliente.id_cliente"
                  :value="cliente.id_cliente"
                >
                  {{ cliente.nombre }} {{ cliente.apellido }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Vehículo *</label>
              <select v-model="form.id_vehiculo">
                <option value="">Seleccionar vehículo</option>
                <option
                  v-for="vehiculo in vehiculosFiltrados"
                  :key="vehiculo.id_vehiculo"
                  :value="vehiculo.id_vehiculo"
                >
                  {{ vehiculo.placa }} - {{ vehiculo.marca }} {{ vehiculo.modelo }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Tipo de Servicio *</label>
              <select v-model="form.id_tipo_servicio">
                <option value="">Seleccionar tipo de servicio</option>
                <option
                  v-for="tipo in tiposServicio"
                  :key="tipo.id_tipo_servicio"
                  :value="tipo.id_tipo_servicio"
                >
                  {{ tipo.nombre }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Técnico Asignado *</label>
              <select v-model="form.id_tecnico">
                <option value="">Seleccionar técnico</option>
                <option
                  v-for="tecnico in tecnicos"
                  :key="tecnico.id_usuario"
                  :value="tecnico.id_usuario"
                >
                  {{ tecnico.nombre }}
                </option>
              </select>
            </div>

            <div class="form-group full-width">
              <label>Descripción del Problema</label>
              <textarea
                v-model="form.descripcion_falla"
                rows="4"
                placeholder="Describe el problema o la solicitud..."
              ></textarea>
            </div>

            <div class="form-group">
              <label>Fecha de Ingreso</label>
              <input v-model="form.fecha_ingreso" type="date" />
            </div>

            <div class="form-group">
              <label>Fecha Estimada de Entrega</label>
              <input v-model="form.fecha_estimada_entrega" type="date" />
            </div>

            <div class="form-group">
              <label>Diagnóstico</label>
              <input
                v-model="form.diagnostico"
                type="text"
                placeholder="Diagnóstico inicial"
              />
            </div>

            <div class="form-group">
              <label>Estado *</label>
              <select v-model="form.id_estado">
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
                rows="3"
                placeholder="Observaciones adicionales..."
              ></textarea>
            </div>

            <div class="form-group">
              <label>Costo Mano de Obra</label>
              <input
                v-model="form.costo_mano_obra"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
              />
            </div>

            <div class="form-group total-box">
              <label>Total Estimado</label>
              <div class="total">{{ moneda(totalEstimado) }}</div>
            </div>
          </div>

          <div class="servicios-actions">
            <button
              type="button"
              class="btn btn-add-service"
              @click="abrirPanelServicios"
            >
              + Agregar Servicio
            </button>
          </div>

          <div v-if="mostrarPanelServicios" class="panel-servicios">
            <div class="panel-servicios-header">
              <h4>Seleccionar servicio adicional</h4>
              <button
                type="button"
                class="btn-close"
                @click="cerrarPanelServicios"
              >
                ✕
              </button>
            </div>

            <div class="lista-servicios">
              <div
                v-for="servicio in serviciosAdicionales"
                :key="servicio.id_servicio"
                class="servicio-item"
              >
                <div>
                  <strong>{{ servicio.nombre }}</strong>
                  <p>{{ servicio.descripcion || 'Sin descripción' }}</p>
                  <span>{{ moneda(servicio.costo_base) }}</span>
                </div>

                <button
                  type="button"
                  class="btn btn-select-service"
                  @click="agregarServicioAdicional(servicio)"
                >
                  Agregar
                </button>
              </div>
            </div>
          </div>

          <div
            class="detalle-servicios-box"
            v-if="detalleServicios && detalleServicios.length > 0"
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
</template>

<script setup>
import Navbar from '../components/Navbar.vue'
import { useOrdenTrabajo } from '../composables/useOrdenTrabajo'
import '../assets/ordenTrabajo.css'

const {
  form,
  clientes,
  vehiculosFiltrados,
  tecnicos,
  estados,
  tiposServicio,
  serviciosAdicionales,
  detalleServicios,
  mostrarPanelServicios,
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
} = useOrdenTrabajo()
</script>