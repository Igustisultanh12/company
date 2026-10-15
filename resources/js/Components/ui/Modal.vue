<script setup>
import { X } from '@lucide/vue';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    size: { type: String, default: 'md' },
    closeable: { type: Boolean, default: true },
});
const emit = defineEmits(['close']);
const panel = ref(null);

const widths = { sm: 'max-w-md', md: 'max-w-xl', lg: 'max-w-3xl', xl: 'max-w-5xl', full: 'max-w-7xl' };

const close = () => {
    if (props.closeable) emit('close');
};
const onKey = (e) => {
    if (e.key === 'Escape' && props.show) close();
};

watch(
    () => props.show,
    async (open) => {
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            window.addEventListener('keydown', onKey);
            await nextTick();
            panel.value?.querySelector('input:not([type=hidden]),textarea,select')?.focus({ preventScroll: true });
        } else {
            window.removeEventListener('keydown', onKey);
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKey);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" @click="close"></div>
                <div
                    ref="panel"
                    :class="[widths[size] || widths.md]"
                    class="animate-pop-in relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl border-2 border-ink bg-card shadow-brutal-lg sm:rounded-3xl"
                >
                    <div v-if="title || closeable" class="flex items-start justify-between gap-4 border-b border-line px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold">{{ title }}</h3>
                            <p v-if="subtitle" class="mt-0.5 text-sm text-muted">{{ subtitle }}</p>
                        </div>
                        <button v-if="closeable" type="button" class="btn btn-ghost btn-icon -mr-2 shrink-0" aria-label="Tutup" @click="close">
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <div class="overflow-y-auto px-6 py-5">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="flex flex-wrap items-center justify-end gap-2 border-t border-line bg-surface-2/50 px-6 py-4">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
