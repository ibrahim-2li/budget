<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { dashboard, users, categories } from '@/routes/admin'
import { index as budgetIndex } from '@/actions/App/Http/Controllers/BudgetController'
import { logout } from '@/actions/App/Http/Controllers/AuthController'

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
})

const page = usePage()
const flash = computed(() => page.props.flash?.success)

const isDark = ref(false)
const mobileNavOpen = ref(false)

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark'
    document.documentElement.classList.toggle('dark', isDark.value)
})

function toggleDark() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const navigation = [
    { name: 'Dashboard', href: dashboard.url(), icon: 'fa-solid fa-chart-pie' },
    { name: 'Users', href: users.url(), icon: 'fa-solid fa-users' },
    { name: 'Categories', href: categories.url(), icon: 'fa-solid fa-tags' },
]

const linkClass = (href: string) => {
    const active = page.url.split('?')[0] === href
    return [
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
        active
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
    ]
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <!-- Top bar -->
        <header
            class="sticky top-0 z-30 border-b border-gray-200 bg-white/85 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/85">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6">
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="mobileNavOpen = !mobileNavOpen" aria-label="Toggle navigation"
                        class="-ml-1 flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800">
                        <i class="fa-solid" :class="mobileNavOpen ? 'fa-xmark' : 'fa-bars'"></i>
                    </button>
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm text-white shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <div class="leading-tight">
                        <span class="block text-sm font-bold tracking-tight sm:text-base">Admin</span>
                        <span class="hidden text-xs text-gray-500 sm:block dark:text-gray-400">Budget App</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <Link :href="budgetIndex.url()"
                        class="flex h-9 items-center gap-2 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i class="fa-solid fa-wallet"></i>
                        <span class="hidden sm:inline">My budget</span>
                    </Link>
                    <button @click="toggleDark" type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
                        <i class="fa-solid" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
                    </button>
                    <Link :href="logout.url()" method="post" as="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        aria-label="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </Link>
                </div>
            </div>
        </header>

        <div class="mx-auto flex max-w-7xl gap-6 px-4 py-5 sm:px-6 sm:py-8">
            <!-- Sidebar (desktop) -->
            <aside class="hidden w-56 shrink-0 lg:block">
                <nav class="sticky top-24 space-y-1">
                    <Link v-for="item in navigation" :key="item.name" :href="item.href" :class="linkClass(item.href)">
                        <i :class="item.icon" class="w-4 text-center"></i>
                        {{ item.name }}
                    </Link>
                </nav>
            </aside>

            <main class="min-w-0 flex-1">
                <!-- Mobile nav -->
                <nav v-show="mobileNavOpen" class="mb-4 space-y-1 lg:hidden">
                    <Link v-for="item in navigation" :key="item.name" :href="item.href" :class="linkClass(item.href)"
                        @click="mobileNavOpen = false">
                        <i :class="item.icon" class="w-4 text-center"></i>
                        {{ item.name }}
                    </Link>
                </nav>

                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{{ title }}</h1>
                        <p v-if="subtitle" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
                    </div>
                    <slot name="actions" />
                </div>

                <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0 -translate-y-1">
                    <div v-if="flash"
                        class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <i class="fa-solid fa-circle-check"></i>
                        {{ flash }}
                    </div>
                </Transition>

                <slot />
            </main>
        </div>
    </div>
</template>
