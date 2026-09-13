<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';
import { update as updateLocale } from '@/routes/locale';

const { locale, t } = useI18n();

function switchLocale(): void {
    router.post(
        updateLocale.url(locale.value === 'ar' ? 'en' : 'ar'),
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <button
        type="button"
        @click="switchLocale"
        :aria-label="t('Change language')"
        class="flex h-9 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
    >
        <i class="fa-solid fa-language"></i>
        <span :lang="locale === 'ar' ? 'en' : 'ar'">{{
            locale === 'ar' ? 'English' : 'العربية'
        }}</span>
    </button>
</template>
