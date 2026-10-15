<script setup>
import { Inbox, Mail, MailOpen, MessageCircle, Reply, Trash } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import Switch from '@/Components/ui/Switch.vue';
import { confirm } from '@/composables/useConfirm';
import { useFilters } from '@/composables/useFilters';
import { datetime, relative, waLink } from '@/lib/format';

const props = defineProps({ messages: Object, filters: Object, unread: Number });
const f = useFilters(props.filters, route('admin.messages.index'));
const openId = ref(null);

const open = (m) => {
    openId.value = openId.value === m.id ? null : m.id;
    if (!m.is_read && openId.value === m.id) router.patch(route('admin.messages.read', m.id), {}, { preserveScroll: true, preserveState: true });
};
const toggleRead = (m) => router.patch(route('admin.messages.read', m.id), {}, { preserveScroll: true });
const destroy = async (m) => {
    if (await confirm({ title: 'Hapus pesan?', message: `Dari ${m.name}`, danger: true })) router.delete(route('admin.messages.destroy', m.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Pesan Masuk" />
    <PageHeader title="Pesan Masuk" :subtitle="`${unread} pesan belum dibaca dari formulir kontak website.`" :icon="Inbox" />
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <SearchInput v-model="f.q" placeholder="Cari nama, email, subjek…" />
        <Switch v-model="f.unread" label="Hanya belum dibaca" />
    </div>
    <div v-if="messages.data.length" class="space-y-3">
        <div v-for="m in messages.data" :key="m.id" :class="m.is_read ? '' : 'border-violet-300 dark:border-violet-500/40'" class="card overflow-hidden transition">
            <button type="button" class="flex w-full items-center gap-4 p-4 text-left" @click="open(m)">
                <Avatar :name="m.name" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-2 truncate"><span :class="m.is_read ? 'font-semibold' : 'font-extrabold'">{{ m.name }}</span><span v-if="!m.is_read" class="h-2 w-2 shrink-0 rounded-full bg-fuchsia-500"></span></p>
                    <p class="truncate text-sm text-muted">{{ m.subject || m.message }}</p>
                </div>
                <span class="shrink-0 text-xs text-muted">{{ relative(m.created_at) }}</span>
            </button>
            <div v-if="openId === m.id" class="animate-fade-up border-t border-line bg-surface-2/50 p-5">
                <p class="text-xs text-muted">{{ m.email }}<span v-if="m.phone"> · {{ m.phone }}</span> · {{ datetime(m.created_at) }}</p>
                <p v-if="m.subject" class="mt-3 font-bold">{{ m.subject }}</p>
                <p class="mt-2 text-sm leading-relaxed whitespace-pre-line">{{ m.message }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a :href="`mailto:${m.email}?subject=${encodeURIComponent('Re: ' + (m.subject || 'Pesan Anda'))}`" class="btn btn-primary btn-sm"><Reply class="h-4 w-4" /> Balas email</a>
                    <a v-if="m.phone" :href="waLink(m.phone, `Halo ${m.name}, terima kasih sudah menghubungi kami.`)" target="_blank" rel="noopener" class="btn btn-sm bg-[#25D366] text-white"><MessageCircle class="h-4 w-4" /> WhatsApp</a>
                    <button type="button" class="btn btn-secondary btn-sm" @click="toggleRead(m)"><component :is="m.is_read ? Mail : MailOpen" class="h-4 w-4" /> Tandai {{ m.is_read ? 'belum dibaca' : 'dibaca' }}</button>
                    <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click="destroy(m)"><Trash class="h-4 w-4" /> Hapus</button>
                </div>
            </div>
        </div>
    </div>
    <EmptyState v-else title="Kotak masuk kosong" description="Pesan dari halaman Kontak akan muncul di sini." />
    <div class="mt-5"><Pagination :paginator="messages" /></div>
</template>
