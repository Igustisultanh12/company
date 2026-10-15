<script setup>
import { Building, CalendarCheck, Contact, Cpu, Plus, Receipt, RefreshCw, Save, Scale, Search, Settings, ShieldCheck, Trash } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/Components/ui/Badge.vue';
import FormField from '@/Components/ui/FormField.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Switch from '@/Components/ui/Switch.vue';

const props = defineProps({ values: Object, tab: String, system: Object });
const active = ref(['company', 'contact', 'legal', 'seo', 'attendance', 'invoice', 'security', 'system'].includes(props.tab) ? props.tab : 'company');

const tabs = [
    { key: 'company', label: 'Profil perusahaan', icon: Building },
    { key: 'contact', label: 'Kontak & sosial', icon: Contact },
    { key: 'legal', label: 'Legalitas', icon: Scale },
    { key: 'seo', label: 'SEO & statistik', icon: Search },
    { key: 'attendance', label: 'Absensi', icon: CalendarCheck },
    { key: 'invoice', label: 'Invoice', icon: Receipt },
    { key: 'security', label: 'Keamanan', icon: ShieldCheck },
    { key: 'system', label: 'Sistem', icon: Cpu },
];

const form = useForm({ group: 'company', ...JSON.parse(JSON.stringify(props.values)) });
const save = (group) => form.transform((d) => ({ ...d, group })).put(route('admin.settings.update'), { preserveScroll: true });
const addKbli = () => form.legal_kbli.push({ code: '', title: '' });
const clearCache = () => router.post(route('admin.settings.cache'), {}, { preserveScroll: true });
const err = (k) => form.errors[k];
</script>

