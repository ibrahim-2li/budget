<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import {
    logout,
    showLogin,
    showRegister,
} from '@/actions/App/Http/Controllers/AuthController';
import { index as budgetIndex } from '@/actions/App/Http/Controllers/BudgetController';
import { index as goalsIndex } from '@/actions/App/Http/Controllers/GoalController';
import AppLogo from '@/components/AppLogo.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useI18n } from '@/lib/i18n';
import { home } from '@/routes';

const page = usePage();
const { t } = useI18n();
const user = computed(() => page.props.auth?.user);

const isDark = ref(false);

const features = [
    {
        icon: 'fa-solid fa-wallet',
        title: 'Income & expenses',
        body: 'Record what comes in and what goes out, each entry tagged to a category and a budget period.',
    },
    {
        icon: 'fa-solid fa-calendar-days',
        title: 'Period-based budgets',
        body: 'Every month stands on its own, so last month’s overspend never muddies this month’s picture.',
    },
    {
        icon: 'fa-solid fa-bullseye',
        title: 'Savings goals',
        body: 'Set a target, put money toward it, and watch the progress bar fill up instead of guessing.',
    },
    {
        icon: 'fa-solid fa-chart-pie',
        title: 'Category breakdown',
        body: 'See which categories eat your income and which ones you barely touch, at a glance.',
    },
    {
        icon: 'fa-solid fa-gauge-high',
        title: 'Overspend warnings',
        body: 'A live progress bar shows how much of your income is already spent — and flags it when you cross the line.',
    },
    {
        icon: 'fa-solid fa-shield-halved',
        title: 'Your data, your account',
        body: 'Entries are scoped to your account. No syncing with your bank, no third parties in the middle.',
    },
];

const steps = [
    {
        title: 'Create your account',
        body: 'Name, email, password. No card, no setup wizard.',
    },
    {
        title: 'Log the period',
        body: 'Add your income and expenses under the categories that fit.',
    },
    {
        title: 'Watch the balance',
        body: 'Totals, spend percentage and goal progress update as you type.',
    },
];

