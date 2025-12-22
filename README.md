# 🏪 Hazel Stock Management System

ระบบจัดการสต็อกวัตถุดิบสำหรับร้าน Hazel – Beverages & Appetizers

## 🚀 Features

- 📊 บันทึกสต็อกวัตถุดิบรายวัน
- 📸 อัปโหลดรูปภาพประกอบการบันทึก
- 📋 ส่งออกข้อมูลเป็น Excel
- 👥 จัดการข้อมูลพนักงาน
- 📦 จัดการข้อมูลวัตถุดิบ
- ⏰ ระบบ Cron Job สำหรับส่งออกอัตโนมัติ

## 🛠️ Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd hazel-stock
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
# Import database schema
mysql -u username -p database_name < database.sql
```

### 4. Environment Variables
สร้างไฟล์ `.env` หรือตั้งค่า environment variables:

```env
DB_HOST=your-database-host
DB_PORT=3306
DB_NAME=your-database-name
DB_USER=your-username
DB_PASS=your-password
```

### 5. Test Connection
เปิด `http://your-domain/test_db.php` เพื่อทดสอบการเชื่อมต่อฐานข้อมูล

## 📁 Project Structure

```
hazel-stock/
├── api/                    # API endpoints
│   ├── export-daily-stock.php
│   ├── export-excel.php
│   ├── generate-excel.php
│   ├── get-materials.php
│   ├── get-today-record.php
│   ├── submit-stock.php
│   └── upload-photo.php
├── assets/                 # Static assets
├── cron/                   # Cron job scripts
│   └── daily-excel-export.php
├── css/                    # Stylesheets
├── excel-exports/          # Generated Excel files
├── js/                     # JavaScript files
├── materials/              # Material management
│   └── add.php
├── stock-photos/           # Uploaded photos
├── vendor/                 # Composer dependencies
├── config.php              # Database configuration
├── database.sql            # Database schema
├── index.php               # Main application
└── test_db.php            # Database connection test
```

## 🔧 API Endpoints

### Stock Management
- `POST /api/submit-stock.php` - บันทึกข้อมูลสต็อก
- `GET /api/get-today-record.php` - ตรวจสอบการบันทึกวันนี้
- `GET /api/export-daily-stock.php?date=YYYY-MM-DD` - ส่งออกข้อมูลสต็อก

### Materials
- `GET /api/get-materials.php` - ดึงรายการวัตถุดิบ

### File Upload
- `POST /api/upload-photo.php` - อัปโหลดรูปภาพ

### Excel Export
- `GET /api/export-excel.php?date=YYYY-MM-DD` - ส่งออก Excel
- `GET /api/generate-excel.php?date=YYYY-MM-DD` - สร้าง Excel (testing)

## 🗄️ Database Schema

### Tables
- `employees` - ข้อมูลพนักงาน
- `raw_materials` - ข้อมูลวัตถุดิบ
- `daily_stock_records` - บันทึกสต็อกรายวัน
- `excel_export_log` - ประวัติการส่งออก Excel

## ⏰ Cron Job Setup

เพิ่ม cron job สำหรับส่งออก Excel อัตโนมัติ:

```bash
# Export Excel ทุกวันเวลา 23:55
55 23 * * * /usr/bin/php /path/to/hazel-stock/cron/daily-excel-export.php
```

## 🔒 Security Features

- ✅ SQL Injection Protection (Prepared Statements)
- ✅ Input Validation
- ✅ File Upload Security
- ✅ Path Traversal Protection
- ✅ Error Handling
- ✅ Database Transaction Support

## 🚀 Deployment

### Render.com
1. เชื่อมต่อ GitHub repository
2. ตั้งค่า Environment Variables
3. Deploy

### Docker
```bash
docker build -t hazel-stock .
docker run -p 80:80 hazel-stock
```

## 🐛 Troubleshooting

### Database Connection Issues
1. ตรวจสอบ environment variables
2. ทดสอบการเชื่อมต่อด้วย `test_db.php`
3. ตรวจสอบ SSL settings สำหรับ cloud databases

### File Upload Issues
1. ตรวจสอบ permissions ของโฟลเดอร์ `stock-photos/`
2. ตรวจสอบ `upload_max_filesize` ใน PHP config

### Excel Export Issues
1. ตรวจสอบว่า PhpSpreadsheet ติดตั้งแล้ว
2. ตรวจสอบ permissions ของโฟลเดอร์ `excel-exports/`

## 📝 License

Private project for Hazel – Beverages & Appetizers

## 👨‍💻 Support

สำหรับการสนับสนุนหรือรายงานปัญหา กรุณาติดต่อทีมพัฒนา