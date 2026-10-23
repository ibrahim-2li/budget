<script setup lang="ts">
import { useHttp, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { store } from '@/actions/App/Http/Controllers/BiometricController';
import { biometricStatus, enrollBiometric } from '@/lib/biometric';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();
const page = usePage();

const visible = ref(false);
const enrolling = ref(false);
const message = ref<{ type: 'success' | 'error'; text: string } | null>(null);

const http = useHttp<Record<string, never>, { token: string }>({});

onMounted(() => {
    visible.value =
        page.flash.offerBiometric === true && biometricStatus() === 'available';
});

function turnOn() {
    enrolling.value = true;
    message.value = null;

    http.post(store.url(), {
        onSuccess: async (response) => {
            const result = await enrollBiometric(response.token);
            enrolling.value = false;

            if (result.status === 'ok') {
                message.value = {
                    type: 'success',
                    text: t('Fingerprint sign-in is on.'),
                };
                setTimeout(() => (visible.value = false), 2500);
            } else if (result.status !== 'cancelled') {
                message.value = {
                    type: 'error',
                    text: t(
                        'Could not turn on fingerprint sign-in. Try again later.',
                    ),
                };
            }
        },
        onError: () => {
            enrolling.value = false;
            message.value = {
                type: 'error',
                text: t(
                    'Could not turn on fingerprint sign-in. Try again later.',
                ),
            };
        },
    });
}
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-4 bottom-24 z-40 mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-4 shadow-lg lg:bottom-6 dark:border-gray-800 dark:bg-gray-900"
        role="dialog"
        :aria-label="t('Sign in faster next time')"
    >
        <div class="flex items-start gap-3">
            <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400"
            >
                <i class="fa-solid fa-fingerprint text-lg"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">
                    {{ t('Sign in faster next time') }}
                </p>
                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                    {{
                        t(
                            'Use your fingerprint instead of your password on this phone.',
                        )
                    }}
                </p>
                <p
                    v-if="message"
                    class="mt-2 text-sm"
                    :class="
                        message.type === 'success'
                            ? 'text-green-600 dark:text-green-400'
                            : 'text-red-500'
                    "
                >
                    {{ message.text }}
                </p>
            </div>
        </div>
        <div
            v-if="message?.type !== 'success'"
            class="mt-4 flex justify-end gap-2"
        >
            <button
                type="button"
                class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="visible = false"
            >
                {{ t('Not now') }}
            </button>
            <button
                type="button"
                :disabled="enrolling"
                class="flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                @click="turnOn"
            >
                <i
                    v-if="enrolling"
                    class="fa-solid fa-circle-notch animate-spin text-xs"
                ></i>
                {{ t('Turn on') }}
            </button>
        </div>
    </div>
</template>
