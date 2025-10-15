# OSPOS Complete Database Setup Documentation

## Overview
This documentation describes the comprehensive database table creation script for Open Source Point of Sale (OSPOS) compatible with SQLite.

## Script Information
- **Script Name**: `create_missing_tables.php`
- **Database Type**: SQLite
- **Tables Created**: 34 OSPOS tables
- **Based On**: Official OSPOS schema from version 3.x

## Execution

### How to Run
```bash
cd /d/pos_ventas/posventa
php create_missing_tables.php
```

### Expected Output
The script will:
1. Connect to the SQLite database (posventa.db)
2. Create all missing tables
3. Add appropriate indexes and foreign keys
4. Populate default data where required
5. Display a summary of created tables

## Tables Created

### Core Critical Tables (14 tables)

#### 1. ospos_giftcards
Gift card management table for tracking gift card issuance and redemption.

**Columns:**
- `giftcard_id` (PRIMARY KEY, AUTOINCREMENT)
- `giftcard_number` (VARCHAR(255), UNIQUE) - Gift card identifier
- `value` (DECIMAL(15,2)) - Current balance
- `deleted` (INTEGER) - Soft delete flag
- `person_id` (INTEGER) - Associated customer
- `record_time` (TIMESTAMP) - Creation timestamp

**Foreign Keys:**
- `person_id` → `ospos_people.person_id`

---

#### 2. ospos_items
Product/inventory item master table.

**Columns:**
- `item_id` (PRIMARY KEY, AUTOINCREMENT)
- `name` (VARCHAR(255)) - Item name
- `category` (VARCHAR(255)) - Item category
- `supplier_id` (INTEGER) - Default supplier
- `item_number` (VARCHAR(255), UNIQUE) - SKU/barcode
- `description` (VARCHAR(255)) - Item description
- `cost_price` (DECIMAL(15,2)) - Purchase cost
- `unit_price` (DECIMAL(15,2)) - Selling price
- `reorder_level` (DECIMAL(15,3)) - Min stock level
- `receiving_quantity` (DECIMAL(15,3)) - Default receiving qty
- `pic_id` (INTEGER) - Associated image
- `allow_alt_description` (INTEGER) - Allow custom descriptions
- `is_serialized` (INTEGER) - Track serial numbers
- `deleted` (INTEGER) - Soft delete flag
- `tax_category_id` (INTEGER) - Tax category

**Foreign Keys:**
- `supplier_id` → `ospos_suppliers.person_id`

**Indexes:**
- `idx_items_supplier` on `supplier_id`

---

#### 3. ospos_item_kits
Item bundle/package definitions.

**Columns:**
- `item_kit_id` (PRIMARY KEY, AUTOINCREMENT)
- `name` (VARCHAR(255)) - Kit name
- `description` (VARCHAR(255)) - Kit description
- `item_kit_number` (VARCHAR(255)) - Kit SKU

---

#### 4. ospos_item_kit_items
Maps individual items to kits with quantities.

**Columns:**
- `item_kit_id` (INTEGER) - Kit reference
- `item_id` (INTEGER) - Item reference
- `quantity` (DECIMAL(15,3)) - Quantity in kit

**Primary Key:** Composite (`item_kit_id`, `item_id`, `quantity`)

**Foreign Keys:**
- `item_kit_id` → `ospos_item_kits.item_kit_id` (ON DELETE CASCADE)
- `item_id` → `ospos_items.item_id` (ON DELETE CASCADE)

---

#### 5. ospos_stock_locations
Warehouse/location management for multi-location inventory.

**Columns:**
- `location_id` (PRIMARY KEY, AUTOINCREMENT)
- `location_name` (VARCHAR(255)) - Location name
- `deleted` (INTEGER) - Soft delete flag

**Default Data:**
- ID 1: 'stock' (default location)

---

#### 6. ospos_suppliers
Supplier/vendor master table.

**Columns:**
- `person_id` (PRIMARY KEY) - Links to ospos_people
- `company_name` (VARCHAR(255)) - Company name
- `agency_name` (VARCHAR(255)) - Agency name
- `account_number` (VARCHAR(255), UNIQUE) - Account number
- `deleted` (INTEGER) - Soft delete flag
- `category` (INTEGER) - Supplier category
- `tax_id` (VARCHAR(32)) - Tax identification

**Foreign Keys:**
- `person_id` → `ospos_people.person_id`

---

#### 7. ospos_receivings
Purchase order/receiving header table.

