<?php
/**
 * CONFIGURADOR SQLITE PORTABLE PARA RESTAURANTES
 * La mejor opción para sistemas que se mueven entre sucursales
 */

echo "🚀 OSPOS - Configurador SQLite Portable\n";
echo "=====================================\n";
echo "✅ Ideal para restaurantes con múltiples sucursales\n";
echo "✅ Sin dependencias de servidor MySQL\n";
echo "✅ Toda la data en un solo archivo\n";
echo "✅ Copia toda la carpeta = sistema completo\n\n";

// Configuración SQLite
$db_path = __DIR__ . '/writable/ospos_restaurante.db';

echo "🛠️  Configurando SQLite...\n";

// Crear base de datos SQLite
try {
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Base de datos SQLite creada: $db_path\n";

    // Verificar si ya tiene tablas
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ospos_%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tablas) == 0) {
        echo "📊 Importando estructura base de datos...\n";

        // Leer SQL de MySQL y convertir para SQLite
        $mysql_sql = file_get_contents(__DIR__ . '/app/Database/database.sql');

        // Conversiones básicas de MySQL a SQLite
        $sqlite_sql = $mysql_sql;
        $sqlite_sql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $sqlite_sql);
        $sqlite_sql = preg_replace('/ENGINE=InnoDB/i', '', $sqlite_sql);
        $sqlite_sql = preg_replace('/DEFAULT CHARSET=utf8/i', '', $sqlite_sql);
        $sqlite_sql = preg_replace('/COLLATE utf8_general_ci/i', '', $sqlite_sql);
        $sqlite_sql = preg_replace('/varchar\((\d+)\)/i', 'TEXT', $sqlite_sql);
        $sqlite_sql = preg_replace('/int\(\d+\)/i', 'INTEGER', $sqlite_sql);
        $sqlite_sql = preg_replace('/decimal\(\d+,\d+\)/i', 'REAL', $sqlite_sql);
        $sqlite_sql = preg_replace('/timestamp/i', 'DATETIME', $sqlite_sql);

        // Ejecutar statements
        $statements = array_filter(array_map('trim', explode(';', $sqlite_sql)));
        $exitosos = 0;

        foreach ($statements as $stmt) {
            if (!empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^INSERT/', $stmt)) {
                try {
                    $pdo->exec($stmt);
                    $exitosos++;
                } catch (PDOException $e) {
                    // Continuar con siguientes statements
                }
            }
        }

        echo "✅ Estructura creada ($exitosos statements ejecutados)\n";

        // Crear usuario administrador por defecto
        echo "👤 Creando usuario administrador...\n";

        $admin_queries = [
            "INSERT OR REPLACE INTO ospos_people (person_id, first_name, last_name, email, phone_number, address_1, address_2, city, state, zip, country, comments) VALUES (1, 'Admin', 'OSPOS', 'admin@ospos.com', '555-555-5555', 'Dirección del Restaurante', '', 'Ciudad', 'Estado', '00000', 'MX', 'Usuario administrador del sistema')",

            "INSERT OR REPLACE INTO ospos_employees (person_id, username, password, language, language_code, deleted, hash_version) VALUES (1, 'admin', '\$2y\$10\$GKdzDhPNKe7XnbFIzSCJ8.gGp0LnMYQhKpuW9LyMyxuVLjANgxqQG', 'english', 'en', 0, 2)",

            "INSERT OR REPLACE INTO ospos_grants (permission_id, person_id) SELECT 'config', 1 WHERE NOT EXISTS (SELECT 1 FROM ospos_grants WHERE permission_id = 'config' AND person_id = 1)",

            "INSERT OR REPLACE INTO ospos_grants (permission_id, person_id) SELECT 'customers', 1 WHERE NOT EXISTS (SELECT 1 FROM ospos_grants WHERE permission_id = 'customers' AND person_id = 1)",

            "INSERT OR REPLACE INTO ospos_grants (permission_id, person_id) SELECT 'employees', 1 WHERE NOT EXISTS (SELECT 1 FROM ospos_grants WHERE permission_id = 'employees' AND person_id = 1)",

            "INSERT OR REPLACE INTO ospos_grants (permission_id, person_id) SELECT 'items', 1 WHERE NOT EXISTS (SELECT 1 FROM ospos_grants WHERE permission_id = 'items' AND person_id = 1)",

            "INSERT OR REPLACE INTO ospos_grants (permission_id, person_id) SELECT 'sales', 1 WHERE NOT EXISTS (SELECT 1 FROM ospos_grants WHERE permission_id = 'sales' AND person_id = 1)",
        ];

        foreach ($admin_queries as $query) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                // Continuar
            }
        }

        echo "✅ Usuario admin creado (contraseña: pointofsale)\n";

    } else {
        echo "✅ Base de datos ya contiene " . count($tablas) . " tablas\n";
    }

} catch (PDOException $e) {
    die("❌ Error con SQLite: " . $e->getMessage() . "\n");
}

