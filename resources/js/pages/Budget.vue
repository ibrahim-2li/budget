<script setup lang="js">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Form, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

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

defineOptions({ title: 'Budget' })

const props = defineProps({
    incomes: Array,
    expenses: Array,
    totalIncome: Number,
    totalExpenses: Number,
})

const balance = computed(() => props.totalIncome - props.totalExpenses)

function deleteIncome(id) {
    router.delete(`/budget/income/${id}`, { preserveScroll: true })
}

function deleteExpense(id) {
    router.delete(`/budget/expense/${id}`, { preserveScroll: true })
}

// Calculator
const calcOpen = ref(false)
const calcDisplay = ref('0')
const calcExpression = ref('')
const justEvaluated = ref(false)

function calcInput(value) {
    if (justEvaluated.value) {
        // After = pressed: start fresh unless operator continues the result
        if (['+', '-', '×', '÷'].includes(value)) {
            calcExpression.value = calcDisplay.value + ' ' + value + ' '
            justEvaluated.value = false
            return
        }
        calcDisplay.value = '0'
        calcExpression.value = ''
        justEvaluated.value = false
    }

    if (['+', '-', '×', '÷'].includes(value)) {
        calcExpression.value = (calcExpression.value || calcDisplay.value) + ' ' + value + ' '
        calcDisplay.value = '0'
        return
    }

    if (value === '.') {
        if (calcDisplay.value.includes('.')) return
        calcDisplay.value += '.'
        return
    }

    calcDisplay.value = calcDisplay.value === '0' ? value : calcDisplay.value + value
}

function calcClear() {
    calcDisplay.value = '0'
    calcExpression.value = ''
    justEvaluated.value = false
}

function calcDelete() {
    if (justEvaluated.value) {
        calcClear()
        return
    }
    calcDisplay.value = calcDisplay.value.length > 1 ? calcDisplay.value.slice(0, -1) : '0'
}

function calcEvaluate() {
    if (!calcExpression.value) return

    const full = calcExpression.value + calcDisplay.value
    const sanitized = full
        .replace(/×/g, '*')
        .replace(/÷/g, '/')
        .replace(/[^0-9+\-*/.() ]/g, '')

    try {
        // eslint-disable-next-line no-new-func
        const result = Function('"use strict"; return (' + sanitized + ')')()
        const rounded = parseFloat(result.toFixed(10))
        calcDisplay.value = isFinite(rounded) ? String(rounded) : 'Error'
    } catch {
        calcDisplay.value = 'Error'
    }

    calcExpression.value = ''
    justEvaluated.value = true
}

const calcButtons = [
    ['C', '⌫', '%', '÷'],
    ['7', '8', '9', '×'],
    ['4', '5', '6', '-'],
    ['1', '2', '3', '+'],
    ['0', '.', '='],
]

function calcButtonClass(btn) {
    if (btn === '=') return 'col-span-2 rounded-xl bg-blue-600 text-white font-semibold text-lg hover:bg-blue-700 active:scale-95 transition-transform'
    if (btn === 'C') return 'rounded-xl bg-red-100 text-red-600 font-semibold hover:bg-red-200 active:scale-95 transition-transform'
    if (btn === '⌫') return 'rounded-xl bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 active:scale-95 transition-transform'
    if (['+', '-', '×', '÷', '%'].includes(btn)) return 'rounded-xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 active:scale-95 transition-transform'
    return 'rounded-xl bg-gray-100 text-gray-800 font-medium hover:bg-gray-200 active:scale-95 transition-transform'
}

function calcPress(btn) {
    if (btn === 'C') { calcClear(); return }
    if (btn === '⌫') { calcDelete(); return }
    if (btn === '=') { calcEvaluate(); return }
    if (btn === '%') {
        const val = parseFloat(calcDisplay.value)
        if (!isNaN(val)) calcDisplay.value = String(val / 100)
        return
    }
    calcInput(btn)
}

