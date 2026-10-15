<script setup>
import { ArrowLeft, ArrowUpRight, Calendar, Layers, User } from '@lucide/vue';
import Blobs from '@/Components/site/Blobs.vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import PortfolioCard from '@/Components/site/PortfolioCard.vue';
import { gradient } from '@/lib/colors';

defineProps({ meta: Object, portfolio: Object, descriptionHtml: String, others: Array });
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <section class="relative isolate overflow-hidden pt-32 pb-12 sm:pt-40">
        <Blobs variant="soft" />
        <div class="container-x">
            <Link :href="route('portfolio.index')" class="btn btn-ghost btn-sm mb-6"><ArrowLeft class="h-4 w-4" /> Semua portofolio</Link>
            <p v-reveal class="text-sm font-bold tracking-wider text-violet-600 uppercase dark:text-violet-300">{{ portfolio.category }}</p>
            <h1 v-reveal="{ type: 'up', delay: 80 }" class="mt-3 max-w-4xl font-display text-4xl leading-tight font-bold sm:text-6xl">{{ portfolio.title }}</h1>
            <p v-reveal="{ type: 'up', delay: 150 }" class="mt-4 max-w-2xl text-lg text-muted">{{ portfolio.excerpt }}</p>
        </div>
    </section>

    <section class="container-x pb-20">
        <div v-reveal="'zoom'" class="card-brutal overflow-hidden">
            <img v-if="portfolio.image_url" :src="portfolio.image_url" :alt="portfolio.title" class="w-full object-cover" />
            <div v-else :class="gradient[portfolio.color] || gradient.violet" class="dot-bg grid aspect-[21/9] place-items-center bg-gradient-to-br">
                <span class="font-display text-4xl font-bold text-white drop-shadow sm:text-6xl">{{ portfolio.title.split(' ')[0] }}</span>
            </div>
        </div>
        <div class="mt-12 grid gap-10 lg:grid-cols-3">
            <div class="prose-content lg:col-span-2" v-html="descriptionHtml"></div>
            <aside class="space-y-4">
                <div class="card p-6">
                    <dl class="space-y-4 text-sm">
                        <div v-if="portfolio.client" class="flex gap-3"><User class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Klien</dt><dd class="font-bold">{{ portfolio.client }}</dd></div></div>
                        <div v-if="portfolio.year" class="flex gap-3"><Calendar class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Tahun</dt><dd class="font-bold">{{ portfolio.year }}</dd></div></div>
                        <div v-if="portfolio.tech_stack?.length" class="flex gap-3"><Layers class="h-5 w-5 text-fuchsia-500" /><div><dt class="text-muted">Teknologi</dt><dd class="mt-1 flex flex-wrap gap-1.5"><span v-for="t in portfolio.tech_stack" :key="t" class="chip">{{ t }}</span></dd></div></div>
                    </dl>
                    <a v-if="portfolio.url" :href="portfolio.url" target="_blank" rel="noopener noreferrer" class="btn btn-brutal mt-6 w-full">Kunjungi website <ArrowUpRight class="h-4 w-4" /></a>
                </div>
            </aside>
        </div>
    </section>

    <section v-if="others.length" class="section bg-surface-2/60">
        <div class="container-x">
            <h2 v-reveal class="font-display text-3xl font-bold">Proyek lainnya</h2>
            <div class="mt-10 grid gap-10 sm:grid-cols-2 lg:grid-cols-3"><PortfolioCard v-for="(p, i) in others" :key="p.id" :item="p" :index="i" /></div>
        </div>
    </section>

    <CtaBanner />
</template>
