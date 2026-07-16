#!/bin/bash
set -e
cd /var/www/pmc-exam
git pull origin dev-pmc-15-07-2569
docker compose up -d --build
docker exec pmc-exam-app php artisan optimize:clear
docker exec pmc-exam-app php artisan config:cache
docker exec pmc-exam-app php artisan route:cache
docker exec pmc-exam-app php artisan view:cache
echo "Deploy เสร็จแล้ว"
