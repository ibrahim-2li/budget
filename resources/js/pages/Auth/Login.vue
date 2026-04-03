<script setup lang="js">
import { Form } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

defineOptions({ title: 'Login' })
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-md">
            <h1 class="mb-6 text-center text-2xl font-bold text-gray-800">Sign in to Budget</h1>

            <Form action="/login" method="post" #default="{ errors, processing }">
                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        name="email"
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        :class="{ 'border-red-400': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
                </div>

                <div class="mb-6">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        :class="{ 'border-red-400': errors.password }"
                    />
                    <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="processing"
                    class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60"
                >
                    {{ processing ? 'Signing in...' : 'Sign In' }}
                </button>
            </Form>

            <p class="mt-4 text-center text-sm text-gray-500">
                Don't have an account?
                <Link href="/register" class="font-medium text-blue-600 hover:underline">Register</Link>
            </p>
        </div>
    </div>
</template>
