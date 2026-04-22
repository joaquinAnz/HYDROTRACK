<template>
  <div class="dashboard-shell">
    <div class="dashboard-frame">
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
          <a href="#">CLIENTES</a>
          <router-link to="/inventario">INVENTARIO</router-link>
          <a href="#">ORDENES</a>
          <a href="#">PAGOS</a>
          <router-link to="/usuarios">USUARIOS</router-link>
          <a href="#">REPORTES</a>
        </nav>

        <div class="top-icons">
          <span>o</span>
          <span>{{ initials }}</span>
        </div>
      </header>

      <main class="dashboard-content">
        <section class="hero">
          <img :src="heroImage" alt="Panel Hydrotrack" class="hero-image" />
          <div class="hero-overlay"></div>
          <div class="hero-copy">
            <p class="hero-subtitle">Taller sistemas de informacion</p>
            <h1>PANEL DE ADMINISTRACION<br />HYDROTRACK</h1>
            <p class="welcome">Bienvenido, {{ nombre }} ({{ usuario }})</p>
          </div>
        </section>

        <section class="stats-grid">
          <article v-for="stat in stats" :key="stat.label" class="stat-card">
            <p class="label">{{ stat.label }}</p>
            <p class="value">{{ stat.value }}</p>
          </article>
        </section>

        <section class="panel-grid">
          <article class="panel orders-panel">
            <h2>ORDENES RECIENTES</h2>
            <div class="order-list">
              <div class="order-item" v-for="order in orders" :key="order.id">
                <div class="status-pill" :class="order.statusClass">{{ order.status }}</div>
                <div class="order-meta">
                  <p class="order-title">#{{ order.id }} {{ order.client }}</p>
                  <p class="order-sub">{{ order.description }}</p>
                </div>
                <div class="order-date">{{ order.date }}</div>
              </div>
            </div>
          </article>

          <article class="panel alerts-panel">
            <h2>ALERTAS DEL SISTEMA</h2>
            <div class="alert-list">
              <div class="alert-item" v-for="alert in alerts" :key="alert.title" :class="alert.tone">
                <div class="alert-icon">!</div>
                <div class="alert-content">
                  <p class="alert-title">{{ alert.title }}</p>
                  <p class="alert-sub">{{ alert.detail }}</p>
                </div>
                <button type="button" @click="goToAlert(alert)">Ver</button>
              </div>
            </div>
          </article>
        </section>

        <section class="chart-panel">
          <h2>INGRESOS MENSUALES</h2>
          <div class="chart-wrap">
            <svg viewBox="0 0 700 260" preserveAspectRatio="none" aria-label="Grafico de ingresos">
              <defs>
                <linearGradient id="incomeFill" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#f5a7a2" stop-opacity="0.45" />
                  <stop offset="100%" stop-color="#f5a7a2" stop-opacity="0.05" />
                </linearGradient>
              </defs>
              <polyline
                points="20,220 95,190 170,200 245,155 320,140 395,162 470,120 545,110 620,92 680,80"
                fill="none"
                stroke="#de6f68"
                stroke-width="4"
                stroke-linecap="round"
              />
              <polygon
                points="20,220 95,190 170,200 245,155 320,140 395,162 470,120 545,110 620,92 680,80 680,240 20,240"
                fill="url(#incomeFill)"
              />
            </svg>
          </div>
          <div class="month-row">
            <span v-for="month in months" :key="month">{{ month }}</span>
          </div>
        </section>
      </main>

      <footer class="footer">
        <div class="footer-brand">
          <img :src="logo" alt="Hydrotrack" />
          <h3>HYDROTRACK</h3>
          <p>La plataforma para gestionar tu taller automotriz de forma clara y eficiente.</p>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import heroImage from '../assets/hero.png'
import logo from '../assets/logo.png'
import api from '../services/api'

const router = useRouter()

const currentUser = JSON.parse(localStorage.getItem('user') || sessionStorage.getItem('user') || '{}')
const nombre = currentUser.nombre || 'Administrador'
const usuario = currentUser.usuario || 'admin'
const initials = (nombre || 'A')
  .split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((word) => word[0].toUpperCase())
  .join('')

const stats = ref([
  { label: 'Clientes Registrados', value: '...' },
  { label: 'Ingresos del mes', value: 'Bs 10k' },
  { label: 'Ordenes en proceso', value: '2' },
  { label: 'Repuestos en stock', value: '...' }
])

