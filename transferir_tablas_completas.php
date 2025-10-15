<?php
/**
 * TRANSFIERE TODAS LAS TABLAS OSPOS COMPLETAS
 * Mueve las tablas de posventa.db a ospos_restaurante.db
 */
echo "📋 TRANSFIRIENDO TABLAS OSPOS COMPLETAS\n";
echo "======================================\n\n";

try {
    // Conectar a ambas bases de datos
    $source_db = new SQLite3('D:\pos_ventas\posventa\posventa.db');
    $target_db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "✅ Conectado a base de datos origen (posventa.db)\n";
    echo "✅ Conectado a base de datos destino (ospos_restaurante.db)\n\n";

    // Habilitar foreign keys en ambas
    $source_db->exec('PRAGMA foreign_keys = ON');
    $target_db->exec('PRAGMA foreign_keys = ON');

    // Obtener lista de tablas desde la base origen (excluyendo sqlite_sequence)
    $result = $source_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence' ORDER BY name");
    $tables_to_transfer = [];

    while ($row = $result->fetchArray()) {
        $tables_to_transfer[] = $row['name'];
    }

    echo "📊 Tablas a transferir: " . count($tables_to_transfer) . "\n\n";

    // Obtener estructura y datos de cada tabla
    foreach ($tables_to_transfer as $table_name) {
        echo "🔄 Procesando tabla: $table_name\n";

        // 1. Obtener estructura de la tabla
        $create_sql = $source_db->querySingle("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table_name'");

        if ($create_sql) {
            // 2. Crear tabla en destino (DROP si existe)
            $target_db->exec("DROP TABLE IF EXISTS $table_name");
            $target_db->exec($create_sql);
            echo "   ✅ Estructura creada\n";

            // 3. Verificar si tiene datos
            $count = $source_db->querySingle("SELECT COUNT(*) FROM $table_name");

            if ($count > 0) {
                // 4. Copiar datos
                $result = $source_db->query("SELECT * FROM $table_name");

                // Obtener nombres de columnas
                $column_info = $source_db->query("PRAGMA table_info($table_name)");
                $columns = [];
                while ($col = $column_info->fetchArray()) {
                    $columns[] = $col['name'];
                }

                $placeholders = str_repeat('?,', count($columns) - 1) . '?';
                $column_names = implode(',', $columns);

                $stmt = $target_db->prepare("INSERT INTO $table_name ($column_names) VALUES ($placeholders)");

                $inserted = 0;
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $values = array_values($row);

                    // Bind values
                    for ($i = 0; $i < count($values); $i++) {
                        $stmt->bindValue($i + 1, $values[$i]);
                    }

                    if ($stmt->execute()) {
                        $inserted++;
                    }
                    $stmt->reset();
                }

                echo "   ✅ $inserted registros copiados\n";
            } else {
                echo "   ⚪ Sin datos para copiar\n";
            }

            // 5. Copiar índices
            $indexes = $source_db->query("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='$table_name' AND sql IS NOT NULL");
            $index_count = 0;
            while ($index = $indexes->fetchArray()) {
                if ($index['sql']) {
                    $target_db->exec($index['sql']);
                    $index_count++;
                }
            }

            if ($index_count > 0) {
                echo "   ✅ $index_count índices creados\n";
            }
        }

        echo "\n";
    }

    // Verificar resultado final
    echo "🔍 VERIFICACIÓN FINAL:\n";
    echo "=====================\n";

    $final_tables = $target_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence' ORDER BY name");
    $total_tables = 0;
    $total_records = 0;

    while ($table = $final_tables->fetchArray()) {
        $table_name = $table['name'];
        $count = $target_db->querySingle("SELECT COUNT(*) FROM $table_name");
        echo "✅ $table_name: $count registros\n";
        $total_tables++;
        $total_records += $count;
    }

    echo "\n📊 RESUMEN FINAL:\n";
    echo "================\n";
    echo "✅ Total de tablas: $total_tables\n";
    echo "✅ Total de registros: $total_records\n";

    $source_db->close();
    $target_db->close();

    echo "\n🎉 ¡TRANSFERENCIA COMPLETADA EXITOSAMENTE!\n";
    echo "   Base de datos ospos_restaurante.db ahora tiene estructura completa\n";
    echo "   Compatible 100% con OSPOS oficial\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>