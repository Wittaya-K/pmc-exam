<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '../../Layouts/AdminLayout.vue'
</script>

<template>
  <Head title="แดชบอร์ด" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">DASHBOARD</div>
          <h1 class="mt-1 text-2xl font-semibold">แดชบอร์ด</h1>
          <p class="mt-2 text-sm text-slate-300/70">ยินดีต้อนรับเข้าสู่ระบบบริหารการจัดสอบ PMC</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a
          href="/admin/test_center"
          class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10"
        >
          <div class="text-3xl">🏫</div>
          <div class="mt-4 text-sm font-semibold">ศูนย์สอบ</div>
          <div class="mt-1 text-xs text-slate-300/70">จัดการข้อมูลศูนย์สอบ</div>
        </a>

        <a
          href="/admin/file_import"
          class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10"
        >
          <div class="text-3xl">📄</div>
          <div class="mt-4 text-sm font-semibold">นำเข้าไฟล์ข้อมูล</div>
          <div class="mt-1 text-xs text-slate-300/70">จัดการข้อมูลผู้เข้าสอบ</div>
        </a>

        <a
          href="/admin/arrange_seat"
          class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10"
        >
          <div class="text-3xl">🪑</div>
          <div class="mt-4 text-sm font-semibold">จัดที่นั่งสอบ</div>
          <div class="mt-1 text-xs text-slate-300/70">จัดที่นั่งสอบอัตโนมัติ</div>
        </a>

        <a
          href="/admin/users"
          class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10"
        >
          <div class="text-3xl">👤</div>
          <div class="mt-4 text-sm font-semibold">ผู้ใช้งาน</div>
          <div class="mt-1 text-xs text-slate-300/70">จัดการสิทธิ์และผู้ใช้งาน</div>
        </a>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="text-sm font-semibold">สถานะระบบ</div>
        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
          <div class="rounded-xl bg-slate-950/50 p-4">
            <div class="text-xs text-slate-300/70">Frontend</div>
            <div class="mt-1 text-sm font-semibold text-emerald-300">Vue 3 + Inertia</div>
          </div>
          <div class="rounded-xl bg-slate-950/50 p-4">
            <div class="text-xs text-slate-300/70">CSS</div>
            <div class="mt-1 text-sm font-semibold text-emerald-300">Tailwind</div>
          </div>
          <div class="rounded-xl bg-slate-950/50 p-4">
            <div class="text-xs text-slate-300/70">Backend</div>
            <div class="mt-1 text-sm font-semibold text-emerald-300">Laravel</div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

