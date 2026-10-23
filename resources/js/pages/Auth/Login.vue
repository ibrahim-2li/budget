<script setup lang="ts">
import { Form, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import {
    login,
    showRegister,
} from '@/actions/App/Http/Controllers/AuthController';
import { login as biometricLogin } from '@/actions/App/Http/Controllers/BiometricController';
import AuthLayout from '@/layouts/AuthLayout.vue';
import {
    biometricStatus,
    clearBiometric,
    unlockBiometric,
} from '@/lib/biometric';
import { useI18n } from '@/lib/i18n';

defineOptions({ title: 'Login' });

const { t } = useI18n();

const page = usePage();

const showPassword = ref(false);
const canUseFingerprint = ref(false);
const fingerprintProcessing = ref(false);
const fingerprintError = ref('');

onMounted(() => {
    canUseFingerprint.value = biometricStatus() === 'enrolled';

    if (canUseFingerprint.value && !page.flash.loggedOut) {
        signInWithFingerprint();
    }
});

async function signInWithFingerprint() {
    fingerprintError.value = '';
    fingerprintProcessing.value = true;

    const result = await unlockBiometric();

    if (result.status !== 'ok' || !result.token) {
        fingerprintProcessing.value = false;

        if (result.status === 'invalidated') {
            canUseFingerprint.value = false;
            fingerprintError.value = t(
                'Your fingerprints changed. Sign in with your password to turn fingerprint sign-in on again.',
            );
        }

        return;
    }

    router.post(
        biometricLogin.url(),
        { token: result.token },
        {
            onError: (errors) => {
                if (errors.biometric) {
                    clearBiometric();
                    canUseFingerprint.value = false;
                    fingerprintError.value = errors.biometric;
                }
            },
            onFinish: () => (fingerprintProcessing.value = false),
        },
    );
}

const fieldClass =
    'w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500';
</script>

<template>
    <AuthLayout
        :title="t('Welcome back')"
        :subtitle="t('Sign in to pick up where you left off.')"
    >
        <div v-if="canUseFingerprint" class="mb-5">
            <button
                type="button"
                :disabled="fingerprintProcessing"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-primary-600 px-4 py-2.5 text-sm font-semibold text-primary-600 transition hover:bg-primary-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-500/10"
                @click="signInWithFingerprint"
            >
                <i
                    :class="
                        fingerprintProcessing
                            ? 'fa-solid fa-circle-notch animate-spin text-xs'
                            : 'fa-solid fa-fingerprint'
                    "
                ></i>
                {{ t('Sign in with fingerprint') }}
            </button>
            <div
                class="mt-5 flex items-center gap-3 text-xs text-gray-400 dark:text-gray-500"
            >
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></span>
                {{ t('or') }}
                <span class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></span>
            </div>
        </div>
        <p
            v-if="fingerprintError"
            class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400"
            role="alert"
        >
            {{ fingerprintError }}
        </p>

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
                            showPassword
                                ? t('Hide password')
                                : t('Show password')
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
