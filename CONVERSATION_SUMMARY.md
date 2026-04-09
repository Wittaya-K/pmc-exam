## สรุปงานในบทสนทนา (AdminLTE3 → Vue 3 + Inertia.js + Tailwind + Docker/Queue)

โปรเจกต์: `dev-pmc-1.0.26` (Laravel 10) บน WAMP (PHP 8.2.29, MySQL 8.0.43, Node 24.x)

---

### เป้าหมาย
- ปรับ UI/Frontend จาก **Blade + AdminLTE3** ไปเป็น **Vue 3 + Inertia.js + Tailwind CSS**
- ทำแบบ **ย้ายทีละหน้า (incremental migration)** ให้ระบบเดิมยังใช้งานได้ระหว่างทาง
- เตรียม **Docker (Ubuntu) + MySQL ใน Docker** สำหรับ deploy
- ปรับงานหนัก “จัดห้องสอบ” ให้เป็น **Laravel Queue Job + lock กันกดซ้ำ + status endpoint** สำหรับหน้า Vue

---

## 1) Frontend stack ใหม่ (Vite + Vue 3 + Inertia + Tailwind)

### ติดตั้ง/ตั้งค่า
- เพิ่ม Inertia Laravel: `inertiajs/inertia-laravel`
- เพิ่ม Vite + Vue3 + Inertia + Tailwind
- เพิ่มไฟล์และ config:
  - `vite.config.js`
  - `tailwind.config.js`
  - `postcss.config.js`
  - `resources/css/app.css`
  - `resources/views/app.blade.php` (Inertia root)
  - `app/Http/Middleware/HandleInertiaRequests.php` + ผูกใน `app/Http/Kernel.php` (middleware group `web`)

### จุดสำคัญที่แก้ให้รันได้จริง
- แก้ปัญหา Inertia initial page เป็น `null`:
  - ปรับ `resources/js/app.js` ให้ parse `data-page` เอง + guard error ชัดเจน
- แก้ปัญหา Tailwind class ไม่ออก:
  - เปลี่ยน Tailwind **v4 → v3** เพื่อให้ utilities (เช่น `bg-slate-*`) ถูก generate ตามคาด
- ปรับ `.env` ให้ `APP_URL/ASSET_URL` เป็นโดเมนเพื่อให้ `@vite` สร้าง asset URL ถูกต้อง

---

## 2) หน้าที่ถูกย้ายเป็น Inertia/Vue แล้ว

### Auth
- `GET /login` → Inertia page: `resources/js/Pages/Auth/Login.vue`
  - ยังคง flow SSO เดิม: `/auth/redirect`
  - `POST /login` ยังใช้ controller เดิม

### Admin layout
- เพิ่ม `resources/js/Layouts/AdminLayout.vue`
  - มี sidebar/topbar/logout
  - เพิ่มเมนู dropdown “จัดการผู้ใช้งาน” (Permissions/Roles/Users)
  - active highlight ตาม URL

### Admin pages
- `/admin` → `Admin/Home.vue`
- `/admin/test_center` → `Admin/TestCenter/Index.vue`
  - **แก้บั๊กสำคัญ**: Inertia navigation เป็น XHR ทำให้ `$request->ajax()` เป็น true → เดิมจะคืน JSON
  - แก้ให้คืน JSON เฉพาะ “AJAX ที่ไม่ใช่ Inertia” โดยเช็ค `X-Inertia`
- `/admin/test_center/create` → `Admin/TestCenter/Create.vue` (อัปโหลด Excel ไป endpoint เดิม `POST /admin/test_center/save`)
- `/admin/file_import` → `Admin/FileImport/Index.vue` (ดึง list จาก `GET /admin/file_import/list`)
- `/admin/file_import/create` → `Admin/FileImport/Create.vue` (อัปโหลดไป `POST /admin/file_import/save`)
- `/admin/arrange_seat` → `Admin/ArrangeSeat/Index.vue`
- `/admin/arrange_seat/view` → `Admin/ArrangeSeat/View.vue` (ใช้ endpoints เดิม `searchStudent`, `getStudent`)
- `/admin/permissions`:
  - index/create/edit/show → Inertia pages: `Admin/Permissions/*`
