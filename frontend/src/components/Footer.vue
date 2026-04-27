<template>
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
</template>

<script setup>
const props = defineProps({
  logo: { type: String, required: true },
})
</script>

<style scoped>
.footer {
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  width: 100vw;
  max-width: 100vw;
  box-sizing: border-box;
  background: #9ea4a4;
  color: #fff;
  padding: 26px 34px;
  display: grid;
  grid-template-columns: minmax(280px, 350px) 1fr 1fr;
  gap: 30px;
  border-bottom: none;
}

.footer-brand {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-self: start;
  text-align: left;
  align-items: center;
  gap: 10px;
}

.footer-brand img {
  width: 70px;
  height: auto;
}

.footer-brand h3 {
  margin: 0;
  font-family: 'Cinzel', serif;
  font-size: 36px;
  font-weight: 500;
}

.footer-brand p {
  margin: 0;
  max-width: 360px;
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
  .footer {
    grid-template-columns: 1fr;
    gap: 18px;
  }
}
</style>
