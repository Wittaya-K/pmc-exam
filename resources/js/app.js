import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { applyTheme, setupSystemThemeListener } from './theme'

applyTheme()
setupSystemThemeListener()

const el = document.getElementById('app')
if (!el) {
  throw new Error('Inertia root element "#app" not found. Make sure the response is using resources/views/app.blade.php and contains @inertia.')
}
if (!el.dataset.page) {
  throw new Error('Inertia root element "#app" is missing the "data-page" attribute. Make sure the response contains @inertia and is not a legacy Blade login view.')
}

let initialPage
try {
  initialPage = JSON.parse(el.dataset.page)
} catch (err) {
  const preview = String(el.dataset.page).slice(0, 200)
  throw new Error(`Failed to parse Inertia data-page JSON. Preview: ${preview}`)
}

createInertiaApp({
  page: initialPage,
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue')
    const path = `./Pages/${name}.vue`
    const importer = pages[path]
    if (!importer) {
      throw new Error(`Inertia page not found: ${path}`)
    }
    return importer().then((module) => module.default)
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) }).use(plugin).mount(el)
  },
})
