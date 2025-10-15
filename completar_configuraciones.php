<?php
/**
 * COMPLETA CONFIGURACIONES FALTANTES EN APP_CONFIG
 * Agrega todas las configuraciones necesarias para OSPOS
 */
echo "⚙️ COMPLETANDO CONFIGURACIONES OSPOS\n";
echo "===================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "📋 Agregando configuraciones faltantes...\n";

    // Configuraciones esenciales que faltan
    $missing_configs = [
        // Formatos de fecha y hora
        ['dateformat', 'm/d/Y', 'Formato de fecha'],
        ['timeformat', 'H:i:s', 'Formato de hora'],

        // Configuraciones de notificaciones
        ['notify_horizontal_position', 'right', 'Posición horizontal notificaciones'],
        ['notify_vertical_position', 'top', 'Posición vertical notificaciones'],

        // Configuraciones de empresa
        ['company', 'Mi Restaurante', 'Nombre de la empresa'],
        ['address', 'Dirección del restaurante', 'Dirección'],
        ['phone', '123-456-7890', 'Teléfono'],
        ['email', 'admin@restaurante.com', 'Email'],
        ['website', 'www.restaurante.com', 'Sitio web'],

        // Configuraciones fiscales
        ['tax_included', '0', 'Impuestos incluidos'],
        ['default_tax_name_1', 'IVA', 'Nombre impuesto 1'],
        ['default_tax_rate_1', '21.00', 'Tasa impuesto 1'],
        ['default_tax_name_2', '', 'Nombre impuesto 2'],
        ['default_tax_rate_2', '0.00', 'Tasa impuesto 2'],

        // Configuraciones de moneda
        ['currency_symbol', '$', 'Símbolo de moneda'],
        ['currency_side', '0', 'Lado del símbolo (0=izquierda)'],
        ['thousands_separator', ',', 'Separador de miles'],
        ['decimal_separator', '.', 'Separador decimal'],
        ['currency_decimals', '2', 'Decimales de moneda'],

        // Configuraciones de stock
        ['stock_location', '1', 'Ubicación de stock por defecto'],
        ['show_stock_locations', '0', 'Mostrar ubicaciones de stock'],

        // Configuraciones de ventas
        ['default_sales_discount', '0', 'Descuento por defecto'],
        ['invoice_enable', '1', 'Habilitar facturas'],
        ['receipt_show_taxes', '1', 'Mostrar impuestos en recibo'],
        ['receipt_show_total_discount', '1', 'Mostrar descuento total'],
        ['receipt_show_description', '1', 'Mostrar descripción'],

        // Configuraciones de sistema
        ['timezone', 'America/Argentina/Buenos_Aires', 'Zona horaria'],
        ['theme', 'flatly', 'Tema visual'],
        ['language', 'spanish', 'Idioma'],
        ['country_codes', 'es', 'Código de país'],
        ['items_per_page', '25', 'Items por página'],

        // Configuraciones de seguridad
        ['logout_on_close', '0', 'Cerrar sesión al cerrar navegador'],
        ['require_employee_login_before_each_sale', '0', 'Requiere login antes de cada venta'],

        // Configuraciones de impresión
        ['print_after_sale', '0', 'Imprimir después de venta'],
        ['print_silently', '0', 'Imprimir silenciosamente'],
        ['receipt_template', 'receipt_default', 'Plantilla de recibo'],
        ['invoice_template', 'invoice_default', 'Plantilla de factura'],
    ];

    $inserted_count = 0;
    $updated_count = 0;

    foreach ($missing_configs as $config) {
        $key = $config[0];
        $value = $config[1];
        $description = $config[2];

        // Verificar si ya existe
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM app_config WHERE key = ?');
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $exists = $result->fetchArray()['count'] > 0;

        if (!$exists) {
            // Insertar nueva configuración
            $stmt = $db->prepare('INSERT INTO app_config (key, value) VALUES (?, ?)');
            $stmt->bindValue(1, $key, SQLITE3_TEXT);
            $stmt->bindValue(2, $value, SQLITE3_TEXT);

            if ($stmt->execute()) {
                echo "✅ Agregado: $key = $value ($description)\n";
                $inserted_count++;
            } else {
                echo "❌ Error agregando $key: " . $db->lastErrorMsg() . "\n";
            }
        } else {
            echo "⏭️  Ya existe: $key\n";
        }
    }

    // Verificar configuraciones críticas
    echo "\n🔍 Verificando configuraciones críticas:\n";

    $critical_keys = ['dateformat', 'timeformat', 'notify_horizontal_position', 'notify_vertical_position', 'company', 'currency_symbol'];

    foreach ($critical_keys as $key) {
        $stmt = $db->prepare('SELECT value FROM app_config WHERE key = ?');
        $stmt->bindValue(1, $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray();

        if ($row) {
            echo "✅ $key: " . $row['value'] . "\n";
        } else {
            echo "❌ FALTA: $key\n";
        }
    }

    // Contar total de configuraciones
    $total_configs = $db->query('SELECT COUNT(*) as count FROM app_config')->fetchArray()['count'];

    echo "\n📊 Resumen:\n";
    echo "   Configuraciones agregadas: $inserted_count\n";
    echo "   Total en base de datos: $total_configs\n";

    $db->close();

    echo "\n🎉 ¡CONFIGURACIONES COMPLETADAS!\n";
    echo "   OSPOS tiene todas las configuraciones necesarias\n";
    echo "   El dashboard debería cargar sin errores ahora\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>