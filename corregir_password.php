<?php
/**
 * CORRECTOR DE PASSWORD ADMIN OSPOS
 * Actualiza la contraseña del usuario admin a "pointofsale"
 */
echo "🔑 CORRIGIENDO PASSWORD ADMIN OSPOS\n";
echo "==================================\n\n";

try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    // Generar hash correcto para "pointofsale"
    $password = 'pointofsale';
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    echo "🔧 Generando nuevo hash para contraseña: $password\n";
    echo "📝 Hash generado: " . substr($hash, 0, 50) . "...\n";

    // Actualizar la contraseña del usuario admin
    $stmt = $db->prepare('UPDATE employees SET password = ? WHERE username = ?');
    $stmt->bindValue(1, $hash, SQLITE3_TEXT);
    $stmt->bindValue(2, 'admin', SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "✅ Contraseña actualizada exitosamente\n";
    } else {
        echo "❌ Error actualizando contraseña: " . $db->lastErrorMsg() . "\n";
    }

    // Verificar la actualización
    $result = $db->query('SELECT username, password FROM employees WHERE username = "admin"');
    $admin = $result->fetchArray();

    if ($admin) {
        echo "\n📊 Verificación:\n";
        echo "   Usuario: " . $admin['username'] . "\n";
        echo "   Nuevo hash: " . substr($admin['password'], 0, 50) . "...\n";

        // Verificar que el hash funciona
        if (password_verify($password, $admin['password'])) {
            echo "   ✅ Verificación exitosa: password_verify() OK\n";
        } else {
            echo "   ❌ Error: password_verify() FAILED\n";
        }
    }

    $db->close();

    echo "\n🎉 CREDENCIALES CORREGIDAS:\n";
    echo "   Usuario: admin\n";
    echo "   Contraseña: pointofsale\n";
    echo "\n💡 Ahora puedes hacer login en OSPOS\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>