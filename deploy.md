#!/bin/bash

# ============================================================

# PMC Exam - Deploy Script

# Branch: dev-pmc-29-06-2569

# ============================================================

set -e
cd /var/www

# 1. ลบและ clone ใหม่

sudo rm -rf pmc-exam
sudo git clone --branch dev-pmc-29-06-2569 https://github.com/Wittaya-K/pmc-exam.git pmc-exam
cd pmc-exam

# 2. คัดลอก .env (ต้องมีไว้ก่อน)

# sudo cp /backup/.env.pmc-exam .env

# 3. Build และ start containers

# sudo docker compose down -v # ใส่ -v เพื่อล้าง volume เก่า (ลบข้อมูล DB ด้วย)

sudo docker compose build --no-cache
sudo docker compose up -d

# 4. รอให้ app container พร้อม

sleep 5

# 5. Install dependencies

sudo docker exec -it pmc-exam-app bash -c "composer install --no-dev --optimize-autoloader"

# 6. Permissions

sudo docker exec -it pmc-exam-app bash -c \
 "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"

# 7. Laravel setup

sudo docker exec -it pmc-exam-app php artisan key:generate
sudo docker exec -it pmc-exam-app php artisan migrate --force

# 8. Clear และ cache ใหม่

sudo docker exec -it pmc-exam-app bash -c \
 "php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan view:clear"
sudo docker exec -it pmc-exam-app bash -c \
 "php artisan config:cache && php artisan route:cache && php artisan view:cache"

# 9. Restart web

sudo docker compose restart web

echo "✅ Deploy เสร็จแล้ว — http://localhost:8083"
