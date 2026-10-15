<script setup>
import { Copy, KeyRound, Pencil, Plus, ShieldCheck, ShieldOff, Trash, UserCheck, UserX, Users } from '@lucide/vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Avatar from '@/Components/ui/Avatar.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import { confirm } from '@/composables/useConfirm';
import { toast } from '@/composables/useToast';
import { useFilters } from '@/composables/useFilters';
import { relative } from '@/lib/format';

const props = defineProps({ users: Object, filters: Object, stats: Object, referralBase: String });
const page = usePage();
const me = computed(() => page.props.auth.user);
const f = useFilters(props.filters, route('admin.users.index'));

const show = ref(false);
const editing = ref(null);
const credentials = ref(null);
const empty = () => ({ name: '', email: '', role: 'agent', position: '', phone: '', employee_number: '', joined_at: '', address: '', password: '' });
const form = useForm(empty());

watch(
    () => page.props.flash?.data,
    (d) => {
        if (d?.credentials) credentials.value = d.credentials;
    },
    { immediate: true },
);

const openCreate = () => {
    editing.value = null;
    form.defaults(empty());
    form.reset();
    form.clearErrors();
    show.value = true;
};
const openEdit = (u) => {
    editing.value = u;
    form.defaults({ name: u.name, email: u.email, role: u.role, position: u.position || '', phone: u.phone || '', employee_number: u.employee_number || '', joined_at: u.joined_at || '', address: u.address || '', password: '' });
    form.reset();
    form.clearErrors();
    show.value = true;
};
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => (show.value = false) };
    if (editing.value) form.put(route('admin.users.update', editing.value.id), opts);
    else form.post(route('admin.users.store'), opts);
};

const toggle = async (u) => {
    if (await confirm({ title: u.is_active ? 'Nonaktifkan akun?' : 'Aktifkan akun?', message: u.is_active ? `${u.name} akan dikeluarkan dari semua perangkat & tidak bisa login.` : `${u.name} dapat login kembali.`, danger: u.is_active })) {
        router.patch(route('admin.users.toggle', u.id), {}, { preserveScroll: true });
    }
};
const reset2fa = async (u) => {
    if (await confirm({ title: 'Reset 2FA?', message: `${u.name} perlu mengaktifkan ulang 2FA.`, danger: true })) router.delete(route('admin.users.reset-2fa', u.id), { preserveScroll: true });
};
const destroy = async (u) => {
    if (await confirm({ title: 'Hapus akun?', message: `${u.name} (${u.email})`, danger: true })) router.delete(route('admin.users.destroy', u.id), { preserveScroll: true });
};
const copy = async (text, msg) => {
    try {
        await navigator.clipboard.writeText(text);
        toast(msg);
    } catch {
        toast(text, 'info');
    }
};
</script>

