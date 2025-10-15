<?php
// Arreglar datos del usuario admin
try {
    $db = new SQLite3('D:\pos_ventas\posventa\writable\ospos_restaurante.db');

    // Verificar datos del usuario admin
    $result = $db->query('SELECT username, language, language_code FROM employees WHERE username = "admin"');
    $admin = $result->fetchArray();

    if ($admin) {
        echo "Usuario admin encontrado:\n";
        echo "- Username: " . $admin['username'] . "\n";
        echo "- Language: " . ($admin['language'] ?: 'NULL') . "\n";
        echo "- Language_code: " . ($admin['language_code'] ?: 'NULL') . "\n";

        // Actualizar si faltan valores
        if (empty($admin['language']) || empty($admin['language_code'])) {
            echo "Actualizando language y language_code...\n";
            $stmt = $db->prepare('UPDATE employees SET language = ?, language_code = ? WHERE username = ?');
            $stmt->bindValue(1, 'spanish');
            $stmt->bindValue(2, 'es');
            $stmt->bindValue(3, 'admin');
            $stmt->execute();
            echo "✅ Usuario admin actualizado\n";
        } else {
            echo "✅ Usuario admin ya tiene language configurado\n";
        }
    } else {
        echo "❌ Usuario admin no encontrado\n";
    }

    $db->close();
    echo "🎉 Verificación completada\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>