const previewExpenses = [
    { name: 'Rent', category: 'Housing', amount: '3,200' },
    { name: 'Groceries', category: 'Food', amount: '840' },
    { name: 'Fuel', category: 'Transport', amount: '260' },
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
    <Head :title="t('Welcome')" />

    <div
        class="min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100"
    >
        <!-- Navbar -->
        <header
            class="sticky top-0 z-40 border-b border-gray-200/70 bg-white/80 backdrop-blur-md dark:border-gray-800/70 dark:bg-gray-950/80"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-6"
            >
                <Link :href="home.url()" class="flex items-center gap-3">
                    <AppLogo size="h-9 w-9" />
                    <span class="text-lg font-semibold tracking-tight"
                        >{{ t('Budget') }}</span
                    >
                </Link>

                <nav
                    class="hidden items-center gap-8 text-sm font-medium text-gray-600 md:flex dark:text-gray-400"
                >
                    <a
                        href="#features"
                        class="transition hover:text-gray-900 dark:hover:text-white"
                        >{{ t('Features') }}</a
                    >
                    <a
                        href="#how-it-works"
                        class="transition hover:text-gray-900 dark:hover:text-white"
                        >{{ t('How it works') }}</a
                    >
                </nav>

                <div class="flex items-center gap-2">
                    <LanguageSwitcher />
                    <button
                        type="button"
                        @click="toggleDark"
                        class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                        :aria-label="
                            isDark
                                ? t('Switch to light mode')
                                : t('Switch to dark mode')
                        "
                    >
                        <i
                            :class="
                                isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'
                            "
                            class="w-4 text-center text-sm"
                        ></i>
                    </button>

                    <template v-if="user">
                        <Link
                            :href="budgetIndex.url()"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700"
                        >
                            {{ t('Open app') }}
                        </Link>
                        <Link
                            :href="logout.url()"
                            method="post"
                            as="button"
                            class="hidden rounded-xl px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:block dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        >
                            {{ t('Log out') }}
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            :href="showLogin.url()"
                            class="hidden rounded-xl px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:block dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        >
                            {{ t('Sign in') }}
                        </Link>
                        <Link
                            :href="showRegister.url()"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700"
                        >
                            {{ t('Get started') }}
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute -top-40 left-1/2 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-primary-500/15 blur-3xl dark:bg-primary-500/20"
            ></div>
            <div
                class="pointer-events-none absolute top-24 -right-32 h-96 w-96 rounded-full bg-secondary-500/10 blur-3xl dark:bg-secondary-500/15"
            ></div>

            <div
                class="relative mx-auto grid max-w-6xl items-center gap-16 px-6 py-20 lg:grid-cols-2 lg:py-28"
            >
                <div>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300"
                    >
                        <i class="fa-solid fa-bolt text-[10px]"></i>
                        {{ t('Simple personal budgeting') }}
                    </span>

                    <h1
                        class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"
                    >
                        {{ t('Know exactly where') }}
                        <span class="text-primary-600 dark:text-primary-400"
                            >{{ t('your money') }}</span
                        >
                        {{ t('goes.') }}
                    </h1>

                    <p
                        class="mt-6 max-w-lg text-lg leading-relaxed text-gray-600 dark:text-gray-400"
                    >
                        <template v-if="user">
                            {{ t('Welcome back, :name. Your budget is right where you left it.', { name: user.name }) }}
                        </template>
                        <template v-else>
                            {{ t('Track income, expenses and savings goals one period at a time — without spreadsheets and without linking your bank.') }}
                        </template>
                    </p>

                    <div class="mt-10 flex flex-wrap gap-3">
                        <template v-if="user">
                            <Link
                                :href="budgetIndex.url()"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700"
                            >
                                <i class="fa-solid fa-wallet text-xs"></i>
                                {{ t('Go to budget') }}
                            </Link>
                            <Link
                                :href="goalsIndex.url()"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                            >
                                <i class="fa-solid fa-bullseye text-xs"></i>
                                {{ t('View goals') }}
                            </Link>
                        </template>

                        <template v-else>
                            <Link
                                :href="showRegister.url()"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700"
                            >
                                {{ t('Create free account') }}
                                <i class="fa-solid fa-arrow-right text-xs rtl:rotate-180"></i>
                            </Link>
                            <Link
                                :href="showLogin.url()"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                            >
                                {{ t('Sign in') }}
                            </Link>
                        </template>
                    </div>

                    <p class="mt-6 text-sm text-gray-500 dark:text-gray-500">
                        {{ t('Free to use · No card required · Dark mode included') }}
                    </p>
                </div>

                <!-- App preview -->
                <div class="relative">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xl shadow-gray-900/5 dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/40"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-500"
                                >
                                    {{ t('Balance') }}
                                </p>
                                <p
                                    class="mt-1 text-3xl font-bold tracking-tight"
                                >
                                    <span class="icon-saudi_riyal">&#xea;</span>
                                    4,700
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-secondary-50 px-3 py-1 text-xs font-medium text-secondary-700 dark:bg-secondary-500/10 dark:text-secondary-400"
                            >
                                <i class="fa-solid fa-arrow-trend-up me-1"></i>
                                {{ t('On track') }}
                            </span>
                        </div>

                        <div class="mt-6">
                            <div
                                class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span>{{ t(':percent% of income spent', { percent: 53 }) }}</span>
                                <span>
                                    <span class="icon-saudi_riyal">&#xea;</span>
                                    5,300 /
                                    <span class="icon-saudi_riyal">&#xea;</span>
                                    10,000
                                </span>
                            </div>
                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-full w-[53%] rounded-full bg-primary-600"
                                ></div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div
                                class="rounded-xl bg-secondary-50 p-4 dark:bg-secondary-500/10"
                            >
                                <p
                                    class="text-xs font-medium text-secondary-700 dark:text-secondary-400"
                                >
                                    {{ t('Income') }}
                                </p>
                                <p
                                    class="mt-1 text-lg font-semibold text-secondary-800 dark:text-secondary-300"
                                >
                                    <span class="icon-saudi_riyal">&#xea;</span>
                                    10,000
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-rose-50 p-4 dark:bg-rose-500/10"
                            >
                                <p
                                    class="text-xs font-medium text-rose-700 dark:text-rose-400"
                                >
                                    {{ t('Expenses') }}
                                </p>
                                <p
                                    class="mt-1 text-lg font-semibold text-rose-800 dark:text-rose-300"
                                >
                                    <span class="icon-saudi_riyal">&#xea;</span>
                                    5,300
                                </p>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-1">
                            <li
                                v-for="expense in previewExpenses"
                                :key="expense.name"
                                class="flex items-center justify-between rounded-lg px-2 py-2 text-sm"
                            >
                                <span class="flex items-center gap-3">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        <i
                                            class="fa-solid fa-receipt text-xs"
                                        ></i>
                                    </span>
                                    <span>
                                        <span class="block font-medium">{{
                                            t(expense.name)
                                        }}</span>
                                        <span
                                            class="block text-xs text-gray-500 dark:text-gray-500"
                                        >
                                            {{ t(expense.category) }}
                                        </span>
                                    </span>
                                </span>
                                <span
                                    class="font-medium text-rose-600 dark:text-rose-400"
                                >
                                    -<span class="icon-saudi_riyal"
                                        >&#xea;</span
                                    >
                                    {{ expense.amount }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="absolute -bottom-6 -start-4 hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-lg shadow-gray-900/5 sm:block dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/40"
                    >
                        <p
                            class="text-xs font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ t('Emergency fund') }}
                        </p>
                        <div class="mt-2 flex items-center gap-3">
                            <div
                                class="h-2 w-28 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-full w-[72%] rounded-full bg-secondary-500"
                                ></div>
                            </div>
                            <span
                                class="text-xs font-semibold text-secondary-600 dark:text-secondary-400"
                                >72%</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section
            id="features"
            class="border-t border-gray-200 py-20 lg:py-28 dark:border-gray-800"
        >
            <div class="mx-auto max-w-6xl px-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        {{ t('Everything a budget needs. Nothing more.') }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        {{ t('Built around one idea: you should be able to answer “can I afford this?” in a couple of seconds.') }}
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="rounded-2xl border border-gray-200 bg-white p-6 transition hover:border-primary-300 hover:shadow-lg hover:shadow-primary-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-500/50"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400"
                        >
                            <i :class="feature.icon"></i>
                        </span>
                        <h3 class="mt-5 font-semibold">{{ t(feature.title) }}</h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400"
                        >
                            {{ t(feature.body) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section
            id="how-it-works"
            class="border-t border-gray-200 bg-gray-50 py-20 lg:py-28 dark:border-gray-800 dark:bg-gray-900/50"
        >
            <div class="mx-auto max-w-6xl px-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        {{ t('Up and running in three steps') }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        {{ t('No imports, no bank connections, no onboarding calls.') }}
                    </p>
                </div>

                <ol class="mt-14 grid gap-8 md:grid-cols-3">
                    <li
                        v-for="(step, index) in steps"
                        :key="step.title"
                        class="relative"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600 text-sm font-bold text-white shadow-sm shadow-primary-600/25"
                        >
                            {{ index + 1 }}
                        </span>
                        <h3 class="mt-5 font-semibold">{{ t(step.title) }}</h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400"
                        >
                            {{ t(step.body) }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- CTA -->
        <section class="px-6 py-20 lg:py-28">
            <div
                class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl bg-primary-600 px-8 py-16 text-center"
            >
                <div
                    class="pointer-events-none absolute -top-24 -left-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"
                ></div>
                <div
                    class="pointer-events-none absolute -right-16 -bottom-24 h-72 w-72 rounded-full bg-secondary-300/20 blur-3xl"
                ></div>

                <h2
                    class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl"
                >
                    {{ t('Start this month with a plan.') }}
                </h2>
                <p class="relative mx-auto mt-4 max-w-xl text-primary-100">
                    {{ t("Set up your first budget period in a minute and see exactly what's left to spend.") }}
                </p>

                <div class="relative mt-10 flex justify-center">
                    <Link
                        :href="user ? budgetIndex.url() : showRegister.url()"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-primary-50"
                    >
                        {{
                            user
                                ? t('Go to my budget')
                                : t('Create your free account')
                        }}
                        <i class="fa-solid fa-arrow-right text-xs rtl:rotate-180"></i>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-gray-800">
            <div
                class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-gray-500 sm:flex-row dark:text-gray-500"
            >
                <div class="flex items-center gap-2">
                    <AppLogo size="h-7 w-7" />
                    <span class="font-medium text-gray-700 dark:text-gray-300"
                        >{{ t('Budget') }}</span
                    >
                </div>
                <p>
                    &copy; {{ new Date().getFullYear() }} {{ t('Budget') }}.
                    {{ t('Built for people who like knowing.') }}
                </p>
            </div>
        </footer>
    </div>
</template>
