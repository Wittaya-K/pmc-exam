<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  roles: { type: Array, default: () => [] }, // [{id,title}]
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
})

function submit() {
  form.post('/admin/users', {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="เพิ่มผู้ใช้งาน" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">เพิ่มผู้ใช้งาน</h1>
        </div>
        <Link href="/admin/users" class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10">กลับ</Link>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <form class="space-y-5" @submit.prevent="submit">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="text-sm font-semibold">ชื่อ</label>
              <input v-model="form.name" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
              <div v-if="form.errors.name" class="mt-1 text-xs text-rose-300">{{ form.errors.name }}</div>
            </div>
            <div>
              <label class="text-sm font-semibold">อีเมล</label>
              <input v-model="form.email" type="email" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
              <div v-if="form.errors.email" class="mt-1 text-xs text-rose-300">{{ form.errors.email }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="text-sm font-semibold">รหัสผ่าน</label>
              <input v-model="form.password" type="password" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
              <div v-if="form.errors.password" class="mt-1 text-xs text-rose-300">{{ form.errors.password }}</div>
            </div>
            <div>
              <label class="text-sm font-semibold">ยืนยันรหัสผ่าน</label>
              <input v-model="form.password_confirmation" type="password" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
            </div>
          </div>

          <div>
            <label class="text-sm font-semibold">Roles</label>
            <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <label
                v-for="r in props.roles"
                :key="r.id"
                class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/20 px-3 py-2 text-sm"
              >
                <input type="checkbox" :value="r.id" v-model="form.roles" />
                <span class="truncate">{{ r.title }}</span>
              </label>
            </div>
            <div v-if="form.errors.roles" class="mt-1 text-xs text-rose-300">{{ form.errors.roles }}</div>
          </div>

          <div class="flex justify-end gap-2">
            <Link href="/admin/users" class="rounded-lg bg-white/5 px-4 py-2 text-sm font-semibold hover:bg-white/10">ยกเลิก</Link>
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

