<script setup>
import { BadgeCheck, CircleX, FileSearch, FileUp, FingerprintPattern, QrCode, ShieldAlert, ShieldCheck } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Blobs from '@/Components/site/Blobs.vue';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import { datetime, number } from '@/lib/format';

const props = defineProps({ meta: Object, document: Object, searched: String, fileCheck: Object });

const search = useForm({ code: props.searched || '' });
const upload = useForm({ file: null, code: props.document?.code || '' });

const doSearch = () => search.post(route('verify.lookup'));
const doUpload = () => upload.post(route('verify.file'), { forceFormData: true, preserveScroll: true, onSuccess: () => upload.reset('file') });
</script>

<template>
    <Head :title="meta.title" />
    <section class="relative isolate overflow-hidden pt-32 pb-24 sm:pt-40">
        <Blobs variant="soft" />
        <div class="container-x max-w-4xl">
            <div class="text-center">
                <span v-reveal="'zoom'" class="eyebrow"><QrCode class="h-3.5 w-3.5" /> Verifikasi TTE</span>
                <h1 v-reveal="{ type: 'up', delay: 80 }" class="mt-5 font-display text-4xl font-bold sm:text-5xl">Cek keaslian <span class="text-gradient">dokumen</span></h1>
                <p v-reveal="{ type: 'up', delay: 140 }" class="mx-auto mt-3 max-w-xl text-muted">Pindai QR pada dokumen atau masukkan kode TTE untuk memastikan dokumen benar diterbitkan & ditandatangani oleh {{ $page.props.site.name }}.</p>
            </div>

            <form v-reveal="{ type: 'up', delay: 200 }" class="mx-auto mt-8 flex max-w-xl gap-2" @submit.prevent="doSearch">
                <input v-model="search.code" type="text" class="input font-mono uppercase" placeholder="TTE-20261015-XXXXXXXX" maxlength="40" aria-label="Kode dokumen" />
                <button type="submit" class="btn btn-primary shrink-0" :disabled="search.processing"><FileSearch class="h-4 w-4" /> Cek</button>
            </form>
            <p v-if="search.errors.code" class="field-error text-center">{{ search.errors.code }}</p>

            <!-- Hasil -->
            <div v-if="searched" class="mt-10">
                <div v-if="document" v-reveal="'zoom'" :class="document.status === 'approved' ? 'border-emerald-500' : 'border-rose-500'" class="overflow-hidden rounded-[2rem] border-2 bg-card shadow-brutal">
                    <div :class="document.status === 'approved' ? 'bg-emerald-500' : 'bg-rose-500'" class="flex items-center gap-4 p-6 text-white">
                        <span class="animate-pop-in grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white/20">
                            <ShieldCheck v-if="document.status === 'approved'" class="h-8 w-8" />
                            <ShieldAlert v-else class="h-8 w-8" />
                        </span>
                        <div>
                            <p class="font-display text-2xl font-bold">{{ document.status === 'approved' ? 'Dokumen SAH & Terverifikasi' : 'Tanda Tangan DICABUT' }}</p>
                            <p class="text-sm text-white/85">{{ document.status === 'approved' ? 'Ditandatangani secara elektronik oleh ' + document.company : 'Dokumen ini tidak lagi berlaku sejak ' + datetime(document.revoked_at) }}</p>
                        </div>
                    </div>
                    <dl class="grid gap-x-8 gap-y-5 p-6 text-sm sm:grid-cols-2 sm:p-8">
                        <div class="sm:col-span-2"><dt class="text-muted">Perihal dokumen</dt><dd class="font-display text-lg font-bold">{{ document.title }}</dd></div>
                        <div><dt class="text-muted">Nomor dokumen</dt><dd class="font-bold">{{ document.document_number || '—' }}</dd></div>
                        <div><dt class="text-muted">Kode TTE</dt><dd class="font-mono font-bold">{{ document.code }}</dd></div>
                        <div><dt class="text-muted">Penandatangan</dt><dd class="font-bold">{{ document.signer_name }}</dd><dd class="text-xs text-muted">{{ document.signer_position }}</dd></div>
                        <div><dt class="text-muted">Waktu tanda tangan</dt><dd class="font-bold">{{ datetime(document.signed_at) }} WIB</dd></div>
                        <div><dt class="text-muted">Diajukan oleh</dt><dd class="font-bold">{{ document.requested_by || '—' }}</dd></div>
                        <div><dt class="text-muted">Jumlah halaman</dt><dd class="font-bold">{{ document.page_count }} halaman</dd></div>
                        <div v-if="document.revoke_reason" class="sm:col-span-2"><dt class="text-muted">Alasan pencabutan</dt><dd class="font-bold text-rose-600">{{ document.revoke_reason }}</dd></div>
                        <div class="sm:col-span-2"><dt class="flex items-center gap-1 text-muted"><FingerprintPattern class="h-4 w-4" /> Sidik jari berkas (SHA-256)</dt><dd class="mt-1 rounded-xl bg-surface-2 p-3 font-mono text-xs break-all">{{ document.hash }}</dd></div>
                    </dl>
                    <p class="border-t border-line px-8 py-4 text-xs text-muted">Telah diverifikasi {{ number(document.verify_count) }} kali.</p>
                </div>
                <div v-else v-reveal="'zoom'" class="card-brutal flex flex-col items-center p-10 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15"><CircleX class="h-8 w-8" /></span>
                    <h2 class="mt-4 font-display text-2xl font-bold">Dokumen tidak ditemukan</h2>
                    <p class="mt-2 max-w-md text-sm text-muted">Kode <b class="font-mono">{{ searched }}</b> tidak terdaftar sebagai dokumen sah. Waspadai dokumen palsu & hubungi kami untuk konfirmasi.</p>
                </div>
            </div>

            <!-- Cek berkas -->
            <div v-reveal="'up'" class="card mt-10 p-6 sm:p-8">
                <h2 class="flex items-center gap-2 font-display text-xl font-bold"><FileUp class="h-5 w-5 text-violet-500" /> Cek keutuhan berkas PDF</h2>
                <p class="mt-1 text-sm text-muted">Unggah PDF yang kamu terima. Sistem mencocokkan sidik jari digital (SHA-256) untuk memastikan berkas tidak diubah sedikit pun. Berkas tidak disimpan.</p>
                <form class="mt-5 space-y-4" @submit.prevent="doUpload">
                    <FileDrop v-model="upload.file" accept=".pdf,application/pdf" :max-mb="20" hint="PDF bertanda tangan elektronik, maks. 20 MB" :error="upload.errors.file" />
                    <FormField v-if="!document" label="Kode TTE (opsional)"><input v-model="upload.code" type="text" class="input font-mono uppercase" maxlength="40" /></FormField>
                    <button type="submit" class="btn btn-brutal" :disabled="!upload.file || upload.processing"><FingerprintPattern class="h-4 w-4" /> {{ upload.processing ? 'Memeriksa…' : 'Periksa berkas' }}</button>
                </form>
                <div v-if="fileCheck" class="animate-pop-in mt-6 flex gap-4 rounded-2xl border-2 p-5" :class="fileCheck.valid ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10' : 'border-rose-500 bg-rose-50 dark:bg-rose-500/10'">
                    <BadgeCheck v-if="fileCheck.valid" class="h-8 w-8 shrink-0 text-emerald-600" />
                    <ShieldAlert v-else class="h-8 w-8 shrink-0 text-rose-600" />
                    <div class="min-w-0">
                        <p class="font-bold">{{ fileCheck.valid ? 'Berkas ASLI — identik dengan dokumen yang ditandatangani.' : fileCheck.match ? 'Berkas cocok, namun tanda tangannya telah DICABUT.' : 'Berkas TIDAK cocok dengan dokumen sah mana pun (mungkin telah diubah).' }}</p>
                        <p class="mt-1 truncate text-xs text-muted">{{ fileCheck.name }}</p>
                        <p class="mt-1 font-mono text-[11px] break-all text-muted">{{ fileCheck.hash }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
