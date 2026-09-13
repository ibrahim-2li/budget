<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index as budgetIndex } from '@/actions/App/Http/Controllers/BudgetController';
import { index as goalsIndex } from '@/actions/App/Http/Controllers/GoalController';
import MobileBottomNav from '@/components/MobileBottomNav.vue';
import { useI18n } from '@/lib/i18n';
import { dashboard as adminDashboard, categories, dashboard, users } from '@/routes/admin';

const page = usePage();
const { t } = useI18n();
const isAdmin = computed(() => page.props.auth?.isAdmin);

const navigation = computed(() => {
    const items = [
        { name: 'Budget', href: budgetIndex.url(), icon: 'fa-solid fa-wallet' },
        { name: 'Goals', href: goalsIndex.url(), icon: 'fa-solid fa-bullseye' },
    ];

    if (isAdmin.value) {

        items.push({
            name: 'Dashboard',
            href: adminDashboard.url(),
            icon: 'fa-solid fa-chart-pie',
        });
    }

    return items;
});

const isActive = (href: string) => page.url.split('?')[0] === href;

const linkClass = (href: string) => [
    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
    isActive(href)
        ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/25'
        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
];
</script>

<template>
    <div class="contents">
        <!-- Desktop sidebar -->
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

        <!-- Mobile bottom bar -->
        <MobileBottomNav :items="navigation" />
    </div>
</template>
