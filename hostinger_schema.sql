-- =============================================
-- Babu Toys Database Schema for Hostinger
-- =============================================
-- 
-- INSTRUCTIONS:
-- 1. Go to Hostinger hPanel → Databases → phpMyAdmin
-- 2. Select your database from the left panel
-- 3. Click "Import" tab at the top
-- 4. Choose this file and click "Go"
-- =============================================

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users table (for admin login)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  email VARCHAR(191) DEFAULT NULL,
  password VARCHAR(255) DEFAULT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_key VARCHAR(100) NOT NULL UNIQUE,
  product_id INT NOT NULL,
  name VARCHAR(191) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address TEXT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INSERT DEFAULT DATA
-- =============================================

-- Default product (Learning Toy)
INSERT INTO products (name, description, price, stock) VALUES
('সোনামণিদের বাংলা ইংরেজি শেখার লার্নিং এন্ড প্লেয়িং টয়', 
 'এই লার্নিং এন্ড প্লেয়িং টয়টি শিশুদের জন্য বিশেষভাবে ডিজাইন করা হয়েছে যাতে তারা খেলার ছলে বাংলা ও ইংরেজি অক্ষর, সংখ্যা, শব্দ শিখতে পারে।', 
 990.00, 
 100);

-- Default admin user
-- Username: admin
-- Password: admin123 (change this after first login!)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@babutoys.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =============================================
-- IMPORTANT: After importing, change the admin password!
-- Go to Admin Panel → Login with admin/admin123
-- Then update password in database or add password change feature
-- =============================================
