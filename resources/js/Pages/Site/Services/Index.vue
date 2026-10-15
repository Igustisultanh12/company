<script setup>
import { ArrowRight, WandSparkles } from '@lucide/vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import PageHero from '@/Components/site/PageHero.vue';
import SectionHeading from '@/Components/site/SectionHeading.vue';
import ServiceCard from '@/Components/site/ServiceCard.vue';

defineProps({ meta: Object, services: Array, packages: Array });
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Layanan" title="Solusi digital" highlight="all-in-one" subtitle="Pilih layanan sesuai kebutuhan, atau kombinasikan. Semua dikerjakan tim profesional dengan standar keamanan tinggi." :crumbs="[{ label: 'Layanan' }]">
        <Link v-magnetic="0.3" :href="route('order.create')" class="btn btn-primary btn-lg shine">Konsultasi gratis <ArrowRight class="h-5 w-5" /></Link>
    </PageHero>

    <section class="container-x pb-20">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ServiceCard v-for="(s, i) in services" :key="s.id" :service="s" :index="i" detailed />
        </div>
    </section>

    <section class="section bg-surface-2/60">
        <div class="container-x grid items-center gap-10 lg:grid-cols-2">
            <SectionHeading eyebrow="Custom Request" title="Kebutuhanmu unik?" highlight="Kami suka tantangan." :center="false" subtitle="Ceritakan alur bisnismu lewat formulir Request Website. Kamu bisa memilih jenis website, fitur, budget sampai tenggat — tim kami akan menyusun penawaran terbaik." />
            <div v-reveal="'right'" class="card-brutal relative overflow-hidden p-8">
                <WandSparkles class="animate-bounce-soft absolute top-6 right-6 h-10 w-10 text-fuchsia-500" />
                <ol class="space-y-4">
                    <li v-for="(s, i) in ['Pilih jenis website & paket', 'Centang fitur yang diinginkan', 'Tentukan budget & tenggat', 'Kirim — dapat kode lacak pesanan']" :key="s" class="flex items-center gap-4">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border-2 border-ink bg-yellow-300 font-display font-bold text-zinc-900">{{ i + 1 }}</span>
                        <span class="font-semibold">{{ s }}</span>
                    </li>
                </ol>
                <Link :href="route('order.create')" class="btn btn-primary mt-8 w-full">Mulai request <ArrowRight class="h-4 w-4" /></Link>
            </div>
        </div>
    </section>

    <CtaBanner />
</template>
