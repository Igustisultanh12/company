<script setup>
import { BadgeCheck, CircleCheck, FileCheck, KeyRound, QrCode, Save, ScanLine, Stamp, Trash } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FileDrop from '@/Components/ui/FileDrop.vue';
import FormField from '@/Components/ui/FormField.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import Switch from '@/Components/ui/Switch.vue';

const props = defineProps({ settings: Object, stampUrl: String, hasPin: Boolean, stats: Object });

const form = useForm({ ...props.settings, stamp: null, remove_stamp: false });
const save = () => form.post(route('admin.tte.settings.update'), { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset('stamp') });

const pin = useForm({ password: '', pin: '', pin_confirmation: '' });
const savePin = () => pin.put(route('admin.tte.settings.pin'), { preserveScroll: true, onSuccess: () => pin.reset() });
</script>

<template>
    <Head title="Pengaturan TTE" />
    <PageHeader title="Pengaturan Tanda Tangan Elektronik" subtitle="Identitas penandatangan, tampilan QR, stempel perusahaan & PIN pengesahan." :icon="FileCheck">
        <Link :href="route('panel.tte.index')" class="btn btn-secondary">Daftar pengajuan</Link>
    </PageHeader>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <StatCard label="Dokumen sah" :value="stats.approved" :icon="BadgeCheck" color="emerald" />
        <StatCard label="Menunggu" :value="stats.pending" :icon="FileCheck" color="amber" />
        <StatCard label="Dicabut" :value="stats.revoked" :icon="Trash" color="rose" />
        <StatCard label="Total verifikasi" :value="stats.verifications" :icon="ScanLine" color="violet" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <form class="card p-6 xl:col-span-2" @submit.prevent="save">
            <h2 class="font-display text-lg font-bold">Identitas & tampilan</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <FormField label="Nama penandatangan" :error="form.errors.tte_signer_name" required><input v-model="form.tte_signer_name" class="input" /></FormField>
                <FormField label="Jabatan" :error="form.errors.tte_signer_position" required><input v-model="form.tte_signer_position" class="input" /></FormField>
                <FormField label="Teks keterangan" :error="form.errors.tte_caption_text"><input v-model="form.tte_caption_text" class="input" maxlength="80" /></FormField>
                <FormField label="Warna QR" :error="form.errors.tte_qr_color">
                    <div class="flex gap-2"><input v-model="form.tte_qr_color" type="color" class="h-11 w-14 cursor-pointer rounded-xl border-2 border-line" /><input v-model="form.tte_qr_color" class="input font-mono" /></div>
                </FormField>
                <div class="sm:col-span-2"><Switch v-model="form.tte_caption_enabled" label="Tampilkan keterangan di bawah QR" description="Nama, jabatan, waktu & kode dokumen." /></div>
                <FormField class="sm:col-span-2" label="Stempel / cap perusahaan (PNG transparan, opsional)" :error="form.errors.stamp">
                    <FileDrop v-model="form.stamp" accept=".png,image/png" :max-mb="2" image :current-url="form.remove_stamp ? '' : stampUrl || ''" hint="PNG dengan latar transparan, maks. 2 MB" @remove-current="form.remove_stamp = true" />
                </FormField>
            </div>
            <button type="submit" class="btn btn-primary mt-6" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan pengaturan</button>
        </form>

        <div class="space-y-6">
            <div class="card-brutal p-6">
                <p class="text-xs font-bold text-muted uppercase">Pratinjau tampilan</p>
                <div class="mt-4 flex items-start justify-center gap-2">
                    <div v-if="stampUrl && !form.remove_stamp" class="mt-2 grid h-20 w-20 place-items-center rounded-full border-4 border-dashed border-violet-400/60 text-violet-500"><Stamp class="h-8 w-8" /></div>
                    <div class="w-32 text-center">
                        <div class="grid aspect-square place-items-center rounded-lg border bg-white p-2"><QrCode class="h-full w-full" :style="{ color: form.tte_qr_color }" /></div>
                        <div v-if="form.tte_caption_enabled" class="mt-1 space-y-0.5 leading-tight">
                            <p class="text-[8px] text-slate-500">{{ form.tte_caption_text }}</p>
                            <p class="text-[10px] font-bold">{{ form.tte_signer_name }}</p>
                            <p class="text-[8px] text-slate-600">{{ form.tte_signer_position }}</p>
                            <p class="text-[7px] text-violet-600">15/10/2026 10:00 WIB | TTE-…</p>
                        </div>
                    </div>
                </div>
            </div>

            <form class="card p-6" @submit.prevent="savePin">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><KeyRound class="h-5 w-5 text-violet-500" /> PIN TTE</h2>
                <p class="mt-1 text-sm text-muted">PIN 6 digit wajib dimasukkan setiap kali mengesahkan / mencabut dokumen (seperti passphrase sertifikat elektronik).</p>
                <p v-if="hasPin" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-emerald-600"><CircleCheck class="h-4 w-4" /> PIN sudah diatur</p>
                <div class="mt-4 space-y-3">
                    <FormField label="Password akun" :error="pin.errors.password"><input v-model="pin.password" type="password" class="input" autocomplete="current-password" /></FormField>
                    <FormField :label="hasPin ? 'PIN baru' : 'PIN'" :error="pin.errors.pin"><input v-model="pin.pin" type="password" inputmode="numeric" maxlength="6" class="input font-mono tracking-[0.5em]" autocomplete="new-password" /></FormField>
                    <FormField label="Ulangi PIN"><input v-model="pin.pin_confirmation" type="password" inputmode="numeric" maxlength="6" class="input font-mono tracking-[0.5em]" autocomplete="new-password" /></FormField>
                </div>
                <button type="submit" class="btn btn-dark mt-5 w-full" :disabled="pin.processing">{{ hasPin ? 'Ganti PIN' : 'Simpan PIN' }}</button>
            </form>
        </div>
    </div>
</template>
