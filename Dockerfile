# Използваме PHP с вграден Apache
FROM php:8.2-apache

# 1. Инсталиране на библиотеки
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    acl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    gd \
    opcache

# 2. Включване на mod_rewrite
RUN a2enmod rewrite

# 3. Настройка на DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# === ВАЖНО: Гарантирано оправяне на 404 грешката ===
# Създаваме специален конфиг файл, който разрешава .htaccess
RUN echo '<Directory /var/www/html/public/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# 5. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Копиране на кода
WORKDIR /var/www/html
COPY . .

# 7. Инсталиране на зависимости
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 8. Финализиране
RUN composer dump-autoload --optimize

# 9. Създаване на папки и права
RUN mkdir -p /var/www/html/var /var/www/html/public/uploads
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public/uploads

# 10. Порт 80
EXPOSE 80