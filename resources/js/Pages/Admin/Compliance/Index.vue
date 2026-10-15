<script setup>
import { Archive, CalendarClock, CircleAlert, CircleCheck, FileText, Info, Scale } from '@lucide/vue';
import LegalSection from '@/Components/site/LegalSection.vue';
import Badge from '@/Components/ui/Badge.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import { bytes, date } from '@/lib/format';

defineProps({ legal: Object, items: Array, legalDocuments: Array });
const statusMeta = {
    ok: { label: 'Terpenuhi', color: 'emerald', icon: CircleCheck },
    due_soon: { label: 'Segera jatuh tempo', color: 'amber', icon: CalendarClock },
    overdue: { label: 'Terlambat', color: 'rose', icon: CircleAlert },
    missing: { label: 'Belum ada dokumen', color: 'rose', icon: CircleAlert },
};
</script>

<template>
    <Head title="Legalitas & Kepatuhan" />
    <PageHeader title="Legalitas & Kepatuhan" subtitle="Ringkasan perizinan perusahaan dan kewajiban dari Sertifikat Standar OSS & SPPL." :icon="Scale">
        <Link :href="route('panel.documents.index')" class="btn btn-secondary"><Archive class="h-4 w-4" /> Arsip dokumen</Link>
    </PageHeader>

    <LegalSection :legal="legal" />

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <h2 class="font-display text-lg font-bold">Checklist kewajiban perizinan</h2>
            <p class="mt-1 flex items-start gap-2 text-sm text-muted"><Info class="mt-0.5 h-4 w-4 shrink-0" /> Unggah dokumen bukti di Arsip Dokumen lalu pilih "Kewajiban terkait" agar status berubah menjadi terpenuhi.</p>
            <ul class="mt-6 space-y-3">
                <li v-for="it in items" :key="it.key" class="flex flex-col gap-3 rounded-2xl border border-line p-4 sm:flex-row sm:items-center">
                    <component :is="statusMeta[it.status].icon" :class="it.status === 'ok' ? 'text-emerald-500' : it.status === 'due_soon' ? 'text-amber-500' : 'text-rose-500'" class="h-6 w-6 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="font-bold">{{ it.title }}</p>
                        <p class="text-xs text-muted">{{ it.source }}<span v-if="it.period_months"> · berulang tiap {{ it.period_months }} bulan</span></p>
                        <p v-if="it.documents.length" class="mt-1 text-xs">Bukti: <span v-for="(d, i) in it.documents" :key="d.id">{{ i ? ', ' : '' }}{{ d.title }} ({{ d.code }})</span></p>
                    </div>
                    <div class="text-right">
                        <Badge :color="statusMeta[it.status].color">{{ statusMeta[it.status].label }}</Badge>
                        <p v-if="it.next_due" class="mt-1 text-xs text-muted">Berikutnya: {{ date(it.next_due) }}</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold">Dokumen legalitas</h2>
            <p class="mt-1 text-sm text-muted">Dari kategori "Legalitas Perusahaan".</p>
            <ul v-if="legalDocuments.length" class="mt-4 space-y-2">
                <li v-for="d in legalDocuments" :key="d.id">
                    <a :href="route('panel.documents.file', [d.id, 'view'])" target="_blank" class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-surface-2">
                        <FileText class="h-5 w-5 shrink-0 text-violet-500" />
                        <span class="min-w-0"><span class="block truncate text-sm font-bold">{{ d.title }}</span><span class="text-xs text-muted">{{ d.document_number || d.code }} · {{ bytes(d.size) }}</span></span>
                    </a>
                </li>
            </ul>
            <p v-else class="mt-4 rounded-2xl bg-amber-50 p-4 text-sm dark:bg-amber-500/10">Belum ada. Unggah SK AHU, NIB / Sertifikat Standar & SPPL ke Arsip Dokumen (kategori Legalitas, tingkat akses Rahasia).</p>
        </div>
    </div>
</template>