**Columns:**
- `receiving_id` (PRIMARY KEY, AUTOINCREMENT)
- `receiving_time` (TIMESTAMP) - Receipt timestamp
- `supplier_id` (INTEGER) - Supplier reference
- `employee_id` (INTEGER) - Receiving employee
- `comment` (TEXT) - Notes
- `payment_type` (VARCHAR(20)) - Payment method
- `reference` (VARCHAR(32)) - Reference number

**Foreign Keys:**
- `employee_id` → `ospos_employees.person_id`
- `supplier_id` → `ospos_suppliers.person_id`

**Indexes:**
- `idx_receivings_supplier` on `supplier_id`
- `idx_receivings_employee` on `employee_id`
- `idx_receivings_reference` on `reference`

---

#### 8. ospos_receivings_items
Purchase order line items.

**Columns:**
- `receiving_id` (INTEGER) - Receiving reference
- `item_id` (INTEGER) - Item reference
- `line` (INTEGER) - Line number
- `description` (VARCHAR(30)) - Line description
- `serialnumber` (VARCHAR(30)) - Serial number
- `quantity_purchased` (DECIMAL(15,3)) - Quantity received
- `item_cost_price` (DECIMAL(15,2)) - Cost per unit
- `item_unit_price` (DECIMAL(15,2)) - Selling price
- `discount_percent` (DECIMAL(15,2)) - Discount percentage
- `item_location` (INTEGER) - Receiving location
- `receiving_quantity` (DECIMAL(15,3)) - Receiving unit qty

**Primary Key:** Composite (`receiving_id`, `item_id`, `line`)

**Foreign Keys:**
- `item_id` → `ospos_items.item_id`
- `receiving_id` → `ospos_receivings.receiving_id`

---

#### 9. ospos_sales
Sales transaction header table.

**Columns:**
- `sale_id` (PRIMARY KEY, AUTOINCREMENT)
- `sale_time` (TIMESTAMP) - Sale timestamp
- `customer_id` (INTEGER) - Customer reference
- `employee_id` (INTEGER) - Sales employee
- `comment` (TEXT) - Notes
- `invoice_number` (VARCHAR(32), UNIQUE) - Invoice number
- `quote_number` (VARCHAR(32)) - Quote number
- `sale_status` (INTEGER) - Status (0=completed, etc.)
- `dinner_table_id` (INTEGER) - Restaurant table

**Foreign Keys:**
- `employee_id` → `ospos_employees.person_id`
- `customer_id` → `ospos_customers.person_id`
- `dinner_table_id` → `ospos_dinner_tables.dinner_table_id`

**Indexes:**
- `idx_sales_customer` on `customer_id`
- `idx_sales_employee` on `employee_id`
- `idx_sales_time` on `sale_time`
- `idx_sales_dinner_table` on `dinner_table_id`

---

#### 10. ospos_sales_items
Sales transaction line items.

**Columns:**
- `sale_id` (INTEGER) - Sale reference
- `item_id` (INTEGER) - Item reference
- `line` (INTEGER) - Line number
- `description` (VARCHAR(30)) - Line description
- `serialnumber` (VARCHAR(30)) - Serial number
- `quantity_purchased` (DECIMAL(15,3)) - Quantity sold
- `item_cost_price` (DECIMAL(15,2)) - Cost per unit
- `item_unit_price` (DECIMAL(15,2)) - Selling price
- `discount_percent` (DECIMAL(15,2)) - Discount percentage
- `item_location` (INTEGER) - Stock location

**Primary Key:** Composite (`sale_id`, `item_id`, `line`)

**Foreign Keys:**
- `item_id` → `ospos_items.item_id`
- `sale_id` → `ospos_sales.sale_id`
- `item_location` → `ospos_stock_locations.location_id`

---

#### 11. ospos_sales_items_taxes
Tax details for each sales line item.

**Columns:**
- `sale_id` (INTEGER) - Sale reference
- `item_id` (INTEGER) - Item reference
- `line` (INTEGER) - Line number
- `name` (VARCHAR(255)) - Tax name
- `percent` (DECIMAL(15,4)) - Tax percentage
- `tax_type` (INTEGER) - Tax type
- `rounding_code` (INTEGER) - Rounding method
- `cascade_tax` (INTEGER) - Cascade flag
- `cascade_sequence` (INTEGER) - Cascade order
- `item_tax_amount` (DECIMAL(15,4)) - Tax amount

**Primary Key:** Composite (`sale_id`, `item_id`, `line`, `name`, `percent`)

**Foreign Keys:**
- `item_id` → `ospos_items.item_id`

---

#### 12. ospos_sales_payments
Payment methods used in sales transactions.

