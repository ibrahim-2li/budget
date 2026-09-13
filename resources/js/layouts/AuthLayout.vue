<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useI18n } from '@/lib/i18n';
import { home } from '@/routes';

defineProps<{
    /** Heading rendered above the form card. */
    title: string;
    /** Supporting line rendered under the heading. */
    subtitle: string;
}>();

const { t } = useI18n();

const isDark = ref(false);

const highlights = [
    {
        icon: 'fa-solid fa-wallet',
        title: 'Every riyal accounted for',
        body: 'Log income and expenses by category and watch your balance update instantly.',
    },
    {
        icon: 'fa-solid fa-bullseye',
        title: 'Goals that stay on track',
        body: 'Set savings targets and follow your progress period after period.',
    },
    {
        icon: 'fa-solid fa-chart-pie',
        title: 'Spending you can actually see',
        body: 'Category breakdowns show where the money really goes each month.',
    },
];

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark';
    document.documentElement.classList.toggle('dark', isDark.value);
});

function toggleDark(): void {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}
</script>

<template>
    <div class="flex min-h-screen bg-white dark:bg-gray-950">
        <!-- Brand panel -->
        <div
            class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-primary-600 p-12 lg:flex"
        >
            <div
                class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary-400/30 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-secondary-400/30 blur-3xl"
            ></div>

            <Link
                :href="home.url()"
                class="relative flex items-center gap-3 text-white"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm"
                >
                    <AppLogo size="h-full w-full" />
                </span>
                <span class="text-lg font-semibold tracking-tight">{{ t('Budget') }}</span>
            </Link>

            <div class="relative">
                <h2
                    class="max-w-sm text-3xl leading-tight font-bold text-white"
                >
                    {{ t('Take control of your money, one period at a time.') }}
                </h2>

                <ul class="mt-10 space-y-6">
                    <li
                        v-for="item in highlights"
                        :key="item.title"
                        class="flex gap-4"
                    >
                        <span
                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white backdrop-blur"
                        >
                            <i :class="item.icon" class="text-sm"></i>
                        </span>
                        <div>
                            <p class="font-medium text-white">
                                {{ t(item.title) }}
                            </p>
                            <p
                                class="mt-1 text-sm leading-relaxed text-primary-100"
                            >
                                {{ t(item.body) }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-primary-200">
                {{ t('Private by default. Your data stays yours.') }}
            </p>
        </div>

        <!-- Form panel -->
        <div
            class="relative flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2"
        >
            <div class="absolute top-6 end-6 flex items-center gap-1">
            <LanguageSwitcher />
            <button
                type="button"
                @click="toggleDark"
                class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                :aria-label="
                    isDark ? t('Switch to light mode') : t('Switch to dark mode')
                "
            >
                <i
                    :class="isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"
                    class="h-4 w-4 text-center"
                ></i>
            </button>
            </div>

            <div class="w-full max-w-sm">
                <Link
                    :href="home.url()"
                    class="mb-10 flex items-center gap-3 lg:hidden"
                >
                    <AppLogo size="h-10 w-10" />
                    <span
                        class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white"
                        >{{ t('Budget') }}</span
                    >
                </Link>

                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ subtitle }}
                </p>

                <div class="mt-8">
                    <slot />
                </div>

                <p
                    class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400"
                >
                    <slot name="footer" />
                </p>
            </div>
        </div>
    </div>
</template>
