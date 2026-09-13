<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

withDefaults(
    defineProps<{
        /** Tailwind sizing utilities applied to the logo image. */
        size?: string;
        /** Render the "Budget" wordmark beside the mark. */
        withName?: boolean;
        /** Tailwind utilities applied to the wordmark. */
        nameClass?: string;
    }>(),
    {
        size: 'h-8 w-8',
        withName: false,
        nameClass: 'text-base font-bold tracking-tight sm:text-lg',
    },
);
</script>

<template>
    <span class="flex items-center gap-2.5">
        <img
            src="/images/logo.png"
            :alt="t('Budget')"
            :class="size"
            class="shrink-0 object-contain"
        />
        <span v-if="withName" :class="nameClass">{{ t('Budget') }}</span>
    </span>
</template>
