<?php
/**
 * VERIFICADOR DE EXTENSIONES PHP PARA OSPOS
 * Verifica que todas las extensiones necesarias estén disponibles
 */

echo "🔍 VERIFICADOR PHP PARA OSPOS\n";
echo "============================\n\n";

// Extensiones requeridas por OSPOS
$extensiones_requeridas = [
    'intl' => '🌐 Internacionalización (CRÍTICO)',
    'mysqli' => '🗄️  MySQL Improved',
    'pdo' => '🗄️  PDO Database',
    'pdo_mysql' => '🗄️  PDO MySQL',
    'pdo_sqlite' => '🗄️  PDO SQLite',
    'sqlite3' => '💾 SQLite3',
    'gd' => '🖼️  Procesamiento de imágenes',
    'mbstring' => '🔤 Manejo de strings multibyte',
    'curl' => '🌐 CURL para HTTP requests',
    'openssl' => '🔐 OpenSSL para encriptación',
    'zip' => '📦 Compresión ZIP',
    'fileinfo' => '📄 Información de archivos',
];

echo "Verificando extensiones PHP...\n\n";

$todas_ok = true;
$criticas_faltantes = [];

foreach ($extensiones_requeridas as $extension => $descripcion) {
    if (extension_loaded($extension)) {
        echo "✅ $descripcion\n";
    } else {
        echo "❌ $descripcion\n";
        $todas_ok = false;

        if ($extension === 'intl') {
            $criticas_faltantes[] = $extension;
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

if ($todas_ok) {
    echo "🎉 ¡TODAS LAS EXTENSIONES ESTÁN DISPONIBLES!\n";
    echo "✅ OSPOS puede ejecutarse correctamente\n";
    echo "\n💡 Para iniciar OSPOS:\n";
    echo "   php -S localhost:8000 -t public\n";
    echo "\n🌐 URL de acceso: http://localhost:8000\n";
    echo "👤 Usuario: admin | Contraseña: pointofsale\n";
} else {
    echo "⚠️  ALGUNAS EXTENSIONES FALTAN\n\n";

    if (!empty($criticas_faltantes)) {
        echo "🚨 EXTENSIONES CRÍTICAS FALTANTES:\n";
        foreach ($criticas_faltantes as $ext) {
            echo "   - $ext\n";
        }
        echo "\n❌ OSPOS NO FUNCIONARÁ sin estas extensiones críticas\n";
    }

    echo "\n🔧 SOLUCIÓN:\n";
    echo "1. Edita tu php.ini: " . php_ini_loaded_file() . "\n";
    echo "2. Descomenta las líneas de extensiones faltantes\n";
    echo "3. Reinicia el servidor PHP\n";
}

echo "\n📄 Información del sistema:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP INI: " . php_ini_loaded_file() . "\n";
echo "Sistema: " . PHP_OS . "\n";

echo "\n" . str_repeat("=", 50) . "\n";
?>