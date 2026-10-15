<script setup>
import { ClipboardCheck, Copy, House, MessageCircle, Radar } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import Blobs from '@/Components/site/Blobs.vue';
import { toast } from '@/composables/useToast';
import { prefersReducedMotion } from '@/directives/motion';
import { waLink } from '@/lib/format';

const props = defineProps({ meta: Object, order: Object });
const page = usePage();
const copied = ref(false);
const confetti = ref([]);

const wa = computed(() => page.props.site?.whatsapp);
const waText = computed(() => `Halo ${page.props.site?.short_name}, saya ${props.order.client_name} baru saja mengirim request website (${props.order.type_label}) dengan kode ${props.order.code}. Mohon info selanjutnya 🙏`);

const copy = async () => {
    try {
        await navigator.clipboard.writeText(props.order.code);
        copied.value = true;
        toast('Kode pesanan disalin!');
    } catch {
        toast('Salin manual: ' + props.order.code, 'info');
    }
};

onMounted(() => {
    if (prefersReducedMotion()) return;
    const colors = ['#8b5cf6', '#d946ef', '#f97316', '#a3e635', '#22d3ee', '#facc15'];
    confetti.value = Array.from({ length: 70 }, (_, i) => ({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 0.8,
        duration: 2.4 + Math.random() * 2,
        color: colors[i % colors.length],
        rotate: Math.random() * 360,
        size: 6 + Math.random() * 8,
    }));
    setTimeout(() => (confetti.value = []), 5500);
});

const steps = ['Tim kami meninjau request kamu', 'Kami hubungi via WhatsApp / email (≤ 1x24 jam kerja)', 'Diskusi & penawaran harga', 'Deal → pengerjaan dimulai 🚀'];
</script>

<template>
    <Head :title="meta.title" />
    <section class="relative isolate overflow-hidden pt-32 pb-24 sm:pt-40">
        <Blobs />
        <div class="pointer-events-none fixed inset-0 z-50 overflow-hidden" aria-hidden="true">
            <span
                v-for="c in confetti"
                :key="c.id"
                class="absolute -top-4 block rounded-sm"
                :style="{ left: `${c.left}%`, width: `${c.size}px`, height: `${c.size * 0.45}px`, background: c.color, transform: `rotate(${c.rotate}deg)`, animation: `confetti-fall ${c.duration}s ${c.delay}s cubic-bezier(.2,.6,.4,1) forwards` }"
            ></span>
        </div>
        <div class="container-x max-w-2xl text-center">
            <div class="animate-pop-in mx-auto grid h-24 w-24 place-items-center rounded-[2rem] border-2 border-ink bg-lime-300 text-5xl shadow-brutal">🎉</div>
            <h1 class="mt-8 font-display text-4xl font-bold sm:text-5xl">Request terkirim, <span class="text-gradient">{{ order.client_name.split(' ')[0] }}!</span></h1>
            <p class="mt-4 text-lg text-muted">Terima kasih sudah mempercayakan proyek <b class="text-ink">{{ order.type_label }}</b> kamu kepada kami.</p>

            <div class="card-brutal mx-auto mt-10 max-w-md p-6">
                <p class="text-xs font-extrabold tracking-wider text-muted uppercase">Kode pesanan kamu</p>
                <div class="mt-2 flex items-center justify-center gap-3">
                    <span class="font-display text-2xl font-bold tracking-wider sm:text-3xl">{{ order.code }}</span>
                    <button type="button" class="btn btn-secondary btn-icon" aria-label="Salin kode" @click="copy">
                        <ClipboardCheck v-if="copied" class="h-5 w-5 text-emerald-500" />
                        <Copy v-else class="h-5 w-5" />
                    </button>
                </div>
                <p class="mt-2 text-xs text-muted">Simpan kode ini. Lacak progres dengan kode + email <b>{{ order.client_email }}</b>.</p>
            </div>

            <ol class="mx-auto mt-10 max-w-md space-y-3 text-left">
                <li v-for="(s, i) in steps" :key="s" :style="{ animationDelay: `${300 + i * 120}ms` }" class="animate-fade-up flex items-center gap-3 rounded-2xl border border-line bg-card p-3">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-ink text-sm font-extrabold text-surface">{{ i + 1 }}</span>
                    <span class="text-sm font-semibold">{{ s }}</span>
                </li>
            </ol>

            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a v-if="wa" v-magnetic="0.25" :href="waLink(wa, waText)" target="_blank" rel="noopener noreferrer" class="btn btn-lg border-2 border-zinc-950 bg-[#25D366] text-white shadow-[4px_4px_0_#09090b]">
                    <MessageCircle class="h-5 w-5" /> Konfirmasi via WhatsApp
                </a>
                <Link :href="route('track.show', order.code)" class="btn btn-primary btn-lg"><Radar class="h-5 w-5" /> Lacak pesanan</Link>
                <Link :href="route('home')" class="btn btn-secondary btn-lg"><House class="h-5 w-5" /> Beranda</Link>
            </div>
        </div>
    </section>
</template>
