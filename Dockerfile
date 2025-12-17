# Dockerfile for Restaurant Stock System
FROM php:8.1-apache

# 1. ติดตั้ง system dependencies และ PHP extensions ในรอบเดียว
# เพิ่ม libonig-dev เพื่อให้ mbstring ทำงานได้
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
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip xml mbstring

# 2. Enable Apache modules
RUN a2enmod rewrite headers

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set working directory และ Copy ไฟล์
WORKDIR /var/www/html
COPY . /var/www/html/

# 5. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 6. สร้างโฟลเดอร์และตั้งค่า Permissions
RUN mkdir -p logs stock-photos excel-exports \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 logs stock-photos excel-exports

# 7. Configure PHP settings
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# 8. Setup cron job
RUN echo "55 23 * * * www-data /usr/local/bin/php /var/www/html/cron/daily-excel-export.php >> /var/www/html/logs/cron.log 2>&1" >> /etc/crontab \
    && touch /var/log/cron.log

# 9. Port และ Health check
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
    CMD curl -f http://localhost/ || exit 1

# 10. Start Apache และ Cron
CMD cron && apache2-foreground
