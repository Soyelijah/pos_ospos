<?php
/**
 * CREADOR DE BASE DE DATOS FINAL OSPOS
 * Crea la base de datos con los nombres exactos que esperan los modelos
 */

echo "🚀 CREANDO BASE DE DATOS FINAL OSPOS\n";
echo "==================================\n\n";

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

    // Crear tablas con nombres exactos que esperan los modelos OSPOS
    echo "🔧 Creando tablas con nombres correctos...\n";

    // Tabla app_config (sin prefijo ospos_)
    $sql_app_config = "
    CREATE TABLE app_config (
        key VARCHAR(50) NOT NULL PRIMARY KEY,
        value VARCHAR(500) NOT NULL
    )";
    $db->exec($sql_app_config);
    echo "✅ Tabla app_config creada\n";

    // Tabla people (sin prefijo ospos_)
    $sql_people = "
    CREATE TABLE people (
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
    echo "✅ Tabla people creada\n";

    // Tabla employees (sin prefijo ospos_)
    $sql_employees = "
    CREATE TABLE employees (
        username VARCHAR(255) NOT NULL PRIMARY KEY,
        password VARCHAR(255) NOT NULL,
        person_id INTEGER NOT NULL,
        deleted INTEGER NOT NULL DEFAULT 0,
        hash_version INTEGER NOT NULL DEFAULT 2,
        language VARCHAR(255) DEFAULT 'english',
        language_code VARCHAR(255) DEFAULT 'en',
        FOREIGN KEY (person_id) REFERENCES people(person_id)
    )";
    $db->exec($sql_employees);
    echo "✅ Tabla employees creada\n";

    // Tabla grants (sin prefijo ospos_)
    $sql_grants = "
    CREATE TABLE grants (
        permission_id VARCHAR(255) NOT NULL,
        person_id INTEGER NOT NULL,
        module_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (permission_id, person_id),
        FOREIGN KEY (person_id) REFERENCES people(person_id)
    )";
    $db->exec($sql_grants);
    echo "✅ Tabla grants creada\n";

    // Tabla sessions (sin prefijo ospos_)
    $sql_sessions = "
    CREATE TABLE sessions (
        id VARCHAR(128) NOT NULL PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        timestamp INTEGER NOT NULL DEFAULT 0,
        data BLOB NOT NULL
    )";
    $db->exec($sql_sessions);
    echo "✅ Tabla sessions creada\n";

    // Tabla customers (necesaria para OSPOS)
    $sql_customers = "
    CREATE TABLE customers (
        person_id INTEGER PRIMARY KEY,
        company_name VARCHAR(255),
        account_number VARCHAR(255),
        taxable INTEGER DEFAULT 1,
        sales_tax_code_id INTEGER,
        discount_percent DECIMAL(15,2) DEFAULT 0,
        package_id INTEGER,
        points INTEGER DEFAULT 0,
        deleted INTEGER DEFAULT 0,
        date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (person_id) REFERENCES people(person_id)
    )";
    $db->exec($sql_customers);
    echo "✅ Tabla customers creada\n";

    // Insertar configuración básica en app_config
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
        'tax_included' => '0',
        'default_tax_category' => '1',
        'invoice_enable' => '1',
        'barcode_width' => '250',
        'barcode_height' => '50',
        'barcode_type' => 'Code128',
        'receipt_show_taxes' => '1',
        'receipt_show_total_discount' => '1'
    ];

    foreach ($config_data as $key => $value) {
        $stmt = $db->prepare("INSERT INTO app_config (key, value) VALUES (?, ?)");
        $stmt->bindValue(1, $key);
        $stmt->bindValue(2, $value);
        $stmt->execute();
    }
    echo "✅ Configuración básica insertada\n";

    // Crear usuario administrador
    echo "\n👤 Creando usuario administrador...\n";

    // Insertar persona admin
    $stmt = $db->prepare("
        INSERT INTO people
        (first_name, last_name, email, phone_number, address_1, city, comments)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bindValue(1, 'Administrator');
    $stmt->bindValue(2, 'System');
    $stmt->bindValue(3, 'admin@restaurant.local');
    $stmt->bindValue(4, '555-123-4567');
    $stmt->bindValue(5, 'Calle Principal 123');
    $stmt->bindValue(6, 'Ciudad');
    $stmt->bindValue(7, 'Administrador del sistema OSPOS');
    $stmt->execute();

    $admin_person_id = $db->lastInsertRowID();
    echo "✅ Persona admin creada con ID: $admin_person_id\n";

    // Insertar empleado admin
    $admin_password = password_hash('pointofsale', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO employees (username, password, person_id, deleted, hash_version, language, language_code)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bindValue(1, 'admin');
    $stmt->bindValue(2, $admin_password);
    $stmt->bindValue(3, $admin_person_id);
    $stmt->bindValue(4, 0);
    $stmt->bindValue(5, 2);
    $stmt->bindValue(6, 'spanish');
    $stmt->bindValue(7, 'es');
    $stmt->execute();
    echo "✅ Usuario admin creado\n";

    // Dar todos los permisos al admin
    $permissions = ['config', 'employees', 'customers', 'suppliers', 'items', 'sales', 'receivings', 'reports', 'giftcards', 'item_kits', 'taxes'];
    foreach ($permissions as $permission) {
        $stmt = $db->prepare("INSERT INTO grants (permission_id, person_id, module_id) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $permission);
        $stmt->bindValue(2, $admin_person_id);
        $stmt->bindValue(3, $permission);
        $stmt->execute();
    }
    echo "✅ Permisos de administrador configurados\n";

    $db->close();

    echo "\n🎉 ¡BASE DE DATOS OSPOS FINAL CREADA!\n";
    echo "=====================================\n";
    echo "📁 Archivo: $dbPath\n";
    echo "📊 Tamaño: " . filesize($dbPath) . " bytes\n";
    echo "👤 Usuario: admin\n";
    echo "🔑 Contraseña: pointofsale\n";
    echo "\n✅ Tablas creadas con nombres correctos:\n";
    echo "   - app_config (sin prefijo)\n";
    echo "   - people (sin prefijo)\n";
    echo "   - employees (sin prefijo)\n";
    echo "   - customers (sin prefijo)\n";
    echo "   - grants (sin prefijo)\n";
    echo "   - sessions (sin prefijo)\n";
    echo "\n🚀 OSPOS debería funcionar correctamente ahora!\n\n";

} catch (Exception $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    exit(1);
}
?>