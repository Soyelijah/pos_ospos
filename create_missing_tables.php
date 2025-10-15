<?php
/**
 * OSPOS Complete Database Table Creation Script
 * Compatible with SQLite
 * Creates all 15+ missing tables with proper structure, indexes, and default data
 *
 * Based on official OSPOS database schema from:
 * - app/Database/database.sql
 * - app/Database/tables.sql
 * - app/Database/constraints.sql
 * - Migration scripts in app/Database/Migrations/sqlscripts/
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_path = __DIR__ . '/posventa.db';

try {
    echo "Connecting to SQLite database: $db_path\n";
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign keys in SQLite
    $db->exec('PRAGMA foreign_keys = ON');

    echo "Database connected successfully.\n\n";

    // ====================================================================================
    // TABLE 1: ospos_giftcards
    // ====================================================================================
    echo "Creating table: ospos_giftcards\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_giftcards (
            record_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            giftcard_id INTEGER PRIMARY KEY AUTOINCREMENT,
            giftcard_number VARCHAR(255) NOT NULL UNIQUE,
            value DECIMAL(15,2) NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0,
            person_id INTEGER DEFAULT NULL,
            FOREIGN KEY (person_id) REFERENCES ospos_people (person_id)
        )
    ");

    // ====================================================================================
    // TABLE 2: ospos_items
    // ====================================================================================
    echo "Creating table: ospos_items\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_items (
            name VARCHAR(255) NOT NULL,
            category VARCHAR(255) NOT NULL,
            supplier_id INTEGER DEFAULT NULL,
            item_number VARCHAR(255) DEFAULT NULL UNIQUE,
            description VARCHAR(255) NOT NULL,
            cost_price DECIMAL(15,2) NOT NULL,
            unit_price DECIMAL(15,2) NOT NULL,
            reorder_level DECIMAL(15,3) NOT NULL DEFAULT 0,
            receiving_quantity DECIMAL(15,3) NOT NULL DEFAULT 1,
            item_id INTEGER PRIMARY KEY AUTOINCREMENT,
            pic_id INTEGER DEFAULT NULL,
            allow_alt_description INTEGER NOT NULL,
            is_serialized INTEGER NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0,
            tax_category_id INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_items_supplier ON ospos_items(supplier_id)");

    // ====================================================================================
    // TABLE 3: ospos_item_kits
    // ====================================================================================
    echo "Creating table: ospos_item_kits\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_item_kits (
            item_kit_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description VARCHAR(255) NOT NULL,
            item_kit_number VARCHAR(255) DEFAULT NULL
        )
    ");

    // ====================================================================================
    // TABLE 4: ospos_item_kit_items
    // ====================================================================================
    echo "Creating table: ospos_item_kit_items\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_item_kit_items (
            item_kit_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            quantity DECIMAL(15,3) NOT NULL,
            PRIMARY KEY (item_kit_id, item_id, quantity),
            FOREIGN KEY (item_kit_id) REFERENCES ospos_item_kits (item_kit_id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id) ON DELETE CASCADE
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_item_kit_items_item ON ospos_item_kit_items(item_id)");

    // ====================================================================================
    // TABLE 5: ospos_stock_locations
    // ====================================================================================
    echo "Creating table: ospos_stock_locations\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_stock_locations (
            location_id INTEGER PRIMARY KEY AUTOINCREMENT,
            location_name VARCHAR(255) DEFAULT NULL,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // Insert default location
    $stmt = $db->query("SELECT COUNT(*) FROM ospos_stock_locations");
    if ($stmt->fetchColumn() == 0) {
        echo "  -> Inserting default location 'stock'\n";
        $db->exec("INSERT INTO ospos_stock_locations (deleted, location_name) VALUES (0, 'stock')");
    }

    // ====================================================================================
    // TABLE 6: ospos_suppliers
    // ====================================================================================
    echo "Creating table: ospos_suppliers\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_suppliers (
            person_id INTEGER PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL,
            agency_name VARCHAR(255) NOT NULL,
            account_number VARCHAR(255) DEFAULT NULL UNIQUE,
            deleted INTEGER NOT NULL DEFAULT 0,
            category INTEGER NOT NULL DEFAULT 0,
            tax_id VARCHAR(32) DEFAULT NULL,
            FOREIGN KEY (person_id) REFERENCES ospos_people (person_id)
        )
    ");

    // ====================================================================================
    // TABLE 7: ospos_receivings
    // ====================================================================================
    echo "Creating table: ospos_receivings\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_receivings (
            receiving_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            supplier_id INTEGER DEFAULT NULL,
            employee_id INTEGER NOT NULL DEFAULT 0,
            comment TEXT NOT NULL,
            receiving_id INTEGER PRIMARY KEY AUTOINCREMENT,
            payment_type VARCHAR(20) DEFAULT NULL,
            reference VARCHAR(32) DEFAULT NULL,
            FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id),
            FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_receivings_supplier ON ospos_receivings(supplier_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_receivings_employee ON ospos_receivings(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_receivings_reference ON ospos_receivings(reference)");

    // ====================================================================================
    // TABLE 8: ospos_receivings_items
    // ====================================================================================
    echo "Creating table: ospos_receivings_items (receiving_items)\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_receivings_items (
            receiving_id INTEGER NOT NULL DEFAULT 0,
            item_id INTEGER NOT NULL DEFAULT 0,
            description VARCHAR(30) DEFAULT NULL,
            serialnumber VARCHAR(30) DEFAULT NULL,
            line INTEGER NOT NULL,
            quantity_purchased DECIMAL(15,3) NOT NULL DEFAULT 0,
            item_cost_price DECIMAL(15,2) NOT NULL,
            item_unit_price DECIMAL(15,2) NOT NULL,
            discount_percent DECIMAL(15,2) NOT NULL DEFAULT 0,
            item_location INTEGER NOT NULL,
            receiving_quantity DECIMAL(15,3) NOT NULL DEFAULT 1,
            PRIMARY KEY (receiving_id, item_id, line),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id),
            FOREIGN KEY (receiving_id) REFERENCES ospos_receivings (receiving_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_receivings_items_item ON ospos_receivings_items(item_id)");

    // ====================================================================================
    // TABLE 9: ospos_sales
    // ====================================================================================
    echo "Creating table: ospos_sales\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales (
            sale_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            customer_id INTEGER DEFAULT NULL,
            employee_id INTEGER NOT NULL DEFAULT 0,
            comment TEXT NOT NULL,
            invoice_number VARCHAR(32) DEFAULT NULL UNIQUE,
            sale_id INTEGER PRIMARY KEY AUTOINCREMENT,
            quote_number VARCHAR(32) DEFAULT NULL,
            sale_status INTEGER NOT NULL DEFAULT 0,
            dinner_table_id INTEGER DEFAULT NULL,
            FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id),
            FOREIGN KEY (customer_id) REFERENCES ospos_customers (person_id),
            FOREIGN KEY (dinner_table_id) REFERENCES ospos_dinner_tables (dinner_table_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_customer ON ospos_sales(customer_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_employee ON ospos_sales(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_time ON ospos_sales(sale_time)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_dinner_table ON ospos_sales(dinner_table_id)");

    // ====================================================================================
    // TABLE 10: ospos_sales_items
    // ====================================================================================
    echo "Creating table: ospos_sales_items\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_items (
            sale_id INTEGER NOT NULL DEFAULT 0,
            item_id INTEGER NOT NULL DEFAULT 0,
            description VARCHAR(30) DEFAULT NULL,
            serialnumber VARCHAR(30) DEFAULT NULL,
            line INTEGER NOT NULL DEFAULT 0,
            quantity_purchased DECIMAL(15,3) NOT NULL DEFAULT 0,
            item_cost_price DECIMAL(15,2) NOT NULL,
            item_unit_price DECIMAL(15,2) NOT NULL,
            discount_percent DECIMAL(15,2) NOT NULL DEFAULT 0,
            item_location INTEGER NOT NULL,
            PRIMARY KEY (sale_id, item_id, line),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id),
            FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id),
            FOREIGN KEY (item_location) REFERENCES ospos_stock_locations (location_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_items_sale ON ospos_sales_items(sale_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_items_item ON ospos_sales_items(item_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_items_location ON ospos_sales_items(item_location)");

    // ====================================================================================
    // TABLE 11: ospos_sales_items_taxes
    // ====================================================================================
    echo "Creating table: ospos_sales_items_taxes\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_items_taxes (
            sale_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            line INTEGER NOT NULL DEFAULT 0,
            name VARCHAR(255) NOT NULL,
            percent DECIMAL(15,4) NOT NULL DEFAULT 0,
            tax_type INTEGER NOT NULL DEFAULT 0,
            rounding_code INTEGER NOT NULL DEFAULT 0,
            cascade_tax INTEGER NOT NULL DEFAULT 0,
            cascade_sequence INTEGER NOT NULL DEFAULT 0,
            item_tax_amount DECIMAL(15,4) NOT NULL DEFAULT 0,
            PRIMARY KEY (sale_id, item_id, line, name, percent),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_items_taxes_sale ON ospos_sales_items_taxes(sale_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_items_taxes_item ON ospos_sales_items_taxes(item_id)");

    // ====================================================================================
    // TABLE 12: ospos_sales_payments
    // ====================================================================================
    echo "Creating table: ospos_sales_payments\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_payments (
            sale_id INTEGER NOT NULL,
            payment_type VARCHAR(40) NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            PRIMARY KEY (sale_id, payment_type),
            FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_payments_sale ON ospos_sales_payments(sale_id)");

    // ====================================================================================
    // TABLE 13: ospos_sales_suspended
    // ====================================================================================
    echo "Creating table: ospos_sales_suspended\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_suspended (
            sale_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            customer_id INTEGER DEFAULT NULL,
            employee_id INTEGER NOT NULL DEFAULT 0,
            comment TEXT NOT NULL,
            invoice_number VARCHAR(32) DEFAULT NULL,
            sale_id INTEGER PRIMARY KEY AUTOINCREMENT,
            quote_number VARCHAR(32) DEFAULT NULL,
            dinner_table_id INTEGER DEFAULT NULL,
            FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id),
            FOREIGN KEY (customer_id) REFERENCES ospos_customers (person_id),
            FOREIGN KEY (dinner_table_id) REFERENCES ospos_dinner_tables (dinner_table_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_customer ON ospos_sales_suspended(customer_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_employee ON ospos_sales_suspended(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_dinner_table ON ospos_sales_suspended(dinner_table_id)");

    // ====================================================================================
    // TABLE 14: ospos_sales_taxes
    // ====================================================================================
    echo "Creating table: ospos_sales_taxes\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_taxes (
            sales_taxes_id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            tax_type INTEGER NOT NULL,
            tax_group VARCHAR(32) NOT NULL,
            sale_tax_basis DECIMAL(15,4) NOT NULL,
            sale_tax_amount DECIMAL(15,4) NOT NULL,
            print_sequence INTEGER NOT NULL DEFAULT 0,
            name VARCHAR(255) NOT NULL,
            tax_rate DECIMAL(15,4) NOT NULL,
            sales_tax_code VARCHAR(32) NOT NULL DEFAULT '',
            rounding_code INTEGER NOT NULL DEFAULT 0
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_taxes_sale ON ospos_sales_taxes(sale_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_taxes_print ON ospos_sales_taxes(sale_id, print_sequence, tax_group)");

    // ====================================================================================
    // ADDITIONAL TABLES (Beyond the 14 requested, but part of complete OSPOS)
    // ====================================================================================

    // TABLE: ospos_item_quantities
    echo "Creating table: ospos_item_quantities\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_item_quantities (
            item_id INTEGER NOT NULL,
            location_id INTEGER NOT NULL,
            quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
            PRIMARY KEY (item_id, location_id),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id),
            FOREIGN KEY (location_id) REFERENCES ospos_stock_locations (location_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_item_quantities_item ON ospos_item_quantities(item_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_item_quantities_location ON ospos_item_quantities(location_id)");

    // TABLE: ospos_inventory
    echo "Creating table: ospos_inventory\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_inventory (
            trans_id INTEGER PRIMARY KEY AUTOINCREMENT,
            trans_items INTEGER NOT NULL DEFAULT 0,
            trans_user INTEGER NOT NULL DEFAULT 0,
            trans_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            trans_comment TEXT NOT NULL,
            trans_location INTEGER NOT NULL,
            trans_inventory DECIMAL(15,3) NOT NULL DEFAULT 0,
            FOREIGN KEY (trans_items) REFERENCES ospos_items (item_id),
            FOREIGN KEY (trans_user) REFERENCES ospos_employees (person_id),
            FOREIGN KEY (trans_location) REFERENCES ospos_stock_locations (location_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_inventory_items ON ospos_inventory(trans_items)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_inventory_user ON ospos_inventory(trans_user)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_inventory_location ON ospos_inventory(trans_location)");

    // TABLE: ospos_items_taxes
    echo "Creating table: ospos_items_taxes\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_items_taxes (
            item_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            percent DECIMAL(15,3) NOT NULL,
            PRIMARY KEY (item_id, name, percent),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id) ON DELETE CASCADE
        )
    ");

    // TABLE: ospos_sales_suspended_items
    echo "Creating table: ospos_sales_suspended_items\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_suspended_items (
            sale_id INTEGER NOT NULL DEFAULT 0,
            item_id INTEGER NOT NULL DEFAULT 0,
            description VARCHAR(30) DEFAULT NULL,
            serialnumber VARCHAR(30) DEFAULT NULL,
            line INTEGER NOT NULL DEFAULT 0,
            quantity_purchased DECIMAL(15,3) NOT NULL DEFAULT 0,
            item_cost_price DECIMAL(15,2) NOT NULL,
            item_unit_price DECIMAL(15,2) NOT NULL,
            discount_percent DECIMAL(15,2) NOT NULL DEFAULT 0,
            item_location INTEGER NOT NULL,
            PRIMARY KEY (sale_id, item_id, line),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id),
            FOREIGN KEY (sale_id) REFERENCES ospos_sales_suspended (sale_id),
            FOREIGN KEY (item_location) REFERENCES ospos_stock_locations (location_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_items_sale ON ospos_sales_suspended_items(sale_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_items_item ON ospos_sales_suspended_items(item_id)");

    // TABLE: ospos_sales_suspended_items_taxes
    echo "Creating table: ospos_sales_suspended_items_taxes\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_suspended_items_taxes (
            sale_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            line INTEGER NOT NULL DEFAULT 0,
            name VARCHAR(255) NOT NULL,
            percent DECIMAL(15,3) NOT NULL,
            PRIMARY KEY (sale_id, item_id, line, name, percent),
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_sales_suspended_items_taxes_item ON ospos_sales_suspended_items_taxes(item_id)");

    // TABLE: ospos_sales_suspended_payments
    echo "Creating table: ospos_sales_suspended_payments\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_suspended_payments (
            sale_id INTEGER NOT NULL,
            payment_type VARCHAR(40) NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            PRIMARY KEY (sale_id, payment_type),
            FOREIGN KEY (sale_id) REFERENCES ospos_sales_suspended (sale_id)
        )
    ");

    // ====================================================================================
    // ADDITIONAL ADVANCED TABLES (Optional but recommended for full OSPOS functionality)
    // ====================================================================================

    // TABLE: ospos_attribute_definitions
    echo "Creating table: ospos_attribute_definitions\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_attribute_definitions (
            definition_id INTEGER PRIMARY KEY AUTOINCREMENT,
            definition_name VARCHAR(255) NOT NULL,
            definition_type VARCHAR(45) NOT NULL,
            definition_flags INTEGER NOT NULL,
            definition_fk INTEGER DEFAULT NULL,
            deleted INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (definition_fk) REFERENCES ospos_attribute_definitions (definition_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_definitions_fk ON ospos_attribute_definitions(definition_fk)");

    // TABLE: ospos_attribute_values
    echo "Creating table: ospos_attribute_values\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_attribute_values (
            attribute_id INTEGER PRIMARY KEY AUTOINCREMENT,
            attribute_value VARCHAR(255) UNIQUE DEFAULT NULL,
            attribute_datetime DATETIME DEFAULT NULL
        )
    ");

    // TABLE: ospos_attribute_links
    echo "Creating table: ospos_attribute_links\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_attribute_links (
            attribute_id INTEGER DEFAULT NULL,
            definition_id INTEGER NOT NULL,
            item_id INTEGER DEFAULT NULL,
            sale_id INTEGER DEFAULT NULL,
            receiving_id INTEGER DEFAULT NULL,
            UNIQUE (attribute_id, definition_id, item_id, sale_id, receiving_id),
            FOREIGN KEY (definition_id) REFERENCES ospos_attribute_definitions (definition_id) ON DELETE CASCADE,
            FOREIGN KEY (attribute_id) REFERENCES ospos_attribute_values (attribute_id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES ospos_items (item_id),
            FOREIGN KEY (receiving_id) REFERENCES ospos_receivings (receiving_id),
            FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_links_attr ON ospos_attribute_links(attribute_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_links_def ON ospos_attribute_links(definition_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_links_item ON ospos_attribute_links(item_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_links_sale ON ospos_attribute_links(sale_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_attribute_links_receiving ON ospos_attribute_links(receiving_id)");

    // TABLE: ospos_expense_categories
    echo "Creating table: ospos_expense_categories\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_expense_categories (
            expense_category_id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_name VARCHAR(255) DEFAULT NULL UNIQUE,
            category_description VARCHAR(255) NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // TABLE: ospos_expenses
    echo "Creating table: ospos_expenses\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_expenses (
            expense_id INTEGER PRIMARY KEY AUTOINCREMENT,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            amount DECIMAL(15,2) NOT NULL,
            payment_type VARCHAR(40) NOT NULL,
            expense_category_id INTEGER NOT NULL,
            description VARCHAR(255) NOT NULL,
            employee_id INTEGER NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (expense_category_id) REFERENCES ospos_expense_categories (expense_category_id),
            FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_expenses_category ON ospos_expenses(expense_category_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_expenses_employee ON ospos_expenses(employee_id)");

    // TABLE: ospos_cash_up
    echo "Creating table: ospos_cash_up\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_cash_up (
            cashup_id INTEGER PRIMARY KEY AUTOINCREMENT,
            open_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            close_date TIMESTAMP DEFAULT NULL,
            open_amount_cash DECIMAL(15,2) NOT NULL,
            transfer_amount_cash DECIMAL(15,2) NOT NULL,
            note INTEGER NOT NULL,
            closed_amount_cash DECIMAL(15,2) NOT NULL,
            closed_amount_card DECIMAL(15,2) NOT NULL,
            closed_amount_check DECIMAL(15,2) NOT NULL,
            closed_amount_total DECIMAL(15,2) NOT NULL,
            closed_amount_due DECIMAL(15,2) NOT NULL,
            description VARCHAR(255) NOT NULL,
            open_employee_id INTEGER NOT NULL,
            close_employee_id INTEGER NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (open_employee_id) REFERENCES ospos_employees (person_id),
            FOREIGN KEY (close_employee_id) REFERENCES ospos_employees (person_id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_cash_up_open_employee ON ospos_cash_up(open_employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_cash_up_close_employee ON ospos_cash_up(close_employee_id)");

    // TABLE: ospos_dinner_tables
    echo "Creating table: ospos_dinner_tables\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_dinner_tables (
            dinner_table_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(30) NOT NULL,
            status INTEGER NOT NULL DEFAULT 0,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // Insert default dinner tables
    $stmt = $db->query("SELECT COUNT(*) FROM ospos_dinner_tables");
    if ($stmt->fetchColumn() == 0) {
        echo "  -> Inserting default dinner tables\n";
        $db->exec("INSERT INTO ospos_dinner_tables (name, status, deleted) VALUES ('Delivery', 0, 0)");
        $db->exec("INSERT INTO ospos_dinner_tables (name, status, deleted) VALUES ('Take Away', 0, 0)");
    }

    // TABLE: ospos_customers_packages
    echo "Creating table: ospos_customers_packages\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_customers_packages (
            package_id INTEGER PRIMARY KEY AUTOINCREMENT,
            package_name VARCHAR(255) DEFAULT NULL,
            points_percent REAL NOT NULL DEFAULT 0,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // Insert default packages
    $stmt = $db->query("SELECT COUNT(*) FROM ospos_customers_packages");
    if ($stmt->fetchColumn() == 0) {
        echo "  -> Inserting default customer packages\n";
        $db->exec("INSERT INTO ospos_customers_packages (package_name, points_percent, deleted) VALUES
            ('Default', 0, 0),
            ('Bronze', 10, 0),
            ('Silver', 20, 0),
            ('Gold', 30, 0),
            ('Premium', 50, 0)");
    }

    // TABLE: ospos_customers_points
    echo "Creating table: ospos_customers_points\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_customers_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            person_id INTEGER NOT NULL,
            package_id INTEGER NOT NULL,
            sale_id INTEGER NOT NULL,
            points_earned INTEGER NOT NULL
        )
    ");

    // TABLE: ospos_sales_reward_points
    echo "Creating table: ospos_sales_reward_points\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_sales_reward_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            earned REAL NOT NULL,
            used REAL NOT NULL
        )
    ");

    // TABLE: ospos_tax_codes
    echo "Creating table: ospos_tax_codes\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_tax_codes (
            tax_code VARCHAR(32) PRIMARY KEY,
            tax_code_name VARCHAR(255) NOT NULL DEFAULT '',
            tax_code_type INTEGER NOT NULL DEFAULT 0,
            city VARCHAR(255) NOT NULL DEFAULT '',
            state VARCHAR(255) NOT NULL DEFAULT ''
        )
    ");

    // TABLE: ospos_tax_categories
    echo "Creating table: ospos_tax_categories\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_tax_categories (
            tax_category_id INTEGER PRIMARY KEY,
            tax_category VARCHAR(32) NOT NULL,
            tax_group_sequence INTEGER NOT NULL,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // TABLE: ospos_tax_jurisdictions
    echo "Creating table: ospos_tax_jurisdictions\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_tax_jurisdictions (
            jurisdiction_id INTEGER PRIMARY KEY AUTOINCREMENT,
            jurisdiction_name VARCHAR(255) DEFAULT NULL,
            tax_group VARCHAR(32) NOT NULL,
            tax_type INTEGER NOT NULL,
            reporting_authority VARCHAR(255) DEFAULT NULL,
            tax_group_sequence INTEGER NOT NULL DEFAULT 0,
            cascade_sequence INTEGER NOT NULL DEFAULT 0,
            deleted INTEGER NOT NULL DEFAULT 0
        )
    ");

    // TABLE: ospos_tax_rates
    echo "Creating table: ospos_tax_rates\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS ospos_tax_rates (
            tax_rate_id INTEGER PRIMARY KEY AUTOINCREMENT,
            rate_tax_code_id INTEGER NOT NULL,
            rate_tax_category_id INTEGER NOT NULL,
            rate_jurisdiction_id INTEGER NOT NULL,
            tax_rate DECIMAL(15,4) NOT NULL DEFAULT 0,
            tax_rounding_code INTEGER NOT NULL DEFAULT 0
        )
    ");

    echo "\n";
    echo "========================================\n";
    echo "SUCCESS! All tables created successfully.\n";
    echo "========================================\n\n";

    // Display summary of created tables
    echo "Summary of created tables:\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    echo "\nTotal tables: " . count($tables) . "\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
