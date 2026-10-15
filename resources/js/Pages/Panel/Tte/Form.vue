<script setup>
import { ArrowLeft, Info, MousePointerClick, Send } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import PdfPlacer from '@/Components/panel/PdfPlacer.vue';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Switch from '@/Components/ui/Switch.vue';

const props = defineProps({ config: Object, item: Object, originalUrl: String });
const editing = computed(() => !!props.item);

const form = useForm({
    title: props.item?.title || '',
    document_number: props.item?.document_number || '',
    purpose: props.item?.purpose || '',
    file: null,
    include_stamp: props.item?.include_stamp || false,
    placements: props.item?.placements ? JSON.parse(JSON.stringify(props.item.placements)) : [],
});
const pageCount = ref(props.item?.page_count || 0);
const pdfUrl = computed(() => (form.file ? '' : props.originalUrl || ''));

const onLoaded = ({ pages }) => {
    pageCount.value = pages;
    form.placements = form.placements.filter((p) => p.page <= pages);
    if (!form.placements.length) form.placements = [{ page: pages, x: 0.62, y: 0.7, w: 0.16 }];
};

const submit = () => {
    form.transform((d) => ({ ...d, placements: JSON.stringify(d.placements), file: d.file || undefined })).post(editing.value ? route('panel.tte.update', props.item.id) : route('panel.tte.store'), { forceFormData: true });
};
</script>

<template>
    <Head :title="editing ? 'Revisi Pengajuan TTE' : 'Ajukan TTE'" />
    <Link :href="editing ? route('panel.tte.show', item.id) : route('panel.tte.index')" class="btn btn-ghost btn-sm mb-4"><ArrowLeft class="h-4 w-4" /> Kembali</Link>
    <PageHeader :title="editing ? `Revisi ${item.code}` : 'Ajukan tanda tangan elektronik'" subtitle="Unggah PDF, isi keterangan, lalu atur posisi QR pada halaman dokumen." />

    <div v-if="editing && item.note && item.status === 'rejected'" class="mb-6 rounded-2xl border-2 border-rose-300 bg-rose-50 p-4 text-sm dark:border-rose-500/40 dark:bg-rose-500/10">
        <b>Catatan revisi dari admin:</b> {{ item.note }}
    </div>

    <form class="grid gap-6 xl:grid-cols-[380px_1fr]" @submit.prevent="submit">
        <div class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            <div class="card p-6">
                <div class="space-y-4">
                    <FormField :label="editing ? 'Ganti berkas PDF (opsional)' : 'Berkas PDF'" :error="form.errors.file" :required="!editing">
                        <FileDrop v-model="form.file" accept=".pdf,application/pdf" :max-mb="config.max_mb" :hint="`PDF, maks. ${config.max_mb} MB`" />
                    </FormField>
                    <FormField label="Perihal / judul dokumen" :error="form.errors.title" required><input v-model="form.title" type="text" class="input" maxlength="200" /></FormField>
                    <FormField label="Nomor dokumen" :error="form.errors.document_number"><input v-model="form.document_number" type="text" class="input" placeholder="mis. 001/KSA/X/2026" maxlength="120" /></FormField>
                    <FormField label="Keperluan" :error="form.errors.purpose"><textarea v-model="form.purpose" rows="3" class="input" maxlength="1000"></textarea></FormField>
                    <Switch v-if="config.stamp_available" v-model="form.include_stamp" label="Sertakan stempel perusahaan" description="Ditempel di sisi kiri QR." />
                </div>
            </div>
            <div class="card p-5 text-sm">
                <p class="flex items-center gap-2 font-bold"><Info class="h-4 w-4 text-violet-500" /> Cara mengatur posisi</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-muted">
                    <li>Seret kotak QR ke posisi tanda tangan.</li>
                    <li>Tarik bulatan pink di pojok untuk mengubah ukuran.</li>
                    <li>Klik dua kali / tombol "Tambah QR" untuk QR tambahan (mis. paraf tiap halaman).</li>
                    <li>Gunakan tombol panah keyboard untuk geser presisi.</li>
                </ul>
                <p v-if="form.errors.placements" class="field-error">{{ form.errors.placements }}</p>
                <p class="mt-3 font-semibold">{{ form.placements.length }} posisi QR · {{ pageCount }} halaman</p>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="form.processing || (!form.file && !editing) || !form.placements.length">
                <Send class="h-5 w-5" /> {{ form.processing ? 'Mengirim…' : editing ? 'Kirim ulang revisi' : 'Kirim pengajuan' }}
            </button>
        </div>

        <div class="card min-h-[400px] p-4 sm:p-6">
            <PdfPlacer
                v-if="form.file || pdfUrl"
                v-model="form.placements"
                :file="form.file"
                :url="pdfUrl"
                :caption-ratio="config.caption_ratio"
                :caption-enabled="config.caption_enabled"
                :show-stamp="form.include_stamp"
                @loaded="onLoaded"
            />
            <div v-else class="grid h-full min-h-[360px] place-items-center text-center text-muted">
                <div>
                    <MousePointerClick class="animate-bounce-soft mx-auto h-12 w-12 text-violet-400" />
                    <p class="mt-3 font-bold text-ink">Pratinjau dokumen tampil di sini</p>
                    <p class="text-sm">Unggah PDF terlebih dahulu untuk menempatkan QR TTE.</p>
                </div>
            </div>
        </div>
    </form>
</template>
