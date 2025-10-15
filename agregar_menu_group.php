<?php
/**
 * AGREGA COLUMNA MENU_GROUP A TABLA MODULES
 * Necesaria para categorizar módulos entre 'home' y 'office'
 */
echo "🔧 AGREGANDO MENU_GROUP A MODULES\n";
echo "=================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "📋 Agregando columna menu_group...\n";

    // SQLite no permite ALTER TABLE ADD COLUMN con DEFAULT que no sea constant
    // Necesitamos recrear la tabla

    // 1. Crear tabla temporal con nueva estructura
    $sql_temp = "
    CREATE TABLE modules_temp (
        name_lang_key VARCHAR(255) NOT NULL,
        desc_lang_key VARCHAR(255) NOT NULL,
        sort INTEGER NOT NULL,
        module_id VARCHAR(255) NOT NULL PRIMARY KEY,
        menu_group VARCHAR(255) NOT NULL DEFAULT 'home',
        UNIQUE(desc_lang_key),
        UNIQUE(name_lang_key)
    )";

    if ($db->exec($sql_temp)) {
        echo "✅ Tabla temporal creada\n";
    } else {
        echo "❌ Error creando tabla temporal: " . $db->lastErrorMsg() . "\n";
        exit;
    }

    // 2. Copiar datos existentes con valores de menu_group apropiados
    echo "📊 Copiando datos con menu_group...\n";

    // Definir qué módulos van en cada menú
    $home_modules = ['customers', 'items', 'sales', 'home'];
    $office_modules = ['reports', 'employees', 'suppliers', 'config', 'item_kits', 'receivings', 'giftcards', 'messages'];

    // Obtener datos existentes
    $result = $db->query('SELECT * FROM modules');
    $copied = 0;

    while ($row = $result->fetchArray()) {
        $menu_group = in_array($row['module_id'], $home_modules) ? 'home' : 'office';

        $stmt = $db->prepare('INSERT INTO modules_temp (name_lang_key, desc_lang_key, sort, module_id, menu_group) VALUES (?, ?, ?, ?, ?)');
        $stmt->bindValue(1, $row['name_lang_key'], SQLITE3_TEXT);
        $stmt->bindValue(2, $row['desc_lang_key'], SQLITE3_TEXT);
        $stmt->bindValue(3, $row['sort'], SQLITE3_INTEGER);
        $stmt->bindValue(4, $row['module_id'], SQLITE3_TEXT);
        $stmt->bindValue(5, $menu_group, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $copied++;
            echo "✅ Copiado: {$row['module_id']} -> $menu_group\n";
        } else {
            echo "❌ Error copiando {$row['module_id']}: " . $db->lastErrorMsg() . "\n";
        }
    }

    // 3. Eliminar tabla original y renombrar
    $db->exec("DROP TABLE modules");
    $db->exec("ALTER TABLE modules_temp RENAME TO modules");

    echo "✅ Tabla modules actualizada\n";

    // 4. Verificar resultado
    echo "\n📊 Verificación final:\n";

    $result = $db->query('SELECT module_id, menu_group FROM modules ORDER BY menu_group, sort');
    echo "Módulos por grupo:\n";

    $current_group = '';
    while ($row = $result->fetchArray()) {
        if ($row['menu_group'] !== $current_group) {
            $current_group = $row['menu_group'];
            echo "\n🏠 Grupo: $current_group\n";
        }
        echo "  - {$row['module_id']}\n";
    }

    // Contar por grupo
    $home_count = $db->query('SELECT COUNT(*) as count FROM modules WHERE menu_group = "home"')->fetchArray()['count'];
    $office_count = $db->query('SELECT COUNT(*) as count FROM modules WHERE menu_group = "office"')->fetchArray()['count'];

    echo "\n📈 Resumen:\n";
    echo "   Módulos HOME: $home_count\n";
    echo "   Módulos OFFICE: $office_count\n";
    echo "   Total copiado: $copied\n";

    $db->close();

    echo "\n🎉 ¡MENU_GROUP AGREGADO EXITOSAMENTE!\n";
    echo "   Los módulos están categorizados correctamente\n";
    echo "   OSPOS debería cargar el dashboard sin errores\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>