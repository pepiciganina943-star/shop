# Използваме PHP с вграден Apache
FROM php:8.2-apache

# 1. Инсталиране на библиотеки + NODE.JS
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
    curl \
    gnupg \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
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

# === 4. Apache настройка за големи файлове ===
RUN echo '<Directory /var/www/html/public/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    LimitRequestBody 67108864\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# === 5. PHP настройка за големи файлове ===
RUN echo "file_uploads = On\n\
memory_limit = 256M\n\
upload_max_filesize = 64M\n\
post_max_size = 64M\n\
max_execution_time = 600\n" > /usr/local/etc/php/conf.d/uploads.ini

# 6. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Копиране на кода
WORKDIR /var/www/html
COPY . .

# === 8. ГЕНЕРИРАНЕ НА АСЕТИТЕ (Node.js) ===
RUN npm install
RUN npm run build

# === 9. НАСТРОЙКИ ЗА СРЕДАТА ===
ENV APP_ENV=prod
ENV APP_DEBUG=0

# === 10. ВАЖНО: SQLite ТРИКЪТ ===
# Вместо mysql (който иска връзка), ползваме sqlite (който е просто файл).
# Така assets:install няма да гърми с "Connection refused"!
ENV DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# 11. Инсталиране на PHP зависимости
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 12. Инсталиране на асетите за EasyAdmin
# Сега вече ще мине, защото SQLite не изисква мрежова връзка!
RUN php bin/console assets:install public

# 13. Финализиране
RUN composer dump-autoload --optimize

# 14. Права за писане
RUN chown -R www-data:www-data /var/www/html

# 15. Порт 80
EXPOSE 80