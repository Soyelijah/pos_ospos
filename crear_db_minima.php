<?php
/**
 * CREADOR DE BASE DE DATOS MÍNIMA OSPOS
 * Crea solo las tablas esenciales para que OSPOS pueda iniciarse
 */

echo "🚀 CREANDO BASE DE DATOS MÍNIMA OSPOS\n";
echo "====================================\n\n";

$dbPath = 'writable/ospos_restaurante.db';

try {
    // Eliminar base de datos existente
    if (file_exists($dbPath)) {
        unlink($dbPath);
        echo "🗑️  Base de datos anterior eliminada\n";
    }

    // Crear nueva conexión SQLite
    $db = new SQLite3($dbPath);
    echo "✅ Conexión SQLite creada: $dbPath\n";

    // Crear tablas esenciales para OSPOS
    echo "🔧 Creando tablas esenciales...\n";

    // Tabla de configuración de la aplicación
    $sql_app_config = "
    CREATE TABLE ospos_app_config (
        key VARCHAR(50) NOT NULL PRIMARY KEY,
        value VARCHAR(500) NOT NULL
    )";
    $db->exec($sql_app_config);
    echo "✅ Tabla ospos_app_config creada\n";

    // Tabla de personas (base para empleados/clientes)
    $sql_people = "
    CREATE TABLE ospos_people (
        person_id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone_number VARCHAR(255),
        address_1 VARCHAR(255),
        address_2 VARCHAR(255),
        city VARCHAR(255),
        state VARCHAR(255),
        zip VARCHAR(255),
        country VARCHAR(255),
        comments TEXT
    )";
    $db->exec($sql_people);
    echo "✅ Tabla ospos_people creada\n";

    // Tabla de empleados
    $sql_employees = "
    CREATE TABLE ospos_employees (
        username VARCHAR(255) NOT NULL PRIMARY KEY,
        password VARCHAR(255) NOT NULL,
        person_id INTEGER NOT NULL,
        deleted INTEGER NOT NULL DEFAULT 0,
        hash_version INTEGER NOT NULL DEFAULT 2,
        FOREIGN KEY (person_id) REFERENCES ospos_people(person_id)
    )";
    $db->exec($sql_employees);
    echo "✅ Tabla ospos_employees creada\n";

    // Tabla de permisos/grants
    $sql_grants = "
    CREATE TABLE ospos_grants (
        permission_id VARCHAR(255) NOT NULL,
        person_id INTEGER NOT NULL,
        module_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (permission_id, person_id),
        FOREIGN KEY (person_id) REFERENCES ospos_people(person_id)
    )";
    $db->exec($sql_grants);
    echo "✅ Tabla ospos_grants creada\n";

    // Tabla de sesiones
    $sql_sessions = "
    CREATE TABLE ospos_sessions (
        id VARCHAR(128) NOT NULL PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        timestamp INTEGER NOT NULL DEFAULT 0,
        data BLOB NOT NULL
    )";
    $db->exec($sql_sessions);
    echo "✅ Tabla ospos_sessions creada\n";

    // Insertar configuración básica
    echo "\n📊 Insertando configuración básica...\n";

    $config_data = [
        'company' => 'Restaurant OSPOS',
        'address' => 'Calle Principal 123',
        'phone' => '555-123-4567',
        'email' => 'admin@restaurant.local',
        'timezone' => 'America/Mexico_City',
        'default_tax_rate' => '16',
        'language' => 'spanish',
        'currency_symbol' => '$',
        'thousands_separator' => ',',
        'decimal_point' => '.',
        'tax_included' => '0'
    ];

    foreach ($config_data as $key => $value) {
        $stmt = $db->prepare("INSERT INTO ospos_app_config (key, value) VALUES (?, ?)");
        $stmt->bindValue(1, $key);
        $stmt->bindValue(2, $value);
        $stmt->execute();
    }
    echo "✅ Configuración básica insertada\n";

    // Crear usuario administrador
    echo "\n👤 Creando usuario administrador...\n";

    // Insertar persona admin
    $stmt = $db->prepare("
        INSERT INTO ospos_people
        (first_name, last_name, email, phone_number, address_1, city, comments)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bindValue(1, 'Administrator');
    $stmt->bindValue(2, 'System');
    $stmt->bindValue(3, 'admin@restaurant.local');
    $stmt->bindValue(4, '555-123-4567');
    $stmt->bindValue(5, 'Calle Principal 123');
    $stmt->bindValue(6, 'Ciudad');
    $stmt->bindValue(7, 'Administrador del sistema');
    $stmt->execute();

    $admin_person_id = $db->lastInsertRowID();
    echo "✅ Persona admin creada con ID: $admin_person_id\n";

    // Insertar empleado admin
    $admin_password = password_hash('pointofsale', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO ospos_employees (username, password, person_id, deleted, hash_version)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bindValue(1, 'admin');
    $stmt->bindValue(2, $admin_password);
    $stmt->bindValue(3, $admin_person_id);
    $stmt->bindValue(4, 0);
    $stmt->bindValue(5, 2);
    $stmt->execute();
    echo "✅ Usuario admin creado\n";

    // Dar todos los permisos al admin
    $permissions = ['config', 'employees', 'customers', 'suppliers', 'items', 'sales', 'receivings', 'reports'];
    foreach ($permissions as $permission) {
        $stmt = $db->prepare("INSERT INTO ospos_grants (permission_id, person_id, module_id) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $permission);
        $stmt->bindValue(2, $admin_person_id);
        $stmt->bindValue(3, $permission);
        $stmt->execute();
    }
    echo "✅ Permisos de administrador configurados\n";

    $db->close();

    echo "\n🎉 ¡BASE DE DATOS MÍNIMA CREADA EXITOSAMENTE!\n";
    echo "=============================================\n";
    echo "📁 Archivo: $dbPath\n";
    echo "📊 Tamaño: " . filesize($dbPath) . " bytes\n";
    echo "👤 Usuario: admin\n";
    echo "🔑 Contraseña: pointofsale\n";
    echo "\n🚀 Para iniciar OSPOS:\n";
    echo "   php -S localhost:8000 -t public\n";
    echo "   URL: http://localhost:8000\n\n";

    echo "💡 Nota: Esta es una base de datos mínima.\n";
    echo "   OSPOS creará automáticamente las tablas faltantes\n";
    echo "   cuando las necesite.\n\n";

} catch (Exception $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    exit(1);
}
?>