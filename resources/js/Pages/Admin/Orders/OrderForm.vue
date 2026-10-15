<script setup>
import FormField from '@/Components/ui/FormField.vue';
import MoneyInput from '@/Components/ui/MoneyInput.vue';
import Switch from '@/Components/ui/Switch.vue';

defineProps({ form: Object, catalog: Object, packages: Array, agents: Array });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <FormField label="Nama klien" :error="form.errors.client_name" required><input v-model="form.client_name" type="text" class="input" /></FormField>
        <FormField label="Perusahaan / instansi" :error="form.errors.company_name"><input v-model="form.company_name" type="text" class="input" /></FormField>
        <FormField label="Email" :error="form.errors.client_email" required><input v-model="form.client_email" type="email" class="input" /></FormField>
        <FormField label="WhatsApp" :error="form.errors.client_phone" required><input v-model="form.client_phone" type="tel" class="input" /></FormField>
        <FormField label="Jenis website" :error="form.errors.website_type" required>
            <select v-model="form.website_type" class="input">
                <option v-for="(t, k) in catalog.websiteTypes" :key="k" :value="k">{{ t.label }}</option>
            </select>
        </FormField>
        <FormField label="Paket" :error="form.errors.package_id">
            <select v-model="form.package_id" class="input">
                <option :value="null">— Tanpa paket —</option>
                <option v-for="p in packages" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </FormField>
        <FormField label="Budget" :error="form.errors.budget_range">
            <select v-model="form.budget_range" class="input">
                <option :value="null">—</option>
                <option v-for="(l, k) in catalog.budgetRanges" :key="k" :value="k">{{ l }}</option>
            </select>
        </FormField>
        <FormField label="Tenggat" :error="form.errors.deadline"><input v-model="form.deadline" type="date" class="input" /></FormField>
        <FormField label="Prioritas" :error="form.errors.priority">
            <select v-model="form.priority" class="input">
                <option v-for="(l, k) in catalog.orderPriorities" :key="k" :value="k">{{ l }}</option>
            </select>
        </FormField>
        <FormField label="PIC / Penanggung jawab" :error="form.errors.assigned_to">
            <select v-model="form.assigned_to" class="input">
                <option :value="null">— Belum ditentukan —</option>
                <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }} ({{ a.role === 'admin' ? 'Admin' : 'Agen' }})</option>
            </select>
        </FormField>
        <FormField label="Harga penawaran" :error="form.errors.quoted_price"><MoneyInput v-model="form.quoted_price" /></FormField>
        <div class="flex flex-col justify-end gap-3 pb-1">
            <Switch v-model="form.has_domain" label="Klien sudah punya domain" />
            <Switch v-model="form.has_hosting" label="Klien sudah punya hosting" />
        </div>
        <FormField class="sm:col-span-2" label="Fitur" :error="form.errors.features">
            <div class="flex flex-wrap gap-1.5">
                <label v-for="(l, k) in catalog.websiteFeatures" :key="k" :class="form.features.includes(k) ? 'border-violet-500 bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200' : 'border-line'" class="cursor-pointer rounded-full border-2 px-3 py-1 text-xs font-bold transition">
                    <input v-model="form.features" type="checkbox" :value="k" class="hidden" /> {{ l }}
                </label>
            </div>
        </FormField>
        <FormField class="sm:col-span-2" label="Deskripsi kebutuhan" :error="form.errors.description" required><textarea v-model="form.description" rows="4" class="input"></textarea></FormField>
        <FormField class="sm:col-span-2" label="Catatan internal" :error="form.errors.internal_notes" help="Hanya terlihat oleh tim."><textarea v-model="form.internal_notes" rows="2" class="input"></textarea></FormField>
    </div>
</template>
