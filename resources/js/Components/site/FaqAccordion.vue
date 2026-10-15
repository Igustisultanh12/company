<script setup>
import { Plus } from '@lucide/vue';
import { ref } from 'vue';

defineProps({ items: { type: Array, required: true } });
const open = ref(0);
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(f, i) in items"
            :key="f.id || i"
            v-reveal="{ type: 'up', delay: i * 60 }"
            :class="open === i ? 'border-ink shadow-brutal-sm' : 'border-line'"
            class="overflow-hidden rounded-2xl border-2 bg-card transition-all duration-300"
        >
            <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open === i" @click="open = open === i ? -1 : i">
                <span class="font-bold">{{ f.question }}</span>
                <span :class="open === i ? 'rotate-45 bg-yellow-300 text-zinc-900' : 'bg-surface-2'" class="grid h-8 w-8 shrink-0 place-items-center rounded-full transition-all duration-300">
                    <Plus class="h-4 w-4" />
                </span>
            </button>
            <div class="grid transition-all duration-300 ease-out" :class="open === i ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                <div class="overflow-hidden">
                    <p class="px-5 pb-5 text-sm leading-relaxed whitespace-pre-line text-muted">{{ f.answer }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
