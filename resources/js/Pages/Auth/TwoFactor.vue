<script setup>
import { KeyRound, ShieldCheck } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { route } from 'ziggy-js';

const recovery = ref(false);
const digits = ref(['', '', '', '', '', '']);
const inputs = ref([]);
const form = useForm({ code: '', recovery_code: '' });

const submit = () => {
    form.code = recovery.value ? '' : digits.value.join('');
    if (!recovery.value) form.recovery_code = '';
    form.post(route('login.2fa.verify'), {
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            nextTick(() => inputs.value[0]?.focus());
        },
    });
};

const onInput = (i, e) => {
    const v = e.target.value.replace(/\D/g, '');
    if (v.length > 1) {
        v.slice(0, 6).split('').forEach((d, j) => (digits.value[j] = d));
        inputs.value[Math.min(5, v.length - 1)]?.focus();
    } else {
        digits.value[i] = v;
        if (v && i < 5) inputs.value[i + 1]?.focus();
    }
    if (digits.value.join('').length === 6) submit();
};
const onKey = (i, e) => {
    if (e.key === 'Backspace' && !digits.value[i] && i > 0) inputs.value[i - 1]?.focus();
};
</script>

<template>
    <Head title="Verifikasi 2FA" />
    <div>
        <div class="grid h-16 w-16 place-items-center rounded-2xl border-2 border-ink bg-lime-300 text-zinc-900 shadow-brutal-sm"><ShieldCheck class="h-8 w-8" /></div>
        <h1 class="mt-6 font-display text-3xl font-bold">Verifikasi dua langkah</h1>
        <p class="mt-2 text-muted">{{ recovery ? 'Masukkan salah satu kode pemulihan yang kamu simpan.' : 'Masukkan 6 digit kode dari aplikasi autentikator (Google Authenticator / Authy).' }}</p>

        <form class="mt-8" @submit.prevent="submit">
            <div v-if="!recovery" class="flex justify-between gap-2">
                <input
                    v-for="(d, i) in digits"
                    :key="i"
                    :ref="(el) => (inputs[i] = el)"
                    :value="d"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    class="input h-14 w-12 text-center font-display text-2xl font-bold sm:w-14"
                    :aria-label="`Digit ${i + 1}`"
                    @input="onInput(i, $event)"
                    @keydown="onKey(i, $event)"
                />
            </div>
            <input v-else v-model="form.recovery_code" type="text" class="input font-mono" placeholder="xxxxx-xxxxx" autocomplete="off" />
            <p v-if="form.errors.code" class="field-error">{{ form.errors.code }}</p>
            <button type="submit" class="btn btn-primary btn-lg mt-6 w-full" :disabled="form.processing">{{ form.processing ? 'Memverifikasi…' : 'Verifikasi' }}</button>
        </form>
        <button type="button" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-violet-600 hover:underline dark:text-violet-300" @click="recovery = !recovery">
            <KeyRound class="h-4 w-4" /> {{ recovery ? 'Gunakan kode autentikator' : 'Gunakan kode pemulihan' }}
        </button>
    </div>
</template>
