import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { debounce } from './useDebounce';

/**
 * Sinkronkan filter tabel ke query string (GET) dengan debounce.
 * const filters = useFilters(props.filters, route('admin.orders.index'))
 */
export function useFilters(initial, url, options = {}) {
    const filters = reactive({ ...initial });
    const go = debounce(() => {
        const query = Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '' && v !== null && v !== false && v !== undefined));
        router.get(url, query, { preserveState: true, preserveScroll: true, replace: true, ...options });
    }, options.delay ?? 350);
    watch(filters, go, { deep: true });
    return filters;
}
