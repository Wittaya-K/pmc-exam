<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  permissions: { type: Array, default: () => [] }, // [{ id, title }]
  can: { type: Object, default: () => ({}) },
})

const q = ref('')
const filtered = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return props.permissions
  return props.permissions.filter((p) => String(p.title ?? '').toLowerCase().includes(term))
})

async function destroyOne(id) {
  if (!confirm('ยืนยันการลบรายการนี้ ?')) return
  await window.axios.delete(`/admin/permissions/${id}`)
  window.location.reload()
}
</script>

<template>
  <Head title="การเข้าถึง" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">การเข้าถึง</h1>
          <p class="mt-2 text-sm text-slate-300/70">จัดการรายการ permission</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <Link
            v-if="props.can?.create"
            href="/admin/permissions/create"
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
              placeholder="ค้นหา…"
              class="w-64 rounded-lg border-white/10 bg-slate-950/30 text-sm placeholder:text-slate-400/60"
            />
            <div class="text-xs text-slate-300/60">ทั้งหมด {{ filtered.length }} รายการ</div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-950/40 text-left text-xs text-slate-300/70">
              <tr>
                <th class="px-4 py-3">ชื่อ</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              <tr v-for="p in filtered" :key="p.id" class="hover:bg-white/5">
                <td class="px-4 py-3 font-medium">{{ p.title }}</td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="props.can?.show"
                      :href="`/admin/permissions/${p.id}`"
                      class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-600"
                    >
                      ดู
                    </Link>
                    <Link
                      v-if="props.can?.edit"
                      :href="`/admin/permissions/${p.id}/edit`"
                      class="rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-500"
                    >
                      แก้ไข
                    </Link>
                    <button
                      v-if="props.can?.delete"
                      type="button"
                      class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500"
                      @click="destroyOne(p.id)"
                    >
                      ลบ
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filtered.length === 0">
                <td class="px-4 py-10 text-center text-slate-300/60" colspan="2">ไม่พบข้อมูล</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

