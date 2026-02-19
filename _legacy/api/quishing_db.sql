-- Quishing Defender Database Schema
-- Import file ini ke phpMyAdmin

CREATE DATABASE IF NOT EXISTS quishing_defender;
USE quishing_defender;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    token VARCHAR(255),
    token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Scan history table
CREATE TABLE IF NOT EXISTS scan_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    url TEXT NOT NULL,
    domain VARCHAR(255),
    risk_level ENUM('SAFE', 'SUSPICIOUS', 'DANGEROUS', 'UNKNOWN') NOT NULL,
    risk_score INT DEFAULT 0,
    threats JSON,
    warnings JSON,
    safe_indicators JSON,
    api_results JSON,
    scan_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- API keys table (for admin)
CREATE TABLE IF NOT EXISTS api_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    api_key TEXT NOT NULL,
    provider ENUM('virustotal', 'google_safe_browsing', 'other') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Statistics table (cached stats)
CREATE TABLE IF NOT EXISTS statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE UNIQUE NOT NULL,
    total_scans INT DEFAULT 0,
    safe_count INT DEFAULT 0,
    suspicious_count INT DEFAULT 0,
    dangerous_count INT DEFAULT 0
);

-- Indexes
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_history_user ON scan_history(user_id);
CREATE INDEX idx_history_date ON scan_history(scan_date);
CREATE INDEX idx_history_level ON scan_history(risk_level);

-- Insert default admin
INSERT INTO users (email, password_hash, name, role) VALUES 
('admin@quishing.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
-- Default password: password
