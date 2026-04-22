import axios from 'axios';
import { ref, computed, onMounted } from 'vue'

const API = 'http://127.0.0.1:8000/api/ordenes-trabajo';

export const crearOrden = (data) => axios.post(API, data);

export const obtenerOrdenes = () => axios.get(API);

export const obtenerOrden = (id) => axios.get(`${API}/${id}`);

export const actualizarOrden = (id, data) =>
  axios.put(`${API}/${id}`, data);a