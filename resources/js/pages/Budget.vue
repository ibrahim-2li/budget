<script setup>
import { computed } from 'vue'
import { Form, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

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
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="border-b border-gray-200 bg-white px-6 py-4">
            <div class="mx-auto flex max-w-5xl items-center justify-between">
                <span class="text-lg font-bold text-gray-800">Budget App</span>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm text-gray-600 transition hover:bg-gray-100"
                >
                    Logout
                </Link>
            </div>
        </nav>

        <div class="mx-auto max-w-5xl px-6 py-8">
            <!-- Summary Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-green-50 p-5">
                    <p class="text-sm font-medium text-green-700">Total Income</p>
                    <p class="mt-1 text-2xl font-bold text-green-600">${{ totalIncome.toFixed(2) }}</p>
                </div>
                <div class="rounded-2xl bg-red-50 p-5">
                    <p class="text-sm font-medium text-red-700">Total Expenses</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">${{ totalExpenses.toFixed(2) }}</p>
                </div>
                <div
                    class="rounded-2xl p-5"
                    :class="balance >= 0 ? 'bg-blue-50' : 'bg-orange-50'"
                >
                    <p class="text-sm font-medium" :class="balance >= 0 ? 'text-blue-700' : 'text-orange-700'">Balance</p>
                    <p class="mt-1 text-2xl font-bold" :class="balance >= 0 ? 'text-blue-600' : 'text-orange-600'">
                        {{ balance >= 0 ? '+' : '' }}${{ balance.toFixed(2) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Income Section -->
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800">Income</h2>

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
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none"
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
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none"
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
                            class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2 text-sm"
                        >
                            <span class="text-gray-700">{{ income.name }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-green-600">+${{ Number(income.amount).toFixed(2) }}</span>
                                <button
                                    @click="deleteIncome(income.id)"
                                    class="text-gray-400 transition hover:text-red-500"
                                    title="Delete"
                                >
                                    ×
                                </button>
                            </div>
                        </li>
                        <li v-if="incomes.length === 0" class="py-4 text-center text-sm text-gray-400">
                            No income added yet.
                        </li>
                    </ul>
                </div>

                <!-- Expenses Section -->
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800">Expenses</h2>

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
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none"
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
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none"
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
                            class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2 text-sm"
                        >
                            <span class="text-gray-700">{{ expense.name }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-red-600">-${{ Number(expense.amount).toFixed(2) }}</span>
                                <button
                                    @click="deleteExpense(expense.id)"
                                    class="text-gray-400 transition hover:text-red-500"
                                    title="Delete"
                                >
                                    ×
                                </button>
                            </div>
                        </li>
                        <li v-if="expenses.length === 0" class="py-4 text-center text-sm text-gray-400">
                            No expenses added yet.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
