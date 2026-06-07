# Library Management System

A complete, production-ready Library Management System built with PHP 8+, MySQL, and Vanilla JS.

## Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript, Chart.js
- **Backend:** PHP 8+ (MVC Architecture)
- **Database:** MySQL (XAMPP)
- **Auth:** Session-based with CSRF, XSS, SQL Injection protection

## User Roles
| Role | Access |
|------|--------|
| Super Admin | Full system control |
| Librarian | Books, members, issues, returns, fines |
| Assistant Librarian | View/add books, issue/return only |
| Member | Search, reserve, view own history |

## Setup

### 1. Database
```sql
-- Open phpMyAdmin or run:
mysql -u root -p < database/schema.sql
```

### 2. Configuration
Edit `config/database.php` if your MySQL credentials differ from defaults (root / no password).

Edit `config/config.php` — update `BASE_URL` if your folder name differs.

### 3. Access
Open: `http://localhost/Library%20ms/Libarary-ms/`

### Default Login
| Email | Password | Role |
|-------|----------|------|
| admin@library.com | password | Super Admin |

> Change the password immediately after first login via Profile → Change Password.

## Features
- ✅ 4 role-based dashboards (Super Admin, Librarian, Assistant, Member)
- ✅ Complete Book CRUD with cover images, PDFs, barcodes
- ✅ Member registration with membership cards
- ✅ Book issue/return with automatic fine calculation
- ✅ Reservation system (Pending → Approved → Collected)
- ✅ Fine management (Pay / Waive)
- ✅ Reports with Chart.js charts
- ✅ CSV export for books, members, transactions
- ✅ Activity logs & audit trail
- ✅ Database backup/restore
- ✅ Dark/Light mode
- ✅ Mobile responsive
- ✅ CSRF, XSS, SQL injection protection
- ✅ Account lockout after failed attempts
- ✅ Forgot/reset password
- ✅ Real-time AJAX search for issue/return

## Project Structure
```
/
├── api/               AJAX endpoints (search, export, notifications)
├── assets/
│   ├── css/style.css  Main stylesheet (light + dark mode)
│   └── js/app.js      Frontend logic
├── backups/           Database backup files
├── config/            Database + app config
├── controllers/       Business logic layer
├── database/          SQL schema
├── includes/          Shared components (sidebar, navbar, helpers)
├── models/            Data access layer (PDO)
├── uploads/           User-uploaded files
│   ├── books/         Book cover images
│   ├── pdfs/          Book PDF files
│   └── profiles/      User photos
└── views/
    ├── admin/         Super Admin pages
    ├── assistant/     Assistant dashboard
    ├── auth/          Login, forgot password, reset
    ├── librarian/     Librarian dashboard
    └── member/        Member portal
```
