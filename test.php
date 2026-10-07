<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Servidor Hostinger - Inbox CEFI</h1>";
echo "<p><strong>Versión de PHP del Servidor:</strong> " . phpversion() . "</p>";
echo "<p><strong>Ruta Actual:</strong> " . __DIR__ . "</p>";

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p style='color:green;'>✔️ <strong>vendor/autoload.php EXISTE.</strong></p>";
} else {
    echo "<p style='color:red;'>❌ <strong>vendor/autoload.php NO EXISTE en el servidor.</strong></p>";
}

if (file_exists(__DIR__ . '/.env')) {
    echo "<p style='color:green;'>✔️ <strong>Archivo .env EXISTE.</strong></p>";
} else {
    echo "<p style='color:red;'>❌ <strong>Archivo .env NO EXISTE en el servidor.</strong></p>";
}

try {
    require __DIR__ . '/vendor/autoload.php';
    echo "<p style='color:green;'>✔️ Autoload cargado con éxito.</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>❌ Error al cargar Autoload: " . $e->getMessage() . "</p>";
}
