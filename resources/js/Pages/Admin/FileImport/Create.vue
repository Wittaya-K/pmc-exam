<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const csrf = computed(() => document.head.querySelector('meta[name="csrf-token"]')?.content ?? '')

const files = ref([])
const submitting = ref(false)
const errorMessage = ref('')

function onPick(e) {
  const picked = Array.from(e.target.files ?? [])
  files.value = picked
  errorMessage.value = ''
}

function removeAt(idx) {
  const next = [...files.value]
  next.splice(idx, 1)
  files.value = next
}

async function submit() {
  errorMessage.value = ''

  if (files.value.length === 0) {
    errorMessage.value = 'กรุณาเลือกไฟล์ที่ต้องการนำเข้าข้อมูล'
    return
  }

  submitting.value = true
  try {
    const fd = new FormData()
    files.value.forEach((f) => fd.append('fileUpload[]', f))

    const res = await window.axios.post('/admin/file_import/save', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const ok = Boolean(res?.data?.success) || Boolean(res?.data?.status)
    if (!ok) {
      errorMessage.value = 'นำเข้าไม่สำเร็จ กรุณาตรวจสอบไฟล์และลองใหม่อีกครั้ง'
      return
    }

    window.location.href = '/admin/file_import'
  } catch (err) {
    errorMessage.value = 'เกิดข้อผิดพลาดระหว่างนำเข้า กรุณาลองใหม่อีกครั้ง'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Head title="นำเข้าไฟล์ข้อมูลผู้สอบ (Excel)" />

  <AdminLayout>
    <div class="space-y-6">
      <div>
        <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">IMPORT</div>
        <h1 class="mt-1 text-2xl font-semibold">นำเข้าไฟล์ข้อมูลผู้สอบ (Excel)</h1>
        <p class="mt-2 text-sm text-slate-300/70">รองรับไฟล์ .xlsx, .xls และเลือกได้หลายไฟล์ (สูงสุด 100 MB/ไฟล์)</p>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2">
            <label class="text-sm font-semibold">ไฟล์ Excel</label>
            <div class="mt-2 rounded-xl border border-dashed border-white/15 bg-slate-950/30 p-5">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-300/70">เลือกไฟล์ที่ต้องการนำเข้า</div>
                <label class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-amber-300 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-200">
                  เลือกไฟล์
                  <input
                    class="hidden"
                    type="file"
                    multiple
                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                    @change="onPick"
                  />
                </label>
              </div>

              <div class="mt-4 text-xs text-slate-300/60">
                {{ files.length ? `เลือกแล้ว ${files.length} ไฟล์` : 'ยังไม่มีไฟล์ที่เลือก' }}
              </div>

              <div v-if="files.length" class="mt-4 space-y-2">
                <div
                  v-for="(f, idx) in files"
                  :key="f.name + idx"
                  class="flex items-center justify-between rounded-lg bg-slate-950/40 px-3 py-2 text-sm"
                >
                  <div class="min-w-0">
                    <div class="truncate font-medium">{{ f.name }}</div>
                    <div class="text-xs text-slate-300/60">{{ Math.ceil(f.size / 1024 / 1024) }} MB</div>
                  </div>
                  <button type="button" class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-semibold hover:bg-white/10" @click="removeAt(idx)">
                    เอาออก
                  </button>
                </div>
              </div>
            </div>

            <div v-if="errorMessage" class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
              {{ errorMessage }}
            </div>
          </div>

          <div class="lg:col-span-1">
            <div class="rounded-xl border border-white/10 bg-slate-950/30 p-5">
              <div class="text-sm font-semibold">ดำเนินการ</div>
              <div class="mt-3 space-y-2 text-xs text-slate-300/70">
                <div>- กด “ยืนยัน” เพื่อเริ่มนำเข้าข้อมูล</div>
                <div>- เมื่อนำเข้าสำเร็จจะกลับไปหน้า “นำเข้าไฟล์ข้อมูล”</div>
              </div>

              <div class="mt-5 flex flex-col gap-2">
                <button
                  type="button"
                  class="rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-400 disabled:opacity-60"
                  :disabled="submitting"
                  @click="submit"
                >
                  {{ submitting ? 'กำลังนำเข้า…' : 'ยืนยัน' }}
                </button>

                <a
                  href="/admin/file_import"
                  class="rounded-lg bg-white/5 px-4 py-2.5 text-center text-sm font-semibold hover:bg-white/10"
                >
                  กลับหน้ารายการ
                </a>
              </div>
            </div>

            <input type="hidden" name="_token" :value="csrf" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

