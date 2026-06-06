-- ============================================================
-- Convenience Store POS System — Database Schema
-- Run this entire file in phpMyAdmin (SQL tab)
-- ============================================================

CREATE DATABASE IF NOT EXISTS pos_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pos_db;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','cashier') DEFAULT 'cashier',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Products
-- ------------------------------------------------------------
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  barcode VARCHAR(100) UNIQUE NOT NULL,
  name VARCHAR(150) NOT NULL,
  category_id INT,
  price DECIMAL(10,2) NOT NULL,
  cost_price DECIMAL(10,2) DEFAULT 0,
  stock INT DEFAULT 0,
  unit VARCHAR(30) DEFAULT 'pcs',
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Sales
-- ------------------------------------------------------------
CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  or_number VARCHAR(20) UNIQUE NOT NULL,
  cashier_id INT,
  subtotal DECIMAL(10,2),
  discount DECIMAL(10,2) DEFAULT 0,
  vat DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2),
  cash_tendered DECIMAL(10,2),
  change_amount DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cashier_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Sale Items
-- ------------------------------------------------------------
CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT,
  product_id INT,
  qty INT,
  unit_price DECIMAL(10,2),
  subtotal DECIMAL(10,2),
  FOREIGN KEY (sale_id) REFERENCES sales(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Settings (key-value store)
-- ------------------------------------------------------------
CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Default Settings
-- ------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
('store_name', 'My Convenience Store'),
('store_address', 'Dumaguete City, Negros Oriental'),
('store_tin', '000-000-000-000'),
('receipt_footer', 'Thank you for shopping with us!'),
('low_stock_threshold', '5');

-- ------------------------------------------------------------
-- Default Admin Account
-- Username: admin  |  Password: admin123
-- ------------------------------------------------------------
INSERT INTO users (name, username, password, role)
VALUES (
  'Admin',
  'admin',
  '$2y$12$mpGJKwWk/QKonOH/Y./3fOB16Jibpqzx78yXQ.x5.idB.L1Tjx17m',
  'admin'
);
