# 📚 LibraryMS — Library Management System

**Stack:** PHP (procedural) + MySQL + HTML/CSS/JS  
**Compatible with:** XAMPP on Windows/Linux/Mac

---

## 📁 Final Folder Structure

```
library_ms/
├── assets/
│   ├── css/style.css          ✅ Main stylesheet
│   └── js/main.js             ✅ Global JavaScript
├── auth/
│   ├── login.php              ✅ Login page
│   └── logout.php             ✅ Logout handler
├── books/
│   ├── index.php              ✅ List books
│   ├── add.php                ✅ Add book
│   ├── edit.php               ✅ Edit book
│   └── delete.php             ✅ Delete book
├── readers/
│   ├── index.php              ✅ List readers
│   ├── add.php                ✅ Add reader
│   ├── edit.php               ✅ Edit reader
│   └── delete.php             ✅ Delete reader
├── staff/
│   ├── index.php              ✅ List staff
│   ├── add.php                ✅ Add staff
│   ├── edit.php               ✅ Edit staff
│   └── delete.php             ✅ Delete staff
├── publishers/
│   ├── index.php              ✅ List publishers
│   ├── add.php                ✅ Add publisher
│   ├── edit.php               ✅ Edit publisher
│   └── delete.php             ✅ Delete publisher
├── issues/
│   ├── index.php              ✅ All issues (with filter tabs)
│   ├── issue.php              ✅ Issue a book
│   └── return.php             ✅ Return a book + fine calc
├── reports/
│   └── index.php              ✅ Summary + detail reports
├── config/
│   └── db.php                 ✅ Database connection
├── includes/
│   ├── header.php             ✅ HTML head + topbar
│   ├── sidebar.php            ✅ Navigation sidebar
│   └── footer.php             ✅ Footer + JS loader
├── database/
│   └── library.sql            ✅ Full schema + sample data
└── index.php                  ✅ Dashboard
```

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 — Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL**

### Step 2 — Copy the Project
1. Copy the `library_ms` folder to:
   ```
   C:\xampp\htdocs\library_ms
   ```

### Step 3 — Create the Database

**Option A: Via phpMyAdmin (Recommended)**
1. Open browser → `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Type database name: `library_ms` → Click **Create**
4. Click the `library_ms` database → Go to **Import** tab
5. Click **"Choose File"** → Select `library_ms/database/library.sql`
6. Click **"Go"** → ✅ Done!

**Option B: Via MySQL Command Line**
```bash
cd C:\xampp\mysql\bin
mysql -u root -p
```
Then run:
```sql
SOURCE C:/xampp/htdocs/library_ms/database/library.sql;
```

### Step 4 — Verify DB Config
Open `config/db.php` and confirm settings match your XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // XAMPP default
define('DB_PASS', '');       // No password by default
define('DB_NAME', 'library_ms');
```

### Step 5 — Access the App
Open your browser and go to:
```
http://localhost/library_ms/
```

---

## 🔐 Login Credentials (Sample Data)

| Role      | Username     | Password  |
|-----------|-------------|-----------|
| Admin     | `admin`      | `admin123` |
| Librarian | `librarian1` | `lib123`   |
| Librarian | `librarian2` | `lib456`   |

---

## ✨ Features

| Module          | Features                                         |
|-----------------|--------------------------------------------------|
| **Auth**        | Login/Logout with session management             |
| **Dashboard**   | 6 stat cards + recent issues + quick actions     |
| **Books**       | CRUD + availability tracking + search            |
| **Readers**     | CRUD + auto member ID + status toggle            |
| **Staff**       | CRUD + role management + password change         |
| **Publishers**  | CRUD + book count display                        |
| **Issue Book**  | Availability check + duplicate guard + auto due date |
| **Return Book** | Fine calculation (₹5/day) + copy restore        |
| **Reports**     | Summary, Issued, Returned, Overdue, Fines + Print |

---

## 💡 Fine Calculation

- Rate: **₹5 per overdue day**
- Calculated automatically on return
- Configurable in `issues/return.php` → `$FINE_PER_DAY`

---

## 🗄️ Database Tables

| Table         | Description                     | Foreign Keys           |
|---------------|---------------------------------|------------------------|
| `staff`       | Login users (admin/librarian)   | —                      |
| `publishers`  | Book publishers                 | —                      |
| `books`       | Book catalog                    | `publisher_id → publishers` |
| `readers`     | Library members                 | —                      |
| `book_issues` | Issue & return records          | `book_id`, `reader_id`, `staff_id` |

---

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+ (procedural, no frameworks)
- **Database:** MySQL 5.7+ via MySQLi extension
- **Frontend:** HTML5, Vanilla CSS, Vanilla JavaScript
- **Fonts:** Inter (Google Fonts)
- **Server:** Apache (XAMPP)
