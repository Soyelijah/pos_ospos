<?php
/**
 * INICIALIZADOR SQLITE PARA OSPOS
 * Convierte la estructura MySQL a SQLite y crea la base de datos inicial
 */

echo "🚀 INICIALIZANDO BASE DE DATOS SQLITE OSPOS\n";
echo "==========================================\n\n";

$dbPath = 'writable/ospos_restaurante.db';

try {
    // Eliminar base de datos existente si existe
    if (file_exists($dbPath)) {
        unlink($dbPath);
        echo "🗑️  Base de datos anterior eliminada\n";
    }

    // Crear nueva conexión SQLite
    $db = new SQLite3($dbPath);
    echo "✅ Conexión SQLite creada: $dbPath\n";

    // Leer el archivo tables.sql (contiene la estructura completa)
    $sqlFile = 'app/Database/tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo tables.sql no encontrado");
    }

    $sql = file_get_contents($sqlFile);
    echo "📄 Archivo tables.sql leído\n";

    // Convertir MySQL a SQLite
    echo "🔄 Convirtiendo sintaxis MySQL a SQLite...\n";

    // Remover ENGINE=InnoDB y DEFAULT CHARSET
    $sql = preg_replace('/\s*ENGINE=InnoDB\s*/i', '', $sql);
    $sql = preg_replace('/\s*DEFAULT CHARSET=utf8\s*/i', '', $sql);

    // Convertir AUTO_INCREMENT a AUTOINCREMENT
    $sql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $sql);

    // Convertir LONGTEXT a TEXT
    $sql = preg_replace('/LONGTEXT/i', 'TEXT', $sql);

    // Convertir DATETIME con DEFAULT CURRENT_TIMESTAMP
    $sql = preg_replace('/DATETIME DEFAULT CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);

    // Separar sentencias SQL y procesarlas
    $statements = explode(';', $sql);
    $executed = 0;
    $errors = 0;

    foreach ($statements as $statement) {
        $statement = trim($statement);

        // Saltar comentarios y líneas vacías
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        // Procesar solo CREATE TABLE statements en esta primera pasada
        if (strtoupper(substr($statement, 0, 6)) !== 'CREATE') {
            continue;
        }

        try {
            echo "🔧 Creando tabla: " . substr($statement, 0, 50) . "...\n";
            $result = $db->exec($statement);
            if ($result === false) {
                echo "⚠️  Error creando tabla: " . $db->lastErrorMsg() . "\n";
                $errors++;
            } else {
                $executed++;
                echo "✅ Tabla creada exitosamente\n";
            }
        } catch (Exception $e) {
            echo "❌ Error creando tabla: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    // Segunda pasada: insertar datos básicos de configuración
    echo "\n📊 Insertando datos básicos...\n";
    foreach ($statements as $statement) {
        $statement = trim($statement);

        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        // Procesar solo INSERT INTO ospos_app_config (configuración básica)
        if (strtoupper(substr($statement, 0, 6)) === 'INSERT' &&
            strpos($statement, 'ospos_app_config') !== false) {

            try {
                $result = $db->exec($statement);
                if ($result === false) {
                    echo "⚠️  Error insertando config: " . $db->lastErrorMsg() . "\n";
                } else {
                    echo "✅ Configuración insertada\n";
                }
            } catch (Exception $e) {
                echo "❌ Error insertando config: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n📊 Estadísticas de importación:\n";
    echo "   ✅ Sentencias ejecutadas: $executed\n";
    echo "   ⚠️  Advertencias/errores: $errors\n";

    // Crear usuario administrador
    echo "\n👤 Creando usuario administrador...\n";
    $adminPassword = password_hash('pointofsale', PASSWORD_DEFAULT);

    $adminSql = "INSERT OR REPLACE INTO ospos_employees (
        username, password, person_id, deleted, hash_version
    ) VALUES (
        'admin', '$adminPassword', 1, 0, 2
    )";

    $db->exec($adminSql);

    // Crear persona para el admin
    $personSql = "INSERT OR REPLACE INTO ospos_people (
        person_id, first_name, last_name, email, phone_number, address_1,
        address_2, city, state, zip, country, comments
    ) VALUES (
        1, 'Administrator', 'Admin', 'admin@example.com', '', '', '', '', '', '', '', 'System Administrator'
    )";

    $db->exec($personSql);

    echo "✅ Usuario admin creado\n";
    echo "   Usuario: admin\n";
    echo "   Contraseña: pointofsale\n";

    // Dar permisos de administrador
    $permissionsSql = "INSERT OR REPLACE INTO ospos_grants (
        permission_id, person_id, module_id
    ) VALUES
        ('config', 1, 'config'),
        ('employees', 1, 'employees'),
        ('customers', 1, 'customers'),
        ('suppliers', 1, 'suppliers'),
        ('items', 1, 'items'),
        ('sales', 1, 'sales'),
        ('receivings', 1, 'receivings'),
        ('reports', 1, 'reports')";

    try {
        $db->exec($permissionsSql);
        echo "✅ Permisos de administrador configurados\n";
    } catch (Exception $e) {
        echo "⚠️  Permisos: " . $e->getMessage() . "\n";
    }

    $db->close();

    echo "\n🎉 ¡BASE DE DATOS SQLITE INICIALIZADA!\n";
    echo "=====================================\n";
    echo "📁 Archivo: $dbPath\n";
    echo "👤 Usuario: admin\n";
    echo "🔑 Contraseña: pointofsale\n";
    echo "\n🚀 Para iniciar OSPOS:\n";
    echo "   php -S localhost:8000 -t public\n";
    echo "   URL: http://localhost:8000\n\n";

} catch (Exception $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    exit(1);
}
?>