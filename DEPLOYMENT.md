# 🚀 Deployment Guide

## ✅ Pre-Deployment Checklist

### 1. Code Quality
- [x] ไม่มี syntax errors
- [x] ทุกไฟล์ผ่านการตรวจสอบ `php -l`
- [x] CSS ไม่มีส่วนที่ล้นขอบจอ
- [x] Responsive design ทำงานบนมือถือ

### 2. Database
- [x] ลบข้อมูลทดสอบทั้งหมด
- [x] เหลือแอดมิน 1 คน (Boss)
- [x] Reset AUTO_INCREMENT
- [x] ลบรูปภาพเก่า (70 รูป)

### 3. Security
- [x] .env ไม่ถูก commit
- [x] Password ใช้ bcrypt
- [x] SQL Injection protection
- [x] XSS protection

### 4. Files
- [x] .gitignore ครบถ้วน
- [x] README.md สมบูรณ์
- [x] .env.example พร้อมใช้งาน

## 📋 Admin Account

**Username**: `bosszazababa@gmail.com`  
**Password**: `Bossmaha_2003`  
**Role**: Admin

## 🗂️ Files to Ignore (Already in .gitignore)

- `.env` - Environment variables
- `/vendor/` - Composer dependencies
- `/stock-photos/*` - Uploaded photos
- `/excel-exports/*.xlsx` - Generated Excel files
- `*.log` - Log files
- `clean-database.php` - Cleanup script

## 📦 What's Included in Git

✅ Source code (PHP, HTML, CSS, JS)  
✅ Database schema (`database.sql`)  
✅ Configuration examples (`.env.example`)  
✅ Assets (logos, images)  
✅ Documentation (README.md)  
✅ Dependencies definition (`composer.json`)  

❌ Environment files (`.env`)  
❌ Vendor directory (`/vendor/`)  
❌ User uploaded files  
❌ Generated exports  
❌ Log files  

## 🚀 Deployment Steps

### Step 1: Push to GitHub

```bash
# Initialize git (if not already)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: Hazel Stock Management System"

# Add remote
git remote add origin https://github.com/Bolos55/hazel-stock.git

# Push to main branch
git push -u origin main
```

### Step 2: Deploy to Production Server

```bash
# On production server
git clone https://github.com/Bolos55/hazel-stock.git
cd hazel-stock

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy and configure .env
cp .env.example .env
nano .env

# Import database
mysql -u username -p database_name < database.sql

# Set permissions
chmod 755 stock-photos
chmod 755 excel-exports
chmod 644 .env
```

### Step 3: Web Server Configuration

**Apache (.htaccess already included)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### Step 4: SSL Certificate (Recommended)

```bash
# Using Let's Encrypt
sudo certbot --nginx -d yourdomain.com
```

## 🔧 Post-Deployment

1. **Test all features**
   - Login as admin
   - Create test employee
   - Record stock
   - View records
   - Export CSV/Excel

2. **Monitor logs**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

3. **Backup database**
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

## 📊 Performance Optimization

### PHP Configuration
```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### MySQL Optimization
```sql
-- Add indexes if needed
ALTER TABLE daily_stock_records ADD INDEX idx_record_date (record_date);
ALTER TABLE daily_stock_records ADD INDEX idx_employee_id (employee_id);
```

## 🔄 Update Workflow

```bash
# On production server
cd hazel-stock
git pull origin main
composer install --no-dev --optimize-autoloader
php migrate.php  # If database changes
```

## 🆘 Troubleshooting

### Issue: Can't connect to database
**Solution**: Check `.env` file and MySQL credentials

### Issue: Photos not uploading
**Solution**: Check folder permissions
```bash
chmod 755 stock-photos
chown www-data:www-data stock-photos
```

### Issue: Composer dependencies missing
**Solution**: Run composer install
```bash
composer install
```

## 📞 Support

For deployment issues:
- Check logs: `/var/log/apache2/error.log`
- Check PHP errors: `tail -f error_log`
- Contact: bosszazababa@gmail.com

---

✅ **Ready to Deploy!**

Repository: https://github.com/Bolos55/hazel-stock
