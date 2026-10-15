<script setup>
defineProps({
    items: { type: Array, required: true },
    reverse: { type: Boolean, default: false },
});
</script>

<template>
    <div class="group relative flex overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
        <div :class="reverse ? 'animate-marquee-reverse' : 'animate-marquee'" class="flex w-max gap-4 py-2 group-hover:[animation-play-state:paused]">
            <template v-for="n in 2" :key="n">
                <div v-for="(item, i) in items" :key="`${n}-${i}`" class="shrink-0">
                    <slot :item="item" />
                </div>
            </template>
        </div>
    </div>
</template>
