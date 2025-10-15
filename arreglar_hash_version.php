<?php
/**
 * ARREGLA EL TIPO DE DATO DE HASH_VERSION
 * Convierte hash_version de INTEGER a TEXT para compatibilidad con OSPOS
 */
echo "🔧 ARREGLANDO HASH_VERSION EN OSPOS\n";
echo "==================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    echo "🔄 Recreando tabla employees con hash_version como TEXT...\n";

    // 1. Crear tabla temporal con estructura correcta
    $sql_temp = "
    CREATE TABLE employees_temp (
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        person_id INT(10) NOT NULL,
        deleted INT(1) NOT NULL DEFAULT 0,
        hash_version TEXT NOT NULL DEFAULT '2',
        language VARCHAR(32) NOT NULL DEFAULT 'english',
        language_code VARCHAR(8) NOT NULL DEFAULT 'en'
    )";

    if ($db->exec($sql_temp)) {
        echo "✅ Tabla temporal creada\n";
    } else {
        echo "❌ Error creando tabla temporal: " . $db->lastErrorMsg() . "\n";
        exit;
    }

    // 2. Copiar datos existentes
    $sql_copy = "INSERT INTO employees_temp SELECT username, password, person_id, deleted, CAST(hash_version AS TEXT), language, language_code FROM employees";

    if ($db->exec($sql_copy)) {
        echo "✅ Datos copiados\n";
    } else {
        echo "❌ Error copiando datos: " . $db->lastErrorMsg() . "\n";
        exit;
    }

    // 3. Eliminar tabla original y renombrar
    $db->exec("DROP TABLE employees");
    $db->exec("ALTER TABLE employees_temp RENAME TO employees");

    echo "✅ Tabla employees recreada\n";

    // 4. Verificar que hash_version es TEXT y tiene valor '2'
    $result = $db->query('SELECT username, hash_version, typeof(hash_version) as hash_type, password FROM employees WHERE username = "admin"');
    $admin = $result->fetchArray();

    if ($admin) {
        echo "\n📊 Verificación final:\n";
        echo "- Username: " . $admin['username'] . "\n";
        echo "- Hash version: " . $admin['hash_version'] . "\n";
        echo "- Tipo de dato: " . $admin['hash_type'] . "\n";

        // Probar verificación exacta como OSPOS
        $password = 'pointofsale';
        if ($admin['hash_version'] === '2' && password_verify($password, $admin['password'])) {
            echo "- ✅ OSPOS verification: OK\n";
        } else {
            echo "- ❌ OSPOS verification: FAILED\n";
        }
    }

    $db->close();

    echo "\n🎉 HASH_VERSION CORREGIDO!\n";
    echo "   El login de OSPOS debería funcionar ahora.\n";
    echo "\n🔑 CREDENCIALES:\n";
    echo "   Usuario: admin\n";
    echo "   Contraseña: pointofsale\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>