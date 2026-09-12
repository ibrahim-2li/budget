<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { index as budgetIndex } from '@/actions/App/Http/Controllers/BudgetController';
import { index as goalsIndex } from '@/actions/App/Http/Controllers/GoalController';
import { dashboard as adminDashboard } from '@/routes/admin';
import { computed } from 'vue';

const open = defineModel('open', { type: Boolean, default: false });

const page = usePage();
const isAdmin = computed(() => page.props.auth?.isAdmin);

const navigation = computed(() => {
    const items = [
        { name: 'Budget', href: budgetIndex.url(), icon: 'fa-solid fa-wallet' },
        { name: 'Goals', href: goalsIndex.url(), icon: 'fa-solid fa-bullseye' },
    ];

    if (isAdmin.value) {
        items.push({
            name: 'Admin',
            href: adminDashboard.url(),
            icon: 'fa-solid fa-shield-halved',
        });
    }

    return items;
});

const isActive = (href: string) => page.url.split('?')[0] === href;

const linkClass = (href: string) => [
    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
    isActive(href)
        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/25'
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
                    {{ item.name }}
                </Link>
            </nav>
        </aside>

        <!-- Mobile drawer -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="open"
                    @click.self="open = false"
                    class="fixed inset-0 z-40 bg-gray-950/50 backdrop-blur-sm lg:hidden"
                ></div>
            </Transition>

            <Transition
                enter-active-class="transition duration-250 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <aside
                    v-if="open"
                    class="fixed inset-y-0 left-0 z-50 w-64 border-r border-gray-200 bg-white p-4 shadow-2xl lg:hidden dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <span class="flex items-center gap-2.5">
                            <img
                                src="/images/logo.ico"
                                alt="logo"
                                class="h-8 w-8"
                            />
                            <span class="text-base font-bold tracking-tight"
                                >Budget</span
                            >
                        </span>
                        <button
                            @click="open = false"
                            type="button"
                            aria-label="Close navigation"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            :class="linkClass(item.href)"
                            @click="open = false"
                        >
                            <i :class="item.icon" class="w-4 text-center"></i>
                            {{ item.name }}
                        </Link>
                    </nav>
                </aside>
            </Transition>
        </Teleport>
    </div>
</template>
