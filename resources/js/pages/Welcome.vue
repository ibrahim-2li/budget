<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { showLogin, showRegister } from '@/actions/App/Http/Controllers/AuthController'

const isDark = ref(false)

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark'
    document.documentElement.classList.toggle('dark', isDark.value)
})

function toggleDark() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 dark:bg-gray-900">
        <button
            @click="toggleDark"
            class="absolute top-4 right-4 rounded-md p-2 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <div class="text-center">
            <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-white">Budget App</h1>
            <p class="mb-8 text-gray-500 dark:text-gray-400">Track your income and expenses with ease.</p>

            <div class="flex gap-4 justify-center">
                <Link
                    :href="showLogin.url()"
                    class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Login
                </Link>
                <Link
                    :href="showRegister.url()"
                    class="rounded-md border border-indigo-600 px-6 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:border-indigo-400 dark:hover:bg-gray-800"
                >
                    Register
                </Link>
            </div>
        </div>
    </div>
</template>
