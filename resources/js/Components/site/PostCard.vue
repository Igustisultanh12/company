<script setup>
import { CalendarDays, Clock } from '@lucide/vue';
import { date } from '@/lib/format';

defineProps({
    post: { type: Object, required: true },
    index: { type: Number, default: 0 },
});
</script>

<template>
    <Link v-reveal="{ type: 'up', delay: (index % 3) * 90 }" :href="route('blog.show', post.slug)" class="group card card-hover flex h-full flex-col overflow-hidden" data-cursor>
        <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-violet-500 via-fuchsia-500 to-orange-400">
            <img v-if="post.cover_url" :src="post.cover_url" :alt="post.title" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" />
            <div v-else class="dot-bg absolute inset-0 grid place-items-center opacity-60">
                <span class="font-display text-5xl font-bold text-white/90">Aa</span>
            </div>
            <span v-if="post.category" class="absolute top-3 left-3 rounded-full border-2 border-zinc-950 bg-yellow-300 px-2.5 py-0.5 text-[11px] font-extrabold text-zinc-900">{{ post.category }}</span>
        </div>
        <div class="flex flex-1 flex-col p-5">
            <h3 class="font-display text-lg leading-snug font-bold transition group-hover:text-violet-600 dark:group-hover:text-violet-300">{{ post.title }}</h3>
            <p class="mt-2 line-clamp-2 flex-1 text-sm text-muted">{{ post.excerpt }}</p>
            <div class="mt-4 flex items-center gap-4 text-xs font-semibold text-muted">
                <span class="inline-flex items-center gap-1"><CalendarDays class="h-3.5 w-3.5" /> {{ date(post.published_at) }}</span>
                <span class="inline-flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ post.reading_time || 1 }} menit</span>
            </div>
        </div>
    </Link>
</template>
