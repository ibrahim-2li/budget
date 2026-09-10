<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { store, update, destroy } from '@/routes/admin/categories'

const props = defineProps({
    categories: { type: Array, required: true },
})

const colorOptions = [
    'text-green-500', 'text-emerald-500', 'text-blue-500', 'text-sky-500',
    'text-indigo-500', 'text-purple-500', 'text-pink-500', 'text-rose-500',
    'text-red-500', 'text-orange-500', 'text-amber-500', 'text-yellow-500',
    'text-teal-500', 'text-gray-500',
]

const iconOptions = [
    'fa-solid fa-money-bill-wave', 'fa-solid fa-laptop-code', 'fa-solid fa-chart-line',
    'fa-solid fa-gift', 'fa-solid fa-house', 'fa-solid fa-burger', 'fa-solid fa-bus',
    'fa-solid fa-car', 'fa-solid fa-ticket', 'fa-solid fa-cart-shopping',
    'fa-solid fa-staff-snake', 'fa-solid fa-graduation-cap', 'fa-solid fa-plane',
    'fa-solid fa-bolt', 'fa-solid fa-wifi', 'fa-solid fa-phone', 'fa-solid fa-box', 'fa-vellum fa-solid fa-chart-pie',
]

const blank = { name: '', type: 'expense', color: 'text-gray-500', icon: 'fa-solid fa-box' }

const form = useForm({ ...blank })
const editingId = ref<number | null>(null)
const isEditing = computed(() => editingId.value !== null)

const incomeCategories = computed(() => props.categories.filter((c: any) => c.type === 'income'))
const expenseCategories = computed(() => props.categories.filter((c: any) => c.type === 'expense'))

function startEdit(category) {
    editingId.value = category.id
    form.clearErrors()
    form.name = category.name
    form.type = category.type
    form.color = category.color
    form.icon = category.icon
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function cancelEdit() {
    editingId.value = null
    form.clearErrors()
    form.defaults({ ...blank })
    form.reset()
}

function submit() {
    if (isEditing.value) {
        form.patch(update.url(editingId.value), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        })
        return
    }

    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

function remove(category) {
    if (!confirm(`Delete "${category.name}"? This cannot be undone.`)) return

    form.delete(destroy.url(category.id), { preserveScroll: true })
}
</script>

<template>
    <Head title="Categories" />

    <AdminLayout title="Categories" subtitle="Shared across every user's budget.">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Editor -->
            <section
                class="rounded-2xl border border-gray-200 bg-white shadow-sm lg:sticky lg:top-24 lg:order-2 lg:self-start dark:border-gray-800 dark:bg-gray-900">
                <header class="border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800">
                    <h2 class="text-base font-semibold">{{ isEditing ? 'Edit category' : 'New category' }}</h2>
                </header>

                <form @submit.prevent="submit" class="space-y-4 px-4 py-4 sm:px-5">
                    <!-- Preview -->
                    <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-gray-800/60">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-gray-900">
                            <i :class="[form.icon, form.color]" class="text-sm"></i>
                        </span>
                        <span class="truncate text-sm font-medium">{{ form.name || 'Category name' }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g. Groceries"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            :class="{ 'border-rose-400': form.errors.name }" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="type in ['income', 'expense']" :key="type" type="button" @click="form.type = type"
                                :class="[
                                    'rounded-lg border px-3 py-2 text-sm font-medium capitalize transition',
                                    form.type === type
                                        ? type === 'income'
                                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'border-rose-500 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800',
                                ]">
                                {{ type }}
                            </button>
                        </div>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-rose-500">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Icon</label>
                        <div class="grid max-h-36 grid-cols-6 gap-1.5 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                            <button v-for="icon in iconOptions" :key="icon" type="button" @click="form.icon = icon"
                                :class="[
                                    'flex h-9 items-center justify-center rounded-lg transition',
                                    form.icon === icon ? 'bg-indigo-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800',
                                ]">
                                <i :class="icon" class="text-sm"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.icon" class="mt-1 text-xs text-rose-500">{{ form.errors.icon }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Color</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="color in colorOptions" :key="color" type="button" @click="form.color = color"
                                :aria-label="color"
                                :class="[
                                    'flex h-7 w-7 items-center justify-center rounded-full border-2 transition',
                                    form.color === color ? 'border-gray-900 dark:border-white' : 'border-transparent',
                                ]">
                                <i class="fa-solid fa-circle text-base" :class="color"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.color" class="mt-1 text-xs text-rose-500">{{ form.errors.color }}</p>
                    </div>

                    <p v-if="form.errors.category" class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">
                        {{ form.errors.category }}
                    </p>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 active:scale-95 disabled:opacity-60">
                            {{ isEditing ? 'Save changes' : 'Add category' }}
                        </button>
                        <button v-if="isEditing" type="button" @click="cancelEdit"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                    </div>
                </form>
            </section>

            <!-- Lists -->
            <div class="space-y-4 lg:col-span-2 lg:order-1">
                <section v-for="group in [
                    { label: 'Income', items: incomeCategories, dot: 'bg-emerald-500' },
                    { label: 'Expense', items: expenseCategories, dot: 'bg-rose-500' },
                ]" :key="group.label"
                    class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <header
                        class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5 dark:border-gray-800">
                        <h2 class="flex items-center gap-2 text-base font-semibold">
                            <span class="h-2 w-2 rounded-full" :class="group.dot"></span>
                            {{ group.label }}
                        </h2>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ group.items.length }}</span>
                    </header>

                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        <li v-for="category in group.items" :key="category.id"
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-gray-50 sm:px-5 dark:hover:bg-gray-800/50"
                            :class="{ 'bg-indigo-50/60 dark:bg-indigo-500/10': editingId === category.id }">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800">
                                <i :class="[category.icon, category.color]" class="text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ category.name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ category.usage_count }} {{ category.usage_count === 1 ? 'entry' : 'entries' }}
                                </p>
                            </div>
                            <button type="button" @click="startEdit(category)" aria-label="Edit category"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/15">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <button type="button" @click="remove(category)" :disabled="category.usage_count > 0"
                                :title="category.usage_count > 0 ? 'In use by existing entries' : 'Delete'"
                                aria-label="Delete category"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-gray-400 dark:hover:bg-rose-500/15">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </li>
                        <li v-if="group.items.length === 0" class="px-5 py-8 text-center text-sm text-gray-400">
                            No {{ group.label.toLowerCase() }} categories yet.
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
