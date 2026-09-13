<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { logout } from '@/actions/App/Http/Controllers/AuthController';
import { index as budgetIndex } from '@/actions/App/Http/Controllers/BudgetController';
import AppLogo from '@/components/AppLogo.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import MobileBottomNav from '@/components/MobileBottomNav.vue';
import { useI18n } from '@/lib/i18n';
import { dashboard, users, categories } from '@/routes/admin';
import goals from '@/routes/goals';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const { t } = useI18n();
const flash = computed(() => page.props.flash?.success);

const isDark = ref(false);

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark';
    document.documentElement.classList.toggle('dark', isDark.value);
});

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

const navigation = [

    { name: 'Dashboard', href: dashboard.url(), icon: 'fa-solid fa-chart-pie' },
    { name: 'Users', href: users.url(), icon: 'fa-solid fa-users' },
    { name: 'Categories', href: categories.url(), icon: 'fa-solid fa-tags' },
    { name: 'My budget', href: budgetIndex.url(), icon: 'fa-solid fa-wallet' },
];

const linkClass = (href: string) => {
    const active = page.url.split('?')[0] === href;

    return [
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
        active
            ? 'bg-primary-600 text-white shadow-sm'
            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
    ];
};
</script>

<template>
    <div
        class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100"
    >
        <!-- Top bar -->
        <header
            class="sticky top-0 z-30 border-b border-gray-200 bg-white/85 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/85"
        >
            <div
                class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:h-16 sm:px-6"
            >
                <div class="flex items-center gap-2.5">
                    <AppLogo size="h-8 w-8" />
                    <div class="leading-tight">
                        <span
                            class="block text-sm font-bold tracking-tight sm:text-base"
                            >{{ t('Admin') }}</span
                        >
                        <span
                            class="hidden text-xs text-gray-500 sm:block dark:text-gray-400"
                            >{{ t('Budget App') }}</span
                        >
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                   
                    <LanguageSwitcher />
                    <button
                        @click="toggleDark"
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="
                            isDark
                                ? t('Switch to light mode')
                                : t('Switch to dark mode')
                        "
                    >
                        <i
                            class="fa-solid"
                            :class="isDark ? 'fa-sun' : 'fa-moon'"
                        ></i>
                    </button>
                    <Link
                        :href="logout.url()"
                        method="post"
                        as="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="t('Logout')"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </Link>
                </div>
            </div>
        </header>

        <div class="mx-auto flex max-w-7xl gap-6 px-4 pt-5 pb-28 sm:px-6 sm:pt-8 lg:pb-8">
            <!-- Sidebar (desktop) -->
            <aside class="hidden w-56 shrink-0 lg:block">
                <nav class="sticky top-24 space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :class="linkClass(item.href)"
                    >
                        <i :class="item.icon" class="w-4 text-center"></i>
                        {{ t(item.name) }}
                    </Link>
                </nav>
            </aside>

            <main class="min-w-0 flex-1">
                <div
                    class="mb-5 flex flex-wrap items-end justify-between gap-3"
                >
                    <div>
                        <h1
                            class="text-xl font-bold tracking-tight sm:text-2xl"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="subtitle"
                            class="mt-0.5 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ subtitle }}
                        </p>
                    </div>
                    <slot name="actions" />
                </div>

                <Transition
                    enter-active-class="transition duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                >
                    <div
                        v-if="flash"
                        class="mb-4 flex items-center gap-2 rounded-xl border border-secondary-200 bg-secondary-50 px-4 py-3 text-sm font-medium text-secondary-700 dark:border-secondary-500/30 dark:bg-secondary-500/10 dark:text-secondary-300"
                    >
                        <i class="fa-solid fa-circle-check"></i>
                        {{ flash }}
                    </div>
                </Transition>

                <slot />
            </main>
        </div>

        <!-- Mobile bottom bar -->
        <MobileBottomNav :items="navigation" />
    </div>
</template>
