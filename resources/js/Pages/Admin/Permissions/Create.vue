<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const form = useForm({
  title: '',
})

function submit() {
  form.post('/admin/permissions', { preserveScroll: true })
}
</script>

<template>
  <Head title="เพิ่มการเข้าถึง (Permission)" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">เพิ่มการเข้าถึง (Permission)</h1>
        </div>
        <Link href="/admin/permissions" class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10">กลับ</Link>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="text-sm font-semibold">ชื่อ (Title)</label>
            <input v-model="form.title" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
            <div v-if="form.errors.title" class="mt-1 text-xs text-rose-300">{{ form.errors.title }}</div>
          </div>

          <div class="flex justify-end gap-2">
            <Link href="/admin/permissions" class="rounded-lg bg-white/5 px-4 py-2 text-sm font-semibold hover:bg-white/10">ยกเลิก</Link>
            <button
              type="submit"
              class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-400 disabled:opacity-60"
              :disabled="form.processing"
            >
              {{ form.processing ? 'กำลังบันทึก…' : 'บันทึก' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

