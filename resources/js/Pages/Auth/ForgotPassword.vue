<script setup>
import { MailCheck } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FormField from '@/Components/ui/FormField.vue';

const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>

<template>
    <Head title="Lupa Password" />
    <div>
        <div class="grid h-16 w-16 place-items-center rounded-2xl border-2 border-ink bg-cyan-300 text-zinc-900 shadow-brutal-sm"><MailCheck class="h-8 w-8" /></div>
        <h1 class="mt-6 font-display text-3xl font-bold">Lupa password?</h1>
        <p class="mt-2 text-muted">Tenang, masukkan email akunmu. Kami kirimkan tautan untuk membuat password baru.</p>
        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="input" autocomplete="email" required /></FormField>
            <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="form.processing">{{ form.processing ? 'Mengirim…' : 'Kirim tautan reset' }}</button>
        </form>
        <p class="mt-6 text-center text-sm"><Link :href="route('login')" class="font-bold text-violet-600 hover:underline dark:text-violet-300">← Kembali ke login</Link></p>
    </div>
</template>
