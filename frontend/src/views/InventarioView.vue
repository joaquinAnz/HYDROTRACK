<template>
  <div class="inventory-shell">
    <div class="inventory-frame">
      <header class="topbar">
        <div class="menu-toggle" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <div class="brand">
          <img :src="logo" alt="Hydrotrack" class="brand-logo" />
          <span class="brand-name">HYDROTRACK</span>
        </div>

        <nav class="top-links">
          <router-link to="/dashboard">DASHBOARD</router-link>
          <a href="#">MIS SERVICIOS</a>
          <a href="#">CLIENTES</a>
          <router-link to="/inventario">INVENTARIOS</router-link>
          <a href="#">PAGOS</a>
          <router-link to="/usuarios">USUARIOS</router-link>
          <a href="#">REPORTES</a>
        </nav>

        <div class="top-icons">
          <span>o</span>
          <span>o</span>
        </div>
      </header>

      <main class="inventory-content">
        <section class="hero-grid">
          <article class="hero-left">
            <img :src="heroImage" alt="Taller Hydrotrack" />
          </article>
          <article class="hero-top-right">
            <img :src="headerImage" alt="Repuestos" />
          </article>
          <article class="hero-bottom-right">
            <img :src="heroImage" alt="Inventario" />
          </article>

          <div class="hero-overlay"></div>
          <div class="hero-copy">
            <h1>INVENTARIO DE REPUESTOS</h1>
            <p>ADMINISTRA TUS REPUESTOS, REVISA INVENTARIO Y GESTIONA DISPONIBILIDAD</p>
          </div>
        </section>

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
                      <span class="thumb" :class="item.thumbClass"></span>
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

      <footer class="footer">
        <div class="footer-brand">
          <img :src="logo" alt="Hydrotrack" />
          <h3>HYDROTRACK</h3>
          <p>La plataforma ideal para la gestion de tu taller automotriz.</p>
        </div>

        <div>
          <h4>Nuestra Compania</h4>
          <a href="#">Nosotros</a>
          <a href="#">Blog</a>
          <a href="#">Nuestros Clientes</a>
        </div>

        <div>
          <h4>Ayuda</h4>
          <a href="#">Preguntas Frecuentes</a>
          <a href="#">Apoyo</a>
          <a href="#">Contactanos</a>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const highlightedId = ref(null)
import headerImage from '../assets/Header.png'
import heroImage from '../assets/hero.png'
import logo from '../assets/logo.png'

const search = ref('')
const currentPage = ref(1)
const itemsPerPage = 7

const items = ref([])
const onlyActive = ref(true)
const loading = ref(false)
const showModal = ref(false)
const modalMode = ref('create')
const modalError = ref('')
const editingId = ref(null)

const form = ref({
  nombre: '',
  marca: '',
  descripcion: '',
  stock_actual: 0,
  stock_minimo: 0,
  precio_compra: 0,
  precio_venta: 0,
  estado: true,
})

const filteredItems = computed(() => {
  const term = search.value.trim().toLowerCase()
  if (!term) return items.value
  return items.value.filter((item) => {
    return (
      item.nombre.toLowerCase().includes(term) ||
      String(item.descripcion || '').toLowerCase().includes(term)
    )
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredItems.value.length / itemsPerPage)))

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredItems.value.slice(start, start + itemsPerPage)
})

watch(search, () => {
  currentPage.value = 1
})

watch(totalPages, (value) => {
  if (currentPage.value > value) currentPage.value = value
})

const stockLabel = (stockActual, stockMinimo) => {
  if (stockActual <= 0) return 'Agotado'
  if (stockActual <= stockMinimo) return 'Bajo stock'
  return 'Disponible'
}

const stockTone = (stockActual, stockMinimo) => {
  if (stockActual <= 0) return 'danger'
  if (stockActual <= stockMinimo) return 'warning'
  return 'ok'
}

const thumbClassFor = (index) => {
  const classes = ['t1', 't2', 't3', 't4', 't5', 't6', 't7', 't8']
  return classes[index % classes.length]
}

const loadRepuestos = async () => {
  const { data } = await api.get('/repuestos', {
    params: { activos: onlyActive.value ? 1 : 0 },
  })
  items.value = data.map((item, index) => ({
    ...item,
    thumbClass: thumbClassFor(index),
  }))
}

