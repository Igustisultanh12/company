<script setup>
import { ArrowLeft, Copy, Pencil, Plus, Printer, Trash, Wallet } from '@lucide/vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import FormField from '@/Components/ui/FormField.vue';
import Modal from '@/Components/ui/Modal.vue';
import MoneyInput from '@/Components/ui/MoneyInput.vue';
import ProgressBar from '@/Components/ui/ProgressBar.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { confirm } from '@/composables/useConfirm';
import { toast } from '@/composables/useToast';
import { date, rupiah, todayLocal } from '@/lib/format';

const props = defineProps({ invoice: Object, statuses: Object, publicUrl: String, bank: Object });

const showPay = ref(false);
const pay = useForm({ amount: props.invoice.balance, paid_at: todayLocal(), method: 'Transfer Bank', reference: '', note: '' });
const addPayment = () => pay.post(route('admin.invoices.payments.store', props.invoice.id), { preserveScroll: true, onSuccess: () => (showPay.value = false) });

const removePayment = async (p) => {
    if (await confirm({ title: 'Hapus pembayaran?', message: `${rupiah(p.amount)} pada ${date(p.paid_at)}`, danger: true })) {
        router.delete(route('admin.invoices.payments.destroy', p.id), { preserveScroll: true });
    }
};
const destroy = async () => {
    if (await confirm({ title: 'Hapus invoice?', message: props.invoice.number, danger: true })) router.delete(route('admin.invoices.destroy', props.invoice.id));
};
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.publicUrl);
        toast('Tautan invoice (berlaku 14 hari) disalin.');
    } catch {
        toast(props.publicUrl, 'info');
    }
};
const paidPct = () => (props.invoice.total ? Math.round((props.invoice.paid_amount / props.invoice.total) * 100) : 0);
</script>

