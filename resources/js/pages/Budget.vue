<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Form, Link } from '@inertiajs/vue3';
import CategorySelect from '../components/CategorySelect.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import { router } from '@inertiajs/vue3';


const isDark = ref(false);
const navOpen = ref(false);
const selectedIncomeCategory = ref('');
const selectedExpenseCategory = ref('');

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark';
    document.documentElement.classList.toggle('dark', isDark.value);
});

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

defineOptions({ title: 'Budget' });

const props = defineProps({
    currentPeriod: String,
    incomes: Array,
    expenses: Array,
    categories: Array,
    totalIncome: Number,
    totalExpenses: Number,
});

const balance = computed(() => props.totalIncome - props.totalExpenses);

/** Percentage of income already spent, capped at 100 for the progress bar. */
const spentPercent = computed(() => {
    if (!props.totalIncome) return props.totalExpenses > 0 ? 100 : 0;
    return Math.min(
        100,
        Math.round((props.totalExpenses / props.totalIncome) * 100),
    );
});

const isOverspending = computed(() => props.totalExpenses > props.totalIncome);

function money(value) {
    return Number(value || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const dateObj = computed(() => new Date(props.currentPeriod + '-01T00:00:00'));

const formattedPeriod = computed(() => {
    return dateObj.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });
});

const shortPeriod = computed(() => {
    return dateObj.value.toLocaleDateString('en-US', {
        month: 'short',
        year: 'numeric',
    });
});

const prevPeriod = computed(() => {
    const d = new Date(dateObj.value);
    d.setMonth(d.getMonth() - 1);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    return y + '-' + m;
});

const nextPeriod = computed(() => {
    const d = new Date(dateObj.value);
    d.setMonth(d.getMonth() + 1);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    return y + '-' + m;
});

const incomeCategories = computed(() =>
    props.categories.filter((c) => c.type === 'income'),
);
const expenseCategories = computed(() =>
    props.categories.filter((c) => c.type === 'expense'),
);

function deleteIncome(id) {
    router.delete(`/budget/income/${id}`, { preserveScroll: true });
}

function deleteExpense(id) {
    router.delete(`/budget/expense/${id}`, { preserveScroll: true });
}

// Calculator
const calcOpen = ref(false);
const calcDisplay = ref('0');
const calcExpression = ref('');
const justEvaluated = ref(false);
/** True right after an operator, while calcDisplay still shows the previous operand. */
const awaitingOperand = ref(false);

const operators = ['+', '-', '×', '÷'];

function calcInput(value) {
    if (justEvaluated.value) {
        calcDisplay.value = '0';
        calcExpression.value = '';
        justEvaluated.value = false;
    }

    if (awaitingOperand.value) {
        calcDisplay.value = '0';
        awaitingOperand.value = false;
    }

    if (value === '.') {
        if (calcDisplay.value.includes('.')) return;
        calcDisplay.value += '.';
        return;
    }

    calcDisplay.value =
        calcDisplay.value === '0' ? value : calcDisplay.value + value;
}

function calcOperator(operator) {
    if (calcDisplay.value === 'Error') return;

    if (justEvaluated.value) {
        // Chain off the result instead of starting over.
        calcExpression.value = calcDisplay.value + ' ' + operator + ' ';
        justEvaluated.value = false;
        awaitingOperand.value = true;
        return;
    }

    if (awaitingOperand.value) {
        // Swap the pending operator rather than appending a second one.
        calcExpression.value = calcExpression.value.replace(
            /[+\-×÷] $/,
            operator + ' ',
        );
        return;
    }

    calcExpression.value += calcDisplay.value + ' ' + operator + ' ';
    awaitingOperand.value = true;
}

function calcClear() {
    calcDisplay.value = '0';
    calcExpression.value = '';
    justEvaluated.value = false;
    awaitingOperand.value = false;
}

function calcDelete() {
    if (justEvaluated.value) {
        calcClear();
        return;
    }

    if (awaitingOperand.value) {
        // Undo the pending operator and keep editing the previous operand.
        calcExpression.value = calcExpression.value.replace(/[+\-×÷] $/, '');
        awaitingOperand.value = false;
        return;
    }

    calcDisplay.value =
        calcDisplay.value.length > 1 ? calcDisplay.value.slice(0, -1) : '0';
}

