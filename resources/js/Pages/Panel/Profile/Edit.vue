<script setup>
import { Camera, CircleCheck, Copy, KeyRound, Laptop, LogOut, MonitorSmartphone, Save, ShieldCheck, ShieldOff, Smartphone, UserRound } from '@lucide/vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import PasswordStrength from '@/Components/ui/PasswordStrength.vue';
import Tabs from '@/Components/ui/Tabs.vue';
import { confirm } from '@/composables/useConfirm';
import { toast } from '@/composables/useToast';
import { date, relative } from '@/lib/format';

const props = defineProps({ profile: Object, twoFactor: Object, sessions: Array, tab: String });
const page = usePage();
const tab = ref(['profile', 'security', 'sessions'].includes(props.tab) ? props.tab : 'profile');
const tabs = [
    { key: 'profile', label: 'Profil', icon: UserRound },
    { key: 'security', label: 'Keamanan & 2FA', icon: ShieldCheck },
    { key: 'sessions', label: 'Sesi perangkat', icon: MonitorSmartphone },
];

const info = useForm({ name: props.profile.name, email: props.profile.email, phone: props.profile.phone || '', address: props.profile.address || '' });
const saveInfo = () => info.put(route('panel.profile.update'), { preserveScroll: true });

const avatarInput = ref(null);
const uploadAvatar = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    router.post(route('panel.profile.avatar'), { avatar: file }, { forceFormData: true, preserveScroll: true, onFinish: () => (e.target.value = '') });
};

const pwd = useForm({ current_password: '', password: '', password_confirmation: '' });
const savePwd = () => pwd.put(route('panel.profile.password'), { preserveScroll: true, onSuccess: () => pwd.reset() });

// 2FA
const enablePwd = ref('');
const enabling = ref(false);
const startEnable = () => {
    enabling.value = true;
    router.post(route('panel.profile.2fa.enable'), { password: enablePwd.value }, { preserveScroll: true, onFinish: () => { enabling.value = false; enablePwd.value = ''; }, onError: (e) => toast(e.password || 'Gagal', 'error') });
};
const code = useForm({ code: '' });
const confirm2fa = () => code.post(route('panel.profile.2fa.confirm'), { preserveScroll: true, onSuccess: () => code.reset() });

const recoveryCodes = ref(null);
watch(() => page.props.flash?.data, (d) => { if (d?.recovery_codes) recoveryCodes.value = d.recovery_codes; }, { immediate: true });

const askPassword = async (title, message) => confirm({ title, message, input: { label: 'Password akun', type: 'password', required: true } });
const regenerate = async () => {
    const password = await askPassword('Buat ulang kode pemulihan?', 'Kode lama tidak bisa dipakai lagi.');
    if (password) router.post(route('panel.profile.2fa.recovery'), { password }, { preserveScroll: true, onError: (e) => toast(e.password || 'Gagal', 'error') });
};
const disable2fa = async () => {
    const password = await askPassword('Nonaktifkan 2FA?', 'Akun hanya akan dilindungi password.');
    if (password) router.delete(route('panel.profile.2fa.disable'), { data: { password }, preserveScroll: true, onError: (e) => toast(e.password || 'Gagal', 'error') });
};
const copyCodes = async () => {
    try {
        await navigator.clipboard.writeText(recoveryCodes.value.join('\n'));
        toast('Kode pemulihan disalin');
    } catch {
        toast('Salin manual kode di layar', 'info');
    }
};

// Sesi
const revoke = (s) => router.delete(route('panel.profile.sessions.destroy', s.id), { preserveScroll: true });
const revokeOthers = async () => {
    const password = await askPassword('Keluarkan semua perangkat lain?', 'Semua sesi selain perangkat ini akan diakhiri.');
    if (password) router.delete(route('panel.profile.sessions.others'), { data: { password }, preserveScroll: true, onError: (e) => toast(e.password || 'Gagal', 'error') });
};
const referralUrl = computed(() => (props.profile.referral_code ? `${route('order.create')}?ref=${props.profile.referral_code}` : ''));
const copyRef = async () => {
    try {
        await navigator.clipboard.writeText(referralUrl.value);
        toast('Tautan referral disalin!');
    } catch {
        toast(referralUrl.value, 'info');
    }
};
</script>