<template>
    <Head title="Pengaturan" />
    <PageHeader title="Pengaturan" subtitle="Atur identitas perusahaan, kontak, legalitas, absensi, invoice & keamanan." :icon="Settings" />

    <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
        <nav class="card h-max p-2 lg:sticky lg:top-24">
            <button v-for="t in tabs" :key="t.key" type="button" :class="active === t.key ? 'bg-ink text-surface' : 'text-muted hover:bg-surface-2 hover:text-ink'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition" @click="active = t.key">
                <component :is="t.icon" class="h-4 w-4" /> {{ t.label }}
            </button>
        </nav>

        <div class="card p-6 sm:p-8">
            <form v-if="active === 'company'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('company')">
                <FormField label="Nama perusahaan" :error="err('company_name')" required><input v-model="form.company_name" class="input" /></FormField>
                <FormField label="Nama singkat (logo)" :error="err('company_short_name')" required><input v-model="form.company_short_name" class="input" /></FormField>
                <FormField label="Bentuk badan usaha" :error="err('company_legal_type')"><input v-model="form.company_legal_type" class="input" /></FormField>
                <FormField label="Tagline" :error="err('company_tagline')"><input v-model="form.company_tagline" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="Deskripsi perusahaan" :error="err('company_description')"><textarea v-model="form.company_description" rows="4" class="input"></textarea></FormField>
                <FormField class="sm:col-span-2" label="Visi" :error="err('company_vision')"><textarea v-model="form.company_vision" rows="2" class="input"></textarea></FormField>
                <FormField class="sm:col-span-2" label="Misi (satu per baris)" :error="err('company_mission')"><textarea v-model="form.company_mission" rows="4" class="input"></textarea></FormField>
                <FormField label="Nama pendiri / direktur" :error="err('founder_name')"><input v-model="form.founder_name" class="input" /></FormField>
                <FormField label="Jabatan" :error="err('founder_position')"><input v-model="form.founder_position" class="input" /></FormField>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'contact'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('contact')">
                <FormField class="sm:col-span-2" label="Alamat lengkap" :error="err('address')"><textarea v-model="form.address" rows="2" class="input"></textarea></FormField>
                <FormField label="Kota" :error="err('city')"><input v-model="form.city" class="input" /></FormField>
                <FormField label="Email" :error="err('email')"><input v-model="form.email" type="email" class="input" /></FormField>
                <FormField label="Telepon" :error="err('phone')"><input v-model="form.phone" class="input" /></FormField>
                <FormField label="WhatsApp" help="Format internasional, mis. 6281234567890. Mengaktifkan tombol chat WA." :error="err('whatsapp')"><input v-model="form.whatsapp" class="input" /></FormField>
                <FormField label="Jam kerja" :error="err('office_hours')"><input v-model="form.office_hours" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="URL embed Google Maps" help="Google Maps › Bagikan › Sematkan peta › salin URL src." :error="err('maps_embed_url')"><input v-model="form.maps_embed_url" class="input" /></FormField>
                <FormField v-for="s in ['instagram', 'tiktok', 'linkedin', 'github', 'youtube']" :key="s" :label="`URL ${s}`" :error="err(s)"><input v-model="form[s]" type="url" class="input" placeholder="https://" /></FormField>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'legal'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('legal')">
                <FormField label="Nomor SK Kemenkum (AHU)" :error="err('legal_sk_number')"><input v-model="form.legal_sk_number" class="input" /></FormField>
                <FormField label="Tanggal SK" :error="err('legal_sk_date')"><input v-model="form.legal_sk_date" type="date" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="Judul SK" :error="err('legal_sk_title')"><input v-model="form.legal_sk_title" class="input" /></FormField>
                <FormField label="NIB" :error="err('legal_nib')"><input v-model="form.legal_nib" class="input" /></FormField>
                <FormField label="Nomor Sertifikat Standar" :error="err('legal_certificate_number')"><input v-model="form.legal_certificate_number" class="input" /></FormField>
                <FormField label="Tanggal Sertifikat" :error="err('legal_certificate_date')"><input v-model="form.legal_certificate_date" type="date" class="input" /></FormField>
                <FormField label="Tingkat risiko" :error="err('legal_risk_level')"><input v-model="form.legal_risk_level" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="Status SPPL" :error="err('legal_sppl_status')"><input v-model="form.legal_sppl_status" class="input" /></FormField>
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between"><p class="label !mb-0">Bidang usaha (KBLI)</p><button type="button" class="btn btn-secondary btn-sm" @click="addKbli"><Plus class="h-4 w-4" /> KBLI</button></div>
                    <div class="mt-3 space-y-2">
                        <div v-for="(k, i) in form.legal_kbli" :key="i" class="flex gap-2">
                            <input v-model="k.code" class="input input-sm w-28" placeholder="62199" />
                            <input v-model="k.title" class="input input-sm" placeholder="Judul KBLI" />
                            <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="form.legal_kbli.splice(i, 1)"><Trash class="h-4 w-4" /></button>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'seo'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('seo')">
                <FormField class="sm:col-span-2" label="Judul SEO beranda" :error="err('seo_title')"><input v-model="form.seo_title" class="input" maxlength="255" /></FormField>
                <FormField class="sm:col-span-2" label="Deskripsi meta" :help="`${(form.seo_description || '').length}/160 karakter ideal`" :error="err('seo_description')"><textarea v-model="form.seo_description" rows="3" class="input" maxlength="300"></textarea></FormField>
                <FormField class="sm:col-span-2" label="Kata kunci" :error="err('seo_keywords')"><input v-model="form.seo_keywords" class="input" /></FormField>
                <p class="sm:col-span-2 text-sm text-muted">Statistik beranda (isi 0 untuk menyembunyikan):</p>
                <FormField label="Proyek selesai" :error="err('stat_projects')"><input v-model="form.stat_projects" type="number" min="0" class="input" /></FormField>
                <FormField label="Klien" :error="err('stat_clients')"><input v-model="form.stat_clients" type="number" min="0" class="input" /></FormField>
                <FormField label="Tahun berkarya" :error="err('stat_years')"><input v-model="form.stat_years" type="number" min="0" class="input" /></FormField>
                <FormField label="Kepuasan (%)" :error="err('stat_satisfaction')"><input v-model="form.stat_satisfaction" type="number" min="0" max="100" class="input" /></FormField>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'attendance'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('attendance')">
                <FormField label="Jam masuk" :error="err('attendance_start')"><input v-model="form.attendance_start" type="time" class="input" /></FormField>
                <FormField label="Jam pulang" :error="err('attendance_end')"><input v-model="form.attendance_end" type="time" class="input" /></FormField>
                <FormField label="Toleransi terlambat (menit)" :error="err('attendance_late_tolerance')"><input v-model="form.attendance_late_tolerance" type="number" min="0" class="input" /></FormField>
                <div class="flex items-end pb-2"><Switch v-model="form.attendance_require_location" label="Wajib izinkan lokasi (GPS)" /></div>
                <FormField label="Latitude kantor" :error="err('attendance_office_lat')"><input v-model="form.attendance_office_lat" class="input" /></FormField>
                <FormField label="Longitude kantor" :error="err('attendance_office_lng')"><input v-model="form.attendance_office_lng" class="input" /></FormField>
                <FormField label="Radius absen WFO (meter)" help="0 = tidak dibatasi." :error="err('attendance_radius_m')"><input v-model="form.attendance_radius_m" type="number" min="0" class="input" /></FormField>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'invoice'" class="grid gap-5 sm:grid-cols-2" @submit.prevent="save('invoice')">
                <FormField label="Prefix nomor invoice" help="Contoh hasil: INV/202610/0001" :error="err('invoice_prefix')"><input v-model="form.invoice_prefix" class="input" /></FormField>
                <FormField label="Pajak default (%)" :error="err('invoice_tax_percent')"><input v-model="form.invoice_tax_percent" type="number" min="0" max="100" step="0.01" class="input" /></FormField>
                <FormField label="Nama bank" :error="err('invoice_bank_name')"><input v-model="form.invoice_bank_name" class="input" placeholder="mis. BRI" /></FormField>
                <FormField label="No. rekening" :error="err('invoice_bank_account')"><input v-model="form.invoice_bank_account" class="input" /></FormField>
                <FormField label="Atas nama" :error="err('invoice_bank_holder')"><input v-model="form.invoice_bank_holder" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="Catatan kaki invoice" :error="err('invoice_footer')"><textarea v-model="form.invoice_footer" rows="2" class="input"></textarea></FormField>
                <div class="sm:col-span-2"><button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button></div>
            </form>

            <form v-else-if="active === 'security'" class="space-y-6" @submit.prevent="save('security')">
                <Switch v-model="form.force_admin_2fa" label="Wajibkan 2FA untuk semua Admin" description="Admin tanpa 2FA akan diarahkan untuk mengaktifkannya sebelum bisa memakai panel." />
                <Switch v-model="form.notify_email_new_order" label="Kirim email saat ada request website baru" description="Dikirim ke email perusahaan (butuh konfigurasi SMTP di .env)." />
                <button class="btn btn-primary" :disabled="form.processing"><Save class="h-4 w-4" /> Simpan</button>
            </form>

            <div v-else class="space-y-6">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">PHP / Laravel</dt><dd class="font-bold">{{ system.php }} · Laravel {{ system.laravel }}</dd></div>
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">Environment</dt><dd class="flex gap-2 font-bold">{{ system.env }} <Badge :color="system.debug ? 'rose' : 'emerald'">{{ system.debug ? 'DEBUG ON' : 'Debug off' }}</Badge></dd></div>
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">HTTPS (APP_URL)</dt><dd><Badge :color="system.https ? 'emerald' : 'amber'">{{ system.https ? 'Aktif' : 'Belum HTTPS' }}</Badge></dd></div>
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">Mailer</dt><dd class="font-bold">{{ system.mail }}</dd></div>
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">qpdf / Ghostscript (TTE PDF 1.5+)</dt><dd class="flex gap-2"><Badge :color="system.qpdf ? 'emerald' : 'amber'">qpdf {{ system.qpdf ? '✓' : '✗' }}</Badge><Badge :color="system.gs ? 'emerald' : 'amber'">gs {{ system.gs ? '✓' : '✗' }}</Badge></dd></div>
                    <div class="rounded-2xl border border-line p-4"><dt class="text-xs text-muted">Storage link</dt><dd><Badge :color="system.storage_link ? 'emerald' : 'rose'">{{ system.storage_link ? 'OK' : 'Jalankan php artisan storage:link' }}</Badge></dd></div>
                </dl>
                <p v-if="system.debug && system.env === 'production'" class="rounded-2xl bg-rose-50 p-4 text-sm font-bold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">⚠️ APP_DEBUG=true di production berbahaya. Ubah menjadi false di file .env.</p>
                <button type="button" class="btn btn-secondary" @click="clearCache"><RefreshCw class="h-4 w-4" /> Bersihkan & optimasi cache</button>
            </div>
        </div>
    </div>
</template>
