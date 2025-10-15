<?php
/**
 * MARCA TODAS LAS MIGRACIONES COMO COMPLETADAS
 * Evita que OSPOS trate de ejecutar migraciones MySQL en SQLite
 */
echo "🔄 MARCANDO MIGRACIONES COMO COMPLETADAS\n";
echo "=======================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    // Obtener lista de archivos de migración
    $migrationsPath = 'app/Database/Migrations/';
    $migrationFiles = glob($migrationsPath . '*.php');

    echo "📁 Archivos de migración encontrados: " . count($migrationFiles) . "\n";

    $batch = 1;
    $insertedCount = 0;

    foreach ($migrationFiles as $file) {
        $filename = basename($file);

        // Extraer el timestamp del nombre del archivo
        if (preg_match('/^(\d{14})_(.+)\.php$/', $filename, $matches)) {
            $version = $matches[1];
            $name = $matches[2];
            $class = 'App\\Database\\Migrations\\Migration_' . str_replace('_', '_', $name);

            // Verificar si ya existe en la base de datos
            $stmt = $db->prepare('SELECT COUNT(*) as count FROM migrations WHERE version = ?');
            $stmt->bindValue(1, $version, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray();

            if ($row['count'] == 0) {
                // Insertar la migración como completada
                $stmt = $db->prepare('INSERT INTO migrations (version, class, `group`, namespace, time, batch) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bindValue(1, $version, SQLITE3_TEXT);
                $stmt->bindValue(2, $class, SQLITE3_TEXT);
                $stmt->bindValue(3, 'default', SQLITE3_TEXT);
                $stmt->bindValue(4, 'App', SQLITE3_TEXT);
                $stmt->bindValue(5, time(), SQLITE3_INTEGER);
                $stmt->bindValue(6, $batch, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    echo "✅ Migración marcada: $version ($name)\n";
                    $insertedCount++;
                } else {
                    echo "❌ Error marcando: $version ($name) - " . $db->lastErrorMsg() . "\n";
                }
            } else {
                echo "⏭️  Ya existe: $version ($name)\n";
            }
        } else {
            echo "⚠️  Archivo ignorado (formato incorrecto): $filename\n";
        }
    }

    // Verificar el resultado
    $result = $db->query('SELECT COUNT(*) as total FROM migrations');
    $total = $result->fetchArray()['total'];

    echo "\n📊 Resumen:\n";
    echo "   Migraciones insertadas: $insertedCount\n";
    echo "   Total en base de datos: $total\n";

    // Verificar que el sistema no necesite migraciones
    echo "\n🔍 Verificando estado de migraciones:\n";
    $latestInDb = $db->query('SELECT MAX(version) as latest FROM migrations')->fetchArray()['latest'];
    echo "   Última migración en DB: $latestInDb\n";

    $db->close();

    echo "\n🎉 ¡MIGRACIONES MARCADAS COMO COMPLETADAS!\n";
    echo "   OSPOS no tratará de ejecutar migraciones MySQL\n";
    echo "   El sistema debería funcionar sin errores de migración\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>