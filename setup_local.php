<?php
/**
 * Script de configuración local para OSPOS
 * Este script configurará una instalación local con datos demo
 */

echo "=== OSPOS - Configuración Local ===\n\n";

// Configuración local temporal
$localConfig = [
    'hostname' => 'localhost',
    'database' => 'ospos_local',
    'username' => 'root',
    'password' => '', // Cambiar si tienes contraseña para root
    'port' => 3306
];

echo "Configurando OSPOS para desarrollo local...\n";
echo "Base de datos: {$localConfig['database']}\n";
echo "Usuario: {$localConfig['username']}\n\n";

// Actualizar .env para configuración local
$envContent = "
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = development
CI_DEBUG = true

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.appTimezone = 'America/Mexico_City'

#--------------------------------------------------------------------
# DATABASE - Configuración Local
#--------------------------------------------------------------------

database.default.hostname = '{$localConfig['hostname']}'
database.default.database = '{$localConfig['database']}'
database.default.username = '{$localConfig['username']}'
database.default.password = '{$localConfig['password']}'
database.default.DBDriver = 'MySQLi'
database.default.DBPrefix = 'ospos_'
database.default.port = {$localConfig['port']}

database.development.hostname = '{$localConfig['hostname']}'
database.development.database = '{$localConfig['database']}'
database.development.username = '{$localConfig['username']}'
database.development.password = '{$localConfig['password']}'
database.development.DBDriver = 'MySQLi'
database.development.DBPrefix = 'ospos_'
database.development.port = {$localConfig['port']}

#--------------------------------------------------------------------
# EMAIL
#--------------------------------------------------------------------

email.SMTPHost = ''
email.SMTPUser = ''
email.SMTPPass = ''
email.SMTPPort =
email.SMTPTimeout = 5
email.SMTPCrypto = 'tls'

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption.key = '" . bin2hex(random_bytes(32)) . "'

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

logger.threshold = 4
app.db_log_enabled = true
app.db_log_only_long = false
";

file_put_contents('.env', $envContent);
echo "✅ Archivo .env actualizado para desarrollo local\n";

// Crear script SQL para configurar la base de datos
$sqlScript = "
-- Script de configuración para OSPOS Local
CREATE DATABASE IF NOT EXISTS {$localConfig['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE {$localConfig['database']};

-- Fuente: app/Database/database.sql (estructura base)
";

file_put_contents('setup_database.sql', $sqlScript);
echo "✅ Script SQL creado: setup_database.sql\n";

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Instalar MySQL/MariaDB localmente si no lo tienes\n";
echo "2. Ejecutar: mysql -u root -p < setup_database.sql\n";
echo "3. Importar el archivo database.sql: mysql -u root -p {$localConfig['database']} < app/Database/database.sql\n";
echo "4. Ejecutar: php -S localhost:8000 -t public\n";
echo "5. Abrir http://localhost:8000 en tu navegador\n";
echo "\nCredenciales por defecto:\n";
echo "Usuario: admin\n";
echo "Contraseña: pointofsale\n";

echo "\n=== Configuración completada ===\n";
?>