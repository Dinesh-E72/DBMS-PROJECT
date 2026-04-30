-- ============================================================
-- Library Management System - Database Setup
-- Compatible with XAMPP / MySQL 5.7+
-- ============================================================

CREATE DATABASE IF NOT EXISTS library_ms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_ms;

-- ============================================================
-- TABLE: publishers
-- ============================================================
CREATE TABLE IF NOT EXISTS publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: staff  (used for login / authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stored as MD5 hash
    role ENUM('admin','librarian') DEFAULT 'librarian',
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: books
-- ============================================================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(30) UNIQUE,
    publisher_id INT,
    category VARCHAR(100),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    price DECIMAL(10,2) DEFAULT 0.00,
    published_year YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: readers  (library members)
-- ============================================================
CREATE TABLE IF NOT EXISTS readers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    member_id VARCHAR(30) UNIQUE,
    membership_date DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: book_issues
-- ============================================================
CREATE TABLE IF NOT EXISTS book_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    reader_id INT NOT NULL,
    staff_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    fine DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('issued','returned','overdue') DEFAULT 'issued',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (reader_id) REFERENCES readers(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Publishers
INSERT INTO publishers (name, address, phone, email) VALUES
('Pearson Education', '221 River St, Hoboken, NJ', '555-0101', 'info@pearson.com'),
('Oxford University Press', 'Great Clarendon St, Oxford', '555-0102', 'info@oup.com'),
('McGraw-Hill', '1221 Avenue of the Americas, NY', '555-0103', 'info@mgh.com'),
('Wiley', '111 River Street, Hoboken, NJ', '555-0104', 'info@wiley.com'),
('O Reilly Media', '1005 Gravenstein Hwy N, Sebastopol', '555-0105', 'info@oreilly.com');

-- Staff (passwords are MD5 hashes)
-- admin / admin123   librarian / lib123
INSERT INTO staff (name, username, password, role, phone, email) VALUES
('Admin User', 'admin', MD5('admin123'), 'admin', '9876543210', 'admin@library.com'),
('John Smith', 'librarian1', MD5('lib123'), 'librarian', '9876543211', 'john@library.com'),
('Jane Doe', 'librarian2', MD5('lib456'), 'librarian', '9876543212', 'jane@library.com');

-- Books
INSERT INTO books (title, author, isbn, publisher_id, category, total_copies, available_copies, price, published_year) VALUES
('Database System Concepts', 'Abraham Silberschatz', '978-0073523323', 3, 'Computer Science', 5, 4, 89.99, 2019),
('Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 2, 'Computer Science', 4, 3, 95.00, 2009),
('Clean Code', 'Robert C. Martin', '978-0132350884', 1, 'Programming', 3, 3, 45.50, 2008),
('The Pragmatic Programmer', 'David Thomas', '978-0135957059', 1, 'Programming', 3, 2, 49.99, 2019),
('Design Patterns', 'Gang of Four', '978-0201633610', 1, 'Computer Science', 2, 2, 55.00, 1994),
('Learning Python', 'Mark Lutz', '978-1449355739', 5, 'Programming', 4, 4, 62.00, 2013),
('JavaScript: The Good Parts', 'Douglas Crockford', '978-0596517748', 5, 'Programming', 3, 3, 29.99, 2008),
('Operating System Concepts', 'Abraham Silberschatz', '978-1119800361', 4, 'Computer Science', 3, 2, 79.99, 2018),
('Computer Networks', 'Andrew Tanenbaum', '978-0132126953', 1, 'Networking', 2, 2, 74.50, 2010),
('Artificial Intelligence', 'Stuart Russell', '978-0134610993', 1, 'AI/ML', 2, 2, 99.00, 2020);

-- Readers
INSERT INTO readers (name, email, phone, address, member_id, membership_date, status) VALUES
('Alice Johnson', 'alice@email.com', '8001112222', '12 Maple St', 'MEM001', '2024-01-10', 'active'),
('Bob Williams', 'bob@email.com', '8001112223', '34 Oak Ave', 'MEM002', '2024-02-15', 'active'),
('Carol Davis', 'carol@email.com', '8001112224', '56 Pine Rd', 'MEM003', '2024-03-01', 'active'),
('David Brown', 'david@email.com', '8001112225', '78 Elm St', 'MEM004', '2024-01-20', 'active'),
('Eva Wilson', 'eva@email.com', '8001112226', '90 Cedar Ln', 'MEM005', '2024-04-05', 'active'),
('Frank Miller', 'frank@email.com', '8001112227', '11 Birch Blvd', 'MEM006', '2024-03-10', 'inactive');

-- Book Issues (sample data)
INSERT INTO book_issues (book_id, reader_id, staff_id, issue_date, due_date, return_date, fine, status) VALUES
(1, 1, 2, '2026-04-01', '2026-04-15', NULL, 0.00, 'issued'),
(2, 2, 2, '2026-04-02', '2026-04-16', NULL, 0.00, 'issued'),
(3, 3, 1, '2026-03-01', '2026-03-15', '2026-03-14', 0.00, 'returned'),
(4, 4, 1, '2026-03-10', '2026-03-24', '2026-03-25', 10.00, 'returned'),
(8, 5, 2, '2026-03-20', '2026-04-03', NULL, 0.00, 'overdue');
