# Hostinger Deployment Guide (via GitHub)

This guide walks you through deploying the Babu Toys website to Hostinger shared hosting using GitHub.

---

## ✅ Project Audit Summary

| Component | Status | Notes |
|-----------|--------|-------|
| **File Structure** | ✅ Ready | `public_html/` matches Hostinger's web root |
| **Database Config** | ⚠️ Needs Update | Update credentials for production |
| **API Endpoints** | ✅ Ready | All use relative `__DIR__` paths |
| **Admin Panel** | ✅ Ready | Session-based auth works on shared hosting |
| **File Uploads** | ✅ Ready | Uses `assets/images/uploads/` with auto-create |
| **.htaccess** | ✅ Ready | Security rules + caching configured |
| **Hardcoded URLs** | ✅ None | No `localhost` references in code |

---

## 📋 Pre-Deployment Checklist

### 1. GitHub Repository Setup
Your repo should have this structure:
```
├── .gitignore
├── README.md
├── HOSTINGER_DEPLOYMENT.md (this file)
├── hostinger_schema.sql
├── db/
│   └── sql/
│       └── schema.sql
└── public_html/          ← This folder maps to Hostinger's web root
    ├── index.php
    ├── .htaccess
    ├── admin/
    ├── api/
    ├── assets/
    ├── config/
    └── includes/
```

### 2. Files to Update Before Going Live

#### A. Database Credentials (`public_html/config/database.php`)
```php
// PRODUCTION (Hostinger) - Update these values:
$DB_HOST = 'localhost';                    // Keep as localhost for Hostinger
$DB_NAME = 'u123456789_babutoys';          // Your Hostinger database name
$DB_USER = 'u123456789_admin';             // Your Hostinger database user
$DB_PASS = 'YourSecurePassword123!';       // Your Hostinger database password
```

#### B. Admin Credentials (`public_html/admin/index.php`)
Change the default admin login (line 18-19):
```php
$ADMIN_USER = getenv('ADMIN_USER') ?: 'your_admin_username';
$ADMIN_PASS = getenv('ADMIN_PASS') ?: 'YourSecurePassword!';
```

---

## 🚀 Step-by-Step Deployment

### Step 1: Create Database on Hostinger

1. Log in to **Hostinger hPanel**
2. Go to **Databases** → **MySQL Databases**
3. Create a new database:
   - Database name: `babutoys` (Hostinger will prefix it, e.g., `u123456789_babutoys`)
   - Username: `admin` (becomes `u123456789_admin`)
   - Password: Use a strong password
4. **Save these credentials** - you'll need them

### Step 2: Import Database Schema

1. In hPanel, go to **Databases** → **phpMyAdmin**
2. Select your new database
3. Click **Import** tab
4. Upload `hostinger_schema.sql` from this repo
5. Click **Go** to import

### Step 3: Connect GitHub to Hostinger

1. In hPanel, go to **Files** → **Git**
2. Click **Create new repository** or **Import from GitHub**
3. Connect your GitHub account if not already connected
4. Select your repository
5. Set deployment settings:
   - **Branch**: `main` (or your production branch)
   - **Auto-deploy**: Enable for automatic updates
   - **Deployment path**: `/public_html` 
   
   ⚠️ **Important**: Your repo's `public_html/` folder contents should deploy to Hostinger's `/public_html/`

### Step 4: Configure Deployment Path

Since your repo has `public_html/` as a subfolder, configure Hostinger to deploy correctly:

**Option A: Use Hostinger's Git settings**
- Set the deployment subdirectory to `public_html`

**Option B: Use a deployment script**
Create `.github/workflows/deploy.yml` for GitHub Actions (if needed)

### Step 5: Update Configuration Files

After first deployment, update `config/database.php` with your Hostinger credentials:

1. In hPanel, go to **Files** → **File Manager**
2. Navigate to `public_html/config/database.php`
3. Edit and update the credentials
4. Save

Or update in your local repo and push to GitHub.

### Step 6: Create Upload Directories

1. Visit `https://yourdomain.com/_setup_uploads.php`
2. This will create necessary upload directories
3. **Delete this file after setup** for security:
   - In File Manager, delete `public_html/_setup_uploads.php`

### Step 7: Enable HTTPS

1. In hPanel, go to **Security** → **SSL**
2. Enable Free SSL (Let's Encrypt)
3. Wait for SSL to be active
4. Edit `public_html/.htaccess` and uncomment the HTTPS redirect:

```apache
# Force HTTPS (uncomment after SSL is enabled)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### Step 8: Test Everything

1. **Homepage**: `https://yourdomain.com/`
2. **Admin Login**: `https://yourdomain.com/admin/`
3. **Create Test Order**: Submit the order form
4. **Admin Dashboard**: Check orders appear
5. **Content Management**: Test adding/editing banners, images, etc.
6. **File Uploads**: Upload a product image or video

---

## 🔒 Security Checklist

- [ ] Changed default admin password
- [ ] Database credentials not in Git (use `.gitignore`)
- [ ] Deleted `_setup_uploads.php` after setup
- [ ] HTTPS enabled and forced
- [ ] `.htaccess` security rules active

---

## 📁 File Permissions (if needed)

If uploads fail, set these permissions in File Manager:

| Directory | Permission |
|-----------|------------|
| `assets/images/uploads/` | 755 |
| `assets/videos/uploads/` | 755 |
| `config/` | 755 |

---

## 🔄 Updating the Site

### Via GitHub (Recommended)
1. Make changes locally
2. Commit and push to `main` branch
3. Hostinger auto-deploys (if enabled)

### Manual Update
1. Go to hPanel → Files → Git
2. Click **Pull** to get latest changes

---

## 🐛 Troubleshooting

### Database Connection Error
- Verify credentials in `config/database.php`
- Check database exists in hPanel → Databases
- Ensure user has permissions on the database

### 500 Internal Server Error
- Check `.htaccess` syntax
- Look at error logs: hPanel → Advanced → Error Logs

### Uploads Not Working
- Run `_setup_uploads.php` to create directories
- Check folder permissions (755)
- Verify PHP `upload_max_filesize` in hPanel → PHP Configuration

### Admin Login Not Working
- Clear browser cookies
- Check session settings in hPanel → PHP Configuration
- Verify credentials in `admin/index.php`

### Images Not Showing
- Check image paths in database (should be relative: `assets/images/uploads/...`)
- Verify files exist in File Manager
- Check file permissions

---

## 📞 Support

- **Hostinger Help**: https://support.hostinger.com
- **PHP Errors**: Check hPanel → Advanced → Error Logs

---

## 🎉 You're Live!

Once deployed, your site will be available at:
- **Website**: `https://yourdomain.com/`
- **Admin Panel**: `https://yourdomain.com/admin/`

Default admin credentials (change these!):
- Username: `admin`
- Password: `admin123`
