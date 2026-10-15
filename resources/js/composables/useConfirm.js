import { reactive } from 'vue';

const state = reactive({
    open: false,
    title: '',
    message: '',
    confirmText: 'Ya, lanjutkan',
    cancelText: 'Batal',
    danger: false,
    input: null,
    value: '',
    resolve: null,
});

/**
 * const ok = await confirm({ title, message, danger: true })
 * const reason = await confirm({ title, input: { label: 'Alasan', required: true } }) // string | false
 */
export function confirm(opts = {}) {
    Object.assign(state, {
        open: true,
        title: opts.title || 'Yakin?',
        message: opts.message || '',
        confirmText: opts.confirmText || (opts.danger ? 'Ya, hapus' : 'Ya, lanjutkan'),
        cancelText: opts.cancelText || 'Batal',
        danger: !!opts.danger,
        input: opts.input || null,
        value: opts.input?.value || '',
    });
    return new Promise((resolve) => {
        state.resolve = resolve;
    });
}

export function settle(result) {
    state.open = false;
    const r = state.resolve;
    state.resolve = null;
    if (r) r(result && state.input ? state.value : result);
}

export function useConfirm() {
    return { state, confirm, settle };
}
