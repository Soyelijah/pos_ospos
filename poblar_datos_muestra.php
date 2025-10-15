<?php
/**
 * POBLAR BASE DE DATOS CON DATOS DE MUESTRA OSPOS
 * Agrega productos, proveedores, clientes y categorías típicas
 */
echo "🛒 POBLANDO DATOS DE MUESTRA OSPOS\n";
echo "=================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "📊 Insertando datos de muestra...\n\n";

    // ====================================================================================
    // PROVEEDORES
    // ====================================================================================
    echo "🏢 Insertando proveedores...\n";

    // Insertar personas para proveedores
    $suppliers_people = [
        ['Distribuidora Central SA', 'Juan', 'Pérez', 'proveedor1@email.com', '555-0101', 'Av. Central 123'],
        ['Alimentos Frescos SRL', 'María', 'González', 'proveedor2@email.com', '555-0102', 'Calle Comercio 456'],
        ['Bebidas Premium Ltda', 'Carlos', 'Rodríguez', 'proveedor3@email.com', '555-0103', 'Zona Industrial 789'],
        ['Productos Lácteos SA', 'Ana', 'Martínez', 'proveedor4@email.com', '555-0104', 'Barrio Norte 321']
    ];

    foreach ($suppliers_people as $index => $person) {
        $person_id = $index + 10; // Comenzar desde ID 10 para evitar conflictos

        $stmt = $db->prepare('INSERT OR REPLACE INTO people (person_id, first_name, last_name, email, phone_number, address_1, address_2, city, state, zip, country, comments) VALUES (?, ?, ?, ?, ?, ?, "", "Ciudad", "Estado", "12345", "Argentina", "")');
        $stmt->bindValue(1, $person_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $person[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $person[2], SQLITE3_TEXT);
        $stmt->bindValue(4, $person[3], SQLITE3_TEXT);
        $stmt->bindValue(5, $person[4], SQLITE3_TEXT);
        $stmt->bindValue(6, $person[5], SQLITE3_TEXT);
        $stmt->execute();

        // Insertar proveedor
        $stmt = $db->prepare('INSERT OR REPLACE INTO ospos_suppliers (person_id, company_name, agency_name, account_number, deleted, category, tax_id) VALUES (?, ?, ?, ?, 0, 0, ?)');
        $stmt->bindValue(1, $person_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $person[0], SQLITE3_TEXT);
        $stmt->bindValue(3, $person[0], SQLITE3_TEXT);
        $stmt->bindValue(4, 'PROV' . str_pad($person_id, 4, '0', STR_PAD_LEFT), SQLITE3_TEXT);
        $stmt->bindValue(5, '20-123456' . $person_id . '-7', SQLITE3_TEXT);
        $stmt->execute();

        echo "   ✅ {$person[0]}\n";
    }

    // ====================================================================================
    // PRODUCTOS
    // ====================================================================================
    echo "\n🍽️ Insertando productos de restaurante...\n";

    $items = [
        // Entradas
        ['Empanadas de Carne', 'Entradas', 10, 'EMP001', 'Empanadas caseras de carne', 80.00, 150.00, 10, 1, 0, 1, 0],
        ['Empanadas de Pollo', 'Entradas', 10, 'EMP002', 'Empanadas caseras de pollo', 75.00, 140.00, 10, 1, 0, 1, 0],
        ['Provoleta', 'Entradas', 10, 'PRO001', 'Provoleta a la parrilla', 120.00, 220.00, 5, 1, 0, 1, 0],
        ['Tabla de Fiambres', 'Entradas', 10, 'TAB001', 'Tabla de fiambres y quesos', 200.00, 380.00, 3, 1, 0, 1, 0],

        // Platos Principales
        ['Bife de Chorizo', 'Carnes', 10, 'CAR001', 'Bife de chorizo 400g', 450.00, 850.00, 8, 1, 0, 1, 0],
        ['Asado de Tira', 'Carnes', 10, 'CAR002', 'Asado de tira 350g', 380.00, 720.00, 10, 1, 0, 1, 0],
        ['Pollo Grillado', 'Carnes', 10, 'CAR003', 'Medio pollo grillado', 280.00, 520.00, 12, 1, 0, 1, 0],
        ['Milanesa Napolitana', 'Carnes', 10, 'CAR004', 'Milanesa con jamón y queso', 320.00, 600.00, 8, 1, 0, 1, 0],
        ['Pasta Bolognesa', 'Pastas', 11, 'PAS001', 'Pasta con salsa bolognesa', 180.00, 340.00, 15, 1, 0, 1, 0],
        ['Ravioles de Ricota', 'Pastas', 11, 'PAS002', 'Ravioles caseros con ricota', 200.00, 380.00, 12, 1, 0, 1, 0],

        // Bebidas
        ['Coca Cola 500ml', 'Bebidas', 12, 'BEB001', 'Coca Cola 500ml', 50.00, 120.00, 50, 1, 0, 1, 0],
        ['Agua Mineral 500ml', 'Bebidas', 12, 'BEB002', 'Agua mineral sin gas', 30.00, 80.00, 60, 1, 0, 1, 0],
        ['Cerveza Quilmes 500ml', 'Bebidas', 12, 'BEB003', 'Cerveza Quilmes botella', 80.00, 180.00, 40, 1, 0, 1, 0],
        ['Vino Tinto Copa', 'Bebidas', 12, 'BEB004', 'Copa de vino tinto de la casa', 120.00, 280.00, 30, 1, 0, 1, 0],

        // Postres
        ['Flan Casero', 'Postres', 11, 'POS001', 'Flan casero con dulce de leche', 90.00, 180.00, 15, 1, 0, 1, 0],
        ['Helado 2 Bochas', 'Postres', 11, 'POS002', 'Helado artesanal 2 bochas', 110.00, 220.00, 20, 1, 0, 1, 0],
        ['Torta Chocolate', 'Postres', 11, 'POS003', 'Porción de torta de chocolate', 130.00, 260.00, 8, 1, 0, 1, 0],

        // Cafetería
        ['Café Espresso', 'Cafeteria', 11, 'CAF001', 'Café espresso', 40.00, 90.00, 100, 1, 0, 1, 0],
        ['Café con Leche', 'Cafeteria', 11, 'CAF002', 'Café con leche', 50.00, 110.00, 100, 1, 0, 1, 0],
        ['Cortado', 'Cafeteria', 11, 'CAF003', 'Cortado', 45.00, 100.00, 100, 1, 0, 1, 0]
    ];

    foreach ($items as $item) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO ospos_items (name, category, supplier_id, item_number, description, cost_price, unit_price, reorder_level, receiving_quantity, pic_id, allow_alt_description, is_serialized, deleted, tax_category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        for ($i = 0; $i < count($item); $i++) {
            $stmt->bindValue($i + 1, $item[$i]);
        }

        $stmt->execute();
        $item_id = $db->lastInsertRowID();

        // Agregar cantidad en stock
        $stmt = $db->prepare('INSERT OR REPLACE INTO ospos_item_quantities (item_id, location_id, quantity) VALUES (?, 1, ?)');
        $stmt->bindValue(1, $item_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $item[7], SQLITE3_FLOAT); // reorder_level como cantidad inicial
        $stmt->execute();

        echo "   ✅ {$item[0]} (\${$item[6]})\n";
    }

    // ====================================================================================
    // CLIENTES
    // ====================================================================================
    echo "\n👥 Insertando clientes...\n";

    $customers_people = [
        ['Juan', 'García', 'juan.garcia@email.com', '11-1234-5678', 'Av. Libertador 1234'],
        ['María', 'López', 'maria.lopez@email.com', '11-2345-6789', 'Calle Corrientes 567'],
        ['Carlos', 'Sánchez', 'carlos.sanchez@email.com', '11-3456-7890', 'Av. Santa Fe 890'],
        ['Ana', 'Fernández', 'ana.fernandez@email.com', '11-4567-8901', 'Calle Florida 123'],
        ['Luis', 'Martín', 'luis.martin@email.com', '11-5678-9012', 'Av. Cabildo 456']
    ];

    foreach ($customers_people as $index => $person) {
        $person_id = $index + 20; // Comenzar desde ID 20

        // Insertar persona
        $stmt = $db->prepare('INSERT OR REPLACE INTO people (person_id, first_name, last_name, email, phone_number, address_1, address_2, city, state, zip, country, comments) VALUES (?, ?, ?, ?, ?, ?, "", "CABA", "Buenos Aires", "1000", "Argentina", "Cliente regular")');
        $stmt->bindValue(1, $person_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $person[0], SQLITE3_TEXT);
        $stmt->bindValue(3, $person[1], SQLITE3_TEXT);
        $stmt->bindValue(4, $person[2], SQLITE3_TEXT);
        $stmt->bindValue(5, $person[3], SQLITE3_TEXT);
        $stmt->bindValue(6, $person[4], SQLITE3_TEXT);
        $stmt->execute();

        // Insertar cliente
        $stmt = $db->prepare('INSERT OR REPLACE INTO customers (person_id, company_name, account_number, taxable, sales_tax_code_id, discount_percent, package_id, points, deleted, date_created) VALUES (?, "", ?, 1, 0, 0, 1, 0, 0, datetime("now"))');
        $stmt->bindValue(1, $person_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, 'CLI' . str_pad($person_id, 4, '0', STR_PAD_LEFT), SQLITE3_TEXT);
        $stmt->execute();

        echo "   ✅ {$person[0]} {$person[1]}\n";
    }

    // ====================================================================================
    // CATEGORÍAS DE GASTOS
    // ====================================================================================
    echo "\n💰 Insertando categorías de gastos...\n";

    $expense_categories = [
        ['Proveedores', 'Pagos a proveedores de mercadería'],
        ['Servicios', 'Electricidad, gas, agua, internet'],
        ['Personal', 'Sueldos y cargas sociales'],
        ['Mantenimiento', 'Reparaciones y mantenimiento del local'],
        ['Marketing', 'Publicidad y promociones'],
        ['Equipamiento', 'Compra de equipos y utensilios'],
        ['Impuestos', 'Impuestos municipales y nacionales']
    ];

    foreach ($expense_categories as $category) {
        $stmt = $db->prepare('INSERT OR REPLACE INTO ospos_expense_categories (category_name, category_description, deleted) VALUES (?, ?, 0)');
        $stmt->bindValue(1, $category[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $category[1], SQLITE3_TEXT);
        $stmt->execute();

        echo "   ✅ {$category[0]}\n";
    }

    // ====================================================================================
    // VERIFICACIÓN FINAL
    // ====================================================================================
    echo "\n🔍 VERIFICACIÓN DE DATOS INSERTADOS:\n";
    echo "===================================\n";

    $verification = [
        ['ospos_suppliers', 'Proveedores'],
        ['ospos_items', 'Productos'],
        ['customers', 'Clientes'],
        ['ospos_expense_categories', 'Categorías de gastos'],
        ['ospos_item_quantities', 'Stock de productos'],
        ['ospos_stock_locations', 'Ubicaciones de stock'],
        ['ospos_dinner_tables', 'Mesas'],
        ['ospos_customers_packages', 'Paquetes de clientes']
    ];

    foreach ($verification as $check) {
        $count = $db->querySingle("SELECT COUNT(*) FROM {$check[0]}");
        echo "✅ {$check[1]}: $count registros\n";
    }

    $db->close();

    echo "\n🎉 ¡DATOS DE MUESTRA INSERTADOS EXITOSAMENTE!\n";
    echo "===========================================\n";
    echo "🏪 El sistema ahora tiene datos típicos de un restaurante:\n";
    echo "   • 4 Proveedores configurados\n";
    echo "   • 19 Productos del menú con precios\n";
    echo "   • 5 Clientes registrados\n";
    echo "   • 7 Categorías de gastos\n";
    echo "   • Stock inicial configurado\n";
    echo "   • Mesas de delivery y take away\n";
    echo "\n💡 OSPOS está listo para operar como un restaurante real\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>