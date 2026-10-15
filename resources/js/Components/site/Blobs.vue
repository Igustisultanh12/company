<script setup>
defineProps({ variant: { type: String, default: 'hero' } });
</script>

<template>
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="grid-bg absolute inset-0 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)] opacity-70"></div>
        <div class="animate-float absolute -top-24 -left-20 h-[28rem] w-[28rem] rounded-full bg-violet-400/40 blur-3xl dark:bg-violet-600/30"></div>
        <div class="animate-float-slow absolute top-20 -right-24 h-[24rem] w-[24rem] rounded-full bg-fuchsia-400/35 blur-3xl dark:bg-fuchsia-600/25"></div>
        <div v-if="variant === 'hero'" class="animate-float absolute bottom-0 left-1/3 h-[20rem] w-[20rem] rounded-full bg-orange-300/40 blur-3xl [animation-delay:-3s] dark:bg-orange-500/20"></div>
        <div v-if="variant === 'hero'" class="animate-float-slow absolute right-1/4 -bottom-20 h-64 w-64 rounded-full bg-cyan-300/40 blur-3xl dark:bg-cyan-500/20"></div>
        <div class="noise absolute inset-0"></div>
    </div>
</template>
