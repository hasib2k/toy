Single-product online store — folder structure

Tech stack: React (static build) frontend, PHP API backend, MySQL database

Top-level layout:

public_html/
├── index.php                  ← main website entry (static build target)
├── .htaccess                  ← routing & security (optional)
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── admin/
│   ├── index.php              ← admin login
│   ├── dashboard.php          ← admin dashboard
│   ├── orders.php             ← orders management
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── includes/
│       ├── header.php
│       ├── footer.php
│       └── auth_check.php
├── api/                       ← PHP API endpoints (REST-style)
├── config/                    ← DB + app configuration (keep out of web root if possible)
├── includes/                  ← shared helpers, auth, functions
├── uploads/                   ← user / product uploads
└── logs/                      ← server logs

website_src/                   ← React source (optional)
website_build/                 ← React static build output (copy here for deployment)

db/
└── sql/                       ← database schema, sample data (e.g. schema.sql)

Notes:
- Put React `build/` contents into `public_html/` (or `public_html/website/`) for static hosting.
- Protect `config/` and `db/` from public access on shared hosting.
- Use prepared statements and password hashing in PHP APIs.

You can add `.gitkeep` files to empty folders or start adding the starter files now.
