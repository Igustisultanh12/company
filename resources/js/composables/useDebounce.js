import { ref, watch } from 'vue';

export function useDebouncedRef(initial, delay = 400) {
    const value = ref(initial);
    const debounced = ref(initial);
    let t = null;
    watch(value, (v) => {
        clearTimeout(t);
        t = setTimeout(() => (debounced.value = v), delay);
    });
    return { value, debounced };
}

export function debounce(fn, delay = 400) {
    let t = null;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}
