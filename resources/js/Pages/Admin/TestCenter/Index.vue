<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  can: {
    type: Object,
    default: () => ({ create: false, edit: false, delete: false }),
  },
})

const csrf = computed(() => document.head.querySelector('meta[name="csrf-token"]')?.content ?? '')

const loading = ref(false)
const rows = ref([])
const selected = ref(new Set())

const modalOpen = ref(false)
const saving = ref(false)
const form = ref({
  id: null,
  test_center: '',
  building: '',
  floor: '',
  room: '',
  capacity: '',
  air_condition: '',
  fan: '',
})

const isEdit = computed(() => !!form.value.id)
const selectedCount = computed(() => selected.value.size)
const allSelected = computed(() => rows.value.length > 0 && selected.value.size === rows.value.length)

function resetForm() {
  form.value = {
    id: null,
    test_center: '',
    building: '',
    floor: '',
    room: '',
    capacity: '',
    air_condition: '',
    fan: '',
  }
}

async function fetchRows() {
  loading.value = true
  try {
    const res = await window.axios.get('/admin/test_center')
    rows.value = res?.data?.data ?? []
    selected.value = new Set()
  } finally {
    loading.value = false
  }
}

function toggleAll() {
  if (allSelected.value) {
    selected.value = new Set()
    return
  }
  selected.value = new Set(rows.value.map((r) => r.id))
}

function toggleRow(id) {
  const next = new Set(selected.value)
  if (next.has(id)) next.delete(id)
  else next.add(id)
  selected.value = next
}

function openCreate() {
  resetForm()
  modalOpen.value = true
}

async function openEdit(id) {
  const res = await window.axios.get(`/admin/test_center/${id}/edit`)
  form.value = {
    id: res.data.id,
    test_center: res.data.test_center ?? '',
    building: res.data.building ?? '',
    floor: res.data.floor ?? '',
    room: res.data.room ?? '',
    capacity: res.data.capacity ?? '',
    air_condition: res.data.air_condition ?? '',
    fan: res.data.fan ?? '',
  }
  modalOpen.value = true
}

async function save() {
  saving.value = true
  try {
    await window.axios.post('/admin/test_center', { ...form.value })
    modalOpen.value = false
    await fetchRows()
  } finally {
    saving.value = false
  }
}

async function destroyOne(id) {
  if (!confirm('ยืนยันการลบรายการนี้ ?')) return
  await window.axios.delete(`/admin/test_center/${id}`)
  await fetchRows()
}

async function bulkDelete() {
  if (selected.value.size === 0) return
  if (!confirm(`ยืนยันการลบ ${selected.value.size} รายการ ?`)) return
  await window.axios.post('/admin/test_center/bulk-delete', { ids: Array.from(selected.value) })
  await fetchRows()
}

onMounted(fetchRows)
</script>

