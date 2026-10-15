import { reactive } from 'vue';

const state = reactive({ items: [] });
let id = 0;

export function toast(message, type = 'success', timeout = 4500) {
    if (!message) return;
    const item = { id: ++id, message, type };
    state.items.push(item);
    if (state.items.length > 5) state.items.shift();
    if (timeout) setTimeout(() => dismiss(item.id), timeout);
}

export function dismiss(itemId) {
    const i = state.items.findIndex((t) => t.id === itemId);
    if (i !== -1) state.items.splice(i, 1);
}

export function useToast() {
    return {
        state,
        success: (m) => toast(m, 'success'),
        error: (m) => toast(m, 'error', 6500),
        warning: (m) => toast(m, 'warning', 6000),
        info: (m) => toast(m, 'info'),
        dismiss,
    };
}
