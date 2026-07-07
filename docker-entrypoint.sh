#!/bin/bash
set -e

# storage/ และ public/ เป็น named volume (storage_data / public_data)
# ที่ mount ทับทุกครั้ง container start -> โฟลเดอร์/สิทธิ์/ไฟล์ build
# ต้องถูกสร้าง/คืนค่าใหม่ทุกครั้งที่นี่ ไม่สามารถพึ่งพา image build ได้

# --- คืนค่า public/ จาก backup ตอน build ถ้า volume ว่าง (ครั้งแรกที่สร้าง) ---
if [ ! -f /var/www/html/public/index.php ] && [ -d /opt/laravel-public-backup ]; then
    cp -r /opt/laravel-public-backup/. /var/www/html/public/
fi

# --- สร้าง path ที่จำเป็นใน storage (volume ว่างตอน container ใหม่) ---
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public/reports
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/public/uploads/temp

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public

# storage:link ต้องรันทุกครั้งที่ volume ใหม่/ว่าง (symlink อยู่ใต้ public/)
if [ ! -L /var/www/html/public/storage ]; then
    php /var/www/html/artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