function calcEvaluate() {
    if (!calcExpression.value) return;

    // A trailing operator has no right-hand operand yet, so drop it.
    const full = awaitingOperand.value
        ? calcExpression.value.replace(/[+\-×÷] $/, '')
        : calcExpression.value + calcDisplay.value;

    const sanitized = full
        .replace(/×/g, '*')
        .replace(/÷/g, '/')
        .replace(/[^0-9+\-*/.() ]/g, '');

    try {
        // eslint-disable-next-line no-new-func
        const result = Function('"use strict"; return (' + sanitized + ')')();
        const rounded = parseFloat(result.toFixed(10));
        calcDisplay.value = isFinite(rounded) ? String(rounded) : 'Error';
    } catch {
        calcDisplay.value = 'Error';
    }

    calcExpression.value = '';
    justEvaluated.value = true;
    awaitingOperand.value = false;
}

const calcButtons = [
    ['C', '⌫', '%', '÷'],
    ['7', '8', '9', '×'],
    ['4', '5', '6', '-'],
    ['1', '2', '3', '+'],
    ['0', '.', '='],
];

function calcButtonClass(btn) {
    const base = 'rounded-xl font-semibold transition active:scale-95';
    if (btn === '=')
        return `${base} col-span-2 bg-indigo-600 text-white text-lg hover:bg-indigo-500`;
    if (btn === 'C')
        return `${base} bg-rose-100 text-rose-600 hover:bg-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:hover:bg-rose-500/25`;
    if (btn === '⌫')
        return `${base} bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600`;
    if (['+', '-', '×', '÷', '%'].includes(btn))
        return `${base} bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:hover:bg-indigo-500/25`;
    return `${base} bg-gray-100 text-gray-800 font-medium hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-100 dark:hover:bg-gray-700`;
}

function calcPress(btn) {
    if (btn === 'C') {
        calcClear();
        return;
    }
    if (btn === '⌫') {
        calcDelete();
        return;
    }
    if (btn === '=') {
        calcEvaluate();
        return;
    }
    if (operators.includes(btn)) {
        calcOperator(btn);
        return;
    }
    if (btn === '%') {
        const val = parseFloat(calcDisplay.value);
        if (isNaN(val)) return;
        calcDisplay.value = String(val / 100);
        justEvaluated.value = false;
        awaitingOperand.value = false;
        return;
    }
    calcInput(btn);
}