- `/admin/roles`:
  - index/create/edit/show → Inertia pages: `Admin/Roles/*`
- `/admin/users`:
  - index/create/edit/show → Inertia pages: `Admin/Users/*`

หมายเหตุ: หน้า/route ที่ยังเป็น Blade ยังสามารถอยู่ร่วมได้ (incremental migration)

---

## 3) Queue สำหรับ “จัดห้องสอบ” (background) + lock + status endpoint

### เพิ่มตารางเก็บสถานะ
- Migration: `database/migrations/2026_04_08_000001_create_arrange_seat_runs_table.php`
- Model: `app/Models/ArrangeSeatRun.php`
- สถานะที่ใช้: `queued | running | succeeded | failed`

### ปรับ Job
- ปรับ `app/Jobs/ArrangeSeatJob.php`:
  - รับ `runId`
  - ใช้ `Cache::lock('arrange_seat:assignSeats', 1 ชั่วโมง)` กันรันซ้อนข้าม worker
  - update สถานะใน `arrange_seat_runs` + เก็บ error เมื่อ failed

### ปรับ Controller + routes
- `ArrangeSeatController@assignSeats`:
  - กันกดซ้ำ/กัน dispatch ซ้ำ
  - สร้าง run (queued) แล้ว dispatch job
  - ถ้าจัดที่นั่งแล้ว (`SeatAssign::count() > 0`) จะ block re-run
- เพิ่ม status endpoint:
  - `GET /admin/arrange_seat/assignSeats/status` (รองรับ `?run_id=...`)
  - route name: `admin.arrange_seat.assignSeatsStatus`

### ปรับ Vue ให้เห็นสถานะ
- `Admin/ArrangeSeat/Index.vue`:
  - กด “จัดห้องสอบ” → ได้ `run_id` → polling status ทุก 2 วินาที
  - แสดง “กำลังทำ/ล้มเหลว” และ reload เมื่อสำเร็จ/ล้มเหลวเพื่ออัปเดตตัวเลข

---

## 4) Docker (Ubuntu) สำหรับ deploy (มี MySQL ใน Docker)

### ไฟล์ที่เพิ่ม
- `.dockerignore`
- `Dockerfile` (composer deps + build Vite assets + php-fpm 8.2)
- `docker-compose.yml` (services: `db`, `app`, `web`, `queue`, `scheduler`)
- `docker/nginx/default.conf` (root `/public`)
- `docker/entrypoint.sh`
- `.env.docker.example`
- `README_DOCKER.md`

### หมายเหตุการใช้งาน
- ต้องสร้างไฟล์ `.env.docker` จาก `.env.docker.example`
- ต้องตั้ง `APP_KEY` และค่า Azure callback ให้ตรงโดเมน `pmc-exam.cs.psu.ac.th`
- Queue worker และ scheduler ถูกแยกเป็น service ใน compose:
  - `queue`: `php artisan queue:work database ...`
  - `scheduler`: `php artisan schedule:work`

---

## 5) ประเด็นที่พบระหว่างทาง (สำคัญ)
- PowerShell ไม่รองรับ `&&` → ใช้ `;` แทนเวลา chain คำสั่ง
- Inertia navigation เป็น XHR → เงื่อนไข `$request->ajax()` ต้องระวังไม่ให้คืน JSON แทนหน้า Inertia
- ปัญหา MySQL migration key too long (1071):
  - เพิ่ม `Schema::defaultStringLength(191)` ใน `AppServiceProvider`
  - เคยมีสถานะ migration ค้างที่ `personal_access_tokens` (แก้แล้วในเครื่อง dev)

---

## Next steps ที่แนะนำ
- ย้ายหน้า admin ที่ยังเป็น Blade ที่เหลือ (ถ้ามี) ทีละหน้า
- ทำ UI components กลาง (Table, Modal, Confirm dialog) เพื่อใช้ซ้ำ
- ถ้า deploy จริง: ตั้ง reverse proxy/HTTPS (Nginx/Traefik) ให้โดเมน `pmc-exam.cs.psu.ac.th` ชี้เข้ามา container `web`

