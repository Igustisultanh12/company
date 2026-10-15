<script setup>
defineProps({
    withText: { type: Boolean, default: true },
    name: { type: String, default: 'Kreatif Sky' },
    light: { type: Boolean, default: false },
});
</script>

<template>
    <span class="inline-flex items-center gap-2.5">
        <span class="relative grid h-10 w-10 shrink-0 place-items-center">
            <svg viewBox="0 0 48 48" class="h-10 w-10 transition-transform duration-500 group-hover:rotate-[-8deg]" aria-hidden="true">
                <defs>
                    <linearGradient id="ksa-logo-g" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#7c3aed" />
                        <stop offset="55%" stop-color="#d946ef" />
                        <stop offset="100%" stop-color="#fb923c" />
                    </linearGradient>
                </defs>
                <rect x="2" y="2" width="44" height="44" rx="14" fill="url(#ksa-logo-g)" />
                <path d="M14 30.5c-2.8 0-4.5-2-4.5-4.3 0-2.4 1.9-4.3 4.3-4.3.4-3.8 3.6-6.6 7.4-6.6 3 0 5.6 1.8 6.8 4.4a5.4 5.4 0 0 1 7.6 4.9c0 3.4-2.6 5.9-5.6 5.9H14z" fill="#fff" opacity=".95" />
                <path d="M20 25.5l3 3 6-6.5" fill="none" stroke="#7c3aed" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="38" cy="11" r="3" fill="#bef264" />
            </svg>
        </span>
        <span v-if="withText" class="leading-none">
            <span :class="light ? 'text-white' : 'text-ink'" class="block font-display text-[17px] font-bold tracking-tight">{{ name }}</span>
            <span :class="light ? 'text-white/70' : 'text-muted'" class="block text-[10px] font-bold tracking-[0.18em] uppercase">PT · Abadi</span>
        </span>
    </span>
</template>
