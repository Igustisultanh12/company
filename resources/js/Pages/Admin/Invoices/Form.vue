<script setup>
import { ArrowLeft, Plus, Save, Trash } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import FormField from '@/Components/ui/FormField.vue';
import MoneyInput from '@/Components/ui/MoneyInput.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import { rupiah } from '@/lib/format';

const props = defineProps({ invoice: Object, orders: Array, statuses: Object });
const editing = computed(() => !!props.invoice.id);

const form = useForm({
    order_id: props.invoice.order_id ?? null,
    client_name: props.invoice.client_name || '',
    client_email: props.invoice.client_email || '',
    client_phone: props.invoice.client_phone || '',
    client_address: props.invoice.client_address || '',
    issue_date: props.invoice.issue_date || '',
    due_date: props.invoice.due_date || '',
    items: (props.invoice.items || []).map((i) => ({ description: i.description, qty: Number(i.qty), price: Number(i.price) })),
    discount: props.invoice.discount || 0,
    tax_percent: Number(props.invoice.tax_percent || 0),
    notes: props.invoice.notes || '',
    status: ['draft', 'cancelled'].includes(props.invoice.status) ? props.invoice.status : editing.value ? props.invoice.status : 'unpaid',
});

const addItem = () => form.items.push({ description: '', qty: 1, price: 0 });
const removeItem = (i) => form.items.length > 1 && form.items.splice(i, 1);

const subtotal = computed(() => form.items.reduce((s, i) => s + Math.round((Number(i.qty) || 0) * (Number(i.price) || 0)), 0));
const base = computed(() => Math.max(0, subtotal.value - (Number(form.discount) || 0)));
const tax = computed(() => Math.round((base.value * (Number(form.tax_percent) || 0)) / 100));
const total = computed(() => base.value + tax.value);

const submit = () => (editing.value ? form.put(route('admin.invoices.update', props.invoice.id)) : form.post(route('admin.invoices.store')));
</script>

<template>
    <Head :title="editing ? `Ubah ${invoice.number}` : 'Buat Invoice'" />
    <Link :href="editing ? route('admin.invoices.show', invoice.id) : route('admin.invoices.index')" class="btn btn-ghost btn-sm mb-4"><ArrowLeft class="h-4 w-4" /> Kembali</Link>
    <PageHeader :title="editing ? `Ubah invoice ${invoice.number}` : 'Buat invoice baru'" subtitle="Nomor invoice dibuat otomatis sesuai format perusahaan." />

    <form class="grid gap-6 xl:grid-cols-3" @submit.prevent="submit">
        <div class="space-y-6 xl:col-span-2">
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold">Data klien</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <FormField class="sm:col-span-2" label="Terkait pesanan">
                        <select v-model="form.order_id" class="input"><option :value="null">— Tidak terkait —</option><option v-for="o in orders" :key="o.id" :value="o.id">{{ o.code }} · {{ o.company_name || o.client_name }}</option></select>
                    </FormField>
                    <FormField label="Nama klien / perusahaan" :error="form.errors.client_name" required><input v-model="form.client_name" type="text" class="input" /></FormField>
                    <FormField label="Email" :error="form.errors.client_email"><input v-model="form.client_email" type="email" class="input" /></FormField>
                    <FormField label="Telepon" :error="form.errors.client_phone"><input v-model="form.client_phone" type="text" class="input" /></FormField>
                    <FormField label="Alamat" :error="form.errors.client_address"><input v-model="form.client_address" type="text" class="input" /></FormField>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold">Item tagihan</h2>
                    <button type="button" class="btn btn-secondary btn-sm" @click="addItem"><Plus class="h-4 w-4" /> Item</button>
                </div>
                <p v-if="form.errors.items" class="field-error">{{ form.errors.items }}</p>
                <div class="mt-4 space-y-3">
                    <div v-for="(item, i) in form.items" :key="i" class="grid gap-2 rounded-2xl border border-line p-3 sm:grid-cols-12 sm:items-start">
                        <div class="sm:col-span-6">
                            <input v-model="item.description" type="text" class="input input-sm" placeholder="Deskripsi, mis. Pembuatan website company profile" />
                            <p v-if="form.errors[`items.${i}.description`]" class="field-error">{{ form.errors[`items.${i}.description`] }}</p>
                        </div>
                        <input v-model.number="item.qty" type="number" min="0.01" step="0.01" class="input input-sm sm:col-span-2" placeholder="Qty" />
                        <div class="sm:col-span-3"><MoneyInput v-model="item.price" /></div>
                        <button type="button" class="btn btn-ghost btn-sm text-rose-500 sm:col-span-1" :disabled="form.items.length === 1" aria-label="Hapus item" @click="removeItem(i)"><Trash class="h-4 w-4" /></button>
                        <p class="text-right text-xs font-bold text-muted sm:col-span-12">= {{ rupiah(Math.round((item.qty || 0) * (item.price || 0))) }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <FormField label="Catatan / syarat pembayaran" :error="form.errors.notes"><textarea v-model="form.notes" rows="3" class="input"></textarea></FormField>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card-brutal p-6">
                <div class="space-y-4">
                    <FormField label="Tanggal terbit" :error="form.errors.issue_date" required><input v-model="form.issue_date" type="date" class="input" /></FormField>
                    <FormField label="Jatuh tempo" :error="form.errors.due_date"><input v-model="form.due_date" type="date" class="input" /></FormField>
                    <FormField label="Status" :error="form.errors.status">
                        <select v-model="form.status" class="input">
                            <option value="draft">Draf (belum dikirim)</option>
                            <option value="unpaid">Terbitkan</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option v-if="editing && ['partial', 'paid'].includes(invoice.status)" :value="invoice.status">{{ statuses[invoice.status].label }} (otomatis)</option>
                        </select>
                    </FormField>
                    <FormField label="Diskon" :error="form.errors.discount"><MoneyInput v-model="form.discount" /></FormField>
                    <FormField label="Pajak (%)" :error="form.errors.tax_percent"><input v-model.number="form.tax_percent" type="number" min="0" max="100" step="0.01" class="input" /></FormField>
                </div>
                <dl class="mt-6 space-y-2 border-t-2 border-dashed border-line pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd class="font-bold">{{ rupiah(subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Diskon</dt><dd>-{{ rupiah(form.discount || 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Pajak</dt><dd>{{ rupiah(tax) }}</dd></div>
                    <div class="flex justify-between pt-2 font-display text-xl font-bold"><dt>Total</dt><dd class="text-gradient">{{ rupiah(total) }}</dd></div>
                </dl>
                <button type="submit" class="btn btn-primary mt-6 w-full" :disabled="form.processing"><Save class="h-4 w-4" /> {{ editing ? 'Simpan perubahan' : 'Simpan invoice' }}</button>
            </div>
        </div>
    </form>
</template>
