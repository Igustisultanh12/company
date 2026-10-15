<script setup>
import { ExternalLink, X } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import Logo from '@/Components/ui/Logo.vue';
import { adminMenu, agentMenu } from './menu';

defineProps({ open: { type: Boolean, default: false } });
const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const groups = computed(() => (user.value?.role === 'admin' ? adminMenu() : agentMenu()));

const isActive = (item) => {
    try {
        if (item.params) return route().current(item.route, item.params);
        return route().current(item.match || item.route);
    } catch {
        return false;
    }
};
const href = (item) => route(item.route, item.params || {});
</script>

<template>
    <div>
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-40 bg-zinc-950/50 backdrop-blur-sm lg:hidden" @click="emit('close')"></div>
        </Transition>
        <aside
            :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-line bg-card transition-transform duration-300 ease-out"
            aria-label="Menu panel"
        >
            <div class="flex h-16 shrink-0 items-center justify-between px-5">
                <Link :href="route('dashboard')" class="group"><Logo :name="page.props.site?.short_name || 'Kreatif Sky'" /></Link>
                <button type="button" class="btn btn-ghost btn-icon lg:hidden" aria-label="Tutup menu" @click="emit('close')"><X class="h-5 w-5" /></button>
            </div>
            <div class="mx-4 mb-2 rounded-2xl border-2 border-ink bg-yellow-300 px-3 py-2 text-xs font-extrabold tracking-wide text-zinc-900 uppercase shadow-brutal-sm">
                {{ user?.role === 'admin' ? '👑 Panel Admin' : '🚀 Panel Agen' }}
            </div>
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-3">
                <div v-for="g in groups" :key="g.title">
                    <p class="px-3 pb-1.5 text-[11px] font-extrabold tracking-[0.14em] text-muted/80 uppercase">{{ g.title }}</p>
                    <ul class="space-y-0.5">
                        <li v-for="item in g.items" :key="item.label">
                            <Link
                                :href="href(item)"
                                :class="isActive(item) ? 'bg-ink text-surface shadow-[0_8px_20px_-8px_rgb(124_58_237/0.6)]' : 'text-muted hover:bg-surface-2 hover:text-ink'"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200"
                                @click="emit('close')"
                            >
                                <span :class="isActive(item) ? 'bg-brand-gradient text-white' : 'bg-surface-2 group-hover:bg-card'" class="grid h-8 w-8 place-items-center rounded-lg transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                                    <component :is="item.icon" class="h-4 w-4" />
                                </span>
                                <span class="truncate">{{ item.label }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="border-t border-line p-3">
                <a :href="route('home')" target="_blank" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-2 hover:text-ink">
                    Lihat website <ExternalLink class="h-4 w-4" />
                </a>
            </div>
        </aside>
    </div>
</template>
