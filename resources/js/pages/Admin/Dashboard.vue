<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { users as usersRoute } from '@/routes/admin'

const props = defineProps({
    stats: { type: Object, required: true },
    recentUsers: { type: Array, required: true },
    monthlyActivity: { type: Array, required: true },
})

function money(value) {
    return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const netTotal = computed(() => props.stats.totalIncome - props.stats.totalExpenses)

/** Tallest bar in the activity chart, used to scale every bar. */
const activityPeak = computed(() => {
    const values = props.monthlyActivity.flatMap((m: any) => [m.income, m.expenses])
    return Math.max(...values, 1)
})

function barHeight(value: number) {
    return Math.max(2, Math.round((value / activityPeak.value) * 100)) + '%'
}

const cards = computed(() => [
    { label: 'Users', value: String(props.stats.users), hint: `+${props.stats.newUsersThisMonth} this month`, icon: 'fa-solid fa-users', tone: 'indigo' },
    { label: 'Categories', value: String(props.stats.categories), hint: 'Income & expense', icon: 'fa-solid fa-tags', tone: 'sky' },
    { label: 'Income logged', value: money(props.stats.totalIncome), hint: `${props.stats.incomeEntries} entries`, icon: 'fa-solid fa-arrow-down', tone: 'emerald', currency: true },
    { label: 'Expenses logged', value: money(props.stats.totalExpenses), hint: `${props.stats.expenseEntries} entries`, icon: 'fa-solid fa-arrow-up', tone: 'rose', currency: true },
])

const toneClasses: Record<string, string> = {
    indigo: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400',
    sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
    rose: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
}
</script>

<template>
    <Head title="Admin dashboard" />

    <AdminLayout title="Dashboard" subtitle="Activity across every account.">
        <!-- Stat cards -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div v-for="card in cards" :key="card.label"
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg" :class="toneClasses[card.tone]">
                        <i :class="card.icon" class="text-xs"></i>
                    </span>
                    <p class="text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">{{ card.label }}</p>
                </div>
                <p class="mt-2 truncate text-xl font-bold tracking-tight tabular-nums sm:text-2xl">
                    <span v-if="card.currency" class="icon-saudi_riyal">&#xea;</span>{{ card.value }}
                </p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ card.hint }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Activity chart -->
            <section
                class="rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
                <header
                    class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800">
                    <h2 class="text-base font-semibold">Last 6 months</h2>
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5"><span
                                class="h-2 w-2 rounded-full bg-emerald-500"></span>Income</span>
                        <span class="flex items-center gap-1.5"><span
                                class="h-2 w-2 rounded-full bg-rose-500"></span>Expenses</span>
                    </div>
                </header>

                <div class="px-4 py-5 sm:px-5">
                    <div class="flex h-44 items-end gap-2 sm:gap-4">
                        <div v-for="month in monthlyActivity" :key="month.period"
                            class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                            <div class="flex h-full w-full items-end justify-center gap-1">
                                <div class="w-1/3 rounded-t bg-emerald-500 transition-all"
                                    :style="{ height: barHeight(month.income) }"
                                    :title="'Income: ' + money(month.income)"></div>
                                <div class="w-1/3 rounded-t bg-rose-500 transition-all"
                                    :style="{ height: barHeight(month.expenses) }"
                                    :title="'Expenses: ' + money(month.expenses)"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ month.label }}</span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Net across all users</span>
                        <span class="text-lg font-bold tabular-nums"
                            :class="netTotal >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            <span class="icon-saudi_riyal">&#xea;</span>{{ money(netTotal) }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Recent users -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <header
                    class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800">
                    <h2 class="text-base font-semibold">Newest users</h2>
                    <Link :href="usersRoute.url()"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">View all</Link>
                </header>

                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li v-for="user in recentUsers" :key="user.id" class="flex items-center gap-3 px-4 py-3 sm:px-5">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 uppercase dark:bg-gray-800 dark:text-gray-300">
                            {{ user.name.slice(0, 2) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ user.name }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ user.created_at }}</span>
                    </li>
                    <li v-if="recentUsers.length === 0" class="px-5 py-8 text-center text-sm text-gray-400">
                        No users yet.
                    </li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
