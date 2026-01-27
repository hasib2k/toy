-- =====================================================
-- Babu Toys - Hostinger Database Schema
-- Import this file via phpMyAdmin on Hostinger
-- =====================================================

-- Create tables with proper structure for Hostinger

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  email VARCHAR(191) DEFAULT NULL,
  password VARCHAR(255) DEFAULT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  INDEX idx_product_id (product_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings
CREATE TABLE IF NOT EXISTS site_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  setting_type VARCHAR(50) DEFAULT 'text',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banners (top carousel)
CREATE TABLE IF NOT EXISTS banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(500) NOT NULL,
  subtitle VARCHAR(500) DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  display_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Images (hero carousel)
CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT DEFAULT 1,
  image_path VARCHAR(255) NOT NULL,
  alt_text VARCHAR(255),
  display_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Details
CREATE TABLE IF NOT EXISTS product_details (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL DEFAULT 1,
  name VARCHAR(500),
  bengali_name VARCHAR(500),
  description TEXT,
  price DECIMAL(10,2) DEFAULT 990.00,
  discount_price DECIMAL(10,2) DEFAULT NULL,
  features TEXT,
  accordion_title VARCHAR(500),
  accordion_description TEXT,
  features_heading VARCHAR(255),
  features_items TEXT,
  order_image VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews (customer images)
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(191) DEFAULT 'Customer',
  customer_image VARCHAR(255) NOT NULL,
  rating INT DEFAULT 5,
  review_text TEXT,
  display_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Videos
CREATE TABLE IF NOT EXISTS videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  video_path VARCHAR(255) NOT NULL,
  thumbnail_path VARCHAR(255),
  caption TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  display_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Links
CREATE TABLE IF NOT EXISTS social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(50) NOT NULL,
  url VARCHAR(500) NOT NULL,
  icon_path VARCHAR(255),
  display_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature Cards
CREATE TABLE IF NOT EXISTS feature_cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  title VARCHAR(500),
  display_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT DATA
-- =====================================================

-- Insert default product
INSERT INTO products (id, name, description, price, stock) VALUES
(1, 'Intelligence Talking Book', 'Interactive learning toy for children', 990.00, 100);

-- Insert default product details
INSERT INTO product_details (product_id, name, bengali_name, description, price, discount_price, features) VALUES
(1, 'Intelligence Talking Book', 'সোনামণিদের বাংলা ইংরেজি শেখার লার্নিং এন্ড প্লেয়িং টয়', 'এই লার্নিং টয় দিয়ে শিশুরা যেমন খেলতে পারবে ঠিক তেমনি শিখতেও পারবে।', 990.00, 1650.00, 'বাংলা ও ইংরেজি শেখা|ইন্টারঅ্যাক্টিভ সাউন্ড|রঙিন ছবি|টেকসই মেটেরিয়াল');

-- Insert default banners
INSERT INTO banners (title, subtitle, display_order, is_active) VALUES
('সোনামণিদের বাংলা ইংরেজি শেখার লার্নিং এন্ড প্লেয়িং টয়', NULL, 1, TRUE),
('সারাদেশে ক্যাশ অন ডেলিভারি', NULL, 2, TRUE),
('৪০% ছাড়ে এখনই অর্ডার করুন!', NULL, 3, TRUE),
('প্রতিটি সন্তুষ্ট গ্রাহক আমাদের অগ্রগতির প্রমাণ', NULL, 4, TRUE),
('বাচ্চাদের মোবাইল আসক্তি দূর করুন', NULL, 5, TRUE);

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'Babu Toys — বাবু টয়', 'text'),
('whatsapp_number', '+8801619703227', 'text'),
('phone_number', '+8801619703227', 'text'),
('shipping_inside_dhaka', '60', 'number'),
('shipping_outside_dhaka', '120', 'number'),
('cod_note', '🛒 ক্যাশ অন ডেলিভারি। পণ্য হাতে পেয়ে টাকা পরিশোধ করুন।', 'text'),
('footer_text', '© 2026 Babu Toys - All rights reserved.', 'text');

-- Insert default social links
INSERT INTO social_links (platform, url, icon_path, display_order, is_active) VALUES
('Facebook', 'https://www.facebook.com/share/1BRiT1FXcY/', 'assets/images/icons8-facebook.svg', 1, TRUE),
('YouTube', 'https://m.youtube.com/@BabuToysYT', 'assets/images/icons8-youtube.svg', 2, TRUE),
('TikTok', 'https://www.tiktok.com/@babutoys.com/', 'assets/images/icons8-tiktok.svg', 3, TRUE);