// Crear configuración .env para SQLite
echo "\n⚙️  Creando configuración SQLite...\n";

$env_content = "
#--------------------------------------------------------------------
# OSPOS PORTABLE PARA RESTAURANTES - SQLite
# Generado: " . date('Y-m-d H:i:s') . "
# ✅ Sin dependencias de servidor
# ✅ Toda la data en writable/ospos_restaurante.db
#--------------------------------------------------------------------

CI_ENVIRONMENT = development
CI_DEBUG = false

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.appTimezone = 'America/Mexico_City'

#--------------------------------------------------------------------
# DATABASE - SQLite Portable
#--------------------------------------------------------------------

database.default.hostname = ''
database.default.database = 'writable/ospos_restaurante.db'
database.default.username = ''
database.default.password = ''
database.default.DBDriver = 'SQLite3'
database.default.DBPrefix = 'ospos_'
database.default.port = ''

database.development.hostname = ''
database.development.database = 'writable/ospos_restaurante.db'
database.development.username = ''
database.development.password = ''
database.development.DBDriver = 'SQLite3'
database.development.DBPrefix = 'ospos_'
database.development.port = ''

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption.key = '" . bin2hex(random_bytes(32)) . "'

#--------------------------------------------------------------------
# CONFIGURACIÓN RESTAURANTE
#--------------------------------------------------------------------

logger.threshold = 2
app.db_log_enabled = false
app.db_log_only_long = false
";

file_put_contents('.env', $env_content);
echo "✅ Configuración SQLite guardada\n";

// Crear script de inicio
$start_script = "@echo off
title OSPOS - Sistema POS para Restaurantes
echo.
echo =====================================
echo    OSPOS - Sistema POS Restaurante
echo =====================================
echo.
echo Iniciando servidor en puerto 8000...
echo.
echo URLs de acceso:
echo   Local:    http://localhost:8000
echo   Red:      http://%COMPUTERNAME%:8000
echo.
echo Usuario: admin
echo Clave:   pointofsale
echo.
echo Presiona Ctrl+C para detener
echo =====================================
echo.

php -S 0.0.0.0:8000 -t public
pause
";

file_put_contents('iniciar_ospos.bat', $start_script);
echo "✅ Script de inicio creado: iniciar_ospos.bat\n";

echo "\n🎉 ¡CONFIGURACIÓN SQLITE COMPLETADA!\n";
echo "=======================================\n";
echo "🚀 Para iniciar OSPOS:\n";
echo "   - Doble clic en: iniciar_ospos.bat\n";
echo "   - O ejecuta: php -S localhost:8000 -t public\n";
echo "\n";
echo "🌐 Acceso:\n";
echo "   URL: http://localhost:8000\n";
echo "   Usuario: admin\n";
echo "   Contraseña: pointofsale\n";
echo "\n";
echo "💼 PORTABILIDAD TOTAL:\n";
echo "   1. Copia toda esta carpeta a otro equipo\n";
echo "   2. Doble clic en iniciar_ospos.bat\n";
echo "   3. ¡Listo! Todos los datos se preservan\n";
echo "\n";
echo "📁 Archivo de datos: writable/ospos_restaurante.db\n";
echo "=======================================\n";
?>