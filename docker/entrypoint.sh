#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.docker ]; then
  cp .env.docker .env
fi

# public_data volume ทับ public/ — ว่างหรือไม่มี Vite manifest ให้ดึงจาก image
if [ ! -f /var/www/html/public/build/manifest.json ] && [ -f /opt/laravel-public.tgz ]; then
  tar xzf /opt/laravel-public.tgz -C /var/www/html
  chown -R www-data:www-data /var/www/html/public 2>/dev/null || true
  chmod -R ug+rX /var/www/html/public 2>/dev/null || true
fi

# Named volumes (storage_data / cache_data) mount ทับโฟลเดอร์ใน image — ต้องสร้าง path + สิทธิ์ทุกครั้งที่ start
# ลบโฟลเดอร์ชื่อผิดที่เคยเกิดจาก mkdir แบบ {a,b,c} บน shell ที่ไม่รองรับ brace expansion
rm -rf "storage/framework/{sessions,views,cache}" 2>/dev/null || true

mkdir -p storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache/data \
  storage/logs \
  bootstrap/cache \
  public/uploads

chown -R www-data:www-data storage bootstrap/cache public/uploads 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache public/uploads 2>/dev/null || true

php artisan config:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

exec "$@"

