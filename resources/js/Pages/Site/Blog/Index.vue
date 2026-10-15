<script setup>
import { route } from 'ziggy-js';
import PageHero from '@/Components/site/PageHero.vue';
import PostCard from '@/Components/site/PostCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SearchInput from '@/Components/ui/SearchInput.vue';
import { useFilters } from '@/composables/useFilters';

const props = defineProps({ meta: Object, posts: Object, categories: Array, filters: Object });
const f = useFilters(props.filters, route('blog.index'));
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <PageHero eyebrow="Blog" title="Insight &" highlight="tips digital" subtitle="Belajar bareng soal website, bisnis online & teknologi — ditulis santai, mudah dipahami." :crumbs="[{ label: 'Blog' }]">
        <div class="mx-auto flex max-w-xl flex-col items-center gap-4">
            <SearchInput v-model="f.q" placeholder="Cari artikel…" class="!w-full" />
            <div v-if="categories.length" class="flex flex-wrap justify-center gap-2">
                <button
                    v-for="c in ['', ...categories]"
                    :key="c || 'all'"
                    type="button"
                    :class="f.kategori === c ? 'border-ink bg-ink text-surface' : 'border-line bg-card hover:border-ink'"
                    class="rounded-full border-2 px-3.5 py-1.5 text-xs font-bold transition"
                    @click="f.kategori = c"
                >
                    {{ c || 'Semua' }}
                </button>
            </div>
        </div>
    </PageHero>

    <section class="container-x pb-24">
        <div v-if="posts.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <PostCard v-for="(p, i) in posts.data" :key="p.id" :post="p" :index="i" />
        </div>
        <EmptyState v-else title="Belum ada artikel" description="Coba kata kunci lain atau kembali lagi nanti ya." />
        <div class="mt-12"><Pagination :paginator="posts" /></div>
    </section>
</template>
