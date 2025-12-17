-- Database Schema for Restaurant Stock Recording System
-- Execute this SQL to create the database structure

CREATE DATABASE IF NOT EXISTS restaurant_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_stock;

-- Raw Materials Master Table
CREATE TABLE raw_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL COMMENT 'kg, bottles, pcs, ml, etc.',
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_material (material_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily Stock Records Table
CREATE TABLE daily_stock_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_date DATE NOT NULL,
    material_id INT NOT NULL,
    remaining_quantity DECIMAL(10,2) NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    employee_name VARCHAR(100) NOT NULL,
    employee_id VARCHAR(50) DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_locked TINYINT(1) DEFAULT 1 COMMENT 'Always locked after submission',
    UNIQUE KEY unique_daily_record (record_date, material_id),
    FOREIGN KEY (material_id) REFERENCES raw_materials(id) ON DELETE RESTRICT,
    INDEX idx_record_date (record_date),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Excel Export Log Table
CREATE TABLE excel_export_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    export_date DATE NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    records_count INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT DEFAULT NULL,
    INDEX idx_export_date (export_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample raw materials based on the provided document
-- These match the materials from the Excel template
INSERT INTO raw_materials (material_name, unit, display_order) VALUES
('นม', 'ml', 1),
('ช็อก', 'ml', 2),
('ดาร์ก', 'ml', 3),
('ไวท์', 'ml', 4),
('ชาไทย', 'ml', 5),
('ชาเขียว', 'ml', 6),
('ชานม', 'ml', 7),
('นมข้น', 'ml', 8),
('ไซรัปมิ้นท์', 'ml', 9),
('ไซรัปส้ม', 'ml', 10),
('ไซรัปสตรอว์เบอร์รี่', 'ml', 11),
('โคลด์บรูว์', 'ml', 12),
('น้ำเปล่า', 'ml', 13),
('ไซรัปทอง', 'ml', 14);

-- Create indexes for performance
CREATE INDEX idx_material_active ON raw_materials(is_active, display_order);
CREATE INDEX idx_record_employee ON daily_stock_records(employee_name);

-- Grant permissions (adjust username/password as needed)
-- CREATE USER 'stock_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT ALL PRIVILEGES ON restaurant_stock.* TO 'stock_user'@'localhost';
-- FLUSH PRIVILEGES;