<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useI18n } from '@/lib/i18n'
import { users as usersRoute } from '@/routes/admin'
import { role as updateRole } from '@/routes/admin/users'

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, required: true },
    filters: { type: Object, required: true },
})

const page = usePage()
const { t } = useI18n()
const currentUserId = page.props.auth?.user?.id

const search = ref(props.filters.search ?? '')
let searchTimeout: ReturnType<typeof setTimeout>

watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(usersRoute.url(), { search: value }, { preserveState: true, replace: true })
    }, 300)
})

function money(value) {
    return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function changeRole(user, roleId) {
    if (Number(roleId) === user.role_id) return

    router.patch(updateRole.url(user.id), { role_id: Number(roleId) }, { preserveScroll: true })
}
</script>

<template>
    <Head :title="t('Users')" />

    <AdminLayout :title="t('Users')" :subtitle="t(users.total === 1 ? ':count registered account' : ':count registered accounts', { count: users.total })">
        <template #actions>
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 start-3 -translate-y-1/2 text-sm text-gray-400"></i>
                <input v-model="search" type="search" :placeholder="t('Search name or email')"
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 pe-3 ps-9 text-sm placeholder-gray-400 transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </div>
        </template>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <!-- Desktop table -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-start text-sm">
                    <thead
                        class="border-b border-gray-100 text-xs font-medium tracking-wide text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">{{ t('User') }}</th>
                            <th class="px-5 py-3 text-end">{{ t('Income') }}</th>
                            <th class="px-5 py-3 text-end">{{ t('Expenses') }}</th>
                            <th class="px-5 py-3 text-end">{{ t('Entries') }}</th>
                            <th class="px-5 py-3">{{ t('Joined') }}</th>
                            <th class="px-5 py-3">{{ t('Role') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in users.data" :key="user.id"
                            class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 uppercase dark:bg-gray-800 dark:text-gray-300">
                                        {{ user.name.slice(0, 2) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ user.name }}
                                            <span v-if="user.id === currentUserId"
                                                class="ms-1 rounded bg-primary-50 px-1.5 py-0.5 text-xs font-medium text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">{{ t('You') }}</span>
                                        </p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-end font-medium tabular-nums text-secondary-600 dark:text-secondary-400">
                                <span class="icon-saudi_riyal">&#xea;</span>{{ money(user.incomes_total) }}
                            </td>
                            <td class="px-5 py-3 text-end font-medium tabular-nums text-rose-600 dark:text-rose-400">
                                <span class="icon-saudi_riyal">&#xea;</span>{{ money(user.expenses_total) }}
                            </td>
                            <td class="px-5 py-3 text-end tabular-nums text-gray-500 dark:text-gray-400">
                                {{ user.incomes_count + user.expenses_count }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ user.created_at }}</td>
                            <td class="px-5 py-3">
                                <select :value="user.role_id" :disabled="user.id === currentUserId"
                                    @change="changeRole(user, $event.target.value)"
                                    class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ t(role.name) }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">{{ t('No users match that search.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <ul class="divide-y divide-gray-100 md:hidden dark:divide-gray-800">
                <li v-for="user in users.data" :key="user.id" class="p-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 uppercase dark:bg-gray-800 dark:text-gray-300">
                            {{ user.name.slice(0, 2) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">
                                {{ user.name }}
                                <span v-if="user.id === currentUserId"
                                    class="ms-1 rounded bg-primary-50 px-1.5 py-0.5 text-xs font-medium text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">{{ t('You') }}</span>
                            </p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                        </div>
                    </div>

                    <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-gray-50 py-2 dark:bg-gray-800/60">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ t('Income') }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-secondary-600 dark:text-secondary-400">
                                <span class="icon-saudi_riyal">&#xea;</span>{{ money(user.incomes_total) }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 py-2 dark:bg-gray-800/60">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ t('Expenses') }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-rose-600 dark:text-rose-400">
                                <span class="icon-saudi_riyal">&#xea;</span>{{ money(user.expenses_total) }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 py-2 dark:bg-gray-800/60">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ t('Entries') }}</dt>
                            <dd class="text-sm font-semibold tabular-nums">{{ user.incomes_count + user.expenses_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ t('Joined :date', { date: user.created_at }) }}</span>
                        <select :value="user.role_id" :disabled="user.id === currentUserId"
                            @change="changeRole(user, $event.target.value)"
                            class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm transition focus:border-primary-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ t(role.name) }}</option>
                        </select>
                    </div>
                </li>
                <li v-if="users.data.length === 0" class="p-10 text-center text-sm text-gray-400">
                    {{ t('No users match that search.') }}
                </li>
            </ul>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-4 flex flex-wrap items-center justify-center gap-1">
            <Link v-for="link in users.links" :key="link.label" :href="link.url ?? '#'" preserve-scroll
                :class="[
                    'rounded-lg px-3 py-1.5 text-sm transition',
                    link.active
                        ? 'bg-primary-600 font-semibold text-white'
                        : link.url
                            ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                            : 'cursor-not-allowed text-gray-300 dark:text-gray-700',
                ]" v-html="link.label" />
        </div>
    </AdminLayout>
</template>
