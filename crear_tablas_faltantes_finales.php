<?php
/**
 * CREAR TABLAS FALTANTES FINALES OSPOS
 * Crea las últimas 2 tablas que faltan: categories y locations
 */
echo "🔧 CREANDO TABLAS FALTANTES FINALES\n";
echo "===================================\n\n";

try {
    $db = new SQLite3('writable/ospos_restaurante.db');

    // Crear tabla categories
    echo "📦 Creando tabla categories...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS categories (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_name VARCHAR(255) NOT NULL,
        parent_id INTEGER DEFAULT NULL,
        sort INTEGER DEFAULT 0,
        color VARCHAR(7) DEFAULT '#2E8B57',
        image VARCHAR(255) DEFAULT NULL,
        deleted INTEGER DEFAULT 0
    )";
    $db->exec($sql);
    echo "   ✅ Tabla categories creada\n";

    // Insertar categorías de ejemplo
    $categories = [
        [1, 'Carnes', 0, 10, '#FF6B6B'],
        [2, 'Pastas', 0, 20, '#4ECDC4'],
        [3, 'Entradas', 0, 30, '#45B7D1'],
        [4, 'Bebidas', 0, 40, '#96CEB4'],
        [5, 'Postres', 0, 50, '#FFEAA7'],
        [6, 'Cafetería', 0, 60, '#DDA0DD']
    ];

    foreach ($categories as $cat) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO categories (category_id, category_name, parent_id, sort, color) VALUES (?, ?, ?, ?, ?)');
        $stmt->bindValue(1, $cat[0], SQLITE3_INTEGER);
        $stmt->bindValue(2, $cat[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $cat[2], SQLITE3_INTEGER);
        $stmt->bindValue(4, $cat[3], SQLITE3_INTEGER);
        $stmt->bindValue(5, $cat[4], SQLITE3_TEXT);
        $stmt->execute();
        echo "   ✅ Categoría: {$cat[1]}\n";
    }

    // Crear tabla locations
    echo "\n📍 Creando tabla locations...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS locations (
        location_id INTEGER PRIMARY KEY AUTOINCREMENT,
        location_name VARCHAR(255) NOT NULL,
        deleted INTEGER DEFAULT 0
    )";
    $db->exec($sql);
    echo "   ✅ Tabla locations creada\n";

    // Insertar ubicaciones de ejemplo
    $locations = [
        [1, 'Depósito Principal'],
        [2, 'Cocina'],
        [3, 'Bar']
    ];

    foreach ($locations as $loc) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO locations (location_id, location_name) VALUES (?, ?)');
        $stmt->bindValue(1, $loc[0], SQLITE3_INTEGER);
        $stmt->bindValue(2, $loc[1], SQLITE3_TEXT);
        $stmt->execute();
        echo "   ✅ Ubicación: {$loc[1]}\n";
    }

    // Actualizar items con categorías
    echo "\n🔗 Asignando categorías a productos...\n";
    $item_categories = [
        // Carnes
        [1, 1], [2, 1], [3, 1], [4, 1], // Bife, Asado, Pollo, Milanesa
        // Pastas
        [5, 2], [6, 2], // Bolognesa, Ravioles
        // Entradas
        [7, 3], [8, 3], [9, 3], [10, 3], // Empanadas, Provoleta, Tabla, etc
        // Bebidas
        [11, 4], [12, 4], [13, 4], [14, 4], // Coca, Agua, Cerveza, Vino
        // Postres
        [15, 5], [16, 5], [17, 5], // Flan, Helado, Torta
        // Cafetería
        [18, 6], [19, 6], [20, 6] // Espresso, Café con Leche, Cortado
    ];

    foreach ($item_categories as $ic) {
        $stmt = $db->prepare('UPDATE items SET category_id = ? WHERE item_id = ?');
        $stmt->bindValue(1, $ic[1], SQLITE3_INTEGER);
        $stmt->bindValue(2, $ic[0], SQLITE3_INTEGER);
        $stmt->execute();
    }
    echo "   ✅ Productos categorizados correctamente\n";

    echo "\n📊 RESUMEN FINAL:\n";
    $tables = $db->querySingle('SELECT COUNT(*) FROM sqlite_master WHERE type="table" AND name NOT LIKE "sqlite%"');
    $categories_count = $db->querySingle('SELECT COUNT(*) FROM categories');
    $locations_count = $db->querySingle('SELECT COUNT(*) FROM locations');

    echo "   📋 Total de tablas: $tables\n";
    echo "   📦 Categorías: $categories_count\n";
    echo "   📍 Ubicaciones: $locations_count\n";
    echo "\n🎉 ¡BASE DE DATOS OSPOS 100% COMPLETA!\n";

    $db->close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}