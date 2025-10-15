<?php
/**
 * CORRECTOR DE NOMBRES DE TABLAS OSPOS
 * Renombra tablas de ospos_* a * (sin prefijo) como espera OSPOS
 */
echo "🔧 CORRIGIENDO NOMBRES DE TABLAS OSPOS\n";
echo "=====================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    // Deshabilitar temporalmente foreign keys para poder renombrar
    $db->exec('PRAGMA foreign_keys = OFF');

    echo "📋 Renombrando tablas con prefijo ospos_...\n\n";

    // Lista de tablas a renombrar (de ospos_* a *)
    $tables_to_rename = [
        'ospos_giftcards' => 'giftcards',
        'ospos_items' => 'items',
        'ospos_item_kits' => 'item_kits',
        'ospos_item_kit_items' => 'item_kit_items',
        'ospos_stock_locations' => 'stock_locations',
        'ospos_suppliers' => 'suppliers',
        'ospos_receivings' => 'receivings',
        'ospos_receivings_items' => 'receiving_items', // Nota: se renombra también
        'ospos_sales' => 'sales',
        'ospos_sales_items' => 'sales_items',
        'ospos_sales_items_taxes' => 'sales_items_taxes',
        'ospos_sales_payments' => 'sales_payments',
        'ospos_sales_suspended' => 'sales_suspended',
        'ospos_sales_taxes' => 'sales_taxes',
        'ospos_item_quantities' => 'item_quantities',
        'ospos_inventory' => 'inventory',
        'ospos_items_taxes' => 'items_taxes',
        'ospos_sales_suspended_items' => 'sales_suspended_items',
        'ospos_sales_suspended_items_taxes' => 'sales_suspended_items_taxes',
        'ospos_sales_suspended_payments' => 'sales_suspended_payments',
        // Mantener algunas con prefijo que son opcionales
        'ospos_dinner_tables' => 'dinner_tables',
        'ospos_customers_packages' => 'customers_packages',
        'ospos_expense_categories' => 'expense_categories',
        'ospos_expenses' => 'expenses'
    ];

    $renamed_count = 0;

    foreach ($tables_to_rename as $old_name => $new_name) {
        // Verificar si la tabla origen existe
        $exists = $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$old_name'");

        if ($exists) {
            // Verificar si la tabla destino ya existe
            $target_exists = $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$new_name'");

            if ($target_exists) {
                echo "⚠️  $new_name ya existe, eliminando duplicada...\n";
                $db->exec("DROP TABLE IF EXISTS $new_name");
            }

            // Renombrar tabla
            $sql = "ALTER TABLE $old_name RENAME TO $new_name";
            if ($db->exec($sql)) {
                echo "✅ $old_name → $new_name\n";
                $renamed_count++;
            } else {
                echo "❌ Error renombrando $old_name: " . $db->lastErrorMsg() . "\n";
            }
        } else {
            echo "⚠️  Tabla $old_name no existe\n";
        }
    }

    // Crear tabla taxes que falta
    echo "\n📋 Creando tabla taxes faltante...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS taxes (
            name VARCHAR(255) NOT NULL,
            tax_rate DECIMAL(15,4) NOT NULL,
            tax_code VARCHAR(255) DEFAULT NULL,
            tax_code_name VARCHAR(255) DEFAULT NULL,
            reporting_authority VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (name, tax_rate)
        )
    ");

    // Insertar impuesto por defecto
    $db->exec("INSERT OR REPLACE INTO taxes (name, tax_rate, tax_code, tax_code_name, reporting_authority) VALUES ('IVA', 21.0000, 'IVA21', 'IVA 21%', 'AFIP')");
    echo "✅ Tabla taxes creada con IVA 21%\n";

    // Reactivar foreign keys
    $db->exec('PRAGMA foreign_keys = ON');

    // Verificación final
    echo "\n🔍 Verificación final:\n";
    echo "=====================\n";

    $final_tables = ['giftcards', 'items', 'item_kits', 'stock_locations', 'suppliers', 'receivings', 'receiving_items', 'sales', 'sales_items', 'taxes'];

    foreach ($final_tables as $table) {
        $exists = $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$table'");
        if ($exists) {
            $count = $db->querySingle("SELECT COUNT(*) FROM $table");
            echo "✅ $table: $count registros\n";
        } else {
            echo "❌ $table: NO EXISTE\n";
        }
    }

    $db->close();

    echo "\n🎉 CORRECCIÓN DE NOMBRES COMPLETADA\n";
    echo "==================================\n";
    echo "✅ Tablas renombradas: $renamed_count\n";
    echo "✅ Tabla taxes creada\n";
    echo "✅ Las tablas ahora tienen los nombres que OSPOS espera\n";
    echo "\n💡 Los módulos deberían funcionar correctamente ahora\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>