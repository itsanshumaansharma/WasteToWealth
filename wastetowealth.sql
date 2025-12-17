-- ==============================
-- DATABASE
-- ==============================
CREATE DATABASE IF NOT EXISTS wastetowealth;
USE wastetowealth;

-- ==============================
-- USERS TABLE (Admin + Users)
-- ==============================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    coins INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin Account
INSERT INTO users (name, username, password, role)
VALUES ('Admin', 'admin', 'admin123', 'admin');

-- ==============================
-- WASTE UPLOADS TABLE
-- ==============================
DROP TABLE IF EXISTS waste_uploads;
CREATE TABLE waste_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    waste_type VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,

    verification_status ENUM('Pending','Verified','Rejected') DEFAULT 'Pending',
    verification_image VARCHAR(255),
    verified_type VARCHAR(100),
    estimated_weight FLOAT,
    verified_at TIMESTAMP NULL,

    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==============================
-- COIN TRANSACTIONS
-- ==============================
DROP TABLE IF EXISTS coin_transactions;
CREATE TABLE coin_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    waste_id INT,
    coins INT NOT NULL,
    transaction_type ENUM('earned','redeemed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (waste_id) REFERENCES waste_uploads(id) ON DELETE SET NULL
);

-- ==============================
-- COIN REDEEM REQUESTS
-- ==============================
DROP TABLE IF EXISTS coin_redeem;
CREATE TABLE coin_redeem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coins_redeemed INT NOT NULL,
    upi_id VARCHAR(100) NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    redeem_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
