# Babu Toys - Hostinger Deployment Guide

## Quick Deployment Steps

### Step 1: Create Database on Hostinger
1. Log in to **Hostinger hPanel**
2. Go to **Databases** → **MySQL Databases**
3. Create a new database:
   - Database name: `toystore` (will become `u123456789_toystore`)
   - Username: `admin` (will become `u123456789_admin`)
   - Password: Create a strong password
4. **Note down these credentials!**

### Step 2: Import Database Schema
1. Go to **Databases** → **phpMyAdmin**
2. Select your new database from left panel
3. Click **Import** tab
4. Upload `hostinger_schema.sql` file
5. Click **Go**

### Step 3: Update Database Configuration
1. Open `public_html/config/database.php`
2. Update these lines with YOUR Hostinger credentials:
```php
$DB_HOST = 'localhost';
$DB_NAME = 'u123456789_toystore';     // Your actual database name
$DB_USER = 'u123456789_admin';         // Your actual username
$DB_PASS = 'YourActualPassword';       // Your actual password
```

### Step 4: Upload Files
**Option A: File Manager (Easy)**
1. Go to **Files** → **File Manager**
2. Open `public_html` folder
3. Delete any existing files (index.html, etc.)
4. Click **Upload** → Select all files from `website_upload.zip`
5. Extract the zip file

**Option B: FTP (For large files)**
1. Go to **Files** → **FTP Accounts**
2. Note FTP credentials
3. Use FileZilla to connect
4. Upload contents of `public_html` folder to Hostinger's `public_html`

### Step 5: Enable SSL (HTTPS)
1. Go to **Security** → **SSL**
2. Enable free SSL certificate
3. Wait for activation (up to 24 hours)
4. Edit `.htaccess` and uncomment the HTTPS redirect section

### Step 6: Test Your Website
- Main store: `https://yourdomain.com`
- Admin panel: `https://yourdomain.com/admin`
- Default login: `admin` / `admin123`

## ⚠️ IMPORTANT: Security Checklist

After deployment, do these immediately:

1. **Change admin password** - Login to admin and change password
2. **Update database.php** - Remove any test credentials
3. **Enable HTTPS** - Uncomment redirect in `.htaccess`
4. **Test everything** - Place a test order, check admin panel

## File Structure
```
public_html/
├── index.html          # Main store page
├── .htaccess           # Security & redirects
├── admin/              # Admin panel
│   ├── index.php       # Login page
│   ├── dashboard.php   # Dashboard
│   ├── orders.php      # Orders management
│   └── assets/         # Admin CSS/JS
├── api/                # Backend APIs
│   ├── create-order.php
│   ├── orders.php
│   └── admin/
├── assets/             # Store CSS/JS/images
└── config/
    └── database.php    # Database configuration
```

## Troubleshooting

**500 Internal Server Error**
- Check database credentials in `config/database.php`
- Verify database exists and tables are imported

**Orders not saving**
- Check if `orders` table exists
- Verify `products` table has at least one product

**Admin login not working**
- Verify `users` table has admin user
- Password should be: `admin123` (hashed in database)

## Support
For issues, check the browser console (F12) and PHP error logs in Hostinger.
