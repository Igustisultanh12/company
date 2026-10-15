<script setup>
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PasswordStrength from '@/Components/ui/PasswordStrength.vue';
import FormField from '@/Components/ui/FormField.vue';

const props = defineProps({ token: String, email: String });
const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
const submit = () => form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <Head title="Reset Password" />
    <div>
        <h1 class="font-display text-3xl font-bold">Buat password baru 🔑</h1>
        <p class="mt-2 text-muted">Minimal 8 karakter dengan huruf besar, huruf kecil & angka.</p>
        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="input" autocomplete="username" required /></FormField>
            <FormField label="Password baru" :error="form.errors.password">
                <input v-model="form.password" type="password" class="input" autocomplete="new-password" required />
                <PasswordStrength :value="form.password" class="mt-2" />
            </FormField>
            <FormField label="Ulangi password" :error="form.errors.password_confirmation"><input v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password" required /></FormField>
            <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="form.processing">Simpan password</button>
        </form>
    </div>
</template>
