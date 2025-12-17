# ใช้ PHP 8.1 พร้อม Apache
FROM php:8.1-apache

# 1. ติดตั้ง Dependencies ที่จำเป็นสำหรับ Extensions
# เพิ่ม libonig-dev (สำหรับ mbstring) และ libpng/libjpeg (สำหรับ gd)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    zip \
    unzip \
    curl \
    cron \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure และติดตั้ง PHP Extensions (แก้ปัญหา Build Failed)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip xml mbstring

# 3. เปิดใช้งาน Apache Modules
RUN a2enmod rewrite headers

# 4. ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. ตั้งค่า Working Directory และก๊อปปี้ไฟล์
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. ติดตั้ง PHP Dependencies (ถ้ามีไฟล์ composer.json)
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# 7. สร้างโฟลเดอร์และตั้งค่า Permissions
RUN mkdir -p logs stock-photos excel-exports \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 logs stock-photos excel-exports

# 8. ตั้งค่า PHP
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'memory_limit = 256M'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# 9. Start Apache
EXPOSE 80
CMD ["apache2-foreground"]
