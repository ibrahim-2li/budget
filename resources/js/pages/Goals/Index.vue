<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Form, Link, router, usePage } from '@inertiajs/vue3';
import GoalController from '@/actions/App/Http/Controllers/GoalController';
import AppSidebar from '@/components/AppSidebar.vue';

defineOptions({ title: 'Goals' });

const props = defineProps({
    goals: Array,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const isDark = ref(false);
const navOpen = ref(false);

function handleKeydown(event) {
    if (event.key === 'Escape') {
        cancelDelete();
        navOpen.value = false;
    }
}

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark';
    document.documentElement.classList.toggle('dark', isDark.value);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function money(value) {
    return Number(value || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const formOpen = ref(false);

/** Id of the goal currently open in the edit panel, or null. */
const editingId = ref(null);
const editForm = ref({
    name: '',
    description: '',
    target_amount: '',
    current_amount: '',
    target_date: '',
    priority: 'medium',
});

/** Per-goal "add funds" amounts, keyed by goal id. */
const contributions = ref({});

const totalTarget = computed(() =>
    props.goals.reduce((sum, goal) => sum + Number(goal.target_amount), 0),
);
const totalSaved = computed(() =>
    props.goals.reduce((sum, goal) => sum + Number(goal.current_amount), 0),
);
const completedCount = computed(
    () => props.goals.filter((goal) => percentage(goal) >= 100).length,
);

const overallPercent = computed(() => {
    if (!totalTarget.value) return 0;
    return Math.min(
        100,
        Math.round((totalSaved.value / totalTarget.value) * 100),
    );
});

function percentage(goal) {
    if (!Number(goal.target_amount)) return 0;
    return Math.min(
        100,
        Math.round(
            (Number(goal.current_amount) / Number(goal.target_amount)) * 100,
        ),
    );
}

function remaining(goal) {
    return Math.max(
        0,
        Number(goal.target_amount) - Number(goal.current_amount),
    );
}

function formatDate(value) {
    if (!value) return null;
    return new Date(value).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

/** Whole days until the target date; negative once the date has passed. */
function daysLeft(goal) {
    if (!goal.target_date) return null;
    const target = new Date(goal.target_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return Math.round((target - today) / 86400000);
}

const priorityStyles = {
    high: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
    medium: 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
    low: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
};

function barClass(goal) {
    if (percentage(goal) >= 100) return 'bg-emerald-500';
    if (goal.priority === 'high') return 'bg-rose-500';
    return 'bg-indigo-500';
}

function startEditing(goal) {
    editingId.value = goal.id;
    editForm.value = {
        name: goal.name,
        description: goal.description ?? '',
        target_amount: goal.target_amount,
        current_amount: goal.current_amount,
        target_date: goal.target_date
            ? String(goal.target_date).slice(0, 10)
            : '',
        priority: goal.priority,
    };
}

function cancelEditing() {
    editingId.value = null;
}

function submitEdit(goal) {
    router.patch(
        GoalController.update.url(goal.id),
        { ...editForm.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                editingId.value = null;
            },
        },
    );
}

function addFunds(goal) {
    const amount = Number(contributions.value[goal.id]);
    if (!amount) return;

    router.patch(
        GoalController.update.url(goal.id),
        {
            name: goal.name,
            description: goal.description,
            target_amount: goal.target_amount,
            current_amount: Math.max(0, Number(goal.current_amount) + amount),
            target_date: goal.target_date
                ? String(goal.target_date).slice(0, 10)
                : null,
            priority: goal.priority,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                contributions.value[goal.id] = '';
            },
        },
    );
}

/** Goal queued for deletion, shown in the confirmation modal. */
const goalPendingDelete = ref(null);
const deleting = ref(false);

function confirmDelete(goal) {
    goalPendingDelete.value = goal;
}

function cancelDelete() {
    if (deleting.value) return;
    goalPendingDelete.value = null;
}

function destroyGoal() {
    const goal = goalPendingDelete.value;
    if (!goal) return;

    router.delete(GoalController.destroy.url(goal.id), {
        preserveScroll: true,
        onStart: () => {
            deleting.value = true;
        },
        onFinish: () => {
            deleting.value = false;
            goalPendingDelete.value = null;
        },
    });
}
</script>

<template>
    <div
        class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100"
    >
        <!-- Navbar -->
        <nav
            class="sticky top-0 z-30 border-b border-gray-200 bg-white/85 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/85"
        >
            <div
                class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:h-16 sm:px-6"
            >
                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        @click="navOpen = true"
                        aria-label="Open navigation"
                        class="-ml-1 flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <a href="/">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm text-white shadow-sm"
                        >
                            <img
                                src="/images/logo.ico"
                                alt="logo"
                                class="h-8 w-8"
                            />
                        </span>
                    </a>
                    <a href="/"
                        ><span
                            class="text-base font-bold tracking-tight sm:text-lg"
                            >Budget</span
                        ></a
                    >
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button
                        @click="toggleDark"
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="
                            isDark
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        "
                    >
                        <svg
                            v-if="isDark"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                    </button>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="flex h-9 items-center gap-2 rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </Link>
                </div>
            </div>
        </nav>

        <div
            class="mx-auto flex max-w-6xl gap-6 px-4 pt-5 pb-20 sm:px-6 sm:pt-8"
        >
            <AppSidebar v-model:open="navOpen" />

            <main class="min-w-0 flex-1">
                <div class="mb-4 flex items-end justify-between gap-2 sm:mb-6">
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                        >
                            Savings
                        </p>
                        <h1
                            class="text-xl font-bold tracking-tight sm:text-2xl"
                        >
                            Goals
                        </h1>
                    </div>
                    <button
                        @click="formOpen = !formOpen"
                        type="button"
                        class="flex h-9 shrink-0 items-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500 active:scale-95"
                    >
                        <i
                            class="fa-solid"
                            :class="formOpen ? 'fa-xmark' : 'fa-plus'"
                        ></i>
                        {{ formOpen ? 'Cancel' : 'New goal' }}
                    </button>
                </div>

                <p
                    v-if="flashSuccess"
                    class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                >
                    {{ flashSuccess }}
                </p>

                <!-- Overall progress -->
                <div
                    class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Total saved
                            </p>
                            <p
                                class="mt-1 text-3xl font-bold tracking-tight tabular-nums sm:text-4xl"
                            >
                                <span class="icon-saudi_riyal">&#xea;</span
                                >{{ money(totalSaved) }}
                                <span
                                    class="text-base font-medium text-gray-400 dark:text-gray-500"
                                >
                                    /
                                    <span class="icon-saudi_riyal">&#xea;</span
                                    >{{ money(totalTarget) }}
                                </span>
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"
                        >
                            {{ completedCount }} of {{ goals.length }} reached
                        </span>
                    </div>
                    <div
                        class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <div
                            class="h-full rounded-full bg-indigo-500 transition-all duration-500"
                            :style="{ width: overallPercent + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- New goal form -->
                <div
                    v-if="formOpen"
                    class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-800 dark:bg-gray-900"
                >
                    <Form
                        :action="GoalController.store.url()"
                        method="post"
                        reset-on-success
                        #default="{ errors, processing }"
                        class="grid gap-3 sm:grid-cols-2"
                    >
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                >Name</label
                            >
                            <input
                                type="text"
                                name="name"
                                placeholder="New laptop"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                                :class="{ 'border-rose-400': errors.name }"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs text-rose-500"
                            >
                                {{ errors.name }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                >Description
                                <span class="text-gray-400"
                                    >(optional)</span
                                ></label
                            >
                            <textarea
                                name="description"
                                rows="2"
                                placeholder="What are you saving for?"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                >Target amount</label
                            >
                            <input
                                type="number"
                                name="target_amount"
                                min="0.01"
                                step="0.01"
                                inputmode="decimal"
                                placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                                :class="{
                                    'border-rose-400': errors.target_amount,
                                }"
                            />
                            <p
                                v-if="errors.target_amount"
                                class="mt-1 text-xs text-rose-500"
                            >
                                {{ errors.target_amount }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                >Target date
                                <span class="text-gray-400"
                                    >(optional)</span
                                ></label
                            >
                            <input
                                type="date"
                                name="target_date"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                :class="{
                                    'border-rose-400': errors.target_date,
                                }"
                            />
                            <p
                                v-if="errors.target_date"
                                class="mt-1 text-xs text-rose-500"
                            >
                                {{ errors.target_date }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                >Priority</label
                            >
                            <select
                                name="priority"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="high">High</option>
                                <option value="medium" selected>Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="h-[38px] w-full rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500 active:scale-95 disabled:opacity-60"
                            >
                                Create goal
                            </button>
                        </div>
                    </Form>
                </div>

                <!-- Goal list -->
                <div class="grid gap-3 sm:grid-cols-2">
                    <section
                        v-for="goal in goals"
                        :key="goal.id"
                        class="flex flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <template v-if="editingId !== goal.id">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2
                                        class="truncate text-base font-semibold"
                                    >
                                        {{ goal.name }}
                                    </h2>
                                    <p
                                        v-if="goal.description"
                                        class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ goal.description }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize"
                                    :class="priorityStyles[goal.priority]"
                                >
                                    {{ goal.priority }}
                                </span>
                            </div>

                            <div
                                class="mt-3 flex items-end justify-between gap-2"
                            >
                                <p
                                    class="text-xl font-bold tracking-tight tabular-nums"
                                >
                                    <span class="icon-saudi_riyal">&#xea;</span
                                    >{{ money(goal.current_amount) }}
                                    <span
                                        class="text-sm font-medium text-gray-400 dark:text-gray-500"
                                    >
                                        /
                                        <span class="icon-saudi_riyal"
                                            >&#xea;</span
                                        >{{ money(goal.target_amount) }}
                                    </span>
                                </p>
                                <span
                                    class="text-sm font-semibold tabular-nums"
                                    :class="
                                        percentage(goal) >= 100
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-gray-500 dark:text-gray-400'
                                    "
                                >
                                    {{ percentage(goal) }}%
                                </span>
                            </div>

                            <div
                                class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="barClass(goal)"
                                    :style="{ width: percentage(goal) + '%' }"
                                ></div>
                            </div>

                            <div
                                class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    v-if="percentage(goal) >= 100"
                                    class="font-semibold text-emerald-600 dark:text-emerald-400"
                                >
                                    <i class="fa-solid fa-circle-check"></i>
                                    Goal reached
                                </span>
                                <span v-else>
                                    <span class="icon-saudi_riyal">&#xea;</span
                                    >{{ money(remaining(goal)) }} to go
                                </span>
                                <span v-if="goal.target_date">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ formatDate(goal.target_date) }}
                                </span>
                                <span
                                    v-if="
                                        goal.target_date &&
                                        daysLeft(goal) < 0 &&
                                        percentage(goal) < 100
                                    "
                                    class="font-semibold text-rose-500"
                                    >overdue</span
                                >
                            </div>

                            <div class="mt-4 flex gap-2">
                                <input
                                    type="number"
                                    v-model="contributions[goal.id]"
                                    min="0.01"
                                    step="0.01"
                                    inputmode="decimal"
                                    placeholder="Add funds"
                                    @keyup.enter="addFunds(goal)"
                                    class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                                />
                                <button
                                    @click="addFunds(goal)"
                                    type="button"
                                    title="Add funds"
                                    class="shrink-0 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 active:scale-95"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                                <button
                                    @click="startEditing(goal)"
                                    type="button"
                                    title="Edit goal"
                                    aria-label="Edit goal"
                                    class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                >
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button
                                    @click="confirmDelete(goal)"
                                    type="button"
                                    title="Delete goal"
                                    aria-label="Delete goal"
                                    class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:bg-rose-50 hover:text-rose-500 dark:border-gray-700 dark:hover:bg-rose-500/15"
                                >
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </template>

                        <!-- Inline edit -->
                        <div v-else class="grid gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                    >Name</label
                                >
                                <input
                                    type="text"
                                    v-model="editForm.name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                    >Description</label
                                >
                                <textarea
                                    v-model="editForm.description"
                                    rows="2"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                ></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >Saved</label
                                    >
                                    <input
                                        type="number"
                                        v-model="editForm.current_amount"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >Target</label
                                    >
                                    <input
                                        type="number"
                                        v-model="editForm.target_amount"
                                        min="0.01"
                                        step="0.01"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >Target date</label
                                    >
                                    <input
                                        type="date"
                                        v-model="editForm.target_date"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >Priority</label
                                    >
                                    <select
                                        v-model="editForm.priority"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    >
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="submitEdit(goal)"
                                    type="button"
                                    class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 active:scale-95"
                                >
                                    Save
                                </button>
                                <button
                                    @click="cancelEditing"
                                    type="button"
                                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </section>

                    <div
                        v-if="goals.length === 0"
                        class="rounded-2xl border border-dashed border-gray-200 py-16 text-center text-sm text-gray-400 sm:col-span-2 dark:border-gray-700 dark:text-gray-500"
                    >
                        No goals yet. Create one to start tracking your savings.
                    </div>
                </div>
            </main>
        </div>

        <!-- Delete confirmation -->
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
                    v-if="goalPendingDelete"
                    @click.self="cancelDelete"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="delete-goal-title"
                    class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 p-4 backdrop-blur-sm sm:items-center"
                >
                    <Transition
                        appear
                        enter-active-class="transition duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                        enter-from-class="translate-y-6 scale-95 opacity-0"
                        enter-to-class="translate-y-0 scale-100 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="translate-y-0 scale-100 opacity-100"
                        leave-to-class="translate-y-4 scale-95 opacity-0"
                    >
                        <div
                            class="w-full max-w-sm overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl shadow-rose-900/10 dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div
                                class="flex flex-col items-center px-6 pt-7 text-center"
                            >
                                <span
                                    class="relative flex h-16 w-16 items-center justify-center"
                                >
                                    <span
                                        class="absolute inset-0 animate-ping rounded-full bg-rose-500/20 [animation-duration:2s]"
                                    ></span>
                                    <span
                                        class="relative flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-rose-600 text-xl text-white shadow-lg shadow-rose-500/30"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </span>
                                </span>

                                <h3
                                    id="delete-goal-title"
                                    class="mt-5 text-lg font-bold tracking-tight"
                                >
                                    Delete this goal?
                                </h3>
                                <p
                                    class="mt-1.5 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200"
                                        >{{ goalPendingDelete.name }}</span
                                    >
                                    will be permanently removed. This can't be
                                    undone.
                                </p>

                                <div
                                    class="mt-4 w-full rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50"
                                >
                                    <div
                                        class="flex items-baseline justify-between text-xs font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        <span>Progress lost</span>
                                        <span class="tabular-nums"
                                            >{{
                                                percentage(goalPendingDelete)
                                            }}%</span
                                        >
                                    </div>
                                    <p
                                        class="mt-1 text-left text-lg font-bold tabular-nums"
                                    >
                                        <span class="icon-saudi_riyal"
                                            >&#xea;</span
                                        >{{
                                            money(
                                                goalPendingDelete.current_amount,
                                            )
                                        }}
                                        <span
                                            class="text-xs font-medium text-gray-400 dark:text-gray-500"
                                        >
                                            /
                                            <span class="icon-saudi_riyal"
                                                >&#xea;</span
                                            >{{
                                                money(
                                                    goalPendingDelete.target_amount,
                                                )
                                            }}
                                        </span>
                                    </p>
                                    <div
                                        class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                                    >
                                        <div
                                            class="h-full rounded-full bg-rose-400"
                                            :style="{
                                                width:
                                                    percentage(
                                                        goalPendingDelete,
                                                    ) + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex gap-2 px-6 pb-6">
                                <button
                                    @click="cancelDelete"
                                    type="button"
                                    :disabled="deleting"
                                    class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 active:scale-95 disabled:opacity-60 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    Keep it
                                </button>
                                <button
                                    @click="destroyGoal"
                                    type="button"
                                    :disabled="deleting"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-600/25 transition hover:bg-rose-500 active:scale-95 disabled:opacity-60"
                                >
                                    <i
                                        v-if="deleting"
                                        class="fa-solid fa-spinner animate-spin text-xs"
                                    ></i>
                                    {{ deleting ? 'Deleting' : 'Delete' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
