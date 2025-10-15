<?php
/**
 * VERIFICACIÓN COMPLETA DEL SISTEMA OSPOS
 * Verifica que todo esté configurado como el OSPOS original
 */
echo "🔍 VERIFICACIÓN COMPLETA DE LA BASE DE DATOS OSPOS\n";
echo "================================================\n\n";

try {
    $db = new SQLite3('writable/ospos_restaurante.db');

    // Verificar tablas existentes
    $result = $db->query('SELECT name FROM sqlite_master WHERE type="table" ORDER BY name');
    $tables = [];
    while ($row = $result->fetchArray()) {
        if ($row['name'] !== 'sqlite_sequence') {
            $tables[] = $row['name'];
        }
    }

    echo "📋 TABLAS EXISTENTES (" . count($tables) . " tablas):\n";
    foreach ($tables as $table) {
        $count = $db->querySingle("SELECT COUNT(*) FROM $table");
        echo "   ✅ $table: $count registros\n";
    }

    // Verificar estructura crítica
    echo "\n🔑 VERIFICACIONES CRÍTICAS:\n";

    // Verificar módulos
    $modules = $db->querySingle('SELECT COUNT(*) FROM modules');
    echo "   📦 Módulos: $modules\n";

    // Verificar permisos
    $permissions = $db->querySingle('SELECT COUNT(*) FROM permissions');
    echo "   🔐 Permisos: $permissions\n";

    // Verificar grants del admin
    $grants = $db->querySingle('SELECT COUNT(*) FROM grants WHERE person_id = 1');
    echo "   ✅ Grants admin: $grants\n";

    // Verificar productos
    $items = $db->querySingle('SELECT COUNT(*) FROM items');
    echo "   🛒 Productos: $items\n";

    // Verificar configuraciones
    $configs = $db->querySingle('SELECT COUNT(*) FROM app_config');
    echo "   ⚙️ Configuraciones: $configs\n";

    // Verificar tablas críticas del OSPOS original
    $critical_tables = [
        'people', 'employees', 'customers', 'suppliers',
        'items', 'categories', 'item_quantities', 'sales',
        'sales_items', 'receivings', 'receiving_items',
        'modules', 'permissions', 'grants', 'app_config',
        'locations', 'stock_locations', 'taxes', 'sessions'
    ];

    echo "\n📊 TABLAS CRÍTICAS DEL OSPOS ORIGINAL:\n";
    $missing_tables = [];
    foreach ($critical_tables as $table) {
        if (in_array($table, $tables)) {
            $count = $db->querySingle("SELECT COUNT(*) FROM $table");
            echo "   ✅ $table: $count registros\n";
        } else {
            echo "   ❌ $table: FALTANTE\n";
            $missing_tables[] = $table;
        }
    }

    echo "\n💾 ESTADO DE LA BASE DE DATOS:\n";
    $size = filesize('writable/ospos_restaurante.db');
    echo "   📊 Tamaño: " . round($size/1024, 2) . " KB\n";

    if (empty($missing_tables)) {
        echo "\n🎉 ¡BASE DE DATOS COMPLETA COMO EL OSPOS ORIGINAL!\n";
    } else {
        echo "\n⚠️ TABLAS FALTANTES: " . implode(', ', $missing_tables) . "\n";
    }

    $db->close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}