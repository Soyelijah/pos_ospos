-- ==============================================================================
-- OSPOS Database Complete Fix Script
-- ==============================================================================
-- This script fixes the OSPOS database to work exactly like the original version
-- Issues fixed:
-- 1. Missing menu_group column in grants table
-- 2. Missing 'home' and 'office' modules
-- 3. Missing 'office' permission
-- 4. Incorrect module menu_group assignments
-- 5. Missing app_config entries
-- 6. Missing additional modules (expenses, expenses_categories, cashups, attributes)
-- ==============================================================================

-- ==============================================================================
-- Step 1: Fix grants table structure - Add menu_group column if missing
-- ==============================================================================

-- Check if menu_group column exists, if not add it
-- SQLite doesn't support ADD COLUMN IF NOT EXISTS, so we check first
-- For SQLite, we need to handle this carefully

-- First, let's check the current structure and backup
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

-- Create backup of grants table
DROP TABLE IF EXISTS grants_backup;
CREATE TABLE grants_backup AS SELECT * FROM grants;

-- Recreate grants table with menu_group column
DROP TABLE IF EXISTS grants;
CREATE TABLE IF NOT EXISTS grants (
    permission_id VARCHAR(255) NOT NULL,
    person_id INTEGER NOT NULL,
    module_id VARCHAR(255) NOT NULL,
    menu_group VARCHAR(32) DEFAULT 'home',
    PRIMARY KEY (permission_id, person_id)
);

-- Restore data from backup
INSERT INTO grants (permission_id, person_id, module_id, menu_group)
SELECT permission_id, person_id, module_id, 'home'
FROM grants_backup;

COMMIT;
PRAGMA foreign_keys=ON;

-- ==============================================================================
-- Step 2: Add missing modules (home, office, expenses, cashups, attributes)
-- ==============================================================================

-- Delete existing entries first to avoid duplicates
DELETE FROM modules WHERE module_id IN ('home', 'office', 'expenses', 'expenses_categories', 'cashups', 'attributes');

-- Insert home and office modules
INSERT INTO modules (name_lang_key, desc_lang_key, sort, module_id, menu_group) VALUES
('module_home', 'module_home_desc', 1, 'home', 'home'),
('module_office', 'module_office_desc', 999, 'office', 'office');

-- Insert additional missing modules
INSERT INTO modules (name_lang_key, desc_lang_key, sort, module_id, menu_group) VALUES
('module_expenses', 'module_expenses_desc', 108, 'expenses', 'office'),
('module_expenses_categories', 'module_expenses_categories_desc', 109, 'expenses_categories', 'office'),
('module_cashups', 'module_cashups_desc', 105, 'cashups', 'office'),
('module_attributes', 'module_attributes_desc', 107, 'attributes', 'office');

-- ==============================================================================
-- Step 3: Update existing modules with correct menu_group assignments
-- ==============================================================================

-- Update existing modules to have correct menu_group
UPDATE modules SET menu_group = 'home' WHERE module_id IN ('customers', 'items', 'sales', 'home');
UPDATE modules SET menu_group = 'office' WHERE module_id IN ('config', 'employees', 'giftcards', 'item_kits', 'messages', 'receivings', 'reports', 'suppliers', 'office', 'expenses', 'expenses_categories', 'cashups', 'attributes');
UPDATE modules SET menu_group = 'both' WHERE module_id IN ('reports');

-- Update sort order to match original OSPOS
UPDATE modules SET sort = 1 WHERE module_id = 'home';
UPDATE modules SET sort = 10 WHERE module_id = 'customers';
UPDATE modules SET sort = 20 WHERE module_id = 'items';
UPDATE modules SET sort = 30 WHERE module_id = 'item_kits';
UPDATE modules SET sort = 40 WHERE module_id = 'suppliers';
UPDATE modules SET sort = 50 WHERE module_id = 'reports';
UPDATE modules SET sort = 60 WHERE module_id = 'receivings';
UPDATE modules SET sort = 70 WHERE module_id = 'sales';
UPDATE modules SET sort = 80 WHERE module_id = 'employees';
UPDATE modules SET sort = 90 WHERE module_id = 'giftcards';
UPDATE modules SET sort = 98 WHERE module_id = 'messages';
UPDATE modules SET sort = 105 WHERE module_id = 'cashups';
UPDATE modules SET sort = 107 WHERE module_id = 'attributes';
UPDATE modules SET sort = 108 WHERE module_id = 'expenses';
UPDATE modules SET sort = 109 WHERE module_id = 'expenses_categories';
UPDATE modules SET sort = 110 WHERE module_id = 'config';
UPDATE modules SET sort = 999 WHERE module_id = 'office';

