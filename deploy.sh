#!/bin/bash
# ============================================================
# PMC Exam - Deploy Script
# Branch: dev-pmc-29-06-2569
# Usage: ./deploy.sh
# ============================================================

set -e

REPO_URL="https://github.com/Wittaya-K/pmc-exam.git"
BRANCH="dev-pmc-29-06-2569"
PROJECT_DIR="/var/www/pmc-exam"
ENV_BACKUP="/var/backups/pmc-exam/.env"   # ปรับ path ตามจริง

echo "==> [1/9] Backup .env (ถ้ามี)"
if [ -f "$PROJECT_DIR/.env" ]; then
    mkdir -p "$(dirname "$ENV_BACKUP")"
    sudo cp "$PROJECT_DIR/.env" "$ENV_BACKUP"
    echo "    Backed up .env -> $ENV_BACKUP"
fi

echo "==> [2/9] ลบโฟลเดอร์เก่าและ clone ใหม่"
cd /tmp   # ออกจาก PROJECT_DIR ก่อนลบ กัน error cwd หาย
sudo rm -rf "$PROJECT_DIR"
sudo git clone --branch "$BRANCH" "$REPO_URL" "$PROJECT_DIR"
cd "$PROJECT_DIR"

echo "==> [3/9] คืนค่าความเป็นเจ้าของให้ user ปัจจุบัน (กัน permission denied ตอน pull ครั้งถัดไป)"
sudo chown -R "$(whoami):$(whoami)" "$PROJECT_DIR"

echo "==> [4/9] คืนค่า .env"
if [ -f "$ENV_BACKUP" ]; then
    cp "$ENV_BACKUP" "$PROJECT_DIR/.env"
    echo "    Restored .env"
else
    echo "    !! ไม่พบ .env backup - ต้องสร้าง .env เองก่อนรัน container"
fi

echo "==> [5/9] เตรียม directory ที่ bind mount ต้องใช้ (กัน Laravel เขียนไฟล์ไม่ได้)"
mkdir -p public/uploads/temp storage/app/public

echo "==> [6/9] Build และ start containers"
sudo docker compose down -v   # ใส่ -v เพื่อล้าง DB เก่า, เอาออกถ้าต้องการเก็บข้อมูล
sudo docker compose build --no-cache
sudo docker compose up -d

echo "==> [7/9] รอ container พร้อม"
sleep 8

echo "==> [8/9] Install dependencies + Laravel setup"
sudo docker exec pmc-exam-app bash -c "composer install --no-dev --optimize-autoloader"
sudo docker exec pmc-exam-app php artisan key:generate
sudo docker exec pmc-exam-app php artisan migrate --force
sudo docker exec pmc-exam-app bash -c \
    "php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan view:clear"
sudo docker exec pmc-exam-app bash -c \
    "php artisan config:cache && php artisan route:cache && php artisan view:cache"

echo "==> [9/9] Restart web container"
sudo docker compose restart web

echo ""
echo "✅ Deploy เสร็จแล้ว — http://localhost:8083"
echo "   (permission ของ storage/uploads ถูกจัดการอัตโนมัติโดย docker-entrypoint.sh ทุกครั้งที่ container start)"