function handleKeydown(e) {
    if (!calcOpen.value) return

    const key = e.key
    if (e.key === 'Escape') { calcOpen.value = false; return }

    const map = {
        'Enter': '=',
        '*': '×',
        '/': '÷',
        'Backspace': '⌫',
        'Delete': 'C',
    }

    const mapped = map[key] ?? (['0','1','2','3','4','5','6','7','8','9','+','-','.','%'].includes(key) ? key : null)
    if (mapped) {
        e.preventDefault()
        calcPress(mapped)
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Navbar -->
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto flex max-w-5xl items-center justify-between">
                <span class="text-lg font-bold text-gray-800 dark:text-white">Budget App</span>
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleDark"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
                        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm text-gray-600 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Logout
                    </Link>
                </div>
            </div>
        </nav>

        <div class="mx-auto max-w-5xl px-6 py-8">
            <!-- Summary Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-green-50 p-5 dark:bg-green-900/20">
                    <p class="text-sm font-medium text-green-700 dark:text-green-400">Total Income</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">${{ totalIncome.toFixed(2) }}</p>
                </div>
                <div class="rounded-2xl bg-red-50 p-5 dark:bg-red-900/20">
                    <p class="text-sm font-medium text-red-700 dark:text-red-400">Total Expenses</p>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">${{ totalExpenses.toFixed(2) }}</p>
                </div>
                <div
                    class="rounded-2xl p-5"
                    :class="balance >= 0 ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-orange-50 dark:bg-orange-900/20'"
                >
                    <p class="text-sm font-medium" :class="balance >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-orange-700 dark:text-orange-400'">Balance</p>
                    <p class="mt-1 text-2xl font-bold" :class="balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400'">
                        {{ balance >= 0 ? '+' : '' }}${{ balance.toFixed(2) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Income Section -->
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Income</h2>

                    <Form
                        action="/budget/income"
                        method="post"
                        reset-on-success
                        #default="{ errors, processing }"
                        class="mb-4 flex gap-2"
                    >
                        <div class="flex-1">
                            <input
                                type="text"
                                name="name"
                                placeholder="Source"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                                :class="{ 'border-red-400': errors.name }"
                            />
                        </div>
                        <div class="w-28">
                            <input
                                type="number"
                                name="amount"
                                placeholder="Amount"
                                min="0.01"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                                :class="{ 'border-red-400': errors.amount }"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60"
                        >
                            Add
                        </button>
                    </Form>

                    <ul class="space-y-2">
                        <li
                            v-for="income in incomes"
                            :key="income.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2 text-sm dark:bg-gray-700"
                        >
                            <span class="text-gray-700 dark:text-gray-200">{{ income.name }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-green-600 dark:text-green-400">+${{ Number(income.amount).toFixed(2) }}</span>
                                <button
                                    @click="deleteIncome(income.id)"
                                    class="text-gray-400 transition hover:text-red-500"
                                    title="Delete"
                                >
                                    ×
                                </button>
                            </div>
                        </li>
                        <li v-if="incomes.length === 0" class="py-4 text-center text-sm text-gray-400 dark:text-gray-500">
                            No income added yet.
                        </li>
                    </ul>
                </div>

                <!-- Expenses Section -->
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Expenses</h2>

                    <Form
                        action="/budget/expense"
                        method="post"
                        reset-on-success
                        #default="{ errors, processing }"
                        class="mb-4 flex gap-2"
                    >
                        <div class="flex-1">
                            <input
                                type="text"
                                name="name"
                                placeholder="Category"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                                :class="{ 'border-red-400': errors.name }"
                            />
                        </div>
                        <div class="w-28">
                            <input
                                type="number"
                                name="amount"
                                placeholder="Amount"
                                min="0.01"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500"
                                :class="{ 'border-red-400': errors.amount }"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60"
                        >
                            Add
                        </button>
                    </Form>

                    <ul class="space-y-2">
                        <li
                            v-for="expense in expenses"
                            :key="expense.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2 text-sm dark:bg-gray-700"
                        >
                            <span class="text-gray-700 dark:text-gray-200">{{ expense.name }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-red-600 dark:text-red-400">-${{ Number(expense.amount).toFixed(2) }}</span>
                                <button
                                    @click="deleteExpense(expense.id)"
                                    class="text-gray-400 transition hover:text-red-500"
                                    title="Delete"
                                >
                                    ×
                                </button>
                            </div>
                        </li>
                        <li v-if="expenses.length === 0" class="py-4 text-center text-sm text-gray-400 dark:text-gray-500">
                            No expenses added yet.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Floating Calculator Button -->
        <button
            @click="calcOpen = !calcOpen"
            class="fixed right-6 bottom-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg transition hover:bg-blue-700 hover:scale-110 active:scale-95"
            title="Calculator"
        >
            <svg v-if="!calcOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="4" y="2" width="16" height="20" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2"/>
                <line x1="8" y1="12" x2="8" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="12" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="16" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="8" y1="16" x2="8" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="16" x2="12" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="16" y1="16" x2="16" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <!-- Calculator Panel -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-if="calcOpen"
                class="fixed right-6 bottom-24 z-40 w-72 rounded-2xl bg-white shadow-2xl overflow-hidden"
            >
                <!-- Display -->
                <div class="bg-gray-900 px-4 pt-4 pb-3">
                    <p class="min-h-5 text-right text-xs text-gray-500 truncate">{{ calcExpression }}&nbsp;</p>
                    <p class="text-right text-3xl font-light text-white truncate">{{ calcDisplay }}</p>
                </div>

                <!-- Buttons -->
                <div class="grid grid-cols-4 gap-2 p-3">
                    <template v-for="row in calcButtons" :key="row.join()">
                        <button
                            v-for="btn in row"
                            :key="btn"
                            @click="calcPress(btn)"
                            :class="['h-14 text-base', calcButtonClass(btn), btn === '0' ? 'col-span-2' : '']"
                        >
                            {{ btn }}
                        </button>
                    </template>
                </div>
            </div>
        </Transition>
    </div>
</template>