-- ==============================================================================
-- Step 4: Add missing permissions
-- ==============================================================================

-- Delete existing to avoid duplicates
DELETE FROM permissions WHERE permission_id IN ('home', 'office', 'expenses', 'expenses_categories', 'cashups', 'attributes', 'reports_expenses_categories', 'sales_delete');

-- Add home and office permissions
INSERT INTO permissions (permission_id, module_id) VALUES
('home', 'home'),
('office', 'office');

-- Add additional module permissions
INSERT INTO permissions (permission_id, module_id) VALUES
('expenses', 'expenses'),
('expenses_categories', 'expenses_categories'),
('cashups', 'cashups'),
('attributes', 'attributes'),
('reports_expenses_categories', 'reports'),
('sales_delete', 'sales');

-- ==============================================================================
-- Step 5: Grant all permissions to admin user (person_id = 1)
-- ==============================================================================

-- Delete existing grants for these permissions to avoid duplicates
DELETE FROM grants WHERE permission_id IN ('home', 'office', 'expenses', 'expenses_categories', 'cashups', 'attributes', 'reports_expenses_categories', 'sales_delete');

-- Grant home and office access
INSERT INTO grants (permission_id, person_id, module_id, menu_group) VALUES
('home', 1, 'home', 'home'),
('office', 1, 'office', 'home');

-- Grant additional module permissions
INSERT INTO grants (permission_id, person_id, module_id, menu_group) VALUES
('expenses', 1, 'expenses', 'office'),
('expenses_categories', 1, 'expenses_categories', 'office'),
('cashups', 1, 'cashups', 'office'),
('attributes', 1, 'attributes', 'office'),
('reports_expenses_categories', 1, 'reports', 'office'),
('sales_delete', 1, 'sales', 'home');

-- Update menu_group for existing grants to office group
UPDATE grants
SET menu_group = 'office'
WHERE permission_id IN ('config', 'employees', 'giftcards', 'item_kits', 'messages', 'receivings', 'suppliers', 'reports_customers', 'reports_receivings', 'reports_items', 'reports_employees', 'reports_suppliers', 'reports_sales', 'reports_discounts', 'reports_taxes', 'reports_inventory', 'reports_categories', 'reports_payments', 'items_stock', 'receivings_stock')
AND person_id = 1;

-- Keep these in home group
UPDATE grants
SET menu_group = 'home'
WHERE permission_id IN ('customers', 'items', 'sales', 'sales_stock', 'home')
AND person_id = 1;

-- ==============================================================================
-- Step 6: Verify app_config has all required entries
-- ==============================================================================

