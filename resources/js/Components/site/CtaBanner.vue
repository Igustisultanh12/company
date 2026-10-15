<script setup>
import { ArrowRight, MessageCircle, Sparkles } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { waLink } from '@/lib/format';

defineProps({
    title: { type: String, default: 'Punya ide website? Yuk wujudkan bareng!' },
    subtitle: { type: String, default: 'Konsultasi gratis, tanpa ribet. Ceritakan kebutuhanmu, kami bantu rancang solusi terbaik sesuai budget.' },
});
const page = usePage();
const wa = computed(() => page.props.site?.whatsapp);
</script>

<template>
    <section class="container-x py-16">
        <div v-reveal="'zoom'" class="animate-gradient bg-brand-gradient relative overflow-hidden rounded-[2.5rem] border-2 border-ink p-8 text-white shadow-brutal-lg sm:p-14">
            <div class="dot-bg absolute inset-0 opacity-25"></div>
            <div class="animate-spin-slow absolute -top-16 -right-16 h-56 w-56 rounded-full border-[18px] border-dashed border-white/20"></div>
            <Sparkles class="animate-bounce-soft absolute right-10 bottom-10 hidden h-14 w-14 text-yellow-300 sm:block" />
            <div class="relative max-w-2xl">
                <h2 class="font-display text-3xl leading-tight font-bold sm:text-5xl">{{ title }}</h2>
                <p class="mt-4 text-base text-white/85 sm:text-lg">{{ subtitle }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link v-magnetic="0.3" :href="route('order.create')" class="btn btn-lg border-2 border-zinc-950 bg-yellow-300 text-zinc-950 shadow-[4px_4px_0_#09090b] hover:bg-lime-300" data-cursor>
                        Request Website Sekarang <ArrowRight class="h-5 w-5" />
                    </Link>
                    <a v-if="wa" v-magnetic="0.3" :href="waLink(wa, 'Halo, saya ingin konsultasi website')" target="_blank" rel="noopener noreferrer" class="btn btn-lg border-2 border-white/70 bg-white/10 text-white backdrop-blur hover:bg-white/20">
                        <MessageCircle class="h-5 w-5" /> Chat WhatsApp
                    </a>
                    <Link v-else :href="route('contact')" class="btn btn-lg border-2 border-white/70 bg-white/10 text-white backdrop-blur hover:bg-white/20">
                        <MessageCircle class="h-5 w-5" /> Hubungi Kami
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
