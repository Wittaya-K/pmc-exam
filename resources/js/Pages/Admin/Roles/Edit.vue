<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '../../../Layouts/AdminLayout.vue'

const props = defineProps({
  permissions: { type: Array, default: () => [] }, // [{id,title}]
  role: { type: Object, required: true }, // { id, title, permission_ids: [] }
})

const form = useForm({
  title: props.role.title ?? '',
  permissions: Array.isArray(props.role.permission_ids) ? [...props.role.permission_ids] : [],
})

function selectAll() {
  form.permissions = props.permissions.map((p) => p.id)
}

function deselectAll() {
  form.permissions = []
}

function submit() {
  form.put(`/admin/roles/${props.role.id}`, { preserveScroll: true })
}
</script>

<template>
  <Head title="แก้ไขสิทธิ์ (Role)" />

  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs font-semibold tracking-[0.18em] text-slate-300/60">USER MANAGEMENT</div>
          <h1 class="mt-1 text-2xl font-semibold">แก้ไขสิทธิ์ (Role)</h1>
        </div>
        <Link href="/admin/roles" class="rounded-lg bg-white/5 px-3 py-2 text-sm font-semibold hover:bg-white/10">กลับ</Link>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="text-sm font-semibold">ชื่อสิทธิ์ (Title)</label>
            <input v-model="form.title" class="mt-2 w-full rounded-lg border-white/10 bg-slate-950/30 text-sm" />
            <div v-if="form.errors.title" class="mt-1 text-xs text-rose-300">{{ form.errors.title }}</div>
          </div>

          <div>
            <div class="flex flex-wrap items-center justify-between gap-2">
              <label class="text-sm font-semibold">Permissions</label>
              <div class="flex gap-2">
                <button type="button" class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-semibold hover:bg-white/10" @click="selectAll">
                  Select all
                </button>
                <button type="button" class="rounded-lg bg-white/5 px-3 py-1.5 text-xs font-semibold hover:bg-white/10" @click="deselectAll">
                  Deselect all
                </button>
              </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <label
                v-for="p in props.permissions"
                :key="p.id"
                class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/20 px-3 py-2 text-sm"
              >
                <input type="checkbox" :value="p.id" v-model="form.permissions" />
                <span class="truncate">{{ p.title }}</span>
              </label>
            </div>

            <div v-if="form.errors.permissions" class="mt-1 text-xs text-rose-300">{{ form.errors.permissions }}</div>
          </div>

          <div class="flex justify-end gap-2">
            <Link href="/admin/roles" class="rounded-lg bg-white/5 px-4 py-2 text-sm font-semibold hover:bg-white/10">ยกเลิก</Link>
            <button
              type="submit"
              class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500 disabled:opacity-60"
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

