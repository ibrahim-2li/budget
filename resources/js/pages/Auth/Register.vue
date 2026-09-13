<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    register,
    showLogin,
} from '@/actions/App/Http/Controllers/AuthController';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useI18n } from '@/lib/i18n';

defineOptions({ title: 'Register' });

const { t } = useI18n();

const showPassword = ref(false);

const fieldClass =
    'w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500';

const errorClass = 'border-red-400 focus:border-red-500 focus:ring-red-500/20';
</script>

<template>
    <AuthLayout
        :title="t('Create your account')"
        :subtitle="t('Start tracking your budget in under a minute.')"
    >
        <Form
            v-bind="register.form()"
            class="space-y-5"
            #default="{ errors, processing }"
        >
            <div>
                <label
                    for="name"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('Name') }}
                </label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    autocomplete="name"
                    :placeholder="t('Your name')"
                    :class="[fieldClass, errors.name && errorClass]"
                />
                <p v-if="errors.name" class="mt-1.5 text-xs text-red-500">
                    {{ errors.name }}
                </p>
            </div>

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
                    :class="[fieldClass, errors.email && errorClass]"
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
                        autocomplete="new-password"
                        :placeholder="t('At least 8 characters')"
                        :class="[
                            fieldClass,
                            'pe-11',
                            errors.password && errorClass,
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

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('Confirm password') }}
                </label>
                <input
                    id="password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    name="password_confirmation"
                    autocomplete="new-password"
                    :placeholder="t('Repeat your password')"
                    :class="[
                        fieldClass,
                        errors.password_confirmation && errorClass,
                    ]"
                />
                <p
                    v-if="errors.password_confirmation"
                    class="mt-1.5 text-xs text-red-500"
                >
                    {{ errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="processing"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-600/25 transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <i
                    v-if="processing"
                    class="fa-solid fa-circle-notch animate-spin text-xs"
                ></i>
                {{ processing ? t('Creating account…') : t('Create account') }}
            </button>
        </Form>

        <template #footer>
            {{ t('Already have an account?') }}
            <Link
                :href="showLogin.url()"
                class="font-medium text-primary-600 hover:underline dark:text-primary-400"
            >
                {{ t('Sign in') }}
            </Link>
        </template>
    </AuthLayout>
</template>
