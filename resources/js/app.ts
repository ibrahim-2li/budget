import { createInertiaApp, router } from '@inertiajs/vue3';
import { applyDocumentLocale } from '@/lib/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Budget';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#10a9c6',
    },
});

// Keep <html lang/dir> in sync when the locale changes without a full reload.
router.on('navigate', (event) => {
    applyDocumentLocale(String(event.detail.page.props.locale ?? 'ar'));
});
