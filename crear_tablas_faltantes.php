<?php
/**
 * CREADOR DE TABLAS FALTANTES OSPOS
 * Crea las tablas modules y permissions que faltan en SQLite
 */
echo "🔧 CREANDO TABLAS FALTANTES OSPOS\n";
echo "================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "📋 Creando tabla modules...\n";

    // Crear tabla modules (sin ospos_ prefix para que coincida con el Model)
    $sql_modules = "CREATE TABLE modules (
        name_lang_key VARCHAR(255) NOT NULL,
        desc_lang_key VARCHAR(255) NOT NULL,
        sort INTEGER NOT NULL,
        module_id VARCHAR(255) NOT NULL PRIMARY KEY,
        UNIQUE(desc_lang_key),
        UNIQUE(name_lang_key)
    )";

    if ($db->exec($sql_modules)) {
        echo "✅ Tabla modules creada\n";
    } else {
        echo "❌ Error creando tabla modules: " . $db->lastErrorMsg() . "\n";
    }

    echo "📋 Creando tabla permissions...\n";

    // Crear tabla permissions (sin ospos_ prefix)
    $sql_permissions = "CREATE TABLE permissions (
        permission_id VARCHAR(255) NOT NULL PRIMARY KEY,
        module_id VARCHAR(255) NOT NULL,
        location_id INTEGER DEFAULT NULL
    )";

    if ($db->exec($sql_permissions)) {
        echo "✅ Tabla permissions creada\n";
    } else {
        echo "❌ Error creando tabla permissions: " . $db->lastErrorMsg() . "\n";
    }

    echo "\n📊 Insertando datos en modules...\n";

    // Insertar datos de modules
    $modules_data = [
        ['module_config', 'module_config_desc', 110, 'config'],
        ['module_customers', 'module_customers_desc', 10, 'customers'],
        ['module_employees', 'module_employees_desc', 80, 'employees'],
        ['module_giftcards', 'module_giftcards_desc', 90, 'giftcards'],
        ['module_items', 'module_items_desc', 20, 'items'],
        ['module_item_kits', 'module_item_kits_desc', 30, 'item_kits'],
        ['module_messages', 'module_messages_desc', 100, 'messages'],
        ['module_receivings', 'module_receivings_desc', 60, 'receivings'],
        ['module_reports', 'module_reports_desc', 50, 'reports'],
        ['module_sales', 'module_sales_desc', 70, 'sales'],
        ['module_suppliers', 'module_suppliers_desc', 40, 'suppliers']
    ];

    $stmt = $db->prepare('INSERT INTO modules (name_lang_key, desc_lang_key, sort, module_id) VALUES (?, ?, ?, ?)');
    $modules_inserted = 0;

    foreach ($modules_data as $module) {
        $stmt->bindValue(1, $module[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $module[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $module[2], SQLITE3_INTEGER);
        $stmt->bindValue(4, $module[3], SQLITE3_TEXT);

        if ($stmt->execute()) {
            $modules_inserted++;
        } else {
            echo "❌ Error insertando módulo {$module[3]}: " . $db->lastErrorMsg() . "\n";
        }
    }

    echo "✅ $modules_inserted módulos insertados\n";

    echo "\n📊 Insertando datos en permissions...\n";

    // Insertar permisos básicos
    $permissions_data = [
        ['reports_customers', 'reports'],
        ['reports_receivings', 'reports'],
        ['reports_items', 'reports'],
        ['reports_employees', 'reports'],
        ['reports_suppliers', 'reports'],
        ['reports_sales', 'reports'],
        ['reports_discounts', 'reports'],
        ['reports_taxes', 'reports'],
        ['reports_inventory', 'reports'],
        ['reports_categories', 'reports'],
        ['reports_payments', 'reports'],
        ['customers', 'customers'],
        ['employees', 'employees'],
        ['giftcards', 'giftcards'],
        ['items', 'items'],
        ['item_kits', 'item_kits'],
        ['messages', 'messages'],
        ['receivings', 'receivings'],
        ['reports', 'reports'],
        ['sales', 'sales'],
        ['config', 'config'],
        ['suppliers', 'suppliers']
    ];

    $stmt = $db->prepare('INSERT INTO permissions (permission_id, module_id) VALUES (?, ?)');
    $permissions_inserted = 0;

    foreach ($permissions_data as $permission) {
        $stmt->bindValue(1, $permission[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $permission[1], SQLITE3_TEXT);

        if ($stmt->execute()) {
            $permissions_inserted++;
        } else {
            echo "❌ Error insertando permiso {$permission[0]}: " . $db->lastErrorMsg() . "\n";
        }
    }

    // Insertar permisos con location_id
    $location_permissions = [
        ['items_stock', 'items', 1],
        ['sales_stock', 'sales', 1],
        ['receivings_stock', 'receivings', 1]
    ];

    $stmt = $db->prepare('INSERT INTO permissions (permission_id, module_id, location_id) VALUES (?, ?, ?)');

    foreach ($location_permissions as $permission) {
        $stmt->bindValue(1, $permission[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $permission[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $permission[2], SQLITE3_INTEGER);

        if ($stmt->execute()) {
            $permissions_inserted++;
        } else {
            echo "❌ Error insertando permiso con location {$permission[0]}: " . $db->lastErrorMsg() . "\n";
        }
    }

    echo "✅ $permissions_inserted permisos insertados\n";

    // Verificar que la tabla grants existe y actualizar permisos del admin
    echo "\n📊 Actualizando permisos del usuario admin...\n";

    // Primero limpiar permisos existentes del admin
    $db->exec('DELETE FROM grants WHERE person_id = 1');

    // Dar todos los permisos al admin (person_id = 1)
    $admin_permissions = [
        'reports_customers', 'reports_receivings', 'reports_items', 'reports_inventory',
        'reports_employees', 'reports_suppliers', 'reports_sales', 'reports_discounts',
        'reports_taxes', 'reports_categories', 'reports_payments',
        'customers', 'employees', 'giftcards', 'items', 'item_kits', 'messages',
        'receivings', 'reports', 'sales', 'config', 'suppliers',
        'items_stock', 'sales_stock', 'receivings_stock'
    ];

    $stmt = $db->prepare('INSERT INTO grants (permission_id, person_id) VALUES (?, 1)');
    $grants_inserted = 0;

    foreach ($admin_permissions as $permission) {
        $stmt->bindValue(1, $permission, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $grants_inserted++;
        } else {
            echo "❌ Error asignando permiso $permission al admin: " . $db->lastErrorMsg() . "\n";
        }
    }

    echo "✅ $grants_inserted permisos asignados al usuario admin\n";

    $db->close();

    echo "\n🎉 ¡TABLAS FALTANTES CREADAS EXITOSAMENTE!\n";
    echo "========================================\n";
    echo "✅ Tabla modules: creada con 11 módulos\n";
    echo "✅ Tabla permissions: creada con $permissions_inserted permisos\n";
    echo "✅ Usuario admin: $grants_inserted permisos asignados\n";
    echo "\n💡 OSPOS debería funcionar correctamente ahora\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>