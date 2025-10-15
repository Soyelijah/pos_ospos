<?php
/**
 * Database Verification Script
 * Verifies all tables and their structure
 */

$db = new PDO('sqlite:posventa.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "OSPOS Database Verification Report\n";
echo "===================================\n\n";

// Get all tables
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'ospos_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "Total OSPOS tables: " . count($tables) . "\n\n";

// Check for the 14+ critical tables
$criticalTables = [
    'ospos_giftcards',
    'ospos_items',
    'ospos_item_kit_items',
    'ospos_item_kits',
    'ospos_stock_locations',
    'ospos_receivings',
    'ospos_receivings_items',
    'ospos_sales',
    'ospos_sales_items',
    'ospos_sales_items_taxes',
    'ospos_sales_payments',
    'ospos_sales_suspended',
    'ospos_suppliers',
];

echo "Critical Tables Status:\n";
echo "-----------------------\n";
foreach ($criticalTables as $table) {
    $status = in_array($table, $tables) ? '✓ EXISTS' : '✗ MISSING';
    echo "$table: $status\n";
}

echo "\n\nAdditional Tables:\n";
echo "------------------\n";
foreach ($tables as $table) {
    if (!in_array($table, $criticalTables)) {
        echo "- $table\n";
    }
}

echo "\n\nDefault Data Status:\n";
echo "--------------------\n";

// Check stock_locations
$count = $db->query("SELECT COUNT(*) FROM ospos_stock_locations")->fetchColumn();
echo "ospos_stock_locations: $count records\n";

// Check dinner_tables
$count = $db->query("SELECT COUNT(*) FROM ospos_dinner_tables")->fetchColumn();
echo "ospos_dinner_tables: $count records\n";

// Check customers_packages
$count = $db->query("SELECT COUNT(*) FROM ospos_customers_packages")->fetchColumn();
echo "ospos_customers_packages: $count records\n";

echo "\nDatabase verification complete!\n";
