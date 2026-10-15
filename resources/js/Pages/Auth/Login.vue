<script setup>
import { Eye, EyeOff, LogIn, Mail, LockKeyhole } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import FormField from '@/Components/ui/FormField.vue';

defineProps({ status: String });
const show = ref(false);
const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login.attempt'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Masuk" />
    <div>
        <span class="eyebrow">Area tim internal</span>
        <h1 class="mt-5 font-display text-4xl font-bold">Halo lagi! 👋</h1>
        <p class="mt-2 text-muted">Masuk untuk mengelola pekerjaan hari ini.</p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email">
                <div class="relative">
                    <Mail class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted" />
                    <input v-model="form.email" type="email" class="input pl-11" :class="{ 'input-error': form.errors.email }" autocomplete="username" required autofocus />
                </div>
            </FormField>
            <FormField label="Password" :error="form.errors.password">
                <div class="relative">
                    <LockKeyhole class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted" />
                    <input v-model="form.password" :type="show ? 'text' : 'password'" class="input pr-12 pl-11" autocomplete="current-password" required />
                    <button type="button" class="absolute top-1/2 right-3 -translate-y-1/2 rounded-lg p-1.5 text-muted hover:text-ink" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'" @click="show = !show">
                        <EyeOff v-if="show" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>
            </FormField>
            <div class="flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold"><input v-model="form.remember" type="checkbox" class="checkbox" /> Ingat saya</label>
                <Link :href="route('password.request')" class="text-sm font-bold text-violet-600 hover:underline dark:text-violet-300">Lupa password?</Link>
            </div>
            <button v-magnetic="0.15" v-ripple type="submit" class="btn btn-primary btn-lg shine w-full" :disabled="form.processing">
                <LogIn class="h-5 w-5" /> {{ form.processing ? 'Memeriksa…' : 'Masuk' }}
            </button>
        </form>
        <p class="mt-8 rounded-2xl border border-dashed border-line p-4 text-xs text-muted">🔐 Login dilindungi pembatasan percobaan, enkripsi sesi & autentikasi dua faktor (2FA). Aktivitas login tercatat di log keamanan.</p>
    </div>
</template>
