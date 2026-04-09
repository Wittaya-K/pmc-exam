<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  role: { type: Object, required: true }, // { id, title, permissions: [{id,title}] }
  can: { type: Object, default: () => ({}) },
})

async function destroyOne() {
  if (!confirm('ยืนยันการลบรายการนี้ ?')) return
  await window.axios.delete(`/admin/roles/${props.role.id}`)
  window.location.href = '/admin/roles'
}
</script>

<template>
  <Head title="รายละเอียดสิทธิ์ (Role)" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">รายละเอียดสิทธิ์ (Role)</h1>
        </div>
        <div class="flex gap-2">
          <Link href="/admin/roles" class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10">กลับ</Link>
          <Link
            v-if="props.can?.edit"
            :href="`/admin/roles/${props.role.id}/edit`"
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
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <div class="text-xs text-slate-300/60">ชื่อ</div>
            <div class="mt-1 text-sm font-semibold">{{ props.role.title }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-300/60">Permissions</div>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <span
                v-for="p in props.role.permissions"
                :key="p.id"
                class="rounded-full bg-sky-500/15 px-2.5 py-1 text-[11px] font-semibold text-sky-200"
              >
                {{ p.title }}
              </span>
              <span v-if="!props.role.permissions || props.role.permissions.length === 0" class="text-xs text-slate-300/60">-</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

