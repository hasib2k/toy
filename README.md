# Babu Toys — Single-Product CMS

A modern, Hostinger-ready, single-product e-commerce CMS with full admin control, dynamic content, and secure PHP+MySQL backend.

---

## 🚀 Project Status (2026-01-28)

**Live-ready, Hostinger-compatible, full CMS for a single-product store.**

### ✨ Key Features
- Modern PHP+MySQL backend, REST-style API
- All content (banners, product, reviews, features, videos, footer, etc) editable from admin
- File/image/video uploads with progress, safe fallback images
- Admin panel with tab persistence, toast notifications, and secure session auth
- Features grid, reviews gallery, and order form all dynamic
- WhatsApp order integration, dynamic phone/price/shipping
- Security: .htaccess rules, no hardcoded localhost, uploads protected, HTTPS ready
- Hostinger deployment: ready for GitHub auto-deploy, with [HOSTINGER_DEPLOYMENT.md](HOSTINGER_DEPLOYMENT.md) and [hostinger_schema.sql](hostinger_schema.sql)

### ✅ Deployment Checklist
- [x] All code and assets in `public_html/` (Hostinger web root)
- [x] Database config supports env vars and Hostinger credentials
- [x] All uploads use `assets/images/uploads/` and `assets/videos/uploads/` (auto-created)
- [x] `.htaccess` enables HTTPS and security headers
- [x] Admin login/password can be changed after deploy
- [x] All content managed via admin UI (no code edits needed for updates)
- [x] Database schema and default data in `hostinger_schema.sql`
- [x] Full deployment guide in `HOSTINGER_DEPLOYMENT.md`

### 📦 How to Deploy
See [HOSTINGER_DEPLOYMENT.md](HOSTINGER_DEPLOYMENT.md) for step-by-step instructions for Hostinger shared hosting via GitHub.

---

## 📁 Folder Structure

```
├── .gitignore
├── README.md
├── HOSTINGER_DEPLOYMENT.md
├── hostinger_schema.sql
├── db/
│   └── sql/
│       └── schema.sql
└── public_html/
    ├── index.php
    ├── .htaccess
    ├── admin/
    ├── api/
    ├── assets/
    │   ├── css/
    │   ├── js/
    │   ├── images/
    │   │   └── uploads/
    │   └── videos/
    │       └── uploads/
    ├── config/
    └── includes/
```

---

## 📝 Notes
- All code, assets, and uploads are ready for production.
- No hardcoded URLs or localhost dependencies remain.
- All sensitive config and uploads are protected by .htaccess and .gitignore.
- Admin and frontend are fully dynamic and editable from the CMS.
- For any issues, see the deployment guide or open an issue.