function handleKeydown(e) {
    if (e.key === 'Escape') {
        navOpen.value = false;
    }
    if (!calcOpen.value) return;

    const key = e.key;
    if (e.key === 'Escape') {
        calcOpen.value = false;
        return;
    }

    const map = {
        Enter: '=',
        '*': '×',
        '/': '÷',
        Backspace: '⌫',
        Delete: 'C',
    };

    const mapped =
        map[key] ??
        ([
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '+',
            '-',
            '.',
            '%',
        ].includes(key)
            ? key
            : null);
    if (mapped) {
        e.preventDefault();
        calcPress(mapped);
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
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
                            <!-- <i class="fa-solid fa-wallet"></i> -->
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
            class="mx-auto flex max-w-6xl gap-6 px-4 pt-5 pb-28 sm:px-6 sm:pt-8 sm:pb-16"
        >
            <AppSidebar v-model:open="navOpen" />

            <main class="min-w-0 flex-1">
                <!-- Month Navigation -->
                <div
                    class="mb-4 flex items-center justify-between gap-2 sm:mb-6"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                        >
                            Overview
                        </p>
                        <h1
                            class="text-xl font-bold tracking-tight sm:text-2xl"
                        >
                            <span class="hidden sm:inline">{{
                                formattedPeriod
                            }}</span>
                            <span class="sm:hidden">{{ shortPeriod }}</span>
                        </h1>
                    </div>
                    <div
                        class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <Link
                            :href="'/budget?period=' + prevPeriod"
                            preserve-scroll
                            aria-label="Previous month"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <i class="fa-solid fa-chevron-left text-sm"></i>
                        </Link>
                        <Link
                            :href="'/budget?period=' + nextPeriod"
                            preserve-scroll
                            aria-label="Next month"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </Link>
                    </div>
                </div>

                <!-- Balance Hero -->
                <div
                    class="mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Remaining balance
                            </p>
                            <p
                                class="mt-1 text-3xl font-bold tracking-tight tabular-nums sm:text-4xl"
                                :class="
                                    balance >= 0
                                        ? 'text-gray-900 dark:text-white'
                                        : 'text-rose-600 dark:text-rose-400'
                                "
                            >
                                <span class="icon-saudi_riyal">&#xea;</span
                                >{{ money(balance) }}
                            </p>
                        </div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                isOverspending
                                    ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300'
                                    : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'
                            "
                        >
                            {{ spentPercent }}% of income spent
                        </span>
                    </div>

                    <div
                        class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="
                                isOverspending
                                    ? 'bg-rose-500'
                                    : 'bg-emerald-500'
                            "
                            :style="{ width: spentPercent + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400"
                            >
                                <i class="fa-solid fa-arrow-down text-xs"></i>
                            </span>
                            <p
                                class="text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400"
                            >
                                Income
                            </p>
                        </div>
                        <p
                            class="mt-2 text-xl font-bold tracking-tight text-emerald-600 tabular-nums sm:text-2xl dark:text-emerald-400"
                        >
                            <span class="icon-saudi_riyal">&#xea;</span
                            >{{ money(totalIncome) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400"
                            >
                                <i class="fa-solid fa-arrow-up text-xs"></i>
                            </span>
                            <p
                                class="text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400"
                            >
                                Expenses
                            </p>
                        </div>
                        <p
                            class="mt-2 text-xl font-bold tracking-tight text-rose-600 tabular-nums sm:text-2xl dark:text-rose-400"
                        >
                            <span class="icon-saudi_riyal">&#xea;</span
                            >{{ money(totalExpenses) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
                    <!-- Income Section -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <header
                            class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800"
                        >
                            <h2
                                class="flex items-center gap-2 text-base font-semibold"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-emerald-500"
                                ></span>
                                Income
                            </h2>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500"
                                >{{ incomes.length }} entries</span
                            >
                        </header>

                        <div class="px-4 py-4 sm:px-5">
                            <Form
                                action="/budget/income"
                                method="post"
                                reset-on-success
                                #default="{ errors, processing }"
                                class="flex flex-col gap-2 sm:flex-row"
                            >
                                <input
                                    type="hidden"
                                    name="period"
                                    :value="currentPeriod"
                                />
                                <div class="sm:flex-1">
                                    <CategorySelect
                                        name="category_id"
                                        v-model="selectedIncomeCategory"
                                        :categories="incomeCategories"
                                        :error="!!errors.category_id"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <input
                                        type="number"
                                        name="amount"
                                        placeholder="Amount"
                                        min="0.01"
                                        step="0.01"
                                        inputmode="decimal"
                                        class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none sm:w-28 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                                        :class="{
                                            'border-rose-400': errors.amount,
                                        }"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 active:scale-95 disabled:opacity-60"
                                    >
                                        Add
                                    </button>
                                </div>
                            </Form>

                            <ul class="mt-4 space-y-1.5">
                                <li
                                    v-for="income in incomes"
                                    :key="income.id"
                                    class="group flex items-center gap-3 rounded-xl border border-transparent bg-gray-50 px-3 py-2.5 transition hover:border-gray-200 dark:bg-gray-800/60 dark:hover:border-gray-700"
                                >
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-gray-900"
                                    >
                                        <i
                                            v-if="income.category"
                                            :class="[
                                                income.category.icon,
                                                income.category.color,
                                            ]"
                                            class="text-sm"
                                        ></i>
                                        <i
                                            v-else
                                            class="fa-solid fa-circle-question text-sm text-gray-400"
                                        ></i>
                                    </span>
                                    <span
                                        class="min-w-0 flex-1 truncate text-sm font-medium"
                                    >
                                        {{
                                            income.name ||
                                            (income.category
                                                ? income.category.name
                                                : 'Unknown')
                                        }}
                                    </span>
                                    <span
                                        class="shrink-0 text-sm font-semibold text-emerald-600 tabular-nums dark:text-emerald-400"
                                    >
                                        +<span class="icon-saudi_riyal"
                                            >&#xea;</span
                                        >{{ money(income.amount) }}
                                    </span>
                                    <button
                                        @click="deleteIncome(income.id)"
                                        type="button"
                                        title="Delete"
                                        aria-label="Delete income"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-500/15"
                                    >
                                        <i
                                            class="fa-solid fa-xmark text-sm"
                                        ></i>
                                    </button>
                                </li>
                                <li
                                    v-if="incomes.length === 0"
                                    class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
                                >
                                    No income added yet.
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Expenses Section -->
                    <section
                        class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <header
                            class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800"
                        >
                            <h2
                                class="flex items-center gap-2 text-base font-semibold"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-rose-500"
                                ></span>
                                Expenses
                            </h2>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500"
                                >{{ expenses.length }} entries</span
                            >
                        </header>

                        <div class="px-4 py-4 sm:px-5">
                            <Form
                                action="/budget/expense"
                                method="post"
                                reset-on-success
                                #default="{ errors, processing }"
                                class="flex flex-col gap-2 sm:flex-row"
                            >
                                <input
                                    type="hidden"
                                    name="period"
                                    :value="currentPeriod"
                                />
                                <div class="sm:flex-1">
                                    <CategorySelect
                                        name="category_id"
                                        v-model="selectedExpenseCategory"
                                        :categories="expenseCategories"
                                        :error="!!errors.category_id"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <input
                                        type="number"
                                        name="amount"
                                        placeholder="Amount"
                                        min="0.01"
                                        step="0.01"
                                        inputmode="decimal"
                                        class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none sm:w-28 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                                        :class="{
                                            'border-rose-400': errors.amount,
                                        }"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="shrink-0 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-500 active:scale-95 disabled:opacity-60"
                                    >
                                        Add
                                    </button>
                                </div>
                            </Form>

                            <ul class="mt-4 space-y-1.5">
                                <li
                                    v-for="expense in expenses"
                                    :key="expense.id"
                                    class="group flex items-center gap-3 rounded-xl border border-transparent bg-gray-50 px-3 py-2.5 transition hover:border-gray-200 dark:bg-gray-800/60 dark:hover:border-gray-700"
                                >
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-gray-900"
                                    >
                                        <i
                                            v-if="expense.category"
                                            :class="[
                                                expense.category.icon,
                                                expense.category.color,
                                            ]"
                                            class="text-sm"
                                        ></i>
                                        <i
                                            v-else
                                            class="fa-solid fa-circle-question text-sm text-gray-400"
                                        ></i>
                                    </span>
                                    <span
                                        class="min-w-0 flex-1 truncate text-sm font-medium"
                                    >
                                        {{
                                            expense.name ||
                                            (expense.category
                                                ? expense.category.name
                                                : 'Unknown')
                                        }}
                                    </span>
                                    <span
                                        class="shrink-0 text-sm font-semibold text-rose-600 tabular-nums dark:text-rose-400"
                                    >
                                        -<span class="icon-saudi_riyal"
                                            >&#xea;</span
                                        >{{ money(expense.amount) }}
                                    </span>
                                    <button
                                        @click="deleteExpense(expense.id)"
                                        type="button"
                                        title="Delete"
                                        aria-label="Delete expense"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-500/15"
                                    >
                                        <i
                                            class="fa-solid fa-xmark text-sm"
                                        ></i>
                                    </button>
                                </li>
                                <li
                                    v-if="expenses.length === 0"
                                    class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
                                >
                                    No expenses added yet.
                                </li>
                            </ul>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <!-- Floating Calculator Button -->
        <button
            @click="calcOpen = !calcOpen"
            type="button"
            title="Calculator"
            class="fixed right-4 bottom-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500 active:scale-95 sm:right-6 sm:bottom-6"
        >
            <svg
                v-if="!calcOpen"
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <rect
                    x="4"
                    y="2"
                    width="16"
                    height="20"
                    rx="2"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                />
                <line
                    x1="8"
                    y1="7"
                    x2="16"
                    y2="7"
                    stroke="currentColor"
                    stroke-width="2"
                />
                <line
                    x1="8"
                    y1="12"
                    x2="8"
                    y2="12"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="12"
                    y1="12"
                    x2="12"
                    y2="12"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="16"
                    y1="12"
                    x2="16"
                    y2="12"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="8"
                    y1="16"
                    x2="8"
                    y2="16"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="12"
                    y1="16"
                    x2="12"
                    y2="16"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <line
                    x1="16"
                    y1="16"
                    x2="16"
                    y2="16"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

        <!-- Calculator backdrop (mobile) -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-if="calcOpen"
                @click="calcOpen = false"
                class="fixed inset-0 z-30 bg-black/40 sm:hidden"
            ></div>
        </Transition>

        <!-- Calculator Panel -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-4 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:scale-95"
        >
            <div
                v-if="calcOpen"
                class="fixed inset-x-3 bottom-20 z-40 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:inset-x-auto sm:right-6 sm:bottom-24 sm:w-72 dark:border-gray-800 dark:bg-gray-900"
            >
                <!-- Display -->
                <div class="bg-gray-900 px-4 pt-4 pb-3 dark:bg-gray-800">
                    <p
                        class="min-h-5 truncate text-right text-xs text-gray-400"
                    >
                        {{ calcExpression }}&nbsp;
                    </p>
                    <p
                        class="truncate text-right text-3xl font-light text-white tabular-nums"
                    >
                        {{ calcDisplay }}
                    </p>
                </div>

                <!-- Buttons -->
                <div class="grid grid-cols-4 gap-2 p-3">
                    <template v-for="row in calcButtons" :key="row.join()">
                        <button
                            v-for="btn in row"
                            :key="btn"
                            @click="calcPress(btn)"
                            type="button"
                            :class="[
                                'h-12 text-base sm:h-14',
                                calcButtonClass(btn),
                                btn === '0' ? 'col-span-2' : '',
                            ]"
                        >
                            {{ btn }}
                        </button>
                    </template>
                </div>
            </div>
        </Transition>
    </div>
</template>
