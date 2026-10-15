<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    highlight: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    center: { type: Boolean, default: true },
});
</script>

<template>
    <div :class="center ? 'mx-auto text-center' : ''" class="max-w-3xl">
        <span v-if="eyebrow" v-reveal="'zoom'" class="eyebrow">{{ eyebrow }}</span>
        <h2 v-reveal="{ type: 'up', delay: 80 }" class="mt-5 font-display text-3xl leading-[1.1] font-bold sm:text-5xl">
            {{ title }} <span v-if="highlight" class="text-gradient">{{ highlight }}</span>
        </h2>
        <p v-if="subtitle" v-reveal="{ type: 'up', delay: 160 }" class="mt-4 text-base leading-relaxed text-muted sm:text-lg">{{ subtitle }}</p>
    </div>
</template>
