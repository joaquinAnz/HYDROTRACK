import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import logo from '../assets/logo.png'

export function useInventario() {
  const route = useRoute()
  const highlightedId = ref(null)
  const search = ref('')
  const currentPage = ref(1)
  const itemsPerPage = 7

  const items = ref([])
  const onlyActive = ref(true)
  const loading = ref(false)
  const showModal = ref(false)
  const modalMode = ref('create')
  const modalError = ref('')
  const codigoError = ref('')
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
    codigoError.value = ''
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
    codigoError.value = ''
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
    codigoError.value = ''

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

  return {
    logo,
    search,
    currentPage,
    items,
    onlyActive,
    loading,
    showModal,
    modalMode,
    modalError,
    highlightedId,
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
    thumbClassFor,
  }
}
