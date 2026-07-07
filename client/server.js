const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.static(__dirname));
app.use(express.json());

// Servir la página principal
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`\n╔═══════════════════════════════════════╗`);
    console.log(`║  Cliente API ejecutándose en:         ║`);
    console.log(`║  http://localhost:${PORT}           ║`);
    console.log(`║                                       ║`);
    console.log(`║  API disponible en:                   ║`);
    console.log(`║  http://localhost:8000                ║`);
    console.log(`╚═══════════════════════════════════════╝\n`);
});