<template>
    <Head :title="`Invoice ${invoice.number}`" />
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <Link :href="route('admin.invoices.index')" class="btn btn-ghost btn-sm"><ArrowLeft class="h-4 w-4" /> Semua invoice</Link>
        <div class="flex flex-wrap gap-2">
            <a :href="route('admin.invoices.print', invoice.id)" target="_blank" class="btn btn-secondary btn-sm"><Printer class="h-4 w-4" /> Cetak / PDF</a>
            <button v-if="publicUrl" type="button" class="btn btn-secondary btn-sm" @click="copyLink"><Copy class="h-4 w-4" /> Tautan klien</button>
            <Link :href="route('admin.invoices.edit', invoice.id)" class="btn btn-secondary btn-sm"><Pencil class="h-4 w-4" /> Ubah</Link>
            <button type="button" class="btn btn-ghost btn-sm text-rose-500" @click="destroy"><Trash class="h-4 w-4" /></button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
            <div class="bg-brand-gradient flex flex-wrap items-end justify-between gap-4 p-6 text-white">
                <div><p class="text-sm opacity-80">Invoice</p><p class="font-display text-2xl font-bold">{{ invoice.number }}</p></div>
                <StatusBadge :value="invoice.status" :map="statuses" class="!bg-white !text-zinc-900" />
            </div>
            <div class="grid gap-6 p-6 sm:grid-cols-2">
                <div><p class="text-xs font-bold text-muted uppercase">Kepada</p><p class="mt-1 font-bold">{{ invoice.client_name }}</p><p class="text-sm text-muted">{{ invoice.client_email }}<br />{{ invoice.client_phone }}</p></div>
                <div class="sm:text-right"><p class="text-sm"><span class="text-muted">Terbit:</span> <b>{{ date(invoice.issue_date) }}</b></p><p class="text-sm"><span class="text-muted">Jatuh tempo:</span> <b>{{ date(invoice.due_date) }}</b></p><p v-if="invoice.order" class="text-sm"><span class="text-muted">Pesanan:</span> <Link :href="route('admin.orders.show', invoice.order.id)" class="font-bold text-violet-600">{{ invoice.order.code }}</Link></p></div>
            </div>
            <div class="px-6"><div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Deskripsi</th><th class="text-right">Qty</th><th class="text-right">Harga</th><th class="text-right">Jumlah</th></tr></thead>
                    <tbody><tr v-for="(i, idx) in invoice.items" :key="idx"><td>{{ i.description }}</td><td class="text-right">{{ i.qty }}</td><td class="text-right">{{ rupiah(i.price) }}</td><td class="text-right font-bold">{{ rupiah(Math.round(i.qty * i.price)) }}</td></tr></tbody>
                </table>
            </div></div>
            <dl class="ml-auto max-w-xs space-y-1.5 p-6 text-sm">
                <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>{{ rupiah(invoice.subtotal) }}</dd></div>
                <div v-if="invoice.discount" class="flex justify-between"><dt class="text-muted">Diskon</dt><dd>-{{ rupiah(invoice.discount) }}</dd></div>
                <div v-if="invoice.tax_percent" class="flex justify-between"><dt class="text-muted">Pajak {{ invoice.tax_percent }}%</dt><dd>{{ rupiah(invoice.tax_amount) }}</dd></div>
                <div class="flex justify-between border-t border-line pt-2 font-display text-lg font-bold"><dt>Total</dt><dd>{{ rupiah(invoice.total) }}</dd></div>
            </dl>
            <p v-if="invoice.notes" class="mx-6 mb-6 rounded-2xl bg-amber-50 p-4 text-sm whitespace-pre-line dark:bg-amber-500/10">{{ invoice.notes }}</p>
        </div>

        <div class="space-y-6">
            <div class="card-brutal p-6">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><Wallet class="h-5 w-5 text-emerald-500" /> Pembayaran</h2>
                <p class="mt-3 font-display text-3xl font-bold">{{ rupiah(invoice.paid_amount) }}</p>
                <p class="text-sm text-muted">dari {{ rupiah(invoice.total) }} · sisa <b class="text-ink">{{ rupiah(invoice.balance) }}</b></p>
                <ProgressBar class="mt-3" :value="paidPct()" />
                <button v-if="invoice.balance > 0 && !['draft', 'cancelled'].includes(invoice.status)" type="button" class="btn btn-success mt-5 w-full" @click="showPay = true"><Plus class="h-4 w-4" /> Catat pembayaran</button>
                <ul class="mt-5 space-y-2">
                    <li v-for="p in invoice.payments" :key="p.id" class="flex items-center gap-3 rounded-2xl border border-line p-3">
                        <div class="min-w-0 flex-1"><p class="font-bold">{{ rupiah(p.amount) }}</p><p class="truncate text-xs text-muted">{{ date(p.paid_at) }} · {{ p.method }}<span v-if="p.reference"> · {{ p.reference }}</span></p></div>
                        <button type="button" class="btn btn-ghost btn-sm text-rose-500" aria-label="Hapus pembayaran" @click="removePayment(p)"><Trash class="h-4 w-4" /></button>
                    </li>
                </ul>
            </div>
            <div v-if="bank.name" class="card p-6 text-sm">
                <p class="text-xs font-bold text-muted uppercase">Rekening tujuan</p>
                <p class="mt-2 font-bold">{{ bank.name }} · {{ bank.account }}</p>
                <p class="text-muted">a.n. {{ bank.holder }}</p>
            </div>
            <p v-else class="card p-5 text-sm text-muted">💡 Isi data rekening di <Link :href="route('admin.settings.index', { tab: 'invoice' })" class="font-bold text-violet-600">Pengaturan › Invoice</Link> agar tampil di invoice.</p>
        </div>
    </div>

    <Modal :show="showPay" title="Catat pembayaran" size="sm" @close="showPay = false">
        <div class="space-y-4">
            <FormField label="Jumlah" :error="pay.errors.amount" required><MoneyInput v-model="pay.amount" /></FormField>
            <FormField label="Tanggal bayar" :error="pay.errors.paid_at" required><input v-model="pay.paid_at" type="date" class="input" /></FormField>
            <FormField label="Metode" :error="pay.errors.method" required>
                <select v-model="pay.method" class="input"><option>Transfer Bank</option><option>QRIS</option><option>Tunai</option><option>E-Wallet</option><option>Lainnya</option></select>
            </FormField>
            <FormField label="No. referensi" :error="pay.errors.reference"><input v-model="pay.reference" type="text" class="input" /></FormField>
            <FormField label="Catatan" :error="pay.errors.note"><input v-model="pay.note" type="text" class="input" /></FormField>
        </div>
        <template #footer>
            <button type="button" class="btn btn-secondary" @click="showPay = false">Batal</button>
            <button type="button" class="btn btn-success" :disabled="pay.processing" @click="addPayment">Simpan</button>
        </template>
    </Modal>
</template>
