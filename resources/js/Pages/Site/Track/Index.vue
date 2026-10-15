<script setup>
import { Radar, Search } from '@lucide/vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PageHero from '@/Components/site/PageHero.vue';
import FormField from '@/Components/ui/FormField.vue';

const props = defineProps({ meta: Object, code: String });
const form = useForm({ code: props.code || '', email: '' });
const submit = () => form.post(route('track.lookup'), { preserveScroll: true });
</script>

<template>
    <Head :title="meta.title" />
    <PageHero eyebrow="Lacak Pesanan" title="Pantau progres" highlight="website kamu" subtitle="Masukkan kode pesanan & email yang kamu gunakan saat request." :crumbs="[{ label: 'Lacak Pesanan' }]" />
    <section class="container-x max-w-xl pb-24">
        <form v-reveal="'zoom'" class="card-brutal relative overflow-hidden p-6 sm:p-8" @submit.prevent="submit">
            <Radar class="animate-spin-slow absolute -top-6 -right-6 h-28 w-28 text-violet-500/10" />
            <div class="relative space-y-5">
                <FormField label="Kode pesanan" :error="form.errors.code" required>
                    <input v-model="form.code" type="text" class="input font-mono tracking-wider uppercase" placeholder="KSA-261015-XXXXX" maxlength="30" autocomplete="off" />
                </FormField>
                <FormField label="Email" :error="form.errors.email" required>
                    <input v-model="form.email" type="email" class="input" placeholder="email@kamu.com" maxlength="150" autocomplete="email" />
                </FormField>
                <button v-magnetic="0.2" type="submit" class="btn btn-primary btn-lg w-full" :disabled="form.processing"><Search class="h-5 w-5" /> {{ form.processing ? 'Mencari…' : 'Lacak sekarang' }}</button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-muted">Lupa kode? Cek email konfirmasi atau <Link :href="route('contact')" class="font-bold text-violet-600 underline dark:text-violet-300">hubungi kami</Link>.</p>
    </section>
</template>