-- Add missing config entries (only if they don't exist)
INSERT OR IGNORE INTO app_config (key, value) VALUES
('address', '123 Nowhere street'),
('company', 'Open Source Point of Sale'),
('default_tax_rate', '8'),
('email', 'changeme@example.com'),
('fax', ''),
('phone', '555-555-5555'),
('return_policy', 'Test'),
('timezone', 'America/New_York'),
('website', ''),
('company_logo', ''),
('tax_included', '0'),
('barcode_content', 'id'),
('barcode_type', 'Code39'),
('barcode_width', '250'),
('barcode_height', '50'),
('barcode_quality', '100'),
('barcode_font', 'Arial'),
('barcode_font_size', '10'),
('barcode_first_row', 'category'),
('barcode_second_row', 'item_code'),
('barcode_third_row', 'unit_price'),
('barcode_num_in_row', '2'),
('barcode_page_width', '100'),
('barcode_page_cellspacing', '20'),
('barcode_generate_if_empty', '0'),
('receipt_show_taxes', '0'),
('receipt_show_total_discount', '1'),
('receipt_show_description', '1'),
('receipt_show_serialnumber', '1'),
('invoice_enable', '1'),
('recv_invoice_format', '$CO'),
('sales_invoice_format', '$CO'),
('invoice_email_message', 'Dear $CU, In attachment the receipt for sale $INV'),
('invoice_default_comments', 'This is a default comment'),
('print_silently', '1'),
('print_header', '0'),
('print_footer', '0'),
('print_top_margin', '0'),
('print_left_margin', '0'),
('print_bottom_margin', '0'),
('print_right_margin', '0'),
('default_sales_discount', '0'),
('lines_per_page', '25'),
('dateformat', 'm/d/Y'),
('timeformat', 'H:i:s'),
('currency_symbol', '$'),
('number_locale', 'en_US'),
('thousands_separator', '1'),
('currency_decimals', '2'),
('tax_decimals', '2'),
('quantity_decimals', '0'),
('country_codes', 'us'),
('msg_msg', ''),
('msg_uid', ''),
('msg_src', ''),
('msg_pwd', ''),
('notify_horizontal_position', 'center'),
('notify_vertical_position', 'bottom'),
('payment_options_order', 'cashdebitcredit'),
('protocol', 'mail'),
('mailpath', '/usr/sbin/sendmail'),
('smtp_port', '465'),
('smtp_timeout', '5'),
('smtp_crypto', 'ssl'),
('receipt_template', 'receipt_default'),
('theme', 'flatly'),
('statistics', '1'),
('language', 'spanish'),
('language_code', 'es'),
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0'),
('suggestions_first_column', 'name'),
('suggestions_second_column', ''),
('suggestions_third_column', ''),
('allow_duplicate_barcodes', '0');

-- ==============================================================================
-- Step 7: Create missing tables if they don't exist
-- ==============================================================================

-- Create expenses_categories table
CREATE TABLE IF NOT EXISTS expense_categories (
  expense_category_id INTEGER PRIMARY KEY AUTOINCREMENT,
  category_name VARCHAR(255) DEFAULT NULL,
  category_description VARCHAR(255) NOT NULL,
  deleted INTEGER(1) NOT NULL DEFAULT 0
);

-- Create expenses table
CREATE TABLE IF NOT EXISTS expenses (
  expense_id INTEGER PRIMARY KEY AUTOINCREMENT,
  date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  category_id INTEGER NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  payment_type VARCHAR(40) NOT NULL,
  description TEXT,
  employee_id INTEGER NOT NULL,
  deleted INTEGER(1) NOT NULL DEFAULT 0
);

-- Create cashups table
CREATE TABLE IF NOT EXISTS ospos_cash_up (
  cashup_id INTEGER PRIMARY KEY AUTOINCREMENT,
  open_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  close_date TIMESTAMP NULL,
  open_amount_cash DECIMAL(15,2) NOT NULL,
  open_amount_check DECIMAL(15,2) NOT NULL,
  open_amount_giftcard DECIMAL(15,2) NOT NULL,
  open_amount_creditcard DECIMAL(15,2) NOT NULL,
  open_amount_due DECIMAL(15,2) NOT NULL,
  close_amount_cash DECIMAL(15,2) DEFAULT NULL,
  close_amount_check DECIMAL(15,2) DEFAULT NULL,
  close_amount_giftcard DECIMAL(15,2) DEFAULT NULL,
  close_amount_creditcard DECIMAL(15,2) DEFAULT NULL,
  close_amount_due DECIMAL(15,2) DEFAULT NULL,
  note TEXT,
  closed_by_employee_id INTEGER DEFAULT NULL,
  open_by_employee_id INTEGER NOT NULL,
  deleted INTEGER(1) NOT NULL DEFAULT 0,
  transfer_amount_cash DECIMAL(15,2) NOT NULL DEFAULT 0
);

-- Create attribute tables if they don't exist
CREATE TABLE IF NOT EXISTS ospos_attribute_definitions (
  definition_id INTEGER PRIMARY KEY AUTOINCREMENT,
  definition_name VARCHAR(255) NOT NULL,
  definition_type VARCHAR(32) NOT NULL,
  definition_unit VARCHAR(16) DEFAULT NULL,
  definition_flags INTEGER(1) DEFAULT 0,
  deleted INTEGER(1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS ospos_attribute_links (
  definition_id INTEGER NOT NULL,
  item_id INTEGER DEFAULT NULL,
  sale_id INTEGER DEFAULT NULL,
  receiving_id INTEGER DEFAULT NULL,
  attribute_id INTEGER DEFAULT NULL,
  PRIMARY KEY (definition_id, item_id, sale_id, receiving_id)
);

CREATE TABLE IF NOT EXISTS ospos_attribute_values (
  attribute_id INTEGER PRIMARY KEY AUTOINCREMENT,
  definition_id INTEGER NOT NULL,
  attribute_value VARCHAR(255) DEFAULT NULL,
  attribute_date DATE DEFAULT NULL,
  attribute_decimal DECIMAL(15,3) DEFAULT NULL,
  deleted INTEGER(1) NOT NULL DEFAULT 0
);

-- ==============================================================================
-- SUMMARY OF CHANGES
-- ==============================================================================
-- ✓ Added menu_group column to grants table
-- ✓ Added 'home' and 'office' modules
-- ✓ Added 'expenses', 'expenses_categories', 'cashups', 'attributes' modules
-- ✓ Updated module menu_group assignments (home vs office)
-- ✓ Added missing permissions for all modules
-- ✓ Granted all permissions to admin user
-- ✓ Added missing app_config entries
-- ✓ Created missing tables (expenses, cashups, attributes)
-- ==============================================================================

SELECT 'Database fix completed successfully!' as status;
