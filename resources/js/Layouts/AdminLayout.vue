<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { applyTheme, getStoredTheme, setStoredTheme } from '../theme'

const sidebarOpen = ref(false)
const page = usePage()

const userEmail = computed(() => page.props?.auth?.user?.email ?? '')

const currentPath = computed(() => page.url ?? '')
const isActive = (prefix) => currentPath.value === prefix || currentPath.value.startsWith(prefix + '/')

const userMgmtOpen = ref(false)
const userMgmtActive = computed(() => isActive('/admin/users') || isActive('/admin/roles') || isActive('/admin/permissions'))

const themeMenuOpen = ref(false)
const themeMode = ref(getStoredTheme()) // light|dark|system

function setTheme(mode) {
  themeMode.value = mode
  setStoredTheme(mode)
  applyTheme(mode)
  themeMenuOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <!-- Mobile overlay -->
    <button
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/60 md:hidden"
      type="button"
      aria-label="Close sidebar"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white/95 backdrop-blur transition-transform md:translate-x-0 dark:border-white/10 dark:bg-slate-950/95"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5 dark:border-white/10">
        <img src="/image/logo.png" alt="PMC" class="h-9 w-9 rounded-full bg-white/10 object-contain" />
        <div class="min-w-0">
          <div class="truncate text-sm font-semibold">ระบบบริหารการจัดสอบ</div>
          <div class="truncate text-xs text-slate-500 dark:text-slate-300/70">PMC Admin</div>
        </div>
      </div>

      <nav class="p-4">
        <div class="text-[11px] font-semibold tracking-[0.18em] text-slate-500 dark:text-slate-300/60">เมนู</div>

        <div class="mt-3 space-y-1">
          <Link
            href="/admin"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
            :class="isActive('/admin') && currentPath === '/admin' ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
            @click="sidebarOpen = false"
          >
            <span class="grid size-8 place-items-center rounded-md bg-slate-900/5 dark:bg-white/5">🏠</span>
            <span>แดชบอร์ด</span>
          </Link>

          <a
            href="/admin/test_center"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
            :class="isActive('/admin/test_center') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
            @click="sidebarOpen = false"
          >
            <span class="grid size-8 place-items-center rounded-md bg-slate-900/5 dark:bg-white/5">🏫</span>
            <span>ศูนย์สอบ</span>
          </a>

          <a
            href="/admin/file_import"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
            :class="isActive('/admin/file_import') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
            @click="sidebarOpen = false"
          >
            <span class="grid size-8 place-items-center rounded-md bg-slate-900/5 dark:bg-white/5">📄</span>
            <span>นำเข้าไฟล์ข้อมูล</span>
          </a>

          <a
            href="/admin/arrange_seat"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
            :class="isActive('/admin/arrange_seat') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
            @click="sidebarOpen = false"
          >
            <span class="grid size-8 place-items-center rounded-md bg-slate-900/5 dark:bg-white/5">🪑</span>
            <span>จัดที่นั่งสอบ</span>
          </a>

          <!-- User Management dropdown -->
          <div class="rounded-lg">
            <button
              type="button"
              class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
              :class="userMgmtActive ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
              @click="userMgmtOpen = !userMgmtOpen"
            >
              <span class="flex items-center gap-3">
                <span class="grid size-8 place-items-center rounded-md bg-slate-900/5 dark:bg-white/5">👥</span>
                <span>จัดการผู้ใช้งาน</span>
              </span>
              <span class="text-xs text-slate-500 dark:text-slate-300/70">{{ userMgmtOpen ? '▾' : '▸' }}</span>
            </button>

            <div v-show="userMgmtOpen || userMgmtActive" class="mt-1 space-y-1 pl-11">
              <a
                href="/admin/permissions"
                class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                :class="isActive('/admin/permissions') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                @click="sidebarOpen = false"
              >
                การเข้าถึง
              </a>
              <a
                href="/admin/roles"
                class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                :class="isActive('/admin/roles') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                @click="sidebarOpen = false"
              >
                สิทธิ์
              </a>
              <a
                href="/admin/users"
                class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                :class="isActive('/admin/users') ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                @click="sidebarOpen = false"
              >
                ผู้ใช้งาน
              </a>
            </div>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main -->
    <div class="md:pl-72">
      <!-- Topbar -->
      <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-white/10 dark:bg-slate-950/80">
        <div class="flex h-16 items-center justify-between px-4 md:px-6">
          <div class="flex items-center gap-3">
            <button
              type="button"
              class="grid size-10 place-items-center rounded-lg bg-slate-900/5 text-slate-900 hover:bg-slate-900/10 md:hidden dark:bg-white/5 dark:text-slate-100 dark:hover:bg-white/10"
              aria-label="Open sidebar"
              @click="sidebarOpen = true"
            >
              ☰
            </button>
            <div class="text-sm text-slate-600 dark:text-slate-300/70">
              <span class="font-semibold text-slate-900 dark:text-slate-100">PMC</span>
              <span class="mx-2 text-slate-400/60 dark:text-white/20">/</span>
              <span>Admin</span>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div v-if="userEmail" class="hidden text-sm text-slate-600 sm:block dark:text-slate-300/80">
              ผู้ใช้งาน: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ userEmail }}</span>
            </div>

            <div class="relative">
              <button
                type="button"
                class="rounded-lg bg-slate-900/5 px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-900/10 dark:bg-white/5 dark:text-slate-100 dark:hover:bg-white/10"
                @click="themeMenuOpen = !themeMenuOpen"
              >
                ธีม: {{ themeMode }}
              </button>

              <div
                v-if="themeMenuOpen"
                class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-white/10 dark:bg-slate-900"
              >
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                  :class="themeMode === 'light' ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                  @click="setTheme('light')"
                >
                  Light
                </button>
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                  :class="themeMode === 'dark' ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                  @click="setTheme('dark')"
                >
                  Dark
                </button>
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-900/5 dark:hover:bg-white/5"
                  :class="themeMode === 'system' ? 'bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
                  @click="setTheme('system')"
                >
                  System
                </button>
              </div>
            </div>

            <a
              href="/logout-azure"
              class="rounded-lg bg-amber-300 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-200"
            >
              ออกจากระบบ
            </a>
          </div>
        </div>
      </header>

      <main class="px-4 py-6 md:px-6">
        <slot />
      </main>

      <footer class="border-t border-slate-200 px-4 py-4 text-xs text-slate-600 md:px-6 dark:border-white/10 dark:text-slate-300/60">
        <span>© 2026 พัฒนาโดย สาขาวิทยาศาสตร์การคำนวณ คณะวิทยาศาสตร์</span>
      </footer>
    </div>
  </div>
</template>

