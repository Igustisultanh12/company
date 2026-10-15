<script setup>
import { ArrowLeft, House } from '@lucide/vue';
import { computed } from 'vue';
import ToastHost from '@/Components/ui/ToastHost.vue';

const props = defineProps({ status: Number, message: String });

const content = computed(
    () =>
        ({
            403: { emoji: '🔒', title: 'Akses ditolak', text: props.message || 'Kamu tidak memiliki izin untuk membuka halaman ini.' },
            404: { emoji: '🛸', title: 'Halaman tidak ditemukan', text: 'Sepertinya halaman ini terbawa awan. Coba kembali ke beranda ya.' },
            429: { emoji: '🐢', title: 'Pelan-pelan dulu', text: 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.' },
            500: { emoji: '🛠️', title: 'Ada yang error', text: 'Server sedang bermasalah. Tim kami sudah diberi tahu.' },
            503: { emoji: '🚧', title: 'Sedang perawatan', text: 'Kami sedang meningkatkan sistem. Kembali lagi sebentar lagi ya!' },
        })[props.status] || { emoji: '😵', title: 'Terjadi kesalahan', text: 'Silakan coba lagi.' },
);
const back = () => window.history.back();
</script>

<template>
    <Head :title="`${status} – ${content.title}`" />
    <div class="relative grid min-h-screen place-items-center overflow-hidden px-6">
        <div class="grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
        <div class="animate-float pointer-events-none absolute -top-20 -left-20 h-96 w-96 rounded-full bg-violet-400/30 blur-3xl"></div>
        <div class="animate-float-slow pointer-events-none absolute -right-20 -bottom-20 h-96 w-96 rounded-full bg-fuchsia-400/30 blur-3xl"></div>
        <div class="relative text-center">
            <div class="animate-bounce-soft text-7xl">{{ content.emoji }}</div>
            <p class="text-gradient mt-4 font-display text-[7rem] leading-none font-bold sm:text-[10rem]">{{ status }}</p>
            <h1 class="mt-2 font-display text-3xl font-bold">{{ content.title }}</h1>
            <p class="mx-auto mt-3 max-w-md text-muted">{{ content.text }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <button type="button" class="btn btn-secondary btn-lg" @click="back"><ArrowLeft class="h-5 w-5" /> Kembali</button>
                <a href="/" class="btn btn-primary btn-lg"><House class="h-5 w-5" /> Beranda</a>
            </div>
        </div>
        <ToastHost />
    </div>
</template>
