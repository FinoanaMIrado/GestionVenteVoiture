import { ref, watch } from 'vue'

const isDark = ref(false)

const STORAGE_KEY = 'gvv-dark-mode'

function init() {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored !== null) {
        isDark.value = stored === 'true'
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    apply()
}

function apply() {
    document.documentElement.classList.toggle('dark', isDark.value)
}

function toggle() {
    isDark.value = !isDark.value
}

watch(isDark, (val) => {
    localStorage.setItem(STORAGE_KEY, val)
    apply()
})

init()

export function useDarkMode() {
    return { isDark, toggle }
}
