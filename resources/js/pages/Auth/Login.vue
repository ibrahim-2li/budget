<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    login,
    showRegister,
} from '@/actions/App/Http/Controllers/AuthController';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ title: 'Login' });

const { t } = useI18n();

const showPassword = ref(false);

const fieldClass =
    'w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500';
</script>

<template>
    <AuthLayout
        :title="t('Welcome back')"
        :subtitle="t('Sign in to pick up where you left off.')"
    >
        <Form
            v-bind="login.form()"
            class="space-y-5"
            #default="{ errors, processing }"
        >
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('Email') }}
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    :class="[
                        fieldClass,
                        errors.email &&
                            'border-red-400 focus:border-red-500 focus:ring-red-500/20',
                    ]"
                />
                <p v-if="errors.email" class="mt-1.5 text-xs text-red-500">
                    {{ errors.email }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('Password') }}
                </label>
                <div class="relative">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :class="[
                            fieldClass,
                            'pe-11',
                            errors.password &&
                                'border-red-400 focus:border-red-500 focus:ring-red-500/20',
                        ]"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                        :aria-label="
                            showPassword ? t('Hide password') : t('Show password')
                        "
                    >
                        <i
                            :class="
                                showPassword
                                    ? 'fa-solid fa-eye-slash'
                                    : 'fa-solid fa-eye'
                            "
                            class="text-sm"
                        ></i>
                    </button>
                </div>
                <p v-if="errors.password" class="mt-1.5 text-xs text-red-500">
                    {{ errors.password }}
                </p>
            </div>

            <label
                class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
            >
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900"
                />
                {{ t('Keep me signed in') }}
            </label>

            <button
                type="submit"
                :disabled="processing"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <i
                    v-if="processing"
                    class="fa-solid fa-circle-notch animate-spin text-xs"
                ></i>
                {{ processing ? t('Signing in…') : t('Sign in') }}
            </button>
        </Form>

        <template #footer>
            {{ t("Don't have an account?") }}
            <Link
                :href="showRegister.url()"
                class="font-medium text-primary-600 hover:underline dark:text-primary-400"
            >
                {{ t('Create one') }}
            </Link>
        </template>
    </AuthLayout>
</template>