**Columns:**
- `sale_id` (INTEGER) - Sale reference
- `payment_type` (VARCHAR(40)) - Payment method (cash, credit, etc.)
- `payment_amount` (DECIMAL(15,2)) - Amount paid

**Primary Key:** Composite (`sale_id`, `payment_type`)

**Foreign Keys:**
- `sale_id` → `ospos_sales.sale_id`

---

#### 13. ospos_sales_suspended
Suspended/parked sales transactions.

**Columns:**
- `sale_id` (PRIMARY KEY, AUTOINCREMENT)
- `sale_time` (TIMESTAMP) - Suspension timestamp
- `customer_id` (INTEGER) - Customer reference
- `employee_id` (INTEGER) - Employee reference
- `comment` (TEXT) - Notes
- `invoice_number` (VARCHAR(32)) - Invoice number
- `quote_number` (VARCHAR(32)) - Quote number
- `dinner_table_id` (INTEGER) - Restaurant table

**Foreign Keys:**
- `employee_id` → `ospos_employees.person_id`
- `customer_id` → `ospos_customers.person_id`
- `dinner_table_id` → `ospos_dinner_tables.dinner_table_id`

---

#### 14. ospos_sales_taxes
Aggregated tax information per sale (for India GST and similar systems).

**Columns:**
- `sales_taxes_id` (PRIMARY KEY, AUTOINCREMENT)
- `sale_id` (INTEGER) - Sale reference
- `tax_type` (INTEGER) - Tax type
- `tax_group` (VARCHAR(32)) - Tax group
- `sale_tax_basis` (DECIMAL(15,4)) - Tax basis amount
- `sale_tax_amount` (DECIMAL(15,4)) - Tax amount
- `print_sequence` (INTEGER) - Print order
- `name` (VARCHAR(255)) - Tax name
- `tax_rate` (DECIMAL(15,4)) - Tax rate
- `sales_tax_code` (VARCHAR(32)) - Tax code
- `rounding_code` (INTEGER) - Rounding method

---

### Supporting Tables (20 additional tables)

#### ospos_item_quantities
Stock quantities by location for each item.

**Columns:**
- `item_id`, `location_id` (COMPOSITE PRIMARY KEY)
- `quantity` (DECIMAL(15,3))

---

#### ospos_inventory
Inventory transaction log for stock movements.

**Columns:**
- `trans_id` (PRIMARY KEY, AUTOINCREMENT)
- `trans_items`, `trans_user`, `trans_location`
- `trans_date`, `trans_comment`, `trans_inventory`

---

#### ospos_items_taxes
Default tax rates for items.

**Columns:**
- `item_id`, `name`, `percent` (COMPOSITE PRIMARY KEY)

---

#### ospos_sales_suspended_items
Line items for suspended sales.

---

#### ospos_sales_suspended_items_taxes
Tax details for suspended sale items.

---

#### ospos_sales_suspended_payments
Payment details for suspended sales.

---

#### ospos_attribute_definitions
Custom attribute field definitions.

**Columns:**
- `definition_id` (PRIMARY KEY, AUTOINCREMENT)
- `definition_name`, `definition_type`, `definition_flags`
- `definition_fk`, `deleted`

---

#### ospos_attribute_values
Custom attribute values.

**Columns:**
- `attribute_id` (PRIMARY KEY, AUTOINCREMENT)
- `attribute_value`, `attribute_datetime`

---

#### ospos_attribute_links
Links attributes to items/sales/receivings.

**Columns:**
- `attribute_id`, `definition_id`, `item_id`, `sale_id`, `receiving_id`

---

#### ospos_expense_categories
Expense category master.

**Columns:**
- `expense_category_id` (PRIMARY KEY, AUTOINCREMENT)
- `category_name` (UNIQUE), `category_description`, `deleted`

---

#### ospos_expenses
Business expense tracking.

**Columns:**
- `expense_id` (PRIMARY KEY, AUTOINCREMENT)
- `date`, `amount`, `payment_type`
- `expense_category_id`, `description`, `employee_id`, `deleted`

---

#### ospos_cash_up
Cash register opening/closing records.

**Columns:**
- `cashup_id` (PRIMARY KEY, AUTOINCREMENT)
- `open_date`, `close_date`
- `open_amount_cash`, `transfer_amount_cash`
- `closed_amount_cash`, `closed_amount_card`, `closed_amount_check`
- `closed_amount_total`, `closed_amount_due`
- `open_employee_id`, `close_employee_id`
- `note`, `description`, `deleted`

---

