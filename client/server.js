// Servidor mínimo para el frontend de pruebas de CORS.
// Sirve la carpeta public/ en http://localhost:3000
// Así el navegador hace peticiones desde :3000 hacia la API de Laravel (:8000),
// que es exactamente el escenario cross-origin que valida el CORS.

const express = require('express');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.static(path.join(__dirname, 'public')));

app.listen(PORT, () => {
  console.log(`Frontend de pruebas corriendo en http://localhost:${PORT}`);
  console.log('Asegúrate de tener Laravel corriendo: php artisan serve (http://localhost:8000)');
});
