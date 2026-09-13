<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';

defineProps<{
    items: { name: string; href: string; icon: string }[];
}>();

const page = usePage();
const { t } = useI18n();

const isActive = (href: string) => page.url.split('?')[0] === href;
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-md lg:hidden dark:border-gray-800 dark:bg-gray-900/90"
    >
        <div class="mx-auto flex h-16 max-w-md items-stretch justify-around">
            <Link
                v-for="item in items"
                :key="item.name"
                :href="item.href"
                :aria-current="isActive(item.href) ? 'page' : undefined"
                class="flex flex-1 flex-col items-center justify-center gap-1 text-[11px] font-medium transition"
                :class="
                    isActive(item.href)
                        ? 'text-primary-600 dark:text-primary-400'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                "
            >
                <span
                    class="flex h-7 w-12 items-center justify-center rounded-full transition"
                    :class="
                        isActive(item.href)
                            ? 'bg-primary-600/10 dark:bg-primary-400/15'
                            : ''
                    "
                >
                    <i :class="item.icon" class="text-base"></i>
                </span>
                {{ t(item.name) }}
            </Link>
        </div>
    </nav>
</template>