<template>
    <Head title="Profil Saya" />
    <PageHeader title="Profil & Keamanan" subtitle="Kelola data diri, password, autentikasi dua faktor & perangkat yang login." :icon="UserRound" />
    <Tabs v-model="tab" :tabs="tabs" class="mb-6" />

    <div v-if="tab === 'profile'" class="grid gap-6 xl:grid-cols-3">
        <div class="card-brutal flex flex-col items-center p-8 text-center">
            <div class="group relative">
                <Avatar :user="{ ...profile, name: profile.name }" :src="profile.avatar_url || ''" size="xl" />
                <button type="button" class="absolute right-0 bottom-0 grid h-9 w-9 place-items-center rounded-full border-2 border-card bg-ink text-surface transition group-hover:scale-110" aria-label="Ganti foto" @click="avatarInput?.click()"><Camera class="h-4 w-4" /></button>
                <input ref="avatarInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="uploadAvatar" />
            </div>
            <h2 class="mt-4 font-display text-xl font-bold">{{ profile.name }}</h2>
            <p class="text-sm text-muted">{{ profile.position || (profile.role === 'admin' ? 'Administrator' : 'Agen') }}</p>
            <Badge class="mt-3" :color="profile.role === 'admin' ? 'violet' : 'fuchsia'">{{ profile.role === 'admin' ? 'Admin' : 'Agen' }}</Badge>
            <dl class="mt-6 w-full space-y-2 text-left text-sm">
                <div v-if="profile.employee_number" class="flex justify-between"><dt class="text-muted">No. pegawai</dt><dd class="font-bold">{{ profile.employee_number }}</dd></div>
                <div v-if="profile.joined_at" class="flex justify-between"><dt class="text-muted">Bergabung</dt><dd class="font-bold">{{ date(profile.joined_at) }}</dd></div>
                <div v-if="profile.last_login_at" class="flex justify-between"><dt class="text-muted">Login terakhir</dt><dd class="font-bold">{{ relative(profile.last_login_at) }}</dd></div>
            </dl>
            <div v-if="profile.referral_code" class="mt-6 w-full rounded-2xl bg-lime-100 p-4 text-left dark:bg-lime-500/10">
                <p class="text-xs font-extrabold text-lime-800 uppercase dark:text-lime-300">Kode referral kamu</p>
                <p class="mt-1 font-mono text-lg font-bold">{{ profile.referral_code }}</p>
                <button type="button" class="btn btn-brutal btn-sm mt-3 w-full" @click="copyRef"><Copy class="h-4 w-4" /> Salin tautan referral</button>
            </div>
        </div>
        <form class="card p-6 xl:col-span-2" @submit.prevent="saveInfo">
            <h2 class="font-display text-lg font-bold">Data diri</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <FormField label="Nama lengkap" :error="info.errors.name" required><input v-model="info.name" type="text" class="input" /></FormField>
                <FormField label="Email" :error="info.errors.email" required><input v-model="info.email" type="email" class="input" /></FormField>
                <FormField label="No. HP / WhatsApp" :error="info.errors.phone"><input v-model="info.phone" type="tel" class="input" /></FormField>
                <FormField class="sm:col-span-2" label="Alamat" :error="info.errors.address"><textarea v-model="info.address" rows="3" class="input"></textarea></FormField>
            </div>
            <button type="submit" class="btn btn-primary mt-6" :disabled="info.processing || !info.isDirty"><Save class="h-4 w-4" /> Simpan</button>
        </form>
    </div>

    <div v-else-if="tab === 'security'" class="grid gap-6 xl:grid-cols-2">
        <form class="card p-6" @submit.prevent="savePwd">
            <h2 class="flex items-center gap-2 font-display text-lg font-bold"><KeyRound class="h-5 w-5 text-violet-500" /> Ganti password</h2>
            <div class="mt-5 space-y-4">
                <FormField label="Password saat ini" :error="pwd.errors.current_password"><input v-model="pwd.current_password" type="password" class="input" autocomplete="current-password" /></FormField>
                <FormField label="Password baru" :error="pwd.errors.password"><input v-model="pwd.password" type="password" class="input" autocomplete="new-password" /><PasswordStrength :value="pwd.password" class="mt-2" /></FormField>
                <FormField label="Ulangi password baru"><input v-model="pwd.password_confirmation" type="password" class="input" autocomplete="new-password" /></FormField>
            </div>
            <button type="submit" class="btn btn-primary mt-6" :disabled="pwd.processing">Perbarui password</button>
        </form>

        <div class="card p-6">
            <h2 class="flex items-center gap-2 font-display text-lg font-bold"><Smartphone class="h-5 w-5 text-violet-500" /> Autentikasi dua faktor (2FA)</h2>
            <template v-if="twoFactor.enabled">
                <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><CircleCheck class="h-4 w-4" /> 2FA aktif</p>
                <p class="mt-3 text-sm text-muted">Sisa kode pemulihan: <b class="text-ink">{{ twoFactor.recovery_left }}</b></p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" @click="regenerate"><KeyRound class="h-4 w-4" /> Buat ulang kode pemulihan</button>
                    <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click="disable2fa"><ShieldOff class="h-4 w-4" /> Nonaktifkan</button>
                </div>
            </template>
            <template v-else-if="twoFactor.pending">
                <ol class="mt-4 space-y-4 text-sm">
                    <li><b>1.</b> Pindai QR dengan Google Authenticator / Authy / Microsoft Authenticator.</li>
                    <li class="grid place-items-center"><div class="w-52 rounded-2xl border-2 border-ink bg-white p-3 [&_svg]:h-auto [&_svg]:w-full" v-html="twoFactor.qr"></div></li>
                    <li class="text-center text-xs text-muted">Atau masukkan kunci manual:<br /><code class="font-mono text-sm font-bold break-all text-ink">{{ twoFactor.secret }}</code></li>
                    <li><b>2.</b> Masukkan 6 digit kode yang muncul di aplikasi.</li>
                </ol>
                <form class="mt-4 flex gap-2" @submit.prevent="confirm2fa">
                    <input v-model="code.code" type="text" inputmode="numeric" maxlength="6" class="input font-mono text-lg tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code" />
                    <button type="submit" class="btn btn-primary shrink-0" :disabled="code.processing || code.code.length < 6">Aktifkan</button>
                </form>
                <p v-if="code.errors.code" class="field-error">{{ code.errors.code }}</p>
            </template>
            <template v-else>
                <p class="mt-3 text-sm text-muted">Tambahkan lapisan keamanan: selain password, login membutuhkan kode dari HP kamu. Sangat disarankan untuk Admin.</p>
                <form class="mt-5 flex gap-2" @submit.prevent="startEnable">
                    <input v-model="enablePwd" type="password" class="input" placeholder="Konfirmasi password" autocomplete="current-password" />
                    <button type="submit" class="btn btn-primary shrink-0" :disabled="!enablePwd || enabling"><ShieldCheck class="h-4 w-4" /> Aktifkan 2FA</button>
                </form>
            </template>
        </div>
    </div>

    <div v-else class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5">
            <div><h2 class="font-display text-lg font-bold">Perangkat yang sedang login</h2><p class="text-sm text-muted">Akhiri sesi yang tidak kamu kenali.</p></div>
            <button type="button" class="btn btn-secondary btn-sm" @click="revokeOthers"><LogOut class="h-4 w-4" /> Keluarkan perangkat lain</button>
        </div>
        <ul class="divide-y divide-line">
            <li v-for="s in sessions" :key="s.id" class="flex items-center gap-4 p-5">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-surface-2"><Laptop class="h-5 w-5" /></span>
                <div class="min-w-0 flex-1"><p class="font-bold">{{ s.agent }} <Badge v-if="s.current" color="emerald" size="xs">Perangkat ini</Badge></p><p class="text-xs text-muted">{{ s.ip }} · aktif {{ relative(s.last_active) }}</p></div>
                <button v-if="!s.current" type="button" class="btn btn-ghost btn-sm text-rose-500" @click="revoke(s)">Akhiri</button>
            </li>
            <li v-if="!sessions.length" class="p-5 text-sm text-muted">Data sesi tidak tersedia (driver sesi bukan database).</li>
        </ul>
    </div>

    <Modal :show="!!recoveryCodes" title="Simpan kode pemulihan 🔐" subtitle="Gunakan bila HP hilang. Setiap kode hanya bisa dipakai sekali. Kode ini tidak akan ditampilkan lagi." size="sm" @close="recoveryCodes = null">
        <div class="grid grid-cols-2 gap-2 rounded-2xl bg-surface-2 p-4 font-mono text-sm font-bold">
            <span v-for="c in recoveryCodes" :key="c">{{ c }}</span>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="copyCodes"><Copy class="h-4 w-4" /> Salin</button>
            <button type="button" class="btn btn-primary" @click="recoveryCodes = null">Sudah saya simpan</button>
        </template>
    </Modal>
</template>
