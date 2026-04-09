<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  users: { type: Array, default: () => [] }, // [{ id, name, email, email_verified_at, roles: [{id,title}] }]
  can: { type: Object, default: () => ({}) },
})

const q = ref('')
const filtered = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return props.users
  return props.users.filter((u) => {
    const hay = [
      u.name ?? '',
      u.email ?? '',
      ...(u.roles ?? []).map((r) => r.title ?? ''),
    ]
      .join(' ')
      .toLowerCase()
    return hay.includes(term)
  })
})

async function destroyOne(id) {
  if (!confirm('ยืนยันการลบรายการนี้ ?')) return
  await window.axios.delete(`/admin/users/${id}`)
  window.location.reload()
}
</script>

<template>
  <Head title="ผู้ใช้งาน" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">ผู้ใช้งาน</h1>
          <p class="mt-2 text-sm text-slate-300/70">จัดการผู้ใช้งานและสิทธิ์ที่ผูกอยู่</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <Link
            v-if="props.can?.create"
            href="/admin/users/create"
            class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-400"
          >
            เพิ่มข้อมูล
          </Link>
        </div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5">
        <div class="flex flex-col gap-3 border-b border-white/10 p-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="text-sm font-semibold">รายการ</div>
          <div class="flex items-center gap-2">
            <input
              v-model="q"
              placeholder="ค้นหา ชื่อ / email / role…"
              class="w-80 rounded-lg border-white/10 bg-slate-950/30 text-sm placeholder:text-slate-400/60"
            />
            <div class="text-xs text-slate-300/60">ทั้งหมด {{ filtered.length }} รายการ</div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-950/40 text-left text-xs text-slate-300/70">
              <tr>
                <th class="px-4 py-3">ชื่อ</th>
                <th class="px-4 py-3">อีเมล</th>
                <th class="px-4 py-3">ยืนยันอีเมล</th>
                <th class="px-4 py-3">Roles</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              <tr v-for="u in filtered" :key="u.id" class="hover:bg-white/5">
                <td class="px-4 py-3 font-medium">{{ u.name ?? '-' }}</td>
                <td class="px-4 py-3">{{ u.email ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-300/80">
                  {{ u.email_verified_at ? 'Yes' : '-' }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="r in u.roles"
                      :key="r.id"
                      class="rounded-full bg-sky-500/15 px-2.5 py-1 text-[11px] font-semibold text-sky-200"
                    >
                      {{ r.title }}
                    </span>
                    <span v-if="!u.roles || u.roles.length === 0" class="text-xs text-slate-300/60">-</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="props.can?.show"
                      :href="`/admin/users/${u.id}`"
                      class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-600"
                    >
                      ดู
                    </Link>
                    <Link
                      v-if="props.can?.edit"
                      :href="`/admin/users/${u.id}/edit`"
                      class="rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-500"
                    >
                      แก้ไข
                    </Link>
                    <button
                      v-if="props.can?.delete"
                      type="button"
                      class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500"
                      @click="destroyOne(u.id)"
                    >
                      ลบ
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filtered.length === 0">
                <td class="px-4 py-10 text-center text-slate-300/60" colspan="5">ไม่พบข้อมูล</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

