<script setup>
import {
    ArrowLeft, BadgeCheck, Ban, Download, ExternalLink, FileText, KeyRound, Pencil, QrCode, RotateCcw, ShieldCheck, Trash, X,
} from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import PdfPlacer from '@/Components/panel/PdfPlacer.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import { confirm } from '@/composables/useConfirm';
import { datetime } from '@/lib/format';

const props = defineProps({ item: Object, urls: Object, can: Object, hasPin: Boolean, config: Object });
const statuses = { pending: { label: 'Menunggu Pengesahan', color: 'amber' }, approved: { label: 'Sah / Ditandatangani', color: 'emerald' }, rejected: { label: 'Ditolak / Revisi', color: 'rose' }, revoked: { label: 'Dicabut', color: 'slate' } };

const view = ref(props.item.status === 'approved' ? 'signed' : props.can.approve ? 'place' : 'preview');
const tabs = computed(() =>
    [
        props.item.signed_path || props.urls.signed ? { key: 'signed', label: 'Dokumen sah' } : null,
        props.can.approve ? { key: 'place', label: 'Atur posisi QR' } : null,
        { key: 'preview', label: 'Pratinjau hasil' },
        { key: 'original', label: 'Berkas asli' },
    ].filter(Boolean),
);
const placements = ref(JSON.parse(JSON.stringify(props.item.placements || [])));
const previewKey = ref(0);

const showApprove = ref(false);
const approve = useForm({ pin: '', note: '', placements: '' });
const doApprove = () =>
    approve.transform((d) => ({ ...d, placements: JSON.stringify(placements.value) })).post(route('admin.tte.approve', props.item.id), {
        preserveScroll: true,
        onSuccess: () => {
            showApprove.value = false;
            view.value = 'signed';
        },
        onFinish: () => approve.reset('pin'),
    });

const reject = async () => {
    const note = await confirm({ title: 'Kembalikan untuk revisi?', danger: true, confirmText: 'Kirim catatan', input: { label: 'Catatan perbaikan untuk pengaju', required: true, textarea: true } });
    if (note) router.post(route('admin.tte.reject', props.item.id), { note }, { preserveScroll: true });
};

const showRevoke = ref(false);
const revoke = useForm({ pin: '', reason: '' });
const doRevoke = () => revoke.post(route('admin.tte.revoke', props.item.id), { preserveScroll: true, onSuccess: () => (showRevoke.value = false), onFinish: () => revoke.reset('pin') });

const destroy = async () => {
    if (await confirm({ title: 'Hapus pengajuan?', message: props.item.title, danger: true })) router.delete(route('panel.tte.destroy', props.item.id));
};
</script>

