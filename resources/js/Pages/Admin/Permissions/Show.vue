<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  permission: { type: Object, required: true }, // { id, title }
  can: { type: Object, default: () => ({}) },
})

async function destroyOne() {
  if (!confirm('ยืนยันการลบรายการนี้ ?')) return
  await window.axios.delete(`/admin/permissions/${props.permission.id}`)
  window.location.href = '/admin/permissions'
}
</script>

<template>
  <Head title="รายละเอียดการเข้าถึง (Permission)" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">รายละเอียดการเข้าถึง (Permission)</h1>
        </div>
        <div class="flex gap-2">
          <Link href="/admin/permissions" class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10">กลับ</Link>
          <Link
            v-if="props.can?.edit"
            :href="`/admin/permissions/${props.permission.id}/edit`"
            class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-500"
          >
            แก้ไข
          </Link>
          <button
            v-if="props.can?.delete"
            type="button"
            class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500"
            @click="destroyOne"
          >
            ลบ
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <div>
          <div class="text-xs text-slate-300/60">ชื่อ (Title)</div>
          <div class="mt-1 text-sm font-semibold">{{ props.permission.title }}</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

