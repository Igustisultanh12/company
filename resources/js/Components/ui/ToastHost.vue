<script setup>
import { CircleAlert, CircleCheck, Info, TriangleAlert, X } from '@lucide/vue';
import { useToast } from '@/composables/useToast';

const { state, dismiss } = useToast();

const styles = {
    success: { icon: CircleCheck, cls: 'bg-lime-300 text-zinc-950' },
    error: { icon: CircleAlert, cls: 'bg-rose-400 text-white' },
    warning: { icon: TriangleAlert, cls: 'bg-amber-300 text-zinc-950' },
    info: { icon: Info, cls: 'bg-sky-300 text-zinc-950' },
};
</script>

<template>
    <div class="pointer-events-none fixed right-0 bottom-0 z-[95] flex w-full flex-col items-end gap-3 p-4 sm:max-w-sm" aria-live="polite">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0 sm:translate-x-8 sm:translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0 scale-95"
            move-class="transition duration-300"
        >
            <div
                v-for="t in state.items"
                :key="t.id"
                :class="(styles[t.type] || styles.info).cls"
                class="pointer-events-auto flex w-full items-start gap-3 rounded-2xl border-2 border-zinc-950 px-4 py-3 text-sm font-semibold shadow-[4px_4px_0_0_#09090b]"
                role="status"
            >
                <component :is="(styles[t.type] || styles.info).icon" class="mt-0.5 h-5 w-5 shrink-0" />
                <p class="flex-1 leading-snug">{{ t.message }}</p>
                <button type="button" class="-mr-1 rounded-lg p-0.5 opacity-70 hover:opacity-100" aria-label="Tutup" @click="dismiss(t.id)">
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
