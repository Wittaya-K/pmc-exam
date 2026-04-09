<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  testCenter: { type: Array, default: () => [] }, // [{ test_center }]
  fNamelname: { type: Array, default: () => [] }, // [{ first_name_th, last_name_th }]
})

const loading = ref(false)
const errorMessage = ref('')

const selectedCenter = ref('')
const selectedName = ref('')

const nameOptions = ref([]) // filled per center (like old behavior)
const rows = ref([])

const sortedRows = computed(() => {
  // mimic old DataTables order: room asc, seat_no asc
  return [...rows.value].sort((a, b) => {
    const ar = String(a.room ?? '')
    const br = String(b.room ?? '')
    if (ar < br) return -1
    if (ar > br) return 1
    return Number(a.seat_no ?? 0) - Number(b.seat_no ?? 0)
  })
})

function nameValue(r) {
  return `${r.first_name_th ?? ''},${r.last_name_th ?? ''}`
}

async function loadByCenter() {
  if (!selectedCenter.value) {
    rows.value = []
    nameOptions.value = []
    selectedName.value = ''
    return
  }

  loading.value = true
  errorMessage.value = ''
  try {
    const res = await window.axios.post('/admin/arrange_seat/searchStudent', {
      test_center: selectedCenter.value,
    })
    rows.value = res?.data?.data ?? []
    nameOptions.value = rows.value.map((r) => ({
      value: nameValue(r),
      label: `${r.first_name_th ?? ''} ${r.last_name_th ?? ''}`.trim(),
    }))
    selectedName.value = ''
  } catch (e) {
    errorMessage.value = 'เกิดข้อผิดพลาดในการโหลดข้อมูล'
  } finally {
    loading.value = false
  }
}

async function loadByName() {
  if (!selectedName.value) {
    // when cleared, fall back to center listing
    await loadByCenter()
    return
  }

  loading.value = true
  errorMessage.value = ''
  try {
    const res = await window.axios.post('/admin/arrange_seat/getStudent', {
      test_center: selectedCenter.value,
      fNamelname: selectedName.value,
    })
    rows.value = res?.data?.data ?? []
  } catch (e) {
    errorMessage.value = 'เกิดข้อผิดพลาดในการค้นหาข้อมูล'
  } finally {
    loading.value = false
  }
}

watch(selectedCenter, () => {
  loadByCenter()
})

watch(selectedName, () => {
  if (!selectedCenter.value) return
  loadByName()
})

onMounted(() => {
  // keep initial empty like old page
})
</script>

<template>
  <Head title="ที่นั่งสอบ" />

  <AdminLayout>
    <div class="space-y-6">
      <div>
        <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">ARRANGE SEATS</div>
        <h1 class="mt-1 text-2xl font-semibold">ที่นั่งสอบ</h1>
        <p class="mt-2 text-sm text-slate-300/70">ค้นหาที่นั่งสอบตามศูนย์สอบและชื่อผู้เข้าสอบ</p>
      </div>

      <!-- Search -->
      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-semibold">เลือกศูนย์สอบ</label>
            <select v-model="selectedCenter" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm">
              <option value="">เลือก</option>
              <option v-for="c in props.testCenter" :key="c.test_center" :value="c.test_center">
                {{ c.test_center }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold">ชื่อผู้เข้าสอบ</label>
            <select
              v-model="selectedName"
              class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm"
              :disabled="!selectedCenter"
            >
              <option value="">เลือก</option>
              <option v-for="o in nameOptions" :key="o.value" :value="o.value">
                {{ o.label }}
              </option>
            </select>
            <div class="mt-1 text-xs text-slate-300/60">
              <span v-if="!selectedCenter">เลือกศูนย์สอบก่อนเพื่อโหลดรายชื่อ</span>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
          {{ errorMessage }}
        </div>
      </div>

      <!-- Table -->
      <div class="rounded-2xl border border-white/10 bg-white/5">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
          <div class="text-sm font-semibold">ที่นั่งสอบ</div>
          <div class="text-xs text-slate-300/60">
            <span v-if="loading">กำลังโหลด…</span>
            <span v-else>ทั้งหมด {{ sortedRows.length }} รายการ</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-950/40 text-left text-xs text-slate-300/70">
              <tr>
                <th class="px-4 py-3">รหัสประจำตัวสอบ</th>
                <th class="px-4 py-3">ชื่อไทย</th>
                <th class="px-4 py-3">สกุลไทย</th>
                <th class="px-4 py-3">โรงเรียน</th>
                <th class="px-4 py-3">ระดับการสอบ</th>
                <th class="px-4 py-3">ศูนย์สอบ</th>
                <th class="px-4 py-3">ชั้นการศึกษา</th>
                <th class="px-4 py-3">ห้องสอบ</th>
                <th class="px-4 py-3">เลขที่นั่ง</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              <tr v-for="r in sortedRows" :key="r.id" class="hover:bg-white/5">
                <td class="px-4 py-3 font-medium">{{ r.id }}</td>
                <td class="px-4 py-3">{{ r.first_name_th }}</td>
                <td class="px-4 py-3">{{ r.last_name_th }}</td>
                <td class="px-4 py-3">{{ r.school }}</td>
                <td class="px-4 py-3">{{ r.program_name }}</td>
                <td class="px-4 py-3">{{ r.test_center }}</td>
                <td class="px-4 py-3">{{ r.classLevel }}</td>
                <td class="px-4 py-3">{{ r.room }}</td>
                <td class="px-4 py-3">{{ r.seat_no }}</td>
              </tr>

              <tr v-if="!loading && sortedRows.length === 0">
                <td class="px-4 py-10 text-center text-slate-300/60" colspan="9">
                  <span v-if="!selectedCenter">เลือกศูนย์สอบเพื่อแสดงรายการ</span>
                  <span v-else>ไม่พบข้อมูล</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

