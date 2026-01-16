# 🏪 Hazel Stock Management System

ระบบจัดการสต็อกวัตถุดิบสำหรับ **Hazel Beverages & Appetizers**

## 📋 คุณสมบัติ

### 🎯 สำหรับพนักงาน
- ✅ บันทึกสต็อกวัตถุดิบประจำวัน
- 📸 ถ่ายรูปยืนยันสต็อกด้วยกล้องมือถือ
- 📊 กรอกจำนวนแบบ 2 หน่วย (หน่วยหลัก + หน่วยย่อย)
- 💾 บันทึกข้อมูลอัตโนมัติ (localStorage)
- 📤 แชร์ผลการบันทึกผ่าน LINE/Messenger

### 👑 สำหรับแอดมิน
- 👥 จัดการพนักงาน (เพิ่ม/แก้ไข/ปิดการใช้งาน)
- 🧪 จัดการวัตถุดิบ (เพิ่ม/แก้ไข/ลบ)
- 📊 ดูข้อมูลสต็อกย้อนหลัง
- ✏️ แก้ไขข้อมูลสต็อก
- 📈 รายงานการใช้งาน (เปรียบเทียบ)
- 📥 Export CSV/Excel
- 🔐 เปลี่ยนรหัสผ่าน

## 🛠️ เทคโนโลยีที่ใช้

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Tailwind CSS (Custom)
- **Libraries**: 
  - PhpSpreadsheet (Excel export)
  - ZipStream (File compression)

## 📦 การติดตั้ง

### ความต้องการของระบบ
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Web Server (Apache/Nginx)
- Composer

### ขั้นตอนการติดตั้ง

1. **Clone repository**
```bash
git clone https://github.com/Bolos55/hazel-stock.git
cd hazel-stock
```

2. **ติดตั้ง dependencies**
```bash
composer install
```

3. **สร้างไฟล์ .env**
```bash
cp .env.example .env
```

4. **แก้ไขไฟล์ .env**
```env
DB_HOST=localhost
DB_NAME=hazel_stock
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

5. **สร้างฐานข้อมูล**
```bash
mysql -u root -p < database.sql
```

6. **เปิดเว็บเซิร์ฟเวอร์**
```bash
php -S localhost:8000
```

7. **เข้าใช้งาน**
- เปิดเบราว์เซอร์: `http://localhost:8000`
- Login แอดมิน: `bosszazababa@gmail.com` / `Bossmaha_2003`

## 📱 Mobile Optimization

ระบบออกแบบมาสำหรับมือถือเป็นหลัก:
- ✅ Responsive Design (รองรับทุกขนาดหน้าจอ)
- ✅ iPhone 16 Pro Optimized
- ✅ Touch-friendly UI
- ✅ Camera Integration
- ✅ PWA Ready

## 🗂️ โครงสร้างโปรเจค

```
hazel-stock/
├── api/                    # API endpoints
│   ├── delete-record.php
│   ├── export-csv.php
│   ├── export-usage-report.php
│   ├── get-materials.php
│   ├── get-today-record.php
│   └── submit-stock.php
├── assets/                 # รูปภาพและไฟล์สื่อ
├── css/                    # Stylesheets
├── excel-exports/          # ไฟล์ Excel ที่ export
├── stock-photos/           # รูปภาพสต็อก
├── vendor/                 # Composer dependencies
├── .env.example           # ตัวอย่างไฟล์ environment
├── .gitignore             # Git ignore rules
├── auth.php               # Authentication functions
├── config.php             # Database configuration
├── database.sql           # Database schema
├── index.php              # หน้าหลัก (บันทึกสต็อก)
├── login.php              # หน้า Login
├── manage-employees.php   # จัดการพนักงาน
├── manage-materials.php   # จัดการวัตถุดิบ
├── view-records.php       # ดูข้อมูลสต็อก
└── README.md              # เอกสารนี้
```

## 🔐 ความปลอดภัย

- ✅ Password hashing (bcrypt)
- ✅ SQL Injection protection (PDO Prepared Statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ CSRF protection (Session-based)
- ✅ Role-based access control (Admin/Employee)

## 📸 Screenshots

### หน้าบันทึกสต็อก
![Stock Recording](docs/screenshots/stock-recording.png)

### หน้าดูข้อมูล
![View Records](docs/screenshots/view-records.png)

### หน้าจัดการวัตถุดิบ
![Manage Materials](docs/screenshots/manage-materials.png)

## 🤝 การพัฒนา

### Branch Strategy
- `main` - Production branch
- `develop` - Development branch
- `feature/*` - Feature branches

### Coding Standards
- PSR-12 Coding Style
- UTF-8 encoding
- Thai language for UI
- English for code comments

## 📝 License

This project is proprietary software developed for **Hazel Beverages & Appetizers**.

## 👨‍💻 Developer

Developed by **Kiro AI** for **ภูริวัฒน์ โภคสวัสดิ์**

## 📞 Support

For support, please contact:
- Email: bosszazababa@gmail.com
- GitHub Issues: [Create an issue](https://github.com/Bolos55/hazel-stock/issues)

---

Made with ❤️ for Hazel Beverages & Appetizers
