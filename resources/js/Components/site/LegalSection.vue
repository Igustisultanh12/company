<script setup>
import { BadgeCheck, FileCheck, Landmark, Leaf, ShieldCheck } from '@lucide/vue';
import { computed } from 'vue';
import { date } from '@/lib/format';

const props = defineProps({ legal: { type: Object, required: true } });

const cards = computed(() => [
    { icon: Landmark, color: 'from-violet-500 to-fuchsia-500', label: 'SK Kementerian Hukum RI', value: props.legal.sk_number, sub: props.legal.sk_date ? `Ditetapkan ${date(props.legal.sk_date, { day: 'numeric', month: 'long', year: 'numeric' })}` : '' },
    { icon: BadgeCheck, color: 'from-orange-400 to-pink-500', label: 'Nomor Induk Berusaha (NIB)', value: props.legal.nib, sub: 'Terdaftar di sistem OSS RBA' },
    { icon: FileCheck, color: 'from-cyan-400 to-violet-500', label: 'Sertifikat Standar', value: props.legal.certificate_number, sub: props.legal.risk_level ? `Tingkat risiko ${props.legal.risk_level}` : '' },
    { icon: Leaf, color: 'from-lime-400 to-emerald-500', label: 'Lingkungan Hidup', value: 'SPPL', sub: props.legal.sppl_status },
]);
</script>

<template>
    <div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(c, i) in cards" :key="c.label" v-reveal="{ type: 'flip', delay: i * 90 }" v-tilt="5" class="card-brutal relative overflow-hidden p-5">
                <div :class="c.color" class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br text-white"><component :is="c.icon" class="h-5 w-5" /></div>
                <p class="mt-4 text-xs font-bold tracking-wider text-muted uppercase">{{ c.label }}</p>
                <p class="mt-1 font-display text-lg leading-tight font-bold break-words">{{ c.value || '—' }}</p>
                <p v-if="c.sub" class="mt-1 text-xs text-muted">{{ c.sub }}</p>
            </div>
        </div>
        <div v-if="legal.kbli?.length" v-reveal="'up'" class="mt-6 rounded-3xl border-2 border-dashed border-line bg-card/60 p-5">
            <p class="flex items-center gap-2 text-sm font-bold"><ShieldCheck class="h-4 w-4 text-emerald-500" /> Bidang usaha resmi (KBLI 2025)</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <span v-for="k in legal.kbli" :key="k.code" class="inline-flex items-center gap-2 rounded-full border border-line bg-card py-1 pr-3 pl-1 text-xs font-semibold">
                    <span class="rounded-full bg-ink px-2 py-0.5 text-[11px] font-extrabold text-surface">{{ k.code }}</span>{{ k.title }}
                </span>
            </div>
        </div>
    </div>
</template>
