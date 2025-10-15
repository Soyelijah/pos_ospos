<?php
$db = new SQLite3('writable/ospos_restaurante.db');
echo "Agregando columna category_id a tabla items...\n";
try {
    $db->exec('ALTER TABLE items ADD COLUMN category_id INTEGER DEFAULT 1');
    echo "✅ Columna category_id agregada\n";
} catch (Exception $e) {
    echo "⚠️ Columna category_id ya existe o error: " . $e->getMessage() . "\n";
}
$db->close();