const resetForm = () => {
  form.value = {
    nombre: '',
    marca: '',
    descripcion: '',
    stock_actual: 0,
    stock_minimo: 0,
    precio_compra: 0,
    precio_venta: 0,
    estado: true,
  }
  modalError.value = ''
  editingId.value = null
}

const openCreateModal = () => {
  modalMode.value = 'create'
  resetForm()
  showModal.value = true
}

const openEditModal = (item) => {
  modalMode.value = 'edit'
  form.value = {
    nombre: item.nombre,
    marca: item.marca || '',
    descripcion: item.descripcion || '',
    stock_actual: item.stock_actual,
    stock_minimo: item.stock_minimo,
    precio_compra: item.precio_compra,
    precio_venta: item.precio_venta,
    estado: item.estado,
  }
  modalError.value = ''
  editingId.value = item.id_repuesto
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const saveRepuesto = async () => {
  if (!form.value.nombre) {
    modalError.value = 'El codigo de repuesto es obligatorio'
    return
  }

  loading.value = true
  modalError.value = ''

  try {
    const payload = {
      nombre: form.value.nombre,
      marca: form.value.marca || null,
      descripcion: form.value.descripcion || null,
      stock_actual: Number(form.value.stock_actual),
      stock_minimo: Number(form.value.stock_minimo),
      precio_compra: Number(form.value.precio_compra),
      precio_venta: Number(form.value.precio_venta),
      estado: form.value.estado,
    }

    if (modalMode.value === 'create') {
      await api.post('/repuestos', payload)
    } else {
      await api.put(`/repuestos/${editingId.value}`, payload)
    }

    await loadRepuestos()
    closeModal()
  } catch (error) {
    const apiErrors = error.response?.data?.errors
    if (apiErrors?.nombre?.length) {
      codigoError.value = apiErrors.nombre[0]
    }
    modalError.value = error.response?.data?.message || (codigoError.value ? '' : 'No se pudo guardar el repuesto')
  } finally {
    loading.value = false
  }
}

const deleteRepuesto = async (id) => {
  if (!window.confirm('¿Eliminar repuesto? Se marcara como inactivo.')) return

  loading.value = true
  try {
    await api.delete(`/repuestos/${id}`)
    await loadRepuestos()
  } finally {
    loading.value = false
  }
}

watch(onlyActive, () => {
  loadRepuestos()
})

onMounted(async () => {
  await loadRepuestos()

  const highlightNombre = route.query.highlight
  if (highlightNombre) {
    const idx = items.value.findIndex((i) => i.nombre === highlightNombre)
    if (idx !== -1) {
      currentPage.value = Math.ceil((idx + 1) / itemsPerPage)
      highlightedId.value = items.value[idx].id_repuesto
      await nextTick()
      const el = document.getElementById(`row-${highlightedId.value}`)
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      setTimeout(() => { highlightedId.value = null }, 3500)
    }
  }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Manrope:wght@400;600;700&display=swap');

.inventory-shell {
  min-height: 100vh;
  background: radial-gradient(circle at top, #d8dde8, #bfc6cf);
  padding: 18px;
  font-family: 'Manrope', sans-serif;
}

.inventory-frame {
  max-width: 1220px;
  margin: 0 auto;
  border: 1px solid #9ba5b5;
  background: #f5f6f8;
  box-shadow: 0 20px 60px rgba(8, 15, 32, 0.24);
}

.topbar {
  min-height: 52px;
  background: linear-gradient(90deg, #0d1d56, #122a78);
  color: #fff;
  display: grid;
  grid-template-columns: auto auto 1fr auto;
  align-items: center;
  gap: 16px;
  padding: 8px 20px;
}

.menu-toggle {
  display: grid;
  gap: 5px;
}

.menu-toggle span {
  width: 22px;
  height: 2px;
  background: #fff;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  width: 20px;
  height: 20px;
}

.brand-name {
  letter-spacing: 0.2em;
  font-size: 11px;
  font-family: 'Cinzel', serif;
}

.top-links {
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

.top-links a {
  color: #eef2ff;
  text-decoration: none;
  font-size: 10px;
  letter-spacing: 0.08em;
}

.top-links a.router-link-active {
  color: #ffffff;
  text-decoration: underline;
}

.top-icons {
  display: flex;
  gap: 8px;
}

.top-icons span {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.55);
  display: grid;
  place-items: center;
  font-size: 10px;
}

.inventory-content {
  padding: 0 26px 30px;
}

.hero-grid {
  margin-top: 8px;
  position: relative;
  display: grid;
  grid-template-columns: 2.1fr 1fr;
  grid-template-rows: 120px 190px;
  gap: 8px;
  overflow: hidden;
}

.hero-grid article {
  position: relative;
  overflow: hidden;
}

.hero-grid img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-left {
  grid-row: 1 / span 2;
}

.hero-top-right {
  grid-column: 2;
  grid-row: 1;
}

.hero-bottom-right {
  grid-column: 2;
  grid-row: 2;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(6, 10, 24, 0.35), rgba(6, 10, 24, 0.62), rgba(255, 255, 255, 0.78));
  pointer-events: none;
}

.hero-copy {
  position: absolute;
  inset: 0;
  display: grid;
  place-content: center;
  text-align: center;
  color: #fff;
  z-index: 2;
  gap: 8px;
  padding: 0 20px;
}

.hero-copy h1 {
  margin: 0;
  font-family: 'Cinzel', serif;
  font-size: clamp(24px, 3.8vw, 48px);
  font-weight: 500;
  letter-spacing: 0.05em;
}

.hero-copy p {
  margin: 0;
  font-family: 'Cinzel', serif;
  font-size: clamp(12px, 1.6vw, 30px);
  letter-spacing: 0.04em;
}

.toolbar-row {
  margin-top: 16px;
  border-bottom: 1px solid #d7dce4;
  display: flex;
  gap: 10px;
  padding: 8px 8px 12px;
}

.tab-action {
  border: none;
  background: transparent;
  color: #8f96a4;
  font-size: 12px;
  letter-spacing: 0.03em;
  padding: 8px;
  cursor: pointer;
}

.tab-action.active {
  color: #2f3858;
  font-weight: 700;
}

.table-card {
  margin-top: 16px;
  background: #fff;
  border: 1px solid #d0d6df;
  border-radius: 8px;
  padding: 14px;
}

.table-title-row {
  border-bottom: 1px solid #cad1dc;
  padding-bottom: 8px;
}

.table-title-row h2 {
  margin: 0;
  font-family: 'Cinzel', serif;
  font-size: 34px;
  font-weight: 500;
  color: #39405b;
}

.search-wrap {
  margin: 14px 0 10px;
  position: relative;
}

.search-wrap input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d6dce5;
  border-radius: 18px;
  padding: 10px 38px 10px 14px;
  font-size: 13px;
}

.search-icon {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #495063;
}

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 760px;
}

thead th {
  background: #eff2f6;
  color: #2f3858;
  text-align: left;
  font-size: 12px;
  padding: 9px 10px;
  border-bottom: 1px solid #d8dde6;
  white-space: nowrap;
}

thead th:nth-child(1) { width: 150px; }
thead th:nth-child(3) { width: 90px; }
thead th:nth-child(4) { width: 90px; }
thead th:nth-child(5) { width: 90px; }
thead th:nth-child(6) { width: 90px; }
thead th:nth-child(7) { width: 100px; }
thead th:nth-child(8) { width: 80px; }

tbody td {
  font-size: 13px;
  color: #2c3245;
  padding: 8px 10px;
  border-bottom: 1px solid #eceff4;
  vertical-align: middle;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.thumb {
  width: 38px;
  height: 26px;
  border-radius: 6px;
  display: inline-block;
  border: 1px solid rgba(36, 42, 56, 0.12);
}

.thumb.t1 { background: linear-gradient(135deg, #6f7b88, #bec8d5); }
.thumb.t2 { background: linear-gradient(135deg, #40464f, #909aa7); }
.thumb.t3 { background: linear-gradient(135deg, #3c4458, #687089); }
.thumb.t4 { background: linear-gradient(135deg, #5f6063, #a0a39f); }
.thumb.t5 { background: linear-gradient(135deg, #758696, #cad4dd); }
.thumb.t6 { background: linear-gradient(135deg, #5ea7ca, #9bd0ec); }
.thumb.t7 { background: linear-gradient(135deg, #8a8e95, #d0d5de); }
.thumb.t8 { background: linear-gradient(135deg, #61656d, #b4bcc8); }

.state-pill {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 3px 9px;
  font-size: 11px;
  font-weight: 700;
}

.state-pill.ok {
  background: #e7f6ee;
  color: #1d7a43;
}

.state-pill.warning {
  background: #fff6dc;
  color: #9d7300;
}

.state-pill.danger {
  background: #ffe7e7;
  color: #bc2f2f;
}

.actions-cell {
  display: flex;
  gap: 7px;
}

.mini-btn {
  border: none;
  border-radius: 6px;
  min-width: 30px;
  height: 28px;
  color: #fff;
  cursor: pointer;
}

.mini-btn.edit {
  background: #2f6bc4;
}

.mini-btn.delete {
  background: #da4a4a;
}

.empty {
  text-align: center;
  color: #81889c;
  padding: 18px;
}

@keyframes blink-row {
  0%, 100% { background: transparent; }
  50% { background: #fff3b0; }
}

.row-blink {
  animation: blink-row 0.7s ease 5;
}

.pager {
  margin-top: 12px;
  display: flex;
  justify-content: center;
  gap: 8px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: grid;
  place-items: center;
  padding: 16px;
  z-index: 1200;
}

.modal {
  width: min(460px, 94vw);
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

.modal h3 {
  margin: 0 0 10px;
  color: #2f3858;
  font-family: 'Cinzel', serif;
}

.modal label {
  display: block;
  font-weight: 700;
  margin: 10px 0 6px;
}

.modal input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #cbd3de;
  border-radius: 8px;
  padding: 9px 10px;
}

.modal textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #cbd3de;
  border-radius: 8px;
  padding: 9px 10px;
  resize: vertical;
}

.inline-check {
  display: flex;
  align-items: center;
  gap: 8px;
}

.inline-check input {
  width: auto;
}

.actions-row {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.actions-row button {
  border: none;
  border-radius: 8px;
  padding: 10px 14px;
  background: #2f6bc4;
  color: #fff;
  cursor: pointer;
}

.actions-row .ghost {
  background: #e4e8ef;
  color: #1f2937;
}

.field-error {
  color: #d32f2f;
  margin: 6px 0 0;
  font-size: 12px;
  font-weight: 600;
}

.description-cell {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.page-btn {
  border: 1px solid #d4dae3;
  background: #fff;
  color: #3a4158;
  min-width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
}

.page-btn.active {
  background: #2f6bc4;
  color: #fff;
  border-color: #2f6bc4;
}

.page-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.footer {
  background: #9ea4a4;
  color: #fff;
  padding: 26px 34px;
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr;
  gap: 30px;
}

.footer-brand img {
  width: 32px;
}

.footer-brand h3 {
  margin: 10px 0 8px;
  font-family: 'Cinzel', serif;
  font-size: 36px;
  font-weight: 500;
}

.footer-brand p {
  margin: 0;
  max-width: 290px;
  color: #ececec;
  font-size: 15px;
}

.footer h4 {
  margin: 0 0 10px;
  font-family: 'Cinzel', serif;
  font-size: 30px;
  font-weight: 500;
}

.footer a {
  display: block;
  color: #ececec;
  text-decoration: none;
  margin-bottom: 6px;
  font-size: 15px;
}

@media (max-width: 980px) {
  .topbar {
    grid-template-columns: auto 1fr auto;
  }

  .menu-toggle {
    display: none;
  }

  .brand {
    justify-self: start;
  }

  .top-links {
    grid-column: 1 / -1;
    order: 2;
    justify-content: flex-start;
    padding-top: 6px;
  }

  .hero-grid {
    grid-template-columns: 1fr;
    grid-template-rows: 240px;
  }

  .hero-top-right,
  .hero-bottom-right {
    display: none;
  }

  .table-title-row h2 {
    font-size: 26px;
  }

  .footer {
    grid-template-columns: 1fr;
    gap: 18px;
  }
}
</style>
