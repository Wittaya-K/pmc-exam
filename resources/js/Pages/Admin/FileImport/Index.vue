<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const csrf = computed(() => document.head.querySelector('meta[name="csrf-token"]')?.content ?? '')

const loading = ref(false)
const rows = ref([])

const columns = [
  { key: 'id', label: 'รหัสประจำตัวสอบ' },
  { key: 'title_th', label: 'คำนำหน้าชื่อไทย' },
  { key: 'first_name_th', label: 'ชื่อไทย' },
  { key: 'last_name_th', label: 'สกุลไทย' },
  { key: 'school', label: 'โรงเรียน' },
  { key: 'program_name', label: 'ระดับการสอบ' },
  { key: 'test_center', label: 'ศูนย์สอบ' },
  { key: 'payment_status', label: 'สถานะการชำระเงิน' },
]

async function fetchRows() {
  loading.value = true
  try {
    const res = await window.axios.get('/admin/file_import/list')
    rows.value = res?.data?.data ?? []
  } finally {
    loading.value = false
  }
}

onMounted(fetchRows)
</script>

<template>
  <Head title="นำเข้าไฟล์ข้อมูล" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">IMPORT</div>
          <h1 class="mt-1 text-2xl font-semibold">นำเข้าไฟล์ข้อมูล</h1>
          <p class="mt-2 text-sm text-slate-300/70">รายชื่อผู้เข้าสอบจากไฟล์นำเข้า</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <a
            href="/admin/file_import/create"
            class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-400"
          >
            นำเข้าข้อมูล
          </a>

          <form method="POST" action="/admin/file_import/resetStudentImport" class="inline">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500"
              onclick="return confirm('ยืนยันการรีเซ็ตข้อมูลทั้งหมด ?')"
            >
              รีเซ็ตข้อมูล
            </button>
          </form>

          <button
            type="button"
            class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10"
            :disabled="loading"
            @click="fetchRows"
          >
            {{ loading ? 'กำลังโหลด…' : 'รีเฟรช' }}
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
          <div class="text-sm font-semibold">รายชื่อผู้เข้าสอบ</div>
          <div class="text-xs text-slate-300/60">
            <span v-if="loading">กำลังโหลด…</span>
            <span v-else>ทั้งหมด {{ rows.length }} รายการ</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-950/40 text-left text-xs text-slate-300/70">
              <tr>
                <th class="px-4 py-3">#</th>
                <th v-for="c in columns" :key="c.key" class="px-4 py-3">
                  {{ c.label }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              <tr v-for="(r, idx) in rows" :key="r.id" class="hover:bg-white/5">
                <td class="px-4 py-3 text-slate-300/80">{{ idx + 1 }}</td>
                <td v-for="c in columns" :key="c.key" class="px-4 py-3">
                  {{ r?.[c.key] ?? '' }}
                </td>
              </tr>

              <tr v-if="!loading && rows.length === 0">
                <td class="px-4 py-10 text-center text-slate-300/60" :colspan="columns.length + 1">
                  ไม่มีข้อมูล
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