#### ospos_dinner_tables
Restaurant table management.

**Columns:**
- `dinner_table_id` (PRIMARY KEY, AUTOINCREMENT)
- `name`, `status`, `deleted`

**Default Data:**
- ID 1: 'Delivery'
- ID 2: 'Take Away'

---

#### ospos_customers_packages
Customer loyalty tier definitions.

**Columns:**
- `package_id` (PRIMARY KEY, AUTOINCREMENT)
- `package_name`, `points_percent`, `deleted`

**Default Data:**
- Default (0%), Bronze (10%), Silver (20%), Gold (30%), Premium (50%)

---

#### ospos_customers_points
Customer loyalty points history.

**Columns:**
- `id` (PRIMARY KEY, AUTOINCREMENT)
- `person_id`, `package_id`, `sale_id`, `points_earned`

---

#### ospos_sales_reward_points
Reward points earned/used per sale.

**Columns:**
- `id` (PRIMARY KEY, AUTOINCREMENT)
- `sale_id`, `earned`, `used`

---

#### ospos_tax_codes
Tax code definitions.

**Columns:**
- `tax_code` (PRIMARY KEY)
- `tax_code_name`, `tax_code_type`, `city`, `state`

---

#### ospos_tax_categories
Product tax categories.

**Columns:**
- `tax_category_id` (PRIMARY KEY)
- `tax_category`, `tax_group_sequence`, `deleted`

---

#### ospos_tax_jurisdictions
Tax jurisdiction definitions (for complex tax systems).

**Columns:**
- `jurisdiction_id` (PRIMARY KEY, AUTOINCREMENT)
- `jurisdiction_name`, `tax_group`, `tax_type`
- `reporting_authority`, `tax_group_sequence`, `cascade_sequence`, `deleted`

---

#### ospos_tax_rates
Tax rate mappings between codes, categories, and jurisdictions.

**Columns:**
- `tax_rate_id` (PRIMARY KEY, AUTOINCREMENT)
- `rate_tax_code_id`, `rate_tax_category_id`, `rate_jurisdiction_id`
- `tax_rate`, `tax_rounding_code`

---

## SQLite Compatibility Notes

The script has been specifically adapted for SQLite with the following considerations:

1. **AUTO_INCREMENT**: Changed to `AUTOINCREMENT` (SQLite syntax)
2. **Data Types**:
   - `int(10)` → `INTEGER`
   - `tinyint(1)` → `INTEGER`
   - `timestamp` → `TIMESTAMP`
   - `decimal(15,2)` → `DECIMAL(15,2)` (SQLite stores as REAL)
   - `varchar(n)` → `VARCHAR(n)` (SQLite stores as TEXT)
3. **Foreign Keys**: Enabled via `PRAGMA foreign_keys = ON`
4. **Default Values**: Adapted for SQLite compatibility
5. **Indexes**: Created separately for better control
6. **Constraints**: Inline foreign keys used instead of ALTER TABLE

## Default Data Populated

The script automatically populates essential default data:

1. **ospos_stock_locations**: Default location 'stock' (ID: 1)
2. **ospos_dinner_tables**: 'Delivery' and 'Take Away' tables
3. **ospos_customers_packages**: 5 loyalty tiers (Default, Bronze, Silver, Gold, Premium)

## Verification

After running the script, verify with:

```bash
php verify_database.php
```

This will show:
- Total number of tables created
- Status of each critical table
- Default data record counts

## Schema Source

This schema is based on the official OSPOS database structure from:
- `app/Database/database.sql` - Main schema
- `app/Database/tables.sql` - Table definitions
- `app/Database/constraints.sql` - Foreign key constraints
- Migration scripts in `app/Database/Migrations/sqlscripts/` - Additional features

## Notes

- All tables use the `ospos_` prefix
- Soft deletes are implemented via `deleted` flag (0 = active, 1 = deleted)
- Foreign keys enforce referential integrity
- Indexes optimize query performance
- The schema supports multi-location inventory
- Advanced features include attributes, expenses, cash management, and complex tax calculations

## Troubleshooting

### Foreign Key Violations
If you encounter foreign key constraint errors, ensure that referenced tables exist and contain the required records before inserting data.

### Default Data Not Inserted
The script checks if default data already exists before inserting to prevent duplicates. If tables already contain data, default inserts are skipped.

### SQLite Specific Issues
- Ensure PHP PDO SQLite extension is enabled
- Check file permissions on the database file
- Verify the database path is correct

## License
This schema is based on Open Source Point of Sale (OSPOS), which is licensed under the MIT License.
