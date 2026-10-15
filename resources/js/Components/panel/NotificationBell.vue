<script setup>
import { Bell, CheckCheck } from '@lucide/vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Dropdown from '@/Components/ui/Dropdown.vue';
import http from '@/lib/http';
import { relative } from '@/lib/format';
import { solid } from '@/lib/colors';

const page = usePage();
const unread = ref(page.props.unreadNotifications || 0);
const items = ref([]);
const loading = ref(false);
let timer = null;

watch(() => page.props.unreadNotifications, (v) => (unread.value = v || 0));

const levelColor = { info: 'sky', success: 'emerald', warning: 'amber', danger: 'rose' };
const badgeText = computed(() => (unread.value > 99 ? '99+' : unread.value));

const load = async () => {
    loading.value = true;
    try {
        const { data } = await http.get(route('panel.notifications.latest'));
        items.value = data.items;
        unread.value = data.unread;
    } catch {
        /* abaikan */
    } finally {
        loading.value = false;
    }
};

const poll = async () => {
    if (document.hidden) return;
    try {
        const { data } = await http.get(route('panel.notifications.latest'));
        if (data.unread > unread.value && items.value.length) items.value = data.items;
        unread.value = data.unread;
    } catch {
        /* abaikan */
    }
};

const openItem = (n) => router.post(route('panel.notifications.read', n.id));
const readAll = () => router.post(route('panel.notifications.read-all'), {}, { preserveScroll: true, onSuccess: () => load() });

onMounted(() => (timer = setInterval(poll, 60000)));
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <Dropdown width="w-[22rem] max-w-[calc(100vw-2rem)]">
        <template #trigger>
            <button type="button" class="btn btn-ghost btn-icon relative" aria-label="Notifikasi" @click="load">
                <Bell :class="unread ? 'animate-wiggle' : ''" class="h-5 w-5" />
                <span v-if="unread" class="absolute top-1 right-1 grid h-5 min-w-5 place-items-center rounded-full border-2 border-card bg-rose-500 px-1 text-[10px] font-extrabold text-white">{{ badgeText }}</span>
            </button>
        </template>
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="font-bold">Notifikasi</p>
            <button v-if="unread" type="button" class="inline-flex items-center gap-1 text-xs font-bold text-violet-600 hover:underline dark:text-violet-300" @click.stop="readAll"><CheckCheck class="h-3.5 w-3.5" /> Tandai dibaca</button>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <p v-if="loading && !items.length" class="p-6 text-center text-sm text-muted">Memuat…</p>
            <p v-else-if="!items.length" class="p-6 text-center text-sm text-muted">Belum ada notifikasi 🎈</p>
            <button
                v-for="n in items"
                :key="n.id"
                type="button"
                :class="n.read_at ? 'opacity-70' : 'bg-violet-50/60 dark:bg-violet-500/5'"
                class="flex w-full gap-3 border-b border-line/60 px-4 py-3 text-left transition hover:bg-surface-2"
                @click="openItem(n)"
            >
                <span :class="solid[levelColor[n.level] || 'sky']" class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-bold">{{ n.title }}</span>
                    <span v-if="n.body" class="line-clamp-2 block text-xs text-muted">{{ n.body }}</span>
                    <span class="mt-1 block text-[11px] font-semibold text-muted/80">{{ relative(n.created_at) }}</span>
                </span>
            </button>
        </div>
        <Link :href="route('panel.notifications.index')" class="block bg-surface-2/60 px-4 py-2.5 text-center text-xs font-bold hover:text-violet-600">Lihat semua notifikasi</Link>
    </Dropdown>
</template>
