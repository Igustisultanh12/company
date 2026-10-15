<script setup>
import { ArrowLeft, CalendarDays, Clock, Copy, Eye, Link2 } from '@lucide/vue';
import { ref } from 'vue';
import CtaBanner from '@/Components/site/CtaBanner.vue';
import PostCard from '@/Components/site/PostCard.vue';
import Avatar from '@/Components/ui/Avatar.vue';
import SocialIcon from '@/Components/ui/SocialIcon.vue';
import { toast } from '@/composables/useToast';
import { dateLong, number } from '@/lib/format';

const props = defineProps({ meta: Object, post: Object, bodyHtml: String, related: Array });
const copied = ref(false);
const url = typeof window !== 'undefined' ? window.location.href : '';

const copy = async () => {
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        toast('Tautan artikel disalin!', 'success');
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        toast('Gagal menyalin tautan', 'error');
    }
};
const shareWa = `https://wa.me/?text=${encodeURIComponent(`${props.post.title} ${url}`)}`;
const shareLinkedin = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
</script>

<template>
    <Head :title="meta.title"><meta head-key="description" name="description" :content="meta.description" /></Head>

    <article class="pt-32 sm:pt-40">
        <header class="container-x max-w-4xl text-center">
            <Link :href="route('blog.index')" class="btn btn-ghost btn-sm mb-6"><ArrowLeft class="h-4 w-4" /> Blog</Link>
            <span v-if="post.category" v-reveal="'zoom'" class="eyebrow">{{ post.category }}</span>
            <h1 v-reveal="{ type: 'up', delay: 80 }" class="mt-5 font-display text-3xl leading-tight font-bold sm:text-5xl">{{ post.title }}</h1>
            <div v-reveal="{ type: 'up', delay: 150 }" class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-muted">
                <span class="inline-flex items-center gap-2"><Avatar :user="post.author" :name="post.author?.name || 'Admin'" size="sm" /> {{ post.author?.name || 'Tim Kreatif Sky' }}</span>
                <span class="inline-flex items-center gap-1.5"><CalendarDays class="h-4 w-4" /> {{ dateLong(post.published_at) }}</span>
                <span class="inline-flex items-center gap-1.5"><Clock class="h-4 w-4" /> {{ post.reading_time }} menit baca</span>
                <span class="inline-flex items-center gap-1.5"><Eye class="h-4 w-4" /> {{ number(post.views) }}x dibaca</span>
            </div>
        </header>
        <div v-if="post.cover_url" v-reveal="'zoom'" class="container-x mt-10 max-w-5xl">
            <img :src="post.cover_url" :alt="post.title" class="card-brutal w-full object-cover" />
        </div>
        <div class="container-x mt-12 grid max-w-5xl gap-10 lg:grid-cols-[1fr_200px]">
            <div class="prose-content" v-html="bodyHtml"></div>
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <p class="text-xs font-bold tracking-wider text-muted uppercase">Bagikan</p>
                <div class="mt-3 flex gap-2 lg:flex-col">
                    <a :href="shareWa" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm justify-start"><SocialIcon name="whatsapp" class="h-4 w-4" /> WhatsApp</a>
                    <a :href="shareLinkedin" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm justify-start"><SocialIcon name="linkedin" class="h-4 w-4" /> LinkedIn</a>
                    <button type="button" class="btn btn-secondary btn-sm justify-start" @click="copy"><component :is="copied ? Link2 : Copy" class="h-4 w-4" /> {{ copied ? 'Tersalin!' : 'Salin tautan' }}</button>
                </div>
            </aside>
        </div>
    </article>

    <section v-if="related.length" class="section">
        <div class="container-x">
            <h2 v-reveal class="font-display text-3xl font-bold">Baca juga</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"><PostCard v-for="(p, i) in related" :key="p.id" :post="p" :index="i" /></div>
        </div>
    </section>

    <CtaBanner />
</template>
