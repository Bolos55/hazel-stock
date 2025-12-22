FROM php:8.2-apache

# -----------------------------
# System dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        gd \
        zip \
        xml \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Install Composer
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for better caching)
COPY composer.json composer.lock* ./

# -----------------------------
# Install PHP dependencies
# -----------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy application source
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize --no-dev

# Create necessary directories
RUN mkdir -p stock-photos excel-exports logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set environment variable
ENV APACHE_DOCUMENT_ROOT /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
