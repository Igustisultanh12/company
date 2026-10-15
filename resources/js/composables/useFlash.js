import { router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';
import { toast } from './useToast';

/**
 * Tampilkan flash message (success / error / warning) dari server sebagai toast.
 * Memakai event Inertia agar pesan yang sama tetap muncul setiap kali aksi diulang.
 */
export function useFlash() {
    const page = usePage();

    const showFlash = (flash = {}) => {
        if (flash.success) toast(flash.success, 'success');
        if (flash.error) toast(flash.error, 'error', 7000);
        if (flash.warning) toast(flash.warning, 'warning', 7000);
    };

    const offs = [];
    onMounted(() => {
        showFlash(page.props.flash || {});
        offs.push(router.on('success', (event) => showFlash(event.detail.page?.props?.flash || {})));
        offs.push(
            router.on('error', (event) => {
                const errors = event.detail.errors || {};
                if (errors.form) toast(errors.form, 'error', 7000);
            }),
        );
    });
    onBeforeUnmount(() => offs.forEach((off) => off?.()));
}
