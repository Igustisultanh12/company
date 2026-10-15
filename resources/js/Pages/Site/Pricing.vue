<script setup>
import { CircleCheck, Handshake, MessagesSquare, Receipt, ShieldCheck } from '@lucide/vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import FaqAccordion from '@/Components/site/FaqAccordion.vue';
import PageHero from '@/Components/site/PageHero.vue';
import PricingCard from '@/Components/site/PricingCard.vue';
import SectionHeading from '@/Components/site/SectionHeading.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

defineProps({ meta: Object, packages: Array, faqs: Array });

const included = ['Konsultasi & analisis kebutuhan', 'Desain responsif (HP, tablet, desktop)', 'Sertifikat SSL / HTTPS', 'Optimasi kecepatan dasar', 'Keamanan standar (anti-spam & proteksi form)', 'Panduan penggunaan', 'Invoice resmi perusahaan', 'Pantau progres via Lacak Pesanan'];
const terms = [
    { icon: Handshake, title: 'DP 50%', text: 'Pengerjaan dimulai setelah DP diterima.' },
    { icon: Receipt, title: 'Invoice resmi', text: 'Setiap pembayaran tercatat & berinvoice.' },
    { icon: ShieldCheck, title: 'Garansi bug', text: 'Perbaikan bug gratis selama masa garansi.' },
    { icon: MessagesSquare, title: 'Support ramah', text: 'Bebas tanya via WhatsApp / email.' },
];
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Harga & Paket" title="Investasi digital yang" highlight="masuk akal" subtitle="Pilih paket yang paling cocok. Semua paket bisa disesuaikan — tinggal request fitur tambahan." :crumbs="[{ label: 'Harga' }]" />

    <section class="container-x pb-20">
        <div v-if="packages.length" class="grid gap-8 md:grid-cols-2 xl:grid-cols-4">
            <PricingCard v-for="(p, i) in packages" :key="p.id" :pkg="p" :index="i" />
        </div>
        <EmptyState v-else title="Paket sedang disiapkan" description="Hubungi kami untuk penawaran terbaik sesuai kebutuhanmu." />
    </section>

    <section class="section bg-surface-2/60">
        <div class="container-x grid gap-12 lg:grid-cols-2">
            <div>
                <SectionHeading eyebrow="Sudah Termasuk" title="Semua paket" highlight="sudah dapat ini" :center="false" />
                <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                    <li v-for="(f, i) in included" :key="f" v-reveal="{ type: 'left', delay: i * 50 }" class="flex items-start gap-3 rounded-2xl border border-line bg-card p-4">
                        <CircleCheck class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                        <span class="text-sm font-semibold">{{ f }}</span>
                    </li>
                </ul>
            </div>
            <div class="grid content-start gap-4 sm:grid-cols-2">
                <div v-for="(t, i) in terms" :key="t.title" v-reveal="{ type: 'zoom', delay: i * 90 }" v-tilt="6" class="card-brutal p-6">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white"><component :is="t.icon" class="h-6 w-6" /></div>
                    <h3 class="mt-4 font-display text-lg font-bold">{{ t.title }}</h3>
                    <p class="mt-1 text-sm text-muted">{{ t.text }}</p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="faqs.length" class="section">
        <div class="container-x max-w-3xl">
            <SectionHeading eyebrow="FAQ" title="Masih" highlight="bingung?" />
            <div class="mt-10"><FaqAccordion :items="faqs" /></div>
        </div>
    </section>

    <CtaBanner title="Belum menemukan paket yang pas?" subtitle="Ceritakan kebutuhanmu, kami buatkan penawaran custom yang sesuai budget." />
</template>