const orders = [
  {
    id: '004',
    client: 'Carlos Ruiz',
    description: 'En Proceso - Servicio de frenos',
    status: 'En Proceso',
    statusClass: 'in-progress',
    date: '23/04/2024'
  },
  {
    id: '003',
    client: 'Jaci Gutierrez',
    description: 'Completada - Mantenimiento general',
    status: 'Completada',
    statusClass: 'done',
    date: '20/04/2024'
  },
  {
    id: '002',
    client: 'Juan Perez',
    description: 'Retrasada - Diagnostico motor',
    status: 'Retrasada',
    statusClass: 'late',
    date: '18/04/2024'
  }
]

const alerts = ref([
  {
    title: 'Orden #003 esta retrasada',
    detail: 'Retraso mayor a la fecha estimada',
    tone: 'caution'
  },
  {
    title: 'Pago pendiente de Maria Gomez',
    detail: 'Bs 140 por servicio pendiente',
    tone: 'success'
  }
])

onMounted(async () => {
  try {
    const { data } = await api.get('/repuestos', { params: { activos: 1 } })
    stats.value[3].value = String(data.length)
    const stockAlerts = data
      .filter((r) => r.stock_actual <= r.stock_minimo)
      .map((r) => ({
        title: r.stock_actual <= 0
          ? `${r.nombre} — AGOTADO`
          : `${r.nombre} — Stock bajo`,
        detail: `${r.descripcion || r.marca || ''} · Stock: ${r.stock_actual} / Min: ${r.stock_minimo}`,
        tone: r.stock_actual <= 0 ? 'warning' : 'caution',
        repuestoNombre: r.nombre
      }))
    alerts.value = [...stockAlerts, ...alerts.value]
  } catch (_) {
    stats.value[3].value = '-'
  }

  try {
    const { data: clientes } = await api.get('/clientes')
    const activos = clientes.filter((c) => c.estado === true)
    stats.value[0].value = String(activos.length)
  } catch (_) {
    stats.value[0].value = '-'
  }
})

const goToAlert = (alert) => {
  if (alert.repuestoNombre) {
    router.push({ path: '/inventario', query: { highlight: alert.repuestoNombre } })
  }
}

const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct']
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Manrope:wght@400;600;700&display=swap');

