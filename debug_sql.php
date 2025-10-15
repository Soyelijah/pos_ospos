<?php
$sql = file_get_contents('app/Database/tables.sql');
$statements = explode(';', $sql);
echo "Total statements: " . count($statements) . "\n";

$createCount = 0;
$showCount = 0;
foreach ($statements as $i => $statement) {
    $statement = trim($statement);

    // Show first few statements regardless
    if ($showCount < 5) {
        $showCount++;
        echo "Statement #$i: " . substr($statement, 0, 80) . "...\n";
    }

    if (empty($statement) || substr($statement, 0, 2) === '--') continue;

    if (strpos(strtoupper($statement), 'CREATE TABLE') !== false) {
        $createCount++;
        echo "CREATE #$createCount found at index $i:\n";
        echo substr($statement, 0, 100) . "...\n\n";

        if ($createCount >= 3) break; // Solo mostrar los primeros 3
    }
}

echo "Total CREATE statements found: $createCount\n";
?>