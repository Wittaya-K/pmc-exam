วิธี deploy บน Ubuntu (สรุปสั้น)

1.สร้างไฟล์ env ของ docker
cp .env.docker.example .env.docker
แก้ใน .env.docker ให้ครบ โดยเฉพาะ:
APP_URL=https://pmc-exam.cs.psu.ac.th
ASSET_URL=https://pmc-exam.cs.psu.ac.th
AZURE_AD_REDIRECT_URI=https://pmc-exam.cs.psu.ac.th/auth/callback
APP_KEY=... (สำคัญ)

2.สร้าง APP_KEY แล้วเอาไปใส่ .env.docker
docker compose run --rm app php artisan key:generate --show

3.Build + up
docker compose up -d --build
migrate

4.docker compose exec app php artisan migrate --force

5.เปิดเว็บ
ตอนนี้ใน compose ผูกพอร์ตไว้ที่ 8080 → เข้า http://SERVER_IP:8080
ตอนจะใช้โดเมนจริง pmc-exam.cs.psu.ac.th ให้เอา reverse proxy/ingress ชี้มา port นี้ หรือเปลี่ยนใน compose เป็น 80:80 / 443 ตามที่คุณต้องการ

สำคัญเรื่อง Queue
service queue ใน compose จะรัน php artisan queue:work database ... ตลอด
service scheduler จะรัน php artisan schedule:work ดังนั้นฟีเจอร์ “จัดห้องสอบแบบ background” จะทำงานได้ทันทีบน Docker

ถ้าคุณต้องการให้ผมปรับ compose เป็น production เต็ม (เช่น Nginx 80/443, healthcheck สำหรับ web, และเพิ่ม php artisan migrate --force แบบ one-shot service) บอกได้เลยครับ


ขั้นตอนเอาโปรเจกต์นี้ไปรันบน Docker (Ubuntu) ต้องทำอะไรบ้าง
1) เตรียมไฟล์ Docker พื้นฐาน
โดยทั่วไปจะแยกเป็น 4–5 ส่วน:

app (php-fpm): รัน Laravel
web (nginx): serve /public และ proxy ไป php-fpm
db (mysql): ฐานข้อมูล
queue (worker): รัน php artisan queue:work
scheduler (แนะนำ): รัน php artisan schedule:work หรือ cron
ถ้าอยากง่ายสุด ใช้ docker-compose.yml มี service app/web/db/queue/scheduler

2) ตั้งค่า .env สำหรับ Docker
แนะนำแยกเป็น .env.docker แล้วตอนรันค่อยชี้ไฟล์ env นั้น

APP_URL: ให้ตรงกับโดเมน/พอร์ตจริง (เช่น http://localhost:8080 หรือโดเมนจริง)
DB_HOST: ให้เป็นชื่อ service เช่น db
DB_DATABASE/DB_USERNAME/DB_PASSWORD: ให้ตรงกับ compose
QUEUE_CONNECTION=database (ของคุณใช้อยู่แล้ว)
CACHE_DRIVER=file ใช้ได้ แต่อยากให้ lock เสถียรกว่าในอนาคตให้ไป redis ได้
ตัวอย่างค่าที่มักต้องเปลี่ยน:

DB_HOST=db
DB_PORT=3306
APP_DEBUG=false (สำหรับ production)
APP_ENV=production
3) ทำ build frontend (Vite) ให้พร้อมใน container
คุณมี 2 แนวทาง:

แบบ production (แนะนำ): build ไฟล์ไปที่ public/build ในขั้นตอน build image (npm ci && npm run build)
แบบ dev: รัน npm run dev เป็นอีก service + เปิด port ของ Vite (มักไม่จำเป็นบน server)
ของโปรเจกต์คุณตอนนี้ใช้ @vite() และอ่าน public/build/manifest.json ดังนั้น production ต้องมี public/build/* อยู่จริง

4) จัดการสิทธิ์โฟลเดอร์ที่ Laravel ต้องเขียน
ใน container ต้องเขียนได้ที่:

storage/
bootstrap/cache/
ปกติใน Dockerfile จะ chown -R www-data:www-data storage bootstrap/cache

5) รัน migrate + queue worker (สำคัญกับระบบจัดห้องสอบแบบ background)
หลัง container ขึ้นแล้ว ให้รันครั้งแรก:

php artisan migrate --force
(ถ้าใช้ key/caches) php artisan config:cache / route:cache (optional)
และต้องมี worker ทำงานตลอด:

queue service: php artisan queue:work database --sleep=1 --tries=1 --timeout=0
scheduler service (ถ้าคุณมี schedule): php artisan schedule:work
ฟีเจอร์ “จัดห้องสอบ” ที่เราทำไว้จะค้างที่ queued ถ้าไม่มี worker

6) เรื่อง storage link (ถ้าใช้ public storage)
ถ้าโปรเจกต์มีไฟล์ใน storage/app/public ที่ต้องเปิดผ่านเว็บ:

รัน php artisan storage:link (ครั้งแรก)
7) ตรวจ nginx ให้ชี้ DocumentRoot ไปที่ /public
นี่คือจุดที่พลาดบ่อยที่สุดบน Docker:

nginx ต้อง root /var/www/html/public;
ต้องส่ง index.php ไปที่ php-fpm