FROM composer:2 AS composer_deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

FROM node:24-bookworm-slim AS frontend_build
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM php:8.2-fpm-bookworm AS app
WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) pdo_mysql zip gd \
  && rm -rf /var/lib/apt/lists/*

# Copy application code
COPY . .

# Copy vendor from composer stage
COPY --from=composer_deps /app/vendor ./vendor

# Copy built frontend assets
COPY --from=frontend_build /app/public/build ./public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
  && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ใช้ PHP 8.2 FPM
FROM php:8.2-fpm

# ติดตั้ง dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev libldap2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd ldap \
    && rm -rf /var/lib/apt/lists/*

# ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ตั้งค่า working directory
WORKDIR /var/www/html

# Copy composer files ก่อนเพื่อใช้ Docker cache
COPY composer.json composer.lock ./

# ติดตั้ง Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copy project files
COPY . .

# สินทรัพย์ Vite (manifest) — .dockerignore ไม่ใส่ public/build จาก host; build จริงมาจาก stage ด้านบน
COPY --from=frontend_build /app/public/build ./public/build

# Generate autoloader อีกครั้งหลัง copy ไฟล์ทั้งหมด
RUN composer dump-autoload --optimize --no-dev

# Copy PHP-FPM config
COPY php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

# สร้าง directory ที่จำเป็นถ้ายังไม่มี (แยก path — sh ใน build ไม่ทำ brace expansion แบบ bash)
RUN mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# ให้สิทธิ์ storage และ bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/public/build \
    && chmod -R 775 /var/www/html/public/uploads /var/www/html/public/build

# Snapshot public/ สำหรับ seed volume ตอน runtime (nginx กับ php-fpm ใช้ชุดเดียวกัน)
RUN tar czf /opt/laravel-public.tgz -C /var/www/html public

# หมายเหตุ:
# - อย่ารัน artisan clear/cache ใน build stage
# - ควรรันตอน runtime หลังจากมี .env แล้ว (ทำไว้ใน entrypoint)

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]