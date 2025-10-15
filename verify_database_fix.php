<?php
/**
 * OSPOS Database Fix Verification Script
 *
 * This script verifies that all database fixes have been applied correctly
 * and provides a detailed report of the current database state.
 */

$db_path = __DIR__ . '/writable/ospos_restaurante.db';

if (!file_exists($db_path)) {
    die("ERROR: Database file not found at: $db_path\n");
}

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "==============================================================================\n";
    echo "OSPOS Database Verification Report\n";
    echo "==============================================================================\n";
    echo "Database: $db_path\n";
    echo "Date: " . date('Y-m-d H:i:s') . "\n";
    echo "==============================================================================\n\n";

    // Test 1: Check grants table structure
    echo "TEST 1: Grants Table Structure\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("PRAGMA table_info(grants)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $has_menu_group = false;

    echo "Columns in grants table:\n";
    foreach ($columns as $col) {
        echo "  - {$col['name']} ({$col['type']})\n";
        if ($col['name'] === 'menu_group') {
            $has_menu_group = true;
        }
    }

    if ($has_menu_group) {
        echo "✓ PASS: menu_group column exists\n\n";
    } else {
        echo "✗ FAIL: menu_group column is MISSING\n\n";
    }

    // Test 2: Check modules count
    echo "TEST 2: Modules Count and Structure\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM modules");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $module_count = $result['count'];

    echo "Total modules: $module_count\n";

    if ($module_count >= 17) {
        echo "✓ PASS: Expected 17+ modules, found $module_count\n\n";
    } else {
        echo "✗ FAIL: Expected 17+ modules, found only $module_count\n\n";
    }

    // Test 3: Check for required modules
    echo "TEST 3: Required Modules Presence\n";
    echo "----------------------------------------------------------------------\n";
    $required_modules = ['home', 'office', 'customers', 'items', 'sales', 'employees',
                        'reports', 'config', 'suppliers', 'receivings', 'giftcards',
                        'item_kits', 'messages', 'expenses', 'expenses_categories',
                        'cashups', 'attributes'];

    $stmt = $db->query("SELECT module_id FROM modules");
    $existing_modules = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missing_modules = array_diff($required_modules, $existing_modules);

    if (empty($missing_modules)) {
        echo "✓ PASS: All required modules are present\n";
        echo "Modules found: " . implode(', ', $existing_modules) . "\n\n";
    } else {
        echo "✗ FAIL: Missing modules: " . implode(', ', $missing_modules) . "\n\n";
    }

    // Test 4: Check module menu_group assignments
    echo "TEST 4: Module Menu Group Assignments\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("SELECT module_id, menu_group, sort FROM modules ORDER BY sort");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo sprintf("%-25s %-15s %-10s\n", "Module ID", "Menu Group", "Sort Order");
    echo str_repeat("-", 70) . "\n";

    $home_count = 0;
    $office_count = 0;
    $both_count = 0;

    foreach ($modules as $module) {
        echo sprintf("%-25s %-15s %-10s\n",
            $module['module_id'],
            $module['menu_group'],
            $module['sort']
        );

        if ($module['menu_group'] === 'home') $home_count++;
        if ($module['menu_group'] === 'office') $office_count++;
        if ($module['menu_group'] === 'both') $both_count++;
    }

    echo "\nSummary:\n";
    echo "  Home modules: $home_count\n";
    echo "  Office modules: $office_count\n";
    echo "  Both modules: $both_count\n";

    if ($home_count >= 4 && $office_count >= 10) {
        echo "✓ PASS: Menu group assignments look correct\n\n";
    } else {
        echo "✗ WARNING: Menu group assignments may need review\n\n";
    }

    // Test 5: Check permissions
    echo "TEST 5: Permissions Count\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM permissions");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $permission_count = $result['count'];

    echo "Total permissions: $permission_count\n";

    if ($permission_count >= 25) {
        echo "✓ PASS: Expected 25+ permissions, found $permission_count\n\n";
    } else {
        echo "✗ FAIL: Expected 25+ permissions, found only $permission_count\n\n";
    }

    // Test 6: Check required permissions
    echo "TEST 6: Required Permissions Presence\n";
    echo "----------------------------------------------------------------------\n";
    $required_permissions = ['home', 'office', 'customers', 'items', 'sales',
                            'employees', 'reports', 'config', 'suppliers',
                            'receivings', 'giftcards', 'item_kits', 'messages',
                            'expenses', 'expenses_categories', 'cashups', 'attributes'];

    $stmt = $db->query("SELECT permission_id FROM permissions");
    $existing_permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missing_permissions = array_diff($required_permissions, $existing_permissions);

    if (empty($missing_permissions)) {
        echo "✓ PASS: All required permissions are present\n\n";
    } else {
        echo "✗ FAIL: Missing permissions: " . implode(', ', $missing_permissions) . "\n\n";
    }

    // Test 7: Check admin grants
    echo "TEST 7: Admin User Grants (person_id = 1)\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM grants WHERE person_id = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $grant_count = $result['count'];

    echo "Total grants for admin: $grant_count\n";

    if ($grant_count >= 30) {
        echo "✓ PASS: Admin has sufficient grants ($grant_count)\n\n";
    } else {
        echo "✗ WARNING: Admin has fewer grants than expected ($grant_count)\n\n";
    }

    // Test 8: Check admin grants with menu_group
    echo "TEST 8: Admin Grants by Menu Group\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("
        SELECT menu_group, COUNT(*) as count
        FROM grants
        WHERE person_id = 1
        GROUP BY menu_group
        ORDER BY menu_group
    ");
    $grant_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo sprintf("%-20s %-10s\n", "Menu Group", "Count");
    echo str_repeat("-", 30) . "\n";

    foreach ($grant_groups as $group) {
        echo sprintf("%-20s %-10s\n", $group['menu_group'], $group['count']);
    }
    echo "\n";

    // Test 9: Check app_config count
    echo "TEST 9: Application Configuration\n";
    echo "----------------------------------------------------------------------\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM app_config");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $config_count = $result['count'];

    echo "Total configuration entries: $config_count\n";

    if ($config_count >= 40) {
        echo "✓ PASS: Configuration appears complete ($config_count entries)\n\n";
    } else {
        echo "✗ WARNING: Configuration may be incomplete ($config_count entries)\n\n";
    }

    // Test 10: Check required tables
    echo "TEST 10: Required Tables Existence\n";
    echo "----------------------------------------------------------------------\n";
    $required_tables = [
        'modules', 'permissions', 'grants', 'people', 'employees', 'customers',
        'items', 'sales', 'suppliers', 'receivings', 'app_config', 'sessions',
        'expense_categories', 'expenses', 'ospos_cash_up',
        'ospos_attribute_definitions', 'ospos_attribute_links', 'ospos_attribute_values'
    ];

    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missing_tables = array_diff($required_tables, $existing_tables);

    if (empty($missing_tables)) {
        echo "✓ PASS: All required tables exist\n";
        echo "Total tables: " . count($existing_tables) . "\n\n";
    } else {
        echo "✗ FAIL: Missing tables: " . implode(', ', $missing_tables) . "\n\n";
    }

    // Final Summary
    echo "==============================================================================\n";
    echo "VERIFICATION SUMMARY\n";
    echo "==============================================================================\n";

    $all_tests_passed =
        $has_menu_group &&
        $module_count >= 17 &&
        empty($missing_modules) &&
        $permission_count >= 25 &&
        empty($missing_permissions) &&
        $grant_count >= 30 &&
        empty($missing_tables);

    if ($all_tests_passed) {
        echo "✓ ALL TESTS PASSED\n";
        echo "The database has been properly fixed and is ready to use.\n";
        echo "You should now be able to see all modules in the OSPOS interface.\n";
    } else {
        echo "✗ SOME TESTS FAILED\n";
        echo "Please review the failed tests above and reapply the fix if necessary.\n";
        echo "Run: sqlite3 writable/ospos_restaurante.db < fix_ospos_database_complete.sql\n";
    }

    echo "==============================================================================\n";

} catch (PDOException $e) {
    die("ERROR: Database error: " . $e->getMessage() . "\n");
}
?>
