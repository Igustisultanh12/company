<script setup>
import { computed, ref } from 'vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import PageHero from '@/Components/site/PageHero.vue';
import PortfolioCard from '@/Components/site/PortfolioCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({ meta: Object, portfolios: Array, categories: Array });
const active = ref('Semua');
const filtered = computed(() => (active.value === 'Semua' ? props.portfolios : props.portfolios.filter((p) => p.category === active.value)));
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Portofolio" title="Karya yang" highlight="bicara sendiri" subtitle="Beberapa proyek website & sistem yang telah kami bangun." :crumbs="[{ label: 'Portofolio' }]" />

    <section class="container-x pb-24">
        <div v-if="categories.length > 1" v-reveal class="mb-10 flex flex-wrap justify-center gap-2">
            <button
                v-for="c in ['Semua', ...categories]"
                :key="c"
                type="button"
                :class="active === c ? 'border-ink bg-ink text-surface shadow-brutal-sm' : 'border-line bg-card hover:border-ink'"
                class="rounded-full border-2 px-4 py-2 text-sm font-bold transition"
                @click="active = c"
            >
                {{ c }}
            </button>
        </div>
        <TransitionGroup v-if="filtered.length" tag="div" class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3" move-class="transition duration-500" enter-active-class="transition duration-500" enter-from-class="opacity-0 scale-95" leave-active-class="hidden">
            <PortfolioCard v-for="(p, i) in filtered" :key="p.id" :item="p" :index="i" />
        </TransitionGroup>
        <EmptyState v-else title="Portofolio segera hadir 🚧" description="Kami sedang merapikan studi kasus terbaru. Sementara itu, yuk diskusikan proyekmu!">
            <Link :href="route('order.create')" class="btn btn-primary">Request Website</Link>
        </EmptyState>
    </section>

    <CtaBanner title="Proyek berikutnya bisa jadi milikmu!" />
</template>
