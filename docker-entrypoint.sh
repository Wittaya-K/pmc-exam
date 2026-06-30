#!/bin/bash
set -e

# Bind mount (.:/var/www/html) ทับ ownership ที่ตั้งไว้ตอน build เสมอ
# จึงต้อง fix permission ใหม่ทุกครั้งที่ container start

mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/public/uploads/temp

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public/uploads

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public/uploads

exec "$@"