<template>
  <Head title="ศูนย์สอบ" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">SETTINGS</div>
          <h1 class="mt-1 text-2xl font-semibold">ศูนย์สอบ</h1>
          <p class="mt-2 text-sm text-slate-300/70">จัดการข้อมูลศูนย์สอบ</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            v-if="props.can?.create"
            type="button"
            class="rounded-lg bg-amber-300 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-200"
            @click="openCreate"
          >
            เพิ่มศูนย์สอบ
          </button>

          <a
            v-if="props.can?.create"
            href="/admin/test_center/create"
            class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-400"
          >
            นำเข้า Excel
          </a>

          <form v-if="props.can?.create" method="POST" action="/admin/test_center/resetTestCenter">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="rounded-lg bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-600"
              onclick="return confirm('ยืนยันการรีเซ็ตศูนย์สอบทั้งหมด ?')"
            >
              รีเซ็ต
            </button>
          </form>

          <form method="POST" action="/admin/test_center/exportFile">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="rounded-lg bg-sky-500 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-400"
            >
              ดาวน์โหลด Excel
            </button>
          </form>

          <button
            v-if="props.can?.delete"
            type="button"
            :disabled="selectedCount === 0"
            class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white disabled:opacity-40"
            @click="bulkDelete"
          >
            ลบที่เลือก ({{ selectedCount }})
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
          <div class="text-sm font-semibold">รายการ</div>
          <div class="text-xs text-slate-300/60">
            <span v-if="loading">กำลังโหลด…</span>
            <span v-else>ทั้งหมด {{ rows.length }} รายการ</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-950/40 text-left text-xs text-slate-300/70">
              <tr>
                <th class="px-4 py-3">
                  <input type="checkbox" :checked="allSelected" @change="toggleAll" />
                </th>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">ศูนย์สอบ</th>
                <th class="px-4 py-3">อาคาร/ตึก</th>
                <th class="px-4 py-3">ชั้น</th>
                <th class="px-4 py-3">ห้อง</th>
                <th class="px-4 py-3">ความจุ</th>
                <th class="px-4 py-3">แอร์</th>
                <th class="px-4 py-3">พัดลม</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
              <tr v-for="(r, idx) in rows" :key="r.id" class="hover:bg-white/5">
                <td class="px-4 py-3">
                  <input type="checkbox" :checked="selected.has(r.id)" @change="toggleRow(r.id)" />
                </td>
                <td class="px-4 py-3 text-slate-300/80">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-medium">{{ r.test_center }}</td>
                <td class="px-4 py-3">{{ r.building }}</td>
                <td class="px-4 py-3">{{ r.floor }}</td>
                <td class="px-4 py-3">{{ r.room }}</td>
                <td class="px-4 py-3">{{ r.capacity }}</td>
                <td class="px-4 py-3">{{ r.air_condition }}</td>
                <td class="px-4 py-3">{{ r.fan }}</td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <button
                      v-if="props.can?.edit"
                      type="button"
                      class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-600"
                      @click="openEdit(r.id)"
                    >
                      แก้ไข
                    </button>
                    <button
                      v-if="props.can?.delete"
                      type="button"
                      class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500"
                      @click="destroyOne(r.id)"
                    >
                      ลบ
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && rows.length === 0">
                <td class="px-4 py-10 text-center text-slate-300/60" colspan="10">ไม่มีข้อมูล</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/60" @click="modalOpen = false" />
      <div class="relative w-full max-w-5xl rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-xl">
        <div class="flex items-center justify-between">
          <div class="text-lg font-semibold">{{ isEdit ? 'แก้ไขศูนย์สอบ' : 'เพิ่มศูนย์สอบ' }}</div>
          <button type="button" class="rounded-lg bg-white/5 px-3 py-1.5 text-sm hover:bg-white/10" @click="modalOpen = false">
            ปิด
          </button>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ศูนย์สอบ</label>
            <input v-model="form.test_center" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm" />
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">อาคาร/ตึก</label>
            <input v-model="form.building" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm" />
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ชั้น</label>
            <input v-model="form.floor" type="number" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm" />
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ห้อง</label>
            <input v-model="form.room" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm" />
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ความจุ</label>
            <input v-model="form.capacity" type="number" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm" />
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ห้องแอร์</label>
            <select v-model="form.air_condition" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm">
              <option value="">เลือก</option>
              <option value="Y">ใช่</option>
              <option value="N">ไม่ใช่</option>
            </select>
          </div>
          <div class="md:col-span-1">
            <label class="text-xs text-slate-300/70">ห้องพัดลม</label>
            <select v-model="form.fan" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950/40 text-sm">
              <option value="">เลือก</option>
              <option value="Y">ใช่</option>
              <option value="N">ไม่ใช่</option>
            </select>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="rounded-lg bg-white/5 px-4 py-2 text-sm font-semibold hover:bg-white/10" @click="modalOpen = false">
            ยกเลิก
          </button>
          <button
            type="button"
            class="rounded-lg bg-amber-300 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-200 disabled:opacity-60"
            :disabled="saving"
            @click="save"
          >
            {{ saving ? 'กำลังบันทึก…' : 'บันทึก' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

