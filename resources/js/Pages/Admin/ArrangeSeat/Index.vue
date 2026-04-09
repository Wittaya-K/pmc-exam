<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  countStudent: { type: Number, default: 0 },
  countRoom: { type: Number, default: 0 },
  countSeatAssign: { type: Number, default: 0 },
  selectTestcenter: { type: Array, default: () => [] }, // [{ test_center, total }]
})

const csrf = computed(() => document.head.querySelector('meta[name="csrf-token"]')?.content ?? '')

const assigning = ref(false)
const errorMessage = ref('')
const run = ref(null) // { id, status, started_at, finished_at, error }
const runId = ref(null)
let pollTimer = null

const canAssign = computed(() => props.countSeatAssign < 1 && !assigning.value && !(run.value && (run.value.status === 'queued' || run.value.status === 'running')))
const canExport = computed(() => props.countSeatAssign > 0)

async function fetchStatus() {
  const url = runId.value
    ? `/admin/arrange_seat/assignSeats/status?run_id=${encodeURIComponent(runId.value)}`
    : '/admin/arrange_seat/assignSeats/status'
  const res = await window.axios.get(url)
  run.value = res?.data?.data ?? null
  if (run.value?.id) runId.value = run.value.id
  return run.value
}

function startPolling() {
  if (pollTimer) return
  pollTimer = window.setInterval(async () => {
    const r = await fetchStatus()
    if (!r) return
    if (r.status === 'succeeded' || r.status === 'failed') {
      stopPolling()
      // update dashboard counts by reloading once finished
      window.location.reload()
    }
  }, 2000)
}

function stopPolling() {
  if (!pollTimer) return
  window.clearInterval(pollTimer)
  pollTimer = null
}

async function assignSeats() {
  if (!canAssign.value) return
  errorMessage.value = ''
  assigning.value = true
  try {
    const res = await window.axios.post('/admin/arrange_seat/assignSeats', new FormData())
    const ok = Boolean(res?.data?.status)
    if (!ok) {
      errorMessage.value = res?.data?.message ?? 'จัดห้องสอบไม่สำเร็จ กรุณาลองใหม่อีกครั้ง'
      return
    }
    runId.value = res?.data?.run_id ?? null
    await fetchStatus()
    startPolling()
  } catch (e) {
    errorMessage.value = 'เกิดข้อผิดพลาดระหว่างจัดห้องสอบ'
  } finally {
    assigning.value = false
  }
}

onMounted(async () => {
  const r = await fetchStatus()
  if (r && (r.status === 'queued' || r.status === 'running')) {
    startPolling()
  }
})

onBeforeUnmount(() => {
  stopPolling()
})
</script>

<template>
  <Head title="จัดที่นั่งสอบอัตโนมัติ" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">ARRANGE SEATS</div>
          <h1 class="mt-1 text-2xl font-semibold">จัดที่นั่งสอบอัตโนมัติ</h1>
          <p class="mt-2 text-sm text-slate-300/70">จัดการห้องสอบและสรุปสถานะการจัดที่นั่ง</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg bg-amber-300 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-200 disabled:opacity-50"
            :disabled="!canAssign"
            @click="assignSeats"
          >
            {{ assigning ? 'กำลังจัดห้องสอบ…' : 'จัดห้องสอบ' }}
          </button>

          <a
            href="/admin/arrange_seat/view"
            class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10"
          >
            ที่นั่งสอบ
          </a>

          <form method="POST" action="/admin/arrange_seat/exportFile">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-400 disabled:opacity-50"
              :disabled="!canExport"
            >
              ส่งออกไฟล์ Excel
            </button>
          </form>

          <form method="POST" action="/admin/arrange_seat/resetAssignSeats">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500"
              onclick="return confirm('ยืนยันการรีเซ็ตการจัดสอบทั้งหมด ?')"
            >
              รีเซ็ตการจัดสอบ
            </button>
          </form>
        </div>
      </div>

      <div v-if="errorMessage" class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
        {{ errorMessage }}
      </div>

      <div
        v-if="run && (run.status === 'queued' || run.status === 'running')"
        class="rounded-xl border border-amber-300/30 bg-amber-300/10 px-4 py-3 text-sm text-amber-200"
      >
        กำลังจัดห้องสอบ… (สถานะ: {{ run.status }})
      </div>

      <div
        v-if="run && run.status === 'failed'"
        class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
      >
        ล้มเหลว: {{ run.error || 'ไม่ทราบสาเหตุ' }}
      </div>

      <!-- Dashboard cards -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
          <div class="text-3xl">👥</div>
          <div class="mt-4 text-xs text-slate-300/60">นักเรียนทั้งหมด</div>
          <div class="mt-1 text-xl font-semibold">{{ countStudent.toLocaleString() }} คน</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
          <div class="text-3xl">🧱</div>
          <div class="mt-4 text-xs text-slate-300/60">ห้องสอบที่จัดแล้ว</div>
          <div class="mt-1 text-xl font-semibold">{{ countRoom.toLocaleString() }} ห้อง</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
          <div class="text-3xl">🪑</div>
          <div class="mt-4 text-xs text-slate-300/60">ที่นั่งที่จัดแล้ว</div>
          <div class="mt-1 text-xl font-semibold">{{ countSeatAssign.toLocaleString() }} ที่นั่ง</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
          <div class="text-3xl">📊</div>
          <div class="mt-4 text-xs text-slate-300/60">ต่อห้องโดยประมาณ</div>
          <div class="mt-1 text-xl font-semibold">30 ที่นั่ง</div>
        </div>
      </div>

      <!-- Centers -->
      <div class="rounded-2xl border border-white/10 bg-white/5">
        <div class="border-b border-white/10 px-4 py-3 text-sm font-semibold">ศูนย์สอบที่จัดแล้ว</div>
        <div class="p-4">
          <select multiple class="h-56 w-full rounded-xl border-white/10 bg-slate-950/30 text-sm">
            <option v-for="c in selectTestcenter" :key="c.test_center" :value="c.test_center">
              {{ c.test_center }} {{ Number(c.total).toLocaleString() }} คน
            </option>
          </select>
          <div class="mt-2 text-xs text-slate-300/60">หมายเหตุ: รายการนี้แสดงศูนย์สอบที่มีการจัดที่นั่งแล้ว</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