<template>
    <Head :title="`TTE ${item.code}`" />
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <Link :href="route('panel.tte.index')" class="btn btn-ghost btn-sm"><ArrowLeft class="h-4 w-4" /> Daftar TTE</Link>
        <div class="flex flex-wrap gap-2">
            <a v-if="urls.signed && item.status === 'approved'" :href="`${urls.signed}?download=1`" class="btn btn-success btn-sm"><Download class="h-4 w-4" /> Unduh PDF sah</a>
            <a v-if="item.status !== 'pending' && item.status !== 'rejected'" :href="urls.verify" target="_blank" class="btn btn-secondary btn-sm"><ExternalLink class="h-4 w-4" /> Halaman verifikasi</a>
            <Link v-if="can.revise" :href="route('panel.tte.edit', item.id)" class="btn btn-secondary btn-sm"><Pencil class="h-4 w-4" /> Revisi</Link>
            <button v-if="can.delete" type="button" class="btn btn-ghost btn-sm text-rose-500" @click="destroy"><Trash class="h-4 w-4" /></button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <div class="space-y-6">
            <div class="card-brutal p-6">
                <StatusBadge :value="item.status" :map="statuses" />
                <h1 class="mt-3 font-display text-xl font-bold">{{ item.title }}</h1>
                <p class="mt-1 font-mono text-xs text-muted">{{ item.code }}</p>
                <dl class="mt-5 space-y-3 text-sm">
                    <div v-if="item.document_number"><dt class="text-muted">Nomor dokumen</dt><dd class="font-bold">{{ item.document_number }}</dd></div>
                    <div><dt class="text-muted">Pengaju</dt><dd class="mt-1 flex items-center gap-2 font-bold"><Avatar :user="item.user" size="xs" /> {{ item.user?.name }}</dd></div>
                    <div><dt class="text-muted">Diajukan</dt><dd class="font-bold">{{ datetime(item.created_at) }}</dd></div>
                    <div><dt class="text-muted">Berkas</dt><dd class="flex items-center gap-1.5 font-bold"><FileText class="h-4 w-4" /> {{ item.original_name }} · {{ item.page_count }} hlm</dd></div>
                    <div v-if="item.purpose"><dt class="text-muted">Keperluan</dt><dd>{{ item.purpose }}</dd></div>
                    <div v-if="item.signed_at"><dt class="text-muted">Disahkan</dt><dd class="font-bold">{{ datetime(item.signed_at) }}</dd><dd class="text-xs text-muted">{{ item.signer_name }} — {{ item.signer_position }}</dd></div>
                    <div v-if="item.signed_sha256"><dt class="text-muted">SHA-256</dt><dd class="font-mono text-[11px] break-all">{{ item.signed_sha256 }}</dd></div>
                    <div v-if="item.note"><dt class="text-muted">Catatan</dt><dd :class="item.status === 'rejected' ? 'text-rose-600' : ''" class="font-semibold">{{ item.note }}</dd></div>
                    <div v-if="item.revoke_reason"><dt class="text-muted">Alasan dicabut</dt><dd class="font-semibold text-rose-600">{{ item.revoke_reason }}</dd></div>
                </dl>
            </div>

            <div v-if="can.approve" class="card p-6">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><ShieldCheck class="h-5 w-5 text-emerald-500" /> Pengesahan</h2>
                <p class="mt-1 text-sm text-muted">Periksa isi & posisi QR. Penandatangan: <b class="text-ink">{{ config.signer_name }}</b>.</p>
                <p v-if="!hasPin" class="mt-3 rounded-xl bg-amber-50 p-3 text-xs font-semibold dark:bg-amber-500/10">⚠️ Atur PIN TTE dulu di <Link :href="route('admin.tte.settings')" class="underline">Pengaturan TTE</Link>.</p>
                <div class="mt-4 grid gap-2">
                    <button type="button" class="btn btn-success" :disabled="!hasPin" @click="showApprove = true"><BadgeCheck class="h-4 w-4" /> Sahkan & tanda tangani</button>
                    <button type="button" class="btn btn-secondary text-rose-500" @click="reject"><X class="h-4 w-4" /> Kembalikan untuk revisi</button>
                </div>
            </div>
            <div v-if="can.revoke" class="card p-6">
                <h2 class="font-display text-lg font-bold">Cabut TTE</h2>
                <p class="mt-1 text-sm text-muted">Gunakan bila dokumen salah / tidak berlaku lagi. Halaman verifikasi akan menampilkan status DICABUT.</p>
                <button type="button" class="btn btn-secondary mt-4 w-full text-rose-500" @click="showRevoke = true"><Ban class="h-4 w-4" /> Cabut tanda tangan</button>
            </div>
            <div v-if="item.status === 'approved'" class="card flex items-center gap-4 p-5">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600 dark:bg-violet-500/15"><QrCode class="h-6 w-6" /></span>
                <p class="text-sm">QR pada dokumen mengarah ke halaman verifikasi publik. Telah diverifikasi <b>{{ item.verify_count }}x</b>.</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-4">
                <Tabs v-model="view" :tabs="tabs" />
                <button v-if="view === 'preview'" type="button" class="btn btn-ghost btn-sm" @click="previewKey++"><RotateCcw class="h-4 w-4" /> Muat ulang</button>
            </div>
            <div class="bg-surface-2/50 p-4">
                <div v-if="view === 'place'">
                    <p class="mb-4 rounded-xl bg-violet-50 p-3 text-sm dark:bg-violet-500/10">Sesuaikan posisi QR bila perlu — posisi ini dipakai saat pengesahan.</p>
                    <PdfPlacer v-model="placements" :url="urls.original" :caption-ratio="config.caption_ratio" :caption-enabled="config.caption_enabled" :show-stamp="item.include_stamp" />
                </div>
                <iframe v-else-if="view === 'preview'" :key="previewKey" :src="urls.preview" class="h-[78vh] w-full rounded-xl border border-line bg-white" title="Pratinjau dokumen TTE"></iframe>
                <iframe v-else-if="view === 'signed' && urls.signed" :src="urls.signed" class="h-[78vh] w-full rounded-xl border border-line bg-white" title="Dokumen bertanda tangan"></iframe>
                <iframe v-else :src="urls.original" class="h-[78vh] w-full rounded-xl border border-line bg-white" title="Dokumen asli"></iframe>
            </div>
        </div>
    </div>

    <Modal :show="showApprove" title="Sahkan dokumen" subtitle="Masukkan PIN TTE untuk menandatangani secara elektronik." size="sm" @close="showApprove = false">
        <div class="space-y-4">
            <FormField label="PIN TTE (6 digit)" :error="approve.errors.pin" required>
                <div class="relative"><KeyRound class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted" /><input v-model="approve.pin" type="password" inputmode="numeric" maxlength="6" class="input pl-11 font-mono tracking-[0.5em]" autocomplete="off" /></div>
            </FormField>
            <FormField label="Catatan (opsional)" :error="approve.errors.note"><input v-model="approve.note" type="text" class="input" maxlength="500" /></FormField>
            <p class="text-xs text-muted">{{ placements.length }} QR akan ditempel. Tindakan ini tercatat di log audit.</p>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showApprove = false">Batal</button>
            <button type="button" class="btn btn-success" :disabled="approve.processing || approve.pin.length < 6" @click="doApprove">{{ approve.processing ? 'Menandatangani…' : 'Sahkan sekarang' }}</button>
        </template>
    </Modal>

    <Modal :show="showRevoke" title="Cabut tanda tangan" size="sm" @close="showRevoke = false">
        <div class="space-y-4">
            <FormField label="Alasan pencabutan" :error="revoke.errors.reason" required><textarea v-model="revoke.reason" rows="3" class="input" maxlength="255"></textarea></FormField>
            <FormField label="PIN TTE" :error="revoke.errors.pin" required><input v-model="revoke.pin" type="password" inputmode="numeric" maxlength="6" class="input font-mono tracking-[0.5em]" autocomplete="off" /></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showRevoke = false">Batal</button>
            <button type="button" class="btn btn-danger" :disabled="revoke.processing || !revoke.reason || revoke.pin.length < 6" @click="doRevoke">Cabut</button>
        </template>
    </Modal>
</template>