<template>
    <Head title="Pengguna" />
    <PageHeader title="Pengguna" subtitle="Kelola akun Admin & Agen (karyawan)." :icon="Users">
        <button type="button" class="btn btn-primary" @click="openCreate"><Plus class="h-4 w-4" /> Tambah pengguna</button>
    </PageHeader>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard label="Admin" :value="stats.admins" :icon="ShieldCheck" color="violet" />
        <StatCard label="Agen" :value="stats.agents" :icon="Users" color="fuchsia" />
        <StatCard label="Nonaktif" :value="stats.inactive" :icon="UserX" color="slate" />
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <SearchInput v-model="f.q" placeholder="Cari nama, email, NIP…" />
        <select v-model="f.role" class="input input-sm sm:w-40"><option value="">Semua peran</option><option value="admin">Admin</option><option value="agent">Agen</option></select>
        <select v-model="f.status" class="input input-sm sm:w-40"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
    </div>

    <div class="table-wrap">
        <table v-if="users.data.length" class="table">
            <thead><tr><th>Pengguna</th><th>Peran</th><th>Keamanan</th><th>Referral</th><th>Login terakhir</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                <tr v-for="u in users.data" :key="u.id" :class="u.is_active ? '' : 'opacity-60'">
                    <td>
                        <div class="flex items-center gap-3">
                            <Avatar :user="u" size="md" />
                            <div class="min-w-0"><p class="truncate font-bold">{{ u.name }} <span v-if="u.id === me.id" class="text-xs text-violet-500">(kamu)</span></p><p class="truncate text-xs text-muted">{{ u.email }}<span v-if="u.employee_number"> · {{ u.employee_number }}</span></p></div>
                        </div>
                    </td>
                    <td><Badge :color="u.role === 'admin' ? 'violet' : 'fuchsia'">{{ u.role === 'admin' ? 'Admin' : 'Agen' }}</Badge><p class="mt-1 text-xs text-muted">{{ u.position }}</p></td>
                    <td><Badge :color="u.two_factor_enabled ? 'emerald' : 'amber'" dot>{{ u.two_factor_enabled ? '2FA aktif' : 'Tanpa 2FA' }}</Badge><p v-if="!u.is_active" class="mt-1 text-xs font-bold text-rose-500">Nonaktif</p></td>
                    <td>
                        <button v-if="u.referral_code" type="button" class="chip hover:border-ink" title="Salin tautan referral" @click="copy(`${referralBase}?ref=${u.referral_code}`, 'Tautan referral disalin')"><Copy class="h-3 w-3" /> {{ u.referral_code }}</button>
                        <p v-if="u.role === 'agent'" class="mt-1 text-xs text-muted">{{ u.referred_orders_count }} referral · {{ u.assigned_orders_count }} proyek</p>
                    </td>
                    <td class="text-sm text-muted">{{ u.last_login_at ? relative(u.last_login_at) : 'Belum pernah' }}<p v-if="u.last_login_ip" class="text-xs">{{ u.last_login_ip }}</p></td>
                    <td class="text-right whitespace-nowrap">
                        <button type="button" class="btn btn-ghost btn-sm" aria-label="Ubah" @click="openEdit(u)"><Pencil class="h-4 w-4" /></button>
                        <button v-if="u.id !== me.id" type="button" class="btn btn-ghost btn-sm" :aria-label="u.is_active ? 'Nonaktifkan' : 'Aktifkan'" @click="toggle(u)"><component :is="u.is_active ? UserX : UserCheck" class="h-4 w-4" /></button>
                        <button v-if="u.two_factor_enabled" type="button" class="btn btn-ghost btn-sm" aria-label="Reset 2FA" @click="reset2fa(u)"><ShieldOff class="h-4 w-4" /></button>
                        <button v-if="u.id !== me.id" type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus" @click="destroy(u)"><Trash class="h-4 w-4" /></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-else title="Tidak ada pengguna" />
    </div>
    <div class="mt-5"><Pagination :paginator="users" /></div>

    <Modal :show="show" :title="editing ? 'Ubah pengguna' : 'Tambah pengguna'" size="lg" @close="show = false">
        <div class="grid gap-4 sm:grid-cols-2">
            <FormField label="Nama lengkap" :error="form.errors.name" required><input v-model="form.name" type="text" class="input" /></FormField>
            <FormField label="Email" :error="form.errors.email" required><input v-model="form.email" type="email" class="input" /></FormField>
            <FormField label="Peran" :error="form.errors.role" required>
                <select v-model="form.role" class="input"><option value="agent">Agen (karyawan)</option><option value="admin">Admin</option></select>
            </FormField>
            <FormField label="Jabatan" :error="form.errors.position"><input v-model="form.position" type="text" class="input" placeholder="mis. Web Developer" /></FormField>
            <FormField label="No. HP" :error="form.errors.phone"><input v-model="form.phone" type="tel" class="input" /></FormField>
            <FormField label="No. pegawai" :error="form.errors.employee_number"><input v-model="form.employee_number" type="text" class="input" placeholder="KSA-003" /></FormField>
            <FormField label="Tanggal bergabung" :error="form.errors.joined_at"><input v-model="form.joined_at" type="date" class="input" /></FormField>
            <FormField :label="editing ? 'Reset password (opsional)' : 'Password (opsional)'" :error="form.errors.password" help="Kosongkan agar dibuat otomatis / tidak diubah."><input v-model="form.password" type="text" class="input font-mono" autocomplete="new-password" /></FormField>
            <FormField class="sm:col-span-2" label="Alamat" :error="form.errors.address"><textarea v-model="form.address" rows="2" class="input"></textarea></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="show = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="form.processing" @click="submit">Simpan</button>
        </template>
    </Modal>

    <Modal :show="!!credentials" title="Kredensial akun 🔑" subtitle="Tampil sekali saja. Kirimkan secara aman, lalu minta pengguna mengganti password." size="sm" @close="credentials = null">
        <div class="space-y-3 rounded-2xl bg-surface-2 p-4 font-mono text-sm">
            <p><span class="text-muted">Email:</span> <b>{{ credentials?.email }}</b></p>
            <p><span class="text-muted">Password:</span> <b>{{ credentials?.password }}</b></p>
        </div>
        <template #footer>
            <button type="button" class="btn btn-primary" @click="copy(`Login: ${route('login')}\nEmail: ${credentials.email}\nPassword: ${credentials.password}`, 'Kredensial disalin'); credentials = null"><KeyRound class="h-4 w-4" /> Salin & tutup</button>
        </template>
    </Modal>
</template>
