const THEME_KEY = 'pmc-theme' // 'light' | 'dark' | 'system'

export function getStoredTheme() {
  const v = localStorage.getItem(THEME_KEY)
  return v === 'light' || v === 'dark' || v === 'system' ? v : 'system'
}

export function setStoredTheme(mode) {
  localStorage.setItem(THEME_KEY, mode)
}

export function isSystemDark() {
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
}

export function applyTheme(mode) {
  const m = mode ?? getStoredTheme()
  const resolved = m === 'system' ? (isSystemDark() ? 'dark' : 'light') : m

  const root = document.documentElement
  root.classList.toggle('dark', resolved === 'dark')
  root.dataset.theme = m
}

export function setupSystemThemeListener() {
  if (!window.matchMedia) return () => {}
  const mq = window.matchMedia('(prefers-color-scheme: dark)')
  const handler = () => {
    const current = getStoredTheme()
    if (current === 'system') applyTheme('system')
  }
  mq.addEventListener?.('change', handler)
  return () => mq.removeEventListener?.('change', handler)
}

