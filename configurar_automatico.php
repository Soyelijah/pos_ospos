<?php
/**
 * CONFIGURADOR AUTOMÁTICO OSPOS
 * Detecta automáticamente la configuración MySQL local
 */

echo "🚀 OSPOS - Configurador Automático para Restaurantes\n";
echo "==============================================\n\n";

// Posibles combinaciones de credenciales MySQL comunes
$credenciales_comunes = [
    // XAMPP por defecto
    ['root', '', 'XAMPP por defecto'],
    // MySQL instalado manualmente
    ['root', 'root', 'MySQL con contraseña root'],
    ['root', '123456', 'MySQL con contraseña simple'],
    ['root', 'admin', 'MySQL con contraseña admin'],
    // Otras instalaciones comunes
    ['admin', 'admin', 'Usuario admin'],
    ['mysql', '', 'Usuario mysql sin contraseña'],
    ['mysql', 'mysql', 'Usuario mysql con contraseña'],
];

$conexion_exitosa = false;
$credenciales_correctas = null;

echo "🔍 Detectando configuración MySQL...\n";

foreach ($credenciales_comunes as $cred) {
    [$usuario, $password, $descripcion] = $cred;

    try {
        $dsn = "mysql:host=localhost;port=3306;charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "✅ CONEXIÓN EXITOSA: $descripcion (Usuario: $usuario)\n";
        $credenciales_correctas = $cred;
        $conexion_exitosa = true;
        break;

    } catch (PDOException $e) {
        echo "❌ Falló: $descripcion\n";
        continue;
    }
}

if (!$conexion_exitosa) {
    echo "\n❌ No se pudo conectar con las credenciales comunes.\n";
    echo "Por favor, introduce las credenciales manualmente:\n";
    echo "Usuario: ";
    $usuario_manual = trim(fgets(STDIN));
    echo "Contraseña (deja vacío si no tiene): ";
    $password_manual = trim(fgets(STDIN));

    try {
        $dsn = "mysql:host=localhost;port=3306;charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario_manual, $password_manual);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "✅ CONEXIÓN MANUAL EXITOSA\n";
        $credenciales_correctas = [$usuario_manual, $password_manual, 'Manual'];
        $conexion_exitosa = true;

    } catch (PDOException $e) {
        die("\n❌ Error de conexión manual: " . $e->getMessage() . "\n");
    }
}

if ($conexion_exitosa) {
    [$usuario_final, $password_final, $tipo] = $credenciales_correctas;

    echo "\n🛠️  Configurando base de datos...\n";

    // Crear base de datos si no existe
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS ospos_restaurante CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "✅ Base de datos 'ospos_restaurante' creada/verificada\n";

        // Verificar si hay tablas
        $pdo->exec("USE ospos_restaurante");
        $stmt = $pdo->query("SHOW TABLES LIKE 'ospos_%'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($tablas) == 0) {
            echo "📊 Importando estructura de base de datos...\n";

            // Leer y ejecutar el archivo database.sql
            $sql_file = __DIR__ . '/app/Database/database.sql';
            if (file_exists($sql_file)) {
                $sql_content = file_get_contents($sql_file);

                // Dividir por declaraciones y ejecutar
                $statements = array_filter(array_map('trim', explode(';', $sql_content)));

                foreach ($statements as $stmt) {
                    if (!empty($stmt) && !preg_match('/^--/', $stmt)) {
                        try {
                            $pdo->exec($stmt);
                        } catch (PDOException $e) {
                            // Algunos statements pueden fallar (como comentarios), continuar
                        }
                    }
                }

                echo "✅ Estructura de base de datos importada\n";
            } else {
                echo "⚠️  Archivo database.sql no encontrado en app/Database/\n";
            }
        } else {
            echo "✅ Base de datos ya contiene " . count($tablas) . " tablas OSPOS\n";
        }

    } catch (PDOException $e) {
        echo "❌ Error configurando base de datos: " . $e->getMessage() . "\n";
    }

    // Crear archivo .env optimizado
    echo "\n⚙️  Creando configuración optimizada...\n";

    $env_content = "
#--------------------------------------------------------------------
# OSPOS PARA RESTAURANTES - CONFIGURACIÓN AUTOMÁTICA
# Generado: " . date('Y-m-d H:i:s') . "
#--------------------------------------------------------------------

CI_ENVIRONMENT = development
CI_DEBUG = false

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.appTimezone = 'America/Mexico_City'

#--------------------------------------------------------------------
# DATABASE - Configuración Detectada: $tipo
#--------------------------------------------------------------------

database.default.hostname = 'localhost'
database.default.database = 'ospos_restaurante'
database.default.username = '$usuario_final'
database.default.password = '$password_final'
database.default.DBDriver = 'MySQLi'
database.default.DBPrefix = 'ospos_'
database.default.port = 3306

database.development.hostname = 'localhost'
database.development.database = 'ospos_restaurante'
database.development.username = '$usuario_final'
database.development.password = '$password_final'
database.development.DBDriver = 'MySQLi'
database.development.DBPrefix = 'ospos_'
database.development.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption.key = '" . bin2hex(random_bytes(32)) . "'

#--------------------------------------------------------------------
# CONFIGURACIÓN PARA RESTAURANTES
#--------------------------------------------------------------------

logger.threshold = 4
app.db_log_enabled = false
app.db_log_only_long = false
";

    file_put_contents('.env', $env_content);
    echo "✅ Archivo .env configurado correctamente\n";

    echo "\n🎉 ¡CONFIGURACIÓN COMPLETADA!\n";
    echo "=======================================\n";
    echo "Para iniciar OSPOS ejecuta:\n";
    echo "   php -S localhost:8000 -t public\n";
    echo "\n";
    echo "Luego abre: http://localhost:8000\n";
    echo "Usuario: admin\n";
    echo "Contraseña: pointofsale\n";
    echo "\n";
    echo "💼 Para mover a otro restaurante:\n";
    echo "1. Copia toda esta carpeta\n";
    echo "2. Ejecuta: php configurar_automatico.php\n";
    echo "3. Ejecuta: php -S localhost:8000 -t public\n";
    echo "=======================================\n";

} else {
    echo "\n❌ No se pudo establecer conexión con MySQL\n";
    echo "Verifica que MySQL esté ejecutándose en el puerto 3306\n";
}
?>