.dashboard-shell {
  --blue-900: #122a78;
  --blue-950: #0d1d56;
  --ink-900: #23252f;
  --ink-700: #545a68;
  --line: #d7dbe3;
  --page: #f2f4f8;
  --card: #ffffff;
  --accent-red: #de6f68;
  min-height: 100vh;
  background: radial-gradient(circle at top, #d9dde5, #bcc2cb);
  padding: 18px;
  font-family: 'Manrope', sans-serif;
}

.dashboard-frame {
  max-width: 1220px;
  margin: 0 auto;
  background: var(--card);
  border: 1px solid #9ba5b5;
  box-shadow: 0 20px 60px rgba(8, 15, 32, 0.22);
}

.topbar {
  min-height: 52px;
  background: linear-gradient(90deg, var(--blue-950), var(--blue-900));
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
  letter-spacing: 0.22em;
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

.top-links a:hover {
  color: #fff;
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

.dashboard-content {
  background: var(--page);
  padding: 0 34px 34px;
}

.hero {
  position: relative;
  height: 340px;
  overflow: hidden;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.02);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(7, 12, 28, 0.28), rgba(7, 12, 28, 0.64));
}

.hero-copy {
  position: absolute;
  inset: 0;
  color: #fff;
  text-align: center;
  display: grid;
  place-content: center;
  gap: 6px;
  font-family: 'Cinzel', serif;
}

.hero-copy > * {
  opacity: 0;
  transform: translateY(12px);
  animation: rise 0.7s ease forwards;
}

.hero-subtitle {
  animation-delay: 0.1s;
  font-size: 18px;
}

.hero-copy h1 {
  animation-delay: 0.25s;
  margin: 0;
  font-size: clamp(28px, 4.4vw, 64px);
  font-weight: 500;
  letter-spacing: 0.06em;
  line-height: 1.1;
  color: #fff;
}

.welcome {
  animation-delay: 0.4s;
  font-size: 13px;
  letter-spacing: 0.08em;
}

.stats-grid {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.stat-card {
  background: #fff;
  border: 1px solid #cfd5e0;
  border-radius: 8px;
  text-align: center;
  padding: 12px;
  box-shadow: 0 4px 10px rgba(35, 42, 62, 0.08);
}

.stat-card .label {
  margin: 0;
  font-size: 14px;
  color: #2f3442;
}

.stat-card .value {
  margin: 8px 0 0;
  font-size: 34px;
  line-height: 1;
  color: #b7b7b7;
  font-family: 'Cinzel', serif;
}

.panel-grid {
  margin-top: 26px;
  padding-top: 26px;
  border-top: 1px solid var(--line);
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 22px;
}

.panel h2,
.chart-panel h2 {
  margin: 0 0 16px;
  font-family: 'Cinzel', serif;
  color: #2f3761;
  letter-spacing: 0.03em;
  font-size: 30px;
  font-weight: 500;
}

.orders-panel,
.alerts-panel {
  min-height: 280px;
}

.order-list,
.alert-list {
  display: grid;
  gap: 10px;
}

.order-item {
  background: #fff;
  border: 1px solid #d5dae3;
  border-left: 5px solid #b8c3d9;
  border-radius: 8px;
  padding: 12px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 12px;
}

.status-pill {
  font-size: 11px;
  font-weight: 700;
  color: #fff;
  border-radius: 999px;
  padding: 4px 8px;
}

.status-pill.in-progress {
  background: #3b955d;
}

.status-pill.done {
  background: #d1a73b;
}

.status-pill.late {
  background: #d34b4b;
}

.order-title,
.alert-title {
  margin: 0;
  color: var(--ink-900);
  font-weight: 700;
  font-size: 14px;
}

.order-sub,
.alert-sub {
  margin: 2px 0 0;
  color: var(--ink-700);
  font-size: 12px;
}

.order-date {
  color: var(--ink-900);
  font-size: 12px;
  font-weight: 600;
}

.alert-item {
  background: #fff;
  border: 1px solid #d5dae3;
  border-left: 4px solid #ea8d2d;
  border-radius: 8px;
  padding: 12px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
}

.alert-item.warning {
  border-left-color: #e95a58;
}

.alert-item.caution {
  border-left-color: #e8bc4a;
}

.alert-item.success {
  border-left-color: #50b673;
}

.alert-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  color: #fff;
  background: #e95a58;
  font-size: 12px;
}

.alert-item.caution .alert-icon {
  background: #e8bc4a;
}

.alert-item.success .alert-icon {
  background: #50b673;
}

.alert-item button {
  border: none;
  border-radius: 6px;
  background: #f07f7a;
  color: #fff;
  font-size: 12px;
  padding: 7px 10px;
  cursor: pointer;
}

.chart-panel {
  margin-top: 26px;
  padding-top: 24px;
  border-top: 1px solid var(--line);
}

.chart-wrap {
  height: 260px;
  background: linear-gradient(to bottom, rgba(222, 111, 104, 0.04), rgba(222, 111, 104, 0.01));
  border: 1px solid #dfe3ea;
  border-radius: 8px;
  overflow: hidden;
}

.chart-wrap svg {
  width: 100%;
  height: 100%;
}

.month-row {
  display: grid;
  grid-template-columns: repeat(10, 1fr);
  margin-top: 8px;
  color: #8690a0;
  font-size: 12px;
}

.month-row span {
  text-align: center;
}

.footer {
  background: #959ca2;
  color: #f6f9fb;
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr;
  gap: 28px;
  padding: 28px 34px;
}

.footer h3,
.footer h4 {
  margin: 0 0 12px;
  color: #fff;
  font-family: 'Cinzel', serif;
  font-size: 22px;
}

.footer-brand img {
  width: 30px;
}

.footer p,
.footer a {
  color: #eef2f7;
  margin: 0 0 6px;
  text-decoration: none;
  font-size: 13px;
  display: block;
}

@keyframes rise {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 1024px) {
  .topbar {
    grid-template-columns: auto 1fr auto;
    row-gap: 10px;
  }

  .brand {
    justify-self: center;
  }

  .top-links {
    grid-column: 1 / -1;
    justify-content: flex-start;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 4px;
  }

  .dashboard-content {
    padding: 0 18px 24px;
  }

  .hero {
    height: 290px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .panel-grid {
    grid-template-columns: 1fr;
  }

  .panel h2,
  .chart-panel h2 {
    font-size: 25px;
  }

  .footer {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 620px) {
  .dashboard-shell {
    padding: 0;
    background: #2d2f35;
  }

  .dashboard-frame {
    border: none;
    box-shadow: none;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .hero-subtitle {
    font-size: 15px;
  }

  .welcome {
    font-size: 11px;
  }

  .order-item,
  .alert-item {
    grid-template-columns: 1fr;
    align-items: flex-start;
  }

  .month-row {
    font-size: 11px;
  }
}
</style>