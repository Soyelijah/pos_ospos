<?php
/**
 * Script para probar la conexión a la base de datos antes de ejecutar OSPOS
 */

echo "=== OSPOS - Test de Conexión a Base de Datos ===\n\n";

// Cargar configuración del .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " '\"");
            $_ENV[$key] = $value;
        }
    }
}

// Configuración de base de datos por defecto
$config = [
    'hostname' => $_ENV['database.default.hostname'] ?? 'localhost',
    'database' => $_ENV['database.default.database'] ?? 'zgamersa_dysapos',
    'username' => $_ENV['database.default.username'] ?? 'zgamersa_dysapos',
    'password' => $_ENV['database.default.password'] ?? '9w94px]S(9',
    'port' => $_ENV['database.default.port'] ?? 3306
];

echo "Probando conexión con:\n";
echo "- Host: " . $config['hostname'] . ":" . $config['port'] . "\n";
echo "- Database: " . $config['database'] . "\n";
echo "- Username: " . $config['username'] . "\n";
echo "- Password: " . str_repeat('*', strlen($config['password'])) . "\n\n";

try {
    $dsn = "mysql:host={$config['hostname']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ CONEXIÓN EXITOSA!\n";

    // Verificar si existen tablas OSPOS
    $stmt = $pdo->query("SHOW TABLES LIKE 'ospos_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "✅ Base de datos ya contiene " . count($tables) . " tablas OSPOS\n";
        echo "Tablas encontradas: " . implode(', ', array_slice($tables, 0, 5));
        if (count($tables) > 5) {
            echo " y " . (count($tables) - 5) . " más...";
        }
        echo "\n";
    } else {
        echo "⚠️  Base de datos existe pero está vacía. Se necesita ejecutar la migración.\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
    echo "\nPosibles soluciones:\n";
    echo "1. Verificar que MySQL/MariaDB esté ejecutándose\n";
    echo "2. Verificar credenciales en el archivo .env\n";
    echo "3. Verificar que la base de datos existe\n";
    echo "4. Verificar permisos del usuario\n";
    exit(1);
}

echo "\n=== Test completado ===\n";
?>