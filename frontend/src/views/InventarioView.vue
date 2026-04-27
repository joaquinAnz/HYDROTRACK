<template>
  <div class="inventory-shell">

    <NavbarAdmin />
      <section class="hero">
          <img :src="heroImage" alt="Taller Hydrotrack" class="hero-image" />
          <div class="hero-copy">
            <h1>INVENTARIO DE REPUESTOS</h1>
            <p>ADMINISTRA TUS REPUESTOS, REVISA INVENTARIO Y GESTIONA DISPONIBILIDAD</p>
          </div>
        </section>

    <div class="inventory-frame">
      
      <main class="inventory-content">

        <section class="toolbar-row">
          <button class="tab-action active" @click="openCreateModal">+ AGREGAR REPUESTO</button>
          <button class="tab-action" @click="onlyActive = !onlyActive">
            {{ onlyActive ? 'REPUESTOS ACTIVOS' : 'VER INACTIVOS' }}
          </button>
        </section>

        <section class="table-card">
          <div class="table-title-row">
            <h2>INVENTARIO DE REPUESTOS</h2>
          </div>

          <div class="search-wrap">
            <input v-model="search" type="text" placeholder="Buscar codigo o descripcion..." />
            <span class="search-icon">⌕</span>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Codigo Repuesto</th>
                  <th>Descripcion</th>
                  <th>Marca</th>
                  <th>Stock Actual</th>
                  <th>Stock Minimo</th>
                  <th>Precio Venta</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in paginatedItems" :key="item.id_repuesto" :id="`row-${item.id_repuesto}`" :class="{ 'row-blink': item.id_repuesto === highlightedId }">
                  <td>
                    <div class="product-cell">
                      <span>{{ item.nombre }}</span>
                    </div>
                  </td>
                  <td class="description-cell">{{ item.descripcion || '-' }}</td>
                  <td>{{ item.marca || '-' }}</td>
                  <td>{{ item.stock_actual }}</td>
                  <td>{{ item.stock_minimo }}</td>
                  <td>Bs {{ item.precio_venta }}</td>
                  <td>
                    <span class="state-pill" :class="stockTone(item.stock_actual, item.stock_minimo)">
                      {{ stockLabel(item.stock_actual, item.stock_minimo) }}
                    </span>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <button class="mini-btn edit" title="Editar" @click="openEditModal(item)">✎</button>
                      <button class="mini-btn delete" title="Eliminar" @click="deleteRepuesto(item.id_repuesto)">🗑</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!paginatedItems.length">
                  <td colspan="8" class="empty">No se encontraron repuestos</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pager">
            <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">&lt;</button>
            <button
              v-for="page in totalPages"
              :key="page"
              class="page-btn"
              :class="{ active: page === currentPage }"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
            <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">&gt;</button>
          </div>
        </section>
      </main>

      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal">
          <h3>{{ modalMode === 'create' ? 'Agregar Repuesto' : 'Editar Repuesto' }}</h3>

          <label>Codigo de Repuesto</label>
          <input v-model="form.nombre" maxlength="100" />
          <p class="field-error" v-if="codigoError">{{ codigoError }}</p>

          <label>Marca</label>
          <input v-model="form.marca" maxlength="50" />

          <label>Descripcion del Repuesto</label>
          <textarea v-model="form.descripcion" rows="3"></textarea>

          <label>Stock Actual</label>
          <input v-model.number="form.stock_actual" type="number" min="0" />

          <label>Stock Minimo</label>
          <input v-model.number="form.stock_minimo" type="number" min="0" />

          <label>Precio Compra (Bs)</label>
          <input v-model.number="form.precio_compra" type="number" min="0" step="0.01" />

          <label>Precio Venta (Bs)</label>
          <input v-model.number="form.precio_venta" type="number" min="0" step="0.01" />

          <label class="inline-check">
            <input type="checkbox" v-model="form.estado" />
            Activo
          </label>

          <p class="field-error" v-if="modalError">{{ modalError }}</p>

          <div class="actions-row">
            <button class="ghost" @click="closeModal">Cancelar</button>
            <button @click="saveRepuesto" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</button>
          </div>
        </div>
      </div>
    </div>    
  </div>
    <FooterInventario :logo="logo" />
</template>

<script setup>
import NavbarAdmin from '../components/NavbarAdmin.vue'
import FooterInventario from '../components/Footer.vue'
import { useInventario } from '../composables/useInventario'
import heroImage from '../assets/Header_Inv.png'

const {
  logo,
  search,
  currentPage,
  items,
  onlyActive,
  loading,
  showModal,
  modalMode,
  modalError,
  codigoError,
  editingId,
  form,
  filteredItems,
  totalPages,
  paginatedItems,
  stockLabel,
  stockTone,
  openCreateModal,
  openEditModal,
  closeModal,
  saveRepuesto,
  deleteRepuesto,
} = useInventario()
</script>

<style scoped src="../assets/inventario.css"></style>
