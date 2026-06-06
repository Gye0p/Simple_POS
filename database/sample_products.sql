-- Optional sample data for testing the cashier screen
-- Run in phpMyAdmin after pos_db.sql

USE pos_db;

INSERT INTO categories (name) VALUES
('Beverages'),
('Snacks'),
('Instant Noodles'),
('Personal Care');

INSERT INTO products (barcode, name, category_id, price, cost_price, stock, unit) VALUES
('4800000001001', 'Coca-Cola 1.5L', 1, 65.00, 55.00, 24, 'bottle'),
('4800000001002', 'Royal Tru-Orange 1.5L', 1, 60.00, 50.00, 18, 'bottle'),
('4800000001003', 'C2 Green Tea 500ml', 1, 20.00, 15.00, 36, 'bottle'),
('4800000002001', 'Chippy Corn Chips', 2, 10.00, 7.00, 50, 'pack'),
('4800000002002', 'Piattos Cheese', 2, 15.00, 11.00, 40, 'pack'),
('4800000002003', 'Nova Country Cheddar', 2, 12.00, 9.00, 3, 'pack'),
('4800000003001', 'Lucky Me Pancit Canton', 3, 15.00, 11.00, 48, 'pack'),
('4800000003002', 'Nissin Cup Noodles', 3, 45.00, 35.00, 20, 'cup'),
('4800000004001', 'Safeguard Soap 135g', 4, 45.00, 35.00, 15, 'bar'),
('4800000004002', 'Sunsilk Shampoo Sachet', 4, 8.00, 5.00, 2, 'sachet');
