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
        mbstring

# -----------------------------
# Install Composer (IMPORTANT)
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy source
COPY . /var/www/html

# -----------------------------
# Composer install (SAFE MODE)
# -----------------------------
RUN if [ -f composer.json ]; then \
    composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader; \
    fi

# Permissions
RUN chown -R www-data:www-data /var/www/html
