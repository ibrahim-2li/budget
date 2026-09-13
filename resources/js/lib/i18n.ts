import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type Locale = 'ar' | 'en';

/** Locales rendered right-to-left. */
const rtlLocales: Locale[] = ['ar'];

export function isRtlLocale(locale: string): boolean {
    return rtlLocales.includes(locale as Locale);
}

/** Sync the <html> lang/dir attributes with the active locale. */
export function applyDocumentLocale(locale: string): void {
    document.documentElement.lang = locale;
    document.documentElement.dir = isRtlLocale(locale) ? 'rtl' : 'ltr';
}

/**
 * Translate UI strings using the JSON translations shared by the server.
 *
 * Keys are the English source strings, so a missing translation falls back
 * to readable English. Placeholders use Laravel's `:name` syntax.
 */
export function useI18n() {
    const page = usePage();

    const locale = computed<Locale>(
        () => (page.props.locale as Locale | undefined) ?? 'ar',
    );
    const isRtl = computed(() => isRtlLocale(locale.value));

    /** Intl locale for dates and numbers; Arabic keeps the Gregorian calendar and Latin digits. */
    const intlLocale = computed(() =>
        locale.value === 'ar' ? 'ar-SA-u-ca-gregory-nu-latn' : 'en-US',
    );

    function t(
        key: string,
        replacements: Record<string, string | number> = {},
    ): string {
        const translations = (page.props.translations ?? {}) as Record<
            string,
            string
        >;

        return Object.entries(replacements).reduce(
            (value, [name, replacement]) =>
                value.replaceAll(`:${name}`, String(replacement)),
            translations[key] ?? key,
        );
    }

    return { t, locale, isRtl, intlLocale };
}
